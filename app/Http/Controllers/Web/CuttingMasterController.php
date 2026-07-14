<?php
namespace Vanguard\Http\Controllers\Web;

use Illuminate\Http\Request;
use Vanguard\Models\CuttingMaster;

class CuttingMasterController extends Controller
{
    /**
     * Get all cutting records
     */
    public function index()
    {
        $data = CuttingMaster::orderBy('id', 'desc')->get();
        return response()->json($data);
    }

    /**
     * Get record by ID
     */
    public function show($id)
    {
        $data = CuttingMaster::findOrFail($id);
        return response()->json($data);
    }

    /**
     * Get records by order increment_id
     */
    public function getByIncrementId($incrementId)
    {
        $data = CuttingMaster::where('increment_id', $incrementId)->get();
        return response()->json($data);
    }

    /**
     * Store new record
     */
    public function store(Request $request)
    {
        $data = CuttingMaster::create($request->all());

        return response()->json([
            'message' => 'Cutting record created successfully',
            'data'    => $data
        ], 201);
    }
}
