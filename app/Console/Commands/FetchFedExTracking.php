<?php
namespace Vanguard\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Vanguard\Mail\FedExTrackingUpdate;
use Vanguard\Models\ShipmentTracking;
use Vanguard\Services\FedExTrackingService;

class FetchFedExTracking extends Command
{
    protected $signature   = 'fedex:fetch-tracking';
    protected $description = 'Sync FedEx tracking numbers from Google Sheet and fetch live status from FedEx API';

    private const SPREADSHEET_ID = '1DqOS8jEMMunm8YqrAURJijMXSsXxWdtLmngAyyf73hU';
    private const SHEET_RANGE    = 'EPS!A:G';
    private const BATCH_SIZE     = 30;
    private array $pendingEmails = [];

    public function handle(): int
    {
        $this->info('FedEx Tracking Sync Started: ' . Carbon::now());
        $sheetRows = $this->getTrackingNumbersFromSheet();
        $trackingRowMap = [];

        foreach ($sheetRows as $row) {
            $trackingRowMap[$row['tracking_number']][] = $row['sheet_row'];
        } 

        if (!empty($sheetRows)) {
            foreach ($sheetRows as $row) {
                $record = ShipmentTracking::firstOrCreate(
                    ['carrier' => 'FEDEX', 'tracking_number' => $row['tracking_number'], 'unique_id' => $row['unique_id']]
                );

                $updateData = array_filter([
                    'order_id'       => $row['order_id'],
                    'product_sku'    => $row['product_sku'],
                    'customer_email' => $row['customer_email'],
                ]);

                if (!empty($updateData)) {
                    $record->update($updateData);
                }
            }
            $this->info(count($sheetRows) . ' FEDEX tracking number(s) synced to DB.');
        }
        $baseQuery = ShipmentTracking::fedex()->where(function ($q) {
            $q->whereNull('status')
              ->orWhere('status', '')
              ->orWhereRaw("UPPER(status) NOT IN ('DELIVERED', 'DL')");
        });

        $total = $baseQuery->count();

        if ($total === 0) {
            $this->info('No pending FedEx tracking numbers to process.');
            $this->updateSheetStatuses($trackingRowMap);
            return 0;
        }

        $this->info("Processing {$total} pending tracking number(s) via FedEx API in batches of " . self::BATCH_SIZE . ".");

        $processed = 0;

        $baseQuery->chunkById(self::BATCH_SIZE, function ($pending) use (&$processed, $total) {
            $trackingNumbers = $pending->pluck('tracking_number')->unique()->values()->all();

            try {
                $response = FedExTrackingService::track($trackingNumbers, true);
                $results  = $response['output']['completeTrackResults'] ?? [];

                foreach ($results as $result) {
                    $trackingNumber = $result['trackingNumber'] ?? null;
                    if (!$trackingNumber) continue;

                    $matchingRecords = $pending->filter(fn($r) => $r->tracking_number === $trackingNumber);
                    if ($matchingRecords->isEmpty()) continue;

                    $trackResults = $result['trackResults'][0] ?? [];
                    $error        = $trackResults['error'] ?? null;

                    if ($error) {
                        $msg = $error['message'] ?? 'Unknown FedEx error';
                        $this->warn("{$trackingNumber}: {$msg}");
                        foreach ($matchingRecords as $rec) {
                            $rec->update([
                                'error_message'   => substr($msg, 0, 255),
                                'last_fetched_at' => now(),
                            ]);
                        }
                        continue;
                    }
                    foreach ($matchingRecords as $rec) {
                        $this->updateRecord($rec, $trackResults);
                    }
                }
            } catch (\Exception $e) {
                $this->error('FedEx API error: ' . $e->getMessage());
                ShipmentTracking::fedex()
                    ->whereIn('tracking_number', $trackingNumbers)
                    ->update([
                        'error_message'   => substr($e->getMessage(), 0, 255),
                        'last_fetched_at' => now(),
                    ]);
            }
            $processed += $pending->count();
            $this->info("Progress: {$processed} / {$total} processed.");
        });

        foreach ($this->pendingEmails as $orderId => $emailData) {
            if (empty($emailData['record']->customer_email)) {
                $this->warn("Skipping email for order {$orderId}: no customer_email on tracking record.");
                continue;
            }
            $this->sendDeliveryEmail($emailData['record'], $emailData['delivered'], $emailData['total']);
        }

        $this->updateSheetStatuses($trackingRowMap);

        $this->info('FedEx Tracking Sync Completed: ' . Carbon::now());
        return 0;
    }

    private function getTrackingNumbersFromSheet(): array
    {
        try {
            $service  = $this->getSheetsService(true);
            $response = $service->spreadsheets_values->get(self::SPREADSHEET_ID, self::SHEET_RANGE);
            $rows     = $response->getValues() ?? [];
            $result   = [];

            foreach ($rows as $index => $row) {
                if ($index <= 1) continue; // skip notice + header rows

                $trackingNumber = trim($row[0] ?? ''); // Column A
                $carrier        = strtoupper(trim($row[1] ?? '')); // Column B
                $orderId        = trim($row[2] ?? ''); // Column C
                $uniqueId       = trim($row[3] ?? ''); // Column D
                $productSku     = trim($row[4] ?? ''); // Column E
                $customerEmail  = trim($row[6] ?? ''); // Column G

                if ($carrier === 'FEDEX' && !empty($trackingNumber)) {
                    $result[] = [
                        'tracking_number' => $trackingNumber,
                        'order_id'        => $orderId ?: null,
                        'unique_id'       => $uniqueId ?: null,
                        'product_sku'     => $productSku ?: null,
                        'customer_email'  => $customerEmail ?: null,
                        'sheet_row'       => $index + 1, // 1-indexed Google Sheet row number
                    ];
                }
            }
            $this->info('Found ' . count($result) . ' FEDEX row(s) in Google Sheet.');
            return $result;

        } catch (\Exception $e) {
            $this->error('Google Sheet error: ' . $e->getMessage());
            return [];
        }
    }

    private function updateRecord(ShipmentTracking $record, array $data): void
    {
        $latestStatus  = $data['latestStatusDetail'] ?? [];
        $dateAndTimes  = $data['dateAndTimes'] ?? [];
        $recipientInfo = $data['recipientInformation']['address'] ?? [];
        $shipperInfo   = $data['shipperInformation']['address'] ?? [];
        $oldStatus = $record->status;
        $newStatus = $latestStatus['code'] ?? null;
        $newStatusDescription = $latestStatus['description'] ?? null;

        $record->update([
            'status'             => $newStatus,
            'status_description' => $newStatusDescription,
            'service_type'       => $data['serviceDetail']['type'] ?? null,
            'origin'             => $this->formatAddress($shipperInfo),
            'destination'        => $this->formatAddress($recipientInfo),
            'estimated_delivery' => $this->findDate($dateAndTimes, 'ESTIMATED_DELIVERY'),
            'actual_delivery'    => $this->findDate($dateAndTimes, 'ACTUAL_DELIVERY'),
            'scan_events'        => $this->parseScanEvents($data['scanEvents'] ?? []),
            'error_message'      => null,
            'last_fetched_at'    => now(),
        ]);

        $this->info("{$record->tracking_number} — Status: " . ($newStatus ?? 'N/A'));

        $isNowDelivered      = strtoupper($newStatus ?? '') === 'DL';
        $wasAlreadyDelivered = in_array(strtoupper($oldStatus ?? ''), ['DL', 'DELIVERED']);

        if ($isNowDelivered && !$wasAlreadyDelivered && !empty($record->order_id)) {
            $progress = $this->getDeliveryProgress($record->order_id);
            $orderId  = $record->order_id;

            $this->info("  → Delivery queued: orderId={$orderId} delivered={$progress['delivered']}/{$progress['total']} email=" . ($record->customer_email ?? 'NULL'));

            if (!isset($this->pendingEmails[$orderId])) {
                $this->pendingEmails[$orderId] = [
                    'record'    => $record->fresh(),
                    'delivered' => $progress['delivered'],
                    'total'     => $progress['total'],
                ];
            } else {
                $this->pendingEmails[$orderId]['delivered'] = $progress['delivered'];
                $this->pendingEmails[$orderId]['total']     = $progress['total'];
                if (empty($this->pendingEmails[$orderId]['record']->customer_email) && !empty($record->customer_email)) {
                    $this->pendingEmails[$orderId]['record'] = $record->fresh();
                }
            }
        }
    }

    private function sendDeliveryEmail(ShipmentTracking $record, int $deliveredCount, int $totalItems): void
    {
        try {
            Mail::to($record->customer_email)
                ->send(new FedExTrackingUpdate($record, $deliveredCount, $totalItems));
        } catch (\Exception $e) {
            $this->error("Email failed for {$record->tracking_number}: {$e->getMessage()}");
        }
    }

    private function getDeliveryProgress(string $orderId): array
    {
        $total = ShipmentTracking::fedex()
            ->where('order_id', $orderId)
            ->count();

        $delivered = ShipmentTracking::fedex()
            ->where('order_id', $orderId)
            ->whereIn('status', ['DELIVERED', 'DL'])
            ->count();

        return [
            'delivered' => max(1, $delivered),
            'total'     => max(1, $total),
        ];
    }

    private function formatAddress(array $address): ?string
    {
        $parts = array_filter([
            $address['city'] ?? null,
            $address['stateOrProvinceCode'] ?? null,
            $address['countryCode'] ?? null,
        ]);
        return $parts ? implode(', ', $parts) : null;
    }

    private function findDate(array $dateAndTimes, string $type): ?string
    {
        foreach ($dateAndTimes as $entry) {
            if (($entry['type'] ?? '') === $type) {
                return $entry['dateTime'] ?? null;
            }
        }
        return null;
    }

    private function getSheetsService(bool $readOnly = true): \Google\Service\Sheets
    {
        $client = new \Google_Client();
        $client->setApplicationName('FedEx Tracking Sync');
        $client->setScopes($readOnly
            ? [\Google\Service\Sheets::SPREADSHEETS_READONLY]
            : [\Google\Service\Sheets::SPREADSHEETS]);
        $client->setAuthConfig(app_path('google-sheets/credentials.json'));
        return new \Google\Service\Sheets($client);
    }

    private function updateSheetStatuses(array $trackingRowMap): void
    {
        if (empty($trackingRowMap)) return;

        try {
            $records = ShipmentTracking::fedex()
                ->whereIn('tracking_number', array_keys($trackingRowMap))
                ->whereNotNull('status')
                ->where('status', '!=', '')
                ->orderByDesc('status')
                ->get(['tracking_number', 'status', 'status_description', 'scan_events'])
                ->keyBy('tracking_number');

            $sheetData = [];
            foreach ($trackingRowMap as $trackingNo => $rowNumbers) { 
                $rec = $records->get($trackingNo);
                if (!$rec) continue;

                $events      = is_array($rec->scan_events) ? array_values($rec->scan_events) : [];
                $firstEvent  = $events[0] ?? null;
                $rawLocation = is_array($firstEvent) ? ($firstEvent['location'] ?? '') : '';
                $latestDesc  = is_string($rawLocation) ? $rawLocation : '';

                foreach ($rowNumbers as $rowNumber) {
                    $sheetData[] = new \Google\Service\Sheets\ValueRange([
                        'range'  => "EPS!H{$rowNumber}:J{$rowNumber}",
                        'values' => [[(string) $rec->status, (string) ($rec->status_description ?? ''), $latestDesc]],
                    ]);
                }
            }

            if (empty($sheetData)) return;

            $service = $this->getSheetsService(false);
            $body    = new \Google\Service\Sheets\BatchUpdateValuesRequest([
                'valueInputOption' => 'RAW',
                'data'             => $sheetData,
            ]);
            $service->spreadsheets_values->batchUpdate(self::SPREADSHEET_ID, $body);
            $this->info("Updated " . count($sheetData) . " row(s) in Google Sheet columns H-J with status.");
        } catch (\Exception $e) {
            $this->error("Failed to update Google Sheet columns H-J: " . $e->getMessage());
        }
    }

    private function parseScanEvents(array $events): array
    {
        return array_map(fn($e) => [
            'date'        => $e['date'] ?? null,
            'location'    => $this->formatAddress($e['scanLocation'] ?? []),
            'status'      => $e['eventType'] ?? null,
            'description' => $e['eventDescription'] ?? null,
        ], $events);
    }
}
