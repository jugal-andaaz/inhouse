<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;

class StitchingToPPTestModel extends Model
{
    protected $connection = 'inhousedb23jansql';
    protected $table = 'stitchingToPPTest';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $guarded = [];
}
