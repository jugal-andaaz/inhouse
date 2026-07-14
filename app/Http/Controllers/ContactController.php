<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;

class ContactController extends Controller
{
     public function __construct()
    {
        $this->middleware('permission:contacts');
    }
    // Show form
    public function create()
    {
        return view('contacts.create');
    }

    public function edit($id)
    {
        $contact = Contact::findOrFail($id);
        return view('contacts.edit', compact('contact'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'sku' => 'required',
            'price' => 'required',
            'description' => 'required',
            'image' => 'nullable|image|max:2048'
        ]);

        $contact = Contact::findOrFail($id);
        
        // Handle Image Upload
        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('images'), $imageName);
            
            // Delete old image if exists
            if ($contact->image) {
                $oldImagePath = public_path('images/' . $contact->image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            $contact->image = $imageName;
        }

        // Update other fields
        $contact->update([
            'name' => $request->name,
            'sku' => $request->sku,
            'price' => $request->price,
            'description' => $request->description,
        ]);

       // return redirect()->route('contact.index')->with('success', 'Contact updated successfully!');
        return redirect()->route('contact')->with('success', 'Contact updated successfully!');

    }

    // Store data
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:255',
            'price' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $imageName = time().'.'.$request->image->extension();
            $request->image->move(public_path('images'), $imageName);
        } else {
            $imageName = null;
        }

        Contact::create([
            'name' => $request->name,
            'sku' => $request->sku,
            'price' => $request->price,
            'description' => $request->description,
            'image' => $imageName,
        ]);

        return redirect()->route('contact')->with('success', 'Contact saved successfully!');
    }

    // Display all records
    public function index()
    {
        $contacts = Contact::all();
        return view('contacts.index', compact('contacts'));
    }
}