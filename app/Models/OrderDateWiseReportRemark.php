<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDateWiseReportRemark extends Model
{
    protected $table = 'datewise_order_report_remark';

    public $timestamps = false;

    protected $fillable = [
        'datewisereport_id',
        'item_sku',
        'remark',
        'created_by',
        'created_at'
    ];
    protected $primaryKey = 'datewisereport_id';
    public $incrementing = false;
    protected $keyType = 'string';
}
