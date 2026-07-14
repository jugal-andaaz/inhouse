<?php

namespace Vanguard\Http\Controllers\Web;

use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Models\AppsheetToInhouse;
use Vanguard\Models\OrderDateWiseReportRemark;

class OrderDateWiseReportRemarkController extends Controller
{
    // Show all remarks
    public function index()
    {
        if(isset($_GET['uniqueid'])){
            $uniqueId   = $_GET['uniqueid'] ?? null;
            $collection = AppsheetToInhouse::where('unique_id', $uniqueId)->first();
            return view('reports.orderremarks', compact('collection', 'uniqueId'));
        } else{
        	$entityId = $_GET['entity_id'];
        	$collection = AppsheetToInhouse::where('entity_id', $entityId)->first();
            return view('reports.orderremarks', compact('collection', 'entityId'));
        }

        /*$entityId   = $_GET['entity_id'] ?? null;
        $collection = AppsheetToInhouse::where('unique_id', $entityId)->first(); */

    }

    // Store a new remark
    public function updateReportRemark(Request $request)
    {
        $request->validate([            
            'remark' => 'required|string|max:500',
        ]);

        /*OrderDateWiseReportRemark::updateOrCreate(// Match condition
        	['item_sku' => $request->item_sku],
        	[
        		'item_sku'   => $request->item_sku,
        		'remark'     => $request->remark,
        		'created_by' => auth()->id() ?? null,
        		'created_at' => now(),
        	]
        );*/
        /*OrderDateWiseReportRemark::where('item_sku', $request->item_sku)
        ->update([ 
            'item_sku'   => $request->item_sku,
            'remark'     => $request->remark,
            'created_by' => auth()->id() ?? null,
            'created_at' => now(),
        ]);*/

        $updated = OrderDateWiseReportRemark::where('item_sku', $request->item_sku)
            ->update([
                'remark'     => $request->remark,
                'created_by' => auth()->id() ?? null,
                'created_at' => now(),
            ]);

        if ($updated === 0) {
            // No records matched, create a new one
            OrderDateWiseReportRemark::create([
                'item_sku'   => $request->item_sku,
                'remark'     => $request->remark,
                'created_by' => auth()->id() ?? null,
                'created_at' => now(),
            ]);
        }
        //return redirect()->back()->with('success', 'Remark added successfully.');
        return "<script>
            window.opener.location.reload();
            window.close();
        </script>";
    }

    // Show remarks for a specific order
    public function show($entityId)
    {
        $remarks = OrderDateWiseReportRemark::where('entity_id', $entityId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('reports.datewise_remarks.show', compact('remarks', 'orderId'));
    }
}