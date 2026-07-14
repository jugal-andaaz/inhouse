<?php

namespace Vanguard\Http\Controllers\Web;

use Illuminate\Http\Request;
use Vanguard\Models\AndaazInhouseOrderItem;
/*use Vanguard\Repositories\User\UserRepository;*/
use Vanguard\Http\Controllers\Controller; 
use Illuminate\Support\Facades\DB;


class OrderItemHoldByCsController extends Controller
{
    public function index(Request $request)
    {
        $itemId = $request->get('item_id');
        $collection = AndaazInhouseOrderItem::where('id', $itemId)->first();

        return view('orderitempopup.order_item_holdbycs', compact('collection', 'itemId'));
    }

    public function updateItemDetails(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|integer|exists:andaaz_inhouse_new,id',
            'mmt_pending_reason' => 'nullable|string',
            'pending_reason' => 'nullable|string',
            'rr_further_action_reason' => 'nullable|string',
            'rr_further_action_reason_other' => 'nullable|string',
            'qc_reason' => 'nullable|string',
        ]);

        // Update the main table
        DB::table('andaaz_inhouse_new')
            ->where('id', $request->item_id)
            ->update([
                'mmt_pending_reason' => $request->mmt_pending_reason,
                'pending_reason' => $request->pending_reason,
                'rr_further_action_reason' => '', //$request->rr_further_action_reason,
                'rr_further_action_reason_other' => '', //$request->rr_further_action_reason_other,
                'qc_reason' => '' //$request->qc_reason, 
            ]);

        // Add a log entry
        DB::table('andaaz_order_log')->insert([
            'order_id' => $request->order_id,
            'sku' => $request->sku,
            'item_id' => $request->item_id,
            'new_value' =>  $request->mmt_pending_reason,
            'old_value' => $request->mmt_oldpending_reason ?? '',
            'column_name' => 'MMT', 
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
