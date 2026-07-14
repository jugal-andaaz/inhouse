<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHP.php to edit this template
 */

namespace Vanguard\Repositories;

use Vanguard\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentOrderRepository implements OrderRepository {

    /**
     * 
     * @return type
     */
    public function all() {
        return Order::all();
    }

    /**
     * 
     * @return type
     */
    public function lists():array {
        $orders = Order::pluck('name', 'id');
        return $orders;
    }

    /**
     * {@inheritdoc}
     */
    public function paginate(int $perPage, ?string $search = null, ?string $status = null): LengthAwarePaginator {
        $query = Order::query();

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            (new UserKeywordSearch)($query, $search);
        }

        $result = $query->orderBy('id', 'desc')
                ->paginate($perPage);

        if ($search) {
            $result->appends(['search' => $search]);
        }

        if ($status) {
            $result->appends(['status' => $status]);
        }

        return $result;
    }
}
