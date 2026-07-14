<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;

class MeasurementQueryEmail extends Model
{
    protected $table      = 'measurement_query_email';
    protected $primaryKey = 'order_id';
    public $incrementing  = false;
    protected $keyType    = 'string';

    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'order_inhouse_newid',
        'product_item_id',
        'product_type',
        'product_sku',
        'email_sent',
        'email_by',
        'email_to',
        'created_at',
    ];
}
