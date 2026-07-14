@extends('layouts.app')

@section('page-title', __('Orders'))
@section('page-heading', __('Orders')) 

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/orderdetails.css') }}" media="all" type="text/css">
@endpush

<h4 class="m-3">Hold Order Item</h4>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-12 mx-auto bg-white">
                <div class="card border-0">
                    <form method="POST" action="{{ route('order.item.update') }}" id="itemLogForm">
                        @csrf
                         
                        @php
                            $orderId = $collection->order_id;
                            $productSku = $collection->product_sku;
                            $mmtOldPendingReason = $collection->mmt_pending_reason;
                        @endphp 

                        <input type="hidden" name="item_id" value="{{ $itemId }}">
                        <input type="hidden" name="order_id" value="{{ $orderId ?? '' }}">
                        <input type="hidden" name="product_sku" value="{{ $productSku ?? '' }}">
                        <input type="hidden" name="mmt_oldpending_reason" value="{{ $mmtOldPendingReason ?? '' }}">
                        <input type="hidden" name="loginuser" value="{{ auth()->user()->present()->nameOrEmail }}">

                        <div class="table-responsive" id="users-table-wrapper">
                            <table class="table table-bordered table-striped small">
                                <tbody>
                                    <tr>
                                        <td>
                                            <label>Status:</label>
                                            <select name="mmt_pending_reason" id="ddlPassport" class="form-control" onchange="ShowHideDiv()">
                                                <option value="0">--Select Status--</option>
                                                <option value="Final Measurements Awaited">Final Measurements Awaited</option>
                                                <option value="Unstitch Dress Material - Size Confirmation Awaited">Unstitch Dress Material - Size Confirmation Awaited</option>
                                                <option value="DIY - Designs Details Awaited">DIY - Designs Details Awaited</option>
                                                <option value="Approval Awaited">Approval Awaited</option>
                                                <option value="Others">Others</option>
                                                <option value="Unhold">Unhold</option>
                                            </select>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>
                                            <div id="dvHold" style="display: none;">
                                                <label>Select Reason:</label>
                                                <select name="pending_reason" id="ddlmmtpeningreason" class="form-control">
                                                    <option value="--Select Reason--">--Select Reason--</option>
                                                    <option value="Colour Approval Awaited">Colour Approval Awaited</option>
                                                    <option value="Embroidery Approval Awaited">Embroidery Approval Awaited</option>
                                                    <option value="Fabric Approval Awaited">Fabric Approval Awaited</option>
                                                    <option value="Design Approval Awaited">Design Approval Awaited</option>
                                                </select>
                                            </div>

                                            <div id="dvUnhold" style="display: none;">
                                                <label>Other Reason:</label>
                                                <textarea id="ddlUnhold" name="rr_further_action_reason_other" class="form-control" rows="2" maxlength="150"></textarea>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>
                                            <button type="submit" class="btn btn-primary">Add</button>
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
 
<script type="text/javascript">
    function ShowHideDiv() {
        var ddlPassport = document.getElementById("ddlPassport");
        var dvHold = document.getElementById("dvHold");
        var dvUnhold = document.getElementById("dvUnhold");

        dvHold.style.display = ddlPassport.value === "Approval Awaited" ? "block" : "none";
        dvUnhold.style.display = ddlPassport.value === "Others" ? "block" : "none";
    }
</script>
<style>
    .navbar, .col-md-2.sidebar { display: none !important; }
</style>
 