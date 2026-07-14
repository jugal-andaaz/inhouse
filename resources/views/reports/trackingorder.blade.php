@extends('layouts.app')

@section('page-title', __('Order Tracking Report'))
@section('page-heading', __('Order Tracking Report'))

<link rel="stylesheet" href="{{ asset('assets/css/orderdetails.css') }}" media="all" type="text/css">
@section('breadcrumbs')
    <li class="breadcrumb-item active">@lang('Orders')</li>
@endsection

@section('content')
    @include('partials.messages')

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-12 @if (! isset($activities)) mx-auto @endif bg-white">
                    <div class="card border-0">
                        <div class="table-responsive" id="users-table-wrapper">
                            <table class="table table-borderless table-striped statement-table ordertop-info">
                                <tbody>
                                    <tr class="statement-row  span">
                                        <td class="statement-td" data-label="Shipping Address">
                                            <!-- <ul class="list-group list-group-flush">
                                                <li class="ulhead p-2">Running Order</li>
                                                <li class="list-group-item totalcountblak" style="background-color: #2bc96be3;">
                                                    <span>count </span>
                                                </li>
                                            </ul>
                                            <ul class="list-group list-group-flush">
                                                <li class="ulhead p-2">Total Delayed</li>
                                                <li class="list-group-item totalcountblak" style="background-color: #fff000;">
                                                    <span>count_dispatch</span>
                                                </li>
                                            </ul>
                                            <ul class="list-group list-group-flush">
                                                <li class="ulhead p-2">Total Expedited</li>
                                                <li class="list-group-item totalcount" style="background-color: #970303ba;">
                                                    <span>count_expedite</span>
                                                </li>
                                            </ul>
                                            <ul class="list-group list-group-flush">
                                                <li class="ulhead p-2">Total Express Shipping</li>
                                                <li class="list-group-item totalcount" style="background-color: #e38613;">
                                                    <span>count_express_shipping</span>
                                                </li>
                                            </ul>
                                            <ul class="list-group list-group-flush">
                                                <li class="ulhead p-2">Total HOLD</li>
                                                <li class="list-group-item totalcount" style="background-color: #b93e0fba;">
                                                    <span>count_hold</span></li>
                                            </ul> -->
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="float-right mr-2 ml-2">
                                <a href="{{ route('orderstatus.export', ['sub_status_status' => request('sub_status_status')]) }}"
                                    class="btn btn-success p-0"><img src="/images/Excel.jpg" height="40px;"></a> 
                            </div>
                            <div class="float-right mr-2 ml-2">
                                <a href="{{ route('orderstatus.exportPdf', ['sub_status_status' => request('sub_status_status')]) }}"
                                    class="btn btn-success p-0"><img src="/images/pdf-icon.png" height="40px"> 
                                </a> 
                            </div>
                             

<table class="table table-hover table-condensed table-striped statement-table ordertop-info table-bordered" id="ordertrack">
    <thead>
        <tr>
            <!-- <th>Entity ID</th>
            <th>Unique ID</th> -->
            <th>Increment ID</th>
            <th>Product SKU</th>
            <th>Dispatch</th>
            <th>Occation</th>
            <th>CO</th>
            <th>Dress Type</th>
            <th>Dress Style</th>
            <th>Qty</th>
            <th>Item Amount</th>
            <th>First Allocated</th>
            <th>Order Status</th>
            <th>Created At</th>
            <!-- Add all columns you want to show -->
        </tr>
    </thead>

    <tbody> 
        @foreach ($orders as $order)
            <tr>
                <!-- <td>{{ $order->entity_id }}</td>
                <td>{{ $order->unique_id }}</td> -->
                <td style="width: 150px;">{{ $order->increment_id }}</td>
                <td style="width: 135px;">
                    <span style="font-weight: 700;border-bottom: 1px solid;display: inline-block;">
                        {{ $order->product_sku }}
                    </span><br>
                    <img src="{{ $order->image_url }}" style="max-width: 75px;"> 
                </td>
                @php
                    $dispatchDate = date('d-m-Y', strtotime($order->dispatch_date_formatted));
                @endphp
                @if(\Carbon\Carbon::parse($dispatchDate)->lte(\Carbon\Carbon::today()))
                    <td style="width: 103px;background-color: #ff0000;color: #fff;font-weight: 700;font-size:15px;">
                        {{ $dispatchDate }}
                    </td>
                @else
                    <td style="width: 103px;">{{ $dispatchDate }}</td>
                @endif
                <td style="width: 135px;">
                    {{ $order->occation }}
                </td>
                <td style="width: 200px;">
                    {{ $order->coordinator_name }}
                </td>

                <td>{{ $order->dress_type }}</td>
                <td>{{ $order->dress_style }}</td>
                <td>{{ $order->product_qty }}</td>
                <td>{{ $order->order_currency_code }} {{ $order->item_amount }}</td>
                <td>{{ $order->firstallocated }}</td>

                <td style="width: 100px;">{{ $order->order_item_status }}</td>
                <td>{{ $order->created_at }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
<div class="pagination">
    {{ $orders->links() }}
</div>


                        </div>
                    </div>
                </div>
            </div>
        </div> 
    </div>
@endsection
@section('scripts')
<script>
    $("#status").change(function () {
        $("#orderstatus-reportform").submit();
    });
</script>
@stop
<style> 
    .active-sort { font-weight: bold; color: #007bff; /* highlight blue */text-decoration: underline;}
    .ordertop-info th,.ordertop-info td {font-size: 13px;}
    .table thead th, .ulhead {color: #fff !important;background: #30353e; list-style: none;font-weight: 700;}
    .ordertop-info ul {max-width: 19%;display: inline-block;text-align: center;width: 100%;vertical-align: top;margin-right: 5px;border: 1px dotted;}
    .totalcount{color: #FFFFFF; font-size:28px;} 
    .totalcountblak{color: #000000; font-size:28px;} 
    #ordertrack td{text-align: center;vertical-align: middle;}
</style>