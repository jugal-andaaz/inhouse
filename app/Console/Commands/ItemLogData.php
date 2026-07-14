<?php
namespace Vanguard\Console\Commands;

use Illuminate\Console\Command;
use \Vanguard\Models\AppsheetToInhouse;
use \Vanguard\Models\NewItemLogs;
use \Vanguard\Models\OrderMsrmtAppsheetSQL;
use \Vanguard\Models\FabricAgainstOrderSQL;
use \Vanguard\Models\CuttingMasterSQL;
use \Vanguard\Models\StitchingToPpSQL;
use \Vanguard\Models\NewItemLogsTracker;
use \Vanguard\Models\AndaazInhouseOrderItem;
use \Vanguard\Models\ShipmentTracking;
use Carbon\Carbon;
use \Vanguard\Models\Order;

class ItemLogData extends Command
{
    protected $signature = 'itemlogtoinhous:newitemlogcron';
    protected $description = 'Item log Table Updated';

    private const MEN_PRODUCT_TYPES = [9389, 9396, 9406, 9397, 9407, 10162, 10164];

    public function handle()
    {
        $trackingOrders = OrderMsrmtAppsheetSQL::where('order_item_status', 'Invoiced')
                        ->orderBy('entity_id', 'DESC')
                        ->get();

        $allocatedToMap = [];
        $bagPrepMap     = [];
        foreach ($trackingOrders as $o) {
            if (!empty($o->unique_id)) {
                $allocatedToMap[$o->unique_id] = $o->allocated_to;
                $bagPrepMap[$o->unique_id]     = $o->bag_prep_name;
            }
        }

        $invoicedUniqueIds = $trackingOrders->pluck('unique_id')->filter()->unique()->values()->toArray();

        $shippedNumberMap = AppsheetToInhouse::whereIn('unique_id', $invoicedUniqueIds)
            ->pluck('shipped_number', 'unique_id');

        $existingLogsMap = NewItemLogs::whereIn('unique_id', $invoicedUniqueIds)
            ->orderByDesc('entity_id')
            ->get()
            ->groupBy('unique_id')
            ->map(fn($logs) => $logs->first());

        $productItemIds = $trackingOrders
            ->map(fn($o) => preg_replace('/^ANDFS_/i', '', $o->unique_id))
            ->filter()->unique()->values()->all();

        $orderItemsMap = AndaazInhouseOrderItem::whereIn('product_item_id', $productItemIds)
            ->get(['product_item_id', 'product_dispatch_date', 'product_type'])
            ->keyBy('product_item_id');

        $incrementIds      = $trackingOrders->pluck('increment_id')->filter()->unique()->values()->all();
        $shippingMethodMap = Order::whereIn('increment_id', $incrementIds)
            ->pluck('shipping_method', 'increment_id');

        $allExistingLogs = NewItemLogs::whereIn('unique_id', $invoicedUniqueIds)
            ->get(['unique_id', 'sub_loaction', 'location', 'type', 'doername', 'updated_by']);

        $existingLogKeys = [];
        foreach ($allExistingLogs as $log) {
            $u  = $log->unique_id;
            $s  = (string)($log->sub_loaction ?? '');
            $l  = (string)($log->location    ?? '');
            $t  = (string)($log->type        ?? '');
            $d  = (string)($log->doername    ?? '');
            $ub = (string)($log->updated_by  ?? '');
            $existingLogKeys["$u|S:$s|T:$t"] = true;
            $existingLogKeys["$u|S:$s"]       = true;
            $existingLogKeys["$u|L:$l|T:$t"] = true;
            $existingLogKeys["$u|D:$d|L:$l"] = true;
            $existingLogKeys["$u|D:$d|UB:$ub|L:$l|T:$t"] = true;
        }
        unset($allExistingLogs);

        $registerLog = function(array $d) use (&$existingLogKeys): void {
            $u  = $d['unique_id']    ?? '';
            $s  = $d['sub_loaction'] ?? '';
            $l  = $d['location']     ?? '';
            $t  = $d['type']         ?? '';
            $dv = $d['doername']     ?? '';
            $ub = $d['updated_by']   ?? '';
            $existingLogKeys["$u|S:$s|T:$t"] = true;
            $existingLogKeys["$u|S:$s"]       = true;
            $existingLogKeys["$u|L:$l|T:$t"] = true;
            $existingLogKeys["$u|D:$dv|L:$l"] = true;
            $existingLogKeys["$u|D:$dv|UB:$ub|L:$l|T:$t"] = true;
        };

        foreach ($trackingOrders as $trackingOrder) {

            if (empty($trackingOrder->unique_id)) {
                continue;
            }

            $existNewItemLog = $existingLogsMap[$trackingOrder->unique_id] ?? null;
            $shippedNumber   = $shippedNumberMap[$trackingOrder->unique_id] ?? null;

            if ($existNewItemLog && $shippedNumber && $existNewItemLog->shipped_number !== $shippedNumber) {
                $existNewItemLog->update(['shipped_number' => $shippedNumber]);

                NewItemLogsTracker::where('unique_id', $trackingOrder->unique_id)
                    ->where(function ($query) {
                        $query->whereNull('shipped_number')
                              ->orWhere('shipped_number', '');
                    })
                    ->update([
                        'shipped_number' => $trackingOrder->order_item_status 
                    ]);

                $this->info("Shipped_number synced for: {$trackingOrder->unique_id} → {$shippedNumber}");
            } 

            // CASE 1: No record found → create new
            if (!$existNewItemLog) {
                NewItemLogs::create([
                    'unique_id'       => $trackingOrder->unique_id,
                    'type'            => 'New',
                    'andaaz_order_id' => $trackingOrder->increment_id,
                    'product_sku'     => $trackingOrder->product_sku,
                    'updated_by'      => $trackingOrder->expedite_marked_by,
                    'source'          => $trackingOrder->allocated_to,
                    'shipped_number'  => $trackingOrder->order_item_status,
                    'updated_at'      => $trackingOrder->updated_at ?? now(),
                ]);
                NewItemLogsTracker::where('unique_id', $trackingOrder->unique_id)
                        ->update(['shipped_number' => $trackingOrder->order_item_status]);            

                $existingLogsMap[$trackingOrder->unique_id] = (object)['shipped_number' => $trackingOrder->order_item_status];

                $this->info("Inserted NEW record: {$trackingOrder->unique_id}");
            }

            $productItemId = preg_replace('/^ANDFS_/i', '', $trackingOrder->unique_id);

            $orderItem    = $orderItemsMap[$productItemId] ?? null;
            $dispatchDate = $orderItem?->product_dispatch_date;
            $productType  = $orderItem?->product_type;
            /*$productType  = in_array($productType, self::MEN_PRODUCT_TYPES) ? 'manwear' : '';*/
            $productType = (in_array($productType, self::MEN_PRODUCT_TYPES) || strpos($trackingOrder->product_sku, 'JUTM') === 0 || strpos($trackingOrder->product_sku, 'JUTW') === 0 ) ? 'manwear' : '';

            $orderShippingMethod = $shippingMethodMap[$trackingOrder->increment_id] ?? '';
            $expressDelivery     = $orderShippingMethod === 'flatrate_flatrate' ? 'Express_Shipping' : '';

            NewItemLogsTracker::updateOrCreate(
                ['unique_id' => $trackingOrder->unique_id],
                [
                    'andaaz_order_id'  => $trackingOrder->increment_id,
                    'order_id'         => $trackingOrder->increment_id,
                    'item_sku'         => $trackingOrder->product_sku,
                    'dispatch_date'    => $this->parseDate($dispatchDate),
                    'manwear'          => $productType,
                    'source'           => $trackingOrder->allocated_to,
                    'dispatch_status'  => $trackingOrder->order_item_status,
                    'shipped_number'  => $trackingOrder->order_item_status ?? 'Invoiced',
                    'updated_at'       => $trackingOrder->updated_at ?? now(),
                    'express_delivery' => $expressDelivery,
                ]
            );

            // CASE A: location/expendition changed
            if (!empty($trackingOrder->expendition) && !empty($trackingOrder->expedite_marked_by)) {
                $alreadyExists = isset($existingLogKeys["{$trackingOrder->unique_id}|L:expendite_status|T:Info"]);

                if (!$alreadyExists) {
                    $logData = [
                        'unique_id'       => $trackingOrder->unique_id,
                        'andaaz_order_id' => $trackingOrder->increment_id,
                        'product_sku'     => $trackingOrder->product_sku,
                        'updated_by'      => $trackingOrder->expedite_marked_by,
                        'location'        => 'expendite_status',
                        'sub_loaction'    => $trackingOrder->expendition,
                        'source'          => $trackingOrder->allocated_to,
                        'type'            => 'Info',
                        'shipped_number'  => $trackingOrder->order_item_status,
                        'updated_at'      => $trackingOrder->updated_at ?? now(),
                    ];
                    NewItemLogs::create($logData);
                    $registerLog($logData); 

                    NewItemLogsTracker::where('unique_id', $trackingOrder->unique_id)
                        ->update([
                            'expendition_status' => $trackingOrder->expendition,
                            'manwear'            => $productType,
                            'dispatch_date'      => $this->parseDate($dispatchDate),
                        ]);

                    $this->info("Updated location-expendition for: {$trackingOrder->unique_id}");
                }
            }

            // CASE A-1: dispatch_date changed
            if (!empty($trackingOrder->dispatch_date)) {
                $alreadyExists = isset($existingLogKeys["{$trackingOrder->unique_id}|S:{$trackingOrder->dispatch_date}|T:Info"]);

                if (!$alreadyExists) {
                    $logData = [
                        'unique_id'       => $trackingOrder->unique_id,
                        'andaaz_order_id' => $trackingOrder->increment_id,
                        'product_sku'     => $trackingOrder->product_sku,
                        'updated_by'      => $trackingOrder->expedite_marked_by,
                        'location'        => 'dispatch_status',
                        'sub_loaction'    => $trackingOrder->dispatch_date,
                        'source'          => $trackingOrder->allocated_to,
                        'type'            => 'Info',
                        'shipped_number'  => $trackingOrder->order_item_status,
                        'updated_at'      => $trackingOrder->updated_at ?? now(),
                    ];
                    NewItemLogs::create($logData);
                    $registerLog($logData);

                    NewItemLogsTracker::where('unique_id', $trackingOrder->unique_id)
                        ->update([
                            'revised_dispatch_date' => $this->parseDate($trackingOrder->dispatch_date),
                        ]);

                    $this->info("Updated dispatch_date for: {$trackingOrder->unique_id}");
                }
            }

            // CASE A-2: occation changed
            if (!empty($trackingOrder->occation)) {
                $alreadyExists = isset($existingLogKeys["{$trackingOrder->unique_id}|S:{$trackingOrder->occation}|T:Info"]);

                if (!$alreadyExists) {
                    $logData = [
                        'unique_id'       => $trackingOrder->unique_id,
                        'andaaz_order_id' => $trackingOrder->increment_id,
                        'product_sku'     => $trackingOrder->product_sku,
                        'updated_by'      => $trackingOrder->expedite_marked_by,
                        'location'        => 'occation_status',
                        'sub_loaction'    => $trackingOrder->occation,
                        'source'          => $trackingOrder->allocated_to,
                        'type'            => 'Info',
                        'shipped_number'  => $shippedNumber ?? $trackingOrder->order_item_status,
                        'updated_at'      => $trackingOrder->updated_at ?? now(),
                    ];
                    NewItemLogs::create($logData);
                    $registerLog($logData);

                    NewItemLogsTracker::where('unique_id', $trackingOrder->unique_id)
                        ->update([
                            'occassion' => $this->parseDate($trackingOrder->occation),
                        ]);

                    $this->info("Updated occation for: {$trackingOrder->unique_id}");
                }
            }

            // CASE B: Source Allocation changed
            if ($existNewItemLog && !empty($trackingOrder->allocated_to)) {
                $doername          = null;
                $sub_loaction      = null;
                $next_sub_location = null;

                if ($trackingOrder->allocated_to === 'BPU') {
                    $doername          = 'Prabir Mukherjee';
                    $sub_loaction      = 'Fabric_Suggestion';
                    $next_sub_location = 'Fabric_Average';
                } elseif ($trackingOrder->allocated_to === 'Vendor') {
                    $doername          = 'KISHOR LONDHE';
                    $sub_loaction      = 'Order_to_Vendor';
                    $next_sub_location = 'Receiving at surat';
                } elseif ($trackingOrder->allocated_to === 'Warehouse') {
                    $doername          = 'Warehouse';
                    $sub_loaction      = 'Warehouse';
                    $next_sub_location = 'Warehouse';
                }

                if ($doername && $sub_loaction) {
                    $alreadyExists = isset($existingLogKeys["{$trackingOrder->unique_id}|S:{$sub_loaction}|T:Flow"]);

                    if (!$alreadyExists) {
                        $logData = [
                            'unique_id'         => $trackingOrder->unique_id,
                            'andaaz_order_id'   => $trackingOrder->increment_id,
                            'product_sku'       => $trackingOrder->product_sku,
                            'updated_by'        => $trackingOrder->allocated_by,
                            'doername'          => $doername,
                            'sub_loaction'      => $sub_loaction,
                            'next_sub_location' => $next_sub_location,
                            'location'          => 'Source_Allocation',
                            'source'            => $trackingOrder->allocated_to,
                            'type'              => 'Flow',
                            'shipped_number'    => $trackingOrder->order_item_status,
                            'updated_at'        => $trackingOrder->actual_allocation ?? now(),
                        ];
                        NewItemLogs::create($logData);
                        $registerLog($logData);

                        NewItemLogsTracker::where('unique_id', $trackingOrder->unique_id)
                            ->update([
                                'source'      => $trackingOrder->allocated_to,
                                'statuslocation' => $sub_loaction,
                                'doer_name'   => $doername,
                            ]);

                        $this->info("Updated Source → allocated_to for: {$trackingOrder->unique_id}");
                    }
                }
            }

            // CASE C: Bag Allocation doername changed
            if (!empty($trackingOrder->bag_prep_name) && !empty($trackingOrder->bag_assigned_by)) {
                $alreadyExists = isset($existingLogKeys["{$trackingOrder->unique_id}|D:{$trackingOrder->bag_prep_name}|L:bag_assigned"]);

                if (!$alreadyExists) {
                    $logData = [
                        'unique_id'       => $trackingOrder->unique_id,
                        'andaaz_order_id' => $trackingOrder->increment_id,
                        'product_sku'     => $trackingOrder->product_sku,
                        'updated_by'      => $trackingOrder->bag_assigned_by,
                        'doername'        => $trackingOrder->bag_prep_name,
                        'location'        => 'bag_assigned',
                        'source'          => $trackingOrder->allocated_to,
                        'type'            => 'Info',
                        'shipped_number'  => $trackingOrder->order_item_status,
                        'updated_at'      => $trackingOrder->updated_at ?? now(),
                    ];
                    NewItemLogs::create($logData);
                    $registerLog($logData);

                    //For Update -> Doer Name = $trackingOrder->bag_prep_name
                    NewItemLogsTracker::where('unique_id', $trackingOrder->unique_id)
                        ->update([
                            'doer_name' => $trackingOrder->bag_prep_name,
                        ]);

                    $this->info("Updated doername (bag_prep_name) for: {$trackingOrder->unique_id}");
                }

                // CASE D: Coordinator name changed
                if (!empty($trackingOrder->coordinator_name)) {
                    $_coord   = $trackingOrder->coordinator_name;
                    $_coordBy = $trackingOrder->coordinator_allc_by;
                    $alreadyExists = isset($existingLogKeys["{$trackingOrder->unique_id}|D:{$_coord}|UB:{$_coordBy}|L:coordinator_assigned|T:Info"]);

                    if (!$alreadyExists) {
                        $logData = [
                            'unique_id'       => $trackingOrder->unique_id,
                            'andaaz_order_id' => $trackingOrder->increment_id,
                            'product_sku'     => $trackingOrder->product_sku,
                            'updated_by'      => $trackingOrder->coordinator_allc_by,
                            'doername'        => $trackingOrder->coordinator_name,
                            'location'        => 'coordinator_assigned',
                            'source'          => $trackingOrder->allocated_to,
                            'type'            => 'Info',
                            'shipped_number'  => $trackingOrder->order_item_status,
                            'updated_at'      => $trackingOrder->updated_at ?? now(),
                        ];
                        NewItemLogs::create($logData);
                        $registerLog($logData);

                        NewItemLogsTracker::where('unique_id', $trackingOrder->unique_id)
                            ->update([
                                'order_coordinator' => $trackingOrder->coordinator_name,
                            ]);

                        $this->info("Updated coordinator_name for: {$trackingOrder->unique_id}");
                    }
                }
            }

            // CASE E: Hold ticket_type changed  
            if (!empty($trackingOrder->ticket_type)) {
                $alreadyExists = isset($existingLogKeys["{$trackingOrder->unique_id}|S:{$trackingOrder->ticket_type}|T:Hold/Unhold"]);

                if (!$alreadyExists) {
                    $logData = [
                        'unique_id'         => $trackingOrder->unique_id,
                        'andaaz_order_id'   => $trackingOrder->increment_id,
                        'product_sku'       => $trackingOrder->product_sku,
                        'updated_by'        => $trackingOrder->coordinator_name,
                        'doername'          => $trackingOrder->current_ticket_status,
                        'location'          => $trackingOrder->location_to_hold,
                        'sub_loaction'      => $trackingOrder->ticket_type,
                        'next_sub_location' => $trackingOrder->before_after_eid,
                        'source'            => $trackingOrder->allocated_to,
                        'type'              => 'Hold/Unhold',
                        'shipped_number'    => $trackingOrder->order_item_status,
                        'updated_at'        => $trackingOrder->last_action_date_oc ?? now(),
                    ];
                    NewItemLogs::create($logData);
                    $registerLog($logData);

                    NewItemLogsTracker::where('unique_id', $trackingOrder->unique_id)
                        ->update([
                            'hold_status' => $trackingOrder->location_to_hold,
                            'hold_reason' => $trackingOrder->current_ticket_status,
                        ]);

                    $this->info("Updated HOLD->ticket_type for: {$trackingOrder->unique_id}");
                }
            }
        }

        // Fabric Against Orders 
        $fabricAgainstOrders = FabricAgainstOrderSQL::whereIn('unique_id', $invoicedUniqueIds)
                        ->orderBy('id', 'DESC')
                        ->get();

        foreach ($fabricAgainstOrders as $fabricAgainstOrder) {
            if (empty($fabricAgainstOrder->unique_id)) {
                continue;
            }

            $uid             = $fabricAgainstOrder->unique_id;
            $loopAllocatedTo = $allocatedToMap[$uid] ?? '';
            $loopBagPrepName = $bagPrepMap[$uid] ?? '';

            $existNewItemLog = $existingLogsMap[$uid] ?? null;
            $shippedNumber   = $shippedNumberMap[$uid] ?? null;

            if (!$existNewItemLog) {
                NewItemLogs::create([
                    'unique_id'       => $uid,
                    'type'            => 'New',
                    'andaaz_order_id' => $fabricAgainstOrder->increment_id,
                    'product_sku'     => $fabricAgainstOrder->product_sku,
                    'updated_by'      => '--',
                    'doername'        => '--',
                    'location'        => '--',
                ]);
                $existingLogsMap[$uid] = (object)['shipped_number' => null];
                $this->info("Inserted NEW fabricAgainstOrder for: {$uid}");
            }

            // CASE F: doername/suggested_by changed
            if (!empty($fabricAgainstOrder->suggested_by)) {
                $alreadyExists = isset($existingLogKeys["$uid|S:Average_Pending|T:Flow"]);

                if (!$alreadyExists) {
                    $logData = [
                        'unique_id'         => $uid,
                        'andaaz_order_id'   => $fabricAgainstOrder->increment_id,
                        'product_sku'       => $fabricAgainstOrder->product_sku,
                        'updated_by'        => $fabricAgainstOrder->suggested_by,
                        'doername'          => $fabricAgainstOrder->suggested_by,
                        'location'          => 'BPU',
                        'sub_loaction'      => 'Average_Pending',
                        'next_sub_location' => 'Bag_Preparation',
                        'source'            => $loopAllocatedTo,
                        'shipped_number'    => $shippedNumber,
                        'type'              => 'Flow',
                        'updated_at'        => $fabricAgainstOrder->timestamp ?? now(),
                    ];
                    NewItemLogs::create($logData);
                    $registerLog($logData);

                    NewItemLogsTracker::where('unique_id', $uid)
                        ->update([
                            'source'            => $loopAllocatedTo,
                            'statuslocation'    => 'BPU',
                            'sub_status_status' => 'Average_Pending',
                            'doer_name'         => $fabricAgainstOrder->suggested_by,
                        ]);

                    $this->info("Updated fabricAgainstOrder - Fabric_Suggestion for: {$uid}");
                }
            }

            // CASE H: Dye
            if (!empty($fabricAgainstOrder->qty_given_by) && $fabricAgainstOrder->given_for_dye == 'Yes') {
                $alreadyExists = isset($existingLogKeys["$uid|S:Given_For_Dye|T:Flow"]);

                if (!$alreadyExists) {
                    $logData = [
                        'unique_id'         => $uid,
                        'andaaz_order_id'   => $fabricAgainstOrder->increment_id,
                        'product_sku'       => $fabricAgainstOrder->product_sku,
                        'updated_by'        => $fabricAgainstOrder->qty_given_by,
                        'doername'          => $loopBagPrepName,
                        'location'          => 'BPU',
                        'sub_loaction'      => 'Given_For_Dye',
                        'next_sub_location' => 'Bag_Preparation',
                        'source'            => $loopAllocatedTo,
                        'shipped_number'    => $shippedNumber,
                        'type'              => 'Flow',
                        'updated_at'        => $fabricAgainstOrder->timestamp_gdye ?? now(),
                    ];
                    NewItemLogs::create($logData);
                    $registerLog($logData);

                    NewItemLogsTracker::where('unique_id', $uid)
                        ->update([
                            'source'            => $loopAllocatedTo,
                            'statuslocation'    => 'BPU',
                            'sub_status_status' => 'Given_For_Dye',
                            'doer_name'         => $loopBagPrepName,
                        ]);

                    $this->info("Updated fabricAgainstOrder - Dye for: {$uid}");
                }
            }

            // CASE I: Dye_Status
            if (!empty($fabricAgainstOrder->approved_by) && !empty($existNewItemLog->dyer_name)) {
                $alreadyExists = isset($existingLogKeys["$uid|S:Dye_Completion|T:Flow"]);

                if (!$alreadyExists) {
                    $logData = [
                        'unique_id'         => $uid,
                        'andaaz_order_id'   => $fabricAgainstOrder->increment_id,
                        'product_sku'       => $fabricAgainstOrder->product_sku,
                        'updated_by'        => $fabricAgainstOrder->dyer_name,
                        'doername'          => $loopBagPrepName,
                        'location'          => 'BPU',
                        'sub_loaction'      => 'Dye_Completion',
                        'next_sub_location' => 'Bag_Preparation',
                        'shipped_number'    => $shippedNumber,
                        'source'            => $loopAllocatedTo,
                        'type'              => 'Flow',
                        'updated_at'        => $fabricAgainstOrder->dye_actual ?? now(),
                    ];
                    NewItemLogs::create($logData);
                    $registerLog($logData);

                    NewItemLogsTracker::where('unique_id', $uid)
                        ->update([
                            'source'            => $loopAllocatedTo,
                            'statuslocation'    => 'BPU',
                            'sub_status_status' => 'Dye_Completion',
                            'doer_name'         => $loopBagPrepName,
                        ]);

                    $this->info("Updated fabricAgainstOrder - Dye_Status for: {$uid}");
                }
            }

            // CASE J: Bag_Preparation
            if (!empty($fabricAgainstOrder->bag_done_by) && $fabricAgainstOrder->confirmation_of_bag_processing == 'Yes') {
                $alreadyExists = isset($existingLogKeys["$uid|S:Bag_Preparation|T:Flow"]);

                if (!$alreadyExists) {
                    $logData = [
                        'unique_id'         => $uid,
                        'andaaz_order_id'   => $fabricAgainstOrder->increment_id,
                        'product_sku'       => $fabricAgainstOrder->product_sku,
                        'updated_by'        => $fabricAgainstOrder->bag_done_by,
                        'doername'          => $fabricAgainstOrder->bag_done_by,
                        'location'          => 'BPU',
                        'sub_loaction'      => 'Bag_Preparation',
                        'next_sub_location' => 'To_Check_by_Samim',
                        'source'            => $loopAllocatedTo,
                        'shipped_number'    => $shippedNumber,
                        'type'              => 'Flow',
                        'updated_at'        => $fabricAgainstOrder->timestamp_qty ?? now(),
                    ];
                    NewItemLogs::create($logData);
                    $registerLog($logData);

                    NewItemLogsTracker::where('unique_id', $uid)
                        ->update([
                            'source'            => $loopAllocatedTo,
                            'statuslocation'    => 'BPU',
                            'sub_status_status' => 'Bag_Preparation',
                            'doer_name'         => $fabricAgainstOrder->bag_done_by,
                        ]);

                    $this->info("Updated fabricAgainstOrder - Bag_Preparation for: {$uid}");
                }
            }

            // CASE K: To_Check_by_Samim
            if (empty($fabricAgainstOrder->cbs_timestamp) && !empty($fabricAgainstOrder->bg_timestamp)) {
                $alreadyExists = isset($existingLogKeys["$uid|S:To_Check_by_Samim|T:Flow"]);

                if (!$alreadyExists) {
                    $logData = [
                        'unique_id'         => $uid,
                        'andaaz_order_id'   => $fabricAgainstOrder->increment_id,
                        'product_sku'       => $fabricAgainstOrder->product_sku,
                        'updated_by'        => 'SAMIM',
                        'doername'          => 'SAMIM',
                        'location'          => 'FACTORY',
                        'sub_loaction'      => 'To_Check_by_Samim',
                        'next_sub_location' => 'Cutting_Pending',
                        'source'            => $loopAllocatedTo,
                        'shipped_number'    => $shippedNumber,
                        'type'              => 'Flow',
                        'updated_at'        => $fabricAgainstOrder->bg_timestamp ?? now(),
                    ];
                    NewItemLogs::create($logData);
                    $registerLog($logData);

                    NewItemLogsTracker::where('unique_id', $uid)
                        ->update([
                            'source'            => $loopAllocatedTo,
                            'statuslocation'    => 'FACTORY',
                            'sub_status_status' => 'To_Check_by_Samim',
                            'doer_name'         => 'SAMIM',
                        ]);

                    $this->info("Updated fabricAgainstOrder - To_Check_by_Samim for: {$uid}");
                }
            }
        }

        /* For Cutting Masters */
        $cuttingMasters = CuttingMasterSQL::whereIn('unique_id', $invoicedUniqueIds)
                    ->orderBy('id', 'DESC')
                    ->get();

        /* Stitching records once — avoids re-querying inside the cutting loop */
        $stitchingForCutting = StitchingToPpSQL::whereIn('unique_Id', $invoicedUniqueIds)
            ->orderBy('id', 'DESC')
            ->get()
            ->groupBy('unique_id');
        
    // CASE I: Cutting_Pending - check all rows for this uid, trigger if any has cbs_timestamp
        $fabricAgainstOrderData = FabricAgainstOrderSQL::whereIn('unique_id', $invoicedUniqueIds)
            ->whereNotNull('cbs_timestamp')
            ->get();

        $cuttingMasterUidSet = CuttingMasterSQL::whereIn('unique_id', $invoicedUniqueIds)
            ->pluck('unique_id')
            ->flip()
            ->all();

        foreach ($fabricAgainstOrderData as $fabric) {

            $uid = $fabric->unique_id;
            $hasCuttingMaster = isset($cuttingMasterUidSet[$uid]);

            if (!$hasCuttingMaster) {
                $alreadyExists = isset($existingLogKeys["$uid|S:Cutting_Pending|T:Flow"]);

                if (!$alreadyExists) {
                    $logData = [
                        'unique_id'         => $uid,
                        'andaaz_order_id'   => $fabric->increment_id,
                        'product_sku'       => $fabric->product_sku,
                        'updated_by'        => 'SAMIM',
                        'doername'          => 'SUJEET CHAUPAL',
                        'location'          => 'FACTORY',
                        'sub_loaction'      => 'Cutting_Pending',
                        'next_sub_location' => 'Cutting',
                        'source'            => $loopAllocatedTo,
                        'shipped_number'    => $shippedNumber,
                        'type'              => 'Flow',
                        'updated_at'        => $fabric->cbs_timestamp ?? now(),
                    ];
                    NewItemLogs::create($logData);
                    $registerLog($logData);

                    NewItemLogsTracker::where('unique_id', $uid)
                        ->update([
                            'source'            => $loopAllocatedTo,
                            'statuslocation'    => 'FACTORY',
                            'sub_status_status' => 'Cutting_Pending',
                            'doer_name'         => 'SUJEET CHAUPAL',
                        ]);

                    $this->info("Updated Cutting_Pending for: {$uid}");
                }
            }
        }

        foreach ($cuttingMasters as $cuttingMaster) {
            if (empty($cuttingMaster->unique_id)) {
                continue;
            }

            $uid             = $cuttingMaster->unique_id;
            $loopAllocatedTo = $allocatedToMap[$uid] ?? '';
            $existNewItemLog = $existingLogsMap[$uid] ?? null;
            $shippedNumber   = $shippedNumberMap[$uid] ?? null;

            if (!$existNewItemLog) {
                NewItemLogs::create([
                    'unique_id'       => $uid,
                    'type'            => 'New',
                    'andaaz_order_id' => $cuttingMaster->increment_id,
                    'product_sku'     => $cuttingMaster->product_sku,
                    'updated_by'      => '--',
                    'doername'        => '--',
                    'location'        => '--',
                ]);
                $existingLogsMap[$uid] = (object)['shipped_number' => null];
                $this->info("Inserted NEW cuttingMaster for: {$uid}");
            }            

            // CASE J: Cutting
            if ($cuttingMaster->dress_type == 'TOP' && !empty($cuttingMaster->emp_id_master) && empty($cuttingMaster->cutting_finished_tmsp)) {
                $alreadyExists = isset($existingLogKeys["$uid|S:Cutting|T:Flow"]);

                if (!$alreadyExists) {
                    $logData = [
                        'unique_id'         => $uid,
                        'andaaz_order_id'   => $cuttingMaster->increment_id,
                        'product_sku'       => $cuttingMaster->product_sku,
                        'updated_by'        => $cuttingMaster->cutting_done_by,
                        'doername'          => $cuttingMaster->emp_id_master,
                        'location'          => 'FACTORY',
                        'sub_loaction'      => 'Cutting',
                        'next_sub_location' => 'Stitching_Pending',
                        'source'            => $loopAllocatedTo,
                        'shipped_number'    => $shippedNumber,
                        'type'              => 'Flow',
                        'updated_at'        => $cuttingMaster->timestamp ?? now(),
                    ];
                    NewItemLogs::create($logData);
                    $registerLog($logData);

                    NewItemLogsTracker::where('unique_id', $uid)
                        ->update([
                            'source'            => $loopAllocatedTo,
                            'statuslocation'    => 'FACTORY',
                            'sub_status_status' => 'Cutting',
                            'doer_name'         => $cuttingMaster->emp_id_master,
                            /*'shipped_number'   => $shippedNumber,*/
                        ]);

                    $this->info("Updated cuttingMaster - Cutting for: {$uid}");
                }
            }

        // CASE K: Stitching_Pending
            if ($cuttingMaster->dress_type == 'TOP' && !empty($cuttingMaster->cutting_finished_tmsp)) {
                $alreadyExists = isset($existingLogKeys["$uid|S:Stitching_Pending|T:Flow"]);

                if (!$alreadyExists) {
                    $firstStitching = ($stitchingForCutting[$uid] ?? collect())->first();

                    $logData = [
                        'unique_id'         => $uid,
                        'andaaz_order_id'   => $cuttingMaster->increment_id,
                        'product_sku'       => $cuttingMaster->product_sku,
                        'updated_by'        => $firstStitching?->emp_Id_allocatort ?? $cuttingMaster->emp_id_allocator,
                        'doername'          => $firstStitching?->allocate_tailor ?? null,
                        'location'          => 'FACTORY',
                        'sub_loaction'      => 'Stitching_Pending',
                        'next_sub_location' => 'Stitching',
                        'source'            => $loopAllocatedTo,
                        'shipped_number'    => $shippedNumber,
                        'type'              => 'Flow',
                        'updated_at'        => $cuttingMaster->cutting_finished_tmsp,
                    ];
                    NewItemLogs::create($logData);
                    $registerLog($logData);

                    NewItemLogsTracker::where('unique_id', $uid)
                        ->update([
                            'source'            => $loopAllocatedTo,
                            'statuslocation'    => 'FACTORY',
                            'sub_status_status' => 'Stitching_Pending',
                            'doer_name'         => $firstStitching?->allocate_tailor ?? null,
                        ]);

                    $this->info("Updated Stitching_Pending for: {$uid}");
                }
            }
        }

        /* Stitching To PP */
        $StitchingToPPs = StitchingToPpSQL::whereIn('unique_Id', $invoicedUniqueIds)
                ->orderBy('id', 'DESC')
                ->get(); 
        foreach ($StitchingToPPs as $StitchingToPP) {
            if (empty($StitchingToPP->unique_Id)) {
                continue;
            }

            $uid             = $StitchingToPP->unique_Id;
            $loopAllocatedTo = $allocatedToMap[$uid] ?? '';
            $existNewItemLog = $existingLogsMap[$uid] ?? null;
            $shippedNumber   = $shippedNumberMap[$uid] ?? null;

            if (!$existNewItemLog) {
                NewItemLogs::create([
                    'unique_id'       => $uid,
                    'type'            => 'New',
                    'andaaz_order_id' => $StitchingToPP->increment_id,
                    'product_sku'     => $StitchingToPP->product_sku,
                    'updated_by'      => '--',
                    'doername'        => '--',
                    'location'        => '--',
                ]);
                $existingLogsMap[$uid] = (object)['shipped_number' => null];
                $this->info("Inserted NEW StitchingToPP for: {$uid}");
            }

            // CASE L: Stitching
            if (!empty($StitchingToPP->allocate_tailor) && $StitchingToPP->dress_type == 'TOP' && empty($StitchingToPP->stitching_finished)) {
                $alreadyExists = isset($existingLogKeys["$uid|S:Stitching|T:Flow"]);

                if (!$alreadyExists) {
                    $logData = [
                        'unique_id'         => $uid,
                        'andaaz_order_id'   => $StitchingToPP->increment_id,
                        'product_sku'       => $StitchingToPP->product_sku,
                        'updated_by'        => $StitchingToPP->emp_Id_allocatort,
                        'doername'          => $StitchingToPP->allocate_tailor,
                        'location'          => 'FACTORY',
                        'sub_loaction'      => 'Stitching',
                        'next_sub_location' => 'Finishing',
                        'source'            => $loopAllocatedTo,
                        'shipped_number'    => $shippedNumber,
                        'type'              => 'Flow',
                        'updated_at'        => $StitchingToPP->timestamp ?? now(),
                    ];
                    NewItemLogs::create($logData);
                    $registerLog($logData); 

                    if (!isset($stitchingTrackerUpdated[$uid])) {
                        NewItemLogsTracker::where('unique_id', $uid)
                            ->update([
                                'source'            => $loopAllocatedTo,
                                'statuslocation'    => 'FACTORY',
                                'sub_status_status' => 'Stitching',
                                'doer_name'         => $StitchingToPP->allocate_tailor,
                            ]);
                        $stitchingTrackerUpdated[$uid] = true;
                    }

                    $this->info("Updated StitchingToPP - Stitching for: {$uid}");
                }
            }

            // CASE K-248: Finishing
            if (!empty($StitchingToPP->stitching_finished) && $StitchingToPP->dress_type == 'TOP' && empty($StitchingToPP->tfinishing)) {
                $alreadyExists = isset($existingLogKeys["$uid|S:Finishing|T:Flow"]);

                if (!$alreadyExists) {
                    $logData = [
                        'unique_id'         => $uid,
                        'andaaz_order_id'   => $StitchingToPP->increment_id,
                        'product_sku'       => $StitchingToPP->product_sku,
                        'updated_by'        => $StitchingToPP->nfinishing_doer,
                        'doername'          => $StitchingToPP->nfinishing_doer,
                        'location'          => 'FACTORY',
                        'sub_loaction'      => 'Finishing',
                        'next_sub_location' => 'Quality_Check',
                        'source'            => $loopAllocatedTo,
                        'shipped_number'    => $shippedNumber,
                        'type'              => 'Flow',
                        'updated_at'        => $StitchingToPP->stitching_finished ?? now(),
                    ];
                    NewItemLogs::create($logData);
                    $registerLog($logData); 

                    if (!isset($stitchingTrackerUpdated[$uid])) {
                        NewItemLogsTracker::where('unique_id', $uid)
                            ->update([
                                'source'            => $loopAllocatedTo,
                                'statuslocation'    => 'FACTORY',
                                'sub_status_status' => 'Finishing',
                                'doer_name'         => $StitchingToPP->nfinishing_doer,
                            ]);
                            $stitchingTrackerUpdated[$uid] = true;
                        }

                    $this->info("Updated StitchingToPP - Finishing for: {$uid}");
                }
            }

            // CASE L-264: Quality_Check
            if (!empty($StitchingToPP->tfinishing) && $StitchingToPP->dress_type == 'TOP' && empty($StitchingToPP->final_quality_status)) {
                $alreadyExists = isset($existingLogKeys["$uid|S:Quality_Check"]);

                if (!$alreadyExists) {
                    $logData = [
                        'unique_id'             => $uid,
                        'andaaz_order_id'       => $StitchingToPP->increment_id,
                        'product_sku'           => $StitchingToPP->product_sku,
                        'updated_by'            => $StitchingToPP->qc_done_by,
                        'doername'              => $StitchingToPP->qc_done_by,
                        'manwear'               => $StitchingToPP->final_quality_status,
                        'location'              => 'FACTORY',
                        'sub_loaction'          => 'Quality_Check',
                        'next_sub_location'     => 'Pressing_Packing',
                        'source'                => $loopAllocatedTo,
                        'shipped_number'        => $shippedNumber,
                        'type'                  => 'Flow',
                        'updated_at'            => $StitchingToPP->tfinishing ?? now(),
                    ];
                    NewItemLogs::create($logData);
                    $registerLog($logData);

                    if (!isset($stitchingTrackerUpdated[$uid])) {
                        NewItemLogsTracker::where('unique_id', $uid)
                            ->update([
                                'source'            => $loopAllocatedTo,
                                'statuslocation'    => 'FACTORY',
                                'sub_status_status' => 'Quality_Check',
                                'doer_name'         => $StitchingToPP->qc_done_by,
                            ]);
                            $stitchingTrackerUpdated[$uid] = true;
                        }

                    $this->info("Updated StitchingToPP - Quality_Check for: {$uid}");
                }
            }
            
            $status = $StitchingToPP->final_quality_status;

            if (in_array($status, ['Alteration Require', 'Accepted'])) {
                NewItemLogsTracker::where('unique_id', $uid)
                    ->update([
                        'given_for' => $status === 'Alteration Require'
                            ? "{$status} :: {$StitchingToPP->qc_remark} :: {$StitchingToPP->mistake_source}"
                            : $status,
                    ]);
            }

            // CASE M-280: Pressing_Packing
            if ($StitchingToPP->final_quality_status == 'Accepted' && $StitchingToPP->dress_type == 'TOP' && empty($StitchingToPP->timestamp_pressing_packing)) {
                $alreadyExists = isset($existingLogKeys["$uid|S:Pressing_Packing"]);

                if (!$alreadyExists) {
                    $logData = [
                        'unique_id'         => $uid,
                        'andaaz_order_id'   => $StitchingToPP->increment_id,
                        'product_sku'       => $StitchingToPP->product_sku,
                        'location'          => 'FACTORY',
                        'sub_loaction'      => 'Pressing_Packing',
                        'next_sub_location' => 'Ready to dispatch',
                        'source'            => $loopAllocatedTo,
                        'shipped_number'    => $shippedNumber,
                        'type'              => 'Flow',
                        'updated_at'        => $StitchingToPP->updated_at ?? now(),
                    ];
                    NewItemLogs::create($logData);
                    $registerLog($logData);

                    if (!isset($stitchingTrackerUpdated[$uid])) {
                        NewItemLogsTracker::where('unique_id', $uid)
                            ->update([
                                'source'            => $loopAllocatedTo,
                                'statuslocation'    => 'FACTORY',
                                'sub_status_status' => 'Pressing_Packing',
                                'doer_name'         => '',
                            ]);
                            $stitchingTrackerUpdated[$uid] = true;
                        }
                    $this->info("Updated StitchingToPP - Pressing_Packing for: {$uid}");
                }
            }
        }       
        $this->syncMeasuremenrFromSheet();

        $this->info('Item log Table Updated: ' . Carbon::now());
        return 0;
    }

    private function syncMeasuremenrFromSheet(): void
    {
        $maxRetries = 3;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $client = new \Google_Client();
                $client->setApplicationName('AndaazFashion Order Measurement Data');
                $client->setScopes([\Google\Service\Sheets::SPREADSHEETS_READONLY]);
                $client->setAuthConfig(app_path('google-sheets/credentials.json'));

                $service  = new \Google\Service\Sheets($client);
                $response = $service->spreadsheets_values->get(
                    '1jnl-rvoraq8xdshfU22syna1iBdkSIDy2auHo8bxPJE',
                    'Sheet1!A:H'
                );
                $rows      = $response->getValues() ?? [];
                $sheetRows = [];

                foreach ($rows as $index => $row) {
                    if ($index <= 1) continue;
                    $uniqueId   = trim($row[0] ?? '');
                    $itemStatus = trim($row[6] ?? '');
                    if (empty($uniqueId) || empty($itemStatus)) continue;
                    $sheetRows[] = ['uid' => $uniqueId, 'status' => $itemStatus];
                }

                if (empty($sheetRows)) {
                    $this->info("Measurement Sheet sync complete: 0 unique_id(s) updated.");
                    return;
                }

                $allUids = array_column($sheetRows, 'uid');

                // Bulk update NewItemLogs (latest per uid) — 2 queries instead of N
                $latestLogIds = NewItemLogs::selectRaw('MAX(entity_id) as max_id')
                    ->whereIn('unique_id', $allUids)
                    ->groupBy('unique_id')
                    ->pluck('max_id');
                if ($latestLogIds->isNotEmpty()) {
                    NewItemLogs::whereIn('entity_id', $latestLogIds)
                        ->update(['shipped_number' => 'Shipped']);
                }

                // Bulk update Tracker and Appsheet — group by status to minimise queries
                $byStatus = collect($sheetRows)->groupBy('status');
                foreach ($byStatus as $status => $items) {
                    $uids = $items->pluck('uid')->all();
                    NewItemLogsTracker::whereIn('unique_id', $uids)
                        ->update(['shipped_number' => $status, 'dispatch_status' => $status]);
                    OrderMsrmtAppsheetSQL::whereIn('unique_id', $uids)
                        ->update(['order_item_status' => $status]);
                }

                $synced = count($sheetRows);
                $this->info("Measurement Sheet sync complete: {$synced} unique_id(s) updated.");
                return; // success — exit retry loop

            } catch (\Google\Service\Exception $e) {
                if ($e->getCode() === 503 && $attempt < $maxRetries) {
                    $this->warn("Google Sheets 503 (attempt {$attempt}/{$maxRetries}), retrying in 5s...");
                    sleep(5);
                    continue;
                }
                $this->warn("Google Sheet sync skipped (transient error {$e->getCode()}): {$e->getMessage()}");
                return;
            } catch (\Exception $e) {
                $this->error('Google Sheet sync failed: ' . $e->getMessage());
                return;
            }
        }
    }

    private function parseDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }
        $date = trim($date);
        foreach ([ 'Y-m-d H:i:s',
                'Y-m-d',
                'd/m/Y',
                'd-m-Y',
                'd/m/y',
                'd M Y',
                'd-M-Y',
                'd-M-y',
                'm/d/Y',
            ] as $format) {
            try {
                return Carbon::createFromFormat($format, $date)->format('Y-m-d');
            } catch (\Exception) {
                /* We will add error if required */
            }
        }
        return null;
    }
}
