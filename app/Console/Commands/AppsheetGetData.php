<?php

namespace Vanguard\Console\Commands;

use Illuminate\Console\Command;
use Vanguard\Helpers\GoogleSheetHelper;
use Vanguard\Models\AppsheetToInhouse;
use Vanguard\Models\InhouseOrderItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AppsheetGetData extends Command
{
    protected $signature   = 'appsheettoinhous:updatecron';
    protected $description = 'Sync Google Sheet data to in-house order tracking';

    public function handle(): void
    {
        date_default_timezone_set('Asia/Kolkata');
        $now = now()->toDateTimeString();
        $sheetData        = GoogleSheetHelper::fetchSheetDataAndMatchinDB();
        $sheetDataShipNum = GoogleSheetHelper::fetchSheetDataAndMatchingForShippNumberDB();

        if ($sheetData === 0) {
            $this->handleRefund($now);
            return;
        }

        if (is_array($sheetData)) {
            $this->processMainSheet($sheetData, $now);
        }

        if (!empty($sheetDataShipNum) && is_array($sheetDataShipNum)) {
            $this->processShippedNumbers($sheetDataShipNum, $now);
        } 
        
        $this->info('Item log sync triggered after appsheet update.');
    }

    private function handleRefund(string $now): void
    {
        $order = InhouseOrderItem::select('product_sku', 'order_id', 'product_item_id', 'product_dispatch_date')
            ->where('rr_refund_status', 1)
            ->first();

        if (!$order) {
            return;
        }

        AppsheetToInhouse::create([
            'unique_id'              => 'ANDFS_' . $order->product_item_id,
            'dispatch_date'          => $this->parseDate($order->product_dispatch_date),
            'sub_status_status'      => 'Refunded',
            'given_for'              => 'Refunded',
            'dispatch_status'        => 'Refunded',
            'order_id'               => $order->order_id,
            'item_sku'               => $order->product_sku,
            'updated_at'             => $now,
        ]);
        DB::table('andaaz_order_log')->insert([
            'order_id'        => $order->order_id,
            'sku'             => $order->product_sku,
            'product_item_id' => $order->product_item_id,
            'new_value'       => 'Refunded',
            'old_value'       => '',
            'column_name'     => 'Updated Magento Side',
            'updated_by'      => 'magento',
            'updated_date'    => $now,
        ]);
    }

    private function processMainSheet(array $sheetData, string $now): void
    {
        $rows = array_filter(
            array_slice($sheetData, 1),
            fn($r) => !empty($r[0]) && ($r[1] ?? '') !== 'Unique ID'
        );

        if (empty($rows)) {
            return;
        }
        $productItemIds = array_map(
            fn($r) => str_replace('ANDFS_', '', trim($r[0])),
            $rows
        );

        $orderMap = InhouseOrderItem::select('product_sku', 'order_id', 'product_item_id', 'id')
            ->whereIn('product_item_id', $productItemIds)
            ->get()
            ->keyBy('product_item_id');
        $uniqueIds = array_column(array_values($rows), 0);

        $existingMap = AppsheetToInhouse::whereIn('unique_id', $uniqueIds)
            ->orderByDesc('entity_id')
            ->get()
            ->unique('unique_id')
            ->keyBy('unique_id');
        $toInsert   = [];
        $logEntries = [];
        $holdInserts = [];
        $deferredUpdates = [];

        foreach ($rows as $row) {
            $uniqueId  = $row[0] ?? null;
            $subStatus = $row[8] ?? null;

            if (!$uniqueId || $subStatus === null || $subStatus === '') {
                continue;
            }

            $productItemId = str_replace('ANDFS_', '', trim($uniqueId));
            $orderDetail   = $orderMap->get($productItemId);
            $orderId       = $orderDetail?->order_id;
            $productSku    = $orderDetail?->product_sku;
            $itemId        = $orderDetail?->id; 
            if (!$productSku || !$orderId) {
                continue;
            }

            $existing      = $existingMap->get($uniqueId);
            $dispatchDate  = $this->parseDate(trim($row[1] ?? ''));
            if (!$existing || $existing->sub_status_status !== $subStatus) {
                if ($subStatus === 'Invoiced') {
                    continue;
                }

                $toInsert[] = [
                    'unique_id'              => $uniqueId,
                    'dispatch_date'          => $dispatchDate,
                    'occassion'              => $row[2]  ?? null,
                    'source'                 => $row[3]  ?? null,
                    'expendition_status'     => $row[4]  ?? null,
                    'order_coordinator'      => $row[5]  ?? null,
                    'express_delivery'       => $row[6]  ?? null,
                    'statuslocation'         => $row[7]  ?? null,
                    'sub_status_status'      => $subStatus,
                    'hold_status'            => $row[9]  ?? null,
                    'hold_reason'            => $row[10] ?? null,
                    'check_list_coordinator' => $row[11] ?? null,
                    'given_for'              => $subStatus,
                    'doer_name'              => $row[12] ?? null,
                    'order_id'               => $orderId,
                    'item_sku'               => $productSku,
                    'shipped_number'         => $this->sanitizeShippedNumber($row[13] ?? null),
                    'dispatch_status'        => $row[15] ?? null,
                    'manwear'                => $row[16] ?? null,
                    'updated_at'             => $now,
                ];

                $updatedBy = in_array($subStatus, ['Cancel', 'Canceled From Processing'])
                    ? ($row[18] ?? null)
                    : ($row[12] ?? null);

               /* $logEntries[] = [
                    'order_id'        => $orderId,
                    'sku'             => $productSku,
                    'item_id'         => $itemId,
                    'product_item_id' => $productItemId,
                    'new_value'       => $subStatus,
                    'old_value'       => $existing?->sub_status_status ?? '',
                    'column_name'     => 'Updated sub_status_status',
                    'pending_reason'  => '',
                    'updated_by'      => $updatedBy,
                    'updated_date'    => $now,
                ];*/

                $this->info('Cron executed: ' . $uniqueId);
                continue;
            }
          /*  if ($existing->dispatch_date !== $dispatchDate) {
                $deferredUpdates[] = ['appsheet', $existing->entity_id, ['dispatch_date' => $dispatchDate, 'updated_at' => $now]];
                $logEntries[] = $this->logEntry($orderId, $productSku, $itemId, $productItemId, 'Updated dispatch_date', $dispatchDate, $existing->dispatch_date, $row[5] ?? null, $now);
            }

            $orderCoordinator = $row[5] ?? null;
            if ($existing->order_coordinator !== $orderCoordinator) {
                $deferredUpdates[] = ['appsheet', $existing->entity_id, ['order_coordinator' => $orderCoordinator, 'updated_at' => $now]];
                $logEntries[] = $this->logEntry($orderId, $productSku, $itemId, $productItemId, 'Updated order_coordinator', $orderCoordinator, $existing->order_coordinator, 'Tanushri', $now);
            }

            $doerName = $row[12] ?? null;
            if ($existing->doer_name !== $doerName) {
                $deferredUpdates[] = ['appsheet', $existing->entity_id, ['doer_name' => $doerName, 'updated_at' => $now]];
                $logEntries[] = $this->logEntry($orderId, $productSku, $itemId, $productItemId, 'Updated doer_name', $doerName, $existing->doer_name, $row[5] ?? null, $now);
            }

            $holdStatus = $row[9] ?? null;
            if ($existing->hold_status !== $holdStatus) {
                $deferredUpdates[] = ['appsheet', $existing->entity_id, ['hold_status' => $holdStatus, 'updated_at' => $now]];
                $logEntries[] = $this->logEntry($orderId, $productSku, $itemId, $productItemId, 'Updated hold_status', $holdStatus, $existing->hold_status, $row[5] ?? null, $now);

                if ($holdStatus === 'Unhold') {
                    $holdInserts[] = ['order_id' => $orderId, 'unique_id' => $uniqueId, 'indicate' => 0, 'updated_at' => $now];
                }
            }

            if ($existing->expendition_status !== ($row[4] ?? null)) {
                $deferredUpdates[] = ['appsheet', $existing->entity_id, ['expendition_status' => $row[4], 'updated_at' => $now]];
                $logEntries[] = $this->logEntry($orderId, $productSku, $itemId, $productItemId, 'Updated expendition_status', $row[4] ?? null, $existing->expendition_status, $row[5] ?? null, $now);
            }

            if ($existing->occassion !== ($row[2] ?? null)) {
                $deferredUpdates[] = ['appsheet', $existing->entity_id, ['occassion' => $row[2], 'updated_at' => $now]];
               $logEntries[] = $this->logEntry($orderId, $productSku, $itemId, $productItemId, 'Updated occassion Date', $row[2] ?? null, $existing->occassion, $row[5] ?? null, $now);
            }

            $source = $row[3] ?? null;
            if ($existing->source !== $source) {
                $deferredUpdates[] = ['appsheet', $existing->entity_id, ['source' => $source, 'updated_at' => $now]];
                $logEntries[] = $this->logEntry($orderId, $productSku, $itemId, $productItemId, 'Updated source', $source, $existing->source, $row[12] ?? null, $now);
            }

            $subLocation = $row[7] ?? null;
            if ($existing->statuslocation !== $subLocation) {
                $deferredUpdates[] = ['appsheet', $existing->entity_id, ['statuslocation' => $subLocation, 'updated_at' => $now]];
                $logEntries[] = $this->logEntry($orderId, $productSku, $itemId, $productItemId, 'Updated status Location', $subLocation, $existing->statuslocation, $row[12] ?? null, $now);
            }*/

            $this->info('Cron executed (field updates): ' . $uniqueId);
        }

        DB::transaction(function () use ($toInsert, $logEntries, $holdInserts, $deferredUpdates, $now) {
            foreach (array_chunk($toInsert, 500) as $chunk) {
                DB::table('appsheet_to_inhouse')->insert($chunk);
            }
           /* foreach (array_chunk($logEntries, 500) as $chunk) {
                DB::table('andaaz_order_log')->insert($chunk);
            }*/
            if (!empty($holdInserts)) {
                DB::table('hold_to_unhold_seen')->insert($holdInserts);
            }
            foreach ($deferredUpdates as [$table, $entityId, $data]) {
                DB::table($table === 'appsheet' ? 'appsheet_to_inhouse' : $table)
                    ->where('entity_id', $entityId)
                    ->update($data);
            }
        });
    }

    private function processShippedNumbers(array $sheetData, string $now): void
    {
        $rows = array_filter(
            array_slice($sheetData, 1),
            fn($r) => !empty($r[0]) && ($r[1] ?? '') !== 'unique_id'
        );

        if (empty($rows)) {
            return;
        }

        $uniqueIds = array_column(array_values($rows), 0);
        $existingMap = AppsheetToInhouse::whereIn('unique_id', $uniqueIds)
            ->orderByDesc('entity_id')
            ->get()
            ->unique('unique_id')
            ->keyBy('unique_id');

        DB::transaction(function () use ($rows, $existingMap, $now) {
            foreach ($rows as $row) {
                $uniqueId      = $row[0] ?? null;
                $subStatus     = $row[1] ?? null;
                $shippedNumber = $row[3] ?? null;

                if (!$uniqueId) {
                    continue;
                }

                $existing = $existingMap->get($uniqueId);
                if (!$existing) {
                    continue;
                }

                $updateData = ['updated_at' => $now];

                $shippedNumber = $this->sanitizeShippedNumber($shippedNumber);
                if ($shippedNumber !== null) {
                    $updateData['shipped_number'] = $shippedNumber;
                }

                if ($subStatus !== null) {
                    $updateData['sub_status_status'] = $subStatus;
                }

                if (count($updateData) > 1) { // more than just updated_at
                    AppsheetToInhouse::where('entity_id', $existing->entity_id)
                        ->update($updateData);
                    $this->info('Updated shipped no.: ' . $uniqueId);
                }
            }
        });
    }

    private function parseDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }
        try {
            return Carbon::createFromFormat('d-M-y', trim($date))->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }

    private function logEntry(
        ?string $orderId,
        ?string $sku,
        ?int    $itemId,
        ?string $productItemId,
        string  $column,
        mixed   $newValue,
        mixed   $oldValue,
        ?string $updatedBy,
        string  $now
    ): array {
        return [
            'order_id'        => $orderId,
            'sku'             => $sku,
            'item_id'         => $itemId,
            'product_item_id' => $productItemId,
            'new_value'       => $newValue,
            'old_value'       => $oldValue,
            'column_name'     => $column,
            'pending_reason'  => '',
            'updated_by'      => $updatedBy,
            'updated_date'    => $now,
        ];
    }

    private function sanitizeShippedNumber(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }
        if (str_starts_with(trim($value), 'IFERROR(')
            || str_starts_with(trim($value), '=')
            || str_contains($value, 'ARRAYFORMULA')
            || str_contains($value, 'IMPORTRANGE')
            || str_contains($value, 'VLOOKUP')
        ) {
            return null;
        }

        return $value;
    }
}