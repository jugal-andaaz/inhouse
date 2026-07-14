<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;

class AndaazInhouseOrderItem extends Model
{
    protected $table = 'andaaz_inhouse_new';

    public $timestamps = false;

    protected $fillable = [
        'order_id', 'product_item_id', 'product_sku', 'mmt_pending_reason',
        'pending_reason', 'rr_further_action_reason', 'rr_further_action_reason_other',
        'qc_reason', // Add other fields as needed
    ];
}