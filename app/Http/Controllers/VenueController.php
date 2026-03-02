<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use Illuminate\Http\Request;

class VenueController extends Controller
{
    public function index()
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        $venues = Venue::all();
        return view('venues.index', compact('venues'),['pageConfigs'=> $pageConfigs]);
    }

    public function create()
    {
        return view('venues.create');
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'seating_capacity' => 'required|integer|min:1',
            'amenities' => 'nullable|array',
            'is_active' => 'boolean'
        ]);

        $venue = Venue::create($validated);

        return redirect()->route('venues.index')->with('success', 'Venue created successfully.');
    }

    public function show(Venue $venue)
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        return view('venues.show', compact('venue'),['pageConfigs'=> $pageConfigs]);
    }

    public function edit(Venue $venue)
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        return view('venues.edit', compact('venue'),['pageConfigs'=> $pageConfigs]);
    }

    public function update(Request $request, Venue $venue)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'seating_capacity' => 'required|integer|min:1',
            'amenities' => 'nullable|array',
            'is_active' => 'boolean'
        ]);

        $venue->update($validated);

        return redirect()->route('venues.index')->with('success', 'Venue updated successfully.');
    }

    public function destroy(Venue $venue)
    {
        $venue->delete();
        return redirect()->route('venues.index')->with('success', 'Venue deleted successfully.');
    }
}
