<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Models\LeaveAssign;
use App\Models\Leave;
use App\Models\EmploymentType;
use Illuminate\Http\Request;


class LeaveAssignController extends Controller
{
    //

   function __construct()
   {

       // $this->middleware('role:Admin');
   }
   //
   public function index()
   {
     $pageConfigs = ['myLayout' => 'horizontal'];
       //
       $leave_types = Leave::orderBy('id','DESC')->get();
       $employment_types = EmploymentType::orderBy('id','DESC')->get();
      //  $leaves = LeaveAssign::orderBy('id','DESC')->get();
       return view('content.leave.leave-assign',compact('leave_types','employment_types'),['pageConfigs'=> $pageConfigs]);
   }
   public function getAllAssignLeaves(Request $request)
   {
       // $permissions = Permission::join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
       // ->orderBy('id','DESC')->paginate(5);
       $leaves = LeaveAssign::select('leave_assigns.id','leaves.leave_type','total_credit','employment_types.employment_type','leave_assigns.status','leave_assigns.created_at')
       ->join("leaves","leaves.id","=","leave_assigns.leave_type")
       ->join("employment_types","employment_types.id","=","leave_assigns.employment_type")
       ->orderBy('leave_assigns.id','DESC')->get();
       return response()->json(['data'=> $leaves]);

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
         'leave_type' => 'required|',
         'total_credit' => 'required',
         'employment_type' => 'required',

     ]);

     $permission = LeaveAssign::create(['leave_type' => $request->input('leave_type'),
     'total_credit' => $request->input('total_credit'),
     'employment_type' => $request->input('employment_type'),
     'status' => 1]);

     if ($permission) {
       return response()->json('created');
     } else {
       return response()->json(['message' => "Internal Server Error"], 500);

     }
   }

   /**
    * Display the specified resource.
    */
   public function show(LeaveAssign $leave)
   {
       //
   }

   /**
    * Show the form for editing the specified resource.
    */
   public function assignEdit($id)
   {
       //
       $leaves = LeaveAssign::find($id);

         return response()->json(['leaves'=> $leaves]);
   }

   /**
    * Update the specified resource in storage.
    */
   public function assignUpdate(Request $request,  $id)
   {
       //
       $this->validate($request, [
         'leave_type' => 'required',
         'total_credit' => 'required',
         'employment_type' => 'required',

     ]);

     $leaves = LeaveAssign::find($id);
     $leaves->leave_type = $request->input('leave_type');
     $leaves->total_credit = $request->input('total_credit');
     $leaves->employment_type = $request->input('employment_type');
     $leaves->save();



     if ($leaves) {
       return response()->json('Updated');
     } else {
       return response()->json(['message' => "Internal Server Error"], 500);

     }
   }

   /**
    * Remove the specified resource from storage.
    */
   public function destroy(LeaveAssign $leaves)
   {
       //
   }
}