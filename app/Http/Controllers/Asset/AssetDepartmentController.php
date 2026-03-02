<?php

namespace App\Http\Controllers\Asset;

use App\Http\Controllers\Controller;
use App\Models\Asset\AssetDepartment;
use App\Models\Employee;
use Illuminate\Http\Request;

class AssetDepartmentController extends Controller
{
  public function index()
  {
    $pageConfigs = ['myLayout' => 'horizontal'];
    $departments = AssetDepartment::with('custodian')
      ->latest()
      ->get();
    return view('assets.departments.index', compact('departments'), ['pageConfigs' => $pageConfigs]);
  }

  public function create()
  {
    $pageConfigs = ['myLayout' => 'horizontal'];
    $employees = Employee::where('status', 1)->get(); // Active employees for custodian
    return view('assets.departments.create', compact('employees'), ['pageConfigs' => $pageConfigs]);
  }

  public function store(Request $request)
  {
    $request->validate([
      'name' => 'required|string|max:255',
      'custodian_id' => 'required|exists:employees,id',
      'status' => 'boolean',
    ]);

    AssetDepartment::create($request->all());

    return redirect()
      ->route('asset.departments.index')
      ->with('success', 'Department created successfully.');
  }

  public function edit(AssetDepartment $department)
  {
    $pageConfigs = ['myLayout' => 'horizontal'];
    $employees = Employee::where('status', 1)->get();
    return view('assets.departments.edit', compact('department', 'employees'), ['pageConfigs' => $pageConfigs]);
  }

  public function update(Request $request, AssetDepartment $department)
  {
    $request->validate([
      'name' => 'required|string|max:255',
      'custodian_id' => 'required|exists:employees,id',
      'status' => 'boolean',
    ]);

    $department->update($request->all());

    return redirect()
      ->route('asset.departments.index')
      ->with('success', 'Department updated successfully.');
  }

  public function destroy(AssetDepartment $department)
  {
    $department->delete();
    return redirect()
      ->route('asset.departments.index')
      ->with('success', 'Department deleted successfully.');
  }
}
