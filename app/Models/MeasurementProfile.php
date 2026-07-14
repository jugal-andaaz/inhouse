<?php

namespace Vanguard\Models; 

use Illuminate\Database\Eloquent\Model;

class MeasurementProfile extends Model
{
    protected $table = 'mymeasurement_profile';
    
    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'height', 'bust', 'under_bust', 'shoulder', 'top_length', 'body_bust_size',
        'customer_note', 'arm_hole', 'front_neck_style', 'front_neck_depth',
        'back_neck_style', 'back_neck_depth', 'sleeve_length', 'blouse_length',
        'closing_side', 'closing_with', 'heels', 'adornment', 'hips', 'bottom_length',
        'dresskameez_length', 'waist', 'special_msg', 'type', 'profile_name', 'unit',
        'mtype', 'customer_id', 'created_date', 'updated_date', 'blousepad',
        'frontimg', 'sideimg', 'prestich', 'product_id', 'arround_belly_button',
        'arround_arm', 'modest_requirement', 'waist_type', 'upload_image', 'STATUS',
        'around_neck', 'thigh_length', 'crotch_length', 'mori_length',
        'calf_length', 'wrist_size', 'hps_under_bust'
    ];
}
