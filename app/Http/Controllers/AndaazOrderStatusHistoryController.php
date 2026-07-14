<?php

namespace Vanguard\Http\Controllers;

use Illuminate\Http\Request; 
use Vanguard\Models\AndaazOrderStatusHistory;
use Vanguard\Http\Controllers\Controller;

class AndaazOrderStatusHistoryController extends Controller
{
    // Save new status
    public function store(Request $request)
    {
        $data = $request->validate([
            'parent_id' => 'required|integer',
            'is_customer_notified' => 'nullable|integer',
            'is_visible_on_front' => 'nullable|integer',
            'comment' => 'nullable|string',
            'status' => 'nullable|string|max:32',
            'created_at' => 'nullable|date',
            'entity_name' => 'nullable|string|max:32',
        ]);

        $status = AndaazOrderStatusHistory::create($data);

        return response()->json($status);
    }

    // Update by ID
    public function update(Request $request, $id)
    {
        $status = AndaazOrderStatusHistory::findOrFail($id);
        $status->update($request->only([
            'is_customer_notified', 'is_visible_on_front', 'comment', 'status', 'created_at', 'entity_name'
        ]));

        return response()->json($status);
    }

    // Get by parent_id
    public function byParentId($parentId)
    {
        $records = AndaazOrderStatusHistory::where('parent_id', $parentId)->get();
        return response()->json($records);
    }
    /*
    use App\Http\Controllers\AndaazOrderStatusHistoryController;

    Route::post('/order-status-history', [AndaazOrderStatusHistoryController::class, 'store']);
    Route::put('/order-status-history/{id}', [AndaazOrderStatusHistoryController::class, 'update']);
    Route::get('/order-status-history/parent/{parentId}', [AndaazOrderStatusHistoryController::class, 'byParentId']);

    */
}
