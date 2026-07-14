<?php 
namespace Vanguard\Http\Controllers\Web; 

use Vanguard\Models\NewItemLogsTracker;
use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Exports\NewItemLogsTrackerExport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelType;
use App\Models\OrderDateWiseReportRemark;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class NewItemLogsTrackerController extends Controller {

	public function index(Request $request)
	{
	    $perPage = ((int) auth()->user()->role_id === 7) ? 100 : 20;
	    $sort    = $request->get('sort', 'oldest');
	    $page    = $request->get('page', 1);

	    $subQuery = NewItemLogsTracker::selectRaw('MAX(entity_id) as max_id')
	        ->groupBy('unique_id');

	    $baseQuery = NewItemLogsTracker::leftJoin('employee_master as em_doer', DB::raw('LOWER(em_doer.employee_id)'), '=', DB::raw('LOWER(new_itemlogs_tracker.doer_name)'))
	        ->select('new_itemlogs_tracker.*', 'em_doer.employee_name as doer_employee_name')
	        ->whereIn('new_itemlogs_tracker.entity_id', $subQuery)
	        ->where(function ($q) {
	            $q->where('new_itemlogs_tracker.shipped_number', '=', 'Invoiced')
	              ->orWhereNull('new_itemlogs_tracker.shipped_number');
	        })
	        ->where(function ($q) {
	            $q->where('new_itemlogs_tracker.dispatch_status', '=', 'Invoiced')
	              ->orWhereNull('new_itemlogs_tracker.dispatch_status');
	        })
	        ->where('new_itemlogs_tracker.refund', 0);

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
	    if ($request->filled('alteration')) {
	        $baseQuery->where('given_for', 'LIKE', $request->input('alteration') . '%');
	    }

	    if ($request->filled('statuslocation')) {
	        $baseQuery->where('statuslocation', $request->input('statuslocation'));
	    }

	    if ($request->filled('source')) {
	        $baseQuery->where('source', $request->input('source'));
	    }

	    if ($request->filled('unique_id')) {
	        $baseQuery->where('unique_id', 'LIKE', '%' . $request->input('unique_id') . '%');
	    }

	    if ($request->filled('order_id')) {
	        $baseQuery->where('order_id', 'LIKE', '%' . $request->input('order_id') . '%');
	    }

	    if (!$request->filled('sub_status_status') && !$request->filled('hold_status') && !$request->filled('manwear') && !$request->filled('statuslocation') && !$request->filled('source') && !$request->filled('unique_id') && !$request->filled('order_id')) {
	        if ((int) auth()->user()->role_id === 7) {
	            $baseQuery->where('source', 'BPU')->where('statuslocation', 'BPU');
	        }
	    }

	    $today = Carbon::today()->format('Y-m-d');

	    $count                  = (clone $baseQuery)->count();
	    $count_dispatch         = (clone $baseQuery)->whereRaw("revised_dispatch_date <= ?", [$today])->count();
	    $count_expedite         = (clone $baseQuery)->where('expendition_status', 'Expedite')->count();
	    $count_express_shipping = (clone $baseQuery)->where('express_delivery', 'Express_Shipping')->count();
	    $count_hold             = (clone $baseQuery)->where('hold_status', 'LIKE', 'hold%')->count();

	    $statusLocationOptions = NewItemLogsTracker::select('statuslocation')
	        ->whereNotNull('statuslocation')->where('statuslocation', '!=', '')
	        ->distinct()->orderBy('statuslocation')->pluck('statuslocation');

	    $sourceOptions = NewItemLogsTracker::select('source')
	        ->whereNotNull('source')->where('source', '!=', '')
	        ->distinct()->orderBy('source')->pluck('source');

	    $subStatusOptions = NewItemLogsTracker::select('sub_status_status')
	        ->whereNotNull('sub_status_status')->where('sub_status_status', '!=', '')
	        ->distinct()->orderBy('sub_status_status')->pluck('sub_status_status');

	    $query = clone $baseQuery;
	    if ($sort === 'newest') {
	        $query->orderBy('revised_dispatch_date', 'desc');
	    } else {
	        $query->orderBy('revised_dispatch_date', 'asc');
	    }

	    $allRows = $query->get();

	    $data = new \Illuminate\Pagination\LengthAwarePaginator(
	        $allRows->forPage($page, $perPage),
	        $allRows->count(),
	        $perPage,
	        $page,
	        ['path' => $request->url(), 'query' => $request->query()]
	    );

	    return view('reports.newitemlogstracker', compact('data', 'count', 'count_dispatch', 'count_expedite', 'count_express_shipping', 'count_hold', 'statusLocationOptions', 'sourceOptions', 'subStatusOptions'));
	}

    public function export(Request $request)
	{
	    $subQuery = NewItemLogsTracker::selectRaw('MAX(entity_id) as max_id')
	        ->groupBy('unique_id');

	    $query = NewItemLogsTracker::leftJoin('datewise_order_report_remark as dorr', 'new_itemlogs_tracker.unique_id', '=', 'dorr.item_sku')
	        ->select('new_itemlogs_tracker.*', 'dorr.remark')
	        ->whereIn('new_itemlogs_tracker.entity_id', $subQuery)
	        ->where(function ($q) {
	            $q->where('new_itemlogs_tracker.shipped_number', '=', '')
	              ->orWhereNull('new_itemlogs_tracker.shipped_number');
	        })
	        ->where('refund', 0);

	    if ($request->filled('sub_status_status')) {
	        $query->where('new_itemlogs_tracker.sub_status_status', $request->input('sub_status_status'));
	    }

	    if ($request->filled('hold_status')) {
	        $query->where('new_itemlogs_tracker.hold_status', 'LIKE', $request->input('hold_status') . '%');
	    }

	    $data = $query->orderBy('new_itemlogs_tracker.entity_id', 'desc')->get();

	    return Excel::download(
	        new NewItemLogsTrackerExport($data),
	        'order_status_report_' . now()->format('Y-m-d_H-i-s') . '.xlsx',
	        \Maatwebsite\Excel\Excel::XLSX
	    );
	}

	public function exportPdf(Request $request)
	{
	    $subQuery = NewItemLogsTracker::selectRaw('MAX(entity_id) as max_id')
	        ->groupBy('unique_id');

	    $query = NewItemLogsTracker::leftJoin('datewise_order_report_remark as dorr', 'new_itemlogs_tracker.unique_id', '=', 'dorr.item_sku')
	        ->select('new_itemlogs_tracker.*', 'dorr.remark')
	        ->whereIn('new_itemlogs_tracker.entity_id', $subQuery)
	        ->where(function ($q) {
	            $q->where('new_itemlogs_tracker.shipped_number', '=', '')
	              ->orWhereNull('new_itemlogs_tracker.shipped_number');
	        })
	        ->where('refund', 0);

	    if ($request->filled('sub_status_status')) {
	        $query->where('new_itemlogs_tracker.sub_status_status', $request->input('sub_status_status'));
	    }

	    if ($request->filled('hold_status')) {
	        $query->where('new_itemlogs_tracker.hold_status', 'LIKE', $request->input('hold_status') . '%');
	    }

	    $data = $query->orderBy('new_itemlogs_tracker.entity_id', 'desc')->get();

	    $pdf = Pdf::loadView('reports.newitemlogstracker_pdf', compact('data'))
	        ->setPaper('a4', 'portrait')
	        ->setOptions(['isPhpEnabled' => true]);

	    return $pdf->download('order_status_report_' . now()->format('Y-m-d_H-i-s') . '.pdf');
	}
}