<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;

class HoldToUnholdSeen extends Model
{
    protected $table = 'hold_to_unhold_seen';

    protected $primaryKey = 'entity_id';

    public $timestamps = false; // since you have updated_at manually

    protected $fillable = [
        'order_id',
        'unique_id',
        'indicate',
        'updated_at',
    ];
}
