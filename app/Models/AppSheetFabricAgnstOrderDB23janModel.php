<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;

class AppSheetFabricAgnstOrderDB23janModel extends Model
{
    protected $connection = 'inhousedb23jansql';
    protected $table = 'fabric_agnst_order';

    protected $primaryKey = 'uniqueid';
    
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false; // because only updated_at exists

    protected $guarded = [];
}
