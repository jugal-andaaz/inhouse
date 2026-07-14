@extends('layouts.app')

@section('page-title', __('Orders'))
@section('page-heading', __('Orders')) 

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/orderdetails.css') }}" media="all" type="text/css"> 
@endpush

<h4 class="m-3">Update Item Qty</h4>
<div class="card">
    <div class="card-body">
         @if ($errors->has('product_updateqty'))
            <div class="text-danger">{{ $errors->first('product_updateqty') }}</div>
        @endif
        <div class="row">
            <div class="col-12 mx-auto bg-white">
                <div class="card border-0">
                    <form method="POST" action="{{ route('order.item.updateqty') }}" id="updateqtyForm">
                        @csrf
                         
                        @php
                            $orderId = $collection->order_id;
                            $productSku = $collection->product_sku;
                            $productQty = $collection->product_qty;
                        @endphp 

                        <input type="hidden" name="item_id" value="{{ $itemId }}">
                        <input type="hidden" name="order_id" value="{{ $orderId ?? '' }}">
                        <input type="hidden" name="product_sku" value="{{ $productSku ?? '' }}">
                        <input type="hidden" name="oldproduct_qty" value="{{ $productQty ?? '' }}">
                        <input type="hidden" name="loginuser" value="{{ auth()->user()->present()->nameOrEmail }}">

                        <div class="table-responsive" id="users-table-wrapper">
                            <table class="table table-bordered table-striped product-tbl-font">
                                <tbody>
                                    <tr> 
                                        <td> 
                                            <input name="product_updateqty" type="text" id="updateqty" class="form-control mb-3">
                                        </td>
                                        <td>
                                            <button type="submit" class="btn btn-primary btn-success" id="btn-success">Update</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    .navbar, .col-md-2.sidebar { display: none !important; }
</style>