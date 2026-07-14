<?php

namespace Vanguard\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Exception;

class FedExAuthService
{
    public static function getAccessTokenTracking(): string
    {
        return Cache::remember('fedex_access_token', 300, function () {
            $response = Http::asForm()->post(
                config('services.fedex.base_url') . '/oauth/token',
                [
                    'grant_type'    => 'client_credentials',
                    'client_id'     => config('services.fedex.client_id'),
                    'client_secret' => config('services.fedex.client_secret'),
                ]
            );

            if (!$response->successful()) {
                throw new Exception('FedEx auth failed: ' . $response->body());
            }
            return $response->json('access_token');
        });
    }
    public static function getAccessToken(): string
    {
        return Cache::remember('fedexftb_access_tokens', 300, function () {
            $response = Http::asForm()->post(
                config('services.fedexftb.base_url') . '/oauth/token',
                [
                    'grant_type'    => 'client_credentials',
                    'client_id'     => config('services.fedexftb.client_id'),
                    'client_secret' => config('services.fedexftb.client_secret'),
                ]
            );
            if (!$response->successful()) {
                throw new Exception('FedEx auth failed: ' . $response->body());
            }
            return $response->json('access_token');
        });
    }
}
