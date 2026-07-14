<?php

namespace Vanguard\Http\Controllers\Web;

use Illuminate\Http\Request;
use Vanguard\Models\OrderItemLogs;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Repositories\User\UserRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderItemLogsController extends Controller
{
    public function index(Request $request)
    {
        $itemId = $request->get('item_id');

        // Get sort direction from query param, default DESC
        $sort = $request->get('sort', 'desc');
        $sort = in_array(strtolower($sort), ['asc', 'desc']) ? $sort : 'desc';

        $collection = OrderItemLogs::where('item_id', $itemId)
            ->orderBy('updated_date', $sort)
            ->paginate(10);

        return view('orderitempopup.order_item_logs', compact('collection', 'itemId', 'sort')); 
    }

}
