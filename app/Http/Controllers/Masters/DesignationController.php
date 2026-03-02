<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Designation;
use App\Models\EmploymentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DesignationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    function __construct()
    {

        // $this->middleware('role:Admin');
    }
    public function index()
    {
        //
        $designation = Designation::orderBy('id','DESC')->get();
        $employment_types = EmploymentType::orderBy('id','DESC')->get();

        $pageConfigs = ['myLayout' => 'horizontal'];
        if(Auth::user()->user_role ==1){
          return view('content.masters.designation',compact('designation','employment_types'));

        }
        else{
          return view('content.masters.designation',compact('designation','employment_types'),['pageConfigs'=> $pageConfigs]);


        }
    }
    public function getAllDesignations(Request $request)
    {
        // $permissions = Permission::join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
        // ->orderBy('id','DESC')->paginate(5);
        $designations = Designation::select('designations.id','designation','designations.status','designations.created_at','employment_types.employment_type')
        ->leftjoin("employment_types","employment_types.id","=","designations.employment_type")
        ->orderBy('designations.id','DESC')->get();
        return response()->json(['data'=> $designations]);

        // return view('content.apps.app-access-permission'.compact('permissions'))
        //     ->with('i', ($request->input('page', 1) - 1) * 5);


    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $this->validate($request, [
          'designation' => 'required',
          'employment_type' => 'required|',
      ]);

      $permission = Designation::create(['designation' => $request->input('designation'),'employment_type' => $request->input('employment_type')]);

      if ($permission) {
        return response()->json('created');
      } else {
        return response()->json(['message' => "Internal Server Error"], 500);

      }
    }

    /**
     * Display the specified resource.
     */
    public function show(Designation $designation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //
        $designation = Designation::find($id);

          return response()->json(['designation'=> $designation]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,  $id)
    {
        //
        $this->validate($request, [
          'designation' => 'required',
          'employment_type' => 'required',

      ]);

      $designation = Designation::find($id);
      $designation->designation = $request->input('designation');
      $designation->employment_type = $request->input('employment_type');
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
    public function destroy(Designation $designation)
    {
        //
    }
}