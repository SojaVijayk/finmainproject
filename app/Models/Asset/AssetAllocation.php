<?php

namespace App\Models\Asset;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;
use App\Models\User;

class AssetAllocation extends Model
{
  use HasFactory;

  protected $fillable = [
    'asset_id',
    'type',
    'employee_id',
    'location_id',
    'floor_id',
    'room_id',
    'issued_by',
    'issued_at',
    'expected_return_at',
    'returned_at',
    'return_remarks',
  ];

  protected $casts = [
    'issued_at' => 'datetime',
    'expected_return_at' => 'date',
    'returned_at' => 'datetime',
  ];

  public function asset()
  {
    return $this->belongsTo(AssetMaster::class, 'asset_id');
  }

  public function employee()
  {
    return $this->belongsTo(Employee::class);
  }

  public function location()
  {
    return $this->belongsTo(Location::class);
  }

  public function floor()
  {
      return $this->belongsTo(Floor::class);
  }

  public function room()
  {
      return $this->belongsTo(Room::class);
  }

  public function issuer()
  {
    return $this->belongsTo(User::class, 'issued_by');
  }
}
