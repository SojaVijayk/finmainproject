<?php

namespace App\Http\Controllers\Asset;

use App\Http\Controllers\Controller;
use App\Models\Asset\AssetBrand;
use Illuminate\Http\Request;

class AssetBrandController extends Controller
{
  public function index()
  {
    $pageConfigs = ['myLayout' => 'horizontal'];
    $brands = AssetBrand::latest()->get();
    return view('assets.masters.brands.index', compact('brands'), ['pageConfigs' => $pageConfigs]);
  }

  public function create()
  {
    $pageConfigs = ['myLayout' => 'horizontal'];
    return view('assets.masters.brands.create', ['pageConfigs' => $pageConfigs]);
  }

  public function store(Request $request)
  {
    $request->validate([
      'name' => 'required|string|max:255',
      'status' => 'boolean',
    ]);

    AssetBrand::create($request->all());

    return redirect()
      ->route('asset.brands.index')
      ->with('success', 'Brand created successfully.');
  }

  public function edit(AssetBrand $brand)
  {
    $pageConfigs = ['myLayout' => 'horizontal'];
    return view('assets.masters.brands.edit', compact('brand'), ['pageConfigs' => $pageConfigs]);
  }

  public function update(Request $request, AssetBrand $brand)
  {
    $request->validate([
      'name' => 'required|string|max:255',
      'status' => 'boolean',
    ]);

    $brand->update($request->all());

    return redirect()
      ->route('asset.brands.index')
      ->with('success', 'Brand updated successfully.');
  }

  public function destroy(AssetBrand $brand)
  {
    $brand->delete();
    return redirect()
      ->route('asset.brands.index')
      ->with('success', 'Brand deleted successfully.');
  }
}
