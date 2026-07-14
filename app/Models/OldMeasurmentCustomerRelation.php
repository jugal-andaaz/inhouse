<?php

namespace Vanguard\Models; 

use Illuminate\Database\Eloquent\Model;
use \Vanguard\Models\OldMeasurementProfile;

class OldMeasurmentCustomerRelation extends Model
{
    protected $table = 'old_mymeasurement_customerrelation';
    public $timestamps = false;

    protected $fillable = [
        'quote_id', 'order_no', 'customer_id', 'store_id',
        'quote_item_id', 'order_item_id', 'store',
        'mm_profile_id', 'created_date'
    ];

    public function profile()
    {
        return $this->belongsTo(OldMeasurementProfile::class, 'mm_profile_id', 'id');
    }
}
