@php
    $customerComments = getOldCustomerCommentsOrderStatusHistory($order->entity_id, 1, 1);
@endphp

@if (count($customerComments) > 0)
    <style>
        .note-table { border: 1px solid #ccc;display: inline-block;width: 100%; }
        .note-table p { margin: 10px; }
        .note-table td { text-align: center; padding: 0.1rem !important; }
        .btn-outline-success:hover { background-color: transparent !important; border-color: #198754 !important; }
        .equal-width-btn { width: 100%; height: auto; } 
    </style>
    <table class="table table-bordered table-striped statement-table">
        <thead>
            <tr class="statement-row">
                <th class="statement-th col-3 min-width-50 greenshade">Customer Note</th> 
            </tr>
        </thead>
    @foreach ($customerComments as $customerComment)
        <tbody>
            <tr class="statement-row">
                <td class="statement-td ordertotal-info high-lightbg lnh-20" data-label="Order Total">
                     {{ $customerComment->customer_note }}
                </td> 
            </tr>
        </tbody>
    @endforeach
    </table>
@endif 