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
        @include('oldorders.partials.order-comments')
        @include('oldorders.partials.track-order-shipment')
    </div> 
    @php
        $productSkuRates = getCurrentProductReviewRate($order->increment_id);
    @endphp
    <table class="table table-borderless table-striped product-tbl-font">
        <thead>
            <th colspan="3" class="greenshade">Current Order's Review</th>
        </thead>
        <tbody>
            @foreach($productSkuRates as $productSkuRate)
            <tr>
                <td>{{$productSkuRate->product_sku}}</td>
                <td>{{$productSkuRate->review_content}}</td>
                <td>{{$productSkuRate->review_rate}}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @php
        $ordersBySameCustomer = getOldOrdersByCustomerEmail($order->customer_email);
    @endphp
    <div class="ordertotaldivold @if (! isset($activities)) mx-auto @endif bg-white p-1" style="margin: 0 !important;">
        <div class="table-responsive" id="users-table-wrapper">
            <table class="table table-bordered table-striped statement-table">
                <thead>
                    <tr class="statement-row">
                        <th class="statement-th col-3 min-width-50">@lang('Old Orders') 
                            <span style="float: right;"><strong>Email:</strong> {{ $order->customer_email }}</span>
                        </th> 
                    </tr>
                </thead>
                <tbody>
                    <tr class="statement-row">
                        <td class="statement-td ordertotal-info" data-label="@lang('Order Total')">
                            @if(is_iterable($ordersBySameCustomer))
                            <table class="table table-border table-striped product-tbl-font">
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
                                     @foreach ($ordersBySameCustomer as $ordercustomer)
                                        @if($ordercustomer->increment_id!==$order->increment_id)
                                            <tr>
                                                <td class="old-inhouse">
                                                    <a href="{{ route('oldorder.show', $ordercustomer->id) }}" target="__blank" data-toggle="tooltip" title="@lang('View Order')">
                                                        {{ $ordercustomer->increment_id }}
                                                    </a>
                                                </td>
                                                <td class="old-inhouse">{{ $ordercustomer->total_qty_ordered }} <input type="hidden" val='{{ $ordercustomer->total_item_count }}' /> </td>
                                                <td class="old-inhouse">{{ $ordercustomer->order_currency_code }} {{ number_format((float)$ordercustomer->grand_total,2) }}</td>
                                                <td class="old-inhouse">{{ parseDateFormat($ordercustomer->created_at) }}</td>
                                                <td class="old-inhouse">
                                                    @php
                                                        $productSkuRates = getCurrentProductReviewRate($ordercustomer->increment_id);                                                        
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
                        </td> 
                    </tr>
                </tbody>
            </table> 
        </div>
    </div>
</div>
<style>
    .oldordhead{font-weight: 700;}
    tbody .old-inhouse {background: beige;}
</style>