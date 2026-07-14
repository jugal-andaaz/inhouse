<?php

namespace Vanguard\Http\Controllers\Web;

use Illuminate\Http\Request; 
/*use Vanguard\Models\AppSheet6FromDB23janModel;*/
use Vanguard\Models\NewItemLogs;;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Repositories\User\UserRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderItemLogsNewController extends Controller
{
    public function index(Request $request)
    {
        $uniqueId = $request->get('unique_id');
        $productsku = $request->get('productsku');
        // Get sort direction from query param, default DESC
        $sort = $request->get('sort', 'desc');
        $sort = in_array(strtolower($sort), ['asc', 'desc']) ? $sort : 'desc';

        $collection = NewItemLogs::where('unique_id', '=', 'ANDFS_'.$uniqueId)
            ->orderBy('updated_at', $sort)
            ->paginate(10);

        return view('orderitempopup.order_item_logs_new', compact('collection','uniqueId' ,'productsku' ,'sort')); 
    }

}
