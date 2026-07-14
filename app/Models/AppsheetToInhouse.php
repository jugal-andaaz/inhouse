<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;

class AppsheetToInhouse extends Model
{
    protected $table = 'appsheet_to_inhouse';
    public $timestamps = false;

    protected $fillable = [
        'entity_id',
        'unique_id',
        'dispatch_date',
        'occassion',
        'source',
        'expendition_status',
        'statuslocation',
        'order_coordinator',
        'express_delivery',
        'sub_status_status',
        'hold_status',
        'hold_reason',
        'check_list_coordinator',
        'given_for',
        'updated_at',
        'doer_name',
        'order_id',
        'andaaz_order_id',
        'item_sku',
        'shipped_number',
        'dispatch_status',
        'manwear',
        'refund'
    ];
}
