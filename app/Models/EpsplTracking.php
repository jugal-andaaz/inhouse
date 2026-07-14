<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;

class EpsplTracking extends Model
{
    protected $table = 'epspl_tracking';

    protected $fillable = [
        'awb_no',
        'customer_email',
        'order_id',
        'unique_id',
        'product_sku',
        'reference_no',
        'booking_date',
        'service_type',
        'package_type',
        'origin',
        'destination',
        'status',
        'consignee',
        'packages',
        'tracking_no',
        'tracking_history',
        'error_message',
        'last_fetched_at',
    ];

    protected $casts = [
        'tracking_history' => 'array',
        'last_fetched_at'  => 'datetime',
    ];
}
