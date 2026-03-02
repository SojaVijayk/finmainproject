<?php

namespace App\Http\Controllers\Asset;

use App\Http\Controllers\Controller;
use App\Models\Asset\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index()
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        $locations = Location::latest()->get();
        return view('assets.masters.locations.index', compact('locations'), ['pageConfigs' => $pageConfigs]);
    }

    public function create()
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        return view('assets.masters.locations.create', ['pageConfigs' => $pageConfigs]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
        ]);

        Location::create($request->all());

        return redirect()->route('asset.locations.index')
            ->with('success', 'Location created successfully.');
    }

    public function edit($id)
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        $location = Location::findOrFail($id);
        return view('assets.masters.locations.edit', compact('location'), ['pageConfigs' => $pageConfigs]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
        ]);

        $location = Location::findOrFail($id);
        $location->update($request->all());

        return redirect()->route('asset.locations.index')
            ->with('success', 'Location updated successfully.');
    }

    public function destroy($id)
    {
        Location::findOrFail($id)->delete();
        return redirect()->route('asset.locations.index')
            ->with('success', 'Location deleted successfully.');
    }
}
