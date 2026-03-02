<?php


namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use App\Models\Leave;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestDetails;
use App\Models\LeaveAssign;
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

class LeaveRequestController extends Controller
{
    //
    public function index()
  {
    $pageConfigs = ['myLayout' => 'horizontal'];

    $id= Auth::user()->id;
    $employee_details=Employee::where('user_id',$id)->first();

    $leave_types = Leave::orderBy('id','DESC')->get();




     $leaves_total_credit = LeaveAssign::select('leave_assigns.id','leave_assigns.leave_type as leave_type_id','leaves.leave_type','total_credit','employment_types.employment_type','leave_assigns.status','leave_assigns.created_at')
     ->leftjoin("leaves","leaves.id","=","leave_assigns.leave_type")
     ->join("employment_types","employment_types.id","=","leave_assigns.employment_type")
     ->where('leave_assigns.employment_type',$employee_details->employment_type)
     ->orderBy('leave_assigns.leave_type','ASC')->get();
     $leaves_total_credit_details=[];
     foreach( $leaves_total_credit as $leave_detail){
    //    $from = date('2023-04-01');
    //    $to = date('2024-03-31');
    //    $subscriptionDate = $employee_details->doj;
    // $dateArray = (explode("-", $subscriptionDate));
    // if (date("Y") ==  $dateArray[0]) {
    //   // Convert the subscription date to a Carbon instance
    //   $subscriptionDateTime = Carbon::parse($subscriptionDate);

    //   // Calculate the expiration date by adding one year to the subscription date
    //   $expirationDateTime = $subscriptionDateTime->addYear();

    //   // Format the expiration date as YYYY-MM-DD
    //   $date_end = $expirationDateTime->format("Y-m-d");
    //   $date_start = $dateArray[2] . '-' . $dateArray[1] . '-' . date("Y");
    // } else {
    //   $doj = $employee_details->doj;
    //   $dateArray = (explode("-", $doj));
    //   $subscriptionDate = date("Y") . '-' . $dateArray[1] . '-' . $dateArray[2];
    //   // Convert the subscription date to a Carbon instance
    //   $subscriptionDateTime = Carbon::parse($subscriptionDate);

    //   // Calculate the expiration date by adding one year to the subscription date
    //   $expirationDateTime = $subscriptionDateTime->addYear();

    //   // Format the expiration date as YYYY-MM-DD
    //   $date_end = $expirationDateTime->format("Y-m-d");
    //   // $date_start = $dateArray[2] . '-' . $dateArray[1] . '-' . date("Y");
    //   $date_start = date("Y") . '-' .$dateArray[1] . '-' . $dateArray[2];
    // }

    $date_result = (new MasterFunctionController)->GenerateLeavePeriod($employee_details->employment_type,$employee_details->doj);
    $date_start = $date_result['start_date'];
    $date_end = $date_result['end_date'];
    // echo $employee_details->employment_type;
    // print_r($date_result);exit();

       $availed_leave = LeaveRequestDetails::where('status',1)->where('user_id',$employee_details->user_id)->where('leave_type_id',$leave_detail->leave_type_id)->whereBetween('date', [$date_start, $date_end])->sum('leave_duration');
       $pending_leave = LeaveRequestDetails::where('status',0)->where('user_id',$employee_details->user_id)->where('leave_type_id',$leave_detail->leave_type_id)->whereBetween('date', [$date_start, $date_end])->sum('leave_duration');
      if(($leave_detail->leave_type_id == 2 || $leave_detail->leave_type_id == 3) && $employee_details->employment_type == 1){

        $user_leave = DB::table('opening_leave_credits')->where('user_id',Auth::user()->id)->where('leave_type_id',$leave_detail->leave_type_id)->where('leave_period_start',$date_start)->where('leave_period_end',$date_end)->first();
       if( $user_leave){
        $opening = $user_leave->credit;
       }
       else{
        $opening = $leave_detail->total_credit;
       }

        //sick Leave
        if($leave_detail->leave_type_id == 2){
          $balance_credit=  ($opening- $availed_leave);
          if($balance_credit > 120){
            $balance_credit = 120;
          }
        }
        //Privilaged
        if($leave_detail->leave_type_id == 3){
          $balance_credit=  ($opening - $availed_leave);
          if($balance_credit > 300){
            $balance_credit = 300;
          }
        }


        $leave_balance = [
          "leave_type"=>$leave_detail->leave_type,
          "leave_type_id"=>$leave_detail->leave_type_id,
          "total_leaves_credit"=>$leave_detail->total_credit,
          "availed_leave"=>$availed_leave,
          "pending_leave"=>$pending_leave,
          "balance_credit"=>($balance_credit-($availed_leave + $pending_leave))

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



    return view('content.leave.leave-request',compact('leave_types','leaves_total_credit_details','date_start','date_end','leaves_total_credit'),['pageConfigs'=> $pageConfigs]);
  }

  public function leaveList()
  {

    $id= Auth::user()->id;



    $list = LeaveRequest::with('leaveRequestDetails')->join("employees","employees.user_id","=","leave_requests.user_id")
    ->leftjoin("designations","designations.id","=","employees.designation")
    ->leftjoin("leaves","leaves.id","=","leave_requests.leave_type_id")
    ->leftjoin("employees as emp","emp.user_id","=","leave_requests.action_by")
    ->select('leave_requests.*','leaves.leave_type','employees.name','employees.email','employees.profile_pic','designations.designation','emp.name as action_by_name')->where('leave_requests.user_id',$id)
    ->orderBy('leave_requests.status')->get();
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
        'leave_period_end' => 'required'


    ]);
    $id= Auth::user()->id;
    $date= date('Y-m-d H:i:s');
    $from = date('Y-m-d', strtotime(str_replace('-', '/', $request->input('from'))));
    $to = date('Y-m-d', strtotime(str_replace('-', '/', $request->input('to'))));
    $date_list = json_encode($request->input('date_list'));
    // $from = date('2023-04-01');
    // $to = date('2024-03-31');
    $employee = Employee::where('employees.user_id',Auth::user()->id)->first();
    $total_leaves_credit = LeaveAssign::where('employment_type', $employee->employment_type)->where('leave_type', $request->input('leave_type_id'))->first();

    $date_result = (new MasterFunctionController)->GenerateLeavePeriod($employee->employment_type,$employee->doj);
    $date_start = $date_result['start_date'];
    $date_end = $date_result['end_date'];

    $availed_leave = LeaveRequestDetails::where('status',1)->where('user_id',$employee->user_id)->where('leave_type_id',$request->input('leave_type_id'))->whereBetween('date', [$date_start, $date_end])->sum('leave_duration');
    $pending_leave = LeaveRequestDetails::where('status',0)->where('user_id',$employee->user_id)->where('leave_type_id',$request->input('leave_type_id'))->whereBetween('date', [$date_start, $date_end])->sum('leave_duration');
    $balance = $total_leaves_credit->total_credit - ( $availed_leave + $pending_leave);
    if($request->input('duration') <=  $balance){

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
           'leave_period_start' => $request->input('leave_period_start'),
           'leave_period_end' => $request->input('leave_period_end')
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


        return response()->json( ["status"=>true, "data"=>$permission]);
      } else {
        return response()->json(["status"=>false,'message' => "Internal Server Error"], 500);

      }
    }
    else{
      return response()->json(["status"=>false,'message' => "You have no credit and cannot request ".$request->input('duration')." Leave" ], 200);
    }

  }



  public function approveList()
    {


      $id= Auth::user()->id;

      $list = LeaveRequest::join("employees","employees.user_id","=","leave_requests.user_id")
      ->select('leave_requests.*')->where('employees.reporting_officer',$id);

      $totalCount = $list->count();
      $action_started = $list->where('leave_requests.status',1)->count();
      $pending = $list->where('leave_requests.status',0)->count();
      $completed =$list->where('leave_requests.status',2)->count();



      $pageConfigs = ['myLayout' => 'horizontal'];
      return view('content.leave.leave-approve-list',compact('totalCount','action_started','pending','completed'),['pageConfigs'=> $pageConfigs]);
    }
    public function requestList()
    {


      $id= Auth::user()->id;

      $list = LeaveRequest::with('leaveRequestDetails')->join("employees","employees.user_id","=","leave_requests.user_id")
      ->leftjoin("designations","designations.id","=","employees.designation")
      ->leftjoin("leaves","leaves.id","=","leave_requests.leave_type_id")
      ->leftjoin("employees as emp","emp.user_id","=","leave_requests.action_by")
      ->select('leave_requests.*','leaves.leave_type','employees.name','employees.email','employees.profile_pic','designations.designation','emp.name as action_by_name')->where('employees.reporting_officer',$id)
      ->orderBy('leave_requests.status')->get();
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
    if($list->leave_type_id == 2 || $list->leave_type_id == 3){

      $user_leave = DB::table('opening_leave_credits')->where('user_id',Auth::user()->id)->where('leave_type_id',$list->leave_type_id)->where('leave_period_start',$date_start)->where('leave_period_end',$date_end)->first();
      if( $user_leave){
        $opening = $user_leave->credit;
       }
       else{
        $opening = $total_leaves_credit->total_credit;
       }
      //sick Leave
      if($list->leave_type_id == 2){
        $balance_credit=  ($opening - $availed_leave);
        if($balance_credit > 120){
          $balance_credit = 120;
        }
      }
      //Privilaged
      if($list->leave_type_id == 3){
        $balance_credit=  ($opening - $availed_leave);
        if($balance_credit > 300){
          $balance_credit = 300;
        }
      }


      $leave_balance = [
        "total_leaves_credit"=>$total_leaves_credit->total_credit,
        "availed_leave"=>$availed_leave,
        "pending_leave"=>$pending_leave,
        "balance_credit"=>($balance_credit-($availed_leave + $pending_leave)),

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

      if ($designation) {
        $list = LeaveRequest::with('leaveRequestDetails')->join("employees","employees.user_id","=","leave_requests.user_id")
        ->leftjoin("designations","designations.id","=","employees.designation")
        ->leftjoin("leaves","leaves.id","=","leave_requests.leave_type_id")
        ->select('leave_requests.*','leaves.leave_type','employees.employment_type','employees.name','employees.email','employees.profile_pic','designations.designation')->where('leave_requests.id',$designation->request_id)->first();

        $total_leaves_credit = LeaveAssign::where('employment_type', $list->employment_type)->where('leave_type', $list->leave_type_id)->first();
        // $date_result = (new MasterFunctionController)->GenerateLeavePeriod($list->employment_type,$list->doj);
        // $date_start = $date_result['start_date'];
        // $date_end = $date_result['end_date'];

        // $availed_leave = LeaveRequestDetails::where('status',1)->where('user_id',$list->user_id)->where('leave_type_id',$list->leave_type_id)->whereBetween('date', [$date_start, $date_end])->sum('leave_duration');
        // $pending_leave = LeaveRequestDetails::where('status',0)->where('user_id',$list->user_id)->where('leave_type_id',$list->leave_type_id)->whereBetween('date', [$date_start, $date_end])->sum('leave_duration');
        $availed_leave = LeaveRequestDetails::where('status',1)->where('user_id',$list->user_id)->where('leave_type_id',$list->leave_type_id)->where('leave_period_start', $date_start)->sum('leave_duration');
        $pending_leave = LeaveRequestDetails::where('status',0)->where('user_id',$list->user_id)->where('leave_type_id',$list->leave_type_id)->where('leave_period_end', $date_end)->sum('leave_duration');
        if($list->leave_type_id == 2 || $list->leave_type_id == 3){

          $user_leave = DB::table('opening_leave_credits')->where('user_id',Auth::user()->id)->where('leave_type_id',$list->leave_type_id)->where('leave_period_start',$date_start)->where('leave_period_end',$date_end)->first();
          if( $user_leave){
            $opening = $user_leave->credit;
           }
           else{
            $opening = $total_leaves_credit->total_credit;
           }
          //sick Leave
          if($list->leave_type_id == 2){
            $balance_credit=  ($opening - $availed_leave);
            if($balance_credit > 120){
              $balance_credit = 120;
            }
          }
          //Privilaged
          if($list->leave_type_id == 3){
            $balance_credit=  ($opening - $availed_leave);
            if($balance_credit > 300){
              $balance_credit = 300;
            }
          }


          $leave_balance = [
            "total_leaves_credit"=>$total_leaves_credit->total_credit,
            "availed_leave"=>$availed_leave,
            "pending_leave"=>$pending_leave,
            "balance_credit"=>($balance_credit-($availed_leave + $pending_leave)),

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


              return response()->json(['leave_list'=> $list,"leave_balance"=>$leave_balance]);
      } else {
        return response()->json(['message' => "Internal Server Error"], 500);

      }
    }

    public function destroy($id)
    {
        //
        $movement=LeaveRequest::find($id);
          $movement->delete(); //returns true/false
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
      // $pdf = PDF::loadView('exports.leave-export-pdf', compact('list'));
      // return $pdf->download('leave.pdf');

    }
    else if($request->input('view_type') == 'excel'){

      return Excel::download(new LeaveExport($list), 'leave.xlsx');

    }
      // return response()->download(public_path('storage/AttendanceRepot01-09-2023To30-09-2023Bulk.pdf'));
    }

}
