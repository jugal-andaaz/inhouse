<link rel="stylesheet" href="{{ asset('assets/css/orderdetails.css') }}" media="all" type="text/css">

@extends('layouts.app')

@section('page-title', __("Add On's"))
@section('page-heading', __("Add On's"))

@section('breadcrumbs')
<li class="breadcrumb-item active">
    @lang('Addons')
</li>
@stop

@section('content')

@include('partials.messages')

<div class="card">
    <div class="card-body">
        <div class="row my-3 flex-md-row flex-column-reverse">
            <div class="col-md-6">
                <a href="{{ route('addons.create') }}" class="btn btn-primary btn-rounded float-right">
                    <i class="fas fa-plus mr-2"></i>
                    @lang("New Add On's")
                </a>
            </div>
        </div>
        <div class="row">
            <div class="col-12 @if (! isset($activities)) mx-auto @endif bg-white">
                <div class="card  border-0">
                    <div class="table-responsive" id="users-table-wrapper">
                        <table class="table table-borderless table-striped statement-table ordertop-info">
                            <thead>
                                <tr><th>ID</th>
                                    <th>Title</th>
                                    <th>SKU</th>
                                    <th>Main Category</th>
                                    <th>Price</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($addons as $addon)
                                <tr>
                                    <td>{{ $addon->id }}</td>
                                    <td>{{ $addon->title }}</td>
                                    <td>{{ $addon->sku }}</td>
                                    <td>{{ implode(', ', $addon->addoncategory ?? []) }}</td>
                                    <td>{{ $addon->price }}</td>
                                    <td>
                                        <a href="{{ route('addons.edit', $addon->id) }}">
                                            <button type="submit" class="btn btn-primary">Edit</button>
                                        </a>
                                        <form action="{{ route('addons.destroy', $addon->id) }}" method="POST" style="display:inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-primary">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 