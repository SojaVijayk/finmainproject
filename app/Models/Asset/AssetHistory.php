<?php

namespace App\Models\Asset;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class AssetHistory extends Model
{
  use HasFactory;

  protected $fillable = ['asset_id', 'action', 'description', 'performed_by'];

  public function asset()
  {
    return $this->belongsTo(AssetMaster::class, 'asset_id');
  }

  public function performer()
  {
    return $this->belongsTo(User::class, 'performed_by');
  }
}
