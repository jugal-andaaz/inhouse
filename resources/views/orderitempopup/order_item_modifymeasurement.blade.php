@extends('layouts.app')

@section('page-title', __('Orders'))
@section('page-heading', __('Orders')) 

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/orderdetails.css') }}" media="all" type="text/css">
@endpush

<h4 class="m-3">Modify Measurement</h4>
@php  
    $productskuidCollection = getProductSkuAndMmtid($_GET['ordid'],$_GET['pid']); 
    $productid = $productskuidCollection['productid'];
    $productsku = $productskuidCollection['productsku'];
    $productsize = $productskuidCollection['productsize'];
    $standardMeasur=[];
    $readySized = 'checked';$woman ='selected';
    $customTailored = $Unstitched = $semiStitched = $men = $couple = '';
@endphp

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-12 mx-auto bg-white">
                <div class="card border-0">
                    <form method="POST" action="{{ route('order.item.addmeasurement') }}" id="itemmeasurementForm" enctype="multipart/form-data">
                        @csrf
                        <table class="table table-bordered table-striped product-tbl-font">
                            <tbody>
                        @if($_GET['tailSrvc']=='customTailored')
                            @forelse($collections as $myMeasure)
                                <input type="hidden" name="mmpid" value="{{$myMeasure->id}}">

                                @if ($myMeasure->type && $myMeasure->mtype && $myMeasure->unit)
                                    <tr>
                                        <td data-label="@lang('Product Type')">Product Type</td>
                                        <td colspan="2">{{ $myMeasure->type }} ({{ $myMeasure->mtype }}) {{ $myMeasure->unit }}</td>
                                    </tr>
                                @endif
                                @php renderMeasureRow('Bust', 'bust', $myMeasure->bust, $myMeasure->unit); @endphp
                                @php renderMeasureRow('Under Bust', 'under_bust', $myMeasure->under_bust, $myMeasure->unit); @endphp
                                @php renderMeasureRow('HPS Under Bust', 'hps_under_bust', $myMeasure->hps_under_bust, $myMeasure->unit); @endphp
                                @php renderMeasureRow('Shoulder', 'shoulder', $myMeasure->shoulder, $myMeasure->unit); @endphp
                                @php renderMeasureRow('Around Belly Button', 'arround_belly_button', $myMeasure->arround_belly_button, $myMeasure->unit); @endphp
                                @php renderMeasureRow('Waist', 'waist', $myMeasure->waist, $myMeasure->unit); @endphp
                                @php renderMeasureRow('Around Hips', 'hips', $myMeasure->hips, $myMeasure->unit); @endphp
                                @php renderMeasureRow('Arround Arm', 'arround_arm', $myMeasure->arround_arm, $myMeasure->unit); @endphp
                                @php renderMeasureRow('Sleeve Length', 'sleeve_length', $myMeasure->sleeve_length, $myMeasure->unit); @endphp
                                @php renderMeasureRow('Top Length', 'top_length', $myMeasure->top_length, $myMeasure->unit); @endphp
                                @php renderMeasureRow('Bottom Length', 'bottom_length', $myMeasure->bottom_length, $myMeasure->unit); @endphp
                                @php renderMeasureRow('Modest Requirement', 'modest_requirement', $myMeasure->modest_requirement, ''); @endphp
                                @php renderMeasureRow('Special Message', 'special_msg', $myMeasure->special_msg,''); @endphp
                                @php renderMeasureRow('Waist Type', 'waist_type', $myMeasure->waist_type, ''); @endphp
                                @php renderMeasureRow('Height', 'height', $myMeasure->height, $myMeasure->unit); @endphp
                                @php renderMeasureRow('Heels', 'heels', $myMeasure->heels, $myMeasure->unit); @endphp
                                @php renderMeasureRow('Arm Hole', 'arm_hole', $myMeasure->arm_hole, $myMeasure->unit); @endphp
                                @php renderMeasureRow('Dress/Kameez Length', 'dresskameez_length', $myMeasure->dresskameez_length, $myMeasure->unit); @endphp
                                @php renderMeasureRow('Adornment', 'adornment', $myMeasure->adornment, $myMeasure->unit); @endphp
                                @php renderMeasureRow('Blouse Lengths', 'blouse_length', $myMeasure->blouse_length, $myMeasure->unit); @endphp
                                @php renderMeasureRow('Blouse Pad', 'blousepad', $myMeasure->blousepad, $myMeasure->unit); @endphp
                                @php renderMeasureRow('Prestich', 'prestich', $myMeasure->prestich, $myMeasure->unit); @endphp
                                @php renderMeasureRow('Around Neck', 'around_neck', $myMeasure->around_neck, $myMeasure->unit); @endphp
                                @php renderMeasureRow('Mid Thigh Length', 'thigh_length', $myMeasure->thigh_length, $myMeasure->unit); @endphp
                                @php renderMeasureRow('Crotch Length', 'crotch_length', $myMeasure->crotch_length, $myMeasure->unit); @endphp
                                @php renderMeasureRow('Mori Length', 'mori_length', $myMeasure->mori_length, $myMeasure->unit); @endphp
                                @php renderMeasureRow('Calf Length', 'calf_length', $myMeasure->calf_length, $myMeasure->unit); @endphp
                                @php renderMeasureRow('Wrist Size', 'wrist_size', $myMeasure->wrist_size, $myMeasure->unit); @endphp
                            @empty
                                <tr><td colspan="3">No records found.</td></tr>
                            @endforelse
                        @endif

                        @if($_GET['tailSrvc']=='ready')
                            @php
                                $standardMeasur = getStandardMeasurementByItemId($productid);
                            @endphp

                            @php 
                                $productSizes = str_replace('"','',$productsize);
                                $productSizeCollection = explode('|', $productSizes);
                            @endphp
                            @foreach($productSizeCollection as $productSizeCollect)
                                @php
                                    $productSize = explode(':', trim($productSizeCollect), 2);
                                @endphp 

                                @if(count($productSize) == 2)
                                    @php
                                    if(trim($productSize[0])=='Andaaz Size' || trim($productSize[0])=='Body Chest Size'){
                                        $productmeasure['andaazSize'] = trim($productSize[1]);
                                    }
                                    if(trim($productSize[0])=='Body Height'){
                                        $productmeasure['bodyHeight'] = trim($productSize[1]);
                                    }
                                    @endphp
                                @endif
                            @endforeach  
                            @php
                                if(isset($productmeasure['andaazSize'])){
                                    $standardMeasurBustSize = $productmeasure['andaazSize'];
                                }else{
                                    $standardMeasurBustSize='';
                                }
                                
                                if(isset($productmeasure['bodyHeight'])){
                                    $standardMeasurBodyHeight = $productmeasure['bodyHeight'];
                                }else{
                                    $standardMeasurBodyHeight='';
                                }
                            @endphp
                            <tr>
                                <td data-label="Enter Your Body Height">Product Type</td>
                                <td colspan="2">
                                    <div class="measuretext mb-3">
                                        @php
                                        if($standardMeasur){
                                            if($standardMeasur->service=='Standard Size'){
                                                $readySized='checked';
                                                $Unstitched = $customTailored= $semiStitched = '';
                                            }
                                            if($standardMeasur->service=='Semi Stitched'){
                                                $semiStitched='checked';
                                                $Unstitched = $customTailored= $readySized = '';
                                            }
                                            if($standardMeasur->service=='Unstitched'){
                                                $Unstitched='checked';
                                                $semiStitched= $customTailored= $readySized = '';
                                            }
                                            if($standardMeasur->service=='Custom Tailored'){
                                                $customTailored='checked';
                                                $Unstitched = $semiStitched= $readySized = '';
                                            }
                                            if($standardMeasur->type=='men'){
                                                $men='selected';
                                                $woman= '';$couple = '';
                                            }if($standardMeasur->type=='woman'){
                                                $woman='selected';
                                                $men= '';$couple = '';
                                            }
                                            if($standardMeasur->type=='couple'){
                                                $couple='selected';
                                                $men= '';$woman= '';
                                            }
                                        }
                                        @endphp 
                                        <label>
                                        <input type="radio" name="sizetype" value="Standard Size" {{ $readySized }}> Standard Size</label>
                                        &nbsp;&nbsp;
                                        <label><input type="radio" name="sizetype" value="Semi Stitched" {{$semiStitched}}> Semi Stitched</label>
                                        &nbsp;&nbsp;
                                        <label><input type="radio" name="sizetype" value="Unstitched" {{$Unstitched}}> Unstitched</label>
                                        &nbsp;&nbsp;
                                        <label><input type="radio" name="sizetype" value="Custom Tailored" {{$customTailored}}> Custom Tailored</label>
                                    </div>
                                    <select name="typechk" id="typechk" class="measuretext mb-3" style="width: 215px;">
                                        <option value="">-- Gender --</option>
                                        <option value="woman" {{$woman}}>Woman</option>
                                        <option value="men" {{$men}}>Men</option>
                                        <option value="couple" {{$couple}}>Couple</option> 
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td data-label="Measurement Type">Measurement Type</td>
                                <td colspan="2">
                                    <input name="measurement_type" type="text" id="measurement_type" value="{{$standardMeasur->measurement_type ?? ''}}">
                                </td>
                            </tr>
                            @php
                                renderMeasureRow('Andaaz Size', 'Bust_Size', $standardMeasur->Bust_Size ?? $standardMeasurBustSize, $standardMeasur->unit ?? ''); 
                                renderMeasureRow('Under Bust', 'under_bust', $standardMeasur->under_bust ?? '', $standardMeasur->unit ?? '');
                                renderMeasureRow('HPS Under Bust', 'hps_under_bust', $standardMeasur->hps_under_bust ?? '', $standardMeasur->unit ?? '');
                                renderMeasureRow('Shoulder', 'shoulder', $standardMeasur->shoulder ?? '', $standardMeasur->unit ?? '');
                                renderMeasureRow('Around Belly Button', 'arround_belly_button', $standardMeasur->arround_belly_button ?? '', $standardMeasur->unit ?? '');
                                renderMeasureRow('Arround Arm', 'arround_arm', $standardMeasur->arround_arm ?? '', $standardMeasur->unit ?? '');
                                renderMeasureRow('Sleeve Length', 'Sleeve_Length', $standardMeasur->Sleeve_Length ?? '', $standardMeasur->unit ?? '');
                                renderMeasureRow('Dress-Top Length', 'Dress_Top_Length', $standardMeasur->Dress_Top_Length ?? '', $standardMeasur->unit ?? '');
                                renderMeasureRow('Bottom Length', 'bottom_length', $standardMeasur->bottom_length ?? '', $standardMeasur->unit ?? '');
                                renderMeasureRow('Modest Requirement', 'modest_requirement', $standardMeasur->modest_requirement ?? '', '');
                                renderMeasureRow('Special Message', 'special_msg', $standardMeasur->special_msg ?? '','');
                                renderMeasureRow('Waist', 'waist', $standardMeasur->waist ?? '', '');
                                renderMeasureRow('Hips', 'hips', $standardMeasur->hips ?? '', '');
                                renderMeasureRow('Waist Type', 'waist_type', $standardMeasur->waist_type ?? '', '');
                                renderMeasureRow('Height', 'Body_Height', $standardMeasur->Body_Height ?? $standardMeasurBodyHeight, $standardMeasur->unit ?? '');
                                renderMeasureRow('Heels', 'heels', $standardMeasur->heels ?? '', $standardMeasur->unit ?? '');
                                renderMeasureRow('Arm Hole', 'arm_hole', $standardMeasur->arm_hole ?? '', $standardMeasur->unit ?? '');
                                renderMeasureRow('Dress/Kameez Length', 'dresskameez_length', $standardMeasur->dresskameez_length ?? '', $standardMeasur->unit ?? '');
                                renderMeasureRow('Adornment', 'adornment', $standardMeasur->adornment ?? '', $standardMeasur->unit ?? '');
                                renderMeasureRow('Blouse Lengths', 'blouse_length', $standardMeasur->blouse_length ?? '', $standardMeasur->unit ?? '');
                                renderMeasureRow('Blouse Pad', 'blousepad', $standardMeasur->blousepad ?? '', $standardMeasur->unit ?? '');
                                renderMeasureRow('Prestich', 'prestich', $standardMeasur->prestich ?? '', $standardMeasur->unit ?? '');
                                renderMeasureRow('Around Neck', 'around_neck', $standardMeasur->around_neck ?? '', $standardMeasur->unit ?? '');
                                renderMeasureRow('Mid Thigh Length', 'thigh_length', $standardMeasur->thigh_length ?? '', $standardMeasur->unit ?? '');
                                renderMeasureRow('Crotch Length', 'crotch_length', $standardMeasur->crotch_length ?? '', $standardMeasur->unit ?? '');
                                renderMeasureRow('Mori Length', 'mori_length', $standardMeasur->mori_length ?? '', $standardMeasur->unit ?? '');
                                renderMeasureRow('Calf Length', 'calf_length', $standardMeasur->calf_length ?? '', $standardMeasur->unit ?? '');
                                renderMeasureRow('Wrist Size', 'wrist_size', $standardMeasur->wrist_size ?? '', $standardMeasur->unit ?? '');


                    renderMeasureRow('Womens Garment Bust Size', 'womens_garment_bust_size', $standardMeasur->womens_garment_bust_size ?? '', $standardMeasur->unit ?? '');
                    renderMeasureRow('Womens Body Height', 'womens_body_height', $standardMeasur->womens_body_height ?? '', $standardMeasur->unit ?? '');
                    renderMeasureRow('Mens Body Chest Size', 'mens_body_chest_size', $standardMeasur->mens_body_chest_size ?? '', $standardMeasur->unit ?? '');
                    renderMeasureRow('Mens Body Height', 'mens_body_height', $standardMeasur->mens_body_height ?? '', $standardMeasur->unit ?? '');

           
                            @endphp
                        @endif
                            </tbody>
                        </table> 
                        <input type="hidden" name="productid" value="{{ $productid }}" />
                        <input type="hidden" name="productsku" value="{{ $productsku }}" />
                        <input type="hidden" name="orderid" value="{{ $_GET['ordid'] }}" />
                        <input type="hidden" name="tailsrvcid" value="{{ $_GET['tailSrvc'] }}" />
                        <input type="hidden" name="productitemid" value="{{ $_GET['pid'] }}" />
                        <input type="hidden" name="loginuser" value="{{ auth()->user()->present()->nameOrEmail }}">
                    @php
                        $specialInstructCollection = getSpecialInstructionBySkuItemId($productsku, $productid);
                    @endphp                    
                        <table class="table table-bordered table-striped ct-edit-table headinginstruction">
                            <thead>
                                <tr>
                                    <th>Heading</th>
                                    <th>Instruction</th>
                                    <th>Image</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="specialInstructionInfo">
                                @foreach($specialInstructCollection as $specialInstruction)
                                <tr class="">
                                    <td>
                                        <span class="display-heading">{{ $specialInstruction->heading }}</span>
                                        <input type="text" name="heading[{{$specialInstruction->id}}]" class="form-control edit-heading d-none" value="{{ $specialInstruction->heading }}">
                                    </td>
                                    <td>
                                        <span class="display-instruction">{{ $specialInstruction->instruction }}</span>
                                        <input type="text" name="instruction[{{$specialInstruction->id}}]" class="form-control edit-instruction d-none" value="{{ $specialInstruction->instruction }}">
                                    </td>
                                    <td>
                                        @if(!empty($specialInstruction->image_url) && ($specialInstruction->image_url!='--'))
                                        <!-- <img src="{{ asset($specialInstruction->image_url) }}" alt="Image" width="50" class="image-preview">
                                        <input type="hidden" name="old_image[]" value="{{ asset($specialInstruction->image_url) }}"> -->

                                        <img src="{{ asset($specialInstruction->image_url) }}" alt="Image" width="50" class="image-preview">
                                        <br><span class="sichkimg deleteimg btn btn-primary p-1 w-100 mb-1"> Delete </span>
                                        <input type="hidden" class="siold-image" name="old_image[{{$specialInstruction->id}}]" value="{{ asset($specialInstruction->image_url) }}">
                                        @else
                                            
                                        @endif
                                        <input type="file" name="image[{{$specialInstruction->id}}]" class="form-control edit-image d-none">
                                    </td>
                                    <td>
                                        <button type="button" class="btn-edit btn btn-primary button-instra w-30">Edit</button>
                                        <button type="button" class="btn-save d-none btn btn-primary button-instra w-30">Save</button>
                                        <button type="button" class="btn-delete btn btn-primary button-instra w-30">Delete</button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        
                        <table class="table table-bordered table-striped ct-edit-table">
                            <thead>
                                <tr>
                                    <th class="ct-instra-th" scope="col" colspan="3" style="color: #ff0000;font-size: 12px;">
                                        "If the Product Type or any other measurement field (e.g., Bust, Under Bust, Shoulder, etc.) is missing or left blank, please add a corresponding entry here."
                                    </th>
                                </tr>
                                <tr>
                                    <th class="ct-instra-th" scope="col">
                                        <div class="head">
                                            <label for="heading">Heading</label>
                                            <input type="text" id="txtHeading" class="form-control mb-2" placeholder="Enter Heading">
                                        </div>
                                    </th>
                                    <th  class="ct-instra-th" scope="col">
                                        <div class="instra"><label for="instruction">Instruction</label>
                                            <input type="text" id="txtInstruction" class="form-control mb-2" placeholder="Enter Instruction">   
                                        </div>
                                    </th>
                                    <th class="ct-instra-th" scope="col">
                                        <div class="instra">
                                            <label for="image">Image</label>
                                            <input type="file" id="txtImage" class="form-control mb-2">
                                            <input type="hidden" id="txtOldImage" class="form-control mb-2">
                                        </div>
                                    </th>
                                    <th  class="ct-instra-th" scope="col">
                                        <div class="button-instra">
                                            <button type="button" id="btnAddInstruction" class="btn btn-primary button-instra w-100">Add</button>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                        </table>
                        <br><br>
<?php /* Add On's condition*/ ?>
                        @php
                            $orderId = $productId = '';
                            $addonsVal =[];
                            $addons = [];
                            $allAddons = getAllAddons($productId); 
                            
                            if (strpos($productsize, 'Add Ons:') !== false) {
                                $parts = explode('|', $productsize);
                                foreach ($parts as $part) {
                                    if (str_contains(trim($part), 'Add Ons:')) {
                                        $addonsString = trim(str_replace('Add Ons:', '', $part));
                                        $addonsString = trim(str_replace('"', '', $addonsString));
                                        $addonsVal = array_merge($addonsVal, array_map('trim', explode(',', $addonsString)));
                                    }
                                }
                            }

                            if (strpos($productsize, 'Blouse Pad:') !== false) {
                                $parts = explode('|', $productsize);
                                foreach ($parts as $part) {
                                    if (str_contains(trim($part), 'Blouse Pad:')) {
                                        $addonsString = trim(str_replace('Blouse Pad:', '', $part));
                                        $addonsString = trim(str_replace('"', '', $addonsString));
                                        $addonsVal = array_merge($addonsVal, array_map('trim', explode(',', $addonsString)));
                                    }
                                }
                            }
                            if (strpos($productsize, 'Fall & Pico:') !== false) {
                                $parts = explode('|', $productsize);
                                foreach ($parts as $part) {
                                    if (str_contains(trim($part), 'Fall & Pico:')) {
                                        $addonsString = trim(str_replace('Fall & Pico:', '', $part));
                                        $addonsString = trim(str_replace('"', '', $addonsString));
                                        $addonsVal = array_merge($addonsVal, array_map('trim', explode(',', $addonsString)));
                                    }
                                }
                            }
                            if (strpos($productsize, 'Petticoat:') !== false) {
                                $parts = explode('|', $productsize);
                                foreach ($parts as $part) {
                                    if (str_contains(trim($part), 'Petticoat:')) {
                                        $addonsString = trim(str_replace('Petticoat:', '', $part));
                                        $addonsString = trim(str_replace('"', '', $addonsString));
                                        $addonsVal = array_merge($addonsVal, array_map('trim', explode(',', $addonsString)));
                                    }
                                }
                            }
                            if (strpos($productsize, 'Convert into Ready to Wear Saree:') !== false) {
                                $parts = explode('|', $productsize);
                                foreach ($parts as $part) {
                                    if (str_contains(trim($part), 'Convert into Ready to Wear Saree:')) {
                                        $addonsString = trim(str_replace('Convert into Ready to Wear Saree:', '', $part));
                                        $addonsString = trim(str_replace('"', '', $addonsString));
                                        $addonsVal = array_merge($addonsVal, array_map('trim', explode(',', $addonsString)));
                                    }
                                }
                            }
                            
                            
                        @endphp

                        <h2>Add On's</h2>
                        <input type="hidden" name="productsizeattr" value='{{$productsize}}' />
                        <table class="table table-bordered table-striped ct-edit-table">
                            <thead>
                                <tr>
                                    <th class="ct-instra-th"> Add On's </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($allAddons as $addons)
                                @php
                                    $style= $class =''; 
                                    
                                    $sareeAddons = [
                                        'Add Blouse Pad',
                                        'Add Inskirt\/Petticoat',
                                        'Add Inskirt/Petticoat',
                                        'Add Fall &amp; Pico',
                                        'Add Fall & Pico',
                                        'Pre Drape This Saree'
                                    ]; 
                                    if(in_array($addons->title,$sareeAddons)){
                                        $class = 'class = sareeaddon';
                                    }
                                @endphp
                                <tr {{$class}}>
                                    <td>
                                        @php
                                            $checked=''; 
                                        @endphp

                                        @if($addonsVal!='' && in_array($addons->title, $addonsVal))
                                            @php
                                                $checked = "checked";
                                            @endphp
                                        @endif

                                        <input type="checkbox" {{$checked}} name="addons[]" value="{{ $addons->title }}"> {{ $addons->title; }} 
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
<?php /* Need to add Add On's condition*/ ?>

                        <button type="submit" class="btn btn-success">Submit All</button>
                    </form>
                    <!-- Loader -->
                    <div id="loading-overlay" style="display: none;">
                        <div class="loader-container">
                            <div class="spinner-border text-primary" role="status"></div>
                            <div class="message">Please wait...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@php
function renderMeasureRow($label, $field, $data, $unit = '') {
    $image = $required = '';
    if (!empty($data) && strpos($data, '|') !== false) {
        list($value, $image) = explode('|', $data);
    } else {
        $value = $data ?? '';
    }
    if($label==='Height' || $field==='Bust_Size' || $field==='bust'){
        $required = 'required';
    }

    echo '<tr>
        <td data-label="' . __($label) . '">' . __($label) . '</td>
        <td><input name="' . $field . 'chk" type="text" id="' . $field . 'chk" class="measuretext mb-3" value="' . $value . '" '.$required.' >';
    if ($image !== '') {
        echo '<div class="imgdiv" style="float:right;"><img class="' . $field . 'chkimg" src="' . asset($image) . '" style="width:50px; max-width:55px;"/>
            <br><span class="' . $field . 'chkimg deleteimg btn btn-primary p-1 w-100 mb-1"> Delete </span></div>';
    }
    echo ' <input name="' . $field . 'chkimg" type="hidden" id="' . $field . 'chkimg" value="' . $image . '">';
    echo '</span>' . ($unit ? ' ' . $unit : '') . '</td><td>
            <input name="' . $field . 'image" type="file" accept="image/*">
        </td>
    </tr>';
}
@endphp
<style>
    .navbar, .col-md-2.sidebar { display: none !important; }
    tr.sareeaddon {background: #ff0 !important;}
</style>
<script type="text/javascript">
    document.getElementById('btnAddInstruction').addEventListener('click', function () {
        var heading = document.getElementById('txtHeading').value.trim();
        var instruction = document.getElementById('txtInstruction').value.trim();
        var imageInput = document.getElementById('txtImage');
        var oldimageInput = document.getElementById('txtOldImage');
        var specialInstructionInfo = document.getElementById('specialInstructionInfo');

        if (!heading && !instruction && !imageInput.files.length) {
            alert('Please fill at least one field or upload an image!');
            return;
        }

        // Create image preview URL
        var imagePreviewUrl = '';
        if (imageInput.files.length) {
            imagePreviewUrl = URL.createObjectURL(imageInput.files[0]);
        }

        // Remove "No records" row if present
        var firstRow = specialInstructionInfo.querySelector('tr');
        if (firstRow && firstRow.children.length === 1) {
            specialInstructionInfo.innerHTML = '';
        }

        var newRow = document.createElement('tr');

        // Create a unique ID for the file input
        var fileInputId = 'imageInput_' + Date.now();

        newRow.innerHTML = `
            <td>
                <input type="hidden" name="addclickspinstruct" class="addclickspinstruct" value="1" />
                <span class="display-heading">${heading}</span>
                <input type="text" name="heading[]" class="form-control edit-heading d-none" value="${heading}">
            </td>
            <td>
                <span class="display-instruction">${instruction}</span>
                <input type="text" name="instruction[]" class="form-control edit-instruction d-none" value="${instruction}">
            </td>
            <td>
                <input type="file" name="image[]" class="form-control edit-image d-none" id="${fileInputId}">
                ${imagePreviewUrl ? `<img src="${imagePreviewUrl}" alt="Preview" class="image-preview" width="50">` : ''}
                <input type="hidden" name="old_image[]" value="${txtOldImage}">
            </td>
            <td>
                <button type="button" class="btn-edit btn btn-primary button-instra w-25">Edit</button>
                <button type="button" class="btn-save d-none btn btn-primary button-instra w-25">Save</button>
                <button type="button" class="btn-delete btn btn-primary button-instra w-28">Delete</button>
            </td>
        `;

        // Append the row
        specialInstructionInfo.appendChild(newRow);

        // Clone and assign image input file to new input
        if (imageInput.files.length) {
            const clonedFileInput = newRow.querySelector(`#${fileInputId}`);
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(imageInput.files[0]);
            clonedFileInput.files = dataTransfer.files;
        }

        // Clear original input boxes
        document.getElementById('txtHeading').value = '';
        document.getElementById('txtInstruction').value = '';
        document.getElementById('txtImage').value = '';
        document.getElementById('txtOldImage').value = '';
    });

    document.getElementById('specialInstructionInfo').addEventListener('click', function (e) {
        if (e.target.classList.contains('btn-edit')) {
            var tr = e.target.closest('tr');
            tr.querySelector('.display-heading').classList.add('d-none');
            tr.querySelector('.display-instruction').classList.add('d-none');
            tr.querySelector('.edit-heading').classList.remove('d-none');
            tr.querySelector('.edit-instruction').classList.remove('d-none');
            var imageInput = tr.querySelector('.edit-image');
            if (imageInput) imageInput.classList.remove('d-none');
            tr.querySelector('.btn-edit').classList.add('d-none');
            tr.querySelector('.btn-save').classList.remove('d-none');
        }

        if (e.target.classList.contains('btn-save')) {
            var tr = e.target.closest('tr');
            var newHeading = tr.querySelector('.edit-heading').value;
            var newInstruction = tr.querySelector('.edit-instruction').value;

            tr.querySelector('.display-heading').textContent = newHeading;
            tr.querySelector('.display-instruction').textContent = newInstruction;

            tr.querySelector('.display-heading').classList.remove('d-none');
            tr.querySelector('.display-instruction').classList.remove('d-none');

            tr.querySelector('.edit-heading').classList.add('d-none');
            tr.querySelector('.edit-instruction').classList.add('d-none');
            var imageInput = tr.querySelector('.edit-image');

            // Show preview if new image selected
            if (imageInput && imageInput.files.length) {
                let img = tr.querySelector('img.image-preview');
                const newImgUrl = URL.createObjectURL(imageInput.files[0]);
                if (img) {
                    img.src = newImgUrl;
                } else {
                    const newImg = document.createElement('img');
                    newImg.src = newImgUrl;
                    newImg.className = 'image-preview';
                    newImg.width = 50;
                    tr.querySelector('td:nth-child(3)').prepend(newImg);
                }
            }

            if (imageInput) imageInput.classList.add('d-none');
            tr.querySelector('.btn-edit').classList.remove('d-none');
            tr.querySelector('.btn-save').classList.add('d-none');
        }

        if (e.target.classList.contains('btn-delete')) {
            e.target.closest('tr').remove();

            var specialInstructionInfo = document.getElementById('specialInstructionInfo');
            if (specialInstructionInfo.children.length === 0) {
                specialInstructionInfo.innerHTML = '<tr><td colspan="4">No records have been added.</td></tr>';
            }
            var hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'deleteclickspinstruct';
            hiddenInput.className = 'deleteclickspinstruct';
            hiddenInput.value = '1';

            // Append it somewhere in the form
            var form = document.querySelector('form'); // Adjust if you have a specific form
            form.appendChild(hiddenInput);
        }
    });
    document.addEventListener('DOMContentLoaded', function () {
        // Add click event to all delete buttons dynamically
        document.querySelectorAll('.deleteimg').forEach(function (btn) {
            btn.addEventListener('click', function () {
                // Extract the base name from the class (e.g., "under_bustchkimg")
                const classes = Array.from(btn.classList);
                const baseClass = classes.find(cls => cls !== 'deleteimg');

                if (baseClass) {
                    // Remove the image
                    const img = document.querySelector('img.' + baseClass);
                    if (img) img.remove();

                    // Clear the hidden input value
                    const input = document.getElementById(baseClass);
                    if (input) input.value = '';
                }
            });
        });
        document.querySelectorAll('.sichkimg').forEach(function (btn) {
            btn.addEventListener('click', function () {
                let td = this.closest('td'); // get the parent <td>
                
                // remove image preview
                let img = td.querySelector('.image-preview');
                if (img) {
                    img.remove();
                }

                // mark hidden input as deleted
                let hiddenInput = td.querySelector('.siold-image');
                if (hiddenInput) {
                    hiddenInput.value = '--'; // mark it for backend

                }

                // optionally hide delete button itself
                this.remove();
            });
        });
    });
</script>
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function () {
        window.attachFormSubmitLoader('itemmeasurementForm', 'btn-success');
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get all radio buttons with name 'sizetype'
    var sizetypeRadios = document.querySelectorAll('input[name="sizetype"]');
    // Get the select element
    var typechkSelect = document.getElementById('typechk');
    // Find the <td> with data-label="Andaaz Size"
    var andaazSizeTd = document.querySelector('td[data-label="Andaaz Size"]');

    // Function to update the label
    function updateLabel() {
        var selectedSizeType = document.querySelector('input[name="sizetype"]:checked').value;
        var selectedType = typechkSelect.value;

        if(selectedSizeType === "Custom Tailored") {
            andaazSizeTd.textContent = "Bust Size";
            if(selectedType === "men" && selectedSizeType !=='Semi Stitched') {
                andaazSizeTd.textContent = "Chest Size";
            }  
        } else {
            andaazSizeTd.textContent = "Andaaz Size";
            if(selectedType === "men" && selectedSizeType !=='Semi Stitched') {
                andaazSizeTd.textContent = "Chest Size";
            } 
        }
    }

    // Attach event listeners
    sizetypeRadios.forEach(function(radio) {
        radio.addEventListener('change', updateLabel);
    });

    typechkSelect.addEventListener('change', updateLabel);

    // Run once on page load to set correct label
    updateLabel();
});
</script>
<!-- Script -->