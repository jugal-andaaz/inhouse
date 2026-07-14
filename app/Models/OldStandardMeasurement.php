<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;

class OldStandardMeasurement extends Model
{ 
    protected $table = 'old_standard_measurement';

    public $timestamps = false; // Because your table uses custom timestamp column

    protected $fillable = [
        'item_id',
        'Bust_Size',
        'Sleeve_Length',
        'Dress_Top_Length',
        'Body_Height',
        'around_neck',
        'Created_Date',
        'Created_By',
        'Check_Bust_Size',
        'Check_Sleeve_Length',
        'Check_Dress_Top_Length',
        'Check_Body_Height',
        'measurement_type',
    ];
}