<?php
namespace Vanguard\Models; 

use Illuminate\Database\Eloquent\Model;

class ProductReviewSkeeper extends Model
{
    protected $table = 'product_reviews_skeepers';

    protected $fillable = [
        'review_id',
        'order_id',
        'order_reference',
        'product_sku',
        'product_variation_id',
        'product_name',
        'product_brand',
        'product_upc',
        'product_mpn',
        'product_ean',
        'product_jan',
        'product_image_url',
        'product_page_url',
        'author_firstname',
        'author_lastname',
        'review_content',
        'review_rate',
        'locale',
        'is_verified',
        'syndicated_review',
        'is_personal_data_disclosed',
        'incentivization',
        'order_date',
        'review_date',
        'publish_date',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'syndicated_review' => 'boolean',
        'is_personal_data_disclosed' => 'boolean',
        'order_date' => 'datetime',
        'review_date' => 'datetime',
        'publish_date' => 'datetime',
    ];
}
