<?php

namespace App\Models\Asset;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['floor_id', 'room_number', 'name'];

    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }

    public function allocations()
    {
        return $this->hasMany(AssetAllocation::class);
    }
}
