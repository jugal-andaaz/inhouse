<?php
    $domainMap = [
        14 => 'www.andaazfashion.co.uk',
        1  => 'www.andaazfashion.com',
        11 => 'www.andaazfashion.com.my',
        2  => 'www.andaazfashion.fr' 
    ]; 
    $order->domain_name = $domainMap[$order->domain] ?? 'Unknown Domain'; 
?>
<ul class="list-group list-group-flush small">
    <li class="ulhead p-2">@lang('Others')</li>
    <li class="list-group-item incrementid">
        <strong>Order No:</strong> {{ $order->increment_id }}
    </li>
    <li class="list-group-item orderstatus">
        <strong>Order Status:</strong> {{ ucfirst($order->order_status) }}
    </li>
    <li class="list-group-item createdat">
        <strong>Date:</strong> {{ utcToIst($order->created_at, 'd-M-Y h:i A') }}
    </li>
    <li class="list-group-item">
        <strong>URL:</strong>
        <a href="https://{{ $order->domain_name }}" target="_blank">{{ $order->domain_name }}</a>
    </li>
    <li class="list-group-item">
        <strong>IP:</strong> {{ $order->ipaddress }}
    </li>
    
    @isset($order->updated_by)
        <li class="list-group-item">
            <strong>Paid By:</strong> {{ $order->updated_by }}
        </li>
    @endisset

    @if(!empty($order->discount_code) && $order->discount_code !== 'null')
        <li class="list-group-item">
            <strong>Discount Code:</strong> {{ $order->discount_code }}
        </li>
    @endif
</ul> 