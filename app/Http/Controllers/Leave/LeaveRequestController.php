<?php


namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use App\Models\Leave;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestDetails;
use App\Models\LeaveAssign;
use App\Models\LeaveDutyAssignment;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Mail\LeaveRequestMail;
use App\Mail\LeaveRequestActionMail;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Exports\LeaveExport;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\MasterFunctionController;
use Illuminate\Support\Facades\Redirect;

class LeaveRequestController extends Controller
{
    //
    public function index()
  {
    $pageConfigs = ['myLayout' => 'horizontal'];

    $id= Auth::user()->id;
    $employee_details=Employee::where('user_id',$id)->first();

    $leave_types = Leave::orderBy('id','DESC')->get();

    if(($employee_details->contract_start_date == NULL || $employee_details->contract_end_date < date('Y-m-d')) && $employee_details->employment_type !=1  && $employee_details->employment_type !=3){
      // return Redirect::back()->withErrors(['insufficient' => 'Contract Period Not Updated']);
      $message = 'Contract Period Not Updated or Expired. Please contact our Human Resources (HR) department at hr@cmd.kereal.gov.in to update your contract period. Once this is done, you can proceed with your request as usual.';
      return view('content.pages.pages-custom-error',compact('message'),['pageConfigs'=> $pageConfigs]);
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

   $employment_type= $employee_details->employment_type;

    $startDate = isset($date_result['start_date']) ? \Carbon\Carbon::parse($date_result['start_date']) : null;
    $endDate = isset($date_result['end_date']) ? \Carbon\Carbon::parse($date_result['end_date']) : null;

    // Check if both dates exist and calculate the duration
    $durationMonths = ($startDate && $endDate) ? $startDate->diffInMonths($endDate) : null;

    $cl_date_start = $date_result['cl_start_date'] ?? $date_result['start_date'];
    $cl_date_end =  $date_result['cl_end_date'] ?? $date_result['end_date'];;
    // echo $employee_details->employment_type;
    // print_r($date_result);exit();

       $availed_leave = LeaveRequestDetails::where('status',1)->where('user_id',$employee_details->user_id)->where('leave_type_id',$leave_detail->leave_type_id)->whereBetween('date', [$date_start, $date_end])->sum('leave_duration');
       $pending_leave = LeaveRequestDetails::where('status',0)->where('user_id',$employee_details->user_id)->where('leave_type_id',$leave_detail->leave_type_id)->whereBetween('date', [$date_start, $date_end])->sum('leave_duration');
     // Leave calculation Regular
       if(($leave_detail->leave_type_id == 2 || $leave_detail->leave_type_id == 3 || $leave_detail->leave_type_id == 1) && $employee_details->employment_type == 1){
        $user_leave = DB::table('opening_leave_credits')->where('user_id',Auth::user()->id)->where('leave_type_id',$leave_detail->leave_type_id)->where('leave_period_start',$date_start)->where('leave_period_end',$date_end)->first();
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
        $cl_date_start = $date_result['cl_start_date'];
        $cl_date_end = $date_result['cl_end_date'];
        // echo $employee_details->employment_type;
        // print_r($date_result);exit();

           $availed_leave = LeaveRequestDetails::where('status',1)->where('user_id',$employee_details->user_id)->where('leave_type_id',$leave_detail->leave_type_id)->whereBetween('date', [$date_start, $date_end])->sum('leave_duration');
           $pending_leave = LeaveRequestDetails::where('status',0)->where('user_id',$employee_details->user_id)->where('leave_type_id',$leave_detail->leave_type_id)->whereBetween('date', [$date_start, $date_end])->sum('leave_duration');


        $user_leave = DB::table('opening_leave_credits')->where('user_id',Auth::user()->id)->where('leave_type_id',$leave_detail->leave_type_id)->where('leave_period_start',$date_start)->where('leave_period_end',$date_end)->first();
      //  print_r( $user_leave );exit;
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
      // else if(($leave_detail->leave_type_id == 2) && ($employee_details->employment_type == 2 || $employee_details->employment_type == 4)){

      //   $user_leave = DB::table('opening_leave_credits')->where('user_id',Auth::user()->id)->where('leave_type_id',$leave_detail->leave_type_id)->where('leave_period_start',$date_start)->where('leave_period_end',$date_end)->first();
      // //  print_r( $user_leave );exit;
      //   if( $user_leave){
      //   $opening = $user_leave->credit;
      //  }
      //  else{
      //   $opening = $leave_detail->total_credit;exit;
      //   $opening = $date_result['total_leave'];

      //  }

      //  $leave_balance = [
      //   "leave_type"=>$leave_detail->leave_type,
      //   "leave_type_id"=>$leave_detail->leave_type_id,
      //   "total_leaves_credit"=>$date_result['total_leave'],
      //   "availed_leave"=>$availed_leave,
      //   "pending_leave"=>$pending_leave,
      //   "balance_credit"=>($date_result['total_leave']-($availed_leave + $pending_leave))

      // ];


      // }



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







    return view('content.leave.leave-request',compact('leave_types','leaves_total_credit_details','date_start','date_end','cl_date_start','cl_date_end','leaves_total_credit','employment_type','durationMonths'),['pageConfigs'=> $pageConfigs]);
  }

  public function leaveList()
  {

    $id= Auth::user()->id;



    $list = LeaveRequest::with('leaveRequestDetails')->with('dutyAssignments.user')
    ->join("employees","employees.user_id","=","leave_requests.user_id")
    ->leftjoin("designations","designations.id","=","employees.designation")
    ->leftjoin("leaves","leaves.id","=","leave_requests.leave_type_id")
    ->leftjoin("employees as emp","emp.user_id","=","leave_requests.action_by")
    ->select('leave_requests.*','leaves.leave_type','employees.name','employees.email','employees.profile_pic','designations.designation','emp.name as action_by_name',DB::raw("DATE_FORMAT(leave_requests.requested_at, '%d-%b-%Y %H:%i') as formatted_requested_at"),DB::raw("DATE_FORMAT(leave_requests.action_at, '%d-%b-%Y %H:%i') as formatted_action_at"))->where('leave_requests.user_id',$id)
    ->orderBy('leave_requests.status')
    ->orderBy('leave_requests.from', 'DESC')
    ->get();
      //  $queries = DB::getQueryLog();
      //   $last_query = end($queries);

    return response()->json(['data'=> $list]);

  }

  public function store(Request $request)
  {
      //
      $this->validate($request, [
        'leave_type_id' => 'required',
        'from' => 'required|',
        'to' => 'required|',
        'date_list' => 'required|',
        'duration' => 'required|',
        'leave_period_start' => 'required',
        'leave_period_end' => 'required',
         'duty_assignments' => 'nullable|array',
        'duty_assignments.*.user_id' => 'nullable|integer|exists:users,id',
        'duty_assignments.*.description' => 'nullable|string|max:255',



    ]);
    $id= Auth::user()->id;
    $date= date('Y-m-d H:i:s');
    // $from = date('Y-m-d', strtotime(str_replace('-', '/', $request->input('from'))));
    // $to = date('Y-m-d', strtotime(str_replace('-', '/', $request->input('to'))));

    // $var = $request->input('from');
    // $datef = str_replace('/', '-', $var);
    // $from=  date('Y-m-d', strtotime($datef));

    // $var2 = $request->input('to');
    // $datet = str_replace('/', '-', $var2);
    // $to=  date('Y-m-d', strtotime($datet));

    try {
      $from = Carbon::createFromFormat('d/m/Y', $request->input('from'))->format('Y-m-d');

  } catch (\Exception $e) {
      return response()->json(['error' => 'Invalid date format'], 400);
  }

  try {
    $to = Carbon::createFromFormat('d/m/Y', $request->input('to'))->format('Y-m-d');

} catch (\Exception $e) {
    return response()->json(['error' => 'Invalid date format'], 400);
}

 $date_list = json_encode($request->input('date_list'));
    // $from = date('2023-04-01');
    // $to = date('2024-03-31');
    $employee = Employee::where('employees.user_id',Auth::user()->id)->first();
     $total_leaves_credit = LeaveAssign::where('employment_type', $employee->employment_type)->where('leave_type', $request->input('leave_type_id'))->first();
    $date_result = (new MasterFunctionController)->GetLeavePeriod($employee->user_id,$request->input('leave_type_id'));
    // $date_result = (new MasterFunctionController)->GenerateLeavePeriod($employee->employment_type,$employee->doj);
    $date_start = $date_result['start_date'];
    $date_end = $date_result['end_date'];


    if($request->input('leave_type_id') < 4){



    $availed_leave = LeaveRequestDetails::where('status',1)->where('user_id',$employee->user_id)->where('leave_type_id',$request->input('leave_type_id'))->whereBetween('date', [$date_start, $date_end])->sum('leave_duration');
    $pending_leave = LeaveRequestDetails::where('status',0)->where('user_id',$employee->user_id)->where('leave_type_id',$request->input('leave_type_id'))->whereBetween('date', [$date_start, $date_end])->sum('leave_duration');
//regular
    if(($request->input('leave_type_id') == 1 || $request->input('leave_type_id') == 2 || $request->input('leave_type_id') == 3) && $employee->employment_type == 1){

      $user_leave = DB::table('opening_leave_credits')->where('user_id',Auth::user()->id)->where('leave_type_id',$request->input('leave_type_id'))->where('leave_period_start',$date_start)->where('leave_period_end',$date_end)->first();
     if( $user_leave){
      $opening = $user_leave->credit;
     }
     else{
      $opening = $total_leaves_credit->total_credit;
     }

      //sick Leave
      if($request->input('leave_type_id') == 2){
        $balance_credit=  ($opening- ($availed_leave + $pending_leave));
        if($balance_credit > 120){
          $balance_credit = 120;
        }
      }
      //Privilaged
      if($request->input('leave_type_id') == 3){
        $balance_credit=  ($opening - ($availed_leave + $pending_leave));
        if($balance_credit > 300){
          $balance_credit = 300;
        }
      }
      //casual Leave
      if($request->input('leave_type_id') == 1){
        $balance_credit=  ($opening- ($availed_leave + $pending_leave));
      }

      $balance =$balance_credit;
      // $balance = ($balance_credit-($availed_leave + $pending_leave));

    }

    //Leave Calculation Contract & Project staff
    else if(($request->input('leave_type_id') == 1  && ($employee->employment_type == 2 ||  $employee->employment_type == 4))){
      $date_start = $date_result['cl_start_date'];
      $date_end = $date_result['cl_end_date'];
      // echo $employee_details->employment_type;
      // print_r($date_result);exit();

         $availed_leave = LeaveRequestDetails::where('status',1)->where('user_id',$employee->user_id)->where('leave_type_id',$request->input('leave_type_id'))->whereBetween('date', [$date_start, $date_end])->sum('leave_duration');
         $pending_leave = LeaveRequestDetails::where('status',0)->where('user_id',$employee->user_id)->where('leave_type_id',$request->input('leave_type_id'))->whereBetween('date', [$date_start, $date_end])->sum('leave_duration');


      $user_leave = DB::table('opening_leave_credits')->where('user_id',Auth::user()->id)->where('leave_type_id',$request->input('leave_type_id'))->where('leave_period_start',$date_start)->where('leave_period_end',$date_end)->first();
     if( $user_leave){
      $opening = $user_leave->credit;
     }
     else{
      // $opening = $total_leaves_credit->total_credit;
      $opening =  $date_result['total_leave'];
     }

      //Casual Leave
      if($request->input('leave_type_id') == 1){
        $balance_credit=  ($opening- ($availed_leave + $pending_leave));
      }

      $balance =$balance_credit;
      // $balance = $balance_credit - ( $availed_leave + $pending_leave);
    }



    else{
      $balance = $total_leaves_credit->total_credit - ( $availed_leave + $pending_leave);
    }



// echo $date_start.' '.$date_end;exit;

    if($request->input('duration') <=  $balance || $request->input('leave_type_id') > 3){

      $permission = LeaveRequest::create(['leave_type_id' => $request->input('leave_type_id'),
      'duration' => $request->input('duration'),
        'from' => $from,
        'to' => $to,
        'date_list' => $date_list,'description' => $request->input('description'),'user_id' => $id,'status' => 0,'requested_at' => $date
    ]);

      if ($permission) {
        foreach($request->input('date_list') as $data){

           LeaveRequestDetails::create(['leave_type_id' => $permission->leave_type_id,
          'request_id' => $permission->id,
          'leave_day_type' => $data['leave_day_type'],
          'leave_duration' => ($data['leave_day_type'] == 1 ? 1 : 0.5),
            'date' => $data['date'],
           'user_id' => $id,'status' => 0,'requested_at' => $date,
           'leave_period_start' => $date_start,
           'leave_period_end' => $date_end
        ]);
        }

        $mailData = [
          'title' => 'Leave Request',
          'button' => 'Take Action',
          'url' => 'http://localhost:8000/leave/approve-list',
          'body' => Auth::user()->name." created a new leave request for the period of ".$request->input('from')." to ".$request->input('to').".  Please login to your account for take action.",
        ];

        $reporting = Employee::where('employees.user_id',Auth::user()->id)
        ->leftjoin("employees as emp","emp.user_id","=","employees.reporting_officer")
        ->select('emp.email')->first();

        // Mail::to($reporting->email)->send(new LeaveRequestMail($mailData));



        $dutyAssignments = $request->input('duty_assignments');
       if( $request->has('duty_assignments') && count($dutyAssignments) > 0) {
         foreach ($dutyAssignments as $assignment) {
            LeaveDutyAssignment::create([
                'leave_request_id' => $permission->id,
                'user_id' => $assignment['user_id'],
                'description' => $assignment['description'],
            ]);
        }
       }




        return response()->json( ["status"=>true, "data"=>$permission]);
      } else {
        return response()->json(["status"=>false,'message' => "Internal Server Error"], 500);

      }
    }
    else{

      return response()->json(["status"=>false,'message' => "You have no credit and cannot request ".$request->input('duration')." Leave" ], 200);
    }



  }
  //manage LOP
  else{

     $permission = LeaveRequest::create(['leave_type_id' => $request->input('leave_type_id'),
      'duration' => $request->input('duration'),
        'from' => $from,
        'to' => $to,
        'date_list' => $date_list,'description' => $request->input('description'),'user_id' => $id,'status' => 0,'requested_at' => $date
    ]);

      if ($permission) {
        foreach($request->input('date_list') as $data){

           LeaveRequestDetails::create(['leave_type_id' => $permission->leave_type_id,
          'request_id' => $permission->id,
          'leave_day_type' => $data['leave_day_type'],
          'leave_duration' => ($data['leave_day_type'] == 1 ? 1 : 0.5),
            'date' => $data['date'],
           'user_id' => $id,'status' => 0,'requested_at' => $date,
           'leave_period_start' => $date_start,
           'leave_period_end' => $date_end
        ]);
        }

        $mailData = [
          'title' => 'Leave Request',
          'button' => 'Take Action',
          'url' => 'http://localhost:8000/leave/approve-list',
          'body' => Auth::user()->name." created a new leave request for the period of ".$request->input('from')." to ".$request->input('to').".  Please login to your account for take action.",
        ];

        $reporting = Employee::where('employees.user_id',Auth::user()->id)
        ->leftjoin("employees as emp","emp.user_id","=","employees.reporting_officer")
        ->select('emp.email')->first();

        // Mail::to($reporting->email)->send(new LeaveRequestMail($mailData));



        $dutyAssignments = $request->input('duty_assignments');
       if( $request->has('duty_assignments') && count($dutyAssignments) > 0) {
         foreach ($dutyAssignments as $assignment) {
            LeaveDutyAssignment::create([
                'leave_request_id' => $permission->id,
                'user_id' => $assignment['user_id'],
                'description' => $assignment['description'],
            ]);
        }
       }




        return response()->json( ["status"=>true, "data"=>$permission]);
      } else {
        return response()->json(["status"=>false,'message' => "Internal Server Error"], 500);

      }

  }



  }



  public function approveList()
    {


      $id= Auth::user()->id;

      $list = LeaveRequest::join("employees","employees.user_id","=","leave_requests.user_id")
      ->select('leave_requests.*')->where('employees.reporting_officer',$id);

      $totalCount = $list->count();
      $action_started = $list->where('leave_requests.status',1)->count();
      $pending = LeaveRequest::join("employees","employees.user_id","=","leave_requests.user_id")
      ->select('leave_requests.*')->where('employees.reporting_officer',$id)->where('leave_requests.status',0)->count();
      $completed =LeaveRequest::join("employees","employees.user_id","=","leave_requests.user_id")
      ->select('leave_requests.*')->where('employees.reporting_officer',$id)->where('leave_requests.status',2)->count();



      $pageConfigs = ['myLayout' => 'horizontal'];
      return view('content.leave.leave-approve-list',compact('totalCount','action_started','pending','completed'),['pageConfigs'=> $pageConfigs]);
    }
    public function requestList()
    {


      $id= Auth::user()->id;

      $list = LeaveRequest::with('leaveRequestDetails')->with('dutyAssignments.user')
      ->join("employees","employees.user_id","=","leave_requests.user_id")
      ->leftjoin("designations","designations.id","=","employees.designation")
      ->leftjoin("leaves","leaves.id","=","leave_requests.leave_type_id")
      ->leftjoin("employees as emp","emp.user_id","=","leave_requests.action_by")

      ->select('leave_requests.*','leaves.leave_type','employees.name','employees.email','employees.profile_pic','designations.designation','emp.name as action_by_name',DB::raw("DATE_FORMAT(leave_requests.requested_at, '%d-%b-%Y %H:%i') as formatted_requested_at"),DB::raw("DATE_FORMAT(leave_requests.action_at, '%d-%b-%Y %H:%i') as formatted_action_at"))->where('employees.reporting_officer',$id)
      ->orderBy('leave_requests.status')
      ->orderBy('leave_requests.from', 'DESC')->get();
        //  $queries = DB::getQueryLog();
        //   $last_query = end($queries);
        //   dd($queries);
      return response()->json(['data'=> $list]);

    }

    public function edit($id)
    {
        //
      $list = LeaveRequest::with('leaveRequestDetails')->join("employees","employees.user_id","=","leave_requests.user_id")
    ->leftjoin("designations","designations.id","=","employees.designation")
    ->leftjoin("leaves","leaves.id","=","leave_requests.leave_type_id")
    ->select('leave_requests.*','leaves.leave_type','employees.employment_type','employees.doj','employees.name','employees.email','employees.profile_pic','designations.designation')->where('leave_requests.id',$id)->first();

    $total_leaves_credit = LeaveAssign::where('employment_type', $list->employment_type)->where('leave_type', $list->leave_type_id)->first();
    // $date_result = (new MasterFunctionController)->GenerateLeavePeriod($list->employment_type,$list->doj);
    // $date_start = $date_result['start_date'];
    // $date_end = $date_result['end_date'];
    //   echo $list->employment_type;
    // print_r($date_result);exit();
    $leave_period_data = LeaveRequestDetails::where('request_id',$id)->first();
    $date_start =  $leave_period_data->leave_period_start;
    $date_end =  $leave_period_data->leave_period_end;
      // print_r(  $leave_period_data);exit();


    // $availed_leave = LeaveRequestDetails::where('status',1)->where('user_id',$list->user_id)->where('leave_type_id',$list->leave_type_id)->whereBetween('date', [$date_start, $date_end])->sum('leave_duration');
    // $pending_leave = LeaveRequestDetails::where('status',0)->where('user_id',$list->user_id)->where('leave_type_id',$list->leave_type_id)->whereBetween('date', [$date_start, $date_end])->sum('leave_duration');
    $availed_leave = LeaveRequestDetails::where('status',1)->where('user_id',$list->user_id)->where('leave_type_id',$list->leave_type_id)->where('leave_period_start', $date_start)->sum('leave_duration');
    $pending_leave = LeaveRequestDetails::where('status',0)->where('user_id',$list->user_id)->where('leave_type_id',$list->leave_type_id)->where('leave_period_end', $date_end)->sum('leave_duration');
if($list->leave_type_id < 4){
          //Regular employee
    if(($list->leave_type_id == 1 || $list->leave_type_id == 2 || $list->leave_type_id == 3) && $list->employment_type == 1){

    // if($list->leave_type_id == 2 || $list->leave_type_id == 3){

      $user_leave = DB::table('opening_leave_credits')->where('user_id',$list->user_id)->where('leave_type_id',$list->leave_type_id)->where('leave_period_start',$date_start)->where('leave_period_end',$date_end)->first();
      if( $user_leave){
        $opening = $user_leave->credit;
       }
       else{
        $opening = $total_leaves_credit->total_credit;
       }
      //sick Leave
      if($list->leave_type_id == 2){
        $balance_credit=  ($opening - ( $availed_leave));
        if($balance_credit > 120){
          $balance_credit = 120;
        }
      }
      //Privilaged
      if($list->leave_type_id == 3){
        $balance_credit=  ($opening - ( $availed_leave ));
        if($balance_credit > 300){
          $balance_credit = 300;
        }
      }
      //casual Leave
      if($list->leave_type_id == 1){
        $balance_credit=  ($opening- ( $availed_leave ));
      }


      $leave_balance = [
        "total_leaves_credit"=>$total_leaves_credit->total_credit,
        "availed_leave"=>$availed_leave,
        "pending_leave"=>$pending_leave,
        "balance_credit"=>$balance_credit,

      ];
    }
//Contract & project Emloyee
    else  if(($list->leave_type_id == 1) && ($list->employment_type == 2 || $list->employment_type == 4)){
      $period_start_date_array = explode('-', $date_start);
      $period_start_month = $period_start_date_array[1];
      $period_start_day   = $period_start_date_array[2];
      $period_start_year  = $period_start_date_array[0];

      $period_end_date_array = explode('-', $date_end);
      $period_end_month = $period_end_date_array[1];
      $period_end_day   = $period_end_date_array[2];
      $period_end_year  = $period_end_date_array[0];

      $days= $period_end_month -  $period_start_month;
      $eligible_start_month_leave=0;
      if($period_end_month == 12 && $period_start_month != 01){
        if( $period_start_day <= 15){
          $eligible_start_month_leave=1;
        }
      }
      if($period_start_month == 01 && $period_end_month != 12){
        if( $period_start_day >= 15){
          $eligible_start_month_leave=1;
        }
      }
      $days= $period_end_month -  $period_start_month;
        if($eligible_start_month_leave ==1){
          $days++;
        }

// echo $period_start_month;exit;
      $user_leave = DB::table('opening_leave_credits')->where('user_id',$list->user_id)->where('leave_type_id',$list->leave_type_id)->where('leave_period_start',$date_start)->where('leave_period_end',$date_end)->first();
     if( $user_leave){
      $opening = $user_leave->credit;
     }
     else{
      //  $opening = $total_leaves_credit->total_credit;
      $opening = $days;
     }

      //Casual Leave
      if($list->leave_type_id == 1){
        $balance_credit=  ($opening- ( $availed_leave ));
      }


      $leave_balance = [

        "total_leaves_credit"=>$days,
        "availed_leave"=>$availed_leave,
        "pending_leave"=>$pending_leave,
        "balance_credit"=>$balance_credit

      ];

    }


    else{
      $leave_balance = [
        "total_leaves_credit"=>$total_leaves_credit->total_credit,
        "availed_leave"=>$availed_leave,
        "pending_leave"=>$pending_leave,
        "balance_credit"=>($total_leaves_credit->total_credit-($availed_leave )),

      ];
    }
}
else{
  $leave_balance = [
        "total_leaves_credit"=>'NA',
        "availed_leave"=>'NA',
        "pending_leave"=>'NA',
        "balance_credit"=>'NA',

      ];
}



          return response()->json(['leave_list'=> $list,"leave_balance"=>$leave_balance,'date_start'=>$date_start,'date_end'=>$date_end]);
    }



    public function action(Request $request,  $id)
    {
        //
        $this->validate($request, [
          'status' => 'required',
          'remark' => 'required',

      ]);

      $date= date('Y-m-d H:i:s');

      $designation = LeaveRequestDetails::find($id);

      $date_start =  $designation->start_date;
      $date_end =  $designation->end_date;


      $designation->status = $request->input('status');
      $designation->action_at = $date;
      $designation->remark = $request->input('remark');
      $designation->action_by = Auth::user()->id;
      $designation->save();


      $count =LeaveRequestDetails::where('status',0)->where('request_id',$designation->request_id)->count();
      $data = LeaveRequest::find($designation->request_id);
      $data->status =   $count > 0 ? 1 : 2;
      $data->action_at = $date;
      $data->action_by = Auth::user()->id;
      $data->save();

      if ($designation ) {
        $list = LeaveRequest::with('leaveRequestDetails')->join("employees","employees.user_id","=","leave_requests.user_id")
        ->leftjoin("designations","designations.id","=","employees.designation")
        ->leftjoin("leaves","leaves.id","=","leave_requests.leave_type_id")
        ->select('leave_requests.*','leaves.leave_type','employees.employment_type','employees.name','employees.email','employees.profile_pic','designations.designation')->where('leave_requests.id',$designation->request_id)->first();
        if($list->leave_type_id < 4){
                 $total_leaves_credit = LeaveAssign::where('employment_type', $list->employment_type)->where('leave_type', $list->leave_type_id)->first();
        // $date_result = (new MasterFunctionController)->GenerateLeavePeriod($list->employment_type,$list->doj);
        // $date_start = $date_result['start_date'];
        // $date_end = $date_result['end_date'];

        $leave_period_data = LeaveRequestDetails::where('request_id',$designation->request_id)->first();
        $date_start =  $leave_period_data->leave_period_start;
        $date_end =  $leave_period_data->leave_period_end;

        // $availed_leave = LeaveRequestDetails::where('status',1)->where('user_id',$list->user_id)->where('leave_type_id',$list->leave_type_id)->whereBetween('date', [$date_start, $date_end])->sum('leave_duration');
        // $pending_leave = LeaveRequestDetails::where('status',0)->where('user_id',$list->user_id)->where('leave_type_id',$list->leave_type_id)->whereBetween('date', [$date_start, $date_end])->sum('leave_duration');
        $availed_leave = LeaveRequestDetails::where('status',1)->where('user_id',$list->user_id)->where('leave_type_id',$list->leave_type_id)->where('leave_period_start', $date_start)->sum('leave_duration');
        $pending_leave = LeaveRequestDetails::where('status',0)->where('user_id',$list->user_id)->where('leave_type_id',$list->leave_type_id)->where('leave_period_end', $date_end)->sum('leave_duration');
        if(($list->leave_type_id == 2 || $list->leave_type_id == 3) && $list->employment_type == 1){

        // if($list->leave_type_id == 2 || $list->leave_type_id == 3){

          $user_leave = DB::table('opening_leave_credits')->where('user_id',$list->user_id)->where('leave_type_id',$list->leave_type_id)->where('leave_period_start',$date_start)->where('leave_period_end',$date_end)->first();
          if( $user_leave){
            $opening = $user_leave->credit;
           }
           else{
            $opening = $total_leaves_credit->total_credit;
           }
          //sick Leave
          if($list->leave_type_id == 2){
            $balance_credit=  ($opening - ( $availed_leave + $pending_leave));
            if($balance_credit > 120){
              $balance_credit = 120;
            }
          }
          //Privilaged
          if($list->leave_type_id == 3){
            $balance_credit=  ($opening - ( $availed_leave + $pending_leave));
            if($balance_credit > 300){
              $balance_credit = 300;
            }
          }

           //casual Leave
      if($list->leave_type_id == 1){
        $balance_credit=  ($opening- ( $availed_leave + $pending_leave));
      }


          $leave_balance = [
            "total_leaves_credit"=>$total_leaves_credit->total_credit,
            "availed_leave"=>$availed_leave,
            "pending_leave"=>$pending_leave,
            "balance_credit"=>$balance_credit,

          ];
        }
//contract & project
        else  if(($list->leave_type_id == 1) && ($list->employment_type == 2 || $list->employment_type == 4)){
          $period_start_date_array = explode('-', $date_start);
          $period_start_month = $period_start_date_array[1];
          $period_start_day   = $period_start_date_array[2];
          $period_start_year  = $period_start_date_array[0];

          $period_end_date_array = explode('-', $date_end);
          $period_end_month = $period_end_date_array[1];
          $period_end_day   = $period_end_date_array[2];
          $period_end_year  = $period_end_date_array[0];

          $days= $period_end_month -  $period_start_month;
          $eligible_start_month_leave=0;
          if($period_end_month == 12 && $period_start_month != 01){
            if( $period_start_day <= 15){
              $eligible_start_month_leave=1;
            }
          }
          if($period_start_month == 01 && $period_end_month != 12){
            if( $period_start_day >= 15){
              $eligible_start_month_leave=1;
            }
          }
          $days= $period_end_month -  $period_start_month;
            if($eligible_start_month_leave ==1){
              $days++;
            }


          $user_leave = DB::table('opening_leave_credits')->where('user_id',$list->user_id)->where('leave_type_id',$list->leave_type_id)->where('leave_period_start',$date_start)->where('leave_period_end',$date_end)->first();
         if( $user_leave){
          $opening = $user_leave->credit;
         }
         else{
          // $opening =$total_leaves_credit->total_credit;
          $opening =$days;
         }

          //Casual Leave
          if($list->leave_type_id == 1){
            $balance_credit=  ($opening- ( $availed_leave + $pending_leave));
          }


          $leave_balance = [
            "total_leaves_credit"=>$days,
            "availed_leave"=>$availed_leave,
            "pending_leave"=>$pending_leave,
            "balance_credit"=>$balance_credit

          ];

        }



        else{
          $leave_balance = [
            "total_leaves_credit"=>$total_leaves_credit->total_credit,
            "availed_leave"=>$availed_leave,
            "pending_leave"=>$pending_leave,
            "balance_credit"=>($total_leaves_credit->total_credit-($availed_leave + $pending_leave)),

          ];
        }


        if($count == 0){
          $leaveDetails =LeaveRequest::find($designation->request_id);

        $mailData = [
          'title' => 'Leave Request Action ',
          'button' => 'View',
          'url' => 'http://localhost:8000/leave/request',
          'body' => Auth::user()->name." procecced your leave request for the period of ".$leaveDetails->from." to ".$leaveDetails->to." . Please login to your account for detailed view.",
        ];

        $user =  User::find($data->user_id);
        // Mail::to($user->email)->send(new LeaveRequestActionMail($mailData));
        }

        }
        //manage LOP
        else{
          $leave_balance ='NA';

        }



              return response()->json(['leave_list'=> $list,"leave_balance"=>$leave_balance]);
      } else {
        return response()->json(['message' => "Internal Server Error"], 500);

      }
    }

    public function destroy($id)
    {
        //
        LeaveDutyAssignment::where('leave_request_id', $id)->delete();
        $movement=LeaveRequest::find($id);
          $movement->delete(); //returns true/false
    }


    public function downloadBulk(Request $request){

      // $from = date('Y-m-d', strtotime(str_replace('-', '/', $request->input('fromDate'))));
      // $to = date('Y-m-d', strtotime(str_replace('-', '/', $request->input('toDate'))));
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

      $list = LeaveRequestDetails::join("employees","employees.user_id","=","leave_request_details.user_id")
      ->leftjoin("designations","designations.id","=","employees.designation")
      ->leftjoin("leaves","leaves.id","=","leave_request_details.leave_type_id")
      ->leftjoin("employees as emp","emp.user_id","=","leave_request_details.action_by")
      ->select('leave_request_details.*','leaves.leave_type','employees.name','employees.email','employees.profile_pic','designations.designation','emp.name as action_by_name')

      ->whereIn('leave_request_details.user_id',$employees)
    ->whereBetween('leave_request_details.date', [$from, $to])
    ->orderBy('leave_request_details.user_id','DESC')->get();



    if($request->input('view_type') == 'html'){
      return response()->json(["list"=>$list]);
    }
    else if($request->input('view_type') == 'pdf'){
      $pdf = PDF::loadView('exports.leave-export-pdf', compact('list'));
      return $pdf->download('leave.pdf');

    }
    else if($request->input('view_type') == 'excel'){

      return Excel::download(new LeaveExport($list), 'leave.xlsx');

    }
      // return response()->download(public_path('storage/AttendanceRepot01-09-2023To30-09-2023Bulk.pdf'));
    }



    public function getLeaveDates($id)
{
    $dates = LeaveRequestDetails::where('request_id', $id)
        ->where('status', 1) // approved
        ->pluck('date');
        //  ->get(['date', 'leave_day_type']);

    return response()->json($dates);
}

public function cancelRequest(Request $request)
{
    $leaveRequestId = $request->leave_request_id;
    $dates = $request->dates ?? [];

    foreach ($dates as $date) {
        LeaveRequestDetails::where('request_id', $leaveRequestId)
            ->whereDate('date', $date)
            ->update(['cancel_status' => 3,'cancel_requested_at'=>now()]); // 3 = Cancel Requested
    }

    return back()->with('success', 'Cancel request submitted.');
}

// public function cancelRequest(Request $request)
// {
//     $leaveRequestId = $request->leave_request_id;
//     $dates = $request->dates ?? [];
//     $partials = $request->partial ?? [];

//      $leave=  LeaveRequest::find($leaveRequestId);



//     foreach ($dates as $date) {
//         $query = LeaveRequestDetails::where('request_id', $leaveRequestId)
//             ->whereDate('date', $date);

//         // Check if partial cancel requested
//         if (isset($partials[$date])) {
//             $partialType = $partials[$date];

//             // Update the original to mark partial cancel
//             $query->update([
//                 // 'leave_day_type' => $partialType,
//                 'cancel_status' => 3, // cancel requested
//                 'cancel_requested_at' => now()
//             ]);
//              $leave_details =  LeaveRequestDetails::where('request_id',$leaveRequestId)->where('date',$date)->first();
//             LeaveRequestDetails::create(['leave_type_id' => $leave->leave_type_id,
//           'request_id' => $leaveRequestId,
//           'leave_day_type' => $partialType,
//           'leave_duration' =>  0.5,
//            'date' => $date,
//            'user_id' => $leave->user_id,'status' => 0,'requested_at' => $date,
//            'leave_period_start' => $leave_details->leave_period_start,
//            'leave_period_end' => $leave_details->leave_period_end
//         ]);


//             // Also adjust duration if needed (0.5 for partial)
//             LeaveRequest::where('id', $leaveRequestId)->decrement('duration', 0.5);
//         } else {
//             // Full cancel for the date
//             $query->update([
//                 'cancel_status' => 3,
//                 'cancel_requested_at' => now()
//             ]);

//             // Decrease duration by 1 full day
//             LeaveRequest::where('id', $leaveRequestId)->decrement('duration', 1);
//         }
//     }

//     return back()->with('success', 'Cancel request submitted successfully.');
// }

public function hrCancelRequests()
{
   $pageConfigs = ['myLayout' => 'horizontal'];
    $cancelRequests = LeaveRequestDetails::where('cancel_status', 3)
        ->with(['leaveRequest.user','leaveRequest.leaveType'])
        ->get()
        ->map(function($detail) {
            $detail->attendance_exists = DB::table('AttendanceLogs')
                ->where('user_id', $detail->leaveRequest->user_id)
                ->whereDate('date', $detail->date)
                ->exists();
            return $detail;
        });

    return view('content.leave.hr-cancel-requests', compact('cancelRequests'),['pageConfigs'=> $pageConfigs]);
}

public function cancelActionByHR(Request $request, $id)
{
    LeaveRequestDetails::where('id', $id)
        ->update(['cancel_status' => $request->status,'hr_cancel_action_taken_at'=>now()]);

    return back()->with('success', 'HR action completed.');
}

public function roCancelRequests()
{
   $pageConfigs = ['myLayout' => 'horizontal'];
    $cancelRequests = LeaveRequestDetails::join("employees","employees.user_id","=","leave_request_details.user_id")
   ->where('employees.reporting_officer',Auth::user()->id)
    ->where('leave_request_details.cancel_status', 4)
    ->select('leave_request_details.*') // HR Approved
        ->with(['leaveRequest.user','leaveRequest.leaveType'])
        ->get();

    return view('content.leave.ro-cancel-requests', compact('cancelRequests'),['pageConfigs'=> $pageConfigs]);
}

public function cancelActionByReportingOfficer(Request $request, $id)
{
    LeaveRequestDetails::where('id', $id)
        ->update(['cancel_status' => $request->status,'status' => $request->status,'ro_cancel_action_taken_at'=>now()]);

    return back()->with('success', 'RO action completed.');
}

}
