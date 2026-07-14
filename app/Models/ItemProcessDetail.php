<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;

class ItemProcessDetail extends Model
{
    protected $table = 'andaaz_item_process_detail';
    public $timestamps = false; // disable Laravel's created_at/updated_at

    protected $fillable = [
        'product_item_id',
        'status_type',
        'status_subtype',
        'created_date',
        'created_by',
        'status',
        'given_to',
        'qty',
        'price',
        'amount',
        'pending',
        'challan_no',
        'fabric_length',
        'fabric_type',
        'new_id',
        'product_complete_date',
        'updated_date',
        'updated_by',
        'qc_status',
        'reason',
        'remark',
        'qc_reject_part',
    ];
}
