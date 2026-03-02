<?php

namespace App\Http\Controllers\Asset;

use App\Http\Controllers\Controller;
use App\Models\Asset\AssetMaster;
use App\Models\Asset\AssetAllocation;
use App\Models\Asset\AssetDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel; // Uncommented
use App\Exports\AssetRegisterExport; // Added
use Barryvdh\DomPDF\Facade\Pdf;

class AssetReportController extends Controller
{
  public function index()
  {
    $pageConfigs = ['myLayout' => 'horizontal'];
    return view('assets.reports.index', ['pageConfigs' => $pageConfigs]);
  }

  public function assetRegister(Request $request)
  {
    $pageConfigs = ['myLayout' => 'horizontal'];
    $mustSelectDepartment = false;
    $query = AssetMaster::with(['category.department', 'allocation.employee']);

    // Permission Logic
    $user = Auth::user();

    if (!($user->hasRole('admin') || $user->user_role === 1)) {
      if ($user->employee && $user->employee->custodianDepartments->isNotEmpty()) {
        $allowedDeptIds = $user->employee->custodianDepartments->pluck('id')->toArray();
        $selectedDeptId = $request->get('department_id');

        if ($selectedDeptId && in_array($selectedDeptId, $allowedDeptIds)) {
          $query->whereHas('category', function ($q) use ($selectedDeptId) {
            $q->where('asset_department_id', $selectedDeptId);
          });
        } else {
          if ($user->employee->custodianDepartments->count() > 1 && !$selectedDeptId) {
            $mustSelectDepartment = true;
            $query->whereRaw('1 = 0');
          } else {
            $query->whereHas('category', function ($q) use ($allowedDeptIds) {
              $q->whereIn('asset_department_id', $allowedDeptIds);
            });
          }
        }
      } else {
        $query->whereRaw('1 = 0');
      }
    }

    if ($request->has('status') && $request->status != '') {
      $query->where('status', $request->status);
    }

    if ($request->has('department_id') && $request->department_id != '') {
      $query->whereHas('category', function ($q) use ($request) {
        $q->where('asset_department_id', $request->department_id);
      });
    }

    $assets = $query->get();

    // For filter dropdown, show only authorized departments
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

    // If PDF Export
    if ($request->has('export') && $request->export == 'pdf') {
      $pdf = Pdf::loadView('assets.reports.pdf.register', compact('assets'));
      return $pdf->download('asset-register.pdf');
    }

    // Original Excel export logic (kept for now, but the instruction implies it might be removed or changed)
    if ($request->action == 'export_excel') {
      return Excel::download(new AssetRegisterExport($assets), 'asset-register.xlsx');
    }

    return view('assets.reports.asset-register', compact('assets', 'departments', 'mustSelectDepartment'), [
      'pageConfigs' => $pageConfigs,
    ]);
  }

  public function depreciation(Request $request)
  {
    $pageConfigs = ['myLayout' => 'horizontal'];
    // Filter only depreciable assets
    $assets = AssetMaster::whereHas('category', function ($q) {
      $q->where('is_depreciable', 1);
    })
      ->with('category')
      ->get();

    // The view will handle accessing $asset->depreciation attribute

    return view('assets.reports.depreciation', compact('assets'), ['pageConfigs' => $pageConfigs]);
  }
}
