<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Attendance;

use App\Models\MissedPunch;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

use DB;
use Carbon\Carbon;


use Illuminate\Support\Facades\Mail;
use App\Exports\MisspunchExport;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class MissedPunchController extends Controller
{
    //

    public function index()
    {
      $pageConfigs = ['myLayout' => 'horizontal'];

      return view('content.attendance.misspunch',['pageConfigs'=> $pageConfigs]);
    }

    public function misspunchList()
    {

      $id= Auth::user()->id;

      $list = MissedPunch::join("employees","employees.user_id","=","missed_punches.user_id")
      ->leftjoin("designations","designations.id","=","employees.designation")
      ->leftjoin("employees as emp","emp.user_id","=","missed_punches.action_by")
      ->select('missed_punches.*',DB::raw("DATE_FORMAT(missed_punches.date, '%d-%b-%Y') as formatted_date"),'employees.name','employees.email','employees.profile_pic','designations.designation','emp.name as action_by_name',DB::raw("DATE_FORMAT(missed_punches.requested_at, '%d-%b-%Y %H:%i') as formatted_requested_at"),DB::raw("DATE_FORMAT(missed_punches.action_at, '%d-%b-%Y %H:%i') as formatted_action_at"))->where('missed_punches.user_id',$id)
      ->orderBy('missed_punches.status')
      ->orderBy('missed_punches.date', 'DESC')->get();
        //  $queries = DB::getQueryLog();
        //   $last_query = end($queries);
        //   dd($queries);
      return response()->json(['data'=> $list]);

    }

    public function store(Request $request)
    {
        //
        $this->validate($request, [
          'type' => 'required|',
          'date' => 'required|',

          // 'description' => 'required|',



      ]);
      $id= Auth::user()->id;
      $date= date('Y-m-d H:i:s');
      // $attendance_date = date('Y-m-d', strtotime(str_replace('-', '/', $request->input('date'))));
      $var = $request->input('date');
      $datef = str_replace('/', '-', $var);
      $attendance_date=  date('Y-m-d', strtotime($datef));


      if($request->input('checkinTime') == '' || $request->input('checkinTime') ==NULL){
        $checkin= NULL;
      }
      else{
        $checkin = $request->input('checkinTime');
      }
      if($request->input('checkoutTime') == '' || $request->input('checkoutTime') ==NULL){
        $checkout= NULL;
      }
      else{
        $checkout = $request->input('checkoutTime');
      }
      $permission = MissedPunch::create(['type' => $request->input('type')
      ,'date' => $attendance_date,'checkinTime' => $checkin,'checkoutTime' => $checkout
      ,'description' => $request->input('description'),'user_id' => $id,'status' => 0,'requested_at' => $date
    ]);

      if ($permission) {
        $mailData = [
          'title' => 'Miss Punch Request',
          'button' => 'Take Action',
          'url' => 'http://localhost:8000/misspunch/approve-list',
          'body' => Auth::user()->name." created a new movement request for the period of ".$request->input('start_date')." - ".$request->input('start_time')." to ".$request->input('end_date')." - ".$request->input('end_time').".  Please login to your account for take action.",
        ];
        $reporting = Employee::where('employees.user_id',Auth::user()->id)
        ->leftjoin("employees as emp","emp.user_id","=","employees.reporting_officer")
        ->select('emp.email')->first();
        // dd($reporting);
        // Mail::to($reporting->email)->send(new LeaveRequestMail($mailData));

        return response()->json('created');
      } else {
        return response()->json(['message' => "Internal Server Error"], 500);

      }
    }

    public function edit($id)
    {
        //
        $designation = MissedPunch::find($id);

          return response()->json(['designation'=> $designation]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,  $id)
    {
        //
        $this->validate($request, [
          'type' => 'required|',
          'date' => 'required|',

          // 'description' => 'required|',

      ]);

      $date= date('Y-m-d H:i:s');
      // $attendance_date = date('Y-m-d', strtotime(str_replace('-', '/', $request->input('date'))));
      $var = $request->input('date');
      $datef = str_replace('/', '-', $var);
      $attendance_date=  date('Y-m-d', strtotime($datef));

      if($request->input('checkinTime') == '' || $request->input('checkinTime') ==NULL){
        $checkin= NULL;
      }
      else{
        $checkin = $request->input('checkinTime');
      }
      if($request->input('checkoutTime') == '' || $request->input('checkoutTime') ==NULL){
        $checkout= NULL;
      }
      else{
        $checkout = $request->input('checkinTime');
      }
      $designation = MissedPunch::find($id);



      $designation->type = $request->input('type');
      $designation->date = $attendance_date;
      $designation->checkinTime = $checkin;
      $designation->checkoutTime = $checkout;
      $designation->description = $request->input('description');
      $designation->requested_at = $date;
      $designation->save();



      if ($designation) {
        return response()->json('Updated');
      } else {
        return response()->json(['message' => "Internal Server Error"], 500);

      }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
        $movement=MissedPunch::find($id);
          $movement->delete(); //returns true/false
    }


    public function approveList()
    {



      $id= Auth::user()->id;

      $list = MissedPunch::join("employees","employees.user_id","=","missed_punches.user_id")
      ->select('missed_punchess.*')->where('employees.reporting_officer',$id);

      $totalCount = $list->count();
      $approved = $list->where('missed_punches.status',1)->count();
      $pending = MissedPunch::join("employees","employees.user_id","=","missed_punches.user_id")
      ->select('missed_punchess.*')->where('employees.reporting_officer',$id)->where('missed_punches.status',0)->count();
      $rejected =MissedPunch::join("employees","employees.user_id","=","missed_punches.user_id")
      ->select('missed_punchess.*')->where('employees.reporting_officer',$id)->where('missed_punches.status',2)->count();


      $pageConfigs = ['myLayout' => 'horizontal'];
      return view('content.attendance.misspunch-approve-list',compact('totalCount','approved','pending','rejected'),['pageConfigs'=> $pageConfigs]);
    }
    public function requestList()
    {

      $id= Auth::user()->id;

      $list = MissedPunch::join("employees","employees.user_id","=","missed_punches.user_id")
      ->leftjoin("designations","designations.id","=","employees.designation")
      ->leftjoin("employees as emp","emp.user_id","=","missed_punches.action_by")
      ->select('missed_punches.*',DB::raw("DATE_FORMAT(missed_punches.date, '%d-%b-%Y') as formatted_date"),DB::raw("DATE_FORMAT(missed_punches.requested_at, '%d-%b-%Y %H:%i') as formatted_requested_at"),DB::raw("DATE_FORMAT(missed_punches.action_at, '%d-%b-%Y %H:%i') as formatted_action_at"),'employees.name','employees.email','employees.profile_pic','designations.designation','emp.name as action_by_name',)->where('employees.reporting_officer',$id)
      ->orderBy('missed_punches.status')
      ->orderBy('missed_punches.date', 'DESC')->get();
        //  $queries = DB::getQueryLog();
        //   $last_query = end($queries);
        //   dd($queries);
      return response()->json(['data'=> $list]);

    }

    public function action(Request $request,  $id)
    {
        //
        $this->validate($request, [
          'status' => 'required',
          'remark' => 'required',

      ]);

      $date= date('Y-m-d H:i:s');

      $designation = MissedPunch::find($id);
      $designation->status = $request->input('status');
      $designation->remark = $request->input('remark');
      $designation->action_at = $date;
      $designation->action_by = Auth::user()->id;
      $designation->save();



      if ($designation) {

        $mailData = [
          'title' => 'Movement Request Action ',
          'button' => 'View',
          'url' => 'http://localhost:8000/movement',
          'body' => Auth::user()->name." take an action against your Movement request for the period of ".$designation->start_date." - ".$designation->start_time." to ".$designation->end_date." -".$designation->end_time.". Please login to your account for detailed view.",
        ];

        $user =  User::find($designation->user_id);
        // Mail::to($user->email)->send(new LeaveRequestActionMail($mailData));


        return response()->json('Updated');
      } else {
        return response()->json(['message' => "Internal Server Error"], 500);

      }
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

      $list = MissedPunch::join("employees","employees.user_id","=","missed_punches.user_id")
      ->leftjoin("designations","designations.id","=","employees.designation")
      ->leftjoin("employees as emp","emp.user_id","=","missed_punches.action_by")
      ->select('missed_punches.*','employees.name','employees.email','employees.profile_pic','designations.designation','emp.name as action_by_name')
      // ->where('movements.user_id',$id)
      // ->orderBy('movements.status')->get();
      ->whereIn('missed_punches.user_id',$employees)
    ->whereBetween('missed_punches.date', [$from, $to])
    ->orderBy('missed_punches.user_id','DESC')->get();



    if($request->input('view_type') == 'html'){
      return response()->json(["list"=>$list]);
    }
    else if($request->input('view_type') == 'pdf'){
      $pdf = PDF::loadView('exports.misspunch-export-pdf', compact('list'));
      return $pdf->download('misspunch.pdf');

    }
    else if($request->input('view_type') == 'excel'){

      return Excel::download(new MisspunchExport($list), 'misspunch.xlsx');

    }
      // return response()->download(public_path('storage/AttendanceRepot01-09-2023To30-09-2023Bulk.pdf'));
    }
}
