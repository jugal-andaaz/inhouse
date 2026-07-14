<link rel="stylesheet" href="{{ asset('assets/css/orderdetails.css') }}" media="all" type="text/css">
@extends('layouts.app')

@section('page-title', __("Add On's"))
@section('page-heading', __("Add On's"))

@section('breadcrumbs')
<li class="breadcrumb-item active">
    @lang("Addon's")
</li>
@stop

@section('content')

@include('partials.messages')
 
<h2>{{ isset($addon) ? 'Edit Addon' : 'Create Addon' }} Add On's</h2>
<div class="card">
    <div class="card-body">         
        <div class="row">
            <div class="container mt-5"> 
                <form method="POST" action="{{ isset($addon) ? route('addons.update', $addon->id) : route('addons.store') }}">
                    @csrf
                    @if(isset($addon)) @method('PUT') @endif
                    
                    @php
                        $addoncategories = ['Salwar','Salwar Kameez', 'Saree','Sherwani','Lehenga Choli', 'Kurta Pajama', 'Kurta','Kurti', 'Gown', 'Kurta Pajama with Waistcoat', 'Waist Coat', 'Men Suits'];
                        $selectedCategories = old('addoncategory', isset($addon) ? $addon->addoncategory : []);
                    @endphp
                    
                    <div class="mb-3">
                        <label for="addoncategory" class="form-label">Main Category:</label>
                         
                        <select name="addoncategory[]" id="addoncategory" class="form-select form-control" multiple>
                            @foreach($addoncategories as $addoncategory)
                            <option value="{{ $addoncategory }}" {{ in_array($addoncategory, $selectedCategories) ? 'selected' : '' }}>
                                {{ $addoncategory }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="title" class="form-label">Title:</label>
                        <input type="text" name="title" value="{{ old('title', $addon->title ?? '') }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="sku" class="form-label">SKU:</label>
                        <input type="text" name="sku" value="{{ old('sku', $addon->sku ?? '') }}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price:</label>
                        <input type="text" name="price" value="{{ old('price', $addon->price ?? '') }}" class="form-control">
                    </div>
                    <button class="btn btn-primary" type="submit">{{ isset($addon) ? 'Update Addon' : 'Create Addon' }}</button>
                </form>
            </div> 
        </div> 
    </div> 
</div> 