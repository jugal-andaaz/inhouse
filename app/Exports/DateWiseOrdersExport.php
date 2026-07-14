<?php

namespace Vanguard\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DateWiseOrdersExport implements FromCollection, WithHeadings
{
    protected $from;
    protected $to;

    public function __construct($from = null, $to = null)
    {
        $this->from = $from;
        $this->to = $to;
    }

    public function collection()
    {
        $query = DB::table('andaaz_order')
            ->selectRaw('DATE(created_at) as order_date, COUNT(*) as total_orders, SUM(total_item_count) as total_items')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('order_date', 'asc');

        if ($this->from && $this->to) {
            $query->whereBetween(DB::raw('DATE(created_at)'), [$this->from, $this->to]);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return ['Order Date', 'Total Orders', 'Total Items'];
    }
}
