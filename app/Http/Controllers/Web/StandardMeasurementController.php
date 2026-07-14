<?php

namespace Vanguard\Http\Controllers;

use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller; 
use Illuminate\Support\Facades\DB;
use App\Models\StandardMeasurement;

class StandardMeasurementController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|integer',
            'Bust_Size' => 'nullable|string|max:35',
            'Sleeve_Length' => 'nullable|string|max:35',
            'Dress_Top_Length' => 'nullable|string|max:35',
            'Body_Height' => 'nullable|string|max:35',
            'Created_By' => 'nullable|string|max:35',
            'Check_Bust_Size' => 'nullable|string|max:35',
            'Check_Sleeve_Length' => 'nullable|string|max:35',
            'Check_Dress_Top_Length' => 'nullable|string|max:35',
            'Check_Body_Height' => 'nullable|string|max:35',
            'measurement_type' => 'nullable|string|max:35',
        ]);

        $validated['Created_Date'] = now();

        StandardMeasurement::create($validated);

        return response()->json(['message' => 'Measurement saved successfully']);
    }
}
