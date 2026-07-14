@extends('layouts.app')

@section('page-title', __('Date Wise Order Report'))
@section('page-heading', __('Date Wise Order Report'))

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
                                            <ul class="list-group list-group-flush">
                                                <li class="ulhead p-2">Running Order</li>
                                                <li class="list-group-item totalcountblak" style="background-color: #2bc96be3;">
                                                    <span>{{ $count }}</span>
                                                </li>
                                            </ul>
                                            <ul class="list-group list-group-flush">
                                                <li class="ulhead p-2">Total Delayed</li>
                                                <li class="list-group-item totalcountblak" style="background-color: #fff000;">
                                                    <span>{{ $count_dispatch }}</span>
                                                </li>
                                            </ul>
                                            <ul class="list-group list-group-flush">
                                                <li class="ulhead p-2">Total Expedited</li>
                                                <li class="list-group-item totalcount" style="background-color: #970303ba;">
                                                    <span>{{ $count_expedite }}</span>
                                                </li>
                                            </ul>
                                            <ul class="list-group list-group-flush">
                                                <li class="ulhead p-2">Total Express Shipping</li>
                                                <li class="list-group-item totalcount" style="background-color: #e38613;">
                                                    <span>{{ $count_express_shipping }}</span>
                                                </li>
                                            </ul>
                                            <ul class="list-group list-group-flush">
                                                <li class="ulhead p-2">Total HOLD</li>
                                                <li class="list-group-item totalcount" style="background-color: #b93e0fba;">
                                                    <span>{{ $count_hold }}</span></li>
                                            </ul>
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
                            <form method="GET" action="{{ route('reports.orderstatus') }}" class="pb-2 mb-3 border-bottom-light">
                                <div class="row my-3 flex-md-row flex-column-reverse">
                                    <div class="col-md-4 mt-2 mt-md-0">
                                        <select name="sub_status_status" class="form-control input-solid">
                                            @php
                                                $extraStatuses = [
                                                    'New',
                                                    'Fabric Suggestion',
                                                    'Bag Preparation',
                                                    'Stitching Pending',
                                                    'Stitching',
                                                    'Qc at Surat',
                                                    'Qc At Delhi',
                                                    'QC',
                                                    'Finishing',
                                                    'Out_of_Stock',
                                                    'Cutting Pending',
                                                    'Cutting',
                                                    'Pressing Packing',
                                                ];
                                            @endphp
                                            <option value="">--- Select Sub Status ---</option>
                                            @foreach($extraStatuses as $status)
                                                <option value="{{ $status }}" {{ Request::get('sub_status_status') == $status ? 'selected' : '' }}>
                                                    {{ $status }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 mt-2 mt-md-0">
                                        <select name="hold_status" class="form-control input-solid">
                                            @php
                                                $extraHolsStatuses = [
                                                    'Hold',
                                                    'Unhold'
                                                ];
                                                $manwearStatuses = [
                                                    'Manwear',
                                                ];
                                            @endphp
                                            <option value="">--- Select Hold/Unhold ---</option>
                                            @foreach($extraHolsStatuses as $holdStatus)
                                                <option value="{{ $holdStatus }}" {{ Request::get('hold_status') == $holdStatus ? 'selected' : '' }}>
                                                    {{ $holdStatus }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 mt-2 mt-md-0">
                                        <select name="manwear" class="form-control input-solid">
                                            @php
                                                $extraHolsStatuses = [
                                                    'Meanwear'
                                                ];
                                            @endphp
                                            <option value="">--- Select Meanwear ---</option>
                                            @foreach($manwearStatuses as $manwearStatus)
                                                <option value="{{ $manwearStatus }}" {{ Request::get('manwear') == $manwearStatus ? 'selected' : '' }}>
                                                    {{ $manwearStatus }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 mt-2 mt-md-0">
                                        <button type="submit" class="btn btn-primary btn-rounded p-2 w-100 mb-2">Filter</button>
                                    </div>
                                    <div class="col-md-2 mt-2 mt-md-0">
                                        <a href="/orderreportstatus"class="btn btn-primary btn-rounded p-2 w-100 mb-2">Reset</a>
                                    </div>
                                </div>
                                @php
                                    $currentSort = request()->get('sort', 'newest'); // default = newest
                                @endphp

                                <div class="sortbydispatchdate" style="text-align:right;margin-right:10px;font-weight:700;">
                                    Sort By: 
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'oldest']) }}" 
                                       class="{{ $currentSort === 'oldest' ? 'active-sort' : '' }}">
                                       Older Date
                                    </a> |

                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}" 
                                       class="{{ $currentSort === 'newest' ? 'active-sort' : '' }}">
                                       Newest
                                    </a>
                                </div>
                            </form>

                            <table class="table table-hover table-condensed table-striped statement-table ordertop-info table-bordered" id="ordertrack">
                                <thead>
                                    <tr>
                                        <th style="width: 160px;">Order ID</th>
                                        <th>SKUs</th>
                                        <!-- <th>Image</th> -->
                                        <th style="width: 103px;">Dispatch</th>
                                        <th style="width: 103px;">Occasion</th>
                                        <th>Source</th>
                                        <th>Expedit Status</th>
                                        <th>Location</th>
                                        <th>Doer Name</th>
                                        <th>Exp. Delivery</th>
                                        <th>Sub Status</th>
                                        <th>Hold Status</th>
                                        <th>Hold Reason</th>
                                        <th>Check List Cordinat.</th>
                                        <th>Given For</th>
                                        <!-- <th>Man-Wear</th> -->
                                        <th>OC</th>
                                        <th>Comment</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $row)
                                        @php
                                            $holdStatusIndicate = '';
                                            $cleanId = preg_replace('/\D/', '', $row->unique_id);
                                            $inhouseData = getOrderIdItemIdByItemId($cleanId);
                                            $holdStatusIndicate = getHoldToUnholdData($row->order_id, $row->unique_id);
                                        @endphp  
                                        
                                        <tr>
                                            <td>
                                               <a href="/order/show/{{$inhouseData->first()->order_table_id ??''}}" target="__blank" style="line-height: 30px;">
                                                    {{ $inhouseData->first()->order_id ?? '' }} 
                                                </a><input type="hidden" name="reportid" value="{{ $row->entity_id }}" />

                                                @if($holdStatusIndicate && $holdStatusIndicate->indicate === 0)
                                                    <form action="{{ route('hold.unhold.update') }}" method="POST" class="d-inline" id="confirm-unhold-form">
                                                        @csrf
                                                        <input type="hidden" name="order_id" value="{{ $row->order_id }}">
                                                        <input type="hidden" name="unique_id" value="{{ $row->unique_id }}">
                                                        <input type="hidden" name="item_id" value="{{ $inhouseData->first()->id }}">
                                                        <input type="hidden" name="loginuser" value="{{ Auth::user()->first_name ??'' }} {{Auth::user()->last_name ?? '' }}">
                                                        <button type="submit" class="btn btn-primary btn-rounded p-2 w-75">Unhold Confirmed?</button>
                                                    </form>
                                                @endif                                                
                                            </td>
                                            <td style="width: 135px;">
                                                <span style="font-weight: 700;border-bottom: 1px solid;display: inline-block;">
                                                    {{ $inhouseData->first()->product_sku ?? '' }}
                                                </span>
                                                <br>
                                                <img src="{{ $inhouseData->first()->product_img ?? '' }}" style="max-width: 75px;" /></td>
                                            
                                            @if(\Carbon\Carbon::parse($row->dispatch_date)->lte(\Carbon\Carbon::today()))
                                            <td style="width: 103px;background-color: #ff0000;color: #fff;font-weight: 700;font-size:15px;">
                                                {{ date('d-m-Y', strtotime($row->dispatch_date)) }}
                                            </td>
                                            @else
                                            <td>{{ date('d-m-Y', strtotime($row->dispatch_date)) }}</td>
                                            @endif

                                            <td>{{ $row->occassion }}</td>
                                            <td>{{ $row->allocated_to }}</td>
                                            <td>{{ $row->expendition }}</td>
                                            <td>{{ $row->statuslocation }}</td>
                                            <td>{{ $row->doer_name }}</td>
                                            <td>{{ $row->express_delivery }}</td>
                                            <td>{{ $row->sub_status_status }}</td>
                                            <td>{{ $row->hold_status }}</td>
                                            <td>{{ $row->hold_reason }}</td>
                                            <td>{{ $row->check_list_coordinator }}</td>
                                            <td>{{ $row->given_for }}</td>
                                            <!-- <td>{{ $row->manwear }}</td> -->
                                            <td>{{ $row->order_coordinator }}</td>
                                            <td style="text-align: left;">
                                                @php
                                                $remarks = getRemarksByEntityId($row->unique_id);
                                                @endphp
                                                @if($remarks->isNotEmpty())
                                                    <b>Remarks:</b>
                                                    
                                                    @foreach($remarks as $remark)
                                                        {!! $remark->remark ?? '' !!}</br>
                                                    @endforeach 
                                                     
                                                @endif
                                                
                                                <a href="{{ route('order.report.remarks', ['entity_id' => $row->entity_id]) }}" 
                                                    onclick="window.open(this.href, 'popupWindow', 'width=900,height=650,scrollbars=yes'); 
                                                    return false;" style="color:#ffffff">
                                                    <button class="btn btn-primary btn-rounded p-2 w-100 mb-2">Add</button>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty 
                                        <tr>
                                            <td colspan="16">No records found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>

                            {{-- Pagination --}}
                            {{ $data->appends(request()->query())->links() }}

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