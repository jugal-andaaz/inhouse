<?php

namespace Vanguard\Http\Controllers\Web;

use Illuminate\Http\Request;
use Vanguard\Models\AndaazInhouseOrderItem; 
use Vanguard\Http\Controllers\Controller; 
use Illuminate\Support\Facades\DB;
use Vanguard\Models\ItemComment;

class OrderItemCommentsController extends Controller
{
    public function index(Request $request)
    {
        $itemId = $request->get('item_id');
        $collection = AndaazInhouseOrderItem::where('id', $itemId)->first();

        return view('orderitempopup.order_item_comments', compact('collection', 'itemId'));
    }

    public function updateItemComment(Request $request)
    {
         $validator = \Validator::make($request->all(), [
            'item_id' => 'required|integer|exists:andaaz_inhouse_new,id',
            'product_comment' => 'required|string|filled',
        ], [
            'product_comment.required' => 'The comment field is required.',
            'product_comment.filled' => 'The comment cannot be empty.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $oldItemComment = getItemComments($request->item_id);
        $oldItemCommentRecord = $oldItemComment->last(); 

        $comment = ItemComment::create([
            'item_id' => $request->item_id,
            'comment' => $request->product_comment,
            'user'    => $request->loginuser ?? 'system',
            'is_visible_on_front' => 0,
            'created_at' => now(),
            'updated_at' => now(),
            
        ]); 

        // Add a log entry
        DB::table('andaaz_order_log')->insert([
            'order_id' => $request->order_id,
            'sku' => $request->sku,
            'item_id' => $request->item_id,
            'new_value' =>  $request->product_comment,
            'old_value' => $oldItemCommentRecord->comment ?? '',
            'column_name' => 'Comment', 
            'pending_reason' => '1',
            'updated_by' => $request->loginuser ?? 'system',
            'updated_date' => now(),
        ]);

        return "<script>
            window.opener.location.reload();
            window.close();
        </script>";
    }

    public function deleteItemComment($commentId, Request $request)
    { 
        $comment = ItemComment::find($commentId);

        if (!$comment) {
            return redirect()->back()->withErrors(['error' => 'Comment not found.']);
        }

        // 2. Log old & new values
        DB::table('andaaz_order_log')->insert([
            'order_id'      => $request->order_id,
            'sku'           => $request->sku,
            'item_id'       => $comment->item_id,
            'new_value'     => 'Comment__DELETED', // new value
            'old_value'     => $comment->comment, // old value
            'column_name'   => 'Comment',
            'pending_reason'=> 'Deleted',
            'updated_by'    => $request->loginuser ?? $comment->user ?? 'system',
            'updated_date'  => now(),
        ]);

        // 3. Update model field
        $comment->update([
            'is_visible_on_front' => 1
        ]);

        return redirect()->back()->with('success', 'Comment visibility updated successfully.');
    }
}
