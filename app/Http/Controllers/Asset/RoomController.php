<?php

namespace App\Http\Controllers\Asset;

use App\Http\Controllers\Controller;
use App\Models\Asset\Room;
use App\Models\Asset\Floor;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        $rooms = Room::with('floor.location')->latest()->get();
        return view('assets.masters.rooms.index', compact('rooms'), ['pageConfigs' => $pageConfigs]);
    }

    public function create()
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        $floors = Floor::with('location')->get();
        return view('assets.masters.rooms.create', compact('floors'), ['pageConfigs' => $pageConfigs]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'floor_id' => 'required|exists:floors,id',
            'room_number' => 'required|string|max:255',
            'name' => 'nullable|string|max:255',
        ]);

        Room::create($request->all());

        return redirect()->route('asset.rooms.index')
            ->with('success', 'Room created successfully.');
    }

    public function edit($id)
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        $room = Room::findOrFail($id);
        $floors = Floor::with('location')->get();
        return view('assets.masters.rooms.edit', compact('room', 'floors'), ['pageConfigs' => $pageConfigs]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'floor_id' => 'required|exists:floors,id',
            'room_number' => 'required|string|max:255',
            'name' => 'nullable|string|max:255',
        ]);

        $room = Room::findOrFail($id);
        $room->update($request->all());

        return redirect()->route('asset.rooms.index')
            ->with('success', 'Room updated successfully.');
    }

    public function destroy($id)
    {
        Room::findOrFail($id)->delete();
        return redirect()->route('asset.rooms.index')
            ->with('success', 'Room deleted successfully.');
    }
}
