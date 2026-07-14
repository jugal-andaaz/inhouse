<?php 
namespace Vanguard\Http\Controllers\Web; 

use Vanguard\Models\AppsheetToInhouse;
use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Exports\OrderStatusExport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelType;
use App\Models\OrderDateWiseReportRemark;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class OrderReportStatusController extends Controller {

	public function index(Request $request)
	{
	    $subQuery = AppsheetToInhouse::selectRaw('MAX(entity_id) as max_id')
	        ->groupBy('unique_id');
	    $sort='';
	    // Base query that applies common filters
	    $baseQuery = AppsheetToInhouse::whereIn('entity_id', $subQuery)
	        ->where(function ($q) {
	            $q->where('shipped_number', '=', '')
	              ->orWhereNull('shipped_number');
	        })
	        ->whereNotIn('sub_status_status', ['Ready to Dispatch', 'Refunded'])
	        ->whereIn('dispatch_status', ['Invoiced', 'Pending', 'Processing'])
	        ->where('refund', 0);


	    // Apply filters from request
	    if ($request->filled('sub_status_status')) {
	        $baseQuery->where('sub_status_status', $request->input('sub_status_status'));
	    }

	    if ($request->filled('hold_status')) {
		    if ($request->input('hold_status') === 'Unhold') {
		        $baseQuery->where(function ($q) {
		            $q->where('hold_status', 'LIKE', 'Unhold%')
		              ->orWhereNull('hold_status')
		              ->orWhere('hold_status', '');
		        });
		    } elseif ($request->input('hold_status') === 'Hold') {
		        $baseQuery->where('hold_status', 'LIKE', 'Hold%');
		    }
		}

	    if ($request->filled('manwear')) {
	        $baseQuery->where('manwear', 'LIKE', $request->input('manwear') . '%');
	    }
	    if (!$request->filled('sub_status_status') && !$request->filled('hold_status') && !$request->filled('manwear')) { 
		    if ((int) auth()->user()->role_id === 7) {
		    	$baseQuery->where('source', 'BPU')
		    				->where('statuslocation', 'BPU');
		    }
		}

	    // For pagination and listing
	    $query = clone $baseQuery;

	    // Count total matching records 
	    $count = (clone $baseQuery)->count();

	    // Today's date for dispatch filter
	    $today = Carbon::today()->format('Y-m-d'); //2025-09-17

	    // Count where dispatch_date <= today
	    $count_dispatch = (clone $baseQuery)
	        ->whereRaw("dispatch_date <= ?", [$today])
	        ->count();

	    // Count where expedition_status = 'EXPEDITE'
	    $count_expedite = (clone $baseQuery)
	        ->where('expendition_status', 'EXPEDITE')
	        ->count();

	    // Count where express_delivery = 'Express_Shipping'
	    $count_express_shipping = (clone $baseQuery)
	        ->where('express_delivery', 'Express_Shipping')
	        ->count();
	    $count_hold = (clone $baseQuery)
	        ->where('hold_status', 'LIKE', 'hold%')
	        ->count();

	    // Sorting by dispatch_date
	    $sort = $request->get('sort', 'oldest');
	    
	    if ($sort === 'oldest') {
	        $query->orderBy('dispatch_date', 'asc');
	    }
	    if ($sort === 'newest') {
	        $query->orderBy('dispatch_date', 'desc');
	    } 

	    $perPage = ((int) auth()->user()->role_id  === 7) ? 100 : 20;
	    
	    $data = $query->orderBy('dispatch_date', 'asc')
              ->paginate($perPage);

	    return view('reports.orderstatus', compact('data', 'count', 'count_dispatch', 'count_expedite', 'count_express_shipping','count_hold'));
	}

    public function export(Request $request)
	{
	    $subQuery = AppsheetToInhouse::selectRaw('MAX(entity_id) as max_id')
	        ->groupBy('unique_id');

	    $query = AppsheetToInhouse::leftJoin('datewise_order_report_remark as dorr', 'appsheet_to_inhouse.unique_id', '=', 'dorr.item_sku')
	        ->select('appsheet_to_inhouse.*', 'dorr.remark')
	        ->whereIn('appsheet_to_inhouse.entity_id', $subQuery)
	        ->where(function ($q) {
	            $q->where('appsheet_to_inhouse.shipped_number', '=', '')
	              ->orWhereNull('appsheet_to_inhouse.shipped_number');
	        })
	        ->where('appsheet_to_inhouse.sub_status_status', '!=', 'Ready to Dispatch')
	        ->whereIn('dispatch_status', ['Invoiced', 'Pending', 'Processing'])
	        ->where('refund', 0);

	    if ($request->filled('sub_status_status')) {
	        $query->where('appsheet_to_inhouse.sub_status_status', $request->input('sub_status_status'));
	    }

	    if ($request->filled('hold_status')) {
	        $query->where('appsheet_to_inhouse.hold_status', 'LIKE', $request->input('hold_status') . '%');
	    }

	    $data = $query->orderBy('appsheet_to_inhouse.entity_id', 'desc')->get();

	    return Excel::download(
	        new OrderStatusExport($data),
	        'order_status_report_' . now()->format('Y-m-d_H-i-s') . '.xlsx',
	        \Maatwebsite\Excel\Excel::XLSX
	    );
	}

	public function exportPdf(Request $request)
	{
	    $subQuery = AppsheetToInhouse::selectRaw('MAX(entity_id) as max_id')
	        ->groupBy('unique_id');

	    $query = AppsheetToInhouse::leftJoin('datewise_order_report_remark as dorr', 'appsheet_to_inhouse.unique_id', '=', 'dorr.item_sku')
	        ->select('appsheet_to_inhouse.*', 'dorr.remark')
	        ->whereIn('appsheet_to_inhouse.entity_id', $subQuery)
	        ->where(function ($q) {
	            $q->where('appsheet_to_inhouse.shipped_number', '=', '')
	              ->orWhereNull('appsheet_to_inhouse.shipped_number');
	        })
	        ->where('appsheet_to_inhouse.sub_status_status', '!=', 'Ready to Dispatch')
	        ->whereIn('dispatch_status', ['Invoiced', 'Pending', 'Processing'])
	        ->where('refund', 0);

	    if ($request->filled('sub_status_status')) {
	        $query->where('appsheet_to_inhouse.sub_status_status', $request->input('sub_status_status'));
	    }

	    if ($request->filled('hold_status')) {
	        $query->where('appsheet_to_inhouse.hold_status', 'LIKE', $request->input('hold_status') . '%');
	    }

	    $data = $query->orderBy('appsheet_to_inhouse.entity_id', 'desc')->get();

	    $pdf = Pdf::loadView('reports.orderstatus_pdf', compact('data'))
	        ->setPaper('a4', 'portrait')
	        ->setOptions(['isPhpEnabled' => true]);

	    return $pdf->download('order_status_report_' . now()->format('Y-m-d_H-i-s') . '.pdf');
	}
}