<link rel="stylesheet" href="{{ asset('assets/css/orderdetails.css') }}" media="all" type="text/css">

@extends('layouts.app')

@section('page-title', $item->product_sku . "_" . $item->order_id)

@section('page-heading', __('Print Measurement'))

@section('breadcrumbs')
    <li class="breadcrumb-item active">@lang('Print Measurement')</li>
@stop

@section('content')
@include('partials.messages')

@php
    $order = \Vanguard\Models\Order::where('increment_id', $item->order_id)->first();
    $measurementValid = $item->mmtid;
    $productSizeCollection = explode('|', str_replace('"', '', $item->product_size));
    $productmeasure = [];

    foreach ($productSizeCollection as $entry) {
        $split = explode(':', trim($entry), 2);
        if (count($split) == 2) {
            [$key, $val] = array_map('trim', $split);
            $productmeasure[str_replace(' ', '', lcfirst($key))] = $val;
        }
    }
    $appsheetDataList = getAppsheetToInhouseData($item->product_item_id, $item->order_id);
    $lastRecord = $appsheetDataList->last();
    $readyToDispatch = null;
@endphp

    <div class="card printmeasurement-info">
        <div class="card-body">
            <h2 class="mb-4" style="margin-left: 24px;">
                Measurement Detail for {{ $item->product_sku }} ({{ $item->order_id }})
            </h2>
            <div class="col-12 @if (! isset($activities)) mx-auto @endif bg-white">
                <div class="card border-0">
                    <div class="table-responsive" id="users-table-wrapper">
                        <table class="table table-striped statement-table ordertop-info">
                            <tbody>
                                <tr class="statement-row">
                                    <td width="25%">
                                        <img src="{{ Str::contains($item->product_img, 'https://assets2.andaazfashion.com') ? $item->product_img : asset($item->product_img) }}" class="img-fluid me-2 mb-2 BBB" alt="{{ $item->product_name }}" style="max-width: 250px;">
                                    </td>
                                    <td>
                                        <table class="w-100"> 
                                            @foreach([
                                                    'Order ID' => $item->order_id,
                                                    'Item Code' => $item->product_sku,
                                                    'Qty' => (int)$item->product_qty,
                                                    'Item Name' => $item->product_name,
                                                    'Dispatch Date' => $lastRecord->dispatch_date ?? 'Not Provided by AppSheet'
                                                ] as $label => $value)
                                                 @if($loop->iteration === 1)
                                                    <tr>
                                                        <td class="tdStyle" style="border-top: 0px !important">{{ $label }}</td>
                                                        <td class="tdStyle" style="border-top: 0px !important">{{ $value }}</td>
                                                    </tr>
                                                @else
                                                    <tr>
                                                        <td class="tdStyle">{{ $label }}</td>
                                                        <td class="tdStyle">{{ $value }}</td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </table>
                                    </td>
                                </tr>

                                <tr>
                                    <td colspan="2" class="printfonttd">
                                        @include('orders.partials.order-comments',['order' => $order])
                                    </td>
                                </tr>

                                <tr>
                                    <td colspan="2">
                                        <table class="table table-bordered">
                                            <thead><tr><th colspan="2" class="printfont">Product Description</th></tr></thead>
                                            <tbody>
                                                <tr>
                                                    <td width="50%" class="printfonttd">{!! html_entity_decode($item->description) ?? '' !!}</td>
                                                    <td width="50%" class="printfonttd">{!! html_entity_decode($item->product_description) ?? '' !!}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="table-responsive printmeasurement">
                            <table class="table statement-table ordertop-info">
                                <tr>
                                    <td>
                                        <table class="table table-bordered table-striped font-weight-bold">
                                            <thead><tr><th colspan="3" class="printfont">Measurement Detail</th></tr></thead>
                                            <tbody>
                                                @php
                                                    $juttiSizes = str_replace('"','',$item->product_size);
                                                    $juttiSize = explode(':', trim($juttiSizes), 2);
                                                    $sku = trim($item->product_sku ?? '');
                                                    $skuUpper = strtoupper($sku);

                                                    if(trim($juttiSize[0]=='Size') && substr($skuUpper, 0, 2) !== 'FT'){
                                                        echo '<tr><td class="high-lightbg">Size</td>
                                                            <td class="high-lightbg">'.$juttiSize[1].'</td></tr>';
                                                    }
                                                @endphp
                                                @if (trim($juttiSize[0]!=='Size') || substr($skuUpper, 0, 2) == 'FT')
                                                    <tr>
                                                        @php
                                                            $measurService='';
                                                            $measurementCollect = getMeasurmentByOrderId($measurementValid);
                                                                foreach($measurementCollect as $measurementCol){
                                                                    $measurType=$measurementCol['type'];
                                                                    $measurService=$measurementCol['service'];
                                                                }
                                                        @endphp
                                                        <td class="high-lightbg printfonttd">Tailoring Service</td>
                                                        <td class="high-lightbg printfonttd" colspan="2">
                                                            {{ $productmeasure['blouseTailoringService'] ?? $productmeasure['tailoringService'] ?? $measurService }}
                                                        </td>
                                                    </tr>
                                                   
                                                    @if ($measurementValid > 0)
                                                        @include('orders.partials.measurements', ['order' => $order, 'product' => $item])
                                                    @else
                                                        @php 
                                                            $standardMeasur = getStandardMeasurementByItemId($item->id); 
                                                        @endphp

                                                        @if ($standardMeasur)
                                                            @include('orders.partials.measurement_readysize',['product_id' => $item->id])
                                                        @else
                                                            @foreach([
                                                                'Andaaz Size' => $productmeasure['andaazSize'] ?? '-',
                                                                'Body Height' => $productmeasure['bodyHeight'] ?? '-'
                                                            ] as $label => $value)
                                                                @php
                                                                    if($value=='-' && isset($productmeasure['bodyChestSize'])){
                                                                        $value = $productmeasure['bodyChestSize'];
                                                                    }
                                                                @endphp
                                                                <tr>
                                                                    <td class="printfonttd">{{ $label }}</td>
                                                                    <td class="printfonttdvalue">{{ $value }}</td>
                                                                </tr>
                                                            @endforeach
                                                        @endif
                                                    @endif
                                                @endif
                                                <tr>
                                                    @include('orders.partials.addons', ['product_size' => $item->product_size])
                                                    @include('orders.partials.addonsareewear',['product_size' => $item->product_size])
                                                    @include('orders.partials.specialinstruction', ['product' => $item])
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                @php
                                    $commentCounts = 0;
                                    $itemComment = getItemComments($item->id);
                                    $commentCounts = count($itemComment);
                                @endphp
                                @if($commentCounts > 0)
                                <tr>
                                    <td colspan="2">
                                        <table class="table table-bordered">
                                            <thead><tr><th class="printfont">Team Comment</th></tr></thead>
                                            <tbody>
                                                 @foreach($itemComment as $getComment)
                                                 @php
                                                    $commentId = $getComment->entity_id;
                                                    $comment = $getComment->comment;
                                                    $commentUser = $getComment->user;
                                                    $updatedAt = $getComment->updated_at;
                                                 @endphp 
                                                    <tr class="commentbt-border">
                                                        <td class="printfonttd">
                                                            <p><strong>{!! $comment ?? '' !!}</strong>
                                                                <small class="text-muted" style="float: right;">
                                                                    {{$commentUser}} Updated At {{$updatedAt}}
                                                                </small>
                                                            </p>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                @endif
                            </table>
                            <div class="text-end mb-3">
                                <button class="btn btn-primary printbtn" onclick="window.print()">🖨 Print</button>
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
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('ul.shortdes a[href]').forEach(function (link) {
            link.removeAttribute('href');
        });
    });
</script>
@endsection 