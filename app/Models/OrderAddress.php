<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderAddress extends Model
{
    protected $primaryKey = 'id'; // if the primary key is not the default 'id'
    protected $table = 'order_address'; // if the table name is custom
}

