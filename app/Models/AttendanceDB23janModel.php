<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceDB23janModel extends Model
{
    protected $connection = 'adzappsheetsql';
    protected $table = 'attendance';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $guarded = [];
}
