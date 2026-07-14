@extends('layouts.app')

@section('page-title', __('Order Report Tracker'))
@section('page-heading', __('Order Report Tracker'))

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
                                                <li class="ulhead p-2">                                                   
                                                    Running Orders
                                                </li>
                                                <li class="list-group-item totalcountblak" style="background-color: #2bc96be3;">
                                                    <span>{{ $count }}</span>
                                                </li>
                                            </ul>
                                            <ul class="list-group list-group-flush">
                                                <li class="ulhead p-2">Delayed</li>
                                                <li class="list-group-item totalcountblak" style="background-color: #fff000;">
                                                    <span>{{ $count_dispatch }}</span>
                                                </li>
                                            </ul>
                                            <ul class="list-group list-group-flush">
                                                <li class="ulhead p-2">Expedited</li>
                                                <li class="list-group-item totalcount" style="background-color: #970303ba;">
                                                    <span>{{ $count_expedite }}</span>
                                                </li>
                                            </ul>
                                            <ul class="list-group list-group-flush">
                                                <li class="ulhead p-2">Express Shipping</li>
                                                <li class="list-group-item totalcount" style="background-color: #e38613;">
                                                    <span>{{ $count_express_shipping }}</span>
                                                </li>
                                            </ul>
                                            <ul class="list-group list-group-flush">
                                                <li class="ulhead p-2">HOLD</li>
                                                <li class="list-group-item totalcount" style="background-color: #b93e0fba;">
                                                    <span>{{ $count_hold }}</span></li>
                                            </ul>
                                        </td>
                                    </tr>
                                </tbody>
                            </table> 
                            <form method="GET" action="{{ route('reports.newitemlogstracker') }}" class="pb-2 mb-3 border-bottom-light">
                                <div class="row my-3 flex-md-row flex-column-reverse">
                                    <!-- <div class="col-md-3 mt-2 mt-md-0"> -->
                                    <div class="col-md-2 mt-2 mt-md-0">
                                        <input type="text" name="unique_id" class="form-control input-solid" placeholder="Unique ID" value="{{ Request::get('unique_id') }}">
                                        <select name="hold_status" class="form-control input-solid" style="margin-top: 15px;">
                                            @php
                                                $bgstyle = '';
                                                $extraHolsStatuses = ['Hold', 'Unhold'];
                                                $manwearStatuses   = ['Manwear'];
                                                $qcAlteration   = ['Alteration'];
                                            @endphp
                                            <option value="">- Hold/Unhold -</option>
                                            @foreach($extraHolsStatuses as $holdStatus)
                                                <option value="{{ $holdStatus }}" {{ Request::get('hold_status') == $holdStatus ? 'selected' : '' }}>
                                                    {{ $holdStatus }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 mt-2 mt-md-0">
                                        <input type="text" name="order_id" class="form-control input-solid" placeholder="Order ID" value="{{ Request::get('order_id') }}">
                                        <select name="manwear" class="form-control input-solid" style="margin-top: 15px;">
                                            <option value="">- Meanwear -</option>
                                            @foreach($manwearStatuses as $manwearStatus)
                                                <option value="{{ $manwearStatus }}" {{ Request::get('manwear') == $manwearStatus ? 'selected' : '' }}>
                                                    {{ $manwearStatus }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-2 mt-2 mt-md-0">
                                        <select name="statuslocation" class="form-control input-solid">
                                            <option value="">- Location -</option>
                                            @foreach($statusLocationOptions as $loc)
                                                <option value="{{ $loc }}" {{ Request::get('statuslocation') == $loc ? 'selected' : '' }}>
                                                    {{ $loc }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <select name="alteration" class="form-control input-solid" style="margin-top: 15px;">
                                            <option value="">- QC Alteration -</option>
                                            @foreach($qcAlteration as $qcAlt)
                                                <option value="{{ $qcAlt }}" {{ Request::get('alteration') == $qcAlt ? 'selected' : '' }}>
                                                    {{ $qcAlt }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-2 mt-2 mt-md-0"> 
                                        <select name="sub_status_status" class="form-control input-solid">
                                            <option value="">- Sub Status -</option>
                                            @foreach($subStatusOptions as $status)
                                                <option value="{{ $status }}" {{ Request::get('sub_status_status') == $status ? 'selected' : '' }}>
                                                    {{ $status }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>                                    
                                    <div class="col-md-2 mt-2 mt-md-0">
                                        <select name="source" class="form-control input-solid">
                                            <option value="">- Source -</option>
                                            @foreach($sourceOptions as $src)
                                                <option value="{{ $src }}" {{ Request::get('source') == $src ? 'selected' : '' }}>
                                                    {{ $src }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 mt-2 mt-md-0">
                                        <button type="submit" class="btn btn-primary btn-rounded p-2 w-100 mb-2">Filter</button>
                                        <a href="/newitemlogstracker" class="btn btn-secondary btn-rounded p-2 w-100 mb-2">Reset</a>
                                    </div>
                                </div> 
                                @php
                                    $currentSort = request()->get('sort', 'newest'); // default = newest
                                @endphp
                                <div class="sortbydispatchdate" style="margin-right:10px;font-weight:700;float: left;">
                                    <a href="https://www.appsheet.com/start/d4969692-d6c9-4080-829d-fd131f09fb06#appName=CuttingReceiptandAllocationforStiching-823254466&group=%5B%5D&page=fastTable&sort=%5B%5D&table=location&view=Production" class="runningorder" target="__blank"> AppSheet Report </a>
                                </div>
                                
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
                            
                            {{-- History Modal --}}
                            <div class="modal fade" id="historyModal" tabindex="-1" role="dialog" aria-labelledby="historyModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-xl" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="historyModalLabel">
                                                History — <span id="historyUniqueIdLabel"></span>
                                            </h5>
                                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body p-0">
                                            <div id="historyLoading" class="text-center py-4" style="display:none;">
                                                <div class="spinner-border text-primary" role="status"></div>
                                                <p class="mt-2 text-muted">Loading history...</p>
                                            </div>
                                            <div id="historyError" class="alert alert-danger m-3" style="display:none;"></div>
                                            <div class="table-responsive" id="historyTableWrap" style="display:none;">
                                                <table class="table table-hover table-condensed ordertop-info table-bordered" id="historyTable">
                                                    <thead style="background:#30353e;color:#fff;">
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Updated By</th>
                                                            <th>Doer Name</th>
                                                            <th>Location</th>
                                                            <th>Sub Location</th>
                                                            <!-- <th>Next Sub Location</th> -->
                                                            <th>Source</th>
                                                            <th>Type</th>
                                                            <!-- <th>Shipped No.</th> -->
                                                            <th>Updated At</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="historyTableBody"></tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <small class="text-muted mr-auto" id="historyCount"></small>
                                            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <table class="table table-hover table-condensed table-striped statement-table ordertop-info table-bordered" id="ordertrack">
                                <thead>
                                    <tr>
                                        <th style="width: 160px;">Order ID</th>
                                        <th>SKUs</th>
                                        <!-- <th>Image</th> -->
                                        <th style="width: 103px;">Dispatch</th>
                                        <th style="width: 103px;">Revised Dispatch</th>
                                        <th style="width: 103px;">Occasion</th>
                                        <th>Source</th>
                                        <th>Expedit Status</th>
                                        <th>Location</th>
                                        <th>Sub Status</th>
                                        <th>Doer Name</th>
                                        <th>Exp. Delivery</th>
                                        <th>Hold Status</th>
                                        <th>Hold Reason</th>
                                        <th>Check List Cordinat.</th>
                                        <th>QC Status</th>
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
                                                </a>
                                                <strong>({{ $row->unique_id ?? '' }})</strong>
                                                <br>
                                                <button class="btn btn-primary btn-sm history-btn mt-1"
                                                    data-unique-id="{{ $row->unique_id }}">
                                                    History
                                                </button>
                                                <input type="hidden" name="reportid" value="{{ $row->entity_id }}" />

                                                @if($holdStatusIndicate && $holdStatusIndicate->indicate === 0)
                                                    <form action="{{ route('hold.unhold.update') }}" method="POST" class="d-inline" id="confirm-unhold-form">
                                                        @csrf
                                                        <input type="hidden" name="order_id" value="{{ $row->order_id }}">
                                                        <input type="hidden" name="unique_id" value="{{ $row->unique_id }}">
                                                        <input type="hidden" name="item_id" value="{{ $inhouseData->first()->id }}">
                                                        <input type="hidden" name="loginuser" value="{{ Auth::user()->first_name ??'' }} {{Auth::user()->last_name ?? '' }}">
                                                        <button type="submit" class="btn btn-primary p-2 w-75" style="font-size: 12px;margin-top: 2px;">Unhold Confirmed?</button>
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
                                            <td style="width: 103px;font-weight: 700;font-size:15px;">
                                                {{ date('d-m-Y', strtotime($row->dispatch_date)) }}
                                            </td>
                                            @else
                                            <td>{{ date('d-m-Y', strtotime($row->dispatch_date)) }}</td>
                                            @endif

                                            @if(\Carbon\Carbon::parse($row->revised_dispatch_date)->lte(\Carbon\Carbon::today()))
                                            <td style="width: 103px;background-color: #fa9ebe;color: #000;font-weight: 700;font-size:15px;">
                                                {{ date('d-m-Y', strtotime($row->revised_dispatch_date)) }}
                                            </td>
                                            @else
                                            <td>{{ $row->revised_dispatch_date }}</td>
                                            @endif

                                            <td>{{ $row->occassion }}</td>
                                            <td>{{ $row->source }}</td>
                                            <td>{{ $row->expendition_status }}</td>
                                            <td>{{ $row->statuslocation }}</td>
                                            <td>{{ $row->sub_status_status }}</td>
                                            <!-- <td>{{ $row->doer_name }}</td> -->
                                            <td>{{ $row->doer_employee_name ?? $row->doer_name }}</td>
                                            <td>{{ $row->express_delivery }}</td>
                                            <td>{{ $row->hold_status }}</td>
                                            <td>{{ $row->hold_reason }}</td>
                                            <td>{{ $row->check_list_coordinator }}</td>
                                            @php $bgstyle = (!empty($row->given_for) && $row->given_for !== 'Accepted') ? 'style="background-color:#c40101;color: #ffffff;"' : ''; @endphp
                                            <td {!! $bgstyle !!}>{{ $row->given_for }}</td>
                                            <!-- <td>{{ $row->manwear }}</td> -->
                                            <td>{{ $row->order_coordinator }}</td>
                                            <td style="text-align: left;">
                                                @php
                                                $remarks = getForTrackersRemarksByEntityId($row->unique_id);
                                                @endphp
                                                @if($remarks->isNotEmpty())
                                                    <b>Remarks:</b>
                                                    
                                                    @foreach($remarks as $remark)
                                                        {!! $remark->remark ?? '' !!}</br>
                                                    @endforeach 
                                                     
                                                @endif
                                                
                                                <a href="{{ route('order.report.remarks', ['uniqueid' => $row->unique_id]) }}" 
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
        $("#newitemlogstracker-reportform").submit();
    });
    $(document).on('click', '.history-btn', function () {
        var uniqueId = $(this).data('unique-id');

        $('#historyUniqueIdLabel').text(uniqueId);
        $('#historyTableBody').empty();
        $('#historyError').hide();
        $('#historyTableWrap').hide();
        $('#historyCount').text('');
        $('#historyLoading').show();
        $('#historyModal').modal('show');

        $.ajax({
            url: '{{ route("reports.newitemlogs.history") }}',
            method: 'GET',
            data: { unique_id: uniqueId },
            success: function (data) {
                $('#historyLoading').hide();

                if (!data.length) {
                    $('#historyError').text('No records found for ' + uniqueId).show();
                    return;
                }

                $.each(data, function (i, row) {
                    $('#historyTableBody').append(
                        '<tr>' +
                        '<td>' + (i + 1) + '</td>' +
                        '<td>' + (row.employee_name || row.updated_by || '—') + '</td>' +
                        '<td>' + (row.doer_employee_name || row.doername || '—') + '</td>' +
                        '<td>' + (row.location || '—') + '</td>' +
                        '<td>' + (row.sub_loaction || '—') + '</td>' +
                        /*'<td>' + (row.next_sub_location || '—') + '</td>' +*/
                        '<td>' + (row.source || '—') + '</td>' +
                        '<td><span class="badge badge-secondary">' + (row.type || '—') + '</span></td>' +
                        '<td>' + (row.updated_at || '—') + '</td>' +
                        '</tr>'
                    );
                });

                $('#historyCount').text(data.length + ' record(s) found');
                $('#historyTableWrap').show();
            },
            error: function (xhr) {
                $('#historyLoading').hide();
                $('#historyError').text('Failed to load history. Please try again.').show();
            }
        });
    });
</script>
@stop
<style> 
    /*.runningorder{ color:#fff !important; }*/
    .active-sort { font-weight: bold; color: #007bff; /* highlight blue */text-decoration: underline;}
    .ordertop-info th,.ordertop-info td {font-size: 13px;}
    .table thead th, .ulhead {color: #fff !important;background: #30353e; list-style: none;font-weight: 700;}
    .ordertop-info ul {max-width: 19%;display: inline-block;text-align: center;width: 100%;vertical-align: top;margin-right: 5px;border: 1px dotted;}
    .totalcount{color: #FFFFFF; font-size:28px;} 
    .totalcountblak{color: #000000; font-size:28px;} 
    #ordertrack td{text-align: center;vertical-align: middle;}
</style>