<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\EmploymentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmploymentTypeController extends Controller
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
        $pageConfigs = ['myLayout' => 'horizontal'];


        $employment_types = EmploymentType::orderBy('id','DESC')->get();
        if(Auth::user()->user_role ==1){
          return view('content.masters.employment_types',compact('employment_types'));
        }
        else{
          return view('content.masters.employment_types',compact('employment_types'),['pageConfigs'=> $pageConfigs]);
        }
    }
    public function getAllEmploymentTypes(Request $request)
    {
        // $permissions = Permission::join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
        // ->orderBy('id','DESC')->paginate(5);
        $employment_types = EmploymentType::select('id','employment_type','status','created_at')->orderBy('id','DESC')->get();
        return response()->json(['data'=> $employment_types]);

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
          'employment_type' => 'required|unique:employment_types,employment_type',
      ]);

      $permission = EmploymentType::create(['employment_type' => $request->input('employment_type')]);

      if ($permission) {
        return response()->json('created');
      } else {
        return response()->json(['message' => "Internal Server Error"], 500);

      }
    }

    /**
     * Display the specified resource.
     */
    public function show(EmploymentType $employment_type)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //
        $employment_types = EmploymentType::find($id);

          return response()->json(['employment_type'=> $employment_types]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,  $id)
    {
        //
        $this->validate($request, [
          'employment_type' => 'required',

      ]);

      $employment_types = EmploymentType::find($id);
      $employment_types->employment_type = $request->input('employment_type');
      $employment_types->save();



      if ($employment_types) {
        return response()->json('Updated');
      } else {
        return response()->json(['message' => "Internal Server Error"], 500);

      }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmploymentType $employment_types)
    {
        //
    }
}