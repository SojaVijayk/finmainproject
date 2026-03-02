<?php

namespace App\Http\Controllers\Employee;
use App\Http\Controllers\Controller;
use App\Models\OfficeFiles;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
// use DB;

use Illuminate\Http\Request;

class OfficeFilesController extends Controller
{
    //

    public function index()
    {
      $pageConfigs = ['myLayout' => 'horizontal'];

      return view('content.officefiles.filemanagement',['pageConfigs'=> $pageConfigs]);
    }
    public function filesList()
    {
      // $office_files = OfficeFiles::all();
      // $totalCount = $office_files->count();
      // $approved = OfficeFiles::where('status',1)->get()->count();
      // $rejected = OfficeFiles::where('status',2)->get()->count();
      // $pending = OfficeFiles::where('status',0)->get()->count();
      $id= Auth::user()->id;

      $list = OfficeFiles::join("employees","employees.user_id","=","office_files.user_id")
      ->leftjoin("designations","designations.id","=","employees.designation")
      ->select('office_files.*',DB::raw("DATE_FORMAT(office_files.date, '%d-%b-%Y') as formatted_date"),'employees.name','employees.email','employees.profile_pic','designations.designation');
      if($id == 1 || $id == 20){
        $list = $list->orderBy('office_files.status')->get();
      }
      else{
        $list = $list->where('office_files.user_id',$id)->orderBy('office_files.status')->get();
      }
        //  $queries = DB::getQueryLog();
        //   $last_query = end($queries);
        //   dd($queries);
      return response()->json(['data'=> $list]);

    }
    public function store(Request $request)
    {
        //
        $this->validate($request, [
          'filename' => 'required',
          'description' => 'required|',
          'numbers' => 'required|',
          'status' => 'required|',

      ]);
      $id= Auth::user()->id;
      $date= date('Y-m-d H:i:s');
      // $from = date('Y-m-d', strtotime(str_replace('-', '/', $request->input('start_date'))));
      // $to = date('Y-m-d', strtotime(str_replace('-', '/', $request->input('end_date'))));
      if($request->input('date') && $request->input('date') != null && $request->input('date') != ''){
        $var = $request->input('date');
        $datef = str_replace('/', '-', $var);
        $from=  date('Y-m-d', strtotime($datef));
      }
      else{
        $from = NULL;
      }
      if($request->input('year') && $request->input('year') != null && $request->input('year') != ''){
        $year = $request->input('year');

      }
      else{
        $year = NULL;
      }




      $permission = OfficeFiles::create(['filename' => $request->input('filename')
      ,'date' => $from,'year' => $year
      ,'numbers' => $request->input('numbers'),'description' => $request->input('description'),'user_id' => $id,'status' => $request->input('status')
    ]);

      if ($permission) {

        return response()->json('created');
      } else {
        return response()->json(['message' => "Internal Server Error"], 500);

      }
    }
    public function edit($id)
    {
        //
        $designation = OfficeFiles::find($id);

          return response()->json(['designation'=> $designation]);
    }

    public function update(Request $request,  $id)
    {
        //
        $this->validate($request, [
          'filename' => 'required',
          'description' => 'required|',
          'numbers' => 'required|',
          'status' => 'required|',

      ]);



      $date= date('Y-m-d H:i:s');
      // $from = date('Y-m-d', strtotime(str_replace('-', '/', $request->input('start_date'))));
      // $to = date('Y-m-d', strtotime(str_replace('-', '/', $request->input('end_date'))));
      if($request->input('date') && $request->input('date') != null && $request->input('date') != ''){
        $var = $request->input('date');
        $datef = str_replace('/', '-', $var);
        $from=  date('Y-m-d', strtotime($datef));
      }
      else{
        $from = NULL;
      }
      if($request->input('year') && $request->input('year') != null && $request->input('year') != ''){
        $year = $request->input('year');

      }
      else{
        $year = NULL;
      }

      $designation = OfficeFiles::find($id);


      $designation->filename = $request->input('filename');
      $designation->year = $year;
      $designation->date = $from;
      $designation->numbers = $request->input('numbers');
      $designation->status = $request->input('status');
      $designation->description = $request->input('description');
      $designation->save();



      if ($designation) {
        return response()->json('Updated');
      } else {
        return response()->json(['message' => "Internal Server Error"], 500);

      }
    }

    public function destroy($id)
    {
        //
        $movement=OfficeFiles::find($id);
          $movement->delete(); //returns true/false
    }


}
