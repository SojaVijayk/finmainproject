<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ProjectEmployee;
use App\Models\ProjectDesignation;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class ProjectDocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($id,Request $request)
  {
    $request->session()->forget('project');
    $pageConfigs = ['myLayout' => 'horizontal'];
    $employees = ProjectEmployee::where('project_id',$id)->get();
    $employeeCount = $employees->count();
    $contract = ProjectEmployee::where('user_type',1)->get()->count();
    $consultant = ProjectEmployee::where('user_type',3)->get()->count();
    $dw = ProjectEmployee::where('user_type',2)->get()->count();
    $unique = $employees->unique(['email']);


    $designations = ProjectDesignation::where('status',1)->get();
    $roles = Role::where('name','!=',"Admin")->get();
    $user_types = DB::table("project_user_types")->where('status',1)->get();
    $project = Project::find($id);

    $request->session()->put('project', $id);
    return view('content.projects.project-doc-management', [
      'totalEmployee' => $employeeCount,
      'contract' => $contract,
      'dw' => $dw,
      'contract' => $contract,
      'consultant'=>$consultant,
      'designations' => $designations,
      'user_types' => $user_types,
      'project_details'=>$project

    ],['pageConfigs'=> $pageConfigs]);
  }

  public function employeeList(Request $request)
  {

    // DB::connection()->enableQueryLog();
    $id = $request->session()->get('project');
    $list = ProjectEmployee::join("project_user_types","project_user_types.id","=","project_employees.user_type")
    ->leftjoin("project_designations","project_designations.id","=","project_employees.designation_id")
    ->leftjoin("gender","gender.id","=","project_employees.gender_id")->select('project_employees.id','project_employees.prefix','project_employees.name','project_employees.last_name',
  'project_employees.profile_pic','project_employees.mobile_pri','project_employees.email_pri','project_employees.status','project_employees.empId',
'project_user_types.user_type','gender.gender_name','project_designations.designation')->where('project_id',$id)->get();
      //  $queries = DB::getQueryLog();
      //   $last_query = end($queries);
      //   dd($queries);
      $project = Project::find($id);

    return response()->json(['data'=> $list]);

  }
  // public function employeeView($id){
  //   $where = ['users.id' => $id];
  //   $employee = User::where($where)->with("roles")->join("employees","employees.user_id","=","users.id")
  //   ->join("usertype_role","usertype_role.id","=","users.user_role")
  //   ->leftjoin("designations","designations.id","=","employees.designation")->first();
  //   $employee_projects = Employee::with('lead_projects')->withCount('lead_projects')->with('member_projects')->withCount('member_projects')->where('user_id',$id)->first();

  //   return view('content.employee.user-employee-view-account',compact('employee','employee_projects'));
  // }
  // public function profileView(Request $request){
  //   $id= Auth::user()->id;
  //   $pageConfigs = ['myLayout' => 'horizontal'];
  //   $where = ['users.id' => Auth::user()->id];
  //   $employee = User::where($where)->with("roles")->join("employees","employees.user_id","=","users.id")
  //   ->join("usertype_role","usertype_role.id","=","users.user_role")
  //   ->leftjoin("designations","designations.id","=","employees.designation")->first();
  //   $employee_projects = Employee::with('lead_projects')->withCount('lead_projects')->with('member_projects')->withCount('member_projects')->where('user_id',$id)->first();

  //   return view('content.employee.user-employee-view-account',compact('employee','employee_projects'),['pageConfigs'=> $pageConfigs]);
  // }






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
          'empId' => 'required|numeric|unique:employees,empId',
          // 'password' => 'required|same:confirm-password',
          'roles' => 'required',
          'usertype_role' => 'required',
          'designation' => 'required',
      ]);

        $input = $request->all();
        $password = Hash::make("Abcd@1234");


        $user = User::create(
          ['name'=>$request->name,
          'email'=>$request->email,
          'username'=>$request->email,
          'password'=>$password,
          'user_role'=>$request->usertype_role,
          ]);
        $user->assignRole($request->input('roles'));

        $employee = ProjectEmployee::create(
          ['user_id'=>$user->id,
          'name'=>$request->name,
          'mobile'=>$request->mobile,
          'email'=>$request->email,
          'designation'=>$request->designation
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
    $where = ['id' => $id];

    $users = User::where($where)->first();

    return response()->json($users);
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id)
  {
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
}