<?php

namespace Vanguard\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Models\ShipmentTracking;

class FedExTrackingController extends Controller
{
    public function show(string $trackingNumber): JsonResponse
    {
        $record = ShipmentTracking::fedex()
            ->where('tracking_number', $trackingNumber)
            ->firstOrFail();

        return response()->json($record);
    }
}
