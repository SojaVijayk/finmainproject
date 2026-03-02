<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    use HasFactory;
    protected $table='AttendanceLogs';
    protected $fillable = ['AttendanceLogId','user_id','date','AttendanceDate','EmployeeId','InTime','InDeviceId','OutTime','OutDeviceId','Duration','LateBy','EarlyBy','IsOnLeave','LeaveType','LeaveDuration','WeeklyOff','Holiday','LeaveRemarks','PunchRecords','ShiftId','Present','Absent','Status','StatusCode','P1Status','P2Status','P3Status','IsonSpecialOff','SpecialOffType','SpecialOffRemark','SpecialOffDuration',
  'OverTime','OverTimeE','MissedOutPunch','MissedInPunch','C1','C2','C3','C4','C5','C6','C7','Remarks','LeaveTypeId','LossOfHours'];
}