<?php
namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StitchingToPp extends Model
{
    use HasFactory;

    protected $table = 'stitching_to_pp';

    protected $primaryKey = 'id';

    public $timestamps = false; // custom timestamp column exists

    protected $fillable = [
        'process_id',
        'dress_type',
        'type',
        'unique_Id',
        'increment_id',
        'product_sku',
        'image_url',
        'allocate_tailor',
        'emp_id_tailor',
        'emp_Id_allocatort',
        'enter_stitching_time',
        'fstart',
        'fhold',
        'ftime_consumed',
        'sstart',
        'shold',
        'stime_consumed',
        'thstart',
        'thhold',
        'thtime_consumed',
        'frtstart',
        'frthold',
        'frttime_consumed',
        'fvstart',
        'stitching_finished',
        'fvtime_consumed',
        'total_time',
        'remark',
        'nfinishing_doer',
        'tfinishing',
        'measurment_checks',
        'finishing_checks',
        'image_checks',
        'website_description_checks',
        'customer_comment_checks',
        'final_quality_status',
        'qc_remark',
        'approval_given_by',
        'mistake_source',
        'qc_done_by',
        'if_alter_require',
        'timestamp_pressing_packing'
    ];
}
