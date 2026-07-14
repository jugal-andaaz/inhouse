<?php
namespace Vanguard\Http\Controllers\Web;

use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Models\AndaazInhouseOrderItem;
use Illuminate\Support\Str;

class SkuOrderListController extends Controller
{
    public function index(Request $request)
    {
        $sku = $request->input('sku');
        $items = [];
        
        if ($sku) {
            if (Str::contains($sku, '-')) {
                // Case 1: Full SKU with dash
                $items = AndaazInhouseOrderItem::where('product_sku', 'like', $sku . '%')->get(); 
            } else {
                // Case 2: SKU without dash → check both full and numeric part
                $items = AndaazInhouseOrderItem::where(function ($query) use ($sku) {
                    $query->where('product_sku', 'like', $sku . '%')
                          ->orWhere('product_sku', 'like', '%' . $sku . '%');
                })->get();
            }
        }

        return view('skuorderlist.index', compact('items', 'sku'));
    }
}
