<?php 
namespace Vanguard\Http\Controllers\Web;

use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Models\OldOrder; 
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;

class OldOrderController extends Controller {

    protected $orders;

    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse {
        $search = $request->input('search');
        $status = $request->input('status');

        $query = OldOrder::Query();
        
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
        $orders = $query->paginate(30);

        // Get total pages
        $totalPages = $orders->lastPage(); // Last page number
        $currentPage = $orders->currentPage(); // Current page number

        // Get list of order statuses
        $statuses = ['' => __('All')] + OldOrder::statusLists();

        // Return JSON response if requested
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $orders
            ]);
        }

        return view('oldorders.index', compact('orders', 'statuses', 'search', 'status','totalOrders', 'totalPages', 'currentPage','domainCounts'));
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View|JsonResponse {
        $order = OldOrder::find($id);

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

        return view('oldorders.show', compact('order'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse|JsonResponse {
        $order = OldOrder::find($id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        $order->update($request->only(['qty', 'description', 'price']));

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $order]);
        }

        return redirect()->route('oldorders.index')->with('success', 'Order has been updated!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OldOrder $order): RedirectResponse|JsonResponse {
        $order->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Order deleted'], 200);
        }

        return redirect()->route('oldorders.index')->with('success', 'Order deleted!');
    }
}
