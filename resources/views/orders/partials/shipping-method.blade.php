@php
    $paymentCollection = getOrderPaymentByOrderId($order->increment_id);
@endphp

<ul class="list-group list-group-flush">
    <li class="ulhead p-2">@lang('Payment & Shipping Method')</li>
    @if(empty($paymentCollection))
        <li class="list-group-item">Payment detail is not found</li>
    @else
        <li class="list-group-item">
            <p><strong><u>Payment Information</u></strong></p>
            <p style="line-height: 23px;font-size: 15px;">
                <strong>Paid By:</strong> {{ $paymentCollection->payment_method }}<br>
                <strong>Transaction ID:</strong> {{ $paymentCollection->transaction_id }}<br>
                <strong>The Order was placed using </strong> {{ $paymentCollection->currency_code }}
            </p>
        </li>
    @endif

    <li class="list-group-item" style="line-height: 23px;font-size: 16px;">
        <strong>Shipping Description:</strong><br>
        {{ $order->shipping_description }}
    </li>

    @if($orderShippingMethod === 'flatrate_flatrate')
        <li class="list-group-item" style="line-height: 23px;font-size: 16px;">
            {{-- BARCODE --}}
            <div class="w-100 text-center">
                <img src="https://inhouse.andaazfashion.com/images/express-delivery2.jpg"
                     width="50%"
                     alt="express delivery"
                     class="img-fluid">
            </div>
        </li>
    @endif
</ul>
