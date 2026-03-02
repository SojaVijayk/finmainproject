<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveDutyAssignment extends Model
{
    use HasFactory;

  protected $fillable = [
    'leave_request_id',
    'user_id',
    'description',
];
    public function user()
{
    return $this->belongsTo(User::class);
}

public function leaveRequest()
{
    return $this->belongsTo(LeaveRequest::class);
}


}
