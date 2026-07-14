@extends('layouts.app')

@section('page-title', __('OLD - Orders'))
@section('page-heading', __('OLD - Orders'))

@section('breadcrumbs')
<li class="breadcrumb-item active">
    @lang('OLD - Orders')
</li>
@stop 

@section('content')

@include('partials.messages')

<div class="card">
    <div class="card-body">
        <form action="" method="GET" id="users-form" class="mb-2 border-bottom-light">
            <div class="row flex-md-row flex-column-reverse">
                <div class="col-md-4 mt-md-0 mt-2">
                    <div class="input-group custom-search-form">
                        <input type="text"
                               class="form-control input-solid"
                               name="search"
                               value="{{ Request::get('search') }}"
                               placeholder="@lang('Search for OLD - Orders...')">

                        <span class="input-group-append">
                            @if (Request::has('search') && Request::get('search') != '')
                            <a href="/orders"
                               class="btn btn-light d-flex align-items-center text-muted"
                               role="button">
                                <i class="fas fa-times"></i>
                            </a>
                            @endif
                            <button class="btn btn-light" type="submit" id="search-users-btn">
                                <i class="fas fa-search text-muted"></i>
                            </button>
                        </span>
                    </div>
                </div>

                <div class="col-md-2 mt-2 mt-md-0">
                    <select name="status" id="status" class="form-control input-solid">
                        @foreach($statuses as $key => $value)
                        <option value="{{ $key }}" {{ Request::get('status') == $key ? 'selected' : '' }}>
                            {{ $value }}
                        </option>
                        @endforeach
                    </select>
                </div>

               <div class="col-md-4 orderdomain">
                    @php
                        $domainMap = [
                            '1' => 'COM',
                            '14' => 'UK',
                            '2' => 'FR',
                            '11' => 'MY',
                        ];
                    @endphp
                    <table class="table table-responsive">
                        <thead>
                            <td colspan="{{count($domainCounts) * 2}}" style="text-align: center;">
                                Total Orders By Domain
                            </td>
                        </thead>
                        <tbody>
                            <tr>
                            @foreach ($domainCounts as $row)
                                <th>{{ $domainMap[$row->domain] ?? $row->domain }}:</th>
                                <td>{{ $row->total }}</td>
                            @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
        <div class="table-responsive pb-1" id="users-table-wrapper" style="background: #61ace375;vertical-align: middle;border-radius: 10px;padding: 8px 15px;">
            <span class="totalord">Total Orders: <b><?= $totalOrders; ?></b></span>
            <span class="rightpages">Pages: <b><?= $currentPage; ?></b> of <b><?= $totalPages; ?></b></span>
        </div>        
        <div class="table-responsive" id="users-table-wrapper">
            <table class="table table-borderless table-striped oldorder">
                <thead>
                    <tr>
                        <th class="min-width-80">@lang('Order No.')</th>
                        <th class="min-width-150">@lang('Customer Name')</th>
                        <th class="min-width-100">@lang('Order Date')</th>
                        <th class="min-width-80">@lang('Quantity')</th>
                        <th class="min-width-80">@lang('Order Status')</th>
                        <th class="min-width-80">@lang('Discount Code')</th>
                        <th class="min-width-80">@lang('Discount')</th>
                        <th class="min-width-80">@lang('Amount')</th>
                        <th class="min-width-80">@lang('Web')</th>
                    </tr>
                </thead>
                
                <tbody>
                    @php $totalQty = 0; @endphp
                    @if (count($orders))
                        @foreach ($orders as $order)
                            @include('oldorders.partials.row')
                            @php $totalQty += $order->total_qty_ordered; @endphp
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7"><em>@lang('No records found.')</em></td>
                        </tr>
                    @endif
                </tbody>
                <tr>
                    <td colspan="3" align="right"><strong>Total Qty:</strong></td>
                    <td>{{ $totalQty }}</td>
                </tr>
            </table>
        </div>
    </div>
</div>

{{ $orders->appends(request()->only(['search', 'status']))->links() }}

@stop

@section('scripts')
<script>
    $("#status").change(function () {
        $("#users-form").submit();
    });
</script>
@stop

<style type="text/css">
    span.label-primary.cancelled{background-color:#ff0000;padding: 5px;color: #fff !important;text-align: center;width:95px !important; display: inline-block;}
    span.text-primary.processing{background-color:#ffff0069;padding: 5px;color: #000 !important;text-align: center;width:95px !important;display: inline-block;}
    span.text-danger.pending{background-color:#0000ff;padding: 5px;color: #fff !important;text-align: center;width:95px !important;display: inline-block;}
    span.label-primary.complete{background-color:#008000;padding: 5px;color: #fff !important;text-align: center;width:95px !important;display: inline-block;}
    .orderdomain td{border-right: 1px solid #ccc;}
    .orderdomain td:last-child{border-right: 0px solid #ccc;}
    .orderdomain tbody td{padding-left: 0 !important;}
    .table.oldorder thead{background: #30353e;}
    .table.oldorder thead th{color: #ffffff;}
    span.totalord{font-size: 14px;}
    span.rightpages {float: right;font-size: 14px;}
</style>