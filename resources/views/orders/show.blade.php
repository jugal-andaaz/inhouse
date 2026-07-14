
@extends('layouts.app')

@section('page-title', __('Orders'))
@section('page-heading', __('Orders'))

@section('breadcrumbs')
<li class="breadcrumb-item active">
    @lang('Orders')
</li>
@stop
<link rel="stylesheet" href="{{ asset('assets/css/orderdetails.css') }}" media="all" type="text/css">

@section('content')

@include('partials.messages')

<?php 
   // use App\Helpers\OrderHelper; 
    $orderShippingMethod = getShippingMethodOrderById($order->increment_id);
    use Vanguard\Http\Controllers\ItemController;
?> 
<div class="card">
    <div class="card-body">         
        <div class="row">
            <div class="col-12 @if (! isset($activities)) mx-auto @endif bg-white">
                <div class="card  border-0">
                    <div class="table-responsive" id="users-table-wrapper">
                        <table class="table table-borderless table-striped statement-table ordertop-info">
                            <tbody>
                                <tr class="statement-row  span">
                                    <td class="statement-td" data-label="@lang('Shipping Address')">
                                        @include('orders.partials.shipping')
                                        @include('orders.partials.billing')
                                        @include('orders.partials.others')
                                        @include('orders.partials.shipping-method')
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        @include('orders.partials.order-item-details')
                    </div>
                    @include('orders.partials.order-total')
                </div>
            </div>
        </div>

    </div> 
</div>
@stop

@section('scripts')
<script>
    $("#status").change(function () {
        $("#users-form").submit();
    });
</script>
@stop 