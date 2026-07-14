<?php

namespace Vanguard\Http\Controllers\Web;

use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Vanguard\Models\HoldToUnholdSeen; // adjust namespace if different

class HoldToUnholdController extends Controller
{
    public function updateIndicate(Request $request)
    {
        $request->validate([
            'order_id' => 'required',
            'unique_id' => 'required',
        ]);

        $record = HoldToUnholdSeen::where('order_id', $request->order_id)
            ->where('unique_id', $request->unique_id)
            ->first();

        if ($record) {
            $record->indicate = 1;
            $record->updated_at = now();
            $record->save();

            DB::table('andaaz_order_log')->insert([
                'order_id' => $request->order_id,
                'sku' => $request->unique_id,
                'item_id' => $request->item_id,
                'product_item_id' => str_replace('ANDFS_','',$request->unique_id),
                'new_value' => 'Unhold Confirm' ,
                'old_value' => 'Unhold Pending',
                'column_name' => 'Hold to Unhold Confirmed', 
                'pending_reason' => '',
                'updated_by' => $request->loginuser ?? 'system',
                'updated_date' => now(),
            ]);
        }

        // For AJAX call
        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        // For normal request
        return back()->with('success', 'Status updated successfully.');
    }
}
