@php
    $billingAddress = getOldOrderAddressByType($order->increment_id, 'billing');
@endphp

@if (is_object($billingAddress))
    @php
        $countryName = getCoutryNameByCode($billingAddress->country_id);
    @endphp

    <ul class="list-group list-group-flush">
        <li class="ulhead p-2">@lang('Billing Address')</li>
        <li class="list-group-item">
            {{ $billingAddress->firstname }} {{ $billingAddress->lastname }}
        </li>
        @if(auth()->user()->hasPermission('order.addresscxinfo'))
            <li class="list-group-item">
                <span class="mb-2 w-100 d-block">{{ $billingAddress->street }}</span>
                <span class="mb-2 d-block">{{ $billingAddress->city }}</span>
                <span class="d-block">{{ $billingAddress->region }}</span>
                <span class="d-block">{{ $billingAddress->postcode }}</span>
                <span class="d-block">{{ $countryName }}</span>
                <span class="d-block">{{ $billingAddress->telephone }}</span>
            </li>
        @endif    
    </ul>
@endif
