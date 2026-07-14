<?php

namespace Vanguard\Repositories;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface OrderRepository {

    /**
     * Paginate registered users.
     */
    public function paginate(int $perPage, ?string $search = null, ?string $status = null): LengthAwarePaginator;

    public function all();
    
    public  function lists();
}
