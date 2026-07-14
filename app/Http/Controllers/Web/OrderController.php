<?php 
namespace Vanguard\Http\Controllers\Web;

use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Models\Order;
use Vanguard\Repositories\OrderRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;

class OrderController extends Controller {

    protected $orders;

    public function __construct(OrderRepository $orders) {
        $this->middleware('permission:orders');
        $this->orders = $orders;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse {
        $search = $request->input('search');
        $status = $request->input('status');

        // Start query for today's orders
        if (empty($search) && empty($status)) {
            $startUtc = Carbon::now('Asia/Kolkata')->startOfDay()->timezone('UTC');
            $endUtc   = Carbon::now('Asia/Kolkata')->endOfDay()->timezone('UTC');
            $query = Order::whereBetween('created_at', [$startUtc, $endUtc]);
            /*$query = Order::whereDate('created_at', Carbon::today());*/
        } else {
            $query = Order::Query();
        }
        
        // Apply search filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('increment_id', 'LIKE', "%{$search}%")
                  ->orWhere('id', 'LIKE', "%{$search}%")
                  ->orWhere('entity_id', 'LIKE', "%{$search}%")
                  ->orWhere('customer_firstname', 'LIKE', "%{$search}%")
                  ->orWhere('customer_lastname', 'LIKE', "%{$search}%")
                  ->orWhere('country_code', 'LIKE', "%{$search}%")
                  ->orWhere('domain', 'LIKE', "%{$search}%");
            });
        }

        // Apply status filter
        if (!empty($status)) {
            $query->where('order_status', $status);
        }

        $domainCounts = (clone $query)
            ->select('domain', \DB::raw('COUNT(*) as total'))
            ->groupBy('domain')
            ->orderBy('total', 'desc')
            ->get();

        $query->orderBy('entity_id', 'desc');
        // Get total count of today's orders
        $totalOrders = $query->count();

        // Paginate results
        $orders = $query->paginate(20);

        // Get total pages
        $totalPages = $orders->lastPage(); // Last page number
        $currentPage = $orders->currentPage(); // Current page number

        // Get list of order statuses
        $statuses = ['' => __('All')] + Order::statusLists();

        // Return JSON response if requested
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $orders
            ]);
        }

        return view('orders.index', compact('orders', 'statuses', 'search', 'status','totalOrders', 'totalPages', 'currentPage','domainCounts'));
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View|JsonResponse {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Return JSON if API request
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $order
            ]);
        }

        return view('orders.show', compact('order'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse|JsonResponse {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        $order->update($request->only(['qty', 'description', 'price']));

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $order]);
        }

        return redirect()->route('orders.index')->with('success', 'Order has been updated!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order): RedirectResponse|JsonResponse {
        $order->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Order deleted'], 200);
        }

        return redirect()->route('orders.index')->with('success', 'Order deleted!');
    }
}
