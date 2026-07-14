<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;

class TrackingDB23janModel extends Model
{
    protected $connection = 'inhousedb23jansql';
    protected $table = 'order_msrmt_appsheet';

    protected $primaryKey = 'entity_id';

    public $timestamps = false;

    protected $guarded = [];
}
