<?php

namespace Vanguard\Http\Controllers;

use Vanguard\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Vanguard\Models\InhouseOrderItem;

class InhouseOrderItemController extends Controller
{
    public function getItemsByOrderId($orderId)
    {
        $items = InhouseOrderItem::where('order_id', $orderId)->get();
        return response()->json($items);
    }
    public function search(Request $request)
    {
        $sku = $request->input('sku');
        
        $product = InhouseOrderItem::where('product_sku', $sku)->first();

        return view('product.details', compact('product'));
    }
}