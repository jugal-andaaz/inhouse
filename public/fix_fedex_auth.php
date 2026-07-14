<?php
if (($_GET['run'] ?? '') !== 'FxAuth_2026') { http_response_code(403); exit('Forbidden'); }

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo '<pre>';

// 1. Show current config (mask secrets)
$clientId     = config('services.fedexftb.client_id') ?? '(null)';
$clientSecret = config('services.fedexftb.client_secret') ?? '(null)';
$baseUrl      = config('services.fedexftb.base_url') ?? '(null)';
$documentUrl  = config('services.fedexftb.document_url') ?? '(null)';
$accountNo    = config('services.fedexftb.account_number') ?? '(null)';

echo "=== FedEx FTB Config ===\n";
echo "client_id     : " . substr($clientId, 0, 8) . "...\n";
echo "client_secret : " . substr($clientSecret, 0, 6) . "...\n";
echo "base_url      : $baseUrl\n";
echo "document_url  : $documentUrl\n";
echo "account_number: $accountNo\n";

// 2. Show cached token (if any)
$cached = \Illuminate\Support\Facades\Cache::get('fedexftb_access_tokens');
echo "\n=== Cached Token ===\n";
echo $cached ? "EXISTS: " . substr($cached, 0, 20) . "...\n" : "NONE (will fetch fresh)\n";

// 3. Clear token + config cache
\Illuminate\Support\Facades\Cache::forget('fedexftb_access_tokens');
\Illuminate\Support\Facades\Cache::flush();
\Artisan::call('config:clear');
\Artisan::call('view:clear');
echo "\n=== Caches Cleared ===\n";
echo "Token cache   : cleared\n";
echo "App cache     : cleared\n";
echo "Config cache  : cleared\n";

// 4. Try fresh token fetch
echo "\n=== Fresh Token Test ===\n";
try {
    $response = \Illuminate\Support\Facades\Http::asForm()->post(
        $baseUrl . '/oauth/token',
        [
            'grant_type'    => 'client_credentials',
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
        ]
    );
    if ($response->successful()) {
        $token = $response->json('access_token');
        echo "Token fetch   : OK\n";
        echo "Token preview : " . substr($token, 0, 20) . "...\n";
        echo "Expires in    : " . $response->json('token_type') . " / " . $response->json('scope') . "\n";
    } else {
        echo "Token fetch   : FAILED (HTTP " . $response->status() . ")\n";
        echo "Response      : " . $response->body() . "\n";
    }
} catch (Exception $e) {
    echo "Token fetch   : EXCEPTION: " . $e->getMessage() . "\n";
}

echo '</pre>';
