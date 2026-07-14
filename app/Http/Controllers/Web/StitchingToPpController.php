<?php
namespace Vanguard\Http\Controllers\Web;

use Illuminate\Http\Request;
use Vanguard\Models\StitchingToPp;

class StitchingToPpController extends Controller
{
    /**
     * Get all stitching records
     */
    public function index()
    {
        return response()->json(
            StitchingToPp::orderBy('id', 'desc')->get()
        );
    }

    /**
     * Get record by ID
     */
    public function show($id)
    {
        return response()->json(
            StitchingToPp::findOrFail($id)
        );
    }

    /**
     * Get records by order increment_id
     */
    public function getByIncrementId($incrementId)
    {
        return response()->json(
            StitchingToPp::where('increment_id', $incrementId)->get()
        );
    }

    /**
     * Store new stitching record
     */
    public function store(Request $request)
    {
        $data = StitchingToPp::create($request->all());

        return response()->json([
            'message' => 'Stitching record created successfully',
            'data'    => $data
        ], 201);
    }
}
