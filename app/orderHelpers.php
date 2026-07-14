<?php

use \Vanguard\Models\Order;
use Illuminate\Support\Facades\DB;
use \Vanguard\Models\InhouseOrderItem;
use \Vanguard\Models\ItemProcessDetail;
use \Vanguard\Models\MeasurmentCustomerRelation;
use \Vanguard\Models\MeasurementProfile;
use \Vanguard\Models\StandardMeasurement;
use \Vanguard\Models\AndaazOrderStatusHistory;
use \Vanguard\Models\Addons;
use Illuminate\Support\Str;
use \Vanguard\Models\AppsheetToInhouse;
use Vanguard\Models\OrderDateWiseReportRemark;
use \Vanguard\Models\ItemComment;
use \Vanguard\Models\HoldToUnholdSeen;
use Vanguard\Models\AppSheet6FromDB23janModel;
use Vanguard\Models\ProductReviewSkeeper;

use Vanguard\Models\NewItemLogsTracker;

if (!function_exists('getShippingMethodOrderById')) {
    function getShippingMethodOrderById($incrementId) {
        $OrderVal = Order::where('increment_id', $incrementId)->first(); 

        return $OrderVal ? $OrderVal->shipping_method : 'Shipping method not found';
    }
}

if (!function_exists('getOrderAddressByType')) {
    function getOrderAddressByType($incrementId, $type = 'shipping') {
        $order = Order::where('increment_id', $incrementId)->first();

        if (!$order) {
            return 'Order not found';
        }

        $address = DB::table('andaaz_order_address')
            ->where('parent_id', $order->entity_id)
            ->where('address_type', $type)
            ->first(); 
        return $address ?: 'Address not found';
    }
}

if (!function_exists('getOrderPaymentByOrderId')) {
    function getOrderPaymentByOrderId($incrementId) {
        $order = Order::where('increment_id', $incrementId)->first();

        if (!$order) {
            return 'Order not found';
        }

        $orderPayment = DB::table('andaaz_order_payment')
            ->where('order_id', $order->entity_id)
            ->first(); 
        return $orderPayment ?: '';
    }
}

if (!function_exists('getOrdersByCustomerEmail')) {
    function getOrdersByCustomerEmail($customerEmail)
    {
        $orders = Order::where('customer_email', $customerEmail)
            ->select('id', 'increment_id', 'customer_email','total_qty_ordered','total_item_count','order_currency_code','grand_total','created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return $orders->isNotEmpty() ? $orders : 'Order not found';
    }
}

if (!function_exists('getCoutryNameByCode')) {
    function getCoutryNameByCode($countryCode) {
        
        $country = DB::table('andaaz_inhouse_country_name')
            ->where('country_code', $countryCode)
            ->first();   

        return $country ? $country->country_name : 'Country not found';
    }
}

if (!function_exists('getItemsByOrderId')) {
    function getItemsByOrderId($orderId, $productSku='') {
        $productCollection = InhouseOrderItem::where('order_id', $orderId);
        if($productSku!=='' && isset($productSku)){
            $productCollection->where('product_sku', 'like', '%' . $productSku . '%'); 
            $productCollection->orderBy('id')->get();
        }
        return $productCollection->get(); 
    }
}

if (!function_exists('getRefundedItemsByOrderId')) {
    function getRefundedItemsByOrderId($orderId, $productSku='') {
        $productCollection = InhouseOrderItem::where('order_id', $orderId) 
                            ->where(function($query) {
            $query->whereNull('rr_refund_status')
                      ->orWhere('rr_refund_status', '=', 1);
            });
        
        if($productSku!=='' && isset($productSku)){        
                $productCollection->where('product_sku', 'like', '%' . $productSku . '%'); 
            $productCollection->orderBy('id')->get();
        }
        return $productCollection->get(); 
    }
}

if (!function_exists('getOrderIdItemIdByItemId')) {
    function getOrderIdItemIdByItemId($productItemId = '') {
        return InhouseOrderItem::query()
            ->join('andaaz_order', 'andaaz_order.entity_id', '=', 'andaaz_inhouse_new.order_entity_id')
            ->where('andaaz_inhouse_new.product_item_id', $productItemId)
            ->select(
                'andaaz_inhouse_new.product_sku',
                'andaaz_inhouse_new.id',
                'andaaz_inhouse_new.product_img',
                'andaaz_inhouse_new.order_id',
                'andaaz_inhouse_new.order_entity_id',
                'andaaz_order.id as order_table_id',
                'andaaz_order.entity_id as order_entity_id_main'
            )
            ->get();
    }
}


if (!function_exists('getItemsProcessDetailsByOrderId')) {
    function getItemsProcessDetailsByOrderId($productItemId) {
        return ItemProcessDetail::where('product_item_id', $productItemId)->orderBy('id', 'desc')->get();
    }
}

if (!function_exists('getMeasurmentCustomerByOrderId')) {
    function getMeasurmentCustomerByOrderId($orderNo) {
        $myMeasurment = MeasurmentCustomerRelation::where('order_no', $orderNo)->pluck('mm_profile_id','order_item_id');
        if ($myMeasurment->count() > 0) {
            return $myMeasurment;
        }

        $productCollection = InhouseOrderItem::where('order_id', $orderNo)
            ->pluck('mmtid','product_item_id'); 
        return $productCollection; 
    }
}

if (!function_exists('getMeasurmentByOrderId')) {
    function getMeasurmentByOrderId($mmProfileId) {
        $myMeasurment = MeasurementProfile::where('id', $mmProfileId)->get();
        return $myMeasurment;
    }
}

if (!function_exists('getSpecialInstructionBySkuItemId')) {
    function getSpecialInstructionBySkuItemId($sku,$itemId){
         $specialInstruction = DB::table('special_instruction')
            ->where('sku', $sku)
            ->where('item_id', $itemId)
            ->get(); 
        return $specialInstruction;
    }
}

if (!function_exists('getCustomerCommentsOrderStatusHistory')) {
    function getCustomerCommentsOrderStatusHistory($parentId, $isCustomerNotified, $isVisibleOnFront) {
        return AndaazOrderStatusHistory::where('parent_id', $parentId)
            ->where('is_customer_notified', $isCustomerNotified)
            ->where('is_visible_on_front', $isVisibleOnFront)
            ->get();
    }
}

if (!function_exists('getItemComments')) {
    function getItemComments($item_id) {
        return ItemComment::where('item_id', $item_id)
            ->where('is_visible_on_front', 0)
            ->get();
    }
}

if (!function_exists('getHoldToUnholdData')) {
    function getHoldToUnholdData($orderId, $uniqueId)
    {
        return HoldToUnholdSeen::where('order_id', $orderId)
            ->where('unique_id', $uniqueId)
            ->first();
    }
} 

if (!function_exists('getAppsheetToInhouseData')) {
    function getAppsheetToInhouseData($productItemId, $orderId = null) {
        $query = AppsheetToInhouse::where('unique_id', 'ANDFS_' . $productItemId);

        if (!empty($orderId)) {
            $query->where('order_id', $orderId);
        }

        $collection = $query->get();
        $shippedRecord = $collection->firstWhere('shipped_number', '!=', null);

        if ($shippedRecord) {
            $collection = $collection->reject(function ($item) use ($shippedRecord) {
                return $item->id === $shippedRecord->id;
            })->push($shippedRecord);
        }

        return $collection;
    }
} 

if (!function_exists('getNewItemLogsTrackerData')) {
    function getNewItemLogsTrackerData($productItemId, $orderId = null) {
        $query = NewItemLogsTracker::where('unique_id', 'ANDFS_' . $productItemId);

        if (!empty($orderId)) {
            $query->where('order_id', $orderId);
        }

        $collection = $query->get();
        return $collection;
    }
}

if (!function_exists('getProductSkuAndMmtid')) {
    function getProductSkuAndMmtid($orderId, $productItemId)
    {
        $item = InhouseOrderItem::select('product_sku', 'id','product_size')
            ->where('order_id', $orderId)
            ->where('product_item_id', $productItemId)
            ->first();

        if ($item) {
            return [
                'productsku' => $item->product_sku,
                'productid' => $item->id,
                'productsize' => $item->product_size,
            ];
        }
        return null;
    }
}

if (!function_exists('getStandardMeasurementByItemId')) {
    function getStandardMeasurementByItemId($itemId) {
        return \Vanguard\Models\StandardMeasurement::where('item_id', $itemId)->first();
    }
}

if (!function_exists('getAllAddons')) {
    function getAllAddons($productId=NULL) {
        return \Vanguard\Models\Addons::all();
    }
}

if (!function_exists('renderMeasurementRow')) {
    function renderMeasurementRow($myMeasure, $field, $label)
    {
        $image = '';
        $value = '';

        if (!empty($myMeasure->$field) && strpos($myMeasure->$field, '|') !== false) {
            list($value, $image) = explode('|', $myMeasure->$field);
        } else {
            $value = $myMeasure->$field ?? '';
        }

        $output = '<tr>
            <td data-label="'.__($label).'">'.__($label).'</td>
            <td>
                <span id="text'.$field.'">'.$value;

        if ($image !== '') {
            $output .= '<img src="'.asset($image).'" style="width:50px; float:right; max-width:55px;" />';
        }

        $output .= '</span>
            </td>
            <td>
                <input name="'.$field.'chk" type="text" id="'.$field.'chk">
                <input name="'.$field.'image" type="file" accept="image/*">
            </td>
        </tr>';

        return $output;
    }

    if (!function_exists('utcToIst')) {
        function utcToIst($utcDateTime, $format = 'd/m/Y H:i:s')
        {
            if (empty($utcDateTime)) {
                return null;
            }

            $date = new DateTime($utcDateTime, new DateTimeZone('UTC'));
            $date->setTimezone(new DateTimeZone('Asia/Kolkata'));

            return $date->format($format);
        }
    }

    if (!function_exists('getRemarksByEntityId')) {
        function getRemarksByEntityId($entityId)
        {
            return OrderDateWiseReportRemark::where('item_sku', $entityId)
                ->orderBy('created_at', 'desc')
                ->get();
        }
    }
    if (!function_exists('getForTrackersRemarksByEntityId')) {
        function getForTrackersRemarksByEntityId($entityId)
        {
            return OrderDateWiseReportRemark::where('item_sku', $entityId)
                ->orderBy('created_at', 'desc')
                ->get();
        }
    }
}

if (!function_exists('getLatestLogByUniqueId')) {
    function getLatestLogByUniqueId(string $uniqueId)
    {
        try {
            return AppSheet6FromDB23janModel::where('unique_id', $uniqueId)
                ->whereNotNull('doername')
                ->whereNotIn('doername', ['EOD', ''])
                ->orderBy('updated_at', 'desc') // latest record by timestamp*/
                ->first();
        } catch (\Illuminate\Database\QueryException $e) {
            return null;
        }
    }
}


if (!function_exists('getProductReviewRate')) {
    function getProductReviewRate($orderReference)
    { 
        if (empty($orderReference)) {
            return null;
        }

        $products = InhouseOrderItem::where('order_id', $orderReference)
             ->get();

        $result = [];

        foreach ($products as $product) {
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

if (!function_exists('getCurrentProductReviewRateNewOrder')) {
    function getCurrentProductReviewRateNewOrder($orderReference)
    {
        if (empty($orderReference)) {
            return collect();
        }
        $products = InhouseOrderItem::where('order_id', $orderReference)->get();
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