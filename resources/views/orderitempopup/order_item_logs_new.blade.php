<link rel="stylesheet" href="{{ asset('assets/css/orderdetails.css') }}" media="all" type="text/css">

@extends('layouts.app')

@section('page-title', __('Orders'))
@section('page-heading', __('Orders'))

<h4 class="m-3">Order Item Logs for SKU:- <span class="text-holdstatus">{{ $productsku }}</span></h4>
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-12 @if (! isset($activities)) mx-auto @endif bg-white">
                <div class="sort-links oilid">
                    Sort by:
                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'asc']) }}"
                       class="{{ $sort === 'asc' ? 'active' : '' }}">
                       Oldest
                    </a> |
                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'desc']) }}"
                       class="{{ $sort === 'desc' ? 'active' : '' }}">
                       Newest
                    </a>
                </div>
                <div class="card  border-0">
                    <div class="table-responsive" id="users-table-wrapper">
                        <table class="table table-bordered table-striped small">
                            <thead>
                                <tr class="d-none d-md-table-row">
                                    <th>Updated By</th>
                                    <th>Doer Name</th>
                                    <th>Location</th>
                                    <th>Sub Location</th> 
                                    <th>Source</th>
                                    <th>Type</th> 
                                    <th>Updated At</th>
                                </tr>
                            </thead> 
                            <tbody>
                            @forelse($collection as $item)
                               
                                <tr class="d-block d-md-table-row ">
                                    <td class="statement-td d-block d-md-table-cell mb-3 td" data-label="@lang('Updated By')">
                                        {{ $item->updated_by }}<input type="hidden" name="logid" value="{{ $item->entity_id }}">
                                    </td>
                                    <td class="statement-td d-block d-md-table-cell mb-3 td" data-label="@lang('Column Name')">
                                        {{ $item->doername }}
                                    </td>
                                    <td class="statement-td d-block d-md-table-cell mb-3 td" data-label="@lang('Old Value')">
                                        {!! $item->location ?? '' !!}
                                    </td>
                                    <td class="statement-td d-block d-md-table-cell mb-3 td" data-label="@lang('New Value')">
                                        {!! $item->sub_loaction ?? '' !!}
                                    </td>
                                    <td class="statement-td d-block d-md-table-cell mb-3 td" data-label="@lang('New Value')">
                                        {!! $item->source ?? '' !!}
                                    </td>
                                    <td class="statement-td d-block d-md-table-cell mb-3 td" data-label="@lang('New Value')">
                                        {!! $item->type ?? '' !!}
                                    </td>
                                    <td class="statement-td d-block d-md-table-cell mb-3 td" data-label="@lang('Updated By')">
                                        {{ $item->updated_at }}
                                    </td>
                                </tr> 
                            @empty
                                <tr><td colspan="6">No records found.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                        {{-- Laravel Pagination --}} 
                        <div class="mt-3">
                            {{ $collection->withQueryString()->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style type="text/css">
    .navbar,.col-md-2.sidebar{display: none !important;}
    tr.appsheet td {font-weight: 700;font-size: 13px;}
    .oilid.sort-links{text-align: right;width: 100%;margin: 0 auto 15px;font-size: 15px;}
</style>