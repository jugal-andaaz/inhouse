<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;

class OldAndaazOrderStatusHistory extends Model
{
    protected $table = 'old_andaaz_order_status_history';
    public $timestamps = false;

    protected $fillable = [
        'parent_id',
        'is_customer_notified',
        'is_visible_on_front',
        'comment',
        'status',
        'created_at',
        'entity_name'
    ];
}
