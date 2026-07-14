@extends('layouts.app')
@section('title', 'Contact Us')
@section('content')  
    <div class="container mt-5"> 
        <h2>Contacts List</h2>
        <p style="text-align: right;">
             <a href="{{ route('contacts.create') }}">Contact Us</a>

        </p>
        <table class="table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Name</th>
                    <th scope="col">SKU</th>
                    <th scope="col">Price</th>
                    <th scope="col">Description</th>
                    <th scope="col">Image</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($contacts as $contact)
                    <tr>
                        <td>{{ $contact->id }}</td>
                        <td>{{ $contact->name }}</td>
                        <td>{{ $contact->sku }}</td>
                        <td>{{ $contact->price }}</td>
                        <td>{{ $contact->description }}</td>
                        <td>
                            @if ($contact->image)
                                <img src="{{ asset('images/'.$contact->image) }}" width="50">
                            @else
                                No Image
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('contacts.edit', $contact->id) }}">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
 @endsection   