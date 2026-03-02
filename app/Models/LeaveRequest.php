<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\LeaveRequestDetails;
use App\Models\LeaveDutyAssignment;
use App\Models\Leave;

class LeaveRequest extends Model
{
    use HasFactory;
    protected $fillable = ['leave_type_id','from','to','duration','date_list','description','status','user_id','requested_at'];

    // public function leaveRequestDetails()
    // {
    //     return $this->hasMany(LeaveRequestDetails::class,'request_id');
    // }
       public function leaveRequestDetails()
    {
        // id in leave_requests → request_id in leave_request_details
        return $this->hasMany(LeaveRequestDetails::class, 'request_id', 'id');
    }

    public function dutyAssignments()
{
    return $this->hasMany(LeaveDutyAssignment::class);
}

public function leaveType()
    {
        return $this->belongsTo(Leave::class, 'leave_type_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}
