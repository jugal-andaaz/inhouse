@extends('layouts.app')

@section('page-title', __('Orders'))
@section('page-heading', __('Orders')) 

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/orderdetails.css') }}" media="all" type="text/css">
@endpush

<h4 class="m-3">Image replace</h4>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-12 mx-auto bg-white">
                <div class="card border-0">
                    @if ($errors->has('product_img'))
                        <div class="text-danger">
                            {{ $errors->first('product_img') }}
                        </div>
                    @endif
                    <form method="POST" action="{{ route('order.item.imagereplace') }}" id="itemLogForm" enctype="multipart/form-data">
                        @csrf
                         
                        @php
                            $orderId = $collection->order_id;
                            $productSku = $collection->product_sku;
                            $productImage = $collection->product_img;
                            $pid = $collection->product_item_id;
                        @endphp 

                        <input type="hidden" name="item_id" value="{{ $itemId }}">
                        <input type="hidden" name="order_id" value="{{ $orderId ?? '' }}">
                        <input type="hidden" name="pid" value="{{ $pid ?? '' }}">
                        <input type="hidden" name="product_sku" value="{{ $productSku ?? '' }}">
                        <input type="hidden" name="oldproduct_image" value="{{ $productImage ?? '' }}">
                        <input type="hidden" name="loginuser" value="{{ auth()->user()->present()->nameOrEmail }}">

                        <div class="table-responsive" id="users-table-wrapper">
                            <table class="table table-bordered table-striped">
                                <tbody>
                                    <tr>
                                        <td>                                            
                                            <label for="image"><b>Select Image *</b></label>
                                        </td>
                                        <td>
                                            <input type="file" name="product_img" id="image">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            <textarea id="productcomment" name="product_comment" class="form-control" rows="2"></textarea>     
                                        </td>
                                    </tr> 
                                    <tr>
                                        <td colspan="2">
                                            <button type="submit" class="btn btn-primary">Submit</button>
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
 