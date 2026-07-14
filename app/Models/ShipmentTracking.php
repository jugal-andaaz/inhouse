<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentTracking extends Model
{
    protected $table = 'shipment_tracking';

    protected $fillable = [
        'carrier',
        'tracking_number',
        'order_id',
        'unique_id',
        'product_sku',
        'customer_email',
        'status',
        'status_description',
        'service_type',
        'origin',
        'destination',
        'estimated_delivery',
        'actual_delivery',
        'scan_events',
        'reference_no',
        'booking_date',
        'package_type',
        'consignee',
        'packages',
        'tracking_no',
        'tracking_history',
        'error_message',
        'last_fetched_at',
    ];

    protected $casts = [
        'scan_events'        => 'array',
        'tracking_history'   => 'array',
        'estimated_delivery' => 'datetime',
        'actual_delivery'    => 'datetime',
        'last_fetched_at'    => 'datetime',
    ];

    public function scopeEps($query)
    {
        return $query->where('carrier', 'EPS');
    }

    public function scopeFedex($query)
    {
        return $query->where('carrier', 'FEDEX');
    }
}
