<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;

class AppSheet6FromDB23janModel extends Model
{
    protected $connection = 'inhousedb23jansql';
    protected $table = 'App_item_logs';

    protected $primaryKey = 'entity_id';

    public $timestamps = false; // because only updated_at exists

    protected $guarded = [];
}
