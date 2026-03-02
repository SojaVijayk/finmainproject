<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\LeaveRequest;

class LeaveRequestDetails extends Model
{
    use HasFactory;
    protected $fillable = ['leave_type_id','request_id','date','leave_day_type','leave_duration','leave_period_start','leave_period_end','status','remark','user_id','requested_at','cancel_status','cancel_requested_at','hr_cancel_action_taken_at','ro_cancel_action_taken_at'];

    // public function leaveRequest()
    // {
    //     return $this->belongsTo(LeaveRequest::class,'id');
    // }
    public function leaveRequest()
    {
        // request_id in leave_request_details → id in leave_requests
        return $this->belongsTo(LeaveRequest::class, 'request_id', 'id');
    }
}
