<?php
namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FabricAgainstOrder extends Model
{
    use HasFactory;

    protected $table = 'fabric_agnst_order';

    protected $primaryKey = 'id';

    public $timestamps = false; // because you already have custom timestamp columns

    protected $fillable = [
        'unique_Id',
        'increment_id',
        'product_sku',
        'product_qty',
        'image_url',
        'scan_required_fabric',
        'fabric_name_py',
        'select_dress_part',
        'select_sub_part',
        'sugestion_type',
        'fabric_issue_for',
        'suggested_by',
        'enter_qty_to_deduct',
        'timestamp_qty',
        'dying_require',
        'qty_given_by',
        'given_for_dye',
        'timestamp_gdye',
        'colur_as_per_Image',
        'saanth_Patta',
        'color_fastness_to_rubbing',
        'used_fabric_is_okay',
        'redye_is_require',
        'dyer_name',
        'dye_actual',
        'fabric_quality',
        'dye_quality',
        'approved_by',
        'dev_remark',
        'dev_qc_stimestamp',
        'dye_colur_qc',
        'prabir_remark',
        'prabir_qc_stimestatus',
        'confirmation_of_bag_processing',
        'bg_timestamp',
        'bag_done_by',
        'checked_by_samim',
        'cbs_timestamp',
        'mens_ladies',
        'rate_per_mtr',
        'fabric_cost',
        'item_id_count',
        'appsheet_status'
    ];
}