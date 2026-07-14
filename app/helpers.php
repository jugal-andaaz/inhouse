<?php

use Illuminate\Support\Facades\DB;
use \Vanguard\Models\OldOrder;
use \Vanguard\Models\OldInhouseOrderItem;
use \Vanguard\Models\OldMeasurmentCustomerRelation;
use \Vanguard\Models\OldMeasurementProfile;
use \Vanguard\Models\OldAndaazOrderStatusHistory;
use Carbon\Carbon;
use Vanguard\Models\ProductReviewSkeeper;
use Vanguard\Models\OldOrderItemImages;

if (!function_exists('getDomain')) {

    function getDomain($storeId) {
        $domain = "COM";
        switch ($storeId) {
            case 1:
                $domain = "COM";
                break;
            case 2:
                $domain = "FR";
                break;
            case 11:
                $domain = "MY";
                break;
            case 14:
                $domain = "UK";
                break;
            default:
                break;
        }
        return $domain;
    }

}
if (!function_exists('getStatusByText')) {

    function getStatusByText($status) {
        $statusVal = '<span class="label label-default pending">Pending</span>';
        switch (strtolower($status)) {
            case 'processing':
                $statusVal = '<span class="label text-primary processing">Processing</span>';
                break;
            case 'pending':
                $statusVal = '<span class="label text-danger pending">Pending</span>';
                break;
            case 'cancelled':
                $statusVal = '<span class="label label-primary cancelled">Cancelled</span>';
                break;
            case 'cancel_from_prcocessing':
                $statusVal = '<span class="label label-primary cancelled">Cancelled</span>';
                break;
            case 'complete':
                $statusVal = '<span class="label label-primary complete">Complete</span>';
                break;
            case 'closed':
                $statusVal = '<span class="label label-primary complete">Closed</span>';
                break;
            default:
                $statusVal = '<span class="label label-primary"> --- </span>';
                break;
        }
        return $statusVal;
    }
} 
if (!function_exists('parseDateFormat')) {
    function parseDateFormat(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        try {
            return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', trim($date))->format('d-M-Y');
        } catch (\Exception $e) {
            return null;
        }
    }
}

/* Old Inhouse DB Functions*/
if (!function_exists('getOldShippingMethodOrderById')) {
    function getOldShippingMethodOrderById($incrementId) {
        $OrderVal = OldOrder::where('increment_id', $incrementId)->first(); 

        return $OrderVal ? $OrderVal->shipping_method : 'Shipping method not found';
    }
}

if (!function_exists('getOldOrderAddressByType')) {
    function getOldOrderAddressByType($incrementId, $type = 'shipping') {
        $order = OldOrder::where('increment_id', $incrementId)->first();

        if (!$order) {
            return 'Order not found';
        }

        $address = DB::table('old_andaaz_order_address')
            ->where('parent_id', $order->entity_id)
            ->where('address_type', $type)
            ->first(); 
        return $address ?: 'Address not found';
    }
}

if (!function_exists('getOldOrderPaymentByOrderId')) {
    function getOldOrderPaymentByOrderId($incrementId) {
        $order = OldOrder::where('increment_id', $incrementId)->first();

        if (!$order) {
            return 'Order not found';
        }

        $orderPayment = DB::table('old_andaaz_order_payment')
            ->where('order_id', $order->entity_id)
            ->first(); 
        return $orderPayment ?: '';
    }
}

if (!function_exists('getOldItemsByOrderId')) {
    function getOldItemsByOrderId($orderId, $productSku='') {
        
        $productCollection = OldInhouseOrderItem::where('order_id', $orderId);

        if($productSku!=='' && isset($productSku)){
            $productCollection->where('product_sku', 'like', '%' . $productSku . '%'); 
            $productCollection->orderBy('id')->get();
        }
        return $productCollection->get(); 
    }
}
if (!function_exists('getOldOrdersByCustomerEmail')) {
    function getOldOrdersByCustomerEmail($customerEmail)
    {
        $orders = OldOrder::where('customer_email', $customerEmail)
            ->select('id', 'increment_id', 'customer_email','total_qty_ordered','total_item_count','order_currency_code','grand_total','created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return $orders->isNotEmpty() ? $orders : 'Order not found';
    }
}

if (!function_exists('getOldStandardMeasurementByItemId')) {
    function getOldStandardMeasurementByItemId($itemId) {
        return \Vanguard\Models\OldStandardMeasurement::where('item_id', $itemId)->first();
    }
}

if (!function_exists('getOldMeasurmentCustomerByOrderId')) {
    function getOldMeasurmentCustomerByOrderId($orderNo) {
        $myMeasurment = OldMeasurmentCustomerRelation::where('order_no', $orderNo)->pluck('mm_profile_id','order_item_id');
        if ($myMeasurment->count() > 0) {
            return $myMeasurment;  // Already have measurement data
        }

        $productCollection = OldInhouseOrderItem::where('order_id', $orderNo)
            ->pluck('mmtid','product_item_id'); 
        return $productCollection; 
    }
}

if (!function_exists('getOldMeasurmentByOrderId')) {
    function getOldMeasurmentByOrderId($mmProfileId) {
        $myMeasurment = OldMeasurementProfile::where('id', $mmProfileId)->get();
        return $myMeasurment;
    }
}

if (!function_exists('getOldSpecialInstructionBySkuItemId')) {
    function getOldSpecialInstructionBySkuItemId($sku,$itemId){
         $specialInstruction = DB::table('old_special_instruction')
            ->where('sku', $sku)
            ->where('item_id', $itemId)
            ->get(); 
        return $specialInstruction;
    }
}

if (!function_exists('getOldCustomerCommentsOrderStatusHistory')) {
    function getOldCustomerCommentsOrderStatusHistory($parentId, $isCustomerNotified, $isVisibleOnFront) {
        return $order = OldOrder::where('entity_id', $parentId)->get();
    }
}

if (!function_exists('getOldProductReviewRate')) {
    function getOldProductReviewRate($orderReference)
    { 
        if (empty($orderReference)) {
            return null;
        }

        // Get all products for the order
        $products = OldInhouseOrderItem::where('order_id', $orderReference)->get();

        $result = [];

        foreach ($products as $product) {
            // Extract base SKU
            $baseSku = explode('-', $product->product_sku)[0];

            $review = ProductReviewSkeeper::where('order_reference', $orderReference)
                ->where('product_sku', $baseSku)
                ->first(['product_sku','review_content','review_rate']); 
            if ($review) {
                $result[] = $review;
            }
        }

        return $result;
    }
}
if (!function_exists('getCurrentProductReviewRate')) {
    function getCurrentProductReviewRate($orderReference)
    {
        if (empty($orderReference)) {
            return collect();
        }

        $products = OldInhouseOrderItem::where('order_id', $orderReference)->get();

        $result = collect();

        foreach ($products as $product) {

            $originalSku = $product->product_sku;
            $baseSku = explode('-', $originalSku)[0];

            $reviews = ProductReviewSkeeper::where('order_reference', $orderReference)
                ->where(function ($query) use ($originalSku, $baseSku) {
                    $query->where('product_sku', $originalSku)
                          ->orWhere('product_sku', $baseSku);
                })
                ->get();

            $result = $result->merge($reviews);
        }

        return $result;
    }
}

if (!function_exists('getProductImagesByPid')) {
    function getProductImagesByPid($pid)
    {
        if (empty($pid)) {
            return collect();
        }

        return OldOrderItemImages::where('pid', $pid)
            ->get();
    }
}

if (!function_exists('getEpsplTrackingByOrderId')) {
    function getEpsplTrackingByOrderId($orderId)
    {
        if (empty($orderId)) {
            return null;
        }

        return \Illuminate\Support\Facades\Cache::remember(
            'shipment_trackingdata_' . $orderId,
            now()->addMinutes(30),
        /*    fn () => \Vanguard\Models\ShipmentTracking::where('order_id', $orderId)->get()*/
                fn () => \Vanguard\Models\ShipmentTracking::where('order_id', $orderId)
                    ->orderBy('last_fetched_at', 'desc')
                    ->get()
                    ->unique('tracking_number')
                    ->values()
        );
    }
}

if (!function_exists('getShipmentTrackingNumber')) {
    function getShipmentTrackingNumber($itemId, $orderId)
    {
        $tracking = DB::table('shipment_tracking')
            ->where('unique_id', $itemId)
            ->where('order_id', $orderId)
            ->first();

        return $tracking ? $tracking->tracking_number : null;
    }
}

if (!function_exists('format_tracking_datetime')) {
    function format_tracking_datetime($datetime)
    {
        if (!$datetime) return '---';

        return Carbon::parse($datetime)->format('d-M-Y H:i');
    }
}