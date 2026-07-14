<?php 
namespace Vanguard\Http\Controllers\Web;

use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Models\Order;
use Vanguard\Repositories\OrderRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatewiseOrdersCount extends Controller {

    protected $orders;

   /* public function __construct(OrderRepository $orders) {
        $this->middleware('permission:orders');
        $this->orders = $orders;
    }*/

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse {
        $filterQuery = Order::query();

        // Apply date filter if provided
        if ($request->filled('from') && $request->filled('to')) {
            $filterQuery->whereBetween(DB::raw('DATE(created_at)'), [$request->from, $request->to]);
        }

        // Get total orders and total items for the filtered query
        $totalData = (clone $filterQuery)
                ->selectRaw('DATE(created_at) as order_date, COUNT(*) as total_orders, SUM(total_item_count) as total_items')
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('created_at', 'asc');

        // Paginate orders
        $orders = $totalData->orderBy('created_at', 'desc')->paginate(20);

        // Always return the view here
        return view('orderreports.datewise', [
            'orders'      => $orders
        ]);
    }      
}
