<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\LeaveRequestDetails;
use App\Models\MissedPunch;
use App\Models\Movement;
use App\Models\Holiday;
use App\Models\User;
use App\Models\EmploymentType;
use App\Models\Employee;
use App\Imports\AttendanceImport;
use App\Exports\AttendanceExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\MasterFunctionController;
use App\Models\Leave;
use App\Models\LeaveRequest;
use App\Models\LeaveAssign;
// use Barryvdh\DomPDF\Facade as PDF;
// use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;
use PDF;
use Illuminate\Support\Facades\DB;


class AttendanceController extends Controller
{
    //
    public function index()
    {
      $pageConfigs = ['myLayout' => 'horizontal'];
      $from = date('Y-m-01');

      // Get today's date
      // $to = date('Y-m-d');
      $id= Auth::user()->id;

      $data = AttendanceLog::select('date')->orderBy('date', 'desc')->first();
      $to= $data->date;

      $DurationMinutes = AttendanceLog::whereBetween('AttendanceLogs.date', [$from, $to])->where('AttendanceLogs.user_id',$id)->sum('Duration');
      // $LateBy = AttendanceLog::whereBetween('AttendanceLogs.date', [$from, $to])->where('AttendanceLogs.user_id',$id)->sum('LateBy');
      // $EarlyBy = AttendanceLog::whereBetween('AttendanceLogs.date', [$from, $to])->where('AttendanceLogs.user_id',$id)->sum('EarlyBy');
      $grace=240;

      $durationHours = floor($DurationMinutes / 60) . " hours " . ($DurationMinutes % 60) . " minutes";
      // $LateByHours = floor($LateBy / 60) . " hours " . ($LateBy % 60) . " minutes";
      // $EarlyByHours = floor($EarlyBy / 60) . " hours " . ($EarlyBy % 60) . " minutes";

      function generateDatesWithWeekday($start_date, $end_date) {
        $dates = array();
        $current_date = strtotime($start_date);
        $end_date = strtotime($end_date);

        while ($current_date <= $end_date) {
            $dates[] = array(
                'date' => date('Y-m-d', $current_date),
                'weekday' => date('l', $current_date)
            );
            $current_date = strtotime('+1 day', $current_date);
        }

        return $dates;
    }

    // Example usage:
    $start_date = $from;
    $end_date = $to;
    $date_range_array = generateDatesWithWeekday($start_date, $end_date);
    // print_r($date_range);
    $attendance_data = AttendanceLog::whereBetween('AttendanceLogs.date', [$from, $to])->where('AttendanceLogs.user_id',$id)->orderBy('date')->get();

    $holidays= Holiday::whereBetween('holidays.date', [$from, $to])->get();
    $leaves = LeaveRequestDetails::select('leave_request_details.leave_day_type','leave_request_details.user_id as leave_user_id','leave_request_details.date as leave_date',
    'leave_request_details.status as leave_status','emp.name as leave_action_by_name','leaves.leave_type',)
   ->leftjoin("employees as emp","emp.user_id","=","leave_request_details.action_by")
   ->leftjoin("leaves","leaves.id","=","leave_request_details.leave_type_id")
  ->where('leave_request_details.user_id',$id)
  ->whereBetween('leave_request_details.date', [$from, $to])
  ->orderBy('leave_request_details.user_id','DESC')->get();

  $movements = Movement::select('movements.user_id as mov_user_id','movements.title','movements.type','movements.start_date','movements.start_time','movements.end_date','movements.end_time','movements.status as movement_status',
  'emp_mov.name as movement_action_by_name','movements.remark','movements.location','movements.description')
->leftjoin("employees as emp_mov","emp_mov.user_id","=","movements.action_by")
->where('movements.user_id',$id)
->where(function ($query) use ($from, $to) {
  $query->where('start_date', '<=', $to) // Database start is before or on the selected end
        ->where('end_date', '>=', $from); // Database end is after or on the selected start
})
  // ->whereBetween('movements.start_date', [$from, $to])
  // ->whereBetween('movements.end_date', [$from, $to])
  ->orderBy('movements.user_id','DESC')->get();

  $missedpunches = MissedPunch::select('missed_punches.user_id as miss_user_id','missed_punches.type as miss_type','missed_punches.date as miss_date','missed_punches.checkinTime','missed_punches.checkoutTime','missed_punches.status as miss_status',
  'emp_miss.name as misspunch_action_by_name','missed_punches.description')
->leftjoin("employees as emp_miss","emp_miss.user_id","=","missed_punches.action_by")
->where('missed_punches.user_id',$id)
  ->whereBetween('missed_punches.date', [$from, $to])
  ->orderBy('missed_punches.user_id','DESC')->get();


      return view('content.attendance.attendance',['pageConfigs'=> $pageConfigs],compact('durationHours','from','to','grace','DurationMinutes','attendance_data','date_range_array',
    'holidays','leaves','movements','missedpunches'));
    }


    public function attendanceMonitor($id)
    {
      $pageConfigs = ['myLayout' => 'horizontal'];
      $from = date('Y-m-01');
      $leaveError=0;
      $message='';

      // Get today's date
      // $to = date('Y-m-d');
      // $id= $request->input('user_id');

      $data = AttendanceLog::select('date')->orderBy('date', 'desc')->first();
      $to= $data->date;

      $DurationMinutes = AttendanceLog::whereBetween('AttendanceLogs.date', [$from, $to])->where('AttendanceLogs.user_id',$id)->sum('Duration');
      // $LateBy = AttendanceLog::whereBetween('AttendanceLogs.date', [$from, $to])->where('AttendanceLogs.user_id',$id)->sum('LateBy');
      // $EarlyBy = AttendanceLog::whereBetween('AttendanceLogs.date', [$from, $to])->where('AttendanceLogs.user_id',$id)->sum('EarlyBy');
      $grace=240;

      $durationHours = floor($DurationMinutes / 60) . " hours " . ($DurationMinutes % 60) . " minutes";
      // $LateByHours = floor($LateBy / 60) . " hours " . ($LateBy % 60) . " minutes";
      // $EarlyByHours = floor($EarlyBy / 60) . " hours " . ($EarlyBy % 60) . " minutes";

      function generateDatesWithWeekdays($start_date, $end_date) {
        $dates = array();
        $current_date = strtotime($start_date);
        $end_date = strtotime($end_date);

        while ($current_date <= $end_date) {
            $dates[] = array(
                'date' => date('Y-m-d', $current_date),
                'weekday' => date('l', $current_date)
            );
            $current_date = strtotime('+1 day', $current_date);
        }

        return $dates;
    }



    // Example usage:
    $start_date = $from;
    $end_date = $to;
    $date_range_array = generateDatesWithWeekdays($start_date, $end_date);
    // print_r($date_range);
    $attendance_data = AttendanceLog::whereBetween('AttendanceLogs.date', [$from, $to])->where('AttendanceLogs.user_id',$id)->orderBy('date')->get();

    $holidays= Holiday::whereBetween('holidays.date', [$from, $to])->get();
    $leaves = LeaveRequestDetails::select('leave_request_details.leave_day_type','leave_request_details.user_id as leave_user_id','leave_request_details.date as leave_date',
    'leave_request_details.status as leave_status','emp.name as leave_action_by_name','leaves.leave_type',)
   ->leftjoin("employees as emp","emp.user_id","=","leave_request_details.action_by")
   ->leftjoin("leaves","leaves.id","=","leave_request_details.leave_type_id")
  ->where('leave_request_details.user_id',$id)
  ->whereBetween('leave_request_details.date', [$from, $to])
  ->orderBy('leave_request_details.user_id','DESC')->get();

  $movements = Movement::select('movements.user_id as mov_user_id','movements.title','movements.type','movements.start_date','movements.start_time','movements.end_date','movements.end_time','movements.status as movement_status',
  'emp_mov.name as movement_action_by_name','movements.remark','movements.location','movements.description')
->leftjoin("employees as emp_mov","emp_mov.user_id","=","movements.action_by")
->where('movements.user_id',$id)
  ->whereBetween('movements.start_date', [$from, $to])
  ->whereBetween('movements.end_date', [$from, $to])
  ->orderBy('movements.user_id','DESC')->get();

  $missedpunches = MissedPunch::select('missed_punches.user_id as miss_user_id','missed_punches.type as miss_type','missed_punches.date as miss_date','missed_punches.checkinTime','missed_punches.checkoutTime','missed_punches.status as miss_status',
  'emp_miss.name as misspunch_action_by_name','missed_punches.description')
->leftjoin("employees as emp_miss","emp_miss.user_id","=","missed_punches.action_by")
->where('missed_punches.user_id',$id)
  ->whereBetween('missed_punches.date', [$from, $to])
  ->orderBy('missed_punches.user_id','DESC')->get();





    $employee_details=Employee::where('user_id',$id)->first();

    $leave_types = Leave::orderBy('id','DESC')->get();

    if($employee_details->contract_start_date == NULL && $employee_details->employment_type !=1  && $employee_details->employment_type !=3){
      $leaveError=1;
      // return Redirect::back()->withErrors(['insufficient' => 'Contract Period Not Updated']);
      $message = 'Contract Period Not Updated. Please contact our Human Resources (HR) department at hr@cmd.kereal.gov.in to update your contract period. Once this is done, you can proceed with your request as usual.';
      // return view('content.pages.pages-custom-error',compact('message'),['pageConfigs'=> $pageConfigs]);
      return view('content.attendance.employee_monitor',['pageConfigs'=> $pageConfigs],compact('durationHours','from','to','grace','DurationMinutes','attendance_data','date_range_array',
      'holidays','leaves','movements','missedpunches','leaveError','message'));

    }


     $leaves_total_credit = LeaveAssign::select('leave_assigns.id','leave_assigns.leave_type as leave_type_id','leaves.leave_type','total_credit','employment_types.employment_type','leave_assigns.status','leave_assigns.created_at')
     ->leftjoin("leaves","leaves.id","=","leave_assigns.leave_type")
     ->join("employment_types","employment_types.id","=","leave_assigns.employment_type")
     ->where('leave_assigns.employment_type',$employee_details->employment_type)
     ->orderBy('leave_assigns.leave_type','ASC')->get();
     $leaves_total_credit_details=[];
     foreach( $leaves_total_credit as $leave_detail){


    // $date_result = (new MasterFunctionController)->GenerateLeavePeriod($employee_details->employment_type,$employee_details->doj);

    $date_result = (new MasterFunctionController)->GetLeavePeriod($employee_details->user_id,$leave_detail->leave_type_id);
// print_r($date_result);exit;

    $date_start = $date_result['start_date'];
    $date_end = $date_result['end_date'];
    // echo $employee_details->employment_type;
    // print_r($date_result);exit();

       $availed_leave = LeaveRequestDetails::where('status',1)->where('user_id',$employee_details->user_id)->where('leave_type_id',$leave_detail->leave_type_id)->whereBetween('date', [$date_start, $date_end])->sum('leave_duration');
       $pending_leave = LeaveRequestDetails::where('status',0)->where('user_id',$employee_details->user_id)->where('leave_type_id',$leave_detail->leave_type_id)->whereBetween('date', [$date_start, $date_end])->sum('leave_duration');
     // Leave calculation Regular
       if(($leave_detail->leave_type_id == 2 || $leave_detail->leave_type_id == 3 || $leave_detail->leave_type_id == 1) && $employee_details->employment_type == 1){
        $user_leave = DB::table('opening_leave_credits')->where('user_id',$employee_details->user_id)->where('leave_type_id',$leave_detail->leave_type_id)->where('leave_period_start',$date_start)->where('leave_period_end',$date_end)->first();
       if( $user_leave){
        $opening = $user_leave->credit;
       }
       else{
        $opening = $leave_detail->total_credit;
       }

        //sick Leave
        if($leave_detail->leave_type_id == 2){
          $balance_credit=  ($opening- ($availed_leave + $pending_leave));
          if($balance_credit > 120){
            $balance_credit = 120;
          }
        }
        //Privilaged
        if($leave_detail->leave_type_id == 3){
          $balance_credit=  ($opening - ($availed_leave + $pending_leave));
          if($balance_credit > 300){
            $balance_credit = 300;
          }
        }
        //Casual
        if($leave_detail->leave_type_id == 1){
          $balance_credit=  ($opening- ($availed_leave + $pending_leave));
        }


        $leave_balance = [
          "leave_type"=>$leave_detail->leave_type,
          "leave_type_id"=>$leave_detail->leave_type_id,
          "total_leaves_credit"=>$leave_detail->total_credit,
          "availed_leave"=>$availed_leave,
          "pending_leave"=>$pending_leave,
          "balance_credit"=>$balance_credit

        ];

      }
      //Leave Calculation Contract & Project staff
      else  if(($leave_detail->leave_type_id == 1) && ($employee_details->employment_type == 2 || $employee_details->employment_type == 4)){

        $date_start = $date_result['cl_start_date'];
        $date_end = $date_result['cl_end_date'];
        // echo $employee_details->employment_type;
        // print_r($date_result);exit();

           $availed_leave = LeaveRequestDetails::where('status',1)->where('user_id',$employee_details->user_id)->where('leave_type_id',$leave_detail->leave_type_id)->whereBetween('date', [$date_start, $date_end])->sum('leave_duration');
           $pending_leave = LeaveRequestDetails::where('status',0)->where('user_id',$employee_details->user_id)->where('leave_type_id',$leave_detail->leave_type_id)->whereBetween('date', [$date_start, $date_end])->sum('leave_duration');


        $user_leave = DB::table('opening_leave_credits')->where('user_id',$employee_details->user_id)->where('leave_type_id',$leave_detail->leave_type_id)->where('leave_period_start',$date_start)->where('leave_period_end',$date_end)->first();
       if( $user_leave){
        $opening = $user_leave->credit;
       }
       else{
        // $opening = $leave_detail->total_credit;
        $opening = $date_result['total_leave'];

       }
      //  echo $opening;exit;

        //Casual Leave
        if($leave_detail->leave_type_id == 1){
          $balance_credit=  ($opening- ($availed_leave + $pending_leave));
        }
// echo   $balance_credit;exit;

        $leave_balance = [
          "leave_type"=>$leave_detail->leave_type,
          "leave_type_id"=>$leave_detail->leave_type_id,
          "total_leaves_credit"=>$date_result['total_leave'],
          "availed_leave"=>$availed_leave,
          "pending_leave"=>$pending_leave,
          "balance_credit"=>$balance_credit

        ];

      }


      else{
        $leave_balance = [
          "leave_type"=>$leave_detail->leave_type,
          "leave_type_id"=>$leave_detail->leave_type_id,
          "total_leaves_credit"=>$leave_detail->total_credit,
          "availed_leave"=>$availed_leave,
          "pending_leave"=>$pending_leave,
          "balance_credit"=>($leave_detail->total_credit-($availed_leave + $pending_leave))

        ];
      }

       array_push($leaves_total_credit_details,$leave_balance);
     }
    // return view('content.leave.leave-request',compact('leave_types','leaves_total_credit_details','date_start','date_end','leaves_total_credit'),['pageConfigs'=> $pageConfigs]);




      return view('content.attendance.employee_monitor',['pageConfigs'=> $pageConfigs],compact('durationHours','from','to','grace','DurationMinutes','attendance_data','date_range_array',
    'holidays','leaves','movements','missedpunches','leaveError','message','leave_types','leaves_total_credit_details','date_start','date_end','leaves_total_credit'));
    }

    public function download(){
      return response()->download(public_path('storage/AttendanceRepot01-09-2023To30-09-2023.pdf'));
    }

    public function attendanceManagement()
    {
      $id= Auth::user()->id;
      $employment_types = EmploymentType::orderBy('id','DESC')->get();
      $hr = User::permission('attendance-management')->where('users.id',$id)->count();
      $team = User::permission('team-attendance-management')->where('users.id',$id)->count();

      // print_r($team);exit;
      if($hr > 0 ){
        $employees = Employee::where('status',1)->orderBy('id','DESC')->get();
      }
      else if($team > 0){

        $employees = Employee::where('employees.reporting_officer',$id)->where('status',1)->orderBy('id','DESC')->get();
      }

      $pageConfigs = ['myLayout' => 'horizontal'];
      return view('content.attendance.attendance-management',compact('employment_types','employees'),['pageConfigs'=> $pageConfigs]);
    }



    public function downloadBulk(Request $request){

      $from = date('Y-m-d', strtotime(str_replace('-', '/', $request->input('fromDate'))));
      $to = date('Y-m-d', strtotime(str_replace('-', '/', $request->input('toDate'))));

      if($request->input('type') == 1){
        $employees = [Auth::user()->id];
      }
      else{
        $employees = $request->input('employeeList');
      }
      // \DB::enableQueryLog();
      $attendance = Attendance::select('attendances.*','employees.empId','employees.profile_pic','employees.email','employees.mobile','employees.name','designations.designation','leave_request_details.leave_day_type',
      'leave_request_details.status as leave_status','emp.name as action_by_name','leaves.leave_type',
      'movements.title','movements.type','movements.start_date','movements.start_time','movements.end_date','movements.end_time','movements.status as movement_status',
    'missed_punches.type as miss_type','missed_punches.date as miss_date','missed_punches.checkinTime','missed_punches.checkoutTime','missed_punches.status as miss_status','emp_mov.name as action_by_name','attendances.date as dates',)
    ->join("employees","employees.user_id","=","attendances.user_id")


    ->leftjoin("leave_request_details",function($join){
      $join->on("leave_request_details.user_id","=","attendances.user_id")
          ->on("leave_request_details.date","=","attendances.date");
  })
  ->leftjoin("employees as emp","emp.user_id","=","leave_request_details.action_by")
  ->leftjoin("leaves","leaves.id","=","leave_request_details.leave_type_id")

  ->leftjoin("movements",function($join){
    $join->on("movements.user_id","=","attendances.user_id");
    $join->on("movements.start_date",">=","attendances.date");
    $join->on("movements.end_date","<=","attendances.date");


        // ->whereBetween('movements.start_date', [$from,$to])
        // ->whereBetween('movements.end_date', [$from,$to]);
        // ->whereBetween('movements.start_date', ['2023-10-20','2023-10-20'])
        // ->whereBetween('movements.end_date',  ['2023-10-20','2023-10-20']);
        // ->whereBetween('attendances.date', ['movements.start_date','movements.end_date']);

})
->leftjoin("employees as emp_mov","emp_mov.user_id","=","movements.action_by")

->leftjoin("missed_punches",function($join){
  $join->on("missed_punches.user_id","=","attendances.user_id");
  $join->on("missed_punches.date","=","attendances.date");


})
->leftjoin("employees as emp_miss","emp_miss.user_id","=","missed_punches.action_by")


    ->leftjoin("designations","designations.id","=","employees.designation")
    ->whereIn('attendances.user_id',$employees)
    ->whereBetween('attendances.date', [$from, $to])
    ->orderBy('attendances.user_id','DESC')->get();
    // dd(\DB::getQueryLog());

    if($request->input('view_type') == 'html'){
      return response()->json(["list"=>$attendance]);
    }
    else if($request->input('view_type') == 'pdf'){
      $pdf = PDF::loadView('exports.attendance-export-pdf', compact('attendance'));
      return $pdf->download('attendance.pdf');


    }
    else if($request->input('view_type') == 'excel'){

      return Excel::download(new AttendanceExport($attendance), 'Attendance.xlsx');

    }
      // return response()->download(public_path('storage/AttendanceRepot01-09-2023To30-09-2023Bulk.pdf'));
    }

    public function import(Request $request)
    {
        Excel::import(new AttendanceImport,  $request->file('file'));
        // Excel::import(new ImportUser,
        // $request->file('file')->store('files'));

        return response()->json("imported");
    }


    public function attendanceMonitorReport(Request $request)
    {
      $pageConfigs = ['myLayout' => 'horizontal'];
      // $from = date('Y-m-d', strtotime(str_replace('-', '/', $request->input('fromDate'))));
      // $to = date('Y-m-d', strtotime(str_replace('-', '/', $request->input('toDate'))));

      // $var = $request->input('fromDate');
      // $datef = str_replace('/', '-', $var);
      // $from=  date('Y-m-d', strtotime($datef));

      // $var2 = $request->input('toDate');
      // $datet = str_replace('/', '-', $var2);
      // $to=  date('Y-m-d', strtotime($datet));

      try {
        $from = Carbon::createFromFormat('d/m/Y', $request->input('fromDate'))->format('Y-m-d');

    } catch (\Exception $e) {
        return response()->json(['error' => 'Invalid date format'], 400);
    }

    try {
      $to = Carbon::createFromFormat('d/m/Y', $request->input('toDate'))->format('Y-m-d');

  } catch (\Exception $e) {
      return response()->json(['error' => 'Invalid date format'], 400);
  }

      // $id = $request->input('employeeList');

      if($request->input('type') == 1){
        $id = [Auth::user()->id];
      }
      else{
        $id = $request->input('employeeList');
      }

      // $from = date('Y-m-01');
      $leaveError=0;
      $message='';

      // Get today's date
      // $to = date('Y-m-d');
      // $id= $request->input('user_id');

      $data = AttendanceLog::select('date')->orderBy('date', 'desc')->first();
      // $to= $data->date;

      $DurationMinutes = AttendanceLog::whereBetween('AttendanceLogs.date', [$from, $to])->where('AttendanceLogs.user_id',$id)->sum('Duration');
      // $LateBy = AttendanceLog::whereBetween('AttendanceLogs.date', [$from, $to])->where('AttendanceLogs.user_id',$id)->sum('LateBy');
      // $EarlyBy = AttendanceLog::whereBetween('AttendanceLogs.date', [$from, $to])->where('AttendanceLogs.user_id',$id)->sum('EarlyBy');
      $grace=240;

      $durationHours = floor($DurationMinutes / 60) . " hours " . ($DurationMinutes % 60) . " minutes";
      // $LateByHours = floor($LateBy / 60) . " hours " . ($LateBy % 60) . " minutes";
      // $EarlyByHours = floor($EarlyBy / 60) . " hours " . ($EarlyBy % 60) . " minutes";

      function generateDatesWithWeek($start_date, $end_date) {
        $dates = array();
        $current_date = strtotime($start_date);
        $end_date = strtotime($end_date);

        while ($current_date <= $end_date) {
            $dates[] = array(
                'date' => date('Y-m-d', $current_date),
                'weekday' => date('l', $current_date)
            );
            $current_date = strtotime('+1 day', $current_date);
        }

        return $dates;
    }



    // Example usage:
    $start_date = $from;
    $end_date = $to;
    $date_range_array = generateDatesWithWeek($start_date, $end_date);
    // print_r($date_range);
    $attendance_data = AttendanceLog::whereBetween('AttendanceLogs.date', [$from, $to])->where('AttendanceLogs.user_id',$id)->orderBy('date')->get();

    $holidays= Holiday::whereBetween('holidays.date', [$from, $to])->get();
    $leaves = LeaveRequestDetails::select('leave_request_details.leave_day_type','leave_request_details.user_id as leave_user_id','leave_request_details.date as leave_date',
    'leave_request_details.status as leave_status','emp.name as leave_action_by_name','leaves.leave_type',)
   ->leftjoin("employees as emp","emp.user_id","=","leave_request_details.action_by")
   ->leftjoin("leaves","leaves.id","=","leave_request_details.leave_type_id")
  ->where('leave_request_details.user_id',$id)
  ->whereBetween('leave_request_details.date', [$from, $to])
  ->orderBy('leave_request_details.user_id','DESC')->get();

  $movements = Movement::select('movements.user_id as mov_user_id','movements.title','movements.type','movements.start_date','movements.start_time','movements.end_date','movements.end_time','movements.status as movement_status',
  'emp_mov.name as movement_action_by_name','movements.remark','movements.location','movements.description')
->leftjoin("employees as emp_mov","emp_mov.user_id","=","movements.action_by")
->where('movements.user_id',$id)
  // ->whereBetween('movements.start_date', [$from, $to])
  // ->whereBetween('movements.end_date', [$from, $to])
  ->where(function ($query) use ($from, $to) {
    $query->where('start_date', '<=', $to) // Database start is before or on the selected end
          ->where('end_date', '>=', $from); // Database end is after or on the selected start
})
  ->orderBy('movements.user_id','DESC')->get();

  $missedpunches = MissedPunch::select('missed_punches.user_id as miss_user_id','missed_punches.type as miss_type','missed_punches.date as miss_date','missed_punches.checkinTime','missed_punches.checkoutTime','missed_punches.status as miss_status',
  'emp_miss.name as misspunch_action_by_name','missed_punches.description')
->leftjoin("employees as emp_miss","emp_miss.user_id","=","missed_punches.action_by")
->where('missed_punches.user_id',$id)
  ->whereBetween('missed_punches.date', [$from, $to])
  ->orderBy('missed_punches.user_id','DESC')->get();





    $employee_details=Employee::where('user_id',$id)->first();
    $employeeName=$employee_details->name;

    $leave_types = Leave::orderBy('id','DESC')->get();

    if($employee_details->contract_start_date == NULL && $employee_details->employment_type !=1  && $employee_details->employment_type !=3){
      $leaveError=1;
      // return Redirect::back()->withErrors(['insufficient' => 'Contract Period Not Updated']);
      $message = 'Contract Period Not Updated. Please contact our Human Resources (HR) department at hr@cmd.kereal.gov.in to update your contract period. Once this is done, you can proceed with your request as usual.';
      // return view('content.pages.pages-custom-error',compact('message'),['pageConfigs'=> $pageConfigs]);
      return view('content.attendance.employee_monitor-report',['pageConfigs'=> $pageConfigs],compact('durationHours','from','to','grace','DurationMinutes','attendance_data','date_range_array',
      'holidays','leaves','movements','missedpunches','leaveError','message'));

    }


     $leaves_total_credit = LeaveAssign::select('leave_assigns.id','leave_assigns.leave_type as leave_type_id','leaves.leave_type','total_credit','employment_types.employment_type','leave_assigns.status','leave_assigns.created_at')
     ->leftjoin("leaves","leaves.id","=","leave_assigns.leave_type")
     ->join("employment_types","employment_types.id","=","leave_assigns.employment_type")
     ->where('leave_assigns.employment_type',$employee_details->employment_type)
     ->orderBy('leave_assigns.leave_type','ASC')->get();
     $leaves_total_credit_details=[];
     foreach( $leaves_total_credit as $leave_detail){


    // $date_result = (new MasterFunctionController)->GenerateLeavePeriod($employee_details->employment_type,$employee_details->doj);

    $date_result = (new MasterFunctionController)->GetLeavePeriod($employee_details->user_id,$leave_detail->leave_type_id);
// print_r($date_result);exit;

    $date_start = $date_result['start_date'];
    $date_end = $date_result['end_date'];
    // echo $employee_details->employment_type;
    // print_r($date_result);exit();

       $availed_leave = LeaveRequestDetails::where('status',1)->where('user_id',$employee_details->user_id)->where('leave_type_id',$leave_detail->leave_type_id)->whereBetween('date', [$date_start, $date_end])->sum('leave_duration');
       $pending_leave = LeaveRequestDetails::where('status',0)->where('user_id',$employee_details->user_id)->where('leave_type_id',$leave_detail->leave_type_id)->whereBetween('date', [$date_start, $date_end])->sum('leave_duration');
     // Leave calculation Regular
       if(($leave_detail->leave_type_id == 2 || $leave_detail->leave_type_id == 3 || $leave_detail->leave_type_id == 1) && $employee_details->employment_type == 1){
        $user_leave = DB::table('opening_leave_credits')->where('user_id',$employee_details->user_id)->where('leave_type_id',$leave_detail->leave_type_id)->where('leave_period_start',$date_start)->where('leave_period_end',$date_end)->first();
       if( $user_leave){
        $opening = $user_leave->credit;
       }
       else{
        $opening = $leave_detail->total_credit;
       }

        //sick Leave
        if($leave_detail->leave_type_id == 2){
          $balance_credit=  ($opening- ($availed_leave + $pending_leave));
          if($balance_credit > 120){
            $balance_credit = 120;
          }
        }
        //Privilaged
        if($leave_detail->leave_type_id == 3){
          $balance_credit=  ($opening - ($availed_leave + $pending_leave));
          if($balance_credit > 300){
            $balance_credit = 300;
          }
        }
        //Casual
        if($leave_detail->leave_type_id == 1){
          $balance_credit=  ($opening- ($availed_leave + $pending_leave));
        }


        $leave_balance = [
          "leave_type"=>$leave_detail->leave_type,
          "leave_type_id"=>$leave_detail->leave_type_id,
          "total_leaves_credit"=>$leave_detail->total_credit,
          "availed_leave"=>$availed_leave,
          "pending_leave"=>$pending_leave,
          "balance_credit"=>$balance_credit

        ];

      }
      //Leave Calculation Contract & Project staff
      else  if(($leave_detail->leave_type_id == 1) && ($employee_details->employment_type == 2 || $employee_details->employment_type == 4)){

        $date_start = $date_result['cl_start_date'];
        $date_end = $date_result['cl_end_date'];
        // echo $employee_details->employment_type;
        // print_r($date_result);exit();

           $availed_leave = LeaveRequestDetails::where('status',1)->where('user_id',$employee_details->user_id)->where('leave_type_id',$leave_detail->leave_type_id)->whereBetween('date', [$date_start, $date_end])->sum('leave_duration');
           $pending_leave = LeaveRequestDetails::where('status',0)->where('user_id',$employee_details->user_id)->where('leave_type_id',$leave_detail->leave_type_id)->whereBetween('date', [$date_start, $date_end])->sum('leave_duration');


        $user_leave = DB::table('opening_leave_credits')->where('user_id',$employee_details->user_id)->where('leave_type_id',$leave_detail->leave_type_id)->where('leave_period_start',$date_start)->where('leave_period_end',$date_end)->first();
       if( $user_leave){
        $opening = $user_leave->credit;
       }
       else{
        // $opening = $leave_detail->total_credit;
        $opening = $date_result['total_leave'];

       }
      //  echo $opening;exit;

        //Casual Leave
        if($leave_detail->leave_type_id == 1){
          $balance_credit=  ($opening- ($availed_leave + $pending_leave));
        }
// echo   $balance_credit;exit;

        $leave_balance = [
          "leave_type"=>$leave_detail->leave_type,
          "leave_type_id"=>$leave_detail->leave_type_id,
          "total_leaves_credit"=>$date_result['total_leave'],
          "availed_leave"=>$availed_leave,
          "pending_leave"=>$pending_leave,
          "balance_credit"=>$balance_credit

        ];

      }


      else{
        $leave_balance = [
          "leave_type"=>$leave_detail->leave_type,
          "leave_type_id"=>$leave_detail->leave_type_id,
          "total_leaves_credit"=>$leave_detail->total_credit,
          "availed_leave"=>$availed_leave,
          "pending_leave"=>$pending_leave,
          "balance_credit"=>($leave_detail->total_credit-($availed_leave + $pending_leave))

        ];
      }

       array_push($leaves_total_credit_details,$leave_balance);
     }
    // return view('content.leave.leave-request',compact('leave_types','leaves_total_credit_details','date_start','date_end','leaves_total_credit'),['pageConfigs'=> $pageConfigs]);




      return view('content.attendance.employee_monitor-report',['pageConfigs'=> $pageConfigs],compact('employee_details','durationHours','from','to','grace','DurationMinutes','attendance_data','date_range_array',
    'holidays','leaves','movements','missedpunches','leaveError','message','leave_types','leaves_total_credit_details','date_start','date_end','leaves_total_credit'));
    }


}
