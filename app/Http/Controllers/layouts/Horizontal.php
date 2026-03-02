<?php

namespace App\Http\Controllers\layouts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveRequestDetails;
use App\Models\LeaveRequest;
use App\Models\Movement;
use App\Models\MissedPunch;
use Illuminate\Support\Facades\Auth;

use App\Helpers\Helpers;

class Horizontal extends Controller
{
  public function index()
  {

    $pageConfigs = ['myLayout' => 'horizontal'];
    $id= Auth::user()->id;

    $pending_leave = LeaveRequest::join("employees","employees.user_id","=","leave_requests.user_id")
    ->select('leave_requests.*')->where('employees.reporting_officer',$id)->where('leave_requests.status',0)->count();

    $pending_leave_cancel_ro = LeaveRequestDetails::join("employees","employees.user_id","=","leave_request_details.user_id")
    ->select('leave_request_details.*')->where('employees.reporting_officer',$id)->where('leave_request_details.cancel_status',4)->count();

     $pending_leave_cancel_hr = LeaveRequestDetails::where('cancel_status',3)->count();

    $pending_movement = Movement::join("employees","employees.user_id","=","movements.user_id")
    ->select('movements.*')->where('employees.reporting_officer',$id)->where('movements.status',0)->count();

     $report = Movement::join("employees","employees.user_id","=","movements.user_id")
    ->select('movements.*')->where('employees.reporting_officer',$id)->where('movements.report', '!=', null)->count();

    $pending_misspunch = MissedPunch::join("employees","employees.user_id","=","missed_punches.user_id")
      ->select('missed_punchess.*')->where('employees.reporting_officer',$id)->where('missed_punches.status',0)->count();

    // return view('content.dashboard.dashboards-analytics',['pageConfigs'=> $pageConfigs]);
    return view('content.dashboard.dashboards-user',['pageConfigs'=> $pageConfigs],compact('pending_leave','pending_movement','pending_misspunch','report','pending_leave_cancel_hr','pending_leave_cancel_ro'));
  }
}
