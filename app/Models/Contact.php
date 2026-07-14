<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'sku', 'price', 'description', 'image'];

    public function address()
    {
        return $this->hasOne(OrderAddress::class, 'orderid', 'orderid');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'orderid', 'orderid');
    }
}
