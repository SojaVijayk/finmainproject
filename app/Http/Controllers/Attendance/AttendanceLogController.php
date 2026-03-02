<?php

namespace App\Http\Controllers\Attendance;


use App\Http\Controllers\Controller;
use App\Imports\AttendanceImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\AttendanceLog;
use App\Models\Leave;
use App\Models\User;
use App\Models\EmploymentType;
use App\Models\Employee;

use App\Models\LeaveRequestDetails;
use App\Models\Movement;
use App\Models\MissedPunch;
use App\Exports\AttendanceExport;
use App\Models\Holiday;
use Illuminate\Support\Facades\Auth;
use PDF;
use Carbon\CarbonPeriod;
use Carbon\Carbon;
class AttendanceLogController extends Controller
{
    //


    public function import(Request $request)
    {
        Excel::import(new AttendanceImport,  $request->file('file'));
        // Excel::import(new ImportUser,
        // $request->file('file')->store('files'));

        return response()->json("imported");
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
      $attendance = AttendanceLog::select('AttendanceLogs.*','employees.empId','employees.profile_pic','employees.email','employees.mobile','employees.name','designations.designation','leave_request_details.leave_day_type',
      'leave_request_details.status as leave_status','emp.name as action_by_name','leaves.leave_type',
      'movements.title','movements.type','movements.start_date','movements.start_time','movements.end_date','movements.end_time','movements.status as movement_status',
    'missed_punches.type as miss_type','missed_punches.date as miss_date','missed_punches.checkinTime','missed_punches.checkoutTime','missed_punches.status as miss_status','emp_mov.name as action_by_name','AttendanceLogs.date as dates',)
    ->join("employees","employees.user_id","=","AttendanceLogs.user_id")


    ->leftjoin("leave_request_details",function($join){
      $join->on("leave_request_details.user_id","=","AttendanceLogs.user_id")
          ->on("leave_request_details.date","=","AttendanceLogs.date");
  })
  ->leftjoin("employees as emp","emp.user_id","=","leave_request_details.action_by")
  ->leftjoin("leaves","leaves.id","=","leave_request_details.leave_type_id")

  ->leftjoin("movements",function($join){
    $join->on("movements.user_id","=","AttendanceLogs.user_id");
    $join->on("movements.start_date",">=","AttendanceLogs.date");
    $join->on("movements.end_date","<=","AttendanceLogs.date");


        // ->whereBetween('movements.start_date', [$from,$to])
        // ->whereBetween('movements.end_date', [$from,$to]);
        // ->whereBetween('movements.start_date', ['2023-10-20','2023-10-20'])
        // ->whereBetween('movements.end_date',  ['2023-10-20','2023-10-20']);
        // ->whereBetween('attendances.date', ['movements.start_date','movements.end_date']);

})
->leftjoin("employees as emp_mov","emp_mov.user_id","=","movements.action_by")

->leftjoin("missed_punches",function($join){
  $join->on("missed_punches.user_id","=","AttendanceLogs.user_id");
  $join->on("missed_punches.date","=","AttendanceLogs.date");


})
->leftjoin("employees as emp_miss","emp_miss.user_id","=","missed_punches.action_by")


    ->leftjoin("designations","designations.id","=","employees.designation")
    ->whereIn('AttendanceLogs.user_id',$employees)
    ->whereBetween('AttendanceLogs.date', [$from, $to])
    ->orderBy('AttendanceLogs.user_id','DESC')->get();
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



    public function downloadBulk1(Request $request){

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



      if($request->input('type') == 1){
        $employees = [Auth::user()->id];
      }
      else{
        $employees = $request->input('employeeList');
      }
      // \DB::enableQueryLog();
      $attendance = AttendanceLog::select('AttendanceLogs.*','employees.empId','employees.profile_pic','employees.email','employees.mobile','employees.name','designations.designation'
   )
    ->join("employees","employees.user_id","=","AttendanceLogs.user_id")

    ->leftjoin("designations","designations.id","=","employees.designation")
    ->whereIn('AttendanceLogs.user_id',$employees)
    ->whereBetween('AttendanceLogs.date', [$from, $to])
    ->orderBy('AttendanceLogs.user_id','DESC')->get();

// print_r($attendance);exit;


    $leaves = LeaveRequestDetails::select('leave_request_details.leave_day_type','leave_request_details.user_id as leave_user_id','leave_request_details.date as leave_date',
    'leave_request_details.status as leave_status','emp.name as leave_action_by_name','leaves.leave_type',)
   ->leftjoin("employees as emp","emp.user_id","=","leave_request_details.action_by")
   ->leftjoin("leaves","leaves.id","=","leave_request_details.leave_type_id")
  ->whereIn('leave_request_details.user_id',$employees)
  ->whereBetween('leave_request_details.date', [$from, $to])
  ->orderBy('leave_request_details.user_id','DESC')->get();

  $movements = Movement::select('movements.user_id as mov_user_id','movements.title','movements.type','movements.start_date','movements.start_time','movements.end_date','movements.end_time','movements.status as movement_status',
  'emp_mov.name as movement_action_by_name','movements.remark','movements.location','movements.description')
->leftjoin("employees as emp_mov","emp_mov.user_id","=","movements.action_by")
->whereIn('movements.user_id',$employees)
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
->whereIn('missed_punches.user_id',$employees)
  ->whereBetween('missed_punches.date', [$from, $to])

  ->orderBy('missed_punches.user_id','DESC')->get();

  $startDate = Carbon::createFromFormat('Y-m-d', $from);
  $endDate = Carbon::createFromFormat('Y-m-d', $to);

  $dateRange = CarbonPeriod::create($startDate, $endDate);


  // $startDate = Carbon::createFromFormat('Y-m-d', '2020-11-01');
  // $endDate = Carbon::createFromFormat('Y-m-d', '2020-11-05');

  // $dateRange = CarbonPeriod::create($startDate, $endDate);

 $dateRange=$dateRange->toArray();
//   print_r($dateRange);
// exit;

$employeedetails= Employee::select('employees.empId','employees.user_id','employees.profile_pic','employees.email','employees.mobile','employees.name','designations.designation')
->leftjoin("designations","designations.id","=","employees.designation")
->whereIn('employees.user_id',$employees)
->orderBy('employees.name','DESC')->get();

$holidays= Holiday::whereBetween('holidays.date', [$from, $to])->get();

    // dd(\DB::getQueryLog());

    if($request->input('view_type') == 'html'){
      return view('content.attendance.attendance-report-view',compact('attendance','dateRange','leaves','movements','missedpunches','employeedetails','holidays','from','to'));

      // return response()->json(["list"=>$attendance]);
    }
    else if($request->input('view_type') == 'pdf'){
      $pdf = PDF::loadView('exports.attendance-export-pdf-date', compact('attendance','dateRange','leaves','movements','missedpunches','employeedetails','holidays','from','to'));
      return $pdf->download('attendance.pdf');


    }
    else if($request->input('view_type') == 'excel'){

      return Excel::download(new AttendanceExport($attendance), 'Attendance.xlsx');

    }
      // return response()->download(public_path('storage/AttendanceRepot01-09-2023To30-09-2023Bulk.pdf'));
    }





}
