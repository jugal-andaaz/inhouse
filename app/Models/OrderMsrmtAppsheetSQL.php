<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;

class OrderMsrmtAppsheetSQL extends Model
{
    protected $connection = 'inhousedb23jansql';
    protected $table = 'order_msrmt_appsheet';
    protected $primaryKey = 'entity_id';
    public $timestamps = false;

    protected $fillable = [
        'unique_id',
        'increment_id',
        'created_at',
        'dispatch_date',
        'product_sku',
        'order_item_status',
        'product_name',
        'dress_type',
        'dress_style',
        'product_qty',
        'Item_amount',
        'order_currency_code',
        'customer_note',
        'amount_ordered',
        'stitching_type',
        'height',
        'bust',
        'expendition',
        'date',
        'expedite_marked_by',
        'bag_prep_name',
        'bag_prep_by',
        'order_comment',
        'addons',
        'image_url',
        'occation',
        'coordinator_allocated_date',
        'firstallocated',
        'actual_allocation',
        'allocated_to',
        'allocated_by',
        'coordinator_name',
        'ticket_type',
        'current_ticket_status',
        'ticket_Comment',
        'customer_type',
        'before_after_eid',
        'location_to_hold',
        'next_date_fu',
        'last_action_date_oc',
        'mock_approval_require',
        'customer_firstname',
        'order_currency_customer_code',
        'item_row_Amount',
        'lastname',
        'customer_email',
        'payment_method',
        'updated_at',
    ];
}
