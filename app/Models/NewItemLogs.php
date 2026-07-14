<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;

class NewItemLogs extends Model
{
    protected $table = 'new_item_logs';
    protected $primaryKey = 'entity_id';
    public $timestamps = false;

    protected $fillable = [
        'unique_id',
        'andaaz_order_id',
        'product_sku',
        'updated_by',
        'doername',
        'location',
        'sub_loaction',
        'next_sub_location',
        'source',
        'type',
        'shipped_number',
        'manwear',
        'updated_at',
    ];
}
