<?php

namespace Vanguard\Exports;

use Vanguard\Models\NewItemLogsTracker;
use Illuminate\Contracts\View\View; 
use Maatwebsite\Excel\Concerns\WithHeadings; 

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Collection;


class OrderStatusExport implements FromCollection, WithHeadings
{ 

    protected $subStatus;

    protected $data;

    public function __construct(Collection $data,$subStatus = null)
    {
        $this->data = $data;
        $this->subStatus = $subStatus;
    }

    public function collection()
    {
        return $this->data;
    } 

    public function headings(): array
    {
        return [
            'Entity ID',
            'Order ID',
            'SKU',
            'Unique ID',
            'Dispatch Date',
            'Occasion',
            'Source',
            'Expedition Status',
            'Status Location',
            'Order Coordinator',
            'Express Delivery',
            'Sub Status',
            'Hold Status',
            'Hold Reason',
            'Check List Coordinator',
            'Given For',
            'Created At',
            'Doer Name','',
            'Remarks'
        ];
    }
}
