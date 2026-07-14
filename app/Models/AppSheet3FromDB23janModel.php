<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;

class AppSheet3FromDB23janModel extends Model
{
    protected $connection = 'inhousedb23jansql';
    protected $table = 'stitching_to_pp';

    protected $primaryKey = 'id';

    public $timestamps = false; // because only updated_at exists

    protected $guarded = [];
}
