<?php

namespace Vanguard\Http\Controllers\Web;

use Illuminate\Http\Request;
use Vanguard\Models\Addons;
use Vanguard\Http\Controllers\Controller; 

class AddonsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:addons');
    }
    public function index()
    {
        $addons = Addons::all();
        return view('addons.index', compact('addons'));
    }

    public function create()
    {
        return view('addons.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'addoncategory' => 'required',
            'sku' => 'required|string|max:255',
            'price' => 'required|numeric',
        ]);

        Addons::create($request->all());
        return redirect()->route('addons.index')->with('success', 'Addon created successfully.');
    }

    public function edit($id)
    {
        $addon = Addons::findOrFail($id);
        return view('addons.edit', compact('addon'));
    }

    public function update(Request $request, $id)
    {
        $addon = Addons::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'addoncategory' => 'required',
            'sku' => 'required|string|max:255',
            'price' => 'required|numeric',
        ]);

        $addon->update($request->all());
        return redirect()->route('addons.index')->with('success', 'Addon updated successfully.');
    }

    public function destroy($id)
    {
        $addon = Addons::findOrFail($id);
        $addon->delete();
        return redirect()->route('addons.index')->with('success', 'Addon deleted successfully.');
    }
}
