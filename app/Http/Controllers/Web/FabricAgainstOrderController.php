<?php
namespace Vanguard\Http\Controllers\Web;

use Illuminate\Http\Request;
use Vanguard\Models\FabricAgainstOrder;

class FabricAgainstOrderController extends Controller
{
    /**
     * Get all records
     */
    public function index()
    {
        /*$data = FabricAgainstOrder::orderBy('id', 'desc')->get();
        return response()->json($data);*/
        $data = FabricAgainstOrder::orderBy('uniqueid', 'desc')
                ->get();

        return response()->json($data);
    }

    /**
     * Get single record by ID
     */
    public function show($id)
    {
        $data = FabricAgainstOrder::findOrFail($id);
        return response()->json($data);
    }

    /**
     * Get data by increment_id
     */
    public function getByIncrementId($incrementId)
    {
        $data = FabricAgainstOrder::where('increment_id', $incrementId)->get();
        return response()->json($data);
    }

    /**
     * Store new record
     */
    public function store(Request $request)
    {
        $data = FabricAgainstOrder::create($request->all());
        return response()->json([
            'message' => 'Record inserted successfully',
            'data' => $data
        ], 201);
    }
}
