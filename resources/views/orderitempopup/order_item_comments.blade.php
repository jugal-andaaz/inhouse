@extends('layouts.app')

@section('page-title', __('Orders'))
@section('page-heading', __('Orders')) 

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/orderdetails.css') }}" media="all" type="text/css"> 
@endpush

<h4 class="m-3">Comments</h4>
<div class="card">
    <div class="card-body">
         @if ($errors->has('product_comment'))
            <div class="text-danger">{{ $errors->first('product_comment') }}</div>
        @endif
        <div class="row">            
            <div class="col-12 mx-auto bg-white">
                <div class="card border-0">
                    <form method="POST" action="{{ route('order.item.updatecomment') }}" id="commentForm">
                        @csrf
                         
                        @php
                            $orderId = $collection->order_id;
                            $productSku = $collection->product_sku;
                            $productComment = $collection->product_comment;
                        @endphp 

                        <input type="hidden" name="item_id" value="{{ $itemId }}">
                        <input type="hidden" name="order_id" value="{{ $orderId ?? '' }}">
                        <input type="hidden" name="product_sku" value="{{ $productSku ?? '' }}">
                        <input type="hidden" name="oldproduct_comment" value="{{ $productComment ?? '' }}">
                        <input type="hidden" name="loginuser" value="{{ auth()->user()->present()->nameOrEmail }}">

                        <div class="table-responsive" id="users-table-wrapper">
                            <table class="table table-bordered table-striped small">
                                <tbody>
                                    <tr>
                                        <td> 
                                            <textarea id="editor" name="product_comment" class="form-control" rows="4"></textarea>
                                        </td>
                                    </tr> 
                                    <tr>
                                        <td>
                                            <button type="submit" class="btn btn-primary btn-success" id="btn-success">Save Comment</button>
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

<script src="//cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function () {
        window.attachFormSubmitLoader('commentForm', 'btn-success');
    }); 

    CKEDITOR.replace('editor', {
        extraPlugins: 'colorbutton,font',versionCheck: false,
        toolbar: [
            { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike'] },
            { name: 'paragraph', items: ['NumberedList', 'BulletedList'] },
            { name: 'styles', items: ['Format', 'Font', 'FontSize'] },
            { name: 'colors', items: ['TextColor', 'BGColor'] },
            { name: 'insert', items: ['Image', 'Link', 'Unlink'] },
            { name: 'tools', items: ['Maximize'] }
        ]
    });
</script>