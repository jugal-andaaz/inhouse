<div class="ordertotal-info">
    <div class="ordertotaldiv @if (! isset($activities)) mx-auto @endif bg-white p-1">
        <div class="table-responsive" id="users-table-wrapper">
            <table class="table table-bordered table-striped statement-table">
                <thead>
                    <tr class="statement-row">
                        <th class="statement-th col-3 min-width-50">@lang('Order Total')</th> 
                    </tr>
                </thead>
                <tbody>
                    <tr class="statement-row">
                        <td class="statement-td ordertotal-info" data-label="@lang('Order Total')">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item incrementid">
                                    <strong>Total Qty: </strong><span>{{ $order->total_qty_ordered}}</span>
                                </li>
                                <li class="list-group-item orderstatus">
                                    <strong>Sub Total: </strong><span>{{ $order->order_currency_code }} {{ number_format((float)$order->subtotal,2) }}</span>
                                </li>
                                <li class="list-group-item createdat">
                                    <strong>Shipping (+): </strong><span>{{ $order->order_currency_code }} {{ number_format((float)$order->shipping_amount,2) }}</span>
                                </li>
                                <li class="list-group-item">
                                    <strong>Discount (-): </strong><span>{{ $order->order_currency_code }} {{ number_format((float)$order->discount_amount,2) }}</span>
                                </li>
                                <li class="list-group-item grandtotal">
                                    <strong>Grand Total: </strong><span>{{ $order->order_currency_code }} {{ number_format((float)$order->grand_total,2) }}</span>
                                </li>
                                <li class="list-group-item">
                                    <strong>Balance Amount: </strong><span>{{ $order->order_currency_code }} {{ number_format((float)$order->total_paid,2) }}</span>
                                </li>
                            </ul>
                        </td> 
                    </tr>
                </tbody>
            </table> 
        </div>
    </div>

    <div class="comment-trackinfo p-1">
        @include('orders.partials.order-comments')
        @include('orders.partials.track-order-shipment') 
    </div>
    @php
        $productSkuRates = getCurrentProductReviewRateNewOrder($order->increment_id);
    @endphp
    <table class="table table-borderless table-striped product-tbl-font">
        <thead>
            <th colspan="3" class="greenshade">Current Order's Review</th>
        </thead>
        <tbody>
            @foreach($productSkuRates as $productSkuRate)
            <tr>
                <td><strong>{{$productSkuRate->product_sku}}</strong></td>
                <td>{{$productSkuRate->review_content}}</td>
                <td>{{$productSkuRate->review_rate}}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div> 
@php 
    // Get orders
    $ordersBySameCustomer = getOrdersByCustomerEmail($order->customer_email);
    $oldOrdersBySameCustomer = getOldOrdersByCustomerEmail($order->customer_email);

    // Make sure we have arrays of increment IDs to check duplicates
    $oldIncrementIds = is_iterable($oldOrdersBySameCustomer)
        ? collect($oldOrdersBySameCustomer)->pluck('increment_id')->toArray()
        : [];
@endphp

<div class="ordertotaldiv1 @if(!isset($activities)) mx-auto @endif bg-white p-1" style="margin: 0 !important;">
    <div class="table-responsive" id="users-table-wrapper">
        <table class="table table-bordered statement-table">
            <thead>
                <tr class="statement-row">
                    <th class="statement-th col-3 min-width-50">
                        @lang('Old Orders') 
                        <span style="float: right;">
                            <strong>Email:</strong> {{ $order->customer_email }}
                        </span>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr class="statement-row">
                    <td class="statement-td ordertotal-info" data-label="@lang('Order Total')">
                    {{-- Display Old Orders --}}
                        @if(is_iterable($oldOrdersBySameCustomer))
                            <table class="table table-border table-striped product-tbl-font">
                                @if(count($oldOrdersBySameCustomer) > 1)
                                    <thead class="oldordhead">
                                        <td>Order ID</td>
                                        <td>Qty</td>
                                        <td>Amount</td>
                                        <td>Date</td>
                                        <td>Reviews</td>
                                    </thead>
                                @endif
                                <tbody> 
                                    @foreach($oldOrdersBySameCustomer as $oldOrder)
                                        @if($oldOrder->increment_id !== $order->increment_id)
                                            <tr>
                                                <td class="old-inhouse">
                                                    <a href="{{ route('oldorder.show', $oldOrder->id) }}" target="__blank" data-toggle="tooltip" title="@lang('View Order')">
                                                        {{ $oldOrder->increment_id }}
                                                    </a>
                                                </td>
                                                <td class="old-inhouse">{{ $oldOrder->total_qty_ordered }} <input type="hidden" val='{{ $oldOrder->total_item_count }}' /> </td>
                                                <td class="old-inhouse">{{ $oldOrder->order_currency_code }} {{ number_format((float)$oldOrder->grand_total,2) }}</td>
                                                <td class="old-inhouse">{{ parseDateFormat($oldOrder->created_at) }}</td> 
                                                <td>
                                                @php
                                                    $productSkuRates = getCurrentProductReviewRate($order->increment_id);                                                        
                                                @endphp
                                                @if(count($productSkuRates)>0)
                                                <table class="table table-bordered table-striped statement-table product-tbl-font" style="max-width: 899px;">
                                                    <tbody>
                                                    @foreach($productSkuRates as $productSkuRate)
                                                    <tr>
                                                        <td style="min-width: 175px;"><b>{{$productSkuRate->product_sku}}</b> ({{$productSkuRate->review_rate}})</td>
                                                        <td>{{$productSkuRate->review_content}}</td>
                                                    </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                                @endif
                                            </td> 
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody> 
                            </table>
                        @endif

                        @if(is_iterable($ordersBySameCustomer))
                            {{-- Display New Orders (excluding duplicates from old orders) --}}
                            <table class="table table-striped product-tbl-font">
                                @if(count($ordersBySameCustomer) > 1)
                                    <thead class="oldordhead">
                                        <td>Order ID</td>
                                        <td>Qty</td>
                                        <td>Amount</td>
                                        <td>Date</td>
                                        <td>Reviews</td>
                                    </thead>
                                @endif
                                <tbody> 
                                    @foreach($ordersBySameCustomer as $newOrder)
                                        @if($newOrder->increment_id !== $order->increment_id)
                                        <?php /*    && !in_array($newOrder->increment_id, $oldIncrementIds) */ ?>
                                        <tr>
                                            <td>
                                                <a href="{{ route('orders.show', $newOrder->id) }}" target="__blank" data-toggle="tooltip" title="@lang('View Order')">
                                                    {{ $newOrder->increment_id }}
                                                </a>
                                            </td>
                                            <td>{{ $newOrder->total_qty_ordered }} <input type="hidden" val='{{ $newOrder->total_item_count }}' /></td>
                                            <td>{{ $newOrder->order_currency_code }} {{ number_format((float)$newOrder->grand_total,2) }}</td> 
                                            <td>{{ parseDateFormat($newOrder->created_at) }}</td> 
                                            <td>
                                                @php
                                                    $productSkuRates = getProductReviewRate($newOrder->increment_id);
                                                @endphp
                                                @if(count($productSkuRates)>0)
                                                <table class="table statement-table product-tbl-font" style="max-width: 899px;">
                                                    <tbody>
                                                    @foreach($productSkuRates as $productSkuRate)
                                                    <tr>
                                                        <td style="min-width: 175px;"><b>{{$productSkuRate->product_sku}}</b> ({{$productSkuRate->review_rate}})</td>
                                                        <td>{{$productSkuRate->review_content}}</td>
                                                    </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                                @endif
                                            </td> 
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<style>
    .oldordhead{font-weight: 700;}
    tbody .old-inhouse,
    li.list-group-item.incrementid.old-inhouse {background: beige;}
</style>