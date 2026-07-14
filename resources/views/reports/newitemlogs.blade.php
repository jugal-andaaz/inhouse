@extends('layouts.app')

@section('page-title', __('New Item Logs'))
@section('page-heading', __('New Item Logs'))

<link rel="stylesheet" href="{{ asset('assets/css/orderdetails.css') }}" media="all" type="text/css">
@section('breadcrumbs')
    <li class="breadcrumb-item active">@lang('newitemlogs')</li>
@endsection

@section('content')
    @include('partials.messages')

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-12 @if (! isset($activities)) mx-auto @endif bg-white">
                    <div class="card border-0">
                        <table class="table table-borderless table-striped statement-table ordertop-info">
                                <tbody>
                                    <tr class="statement-row  span">
                                        <td class="statement-td" data-label="Shipping Address">
                                            <ul class="list-group list-group-flush">
                                                <li class="ulhead p-2">Running Order</li>
                                                <li class="list-group-item totalcountblak" style="background-color: #2bc96be3;">
                                                    <span>{{ $count ?? 0 }}</span>
                                                </li>
                                            </ul>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        <div class="table-responsive" id="users-table-wrapper">
                            <form method="GET" action="{{ request()->url() }}" id="filter-form" class="mb-3">
                                <div class="row align-items-end px-2">
                                    <div class="col-md-2 col-sm-6 mb-2">
                                        <label class="filter-label">Order ID</label>
                                        <input type="text" name="order_id" class="form-control form-control-sm" placeholder="Search Order ID" value="{{ $filterOrderId }}">
                                    </div>
                                    <div class="col-md-2 col-sm-6 mb-2">
                                        <label class="filter-label">Unique ID</label>
                                        <input type="text" name="unique_id" class="form-control form-control-sm" placeholder="Search Unique ID" value="{{ $filterUniqueId }}">
                                    </div>
                                    <div class="col-md-2 col-sm-6 mb-2">
                                        <label class="filter-label">Location</label>
                                        <select name="location" class="form-control form-control-sm" onchange="this.form.submit()">
                                            <option value="">-- All --</option>
                                            @foreach ($locations as $loc)
                                                <option value="{{ $loc }}" {{ $filterLocation == $loc ? 'selected' : '' }}>{{ $loc }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 col-sm-6 mb-2">
                                        <label class="filter-label">Source</label>
                                        <select name="source" class="form-control form-control-sm" onchange="this.form.submit()">
                                            <option value="">-- All --</option>
                                            @foreach ($sources as $src)
                                                <option value="{{ $src }}" {{ $filterSource == $src ? 'selected' : '' }}>{{ $src }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 col-sm-6 mb-2">
                                        <label class="filter-label">Sub Location</label>
                                        <select name="sub_location" class="form-control form-control-sm" onchange="this.form.submit()">
                                            <option value="">-- All --</option>
                                            @foreach ($subLocations as $sub)
                                                <option value="{{ $sub }}" {{ $filterSubLocation == $sub ? 'selected' : '' }}>{{ $sub }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 col-sm-6 mb-2">
                                        <label class="filter-label">Next Sub Location</label>
                                        <select name="next_sub_location" class="form-control form-control-sm" onchange="this.form.submit()">
                                            <option value="">-- All --</option>
                                            @foreach ($nextSubLocations as $nxt)
                                                <option value="{{ $nxt }}" {{ $filterNextSubLocation == $nxt ? 'selected' : '' }}>{{ $nxt }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row px-2 mt-1">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary btn-sm">Search</button>
                                        <a href="{{ request()->url() }}" class="btn btn-secondary btn-sm ml-1">Clear</a>
                                    </div>
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
                                                            <th>Next Sub Location</th>
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
                                        <th style="max-width: 120px;">Order ID</th>
                                        <th style="max-width: 90px;">SKUs</th> 
                                        <th>Updated By</th>
                                        <th>Doer Name</th>
                                        <th>Location</th>
                                        <th>Sub Location</th>
                                        <th>Next-Sub Location</th>
                                        <th>Source</th>
                                        <th>Type</th>                                         
                                        <th>Updated At</th>
                                        <th>Comment</th>
                                        <!-- Add all columns you want to show -->
                                    </tr>
                                </thead>
                                <tbody> 
                                    @foreach ($newitemlogs as $newitemlog)
                                    @php
                                        $cleanId = preg_replace('/\D/', '', $newitemlog->unique_id);
                                        $inhouseData = getOrderIdItemIdByItemId($cleanId);
                                    @endphp
                                        <tr>
                                            <td style="max-width:120px;">
                                                <a href="/order/show/{{$inhouseData->first()->order_table_id ??''}}" target="__blank" style="line-height: 30px;">
                                                    {{ $inhouseData->first()->order_id ?? '' }}
                                                </a>
                                                <br>(<b>{{ $newitemlog->unique_id }}</b>)
                                                <br>
                                                <button class="btn btn-primary btn-sm history-btn mt-1"
                                                    data-unique-id="{{ $newitemlog->unique_id }}">
                                                    History
                                                </button>
                                            </td>
                                            <td style="max-width:90px;">
                                                <span style="font-weight: 700;border-bottom: 1px solid;display: inline-block;">
                                                    {{ $inhouseData->first()->product_sku ?? '' }}
                                                </span><br>
                                                <img src="{{ $inhouseData->first()->product_img ?? '' }}" style="max-width: 75px;" />
                                            </td> 
                                            <td>{{ $newitemlog->employee_name ?? $newitemlog->updated_by }}</td>
                                            <td>{{ $newitemlog->doer_employee_name ?? $newitemlog->doername }}</td>
                                            <td>{{ $newitemlog->location }}</td>
                                            <td>{{ $newitemlog->sub_loaction }}</td>
                                            <td>{{ $newitemlog->next_sub_location }}</td>
                                            <td>{{ $newitemlog->source }}</td>
                                            <td>{{ $newitemlog->type }}</td> 
                                            <td>{{ $newitemlog->updated_at }}</td>
                                            <td style="text-align: left;">
                                                @php
                                                $remarks = getRemarksByEntityId($newitemlog->unique_id);
                                                @endphp
                                                @if($remarks->isNotEmpty())
                                                    <b>Remarks:</b>
                                                    
                                                    @foreach($remarks as $remark)
                                                        {!! $remark->remark ?? '' !!}</br>
                                                    @endforeach 
                                                     
                                                @endif
                                                
                                                <a href="{{ route('order.report.remarks', ['entity_id' => $newitemlog->unique_id]) }}" 
                                                    onclick="window.open(this.href, 'popupWindow', 'width=900,height=650,scrollbars=yes'); 
                                                    return false;" style="color:#ffffff">
                                                    <button class="btn btn-primary btn-rounded p-2 w-100 mb-2">Add</button>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="pagination">
                                {{ $newitemlogs->links() }}
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
                        '<td>' + (row.next_sub_location || '—') + '</td>' +
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
    .filter-label { font-size: 12px; font-weight: 600; margin-bottom: 3px; display: block; color: #444; }
    .active-sort { font-weight: bold; color: #007bff; /* highlight blue */text-decoration: underline;}
    .ordertop-info th,.ordertop-info td {font-size: 13px;}
    .table thead th, .ulhead {color: #fff !important;background: #30353e; list-style: none;font-weight: 700;}
    .ordertop-info ul {max-width: 19%;display: inline-block;text-align: center;width: 100%;vertical-align: top;margin-right: 5px;border: 1px dotted;}
    .totalcount{color: #FFFFFF; font-size:28px;} 
    .totalcountblak{color: #000000; font-size:28px;} 
    #ordertrack td{text-align: center;vertical-align: middle;}
</style>