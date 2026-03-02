<?php

namespace App\Http\Controllers\Asset;

use App\Http\Controllers\Controller;
use App\Models\Asset\AssetVendor;
use Illuminate\Http\Request;

class AssetVendorController extends Controller
{
  public function index()
  {
    $pageConfigs = ['myLayout' => 'horizontal'];
    $vendors = AssetVendor::latest()->get();
    return view('assets.masters.vendors.index', compact('vendors'), ['pageConfigs' => $pageConfigs]);
  }

  public function create()
  {
    $pageConfigs = ['myLayout' => 'horizontal'];
    return view('assets.masters.vendors.create', ['pageConfigs' => $pageConfigs]);
  }

  public function store(Request $request)
  {
    $request->validate([
      'name' => 'required|string|max:255',
      'email' => 'nullable|email|max:255',
      'phone' => 'nullable|string|max:20',
      'status' => 'boolean',
    ]);

    AssetVendor::create($request->all());

    return redirect()
      ->route('asset.vendors.index')
      ->with('success', 'Vendor created successfully.');
  }

  public function edit(AssetVendor $vendor)
  {
    $pageConfigs = ['myLayout' => 'horizontal'];
    return view('assets.masters.vendors.edit', compact('vendor'), ['pageConfigs' => $pageConfigs]);
  }

  public function update(Request $request, AssetVendor $vendor)
  {
    $request->validate([
      'name' => 'required|string|max:255',
      'email' => 'nullable|email|max:255',
      'phone' => 'nullable|string|max:20',
      'status' => 'boolean',
    ]);

    $vendor->update($request->all());

    return redirect()
      ->route('asset.vendors.index')
      ->with('success', 'Vendor updated successfully.');
  }

  public function destroy(AssetVendor $vendor)
  {
    $vendor->delete();
    return redirect()
      ->route('asset.vendors.index')
      ->with('success', 'Vendor deleted successfully.');
  }
}
