<?php
namespace Vanguard\Http\Controllers\Web;

use Vanguard\Models\NewItemLogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Vanguard\Http\Controllers\Controller;

class NewItemLogsController extends Controller {

    public function index(Request $request)
    {
        $filterOrderId         = trim($request->input('order_id', ''));
        $filterUniqueId        = trim($request->input('unique_id', ''));
        $filterLocation        = $request->input('location');
        $filterSource          = $request->input('source');
        $filterSubLocation     = $request->input('sub_location');
        $filterNextSubLocation = $request->input('next_sub_location');

        // Cache the expensive "latest entity_id per unique_id" scan (2.4M rows → 400 IDs)
        // Refreshed every 5 minutes; bust manually after cron writes by calling Cache::forget('newitemlogs_max_ids')
        $maxIds = Cache::remember('newitemlogs_max_i1ds', 300, function () {
            return NewItemLogs::select(DB::raw('MAX(entity_id) as max_id'))
                ->where(function ($q) {
                    $q->whereNull('shipped_number')
                      ->orWhere('shipped_number', 'Invoiced')
                      ->orWhere('shipped_number', '');
                })
                ->groupBy('unique_id')
                ->pluck('max_id')
                ->all();
        });

        // PK lookup on ~400 IDs — instant regardless of table size
        $query = NewItemLogs::whereIn('new_item_logs.entity_id', $maxIds)
            ->leftJoin('employee_master as em_updated', DB::raw('LOWER(em_updated.employee_id)'), '=', DB::raw('LOWER(new_item_logs.updated_by)'))
            ->leftJoin('employee_master as em_doer', DB::raw('LOWER(em_doer.employee_id)'), '=', DB::raw('LOWER(new_item_logs.doername)'))
            ->select('new_item_logs.*', 'em_updated.employee_name as employee_name', 'em_doer.employee_name as doer_employee_name');

        if ($filterOrderId) {
            $query->where('andaaz_order_id', 'LIKE', "%{$filterOrderId}%");
        }
        if ($filterUniqueId) {
            $query->where('unique_id', 'LIKE', "%{$filterUniqueId}%");
        }
        if ($filterLocation) {
            $query->where('location', $filterLocation);
        }
        if ($filterSource) {
            $query->where('source', $filterSource);
        }
        if ($filterSubLocation) {
            $query->where('sub_loaction', $filterSubLocation);
        }
        if ($filterNextSubLocation) {
            $query->where('next_sub_location', $filterNextSubLocation);
        } 

        // Count total matching records 
        $count = (clone $query)->count();

        $NewItemLogs = $query->orderBy('updated_at', 'DESC')->paginate(30)->withQueryString();

        // Dropdown values — built from the already-resolved 400 rows, cached separately
        $dropdowns = Cache::remember('newitemlogs_dropdown', 300, function () use ($maxIds) {
            $rows = NewItemLogs::whereIn('entity_id', $maxIds)
                        ->select('location', 'source', 'sub_loaction', 'next_sub_location')
                        ->get();
            return [
                'locations'        => $rows->pluck('location')->filter()->unique()->sort()->values(),
                'sources'          => $rows->pluck('source')->filter()->unique()->sort()->values(),
                'subLocations'     => $rows->pluck('sub_loaction')->filter()->unique()->sort()->values(),
                'nextSubLocations' => $rows->pluck('next_sub_location')->filter()->unique()->sort()->values(),
            ];
        });

        return view('reports.newitemlogs', [
            'count'                 => $count,
            'newitemlogs'           => $NewItemLogs,
            'locations'             => $dropdowns['locations'],
            'sources'               => $dropdowns['sources'],
            'subLocations'          => $dropdowns['subLocations'],
            'nextSubLocations'      => $dropdowns['nextSubLocations'],
            'filterOrderId'         => $filterOrderId,
            'filterUniqueId'        => $filterUniqueId,
            'filterLocation'        => $filterLocation,
            'filterSource'          => $filterSource,
            'filterSubLocation'     => $filterSubLocation,
            'filterNextSubLocation' => $filterNextSubLocation,
        ]);
    }

    public function history(Request $request)
    {
        $uniqueId = trim($request->input('unique_id', ''));

        if (!$uniqueId) {
            return response()->json(['error' => 'unique_id required'], 422);
        }

        $records = NewItemLogs::where('new_item_logs.unique_id', $uniqueId)
            ->leftJoin('employee_master as em_updated', DB::raw('LOWER(em_updated.employee_id)'), '=', DB::raw('LOWER(new_item_logs.updated_by)'))
            ->leftJoin('employee_master as em_doer', DB::raw('LOWER(em_doer.employee_id)'), '=', DB::raw('LOWER(new_item_logs.doername)'))
            ->orderBy('new_item_logs.updated_at', 'DESC')
            ->orderBy('new_item_logs.entity_id', 'DESC')
            ->get(['new_item_logs.entity_id', 'new_item_logs.unique_id', 'new_item_logs.andaaz_order_id',
                   'new_item_logs.updated_by', 'new_item_logs.doername', 'new_item_logs.location',
                   'new_item_logs.sub_loaction', 'new_item_logs.next_sub_location', 'new_item_logs.source',
                   'new_item_logs.type', 'new_item_logs.shipped_number', 'new_item_logs.updated_at',
                   'em_updated.employee_name as employee_name', 'em_doer.employee_name as doer_employee_name']);

        return response()->json($records);
    }
}
