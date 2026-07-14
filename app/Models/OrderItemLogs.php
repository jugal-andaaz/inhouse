<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemLogs extends Model
{
    protected $table = 'andaaz_order_log';
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'sku',
        'item_id',
        'product_item_id'
    ];
}
