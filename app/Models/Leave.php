<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    use HasFactory;
    protected $fillable = ['leave_type','status'];

      public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'leave_type_id');
    }
}
