<link rel="stylesheet" href="{{ asset('assets/css/orderdetails.css') }}" media="all" type="text/css">
@extends('layouts.app')

@section('page-title', 'SKU Order List')
@section('page-heading', 'SKU Order List')

@section('breadcrumbs')
<li class="breadcrumb-item active">
    @lang('Orders')
</li>
@stop

@section('content')
@include('partials.messages')
<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('skuorderlist') }}">
            <div class="form-group">
                <label for="sku">Enter SKU</label>
                <input type="text" name="sku" id="sku" value="{{ old('sku', $sku) }}" class="form-control" placeholder="Enter Product SKU" required>
            </div>
            <button type="submit" class="btn btn-primary mt-2">Search</button>
        </form>

        @if (!empty($items) && count($items))
            <hr>
            <h5 class="mt-4">Results for SKU: <strong>{{ $sku }}</strong></h5>
            <div class="row">
                <div class="col-12 @if (! isset($activities)) mx-auto @endif bg-white">
                    <div class="card  border-0">
                        <div class="table-responsive" id="users-table-wrapper">
                            @php                                
                                $orderIds = $items->pluck('order_id')->unique();
                                $orders = \Vanguard\Models\Order::whereIn('increment_id', $orderIds)->get();
                            @endphp

                            @foreach ($orders as $order)
                                @include('orders.partials.order-item-details', ['order' => $order])
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @elseif($sku)
            <div class="alert alert-warning mt-4">No records found for SKU: <strong>{{ $sku }}</strong></div>
        @endif
    </div>
</div>
@endsection
 