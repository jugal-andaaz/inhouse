<?php

namespace Vanguard\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Exception;

class SkeepersAuthService
{
    public static function getAccessToken(): string
    {
        return Cache::remember('skeepers_access_token', 250, function () {

            $response = Http::asForm()
                ->withBasicAuth(
                    config('services.skeepers.client_id'),
                    config('services.skeepers.client_secret')
                )
                ->post(
                    'https://auth.skeepers.io/realms/skeepers/protocol/openid-connect/token',
                    [
                        'grant_type' => 'client_credentials',
                        'audience'   => 'verified-reviews',
                    ]
                );

            if (!$response->successful()) {
                throw new Exception('Token error: ' . $response->body());
            }

            return $response->json('access_token');
        });
    }
}
