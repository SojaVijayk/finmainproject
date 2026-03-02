<?php

namespace App\Models\Asset;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetMaster extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'asset_category_id',
    'asset_vendor_id',
    'asset_brand_id',
    'asset_number',
    'name',
    'make', // Keeping for legacy/migration if needed, though replaced by brand_id
    'model',
    'serial_number',
    'purchase_date',
    'purchase_cost',
    'warranty_expiry_date',
    'condition',
    'status',
    'specifications',
    'qr_code_path',
  ];

  protected $casts = [
    'specifications' => 'array',
    'purchase_date' => 'date',
    'warranty_expiry_date' => 'date',
  ];

  public function category()
  {
    return $this->belongsTo(AssetCategory::class, 'asset_category_id');
  }

  public function vendor()
  {
    return $this->belongsTo(AssetVendor::class, 'asset_vendor_id');
  }

  public function brand()
  {
    return $this->belongsTo(AssetBrand::class, 'asset_brand_id');
  }

  public function allocations()
  {
    return $this->hasMany(AssetAllocation::class, 'asset_id');
  }
  public function latestAllocation()
  {
    return $this->hasOne(\App\Models\Asset\AssetAllocation::class, 'asset_id')->latestOfMany();
  }

  public function currentAllocation()
  {
    return $this->hasOne(AssetAllocation::class, 'asset_id')
      ->whereNull('returned_at')
      ->latestOfMany();
  }

  public function allocation()
  {
    return $this->currentAllocation();
  }

  public function getAllocationsAttribute()
  {
    return $this->hasMany(AssetAllocation::class, 'asset_id');
  }

  public function history()
  {
    return $this->hasMany(AssetHistory::class, 'asset_id')->latest();
  }

  /**
   * Calculate Depreciation Data (Straight Line Method)
   */
  public function getDepreciationAttribute()
  {
    $cost = $this->purchase_cost ?? 0;
    $salvage = $this->category->salvage_value ?? 0; // Assuming category provides default, or asset overrides it?
    // For now, let's use category's unless we add asset-specific salvage.
    $usefulLifeYears = $this->category->useful_life_years ?? 0;
    $purchaseDate = $this->purchase_date;

    if (!$this->category->is_depreciable || !$usefulLifeYears || !$purchaseDate) {
      return [
        'is_depreciable' => false,
        'cost' => $cost,
        'accumulated' => 0,
        'book_value' => $cost,
      ];
    }

    // Straight Line: (Cost - Salvage) / Life
    $annualDepreciation = ($cost - $salvage) / $usefulLifeYears;
    $monthlyDepreciation = $annualDepreciation / 12;

    $ageInMonths = max(0, now()->diffInMonths($purchaseDate));

    // Cap depreciation at (Cost - Salvage)
    $maxDepreciation = max(0, $cost - $salvage);
    $accumulated = min($maxDepreciation, $monthlyDepreciation * $ageInMonths);

    $bookValue = max($salvage, $cost - $accumulated);

    return [
      'is_depreciable' => true,
      'method' => 'Straight Line',
      'useful_life_years' => $usefulLifeYears,
      'age_months' => $ageInMonths,
      'cost' => $cost,
      'salvage_value' => $salvage,
      'annual_depreciation' => round($annualDepreciation, 2),
      'accumulated_depreciation' => round($accumulated, 2),
      'book_value' => round($bookValue, 2),
    ];
  }
}
