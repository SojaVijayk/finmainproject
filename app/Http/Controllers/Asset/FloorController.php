<?php

namespace App\Http\Controllers\Asset;

use App\Http\Controllers\Controller;
use App\Models\Asset\Floor;
use App\Models\Asset\Location;
use Illuminate\Http\Request;

class FloorController extends Controller
{
    public function index()
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        $floors = Floor::with('location')->latest()->get();
        return view('assets.masters.floors.index', compact('floors'), ['pageConfigs' => $pageConfigs]);
    }

    public function create()
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        $locations = Location::all();
        return view('assets.masters.floors.create', compact('locations'), ['pageConfigs' => $pageConfigs]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'location_id' => 'required|exists:locations,id',
            'name' => 'required|string|max:255',
        ]);

        Floor::create($request->all());

        return redirect()->route('asset.floors.index')
            ->with('success', 'Floor created successfully.');
    }

    public function edit($id)
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        $floor = Floor::findOrFail($id);
        $locations = Location::all();
        return view('assets.masters.floors.edit', compact('floor', 'locations'), ['pageConfigs' => $pageConfigs]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'location_id' => 'required|exists:locations,id',
            'name' => 'required|string|max:255',
        ]);

        $floor = Floor::findOrFail($id);
        $floor->update($request->all());

        return redirect()->route('asset.floors.index')
            ->with('success', 'Floor updated successfully.');
    }

    public function destroy($id)
    {
        Floor::findOrFail($id)->delete();
        return redirect()->route('asset.floors.index')
            ->with('success', 'Floor deleted successfully.');
    }
}
