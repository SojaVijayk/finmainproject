<?php

namespace App\Http\Controllers\Asset;

use App\Http\Controllers\Controller;
use App\Models\Asset\AssetCategory;
use App\Models\Asset\AssetDepartment;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

class AssetCategoryController extends Controller
{
  public function index()
  {
    $pageConfigs = ['myLayout' => 'horizontal'];
    $user = Auth::user();
    $mustSelectDepartment = false;

    $query = AssetCategory::with('department')->latest();

    if (!($user->hasRole('admin') || $user->user_role === 1)) {
      if ($user->employee && $user->employee->custodianDepartments->isNotEmpty()) {
        $allowedDeptIds = $user->employee->custodianDepartments->pluck('id')->toArray();

        // Filter by selected or all allowed
        $selectedDeptId = request('department_id');
        if ($selectedDeptId && in_array($selectedDeptId, $allowedDeptIds)) {
          $query->where('asset_department_id', $selectedDeptId);
        } else {
          // If multiple departments and none selected, user requested to "ask to choose"
          if ($user->employee->custodianDepartments->count() > 1 && !$selectedDeptId) {
            $mustSelectDepartment = true;
            $query->whereRaw('1 = 0');
          } else {
            $query->whereIn('asset_department_id', $allowedDeptIds);
          }
        }
      } else {
        $query->whereRaw('1 = 0');
      }
    } else {
      // Admin filter
      if (request()->filled('department_id')) {
        $query->where('asset_department_id', request('department_id'));
      }
    }

    $categories = $query->get();

    // For the switcher in view
    $myDepartments = collect();
    if ($user->hasRole('admin') || $user->user_role === 1) {
      $myDepartments = AssetDepartment::where('status', 1)->get();
    } elseif ($user->employee) {
      $myDepartments = $user->employee->custodianDepartments;
    }

    return view('assets.categories.index', compact('categories', 'myDepartments', 'mustSelectDepartment'), [
      'pageConfigs' => $pageConfigs,
    ]);
  }

  public function create()
  {
    $pageConfigs = ['myLayout' => 'horizontal'];
    $user = Auth::user();

    $deptQuery = AssetDepartment::where('status', 1);

    if (!($user->hasRole('admin') || $user->user_role === 1)) {
      if ($user->employee && $user->employee->custodianDepartments->isNotEmpty()) {
        $allowedDeptIds = $user->employee->custodianDepartments->pluck('id')->toArray();
        $deptQuery->whereIn('id', $allowedDeptIds);
      } else {
        $deptQuery->whereRaw('1 = 0');
      }
    }

    $departments = $deptQuery->get();
    $parents = AssetCategory::all();

    return view('assets.categories.create', compact('departments', 'parents'), ['pageConfigs' => $pageConfigs]);
  }

  public function store(Request $request)
  {
    $request->validate([
      'asset_department_id' => 'required|exists:asset_departments,id',
      'parent_id' => 'nullable|exists:asset_categories,id',
      'name' => 'required|string|max:255',
      'prefix' => 'required|string|max:10|unique:asset_categories,prefix',
      'is_depreciable' => 'required|boolean',
      'useful_life_years' => 'required_if:is_depreciable,1|nullable|integer|min:0',
      'salvage_value' => 'nullable|numeric|min:0',
      'specifications_schema' => 'nullable|array',
    ]);

    AssetCategory::create([
      'asset_department_id' => $request->asset_department_id,
      'parent_id' => $request->parent_id,
      'name' => $request->name,
      'prefix' => strtoupper($request->prefix),
      'is_depreciable' => $request->is_depreciable,
      'useful_life_years' => $request->useful_life_years,
      'salvage_value' => $request->salvage_value ?? 0,
      'specifications_schema' => $request->specifications_schema,
    ]);

    return redirect()
      ->route('asset.categories.index')
      ->with('success', 'Category created successfully.');
  }

  public function edit($id)
  {
    $pageConfigs = ['myLayout' => 'horizontal'];
    $category = AssetCategory::findOrFail($id);
    $departments = AssetDepartment::where('status', 1)->get();
    $parents = AssetCategory::where('id', '!=', $id)->get(); // Prevent self-parenting
    return view('assets.categories.edit', compact('category', 'departments', 'parents'), [
      'pageConfigs' => $pageConfigs,
    ]);
  }

  public function update(Request $request, $id)
  {
    $category = AssetCategory::findOrFail($id);

    $request->validate([
      'asset_department_id' => 'required|exists:asset_departments,id',
      'parent_id' => 'nullable|exists:asset_categories,id',
      'name' => 'required|string|max:255',
      'prefix' => 'required|string|max:10|unique:asset_categories,prefix,' . $category->id,
      'is_depreciable' => 'required|boolean',
      'useful_life_years' => 'required_if:is_depreciable,1|nullable|integer|min:0',
      'salvage_value' => 'nullable|numeric|min:0',
      'specifications_schema' => 'nullable|array',
    ]);

    $category->update([
      'asset_department_id' => $request->asset_department_id,
      'parent_id' => $request->parent_id,
      'name' => $request->name,
      'prefix' => strtoupper($request->prefix),
      'is_depreciable' => $request->is_depreciable,
      'useful_life_years' => $request->useful_life_years,
      'salvage_value' => $request->salvage_value ?? 0,
      'specifications_schema' => $request->specifications_schema,
    ]);

    return redirect()
      ->route('asset.categories.index')
      ->with('success', 'Category updated successfully.');
  }

  public function destroy($id)
  {
    AssetCategory::findOrFail($id)->delete();
    return redirect()
      ->route('asset.categories.index')
      ->with('success', 'Category deleted successfully.');
  }
}
