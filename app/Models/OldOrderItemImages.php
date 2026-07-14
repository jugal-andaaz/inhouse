<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;

class OldOrderItemImages extends Model
{
    protected $table = 'old_andaaz_item_image';
    protected $fillable = [
        'id','name', 'path','pid','remark', 'reason', 'status', 
    ];
}
