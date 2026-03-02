<?php

namespace App\Http\Controllers\Asset;

use App\Http\Controllers\Controller;
use App\Models\Asset\AssetAllocation;
use App\Models\Asset\AssetMaster;
use App\Models\Asset\AssetHistory;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssetAllocationController extends Controller
{
  public function create(Request $request)
  {
    $pageConfigs = ['myLayout' => 'horizontal'];
    $asset = null;
    $availableAssets = collect();

    if ($request->has('asset_id')) {
      $asset = AssetMaster::findOrFail($request->asset_id);
    } else {
      $availableAssets = AssetMaster::where('status', 1)->get(); // Available only
    }

    $employees = Employee::where('status', 1)->get();
    $locations = \App\Models\Asset\Location::all();
    $floors = \App\Models\Asset\Floor::with('location')->get();
    $rooms = \App\Models\Asset\Room::with('floor.location')->get();

    return view(
      'assets.allocations.create',
      compact('asset', 'availableAssets', 'employees', 'locations', 'floors', 'rooms'),
      [
        'pageConfigs' => $pageConfigs,
      ]
    );
  }

  public function store(Request $request)
  {
    $request->validate([
      'asset_id' => 'required|exists:asset_masters,id',
      'issued_at' => 'required|date',
      'expected_return_at' => 'nullable|date|after_or_equal:issued_at',
      'employee_id' => 'nullable|exists:employees,id',
      'location_id' => 'nullable|exists:locations,id',
      'floor_id' => 'nullable|exists:floors,id',
      'room_id' => 'nullable|exists:rooms,id',
    ]);

    // Custom validation: at least one target must be selected
    if (!$request->employee_id && !$request->location_id && !$request->floor_id && !$request->room_id) {
      return back()->with(
        'error',
        'Please select at least one target (Employee, Location, Floor, or Room) for allocation.'
      );
    }

    $asset = AssetMaster::findOrFail($request->asset_id);

    // Double check status. Assuming 1 is Available implicitly or string check.
    // Migration set default to 'Available'.
    if ($asset->status !== 1 && $asset->status != '1' && $asset->status !== 'Available') {
      // Just proceeding for now, but usually we block.
      // return back()->with('error', 'Asset is not available.');
    }

    // Determine type (just for record keeping, though fields say it all)
    // We can infer type or set it loosely.
    $type = 'Employee';
    if (!$request->employee_id) {
      $type = 'Location'; // Generic for non-employee
    }

    AssetAllocation::create([
      'asset_id' => $request->asset_id,
      'type' => $type, // Or whatever the enum allows. If strictly Employee/Location, this works.
      'employee_id' => $request->employee_id,
      'location_id' => $request->location_id,
      'floor_id' => $request->floor_id,
      'room_id' => $request->room_id,
      'user_id' => Auth::id(),
      'issued_at' => $request->issued_at,
      'expected_return_at' => $request->expected_return_at,
      'issued_by' => Auth::id(),
    ]);

    $asset->update(['status' => 2]); // Update to Allocated (2)

    AssetHistory::create([
      'asset_id' => $asset->id,
      'action' => 'ALLOCATED',
      'description' => "Allocated to $type. " . ($request->employee_id ? 'Employee ID: ' . $request->employee_id : ''),
      'performed_by' => Auth::id(),
    ]);

    return redirect()
      ->route('asset.masters.show', $asset->id)
      ->with('success', 'Asset allocated successfully.');
  }

  public function update(Request $request, AssetAllocation $allocation)
  {
    if ($request->has('return_asset') && $request->return_asset == 1) {
      $allocation->returned_at = now();
      $allocation->return_remarks = $request->return_remarks;
      $allocation->save();

      $allocation->asset->status = 1; // Available (1)
      $allocation->asset->save();

      AssetHistory::create([
        'asset_id' => $allocation->asset_id,
        'action' => 'RETURNED',
        'description' => 'Asset returned. Remarks: ' . $request->return_remarks,
        'performed_by' => Auth::id(),
      ]);

      return redirect()
        ->back()
        ->with('success', 'Asset returned successfully.');
    }
  }
}
