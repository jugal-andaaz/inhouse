<?php
namespace Vanguard\Http\Controllers\Web;

use Illuminate\Http\Request;
use Vanguard\Models\AndaazInhouseOrderItem; 
use Vanguard\Http\Controllers\Controller; 
use Illuminate\Support\Facades\DB;

class OrderItemUpdateQtyController extends Controller
{
    public function index(Request $request)
    {
        $itemId = $request->get('item_id');
        $collection = AndaazInhouseOrderItem::where('id', $itemId)->first();

        return view('orderitempopup.order_item_updateqty', compact('collection', 'itemId'));
    }

    public function updateItemQty(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|integer|exists:andaaz_inhouse_new,id', 
            'product_updateqty' => 'required|integer',
        ]);
  
        // Update the main table
        DB::table('andaaz_inhouse_new')
            ->where('id', $request->item_id)
            ->update([
                'product_qty' => $request->product_updateqty
            ]);
 
        // Add a log entry
        DB::table('andaaz_order_log')->insert([
            'order_id' => $request->order_id,
            'sku' => $request->sku,
            'item_id' => $request->item_id,
            'new_value' =>  $request->product_updateqty,
            'old_value' => $request->oldproduct_qty ?? '---',
            'column_name' => 'Update Qty', 
            'pending_reason' => '1',
            'updated_by' => $request->loginuser ?? 'system',
            'updated_date' => now(),
        ]); 

        return "<script>
            window.opener.location.reload();
            window.close();
        </script>";
    }
}
