<?php

namespace Vanguard\Http\Controllers\Web;

use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller; 
use Vanguard\Models\AndaazInhouseOrderItem; 

class PrintMeasurementController extends Controller
{
   public function show($id)
    {
        $item = AndaazInhouseOrderItem::findOrFail($id);

        return view('printmeasurement.show', compact('item'));
    }
}
