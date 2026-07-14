@php
    $increment_id = isset($order) && !empty($order->increment_id) ? $order->increment_id : null;
    if(isset($_GET['sku'])){
        $products = getItemsByOrderId($increment_id,$_GET['sku']); 
    }else{
        $products = getItemsByOrderId($increment_id);
    }
    $styeleHide = $styeleBPUHide =  $styeleFactoryHide = '';
@endphp

@if($products->count())
    @foreach($products as $product)
        @php
            $appsheetDataList = getNewItemLogsTrackerData($product->product_item_id, $order->increment_id);
            $lastRecord = $appsheetDataList?->last();
            $readyToDispatch = $appsheetDataList?->firstWhere('sub_status_status', 'Ready to Dispatch');
            $user = auth()->user();
            
            $refunded = $product->rr_refund_status == 1 ? 'refunded' : '';

            // Role-based visibility
            if ($user && request()->has('sku')) {
                if ($user->hasRole('BPU') && $lastRecord?->statuslocation === 'FACTORY') {
                    $styeleFactoryHide = 'style-factory-hide';
                } elseif ($user->hasRole('Factory') && $lastRecord?->statuslocation === 'BPU') {
                    $styeleBPUHide = 'style-bpu-hide';
                }
            }

            // Hide if shipped/cancelled
            if ($lastRecord && request()->has('sku') && (
                $lastRecord->shipped_number !='Invoiced' ||
                in_array($lastRecord->sub_status_status, ['Shipped', 'Cancel'])
            )) {
                $styeleHide = 'stylehide';
            }

            // Hide if SKU param but no record
            if (request()->has('sku') && !$lastRecord) {
                $styeleHide = 'stylehide';
            }
        @endphp
    @endforeach
@endif

<table class="table table-bordered table-striped product-tbl product-tbl-font {{ $styeleHide }} {{ $styeleBPUHide }} {{ $styeleFactoryHide }}">
    <thead>
        <tr class="d-none d-md-table-row">
            <th scope="col">@lang('#')</th>
            <th scope="col">@lang('Image')</th>
            <th scope="col">@lang('Item Information')</th>
            <th scope="col">@lang('Item Code')</th>
            <!-- <th scope="col">@lang('Qty')</th>
            <th scope="col">@lang('Amount')</th> -->
            <th scope="col">@lang('Team Comment')</th>
            <th scope="col">@lang('Size Detail')</th>
        </tr>
    </thead>
    <tbody> 
    @if($products->count() > 0)
        @foreach($products as $i => $product)
            @php
                $i++;$refunded='';
                $trackDetail[] = [
                    'sku' => $product->product_sku,
                    'trackid' => $product->product_tracking,
                    'courier' => $product->product_courier,
                    'dispatch_date' => $product->product_dispatch_date,
                ];
                if($product->rr_refund_status==1){
                    $refunded = 'refunded';
                }

                $measurementValid = $product->mmtid;
            @endphp
            @php
                $appsheetDataList = getNewItemLogsTrackerData($product->product_item_id, $order->increment_id);
                $lastRecord = $appsheetDataList->last();
                $readyToDispatch = $appsheetDataList->firstWhere('sub_status_status', 'Ready to Dispatch');
                $styeleHide = '';
                

                $getItemId = 'ANDFS_'.$product->product_item_id;
                $trackingIdValue = getShipmentTrackingNumber($getItemId, $product->order_id);

            @endphp

            @if($lastRecord && $lastRecord->shipped_number!='Invoiced'  && request()->has('sku'))
                @php 
                    $styeleHide = "stylehide";
                @endphp
            @endif

            <tr class="d-block d-md-table-row {{ $refunded }} {{ $styeleHide }}">
                <td data-label="@lang('#')" style="width: 1%;">
                    <p class="{{$product->product_item_id}}">{{ $i;}}</p>
                </td>
                <td class="statement-td d-block d-md-table-cell mb-3 td img" data-label="@lang('#')" style="width: 18%;">
                    <div class="card">
                        <div class="d-flex flex-column flex-md-row align-items-start"> 
                            <img src="{{ Str::contains($product->product_img, 'https://assets2.andaazfashion.com') ? $product->product_img : asset($product->product_img) }}" class="img-fluid me-2 mb-2 mb-md-0 BBB" alt="{{ $product->product_name }}">
                        </div>
                    </div>
                </td>
                <td class="perticular" data-label="@lang('Particulars')" style="width:22%;">
                    <div class="card border-0 shadow-none m-0 p-0">
                        <div class="d-flex flex-column flex-md-row align-items-start">

                            @if ($lastRecord) 
                            <table class="table table-borderless table-striped product-tbl-font">
                                <tbody>
                                    <tr>
                                        <td><strong>Location</strong><span style="float: right;">:</span></td>
                                       
                                        <td>{!! $lastRecord->statuslocation ?? '--' !!}</td>
                                        
                                    </tr>
                                    <tr>
                                        <td><strong>Status</strong><span style="float: right;">:</span></td>
                                      
                                        <td>{!! $lastRecord->sub_status_status ?? '--' !!}</td>
                                       
                                    </tr>

                                    <tr>
                                        <td><strong>Source</strong><span style="float: right;">:</span></td>
                                        <td>{!! $lastRecord->source ?? '--' !!}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Expendition Status</strong><span style="float: right;">:</span></td>
                                        <td>{{ $lastRecord->expendition_status ?? '--' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Order Coordinator</strong><span style="float: right;">:</span></td>
                                        <td>{{ $lastRecord->order_coordinator ?? '--' }}</td>
                                    </tr>

                                    <tr>
                                        <td><strong>Check List Coordinator</strong><span style="float: right;">:</span></td>
                                        <td>{{ $lastRecord->check_list_coordinator ?? '--' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Dispatch Date</strong><span style="float: right;">:</span></td>
                                        <td>{{ $lastRecord->revised_dispatch_date ?? '--' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Occassion Date</strong><span style="float: right;">:</span></td>
                                        <td>{{ $lastRecord->occassion ?? '--' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Hold Status</strong><span style="float: right;">:</span></td>
                                        <td><span class="text-holdstatus">{{ $lastRecord->hold_status ?? '--' }}</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Hold Reason</strong><span style="float: right;">:</span></td>
                                        <td>{{ $lastRecord->hold_reason ?? '--' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Doer Name</strong><span style="float: right;">:</span></td>
                                        <td>{!! $lastRecord->doer_name ?? '--' !!}</td>
                                    </tr>

                                @if($trackingIdValue!='')                                    
                                    <tr>
                                        <td colspan="2">
                                            <p class="mb-2" 
                                            style="text-align:center;padding: 5px 10px;background-color:#e73aa8;color:#fff;margin-bottom:0px !important;font-weight: 700;font-size: 15px;border-radius: 6px;">
                                            <strong style="font-size: 17px;">Tracking ::  </strong>{{ $trackingIdValue }}</p>
                                        </td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>

                            @endif                             
                        </div>
                        
                        <div class="d-grid gap-1 my-2 px-1">
                            @if(auth()->user()->hasPermission('order.editimage'))
                                @if($refunded != 'refunded')
                                    <a href="{{ route('order.item.imgreplace', ['item_id' => $product->id]) }}" 
                                        onclick="window.open(this.href, 'popupWindow', 'width=900,height=650,scrollbars=yes'); 
                                        return false;"><button class="btn btn-primary p-2 mb-2">Replace Image</button>
                                    </a>
                                @endif
                            @endif
                            
                            <a href="{{ route('order.item.logsnew', ['unique_id' =>$product->product_item_id, 'productsku' =>$product->product_sku]) }}" onclick="window.open(this.href, 'popupWindow', 'width=1000,height=650,scrollbars=yes'); 
                                return false;"><button class="btn btn-primary p-2 mb-2">New Item Logs</button>
                            </a>                            
                            @if($refunded != 'refunded')
                                <a href="/printmeasurement/{{$product->id}}" target="__blank">
                                    <button class="btn btn-primary p-2 mb-2">Print</button>
                                </a>
                            @endif
                            <a href="{{ route('order.item.logs', ['item_id' => $product->id, 'proditem_id' =>$product->product_item_id, 'order_id' =>$order->increment_id]) }}" onclick="window.open(this.href, 'popupWindow', 'width=900,height=650,scrollbars=yes'); 
                                return false;"><button class="btn btn-primary p-2 mb-2">Measurement Changes</button>
                            </a>
                        </div>
                    </div>
                </td>
                <td class="statement-td d-block d-md-table-cell mb-3 w-15 sku" style="width: 8%;">
                    <p style="color:#ff0000;text-transform: uppercase;font-size: 22px;"><strong>{{ $refunded }}</strong></p>
                    <p>{{ $product->product_sku }}</p>
                    <p><strong>Qty: </strong> {{ (int)$product->product_qty }}</p>
                    @if(auth()->user()->hasPermission('order.updateqty'))
                        <p> <a href="{{ route('order.item.updateqty', ['item_id' => $product->id]) }}" 
                                onclick="var width = 600;
                                        var height = 250;

                                        // Get browser window position and size
                                        var dualScreenLeft = window.screenLeft !== undefined ? window.screenLeft : window.screenX;
                                        var dualScreenTop = window.screenTop !== undefined ? window.screenTop : window.screenY;

                                        var browserWidth = window.innerWidth || document.documentElement.clientWidth || screen.width;
                                        var browserHeight = window.innerHeight || document.documentElement.clientHeight || screen.height;

                                        // Calculate centered position
                                        var left = dualScreenLeft + (browserWidth - width) / 2;
                                        var top = dualScreenTop + (browserHeight - height) / 2;

                                        window.open(this.href, 'popupWindow', 
                                            'width=' + width + 
                                            ',height=' + height + 
                                            ',scrollbars=yes,left=' + left + ',top=' + top);
                                        return false;
                                " style="color:#ffffff">
                                @if($refunded != 'refunded')
                                <button class="btn btn-primary btn-rounded p-2 w-100 mb-2">Update Qty</button>
                                @endif
                            </a>
                        </p>
                    @endif    
                    <p><strong>Amount:</strong><br />
                        @php
                            $productPrice = ($product->price - $product->discount_amount)
                        @endphp
                        {{ $order->order_currency_code }} {{ (float)$productPrice }}
                    </p>
                    @if(isset($_GET['sku']))
                        <p><strong>Order ID: </strong> {{ $order->increment_id }}</p>
                        <p><strong>Order Date: </strong><br />{{ $product->order_date }}</p>
                    @endif

                </td>
                <td class="statement-td d-block d-md-table-cell mb-3 w-20 td" style="width: 22%;">
                    @php 
                        $itemComment = getItemComments($product->id);
                    @endphp
                     @foreach($itemComment as $getComment)
                     @php
                        $commentId = $getComment->entity_id;
                        $comment = $getComment->comment;
                        $commentUser = $getComment->user;
                        $updatedAt = $getComment->updated_at;
                     @endphp 
                        <li class="mb-2 commentbt-border">
                            <span><p>{!! $comment ?? '' !!}</p></span>
                            <small class="text-muted">{{$commentUser}} Updated At {{$updatedAt}}</small>
                            @if($refunded != 'refunded')
                                <!-- Delete Button -->
                                <form action="{{ route('order.item.deletecomment', $commentId) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="order_id" value="{{ $product->order_id }}">
                                    <input type="hidden" name="sku" value="{{ $product->sku }}">
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this comment?')">
                                        Delete
                                    </button>
                                </form>
                            @endif
                        </li>
                     @endforeach
                     @if($refunded != 'refunded')
                        <a href="{{ route('order.item.comments', ['item_id' => $product->id]) }}" 
                            onclick="window.open(this.href, 'popupWindow', 'width=900,height=650,scrollbars=yes'); 
                            return false;" style="color:#ffffff">
                            <button class="btn btn-primary btn-rounded p-2 w-100 mb-2">Add comment</button>
                        </a>
                    @endif
                    @if(isset($_GET['sku']))
                        @include('orders.partials.order-comments',['order' => $order])
                    @endif
                </td>
                <td class="statement-td d-block d-md-table-cell mb-3 w-20 td" style="width: 30%;">
                    @if($measurementValid > 0)
                        @php
                            $tailService = 'customTailored';
                        @endphp
                    @else
                        @php
                            $tailService = 'ready';
                        @endphp   
                    @endif

                    @if(auth()->user()->hasPermission('order.measurementedit'))
                        @if($refunded != 'refunded')
                            <a href="{{ route('order.item.measurementform', ['ordid' => $order->increment_id,'pid' =>$product->product_item_id,'tailSrvc' => $tailService,]) }}" 
                                onclick="window.open(this.href, 'popupWindow', 'width=1050,height=650,scrollbars=yes'); 
                                return false;" style="color:#ffffff"><button class="btn btn-primary btn-rounded p-2 w-100 mb-2">Add Sizes</button>
                            </a>
                        @endif
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped product-tbl-font">
                            <thead>
                                <tr>
                                    <th scope="col" colspan="3">Measurement Detail</th> 
                                </tr>
                            </thead>
                            @php
                                $productmeasure=[];$coupleDetailsVal=0;
                                $productSizes = str_replace('"','',$product->product_size);
                                $productSizeCollection = explode('|', $productSizes);
                            @endphp 

                                @foreach($productSizeCollection as $productSizeCollect)
                                    @php
                                        $measurType=$coupleDetails='';$coupleDetailsVal=0;
                                        $productSize = explode(':', trim($productSizeCollect), 2);
                                        $standardMeasur = getStandardMeasurementByItemId($product->id);
                                        if ((strpos($productSizeCollect, 'Womens') !== false) || (strpos($productSizeCollect, 'Mens') !== false)) {
                                            if(!$standardMeasur){
                                          echo $coupleDetails = '<tr><td class="">'.$productSize[0].'</td><td class="" colspan="2">'.$productSize[1].'</td></tr>';
                                                $coupleDetailsVal = 1; 
                                        } }

                                        $measurementCollect = getMeasurmentByOrderId($measurementValid);

                                        foreach($measurementCollect as $measurementCol){
                                            $measurType=$measurementCol['type'];
                                            $measurService=$measurementCol['service'];
                                        }
                                    @endphp

                                    @if(count($productSize) == 2)
                                        @php
                                        if(trim($productSize[0])=='Tailoring Service' || trim($productSize[0])=='Select Tailoring Service'){
                                            $productmeasure['tailoringService'] = trim($productSize[1]);$coupleDetailsVal = 0;
                                        } else if(trim($productSize[0])=='Blouse Tailoring Service'){
                                            $productmeasure['tailoringService'] = trim($productSize[1]);$coupleDetailsVal = 0;
                                        } else if(trim($productSize[0])=='Andaaz Size' || trim($productSize[0])=='Size' || trim($productSize[0])=='Body Chest Size'){
                                            $productmeasure['andaazSize'] = trim($productSize[1]);$coupleDetailsVal = 0;
                                        } else if(trim($productSize[0])=='Body Height' || trim($productSize[0])=='Body Height (Head to Toe)'){
                                            $productmeasure['bodyHeight'] = trim($productSize[1]);$coupleDetailsVal = 0;
                                        }
                                        @endphp
                                    @endif
                                @endforeach
                            <tbody>
                                @php
                                    $productSize = explode(':', trim($productSizeCollect), 2);  
                                    if(trim($productSize[0]=='Size')){
                                        echo '<tr><td class="high-lightbg">Size</td>
                                            <td class="high-lightbg">'.$productSize[1].'</td></tr>';
                                    }
                                @endphp

                                @if (trim($productSize[0]!=='Size'))    
                                    @if (((strpos($productSizeCollect, 'Womens') === false) || (strpos($productSizeCollect, 'Mens') === false)) && $coupleDetailsVal==0 ) 
                                    <tr>
                                        <td class="high-lightbg {{ $product->id }}">Tailoring Service</td>
                                        <td class="high-lightbg" colspan="2">
                                    @php 
                                        $standardMeasur = getStandardMeasurementByItemId($product->id);
                                    @endphp
                                            @if($measurementValid > 0)
                                                {{ $measurService ?? "Customer Tailored" }}
                                            @else
                                                @if($standardMeasur)
                                                    {{ $standardMeasur->service ?? '' }}
                                                @else
                                                    {{ $productmeasure['tailoringService'] ?? 'Standard Size' }}
                                                @endif
                                            @endif 
                                        </td>
                                    </tr>
                                    @endif
                                @endif
                            </tbody>
                            @if (trim($productSize[0]!=='Size')) 
                                @if($measurementValid > 0)
                                    @include('orders.partials.measurements')
                                @else
                                    @php 
                                        $productSizes = str_replace('"','',$product->product_size);
                                        $productSizeCollection = explode('|', $productSizes);

                                        $standardMeasur = getStandardMeasurementByItemId($product->id);
                                    @endphp

                                    <tbody>
                                        @if ($standardMeasur) 
                                           @include('orders.partials.measurement_readysize',['product_id' => $product->id])
                                        @else
                                            @if (((strpos($productSizeCollect, 'Womens') === false) || (strpos($productSizeCollect, 'Mens') === false)) && $coupleDetailsVal==0 ) 
                                                <tr>
                                                    <td>Andaaz Size</td>
                                                    <td>{{ $productmeasure['andaazSize'] ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Body Height</td>
                                                    <td>{{ $productmeasure['bodyHeight'] ?? '-' }}</td>
                                                </tr>
                                            @endif
                                        @endif   
                                    </tbody>
                                @endif
                            @endif
                        </table> 
                    </div>
                    @include('orders.partials.specialinstruction') 
                    @include('orders.partials.addons',['product_size' => $product->product_size])
                    @include('orders.partials.addonsareewear',['product_size' => $product->product_size])
                </td>
            </tr>
       @endforeach
    @else
        @php
            $productsRefunded = getRefundedItemsByOrderId($increment_id);
            $count=1;
        @endphp   

        @if($productsRefunded->count() > 0)
            @foreach($productsRefunded as $i => $product)
            <tr>
                <td>{{ $count;}}</td>
                <td class="statement-td d-block d-md-table-cell td img" data-label="@lang('#')" style="width: 50px;">
                    <div class="card mb-0">
                        <div class="d-flex flex-column flex-md-row align-items-start"> 
                            <img src="{{ Str::contains($product->product_img, 'https://assets2.andaazfashion.com') ? $product->product_img : asset($product->product_img) }}" class="img-fluid me-2 mb-2 mb-md-0 BBB" alt="{{ $product->product_name }}">
                        </div>
                    </div>
                </td>
                <td>
                    <p style="color:#ff0000;text-transform: uppercase;font-size: 22px;"><strong>Refunded</strong></p>
                </td>
                <td colspan="3">
                    <p>{{ $product->product_sku }}</p>
                    <p><strong>{{ $product->order_id }}</strong></p>
                </td>
            </tr>
            @php
                $count++;
            @endphp

            @endforeach
        @endif
    @endif
    </tbody> 
</table>
@include('orders.partials.add-measurement-pop') 
<style type="text/css">
    .style-factory-hide,.style-bpu-hide,
    .stylehide{display: none !important;}
</style>
@if(!isset($_GET['sku']))
    @php 
        $epsplTracking = getEpsplTrackingByOrderId($order->increment_id); 
        $countSize = 0;
        $trackNoLbl= 'AWB No.';
    @endphp

    @if($epsplTracking->isNotEmpty())

        @foreach($epsplTracking as $epsplTrack)
            <div class="epspl-wrap mt-3 mb-3">

                {{-- Top info bar --}}
                <div class="epspl-infobar">
                    <div class="epspl-info-item">
                        <span class="epspl-label">
                        @php
                            if($epsplTrack->carrier == 'FEDEX'){
                                $trackNoLbl = 'Tracking Number';
                            }
                        @endphp
                        {{ $trackNoLbl }}
                        </span>
                        <span class="epspl-value">{{ $epsplTrack->tracking_number }}</span>
                    </div>                     
                    <div class="epspl-info-item">
                        <span class="epspl-label">Booking Date:</span>
                        <span class="epspl-value">{{ $epsplTrack->booking_date ?? '---' }}</span>
                    </div>
                    <div class="epspl-info-item">
                        <span class="epspl-label">Reference No:</span>
                        <span class="epspl-value">{{ $epsplTrack->reference_no ?? '---' }}</span>
                    </div>
                    <div class="epspl-info-item">
                        <span class="epspl-label">Tracking No:</span>
                        <span class="epspl-value">{{ $epsplTrack->tracking_no ?? '---' }}</span>
                    </div>
                    <div class="epspl-info-item">
                        <span class="epspl-label">Packages:</span>
                        <span class="epspl-value">{{ $epsplTrack->packages ?? '---' }}</span>
                    </div>
                    <div class="epspl-info-item">
                        <span class="epspl-label">Status:</span>
                        <span class="epspl-value epspl-status {{ $epsplTrack->status === 'DELIVERED' ? 'epspl-delivered' : 'epspl-transit' }}">
                            {{ $epsplTrack->status ?? $epsplTrack->status_description }}
                        </span>
                    </div>
                    <div class="epspl-info-item">
                        <span class="epspl-label">Service:</span>
                        <span class="epspl-value">{{ $epsplTrack->service_type ?? '---' }}</span>
                    </div>
                    <div class="epspl-info-item">
                        <span class="epspl-label">Package Type:</span>
                        <span class="epspl-value">{{ $epsplTrack->package_type ?? '---' }}</span>
                    </div>                
                </div>             
                @if($epsplTrack->error_message)
                    <div class="epspl-error">
                        <strong>Error:</strong> {{ $epsplTrack->error_message }}
                    </div>
                @endif

                {{-- Tracking History collapsible --}}
                @php
                    $trackingHistory = collect($epsplTrack->tracking_history ?? $epsplTrack->scan_events)
                        ->unique(fn($e) =>
                            substr(trim($e['Date'] ?? ''), 0, 14) . '|' .
                            trim($e['Time'] ?? '') . '|' .
                            trim($e['Location'] ?? '') . '|' .
                            trim($e['MovementDetail'] ?? $e['description'] ?? $e['Activity'] ?? $e['Remark'] ?? $e['Status'] ?? '')
                        )
                        ->sortByDesc(fn($e) => strtotime(trim($e['Date'] ?? '') . ' ' . trim($e['Time'] ?? '')))
                        ->values();
                @endphp
                @if($trackingHistory->isNotEmpty())
                <div class="epspl-history-wrap">
                    <button class="epspl-history-toggle" type="button" data-toggle="collapse" data-target="#epsplHistory{{ $countSize }}" aria-expanded="true">
                        Tracking History
                        <svg class="epspl-chevron" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="collapse hide" id="epsplHistory{{ $countSize }}">
                        {{-- Origin / Destination cards --}}
                        <div class="epspl-cards">
                            <div class="epspl-card epspl-card-origin">
                                <div class="epspl-card-title">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                                    Origin
                                </div>
                                <div class="epspl-card-body">
                                    <div class="epspl-field">
                                        <span class="epspl-field-label">Location:</span>
                                        <span class="epspl-field-value">{{ $epsplTrack->origin ?? '---' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="epspl-card epspl-card-dest">
                                <div class="epspl-card-title">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
                                    Recipient / Destination
                                </div>
                                <div class="epspl-card-body">
                                    <div class="epspl-field">
                                        <span class="epspl-field-label">Consignee:</span>
                                        <span class="epspl-field-value">{{ $epsplTrack->consignee ?? '---' }}</span>
                                    </div>
                                    <div class="epspl-field">
                                        <span class="epspl-field-label">Location:</span>
                                        <span class="epspl-field-value">{{ $epsplTrack->destination ?? '---' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="epspl-timeline">
                            @foreach($trackingHistory as $event)
                            @php
                                $dat = $event['date'] ?? '';
                                $dateFedEx='';
                                if($dat){
                                    $dateFedEx = format_tracking_datetime($dat);
                                }
                                
                                $evDate     = trim(($event['Date'] ?? $dateFedEx) . ' ' . ($event['Time'] ?? ''));
                                $evMovementDetail = $event['MovementDetail'] ?? $event['Activity'] ?? $event['Remark'] ?? $event['Status'] ?? $event['description'];
                                $evLocation = $event['Location'] ?? $event['location'] ?? '---';
                                $evRemark   = $event['Activity']  ?? ($event['activity']  ?? ($event['Remark'] ?? ($event['remark'] ?? ($event['Status'] ?? ($event['status'] ?? $evMovementDetail)))));
                            @endphp
                            <div class="epspl-tl-row">
                                <div class="epspl-tl-date">{{ $evDate ?: '---' }}</div>
                                <div class="epspl-tl-spine">
                                    <div class="epspl-tl-dot"></div>
                                    <div class="epspl-tl-line"></div>
                                </div>
                                <div class="epspl-tl-content">
                                    <span class="epspl-tl-loc">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
                                        <strong>Location:</strong> {{ $evLocation }}
                                    </span>
                                    <span class="epspl-tl-remark">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="7" y1="8" x2="17" y2="8"/><line x1="7" y1="12" x2="13" y2="12"/></svg>
                                        <strong>Remark:</strong> {{ $evRemark }} {{$event['description'] ?? ''}}
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                @if($trackingHistory->isNotEmpty())
                    @php $countSize++; @endphp
                @endif 

                @endif

                @if($epsplTrack->last_fetched_at)
                <div class="epspl-footer">Last synced: {{ $epsplTrack->last_fetched_at->format('d M Y H:i') }}</div>
                @endif
            </div>
        @endforeach
        <style>
            .epspl-wrap { border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; font-size: 13px; background: #fff; }

            /* Info bar */
            .epspl-infobar { display: flex; flex-wrap: wrap; gap: 0; border-bottom: 1px solid #e0e0e0; padding: 12px 16px; background: #e7aaaa; }
            .epspl-info-item { flex: 1 1 auto; min-width: 130px; padding: 4px 12px 4px 0; }
            .epspl-label { display: block; color: #1e3a5f; font-size: 12px; margin-bottom: 2px; }
            .epspl-value { font-weight: 600; color: #222; }
            .epspl-status { font-weight: 700; }
            .epspl-delivered { color: #16a34a; }
            .epspl-transit { color: #d97706; }

            /* Cards */
            .epspl-cards { display: flex; gap: 16px; padding: 16px; }
            .epspl-card { flex: 1; border-radius: 8px; padding: 14px 16px; border: 1px solid #e0e0e0; }
            .epspl-card-origin { background: #fffbeb; border-color: #fde68a; }
            .epspl-card-dest   { background: #f0fdf4; border-color: #bbf7d0; }
            .epspl-card-title  { display: flex; align-items: center; gap: 6px; font-weight: 700; font-size: 14px; color: #1e3a5f; margin-bottom: 12px; }
            .epspl-field { margin-bottom: 6px; }
            .epspl-field-label { color: #888; font-size: 11px; display: block; }
            .epspl-field-value { font-weight: 600; color: #222; }

            /* Error */
            .epspl-error { margin: 0 16px 12px; padding: 8px 12px; background: #fef2f2; border: 1px solid #fca5a5; border-radius: 6px; color: #dc2626; font-size: 12px; }

            /* History toggle */
            .epspl-history-toggle { width: 100%; display: flex; justify-content: space-between; align-items: center; background: #1e3a5f; color: #fff; font-weight: 700; font-size: 14px; padding: 10px 16px; border: none; cursor: pointer; }
            .epspl-history-toggle:hover { background: #16304f; }
            .epspl-chevron { transition: transform .2s; }
            .epspl-history-toggle[aria-expanded="false"] .epspl-chevron { transform: rotate(-90deg); }

            /* Timeline */
            .epspl-timeline { padding: 12px 16px; max-height: 340px; overflow-y: auto; background: #fff; }
            .epspl-tl-row { display: flex; align-items: flex-start; margin-bottom: 0; }
            .epspl-tl-date { width: 100px; min-width: 100px; text-align: right; padding-right: 12px; color: #555; font-size: 12px; padding-top: 2px; }
            .epspl-tl-spine { display: flex; flex-direction: column; align-items: center; width: 20px; min-width: 20px; }
            .epspl-tl-dot  { width: 12px; height: 12px; border-radius: 50%; background: #1e3a5f; flex-shrink: 0; margin-top: 4px; }
            .epspl-tl-line { width: 2px; flex: 1; min-height: 30px; background: #c7d2e0; }
            .epspl-tl-row:last-child .epspl-tl-line { display: none; }
            .epspl-tl-content { flex: 1; padding: 0 0 24px 12px; display: flex; flex-wrap: wrap; gap: 6px 24px; }
            .epspl-tl-loc, .epspl-tl-remark { display: flex; align-items: center; gap: 4px; color: #333; font-size: 13px; }
            .epspl-tl-loc svg, .epspl-tl-remark svg { color: #1e3a5f; flex-shrink: 0; }

            /* Footer */
            .epspl-footer { text-align: right; padding: 6px 16px; font-size: 11px; color: #aaa; border-top: 1px solid #f0f0f0; background: #fafafa; }

            @media (max-width: 768px) {
                .epspl-cards { flex-direction: column; }
                .epspl-infobar { gap: 4px; }
                .epspl-tl-date { width: 80px; min-width: 80px; font-size: 11px; }
            }
        </style> 
    @endif
@endif