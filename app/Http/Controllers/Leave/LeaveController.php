<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Models\Holiday;
use Illuminate\Http\Request;
use App\Http\Controllers\MasterFunctionController;

class LeaveController extends Controller
{
  /**
     * Display a listing of the resource.
     */
    function __construct()
    {

        // $this->middleware('role:Admin');
    }
    //
    public function index()
    {
      $pageConfigs = ['myLayout' => 'horizontal'];
        //
        $leaves = Leave::orderBy('id','DESC')->get();
        return view('content.leave.leave-master',compact('leaves'),['pageConfigs'=> $pageConfigs]);
    }
    public function getAllLeaves(Request $request)
    {
        // $permissions = Permission::join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
        // ->orderBy('id','DESC')->paginate(5);
        $leaves = Leave::select('id','leave_type','status','created_at')->orderBy('id','DESC')->get();
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
          'leave_type' => 'required|unique:leaves,leave_type',

      ]);

      $permission = Leave::create(['leave_type' => $request->input('leave_type'),

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
    public function show(Leave $leave)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //
        $leaves = Leave::find($id);

          return response()->json(['leaves'=> $leaves]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,  $id)
    {
        //
        $this->validate($request, [
          'leave_type' => 'required',


      ]);

      $leaves = Leave::find($id);
      $leaves->leave_type = $request->input('leave_type');

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
    public function destroy(Leave $leaves)
    {
        //
    }

    Public Function generateHoliday(){

      $year = date('Y');
    $weekends = (new MasterFunctionController)->findWeekendDays($year);

      // Print the result
      foreach ($weekends as $month => $dates) {
          // echo "Month: $month\n";
          foreach ($dates as $date) {
              // echo "$date\n";
              $count = Holiday::where('date',$date)->where('description','Holiday')->count();
              if($count==0){
            Holiday::create(['date' => $date,
                'description' => 'Holiday']);
              }


          }

      }

    }
}