<?php

namespace App\Models\Asset;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Employee;

class AssetDepartment extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = ['name', 'custodian_id', 'status'];

  // Relation to employee as custodian
  public function custodian()
  {
    return $this->belongsTo(\App\Models\Employee::class, 'custodian_id');
  }

  public function categories()
  {
    return $this->hasMany(AssetCategory::class);
  }

  public function custodians()
  {
    return $this->belongsToMany(Employee::class, 'asset_department_employee', 'asset_department_id', 'employee_id');
  }
}
