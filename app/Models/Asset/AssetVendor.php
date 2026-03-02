<?php

namespace App\Models\Asset;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetVendor extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = ['name', 'contact_person', 'email', 'phone', 'address', 'status'];
}
