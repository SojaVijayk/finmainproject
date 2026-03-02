<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Employee;
use App\Models\Designation;
use App\Models\EmploymentType;
use App\Models\BankAccount;
use App\Models\LeaveRequest;
use App\Models\Movement;
use App\Models\AttendanceLog;
use App\Models\LeaveRequestDetails;
use App\Models\Holiday;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use DateTime;
use DatePeriod;
use DateInterval;


class EmployeeController extends Controller
{
    //
     /**
   * Redirect to user-management view.
   *
   */
  public function index()
  {
    $users = User::all();
    $userCount = $users->count();
    $verified = User::whereNotNull('email_verified_at')->get()->count();
    $active = User::where('active',1)->get()->count();
    $nonactive = User::where('active',0)->get()->count();
    $notVerified = User::whereNull('email_verified_at')->get()->count();
    $usersUnique = $users->unique(['email']);
    $userDuplicates = $users->diff($usersUnique)->count();
    $reporting_officers = User::join("employees","employees.user_id","=","users.id")->where('status',1)->get();

    $designations = Designation::where('status',1)->get();
    $employment_types = EmploymentType::orderBy('id','DESC')->get();
    $roles = Role::where('name','!=',"Admin")->get();
    $usertype_roles = DB::table("usertype_role")->where('status',1)->get();

    if(Auth::user()->user_role ==1){
      return view('content.employee.employee-management', [
        'totalUser' => $userCount,
        'active' => $active,
        'nonactive' => $nonactive,
        'notVerified' => $notVerified,
        'userDuplicates' => $userDuplicates,
        'designations' => $designations,
        'roles' => $roles,
        'usertype_roles' => $usertype_roles,
        'reporting_officers'=>$reporting_officers,
        'employment_types'=>$employment_types

      ]);
    }
    else{
      $pageConfigs = ['myLayout' => 'horizontal'];
      return view('content.employee.employee-management', [
        'totalUser' => $userCount,
        'active' => $active,
        'nonactive' => $nonactive,
        'notVerified' => $notVerified,
        'userDuplicates' => $userDuplicates,
        'designations' => $designations,
        'roles' => $roles,
        'usertype_roles' => $usertype_roles,
        'reporting_officers'=>$reporting_officers,
        'employment_types'=>$employment_types,
        'pageConfigs'=> $pageConfigs

      ]);

    }

  }

  public function employeeList()
  {
    $users = Employee::all();
    $userCount = $users->count();
    $verified = User::whereNotNull('email_verified_at')->get()->count();
    $notVerified = User::whereNull('email_verified_at')->get()->count();
    $usersUnique = $users->unique(['email']);
    $userDuplicates = $users->diff($usersUnique)->count();
    DB::connection()->enableQueryLog();
    $list = User::with("roles")
    ->select('users.*','employees.status','employees.id as employee_id','employees.contract_end_date','employees.employment_type','employment_types.employment_type as employment_type_name','employees.empId','employees.profile_pic','employees.email','employees.mobile','employees.name','designations.designation','usertype_role.usertype_role')
    ->join("employees","employees.user_id","=","users.id")
    ->join("usertype_role","usertype_role.id","=","users.user_role")
    ->join("employment_types","employment_types.id","=","employees.employment_type")
    ->leftjoin("designations","designations.id","=","employees.designation")->orderBy('employees.employment_type','DESC')->orderBy('employees.contract_end_date')->get();
      //  $queries = DB::getQueryLog();
      //   $last_query = end($queries);
      //   dd($queries);
    return response()->json(['data'=> $list]);

  }
  public function employeeView($id){
    $where = ['users.id' => $id];
    $employee = User::where($where)->with("roles")->join("employees","employees.user_id","=","users.id")
    ->join("usertype_role","usertype_role.id","=","users.user_role")
    ->leftjoin("designations","designations.id","=","employees.designation")->first();
    // $employee_projects = Employee::with('lead_projects')->withCount('lead_projects')->with('member_projects')->withCount('member_projects')->where('user_id',$id)->first();

    return view('content.employee.user-employee-view-account',compact('employee'));
  }
  public function profileView(Request $request){
    $id= Auth::user()->id;
    $pageConfigs = ['myLayout' => 'horizontal'];
    $where = ['users.id' => Auth::user()->id];
    $employee = User::where($where)->with("roles")->join("employees","employees.user_id","=","users.id")
    ->join("usertype_role","usertype_role.id","=","users.user_role")
    ->leftjoin("designations","designations.id","=","employees.designation")
    ->select('users.*','employees.status','employees.id as employee_id','employees.empId','employees.profile_pic','employees.email','employees.mobile','employees.name','designations.designation','usertype_role.usertype_role')
    ->first();
    // $employee_projects = Employee::with('lead_projects')->withCount('lead_projects')->with('member_projects')->withCount('member_projects')->where('user_id',$id)->first();
   $employeeRoles= User::where($where)->with("roles")->first();
   $employeeAccounts = BankAccount::where('user_id',$id)->get();
    return view('content.employee.user-employee-view-account',compact('employee','employeeRoles','employeeAccounts'),['pageConfigs'=> $pageConfigs]);
  }






  public function employeeAccountView($id){
    $where = ['id' => $id];
    $user = User::where($where)->with("roles")->join("employees","employees.user_id","=","users.id")
    ->join("usertype_role","usertype_role.id","=","users.user_role")
    ->leftjoin("designations","designations.id","=","employees.designation")->first();
    return view('content.employee.user-employee-view-account');

  }
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */


  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    //
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {

        $this->validate($request, [
          'name' => 'required',
          'email' => 'required|email|unique:users,email',
          'mobile' => 'required|numeric|unique:employees,mobile',
          'empId' => 'required|unique:employees,empId',
          // 'password' => 'required|same:confirm-password',
          'doj' => 'required',
          'roles' => 'required',
          'usertype_role' => 'required',
          'designation' => 'required',
          'employment_type'=>'required',
          'reporting_officer'=>'required'
      ]);

        $input = $request->all();
        $password = Hash::make("Abcd@1234");


        $user = User::create(
          ['name'=>$request->name,
          'email'=>$request->email,
          'username'=>$request->empId,
          'password'=>$password,
          'user_role'=>$request->usertype_role,
          ]);
          // foreach($request->input('roles') as $role){
          //   $user->assignRole($role);
          // }
          $user->assignRole($request->input('roles'));

          $doj = date('Y-m-d', strtotime(str_replace('-', '/', $request->input('doj'))));
          $contract_start_date =NULL;
          if ($request->has('contract_start_date')) {
            $contract_start_date = date('Y-m-d', strtotime(str_replace('-', '/', $request->input('contract_start_date'))));
          }
          $contract_end_date =NULL;
          if ($request->has('contract_end_date')) {
            $contract_end_date = date('Y-m-d', strtotime(str_replace('-', '/', $request->input('contract_end_date'))));
          }


        $employee = Employee::create(
          ['user_id'=>$user->id,
          'name'=>$request->name,
          'mobile'=>$request->mobile,
          'empId'=>$request->empId,
          'doj'=>$doj,
          'email'=>$request->email,
          'designation'=>$request->designation,
          'employment_type'=>$request->employment_type,
          'reporting_officer'=>$request->reporting_officer,
          'contract_start_date'=>$contract_start_date,
          'contract_end_date'=>$contract_end_date

          ,
          ]);


          if($user && $employee ){
            return response()->json('Created');
          } else {
            // user already exist
            return response()->json(['message' => "already exits"], 422);
          }



    // $userID = $request->id;

    // if ($userID) {
    //   // update the value
    //   $users = User::updateOrCreate(
    //     ['id' => $userID],
    //     ['name' => $request->name, 'email' => $request->email]
    //   );

    //   // user updated
    //   return response()->json('Updated');
    // } else {
    //   // create new one if email is unique
    //   $userEmail = User::where('email', $request->email)->first();

    //   if (empty($userEmail)) {
    //     $users = User::updateOrCreate(
    //       ['id' => $userID],
    //       ['name' => $request->name, 'email' => $request->email, 'password' => bcrypt(Str::random(10))]
    //     );

    //     // user created
    //     return response()->json('Created');
    //   } else {
    //     // user already exist
    //     return response()->json(['message' => "already exits"], 422);
    //   }
    // }


  }

  public function update(Request $request, $id)
  {

    $user = User::find($id);
    $employee_details = Employee::where('user_id',$id)->first();
    if($user && $employee_details){
      $this->validate($request, [
        'name' => 'required',
        'email' => 'required|email|unique:users,email,'.$user->id,
        'mobile' => 'required|numeric|unique:employees,mobile,'.$employee_details->id,
        'empId' => 'required|unique:employees,empId,'.$employee_details->id,
        // 'password' => 'required|same:confirm-password',
        'doj' => 'required',
        'roles' => 'required',
        'usertype_role' => 'required',
        'designation' => 'required',
        'employment_type'=>'required',
        'reporting_officer'=>'required'
    ]);

      $input = $request->all();

      $user->update(['name'=>$request->name,
      'email'=>$request->email,
      'username'=>$request->empId,
      'user_role'=>$request->usertype_role,
      ]);
      DB::table('model_has_roles')->where('model_id',$id)->delete();
      $user->assignRole($request->input('roles'));
        $doj = date('Y-m-d', strtotime(str_replace('-', '/', $request->input('doj'))));
        $contract_start_date =NULL;
        if ($request->has('contract_start_date')) {
          $contract_start_date = date('Y-m-d', strtotime(str_replace('-', '/', $request->input('contract_start_date'))));
        }
        $contract_end_date =NULL;
        if ($request->has('contract_end_date')) {
          $contract_end_date = date('Y-m-d', strtotime(str_replace('-', '/', $request->input('contract_end_date'))));
        }

      $employee = Employee::where('user_id',$id)->update(
        [
        'name'=>$request->name,
        'mobile'=>$request->mobile,
        'empId'=>$request->empId,
        'doj'=>$doj,
        'email'=>$request->email,
        'designation'=>$request->designation,
        'employment_type'=>$request->employment_type,
        'reporting_officer'=>$request->reporting_officer,
        'contract_start_date'=>$contract_start_date,
        'contract_end_date'=>$contract_end_date
        ]);


        if($user && $employee ){
          return response()->json('Updated');
        } else {
          // user already exist
          return response()->json(['message' => "already exits"], 422);
        }
    }
    else{
      return response()->json(['message' => "no user found"], 422);
    }





    // $userID = $request->id;

    // if ($userID) {
    //   // update the value
    //   $users = User::updateOrCreate(
    //     ['id' => $userID],
    //     ['name' => $request->name, 'email' => $request->email]
    //   );

    //   // user updated
    //   return response()->json('Updated');
    // } else {
    //   // create new one if email is unique
    //   $userEmail = User::where('email', $request->email)->first();

    //   if (empty($userEmail)) {
    //     $users = User::updateOrCreate(
    //       ['id' => $userID],
    //       ['name' => $request->name, 'email' => $request->email, 'password' => bcrypt(Str::random(10))]
    //     );

    //     // user created
    //     return response()->json('Created');
    //   } else {
    //     // user already exist
    //     return response()->json(['message' => "already exits"], 422);
    //   }
    // }


  }
  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function edit($id)
  {
    $where = ['users.id' => $id];

    // $users = User::where($where)->first();
    $users = User::with("roles")
    ->select('users.*','employees.status','employees.empId','employees.profile_pic','employees.email','employees.mobile','employees.name','employees.employment_type','employees.contract_end_date','employees.contract_start_date','employees.designation as desig_id','employees.doj','employees.reporting_officer','designations.designation','usertype_role.usertype_role')
    ->join("employees","employees.user_id","=","users.id")
    ->join("usertype_role","usertype_role.id","=","users.user_role")
    ->leftjoin("designations","designations.id","=","employees.designation")->where($where)->first();

    return response()->json($users);
  }

  public function editInfo($id)
  {
    $where = ['employees.id' => $id];

    // $users = User::where($where)->first();
    $users = User::with("roles")
    // ->select('users.*','employees.status','employees.empId','employees.profile_pic','employees.email','employees.mobile','employees.name','employees.employment_type','employees.designation as desig_id','employees.doj','employees.reporting_officer','designations.designation','usertype_role.usertype_role')
    ->select('employees.*','users.id','employees.designation as desig_id','designations.designation','usertype_role.usertype_role','account_holder_name','account_number', 'ifsc', 'branch', 'bank_name', 'bank_address',  'bank_accounts.status as bank_status', 'primary')
    ->join("employees","employees.user_id","=","users.id")
    ->leftjoin("bank_accounts",function($join){
      $join->on("bank_accounts.user_id","=","users.id")
      ->where("bank_accounts.primary","=",1)
          ->where("bank_accounts.status","=",1);
  })
    ->join("usertype_role","usertype_role.id","=","users.user_role")
    ->leftjoin("designations","designations.id","=","employees.designation")->where($where)->first();
    // return view('_partials/_modals/modal-edit-user',compact('users'));

    return response()->json($users);
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */


  public function updateInfo(Request $request, $id)
  {


  $employee = Employee::find($id);
  // print_r($employee);
  $employee->prefix = $request->prefix;
  $employee->gender = $request->gender;
  $employee->pincode = $request->pincode;
  $employee->address = $request->address;
  $employee->dob = $request->dob;
  $employee->country = $request->country;
  $employee->state = $request->state;
  $employee->district = $request->district;
  $employee->mobile_sec = $request->mobile_sec;
  $employee->email_sec = $request->email_sec;
  $employee->twitter = $request->twitter;
  $employee->facebook = $request->facebook;
  $employee->linkedin = $request->linkedin;
  $employee->instagram = $request->instagram;
  $employee->whatsapp = $request->whatsapp;
  $employee->languages = $request->languages;
  $employee->pan = $request->pan;

  if($request->has('type') && $request->type == 'HR'){

    $employee->contract_start_date =   date('Y-m-d', strtotime(str_replace('-', '/', $request->contract_start_date)));
    $employee->contract_end_date = date('Y-m-d', strtotime(str_replace('-', '/', $request->contract_end_date)));
    $employee->contract_duration = $request->contract_duration;
    $employee_account = BankAccount::where('user_id',$employee->user_id)->count();
    if($employee_account <= 0){
      BankAccount::where('user_id',$employee->user_id)->update(["status"=>0,"primary"=>0]);
      $employee = BankAccount::create(
        ['account_number'=>$request->account_number,
        'account_holder_name'=>$request->account_holder_name,
        'ifsc'=>$request->ifsc,
        'branch'=>$request->branch,
        'bank_name'=>$request->bank_name,
        'bank_address'=>$request->address,
        'user_id'=>$employee->user_id,
        'status'=>1,
        'primary'=>1,
        "entry_by"=>Auth::user()->id

        ,
        ]);

    }
    else{

      BankAccount::where('user_id',$employee->user_id)->where('primary',1)->update(
        ['account_number'=>$request->account_number,
        'account_holder_name'=>$request->account_holder_name,
        'ifsc'=>$request->ifsc,
        'branch'=>$request->branch,
        'bank_name'=>$request->bank_name,
        'bank_address'=>$request->address,
        'user_id'=>$employee->user_id,
        'status'=>1,
        'primary'=>1,
        "entry_by"=>Auth::user()->i]
      );

    }

  }

  $employee->save();



  if ($employee) {
    return response()->json('Updated');
  } else {
    return response()->json(['message' => "Internal Server Error"], 500);

  }
}

public function AddBankAccount(Request $request,$id){
  BankAccount::where('user_id',$id)->update(["status"=>0,"primary"=>0]);
      $employee = BankAccount::create(
        ['account_number'=>$request->account_number,
        'account_holder_name'=>$request->account_holder_name,
        'ifsc'=>$request->ifsc,
        'branch'=>$request->branch,
        'bank_name'=>$request->bank_name,
        'address'=>$request->address,
        'user_id'=>$id,
        'status'=>1,
        'primary'=>1,
        "entry_by"=>Auth::user()->id

        ,
        ]);
        if ($employee) {
          return response()->json('Updated');
        } else {
          return response()->json(['message' => "Internal Server Error"], 500);

        }
}
public function updateBankAccount(Request $request){
  BankAccount::where('user_id',$request->user_id)->update(["status"=>0,"primary"=>0]);
  $employee = BankAccount::where('user_id',$request->user_id)->where('id',$request->account_id)->update(["status"=>1,"primary"=>1]);

        if ($employee) {
          return response()->json('Updated');
        } else {
          return response()->json(['message' => "Internal Server Error"], 500);

        }
}

public function uploadImage(Request $request){

  $request->validate([
    'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
]);


        // $request->image->storeAs('public/img/avatars', $fileName);
        $destinationPath = 'assets/img/avatars';
        // $myimage = $request->image->getClientOriginalName();
        $fileName = time() . '.' . $request->image->extension();
        $request->image->move(public_path($destinationPath), $fileName);
        $id= Auth::user()->id;

        $employee_details = Employee::where('user_id',$id)->first();

        if(file_exists(public_path('assets/img/avatars/'.$employee_details->profile_pic))){
          if($employee_details->profile_pic != 'avatar.png'){
            $file =public_path('assets/img/avatars/'.$employee_details->profile_pic);
            $img= unlink($file);
          }

          $employee = Employee::where('user_id',$id)->update(["profile_pic"=> $fileName]);

              if ($employee) {
                return response()->json('Updated');
              } else {
                return response()->json(['message' => "Internal Server Error"], 500);

              }


          }else{
            return response()->json(['message' => "Internal Server Error"], 500);
          }



}



  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $users = User::where('id', $id)->delete();
  }


  public function resetPassword(Request $request){
    $request->validate([
      'password' => 'required|string|min:6|confirmed',
      'password_confirmation' => 'required'
  ]);
  $id= Auth::user()->id;
  $user = User::where('id',$id)
  ->update(['password' => Hash::make($request->password)]);
  $pageConfigs = ['myLayout' => 'blank'];
  // return view('content.authentications.auth-forgot-password-cover', ['pageConfigs' => $pageConfigs]);
  return response()->json('updated');

  }
  public function fetchEvents(Request $request){
    $id= Auth::user()->id;
    $employee_details=Employee::where('user_id',$id)->first();
    $leave_details = LeaveRequestDetails::where('leave_request_details.user_id',$id)
    ->leftjoin("leaves","leaves.id","=","leave_request_details.leave_type_id")
    ->leftjoin("employees as emp","emp.user_id","=","leave_request_details.action_by")
    ->select('leave_request_details.*','leaves.leave_type','emp.name as action_by_name')
    ->get();

    $movement_details = Movement::where('movements.user_id',$id)
    ->leftjoin("employees as emp","emp.user_id","=","movements.action_by")
    ->select('movements.*','emp.name as action_by_name')
    ->get();


    $attendance = AttendanceLog::select('AttendanceLogs.*','employees.empId','employees.profile_pic','employees.email','employees.mobile','employees.name','designations.designation')
  ->join("employees","employees.user_id","=","AttendanceLogs.user_id")
  ->leftjoin("designations","designations.id","=","employees.designation")
  ->where('AttendanceLogs.user_id',$id)
  ->orderBy('AttendanceLogs.user_id','DESC')->get();

  $holidays = Holiday::get();


    $j=0;
    $leaves=[];


    foreach( $leave_details as $leave){
      $date_from = strtotime($leave->date);
     $from= date('Y-m-d H:i:s', $date_from);

      $prop=["calendar"=>($leave->status == 1 ? "Approved" :  ($leave->status == 2 ? "Rejected" :  "Requested"))];
      $obj = [
        'id'=>$j,
        'url'=>'',
        'title'=>$leave->leave_type.' '.($leave->status == 1 ? 'Approved (Action By :'.$leave->action_by_name.')' : ($leave->status == 2 ? 'Rejected (Action By :'.$leave->action_by_name.')' : "Pending")),
        'start'=>$from,
        'end'=>$from,
        'allDay'=>($leave->leave_day_type == 1 ? true : false),
        'extendedProps'=>$prop

      ];
      array_push($leaves,$obj);
      $j++;


    }

    foreach( $movement_details as $movement){
      $date_from = strtotime($movement->start_date.' '.$movement->start_time);
      $from= date('Y-m-d H:i:s', $date_from);

      $date_to = strtotime($movement->end_date.' '.$movement->end_time);
      $to= date('Y-m-d H:i:s', $date_to);

      $prop=["calendar"=>($movement->status == 1 ? "Approved" :  ($movement->status == 2 ? "Rejected" :  "Requested"))];
      $obj = [
        'id'=>$j,
        'url'=>'',
        'title'=>$movement->title.' '.($movement->status == 1 ? 'Approved (Action By :'.$movement->action_by_name.')' : ($movement->status == 2 ? 'Rejected (Action By :'.$movement->action_by_name.')' : "Pending")),
        'start'=>$from,
        'end'=>$to,
        'allDay'=>false,
        'extendedProps'=>$prop

      ];
      array_push($leaves,$obj);
      $j++;


    }

    foreach( $attendance as $item){
      $date_from = strtotime($item->date);
     $from= date('Y-m-d H:i:s', $date_from);
      if(($item->InTime == null && $item->OutTime == null) || ($item->InTime == '' && $item->OutTime == '')){
        $prop=["calendar"=>"Absent"];
      }
      else{
        $prop=["calendar"=>"Present"];
      }

      $obj = [
        'id'=>$j,
        'url'=>'',
        // 'title'=>'IN '.($item->in_time != null && $item->in_time != '' ? $item->in_time : 'No Record' ).' OUT '.$item->out_time,
        'title'=>(($item->InTime != null && $item->InTime != '') && ($item->OutTime != null && $item->OutTime != '')  ? 'Present (IN '.$item->InTime.' OUT '.($item->OutTime != $item->InTime ? $item->OutTime : 'No Records').')' : (($item->InTime != null && $item->InTime != '') && ($item->OutTime == null || $item->OutTime == '') ? 'IN '.$item->InTime: 'Absent') ),
        'start'=>$from,
        'end'=>$from,
        'allDay'=>true,
        'extendedProps'=>$prop

      ];
      array_push($leaves,$obj);
      $j++;

    }


    foreach( $holidays as $holiday){
      $date_from = strtotime($holiday->date);
      $from= date('Y-m-d H:i:s', $date_from);

      $date_to = strtotime($holiday->date);
      $to= date('Y-m-d H:i:s', $date_to);

      $prop=["calendar"=>"Holiday"];
      $obj = [
        'id'=>$j,
        'url'=>'',
        'title'=>$holiday->description,
        'start'=>$from,
        'end'=>$to,
        'allDay'=>true,
        'extendedProps'=>$prop

      ];
      array_push($leaves,$obj);
      $j++;

    }




    return response()->json(['events'=> $leaves]);
     }


}