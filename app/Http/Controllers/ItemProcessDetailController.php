<?php

namespace Vanguard\Http\Controllers;

use Illuminate\Http\Request;

use Vanguard\Models\ItemProcessDetail;
use Vanguard\Http\Controllers\Controller;

class ItemProcessDetailController extends Controller
{
    public function index()
    {
        $items = ItemProcessDetail::all();
        return view('productdetail.index', compact('items'));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        ItemProcessDetail::create($data);
        return redirect()->back()->with('success', 'Item process detail added successfully');
    }

    // Add edit, update, delete methods as needed
}
