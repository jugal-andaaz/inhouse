<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;

class AppSheetRedyeDB23janModel extends Model
{
    protected $connection = 'inhousedb23jansql';
    protected $table = 'redye';

    protected $primaryKey = 'entity_id';

    public $timestamps = false;

    protected $guarded = [];
}
