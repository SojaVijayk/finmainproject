<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Asset\AssetDepartment;
use App\Models\Asset\AssetCategory;
use App\Models\Asset\AssetMaster;
use App\Models\Employee;
use App\Models\User;

class AssetModuleVerificationSeeder extends Seeder
{
  public function run()
  {
    // 1. Create Department
    $dept = AssetDepartment::firstOrCreate(['name' => 'IT Infrastructure'], ['status' => 1]);
    $this->command->info(
      'Department: ' . $dept->name . ' (' . ($dept->wasRecentlyCreated ? 'Created' : 'Exists') . ')'
    );

    // 2. Create Category with Specs
    $category = AssetCategory::firstOrCreate(
      ['name' => 'High-End Laptops', 'asset_department_id' => $dept->id],
      [
        'prefix' => 'HLAP',
        'is_depreciable' => 1,
        'useful_life_years' => 3,
        'specifications_schema' => [
          ['label' => 'Processor', 'type' => 'text', 'required' => '1'],
          ['label' => 'RAM', 'type' => 'number', 'required' => '1'],
          ['label' => 'SSD', 'type' => 'text', 'required' => '1'],
        ],
      ]
    );
    $this->command->info(
      'Category: ' . $category->name . ' (' . ($category->wasRecentlyCreated ? 'Created' : 'Exists') . ')'
    );

    // 3. Create Asset
    $assetNumber = 'HLAP-' . now()->year . '-0001';
    $asset = AssetMaster::where('asset_number', $assetNumber)->first();

    if (!$asset) {
      $asset = AssetMaster::create([
        'asset_category_id' => $category->id,
        'asset_number' => $assetNumber,
        'name' => 'MacBook Pro M3 Max',
        'status' => 1, // Available
        'condition' => 'New',
        'purchase_date' => now(),
        'purchase_cost' => 350000,
        'specifications' => [
          'Processor' => 'M3 Max',
          'RAM' => '64',
          'SSD' => '1TB',
        ],
      ]);
      $this->command->info('Created Asset: ' . $asset->asset_number);
    } else {
      $this->command->info('Asset ' . $assetNumber . ' already exists.');
    }

    // Ensure QR Code exists
    if (
      empty($asset->qr_code_path) ||
      !\Illuminate\Support\Facades\Storage::disk('public')->exists(str_replace('storage/', '', $asset->qr_code_path))
    ) {
      $qrPath = 'qrcodes/' . $asset->asset_number . '.svg';
      $qrUrl = route('asset.masters.show', $asset->id);

      if (!\Illuminate\Support\Facades\Storage::disk('public')->exists('qrcodes')) {
        \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory('qrcodes');
      }

      \Illuminate\Support\Facades\Storage::disk('public')->put(
        $qrPath,
        \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
          ->size(300)
          ->generate($qrUrl)
      );
      $asset->update(['qr_code_path' => $qrPath]);
      $this->command->info('Generated missing QR Code for: ' . $asset->asset_number);
    }

    // 4. Issue Asset
    // Find a user/employee
    $user = User::first();
    $employee = Employee::first();

    if ($user && $employee && $asset->status == 1) {
      // Only issue if available
      $asset->allocations()->create([
        'employee_id' => $employee->id,
        'issued_by' => $user->id,
        'issued_at' => now(),
        'expected_return_at' => now()->addYear(),
      ]);
      $asset->update(['status' => 2]); // Allocated
      $this->command->info('Issued Asset to: ' . $employee->name);
    } else {
      $this->command->warn('Skipping allocation: No user/employee found or asset already allocated.');
    }
  }
}
