@php
    $shippingAddress = getOrderAddressByType($order->increment_id, 'shipping');
@endphp

@if(is_object($shippingAddress))
    @php
        $countryName = getCoutryNameByCode($shippingAddress->country_id);
    @endphp

    <ul class="list-group list-group-flush">
        <li class="ulhead p-2">@lang('Shipping Address')</li>
        <li class="list-group-item">
            {{ $shippingAddress->firstname }} {{ $shippingAddress->lastname }}
        </li>
        @if(auth()->user()->hasPermission('order.addresscxinfo'))
            <li class="list-group-item">
                <span class="mb-2 w-100 d-block">{{ $shippingAddress->street }}</span>
                <span class="mb-2 d-block">{{ $shippingAddress->city }}</span>
                <span class="d-block">{{ $shippingAddress->region }}</span>
                <span class="d-block">{{ $shippingAddress->postcode }}</span>
                <span class="d-block">{{ $countryName }}</span>
                <span class="d-block">{{ $shippingAddress->telephone }}</span>
            </li>
            <li class="list-group-item">
                {{ $shippingAddress->email }}
            </li>
        @endif 

        @if(auth()->user()->hasPermission('order.countrynameinfo'))
            <li class="list-group-item">
                <span class="d-block">{{ $countryName }}</span>
            </li>
        @endif

    </ul>
@endif
