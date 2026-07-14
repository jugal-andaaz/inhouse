<?php

namespace Vanguard\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Vanguard\Mail\EpsplTrackingUpdate;
use Vanguard\Models\ShipmentTracking;

class FetchEpsplTracking extends Command
{
    protected $signature = 'epspl:fetch-tracking';
    protected $description = 'Fetch shipment tracking details from EPSPL API and store in shipment_tracking table';

    private const API_URL    = 'https://api.epspl.co.in/api/Client/TrackingDetail';
    private const TOKEN      = 'kdsfo320j34373@!32*lkwew';
    private const USER_ID    = 'S040';
    private const PASSWORD   = 'S040@234';
    private const BATCH_SIZE = 500;    
    private array $pendingEmails = [];

    public function handle(): int
    {
        $this->info('EPSPL Tracking Sync Started : ' . Carbon::now());

        $awbNumbers = $this->getAwbNumbers();

        if (!empty($awbNumbers)) {
            $awbRowMap = [];
            foreach ($awbNumbers as $item) {
                $awbRowMap[$item['awb_no']][] = $item['sheet_row'];
            }

            foreach ($awbNumbers as $item) {
                $awbNo         = trim($item['awb_no'] ?? '');
                $customerEmail = $item['customer_email'] ?: null;
                $uniqueId      = $item['unique_id'] ?: null;
                $productSKU    = $item['product_sku'] ?: null;
                $orderId       = $item['order_id'] ?: null;

                if (!empty($awbNo)) {
                    $record = ShipmentTracking::updateOrCreate(
                        ['carrier' => 'EPS', 'tracking_number' => $awbNo, 'unique_id' => $uniqueId],
                        ['customer_email' => $customerEmail,
                         'product_sku' => $productSKU, 'order_id' => $orderId]
                    );

                    if (!empty($record->tracking_history)) {
                        $cleaned = $this->deduplicateHistory($record->tracking_history);
                        if (count($cleaned) !== count($record->tracking_history)) {
                            $record->update(['tracking_history' => $cleaned]);
                        }
                    }

                    if ($orderId) {
                        Cache::forget('shipment_tracking_eps_' . $orderId);
                    }
                }
            }

            $awbNoList = array_column($awbNumbers, 'awb_no');

            $records = ShipmentTracking::eps()
                ->whereIn('tracking_number', $awbNoList)
                ->where(function ($q) {
                    $q->whereNull('status')
                      ->orWhere('status', '')
                      ->orWhereNotIn('status', ['DELIVERED', 'DL']);
                })
                ->orderBy('last_fetched_at', 'asc')
                ->limit(self::BATCH_SIZE)
                ->get();

            $this->info("Processing " . $records->count() . " AWB(s) this run (batch limit: " . self::BATCH_SIZE . ").");
            $this->processRecords($records);
            $this->updateSheetStatuses($awbRowMap);
            $this->info('EPSPL Tracking Sync Completed : ' . Carbon::now());
            return 0;
        }

        $records = ShipmentTracking::eps()->where(function ($query) {
            $query->whereNull('status')
                  ->orWhere('status', '')
                  ->orWhereNotIn('status', ['DELIVERED', 'DL']);
        })->get();

        if ($records->isEmpty()) {
            $this->info('No pending AWB numbers to track.');
            return 0;
        }

        $this->info("Found {$records->count()} AWB(s) to process.");
        $this->processRecords($records);
        $this->info('EPSPL Tracking Sync Completed : ' . Carbon::now());
        return 0;
    }

    private function processRecords($records): void
    {
        $recordsByAwb = $records->groupBy('tracking_number');
        foreach ($recordsByAwb as $awbNo => $awbRecords) {
            $trackData = $this->fetchTrackData($awbNo);
            if ($trackData === null) continue;

            foreach ($awbRecords as $record) {
                $this->processAwb($record, $trackData);
            }
        }
        foreach ($this->pendingEmails as $emailData) {
            $this->sendTrackingEmail($emailData['record'], $emailData['delivered'], $emailData['total']);
        }
    }

    private function getSheetsService(bool $readOnly = true): \Google\Service\Sheets
    {
        $client = new \Google_Client();
        $client->setApplicationName('EPSPL Tracking Sync');
        $client->setScopes($readOnly
            ? [\Google\Service\Sheets::SPREADSHEETS_READONLY]
            : [\Google\Service\Sheets::SPREADSHEETS]);
        $client->setAuthConfig(app_path('google-sheets/credentials.json'));
        return new \Google\Service\Sheets($client);
    }

    private function getAwbNumbers(): array
    {
        try {
            $service = $this->getSheetsService(true);

            $response = $service->spreadsheets_values->get(
                '1DqOS8jEMMunm8YqrAURJijMXSsXxWdtLmngAyyf73hU',
                'EPS!A:G'
            );

            $rows   = $response->getValues() ?? [];
            $result = [];

            foreach ($rows as $index => $row) {
                if ($index <= 1) continue; // skip notice + header rows

                $awbNo         = trim($row[0] ?? ''); // Column A
                $carrier       = trim($row[1] ?? ''); // Column B
                $orderId       = trim($row[2] ?? ''); // Column C
                $uniqueId      = trim($row[3] ?? ''); // Column D
                $productSKU    = trim($row[4] ?? ''); // Column E
                $customerEmail = trim($row[6] ?? ''); // Column G

                if (strtoupper($carrier) === 'EPS' && !empty($awbNo)) {
                    $result[] = [
                        'awb_no'         => $awbNo,
                        'customer_email' => $customerEmail ?: null,
                        'unique_id'      => $uniqueId,
                        'product_sku'    => $productSKU,
                        'order_id'       => $orderId ?: null,
                        'sheet_row'      => $index + 1, // 1-indexed Google Sheet row number
                    ];
                }
            }

            $this->info("Found " . count($result) . " EPS AWB(s) from Google Sheet.");

            return $result;

        } catch (\Exception $e) {
            $this->error("Google Sheet Error: " . $e->getMessage());
            return [];
        }
    }

    private function fetchTrackData(string $awbNo): ?array
    {
        $this->info("Fetching tracking for AWB: {$awbNo}");

        try {
            $response = Http::timeout(15)->get(self::API_URL, [
                'Token'    => self::TOKEN,
                'AwbNo'    => $awbNo,
                'UserID'   => self::USER_ID,
                'Password' => self::PASSWORD,
                'Type'     => 'json',
            ]);

            if (!$response->successful()) {
                $this->error("API request failed for AWB {$awbNo}: HTTP {$response->status()}");
                return null;
            }

            $trackDetails = $response->json('TrackDetail');

            if (empty($trackDetails[0])) {
                $this->warn("Empty response for AWB {$awbNo}");
                return null;
            }

            $detail = $trackDetails[0];

            if (isset($detail['Message'])) {
                $this->warn("AWB {$awbNo}: {$detail['Message']}");
                return null;
            }

            return $detail;

        } catch (\Exception $e) {
            $this->error("Exception for AWB {$awbNo}: {$e->getMessage()}");
            return null;
        }
    }

    private function processAwb(ShipmentTracking $record, array $detail): void
    {
        try {
            $oldStatus  = $record->status;
            $newHistory = $this->deduplicateHistory($detail['TrackingHistory'] ?? []);
            $newStatus  = $detail['Status'] ?? null;

            $record->update([
                'reference_no'     => $detail['ReferenceNo'] ?? null,
                'booking_date'     => $detail['BookingDate'] ?? null,
                'service_type'     => $detail['ServiceType'] ?? null,
                'package_type'     => $detail['PackageType'] ?? null,
                'origin'           => $detail['Origin'] ?? null,
                'destination'      => $detail['Destination'] ?? null,
                'status'           => $newStatus,
                'consignee'        => $detail['Consignee'] ?? null,
                'packages'         => $detail['Packages'] ?? null,
                'tracking_no'      => $detail['TrackingNo'] ?? null,
                'tracking_history' => $newHistory,
                'status_description' => $detail['StatusDescription'] ?? null,
                'tracking_history'   => $newHistory,
                'error_message'    => null,
                'last_fetched_at'  => now(),
            ]);

            $this->clearCache($record);
            $this->info("AWB {$record->tracking_number} ({$record->unique_id}) updated. Status: " . ($newStatus ?? 'N/A'));

            $isNowDelivered      = $newStatus === 'DELIVERED';
            $wasAlreadyDelivered = in_array($oldStatus, ['DELIVERED', 'DL']);

            if ($isNowDelivered && !$wasAlreadyDelivered && !empty($record->customer_email) && !empty($record->order_id)) {
                $progress = $this->getDeliveryProgress($record->order_id);
                $orderId  = $record->order_id;

                if (!isset($this->pendingEmails[$orderId])) {
                    $this->pendingEmails[$orderId] = [
                        'record'    => $record->fresh(),
                        'delivered' => $progress['delivered'],
                        'total'     => $progress['total'],
                    ];
                } else {
                    // Update counts; keep whichever record has a valid customer_email
                    $this->pendingEmails[$orderId]['delivered'] = $progress['delivered'];
                    $this->pendingEmails[$orderId]['total']     = $progress['total'];
                    if (empty($this->pendingEmails[$orderId]['record']->customer_email) && !empty($record->customer_email)) {
                        $this->pendingEmails[$orderId]['record'] = $record->fresh();
                    }
                }
            }

        } catch (\Exception $e) {
            $this->error("Exception processing AWB {$record->tracking_number}: {$e->getMessage()}");
            $record->update(['error_message' => substr($e->getMessage(), 0, 255), 'last_fetched_at' => now()]);
            $this->clearCache($record);
        }
    }

    private function sendTrackingEmail(ShipmentTracking $record, int $deliveredCount, int $totalItems): void
    {
        try {
            Mail::to($record->customer_email)
                ->send(new EpsplTrackingUpdate($record, true, $deliveredCount, $totalItems));
                
            $this->info("Email sent to {$record->customer_email} for AWB {$record->tracking_number} ({$deliveredCount}/{$totalItems} items delivered)");
        } catch (\Exception $e) {
            $this->error("Email failed for AWB {$record->tracking_number}: {$e->getMessage()}");
        }
    }

    private function getDeliveryProgress(string $orderId): array
    {
        // Use only EPS shipment rows as the source of truth
        $total = ShipmentTracking::eps()
            ->where('order_id', $orderId)
            ->count();

        $delivered = ShipmentTracking::eps()
            ->where('order_id', $orderId)
            ->whereIn('status', ['DELIVERED', 'DL'])
            ->count();

        return [
            'delivered' => max(1, $delivered),
            'total'     => max(1, $total),
        ];
    }

    private function clearCache(ShipmentTracking $record): void
    {
        if (!empty($record->order_id)) {
            Cache::forget('shipment_tracking_eps_' . $record->order_id);
        }
    }

    private function updateSheetStatuses(array $awbRowMap): void
    {
        if (empty($awbRowMap)) return;
        try {
            $records = ShipmentTracking::eps()
                ->whereIn('tracking_number', array_keys($awbRowMap))
                ->whereNotNull('status')
                ->where('status', '!=', '')
                ->get(['tracking_number', 'status','status_description','tracking_history'])
                ->keyBy('tracking_number');

            $sheetData = [];
            foreach ($awbRowMap as $awbNo => $rowNumbers) {
                $record = $records->get($awbNo);
                if (!$record) continue;

                $history    = is_array($record->tracking_history) ? $record->tracking_history : [];
                $latest     = $history[0] ?? null;
                $latestDesc = $latest
                    ? ($latest['MovementDetail'] ?? $latest['Activity'] ?? $latest['Remark'] ?? $latest['Status'] ?? '')
                    : '';

                foreach ($rowNumbers as $rowNumber) {
                    $sheetData[] = new \Google\Service\Sheets\ValueRange([
                        'range'  => "EPS!H{$rowNumber}:J{$rowNumber}",
                        'values' => [[
                            $record->status ?? '',
                            $record->status_description ?? '',
                            $latestDesc,
                        ]],
                    ]);
                }
            }

            if (empty($sheetData)) return;

            $service = $this->getSheetsService(false);
            $body    = new \Google\Service\Sheets\BatchUpdateValuesRequest([
                'valueInputOption' => 'RAW',
                'data'             => $sheetData,
            ]);
            $service->spreadsheets_values->batchUpdate(
                '1DqOS8jEMMunm8YqrAURJijMXSsXxWdtLmngAyyf73hU',
                $body
            );
            $this->info("Updated " . count($sheetData) . " row(s) in Google Sheet column H with status.");
        } catch (\Exception $e) {
            $this->error("Failed to update Google Sheet column H: " . $e->getMessage());
        }
    }

    private function deduplicateHistory(array $history): array
    {
        $seen    = [];
        $deduped = array_values(array_filter($history, function ($event) use (&$seen) {
            $date     = substr(trim($event['Date'] ?? ''), 0, 14);
            $time     = trim($event['Time'] ?? '');
            $location = trim($event['Location'] ?? '');
            $activity = trim(
                $event['MovementDetail'] ?? $event['Activity'] ??
                $event['Remark'] ?? $event['Status'] ?? ''
            );
            $key = $date . '|' . $time . '|' . $location . '|' . $activity;

            if (isset($seen[$key])) return false;
            $seen[$key] = true;
            return true;
        }));

        usort($deduped, function ($a, $b) {
            $dtA = strtotime(trim($a['Date'] ?? '') . ' ' . trim($a['Time'] ?? ''));
            $dtB = strtotime(trim($b['Date'] ?? '') . ' ' . trim($b['Time'] ?? ''));
            return $dtB <=> $dtA;
        });

        return $deduped;
    }
}
