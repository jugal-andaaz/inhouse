<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model {

    use HasFactory;

    protected $table = 'andaaz_order';
    protected $fillable = ['entity_id', 'order_status', 'increment_id', 'customer_email', 'customer_firstname', 'customer_lastname'];

    /**
     * {@inheritdoc}
     */
    protected function lists(string $column = 'name', string $key = 'id') {
        return [];
    }

    /**
     * 
     * @return type
     */
    protected function statusLists() {
        return [
            'pending' => 'Pending',
            'processing' => 'Processing',
            'cancelled' => 'Cancelled',
            'complete' => 'Complete'
        ];
    }
}
