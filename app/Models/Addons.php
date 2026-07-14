<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;

class Addons extends Model
{
    protected $table = 'andaaz_addons'; 

    protected $fillable = ['title', 'sku', 'price', 'addoncategory'];
    protected $casts = ['addoncategory' => 'array',];
}
