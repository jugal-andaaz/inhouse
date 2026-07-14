<?php

namespace Vanguard\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class FedExTrackingService
{
    public static function track(array|string $trackingNumbers, bool $includeDetailedScans = true): array
    {
        $token = FedExAuthService::getAccessTokenTracking();
        $numbers = is_array($trackingNumbers) ? $trackingNumbers : [$trackingNumbers];
        $trackingInfo = array_map(fn($n) => [
            'trackingNumberInfo' => ['trackingNumber' => $n],
        ], $numbers);

        $response = Http::withToken($token)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'X-locale'     => 'en_US',
            ])
            ->post(config('services.fedex.base_url') . '/track/v1/trackingnumbers', [
                'includeDetailedScans' => $includeDetailedScans,
                'trackingInfo'         => $trackingInfo,
            ]); 
        if (!$response->successful()) {
            throw new Exception('FedEx tracking failed: ' . $response->body());
        }

        return $response->json();
    }
}
