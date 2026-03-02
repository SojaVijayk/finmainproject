<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
  use HasFactory;
  protected $fillable = [
    'user_id',
    'prefix',
    'name',
    'gender',
    'employment_type',
    'designation',
    'mobile',
    'empId',
    'doj',
    'email',
    'address',
    'country',
    'state',
    'district',
    '	pincode',
    'whatsapp',
    'dob',
    'pan',
    'profile_pic',
    'languages',
    'bank_id',
    'contract_end_date',
    'contract_start_date',
    'reporting_officer',
  ];
  public function user()
  {
    return $this->belongsTo('App\User');
  }

  public function lead_projects()
  {
    return $this->belongsToMany(Project::class, 'projects_leads', 'employee_id', 'project_id');
  }
  public function member_projects()
  {
    return $this->belongsToMany(Project::class, 'projects_members', 'employee_id', 'project_id');
  }

  public function custodianDepartments()
  {
    return $this->belongsToMany(
      \App\Models\Asset\AssetDepartment::class,
      'asset_department_employee',
      'employee_id',
      'asset_department_id'
    );
  }

  public function assetAllocations()
  {
    return $this->hasMany(\App\Models\Asset\AssetAllocation::class);
  }
}
