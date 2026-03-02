<?php

namespace App\Http\Controllers\PMS;

use App\Http\Controllers\Controller;

use App\Models\PMS\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index()
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        $vendors = Vendor::paginate(20);
        return view('pms.expenses.vendors.index', compact('vendors'),['pageConfigs'=> $pageConfigs]);
    }

    public function create()
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        return view('pms.expenses.vendors.create',['pageConfigs'=> $pageConfigs]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:vendors',
            'contact_details' => 'nullable|string'
        ]);

        Vendor::create($validated);

        return redirect()->route('pms.vendors.index')
            ->with('success', 'Vendor created successfully.');
    }

    public function edit(Vendor $vendor)
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        return view('pms.expenses.vendors.edit', compact('vendor'),['pageConfigs'=> $pageConfigs]);
    }

    public function update(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:vendors,name,' . $vendor->id,
            'contact_details' => 'nullable|string'
        ]);

        $vendor->update($validated);

        return redirect()->route('pms.vendors.index')
            ->with('success', 'Vendor updated successfully.');
    }

    public function destroy(Vendor $vendor)
    {
        if ($vendor->expenses()->count() > 0) {
            return redirect()->route('vendors.index')
                ->with('error', 'Cannot delete vendor with associated expenses.');
        }

        $vendor->delete();

        return redirect()->route('pms.vendors.index')
            ->with('success', 'Vendor deleted successfully.');
    }
}