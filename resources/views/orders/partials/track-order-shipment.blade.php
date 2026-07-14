@php
    $products = getItemsByOrderId($order->increment_id);
@endphp
<table class="table table-responsive table-bordered table-striped statement-table product-tbl-font">
    <thead>
        <tr class="d-none d-md-table-row">
            <th scope="col" class="statement-th">@lang('SKU')</th>
            <th scope="col" class="statement-th">@lang('Tracking ID')</th>
            <th scope="col" class="statement-th">@lang('Courier')</th>
            <th scope="col" class="statement-th">@lang('Dispatch Date')</th>
        </tr>
    </thead>
    <tbody>
    @foreach($products as $product)
        <tr class="d-block d-md-table-row">
            <td class="statement-td d-block d-md-table-cell mb-3 td" data-label="@lang('SKU')">
                {{ $product->product_sku }}
            </td>
            <td class="statement-td d-block d-md-table-cell mb-3 td" data-label="@lang('Tracking ID')">
                {{ $product->product_tracking }}
            </td>
            <td class="statement-td d-block d-md-table-cell mb-3 td" data-label="@lang('Courier')">
                {{ $product->product_courier }}
            </td>
            <td class="statement-td d-block d-md-table-cell mb-3 td" data-label="@lang('Dispatch Date')">
                {{ $product->product_dispatch_date }}
            </td>
        </tr>
    @endforeach    
    </tbody>
</table>
           