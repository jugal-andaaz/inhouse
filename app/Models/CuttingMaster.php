<?php
namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CuttingMaster extends Model
{
    use HasFactory;

    protected $table = 'cutting_master';

    protected $primaryKey = 'id';

    public $timestamps = false; // you already have custom timestamp column

    protected $fillable = [
        'process_id',
        'dress_type',
        'type',
        'unique_id',
        'increment_id',
        'product_sku',
        'height',
        'image_url',
        'allocate_master',
        'emp_id_master',
        'emp_id_allocator',
        'Image_of_measurment',
        'pattern',
        'cutting_finished_tmsp',
        'cutting_done_by'
    ];

    protected $casts = [
        'cutting_finished_tmsp' => 'datetime',
    ];
}
