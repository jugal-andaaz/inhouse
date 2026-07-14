<tr>
    <td style="width:40px;vertical-align: middle;">
        <a href="{{ route('oldorder.show', $order->id) }}"
           data-toggle="tooltip" title="@lang('View Order')">
            {{ $order->increment_id }}
        </a>
    </td>
    <td class="align-middle">{{ $order->customer_firstname }} {{ $order->customer_lastname }}</td>
    <td class="align-middle">{{ utcToIst($order->created_at, 'd-M-Y h:i A') }}</td>
    <td class="align-middle">{{ $order->total_qty_ordered }}</td>
    <td class="align-middle">{!! getStatusByText($order->order_status) !!}</td>
    <td class="align-middle">
        {{ (is_null($order->discount_code) || trim(strtolower($order->discount_code)) === 'null' || trim($order->discount_code) === '') 
        ? '' : $order->discount_code }}
    </td>
    <td class="align-middle">{{ $order->discount_amount }} </td>
    <td class="align-middle">
        <small class="text-muted">{{$order->ordered_currency_code }}</small> 
        <b>{{ (float) $order->grand_total }}</b>
    </td>
    <td class="align-middle"> {{ getDomain($order->domain)}} </td>
</tr>