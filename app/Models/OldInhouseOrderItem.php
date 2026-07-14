<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;

class OldInhouseOrderItem extends Model
{ 
    protected $table = 'old_andaaz_inhouse_new';
    protected $fillable = [
        'id','product_sku', 'product_item_id','order_id','product_name', 'product_price', 'quantity', 
    ];
}
