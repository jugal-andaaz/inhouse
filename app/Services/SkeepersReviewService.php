<?php
namespace Vanguard\Services;

use Illuminate\Support\Facades\Http;
use Vanguard\Models\ProductReviewSkeeper;

class SkeepersReviewService
{
    public static function fetchAndStore(string $from, string $to): int
    {
        $token = SkeepersAuthService::getAccessToken();

        $url = 'https://api.skeepers.io/verified-reviews/v1/published/products/reviews';

        $response = Http::withToken($token)
            ->get($url, [
                'publish_date.gte' => $from . 'T00:00:00Z',
                'publish_date.lt'  => $to . 'T23:59:59Z',
                'limit'            => 200,
            ]);

        if (!$response->successful()) {
            throw new \Exception($response->body());
        }

        $count = 0;

        foreach ($response->json() as $review) {

            ProductReviewSkeeper::updateOrCreate(
                ['review_id' => $review['review_id']],
                [
                    'order_id' => $review['order_id'] ?? null,
                    'order_reference' => $review['order_reference'] ?? null,
                    'product_sku' => $review['product_sku'] ?? null,
                    'product_variation_id' => $review['product_variation_id'] ?? null,
                    'product_name' => $review['product_name'] ?? null,
                    'product_brand' => $review['product_brand'] ?? null,
                    'product_upc' => $review['product_upc'] ?? null,
                    'product_mpn' => $review['product_mpn'] ?? null,
                    'product_ean' => $review['product_ean'] ?? null,
                    'product_jan' => $review['product_jan'] ?? null,
                    'product_image_url' => $review['product_image_url'] ?? null,
                    'product_page_url' => $review['product_page_url'] ?? null,
                    'author_firstname' => $review['author_firstname'] ?? null,
                    'author_lastname' => $review['author_lastname'] ?? null,
                    'review_content' => $review['review_content'] ?? null,
                    'review_rate' => $review['review_rate'],
                    'locale' => $review['locale'] ?? 'en',
                    'is_verified' => $review['is_verified'] ?? 0,
                    'syndicated_review' => $review['syndicated_review'] ?? 0,
                    'is_personal_data_disclosed' => $review['is_personal_data_disclosed'] ?? 0,
                    'incentivization' => $review['incentivization'] ?? null,
                    'order_date' => $review['order_date'] ?? null,
                    'review_date' => $review['review_date'] ?? null,
                    'publish_date' => $review['publish_date'] ?? null,
                ]
            );
            $count++;
        }
        return $count;
    }
}