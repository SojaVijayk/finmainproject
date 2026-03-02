<?php

namespace App\Models\Asset;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetCategory extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'asset_department_id',
    'parent_id',
    'name',
    'prefix',
    'is_depreciable',
    'useful_life_years',
    'salvage_value',
    'specifications_schema',
  ];

  protected $casts = [
    'specifications_schema' => 'array',
    'is_depreciable' => 'boolean',
  ];

  public function department()
  {
    return $this->belongsTo(AssetDepartment::class, 'asset_department_id');
  }

  public function parent()
  {
      return $this->belongsTo(AssetCategory::class, 'parent_id');
  }

  public function children()
  {
      return $this->hasMany(AssetCategory::class, 'parent_id');
  }

  public function assets()
  {
    return $this->hasMany(AssetMaster::class);
  }
}
