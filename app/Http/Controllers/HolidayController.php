<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HolidayController extends Controller
{
    //

    public function index()
    {
        //
        $pageConfigs = ['myLayout' => 'horizontal'];


        $holidays = Holiday::orderBy('id','DESC')->get();
        if(Auth::user()->user_role ==1){
          return view('content.masters.holiday',compact('holidays'));
        }
        else{
          return view('content.masters.holiday',compact('holidays'),['pageConfigs'=> $pageConfigs]);
        }
    }
    public function getAllHolidays(Request $request)
    {
        // $permissions = Permission::join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
        // ->orderBy('id','DESC')->paginate(5);
        $employment_types = Holiday::select('id','date','description','created_at')->orderBy('date','DESC')->get();
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
          'date' => 'required',
          'description' => 'required'
      ]);
      $date = date('Y-m-d', strtotime(str_replace('-', '/', $request->input('date'))));
      $permission = Holiday::create(['date' => $date,'description' => $request->input('description')]);

      if ($permission) {
        return response()->json('created');
      } else {
        return response()->json(['message' => "Internal Server Error"], 500);

      }
    }

    /**
     * Display the specified resource.
     */
    public function show(Holiday $employment_type)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //
        $employment_types = Holiday::find($id);

          return response()->json(['employment_type'=> $employment_types]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,  $id)
    {
        //
        $this->validate($request, [
          'date' => 'required',

      ]);

      $employment_types = Holiday::find($id);
      $employment_types->date = $request->input('date');
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
    public function destroy(Holiday $employment_types)
    {
        //
    }
}