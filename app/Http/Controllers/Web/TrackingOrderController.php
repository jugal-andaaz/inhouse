<?php
namespace Vanguard\Http\Controllers\Web;

use Vanguard\Models\TrackingOrder;
use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
/*use Vanguard\Exports\OrderStatusExport;*/
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelType;
/*use App\Models\OrderDateWiseReportRemark;*/
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class TrackingOrderController extends Controller {

    public function index(Request $request)
    {
        $orders = TrackingOrder::where('order_item_status', 'Invoiced')
		    ->selectRaw("*, DATE_FORMAT(STR_TO_DATE(dispatch_date, '%e %b %Y'), '%d-%m-%Y') AS dispatch_date_formatted")
		    ->orderByRaw("STR_TO_DATE(dispatch_date, '%e %b %Y') ASC")
		    ->paginate(20);
		return view('reports.trackingorder', [
	        'orders' => $orders
	    ]);
    }
}
