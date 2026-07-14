<?php //use Illuminate\Support\Facades\DB; 
use Vanguard\Models\Contact;

?>
@extends('layouts.app')
@section('title', 'Edit Contact')
@section('content')
    <div class="container mt-5"> 
        <h2>Edit Contact</h2>

        @if ($errors->any())
            <div style="color: red;">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <p style="text-align: right;"><a href="{{ route('contact') }}">Back to Contacts</a></p>
        <form action="{{ route('contacts.update', $contact->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" name="name" value="{{ $contact->name }}" class="form-control" required> 
            </div>

            <div class="mb-3">
                <label for="sku" class="form-label">SKU</label>
                <input type="text" name="sku" value="{{ $contact->sku }}" class="form-control" required> 
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Price</label>
                <input type="text" name="price" value="{{ $contact->price }}" class="form-control" required> 
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" class="form-control" required>{{ $contact->description }}</textarea>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Image</label>
                <input type="file" name="image" class="form-control">
            </div>

            @if ($contact->image)
                <img src="{{ asset('images/' . $contact->image) }}" width="100"> <br><br>
            @endif 
            <button type="submit" class="btn btn-primary">Update</button>
        </form>

        <?php  /*
            $orderid = $contact->id; // replace with your orderid value

 
            // Perform the query to fetch order details along with address and items
            $orders = DB::table('contacts')
                ->join('order_address', 'contacts.id', '=', 'order_address.orderid')
                ->join('order_item', 'contacts.id', '=', 'order_item.orderid')
                ->select(
        'contacts.id as order_id', 'contacts.id', 'contacts.name', 'contacts.sku', 'contacts.price', 'contacts.description', 'contacts.image', 
        'order_address.street', 'order_address.pincode', 'order_address.city', 'order_address.phone',
        'order_item.itemcode', 'order_item.comment', 'order_item.price as item_price', 'order_item.size'
    )
                ->where('contacts.id', $orderid)
                ->get(); // get the first order matching the orderid
if ($orders->isNotEmpty()) {
     $currentOrder = null;
    foreach ($orders as $order) {
        if ($currentOrder != $order->id) {
        // If we're starting a new order, display the main order info
            if ($currentOrder !== null) {
                echo "\n"; // Space between orders
            }
            $currentOrder = $order->id;
            // Display order details
            echo "Order ID: " . $order->id . "<br>";
            echo "Customer Name: " . $order->name . "<br>";
           /* echo "Status: " . $order->status . "\n";
            echo "Amount: " . $order->amount . "\n";
            echo "Web: " . $order->web . "\n";* /
            echo "SKU: " . $order->sku . "<br>";
            echo "Price: " . $order->price . "<br>";
            echo "Description: " . $order->description . "<br>";
            echo "Image: " . $order->image . "<br>";

            // Display the order's address
            echo "------------------------------- <br>";
            echo "Street: " . $order->street . "<br>";
            echo "Pincode: " . $order->pincode . "<br>";
            echo "City: " . $order->city . "<br>";
            echo "Phone: " . $order->phone . "<br>";
            echo "================================================================= <br>";
             echo "<table class='table table-striped table-bordered table-hover'>";
        echo "<thead><tr>
        <th scope='col'>Item Code</th>
        <th scope='col'>Comment</th>
        <th scope='col'>Item Price</th>
        <th scope='col'>Size</th>        
        </tr><thead><tbody>";
        }
        // Display order items
       
        echo "<tr>
        <td>".$order->itemcode."</td>
        <td>".$order->comment."</td>
        <td>".$order->price."</td>
        <td>".$order->size."</td>       
        </tr>";
        
        
    }echo "<tbody></table>";
} else {
    echo "Order not found.";
}  
*/ ?>
    
    </div>
@endsection

