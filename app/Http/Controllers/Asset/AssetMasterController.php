<?php

namespace App\Http\Controllers\Asset;

use App\Http\Controllers\Controller;
use App\Models\Asset\AssetCategory;
use App\Models\Asset\AssetMaster;
use App\Models\Asset\AssetVendor;
use App\Models\Asset\AssetBrand;
use App\Models\Asset\AssetHistory;
use App\Models\Employee;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class AssetMasterController extends Controller
{
  public function index(Request $request)
  {
    $pageConfigs = ['myLayout' => 'horizontal'];
    $user = Auth::user();
    $query = AssetMaster::with([
      'category',
      'category.department',
      'allocation.employee',
      'latestAllocation.employee',
    ])->latest();

    // Admin can see all
    if ($user->hasRole('admin') || $user->user_role === 1) {
      if ($request->filled('department_id')) {
        $query->whereHas('category', function ($q) use ($request) {
          $q->where('asset_department_id', $request->department_id);
        });
      }
    } elseif ($user && $user->employee) {
      $custodianDepartments = $user->employee
        ->custodianDepartments()
        ->with('department')
        ->get();

      if ($custodianDepartments->isNotEmpty()) {
        $allowedDeptIds = $custodianDepartments->pluck('id')->toArray();
        $selectedDeptId = $request->get('department_id');

        if ($selectedDeptId && in_array($selectedDeptId, $allowedDeptIds)) {
          $query->whereHas('category', function ($q) use ($selectedDeptId) {
            $q->where('asset_department_id', $selectedDeptId);
          });
        } else {
          // If multiple departments and none selected, user requested to "ask to choose"
          // We can either filter by all or force selection.
          // Let's force selection if count > 1 and no selection made.
          if ($custodianDepartments->count() > 1 && !$selectedDeptId) {
            $mustSelectDepartment = true;
            $query->whereRaw('1 = 0'); // Show nothing until selected
          } else {
            $query->whereHas('category', function ($q) use ($allowedDeptIds) {
              $q->whereIn('asset_department_id', $allowedDeptIds);
            });
          }
        }
      } else {
        // If not custodian and not admin, but user has "Asset Management" roll/permission
        // They might still be able to see assets they are allocated, but usually index is for management.
        // Let's allow them to see assets they are allocated if they aren't a custodian.
        $query->whereHas('allocation', function ($q) use ($user) {
          $q->where('employee_id', $user->employee->id)->whereNull('returned_at');
        });
      }
    }

    $mustSelectDepartment = false;
    $assets = $query->get();

    // For the switcher in view
    $myDepartments = collect();
    if ($user->hasRole('admin') || $user->user_role === 'admin') {
      $myDepartments = \App\Models\Asset\AssetDepartment::where('status', 1)->get();
    } elseif ($user->employee) {
      $myDepartments = $user->employee->custodianDepartments;
    }

    return view('assets.masters.index', compact('assets', 'myDepartments', 'mustSelectDepartment'), [
      'pageConfigs' => $pageConfigs,
    ]);
  }

  public function show(AssetMaster $master)
  {
    // Route model binding param name matches resource param 'master'
    $pageConfigs = ['myLayout' => 'horizontal'];
    $asset = $master->load(['category.department', 'vendor', 'brand', 'allocations.employee', 'history.performer']);

    return view('assets.masters.show', compact('asset'), ['pageConfigs' => $pageConfigs]);
  }

  public function create()
  {
    $pageConfigs = ['myLayout' => 'horizontal'];
    $user = Auth::user();

    $categoryQuery = AssetCategory::whereHas('department', function ($q) {
      $q->where('status', 1);
    });

    // Admin sees all, Custodians see only their department's categories
    if (!($user->hasRole('admin') || $user->user_role === 1)) {
      if ($user->employee && $user->employee->custodianDepartments->isNotEmpty()) {
        $allowedDeptIds = $user->employee->custodianDepartments->pluck('id')->toArray();
        $categoryQuery->whereIn('asset_department_id', $allowedDeptIds);
      } else {
        // If not admin and not custodian, they shouldn't be here, but let's show nothing
        $categoryQuery->whereRaw('1 = 0');
      }
    }

    $categories = $categoryQuery->with(['department', 'parent'])->get();

    $vendors = AssetVendor::where('status', 1)->get();
    $brands = AssetBrand::where('status', 1)->get();

    return view('assets.masters.create', compact('categories', 'vendors', 'brands'), ['pageConfigs' => $pageConfigs]);
  }

  public function store(Request $request)
  {
    $request->validate([
      'asset_category_id' => 'required|exists:asset_categories,id',
      'asset_vendor_id' => 'nullable|exists:asset_vendors,id',
      'asset_brand_id' => 'nullable|exists:asset_brands,id',
      'name' => 'required|string|max:255',
      'status' => 'required|in:1,2,3,4,5',
      'condition' => 'required|string',
      'purchase_date' => 'nullable|date',
      'purchase_cost' => 'nullable|numeric|min:0',
      'warranty_expiry_date' => 'nullable|date',
      'specifications' => 'nullable|array',
    ]);

    $category = AssetCategory::findOrFail($request->asset_category_id);
    $year = now()->year;
    $prefix = $category->prefix ?: 'AST';

    // Generate Asset Number: PREFIX-YEAR-SEQUENCE
    $lastAsset = AssetMaster::where('asset_number', 'LIKE', "{$prefix}-{$year}-%")
      ->orderBy('asset_number', 'desc')
      ->first();

    $sequence = 1;
    if ($lastAsset) {
      $parts = explode('-', $lastAsset->asset_number);
      $sequence = (int) end($parts) + 1;
    }
    $assetNumber = "{$prefix}-{$year}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);

    $asset = AssetMaster::create([
      'asset_category_id' => $request->asset_category_id,
      'asset_vendor_id' => $request->asset_vendor_id,
      'asset_brand_id' => $request->asset_brand_id,
      'asset_number' => $assetNumber,
      'name' => $request->name,
      'model' => $request->model,
      'serial_number' => $request->serial_number,
      'make' => $request->make,
      'status' => $request->status,
      'condition' => $request->condition,
      'purchase_date' => $request->purchase_date,
      'purchase_cost' => $request->purchase_cost,
      'warranty_expiry_date' => $request->warranty_expiry_date,
      'specifications' => $request->specifications,
    ]);

    // Generate QR Code
    $qrPath = 'qrcodes/' . $asset->asset_number . '.svg';
    $qrUrl = route('asset.masters.show', $asset->id);

    if (!Storage::disk('public')->exists('qrcodes')) {
      Storage::disk('public')->makeDirectory('qrcodes');
    }

    Storage::disk('public')->put(
      $qrPath,
      QrCode::format('svg')
        ->size(300)
        ->generate($qrUrl)
    );

    $asset->update(['qr_code_path' => 'storage/' . $qrPath]);

    AssetHistory::create([
      'asset_id' => $asset->id,
      'action' => 'CREATED',
      'description' => 'Asset created manually.',
      'performed_by' => Auth::id(),
    ]);

    return redirect()
      ->route('asset.masters.index')
      ->with('success', "Asset created successfully. Asset Number: {$assetNumber}");
  }

  public function edit(AssetMaster $master)
  {
    $pageConfigs = ['myLayout' => 'horizontal', 'hasCustomizer' => false];
    $categories = AssetCategory::whereHas('department', function ($q) {
      $q->where('status', 1);
    })
      ->with(['department', 'parent'])
      ->get();

    $vendors = AssetVendor::where('status', 1)->get();
    $brands = AssetBrand::where('status', 1)->get();

    return view('assets.masters.edit', compact('master', 'categories', 'vendors', 'brands'), [
      'pageConfigs' => $pageConfigs,
    ]);
  }

  public function update(Request $request, AssetMaster $master)
  {
    $request->validate([
      'asset_vendor_id' => 'nullable|exists:asset_vendors,id',
      'asset_brand_id' => 'nullable|exists:asset_brands,id',
      'name' => 'required|string|max:255',
      'status' => 'required|in:1,2,3,4,5', // Added 5 for Scrap
      'specifications' => 'nullable|array',
    ]);

    $master->update($request->except(['asset_number', 'asset_category_id']));

    // Update specifications if provided
    if ($request->has('specifications')) {
      $master->specifications = $request->specifications;
      $master->save();
    }

    return redirect()
      ->route('asset.masters.index')
      ->with('success', 'Asset updated successfully.');
  }

  public function destroy(AssetMaster $master)
  {
    $master->delete();
    return redirect()
      ->route('asset.masters.index')
      ->with('success', 'Asset deleted successfully.');
  }

  public function history(AssetMaster $master)
  {
    $pageConfigs = ['myLayout' => 'horizontal'];
    $histories = $master
      ->history()
      ->with('performer')
      ->get();
    return view(
      'assets.masters.history',
      [
        'asset' => $master,
        'histories' => $histories,
      ],
      ['pageConfigs' => $pageConfigs]
    );
  }

  public function changeStatus(Request $request, AssetMaster $master)
  {
    $request->validate([
      'status' => 'required|in:1,2,3,4,5',
      'remarks' => 'nullable|string',
    ]);

    $oldStatus = $master->status;
    $master->update(['status' => $request->status]);

    AssetHistory::create([
      'asset_id' => $master->id,
      'action' => 'STATUS_CHANGE',
      'description' =>
        'Status changed from ' . $oldStatus . ' to ' . $request->status . '. Remarks: ' . $request->remarks,
      'performed_by' => Auth::id(),
    ]);

    return redirect()
      ->back()
      ->with('success', 'Asset status updated successfully.');
  }

  public function getCategorySchema($id)
  {
    $category = AssetCategory::findOrFail($id);
    return response()->json($category->specifications_schema ?? []);
  }

  public function myAssets()
  {
    $pageConfigs = ['myLayout' => 'horizontal'];
    $user = Auth::user();
    $employee = $user->employee;

    if (!$employee) {
      $assets = collect();
    } else {
      $assets = $employee
        ->assetAllocations()
        ->whereNull('returned_at')
        ->with('asset')
        ->get()
        ->pluck('asset');
    }

    return view('assets.masters.my-assets', compact('assets'), ['pageConfigs' => $pageConfigs]);
  }
}
