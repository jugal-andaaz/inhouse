<?php

namespace Vanguard\Http\Controllers\Web;

use Illuminate\Http\Request;
use Vanguard\Models\AndaazInhouseOrderItem; 
use Vanguard\Http\Controllers\Controller; 
use Illuminate\Support\Facades\DB;

class OrderItemImageReplaceController extends Controller
{
    public function index(Request $request)
    {
        $itemId = $request->get('item_id');
        $collection = AndaazInhouseOrderItem::where('id', $itemId)->first();

        return view('orderitempopup.order_item_imagereplace', compact('collection', 'itemId'));
    }

    public function updateItemImageReplace(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|integer|exists:andaaz_inhouse_new,id', 
            'product_img' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imagePath = $request->file('product_img')->store('extra-images', 'public');


        // Handle Image Upload
        if ($request->hasFile('product_img')) {
            $imageName = time() . '.' . $request->product_img->extension();
            $request->product_img->move(public_path('images'), $imageName);
        } 

        // Update the main table
        DB::table('andaaz_inhouse_new')
            ->where('id', $request->item_id)
            ->update([
                'product_img' => $imagePath
            ]);

        // Add a log entry
        DB::table('andaaz_order_log')->insert([
            'order_id' => $request->order_id,
            'sku' => $request->sku,
            'item_id' => $request->item_id,
            'new_value' =>  $imagePath,
            'old_value' => $request->oldproduct_image ?? '---',
            'column_name' => 'Replace Image', 
            'pending_reason' => '1',
            'updated_by' => $request->loginuser ?? 'system',
            'updated_date' => now(),
        ]); 
        DB::table('andaaz_item_image')->insert([
            'name' => str_replace('extra-images/','',$imagePath),
            'path' => $imagePath,
            'pid' => $request->pid,
            'remark' => $request->product_comment ?? 'Image Replaced without Comment',
            'reason' => 'Replace Image',
            'status' => '0',             
            'updated_by' => $request->loginuser ?? 'system',
            'updated_date' => now(),
        ]); 

        return "<script>
            window.opener.location.reload();
            window.close();
        </script>";
    }
}
