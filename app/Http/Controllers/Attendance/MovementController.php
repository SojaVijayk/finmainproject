<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\EmploymentType;
use App\Models\Movement;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;

use App\Mail\LeaveRequestMail;
use App\Mail\LeaveRequestActionMail;
use Illuminate\Support\Facades\Mail;
use App\Exports\MovementExport;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use App\Models\PMS\Project;
use App\Models\PMS\Requirement;
use App\Models\PMS\Proposal;

class MovementController extends Controller
{
  public function index()
  {
    $pageConfigs = ['myLayout' => 'horizontal'];

    return view('content.attendance.movement', ['pageConfigs' => $pageConfigs]);
  }

  public function movementList()
  {
    $movements = Movement::all();
    $totalCount = $movements->count();
    $approved = Movement::where('status', 1)
      ->get()
      ->count();
    $rejected = Movement::where('status', 2)
      ->get()
      ->count();
    $pending = Movement::where('status', 0)
      ->get()
      ->count();
    $id = Auth::user()->id;

    $list = Movement::join('employees', 'employees.user_id', '=', 'movements.user_id')
      ->leftjoin('designations', 'designations.id', '=', 'employees.designation')
      ->leftjoin('employees as emp', 'emp.user_id', '=', 'movements.action_by')
      ->leftJoin('projects', function ($join) {
        $join->on('movements.reference_id', '=', 'projects.id')->where('movements.reference_type', 'Project');
      })
      ->leftJoin('requirements', function ($join) {
        $join->on('movements.reference_id', '=', 'requirements.id')->where('movements.reference_type', 'Requirement');
      })
      ->leftJoin('proposals', function ($join) {
        $join->on('movements.reference_id', '=', 'proposals.id')->where('movements.reference_type', 'Proposal');
      })
      ->leftJoin('requirements as prop_req', 'proposals.requirement_id', '=', 'prop_req.id')
      ->select(
        'movements.*',
        DB::raw("DATE_FORMAT(movements.start_date, '%d-%b-%Y') as formatted_start_date"),
        DB::raw("DATE_FORMAT(movements.end_date, '%d-%b-%Y') as formatted_end_date"),
        DB::raw("DATE_FORMAT(movements.requested_at, '%d-%b-%Y %H:%i') as formatted_requested_at"),
        'employees.name',
        'employees.email',
        'employees.profile_pic',
        'designations.designation',
        'emp.name as action_by_name',
        DB::raw("DATE_FORMAT(movements.action_at, '%d-%b-%Y %H:%i') as formatted_action_at"),
        DB::raw("CASE
            WHEN movements.reference_type = 'Project' THEN projects.title
            WHEN movements.reference_type = 'Requirement' THEN requirements.project_title
            WHEN movements.reference_type = 'Proposal' THEN prop_req.project_title
            ELSE '' END as ref_title"),
        DB::raw("CASE
            WHEN movements.reference_type = 'Project' THEN projects.project_code
            WHEN movements.reference_type = 'Requirement' THEN requirements.temp_no
            WHEN movements.reference_type = 'Proposal' THEN prop_req.ref_no
            ELSE '' END as ref_code"),
        DB::raw("CASE
            WHEN movements.reference_type = 'Proposal' THEN 'Proposal'
             ELSE movements.reference_type END as ref_type_label")
      )
      ->where('movements.user_id', $id)
      ->orderBy('movements.status')
      ->orderBy('movements.start_date', 'DESC')
      ->get();
    //  $queries = DB::getQueryLog();
    //   $last_query = end($queries);
    //   dd($queries);
    return response()->json(['data' => $list]);
  }

  public function getReferenceList(Request $request)
  {
    $type = $request->input('type');
    $userId = Auth::user()->id;
    $data = [];

    if ($type == 'Project') {
      $data = Project::whereHas('teamMembers', function ($q) use ($userId) {
        $q->where('user_id', $userId);
      })
        ->with('investigator:id,name')
        ->get()
        ->map(function ($item) {
          return [
            'id' => $item->id,
            'text' =>
              $item->project_code . ' - ' . $item->title . ' (Inv: ' . ($item->investigator->name ?? 'N/A') . ')',
          ];
        });
    } elseif ($type == 'Requirement') {
      $data = Requirement::with('creator:id,name')
        ->get()
        ->map(function ($item) {
          return [
            'id' => $item->id,
            'text' => $item->temp_no . ' - ' . $item->project_title . ' (By: ' . ($item->creator->name ?? 'N/A') . ')',
          ];
        });
    } elseif ($type == 'Proposal') {
      $data = Proposal::with(['requirement', 'creator:id,name'])
        ->get()
        ->map(function ($item) {
          return [
            'id' => $item->id,
            'text' =>
              ($item->requirement->temp_no ?? 'N/A') .
              ' - ' .
              ($item->requirement->project_title ?? 'N/A') .
              ' (By: ' .
              ($item->creator->name ?? 'N/A') .
              ')',
          ];
        });
    }

    return response()->json($data);
  }

  public function store(Request $request)
  {
    //
    $this->validate($request, [
      'title' => 'required',
      'type' => 'required|',
      'start_date' => 'required|',
      'start_time' => 'required|',
      'end_date' => 'required|',
      'end_time' => 'required|',
      'location' => 'required|',
    ]);
    $reference_type = $request->input('reference_type');
    $reference_id = $request->input('reference_id');

    if (empty($reference_type) || empty($reference_id)) {
      $reference_type = null;
      $reference_id = null;
    }
    $id = Auth::user()->id;
    $date = date('Y-m-d H:i:s');
    // $from = date('Y-m-d', strtotime(str_replace('-', '/', $request->input('start_date'))));
    // $to = date('Y-m-d', strtotime(str_replace('-', '/', $request->input('end_date'))));

    // $var = $request->input('start_date');
    // $datef = str_replace('/', '-', $var);
    // $from=  date('Y-m-d', strtotime($datef));

    // $var2 = $request->input('end_date');
    // $datet = str_replace('/', '-', $var2);
    // $to=  date('Y-m-d', strtotime($datet));

    try {
      $from = Carbon::createFromFormat('d/m/Y', $request->input('start_date'))->format('Y-m-d');
    } catch (\Exception $e) {
      return response()->json(['error' => 'Invalid date format'], 400);
    }

    try {
      $to = Carbon::createFromFormat('d/m/Y', $request->input('end_date'))->format('Y-m-d');
    } catch (\Exception $e) {
      return response()->json(['error' => 'Invalid date format'], 400);
    }
    if ($request->input('type') == 'Official') {
      if ($request->has('report') && $request->input('report') != '') {
        $report = $request->input('report');
        $report_updated_at = $date;
      } else {
        $report = null;
        $report_updated_at = null;
      }
    } else {
      $report = null;
      $report_updated_at = null;
    }
    $permission = Movement::create([
      'title' => $request->input('title'),
      'type' => $request->input('type'),
      'start_date' => $from,
      'start_time' => $request->input('start_time'),
      'end_date' => $to,
      'end_time' => $request->input('end_time'),
      'location' => $request->input('location'),
      'description' => $request->input('description'),
      'report' => $report,
      'report_updated_at' => $report_updated_at,
      'user_id' => $id,
      'status' => 0,
      'requested_at' => $date,
      'reference_type' => $reference_type,
      'reference_id' => $reference_id,
    ]);

    if ($permission) {
      $mailData = [
        'title' => 'Movement Request',
        'button' => 'Take Action',
        'url' => 'http://localhost:8000/movement/approve-list',
        'body' =>
          Auth::user()->name .
          ' created a new movement request for the period of ' .
          $request->input('start_date') .
          ' - ' .
          $request->input('start_time') .
          ' to ' .
          $request->input('end_date') .
          ' - ' .
          $request->input('end_time') .
          '.  Please login to your account for take action.',
      ];
      $reporting = Employee::where('employees.user_id', Auth::user()->id)
        ->leftjoin('employees as emp', 'emp.user_id', '=', 'employees.reporting_officer')
        ->select('emp.email')
        ->first();
      // dd($reporting);
      // Mail::to($reporting->email)->send(new LeaveRequestMail($mailData));

      return response()->json('created');
    } else {
      return response()->json(['message' => 'Internal Server Error'], 500);
    }
  }

  public function edit($id)
  {
    //
    $designation = Movement::find($id);

    return response()->json(['designation' => $designation]);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, $id)
  {
    //
    $this->validate($request, [
      'title' => 'required',
      'type' => 'required|',
      'start_date' => 'required|',
      'start_time' => 'required|',
      'end_date' => 'required|',
      'end_time' => 'required|',
      'location' => 'required|',
    ]);

    $date = date('Y-m-d H:i:s');
    // $from = date('Y-m-d', strtotime(str_replace('-', '/', $request->input('start_date'))));
    // $to = date('Y-m-d', strtotime(str_replace('-', '/', $request->input('end_date'))));

    // $var = $request->input('start_date');
    // $datef = str_replace('/', '-', $var);
    // $from=  date('Y-m-d', strtotime($datef));

    // $var2 = $request->input('end_date');
    // $datet = str_replace('/', '-', $var2);
    // $to=  date('Y-m-d', strtotime($datet));

    try {
      $from = Carbon::createFromFormat('d/m/Y', $request->input('start_date'))->format('Y-m-d');
    } catch (\Exception $e) {
      return response()->json(['error' => 'Invalid date format'], 400);
    }

    try {
      $to = Carbon::createFromFormat('d/m/Y', $request->input('end_date'))->format('Y-m-d');
    } catch (\Exception $e) {
      return response()->json(['error' => 'Invalid date format'], 400);
    }
    if ($request->input('type') == 'Official') {
      if ($request->has('report') && $request->input('report') != '') {
        $report = $request->input('report');
        $report_updated_at = $date;
      } else {
        $report = null;
        $report_updated_at = null;
      }
    } else {
      $report = null;
      $report_updated_at = null;
    }

    $designation = Movement::find($id);

    $designation->title = $request->input('title');
    $designation->type = $request->input('type');
    $designation->start_date = $from;
    $designation->start_time = $request->input('start_time');
    $designation->end_date = $to;
    $designation->end_time = $request->input('end_time');
    $designation->location = $request->input('location');
    $designation->description = $request->input('description');
    $designation->report = $report;
    $designation->report_updated_at = $report_updated_at;
    $designation->requested_at = $date;

    $reference_type = $request->input('reference_type');
    $reference_id = $request->input('reference_id');
    if (!empty($reference_type) && !empty($reference_id)) {
      $designation->reference_type = $reference_type;
      $designation->reference_id = $reference_id;
    } else {
      $designation->reference_type = null;
      $designation->reference_id = null;
    }

    $designation->save();

    if ($designation) {
      return response()->json('Updated');
    } else {
      return response()->json(['message' => 'Internal Server Error'], 500);
    }
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy($id)
  {
    //
    $movement = Movement::find($id);
    $movement->delete(); //returns true/false
  }

  public function approveList(Request $request)
  {
    $id = Auth::user()->id;

    $list = Movement::join('employees', 'employees.user_id', '=', 'movements.user_id')
      ->select('movements.*')
      ->where('employees.reporting_officer', $id);

    $totalCount = $list->count();

    $approved = $list->where('movements.status', 1)->count();
    $pending = Movement::join('employees', 'employees.user_id', '=', 'movements.user_id')
      ->select('movements.*')
      ->where('employees.reporting_officer', $id)
      ->where('movements.status', 0)
      ->count();
    $rejected = Movement::join('employees', 'employees.user_id', '=', 'movements.user_id')
      ->select('movements.*')
      ->where('employees.reporting_officer', $id)
      ->where('movements.status', 2)
      ->count();
    $report = Movement::join('employees', 'employees.user_id', '=', 'movements.user_id')
      ->select(
        'movements.*',
        DB::raw("CASE
            WHEN movements.reference_type = 'Project' THEN projects.title
            WHEN movements.reference_type = 'Requirement' THEN requirements.project_title
            WHEN movements.reference_type = 'Proposal' THEN prop_req.project_title
            ELSE '' END as ref_title"),
        DB::raw("CASE
            WHEN movements.reference_type = 'Project' THEN projects.project_code
            WHEN movements.reference_type = 'Requirement' THEN requirements.temp_no
            WHEN movements.reference_type = 'Proposal' THEN prop_req.ref_no
            ELSE '' END as ref_code")
      )
      ->leftJoin('projects', function ($join) {
        $join->on('movements.reference_id', '=', 'projects.id')->where('movements.reference_type', 'Project');
      })
      ->leftJoin('requirements', function ($join) {
        $join->on('movements.reference_id', '=', 'requirements.id')->where('movements.reference_type', 'Requirement');
      })
      ->leftJoin('proposals', function ($join) {
        $join->on('movements.reference_id', '=', 'proposals.id')->where('movements.reference_type', 'Proposal');
      })
      ->leftJoin('requirements as prop_req', 'proposals.requirement_id', '=', 'prop_req.id')
      ->where('employees.reporting_officer', $id)
      ->where('movements.report', '!=', null)
      ->count();
    // echo $pending;exit;
    $pageConfigs = ['myLayout' => 'horizontal'];
    if ($request->has('report')) {
      return view(
        'content.attendance.movement-approve-list-with-report',
        compact('totalCount', 'approved', 'pending', 'rejected', 'report'),
        ['pageConfigs' => $pageConfigs]
      );
    }

    return view(
      'content.attendance.movement-approve-list',
      compact('totalCount', 'approved', 'pending', 'rejected', 'report'),
      ['pageConfigs' => $pageConfigs]
    );
  }
  public function requestList(Request $request)
  {
    $id = Auth::user()->id;

    if ($request->has('report')) {
      $list = Movement::join('employees', 'employees.user_id', '=', 'movements.user_id')
        ->leftjoin('designations', 'designations.id', '=', 'employees.designation')
        ->leftjoin('employees as emp', 'emp.user_id', '=', 'movements.action_by')
        ->leftJoin('projects', function ($join) {
          $join->on('movements.reference_id', '=', 'projects.id')->where('movements.reference_type', 'Project');
        })
        ->leftJoin('requirements', function ($join) {
          $join->on('movements.reference_id', '=', 'requirements.id')->where('movements.reference_type', 'Requirement');
        })
        ->leftJoin('proposals', function ($join) {
          $join->on('movements.reference_id', '=', 'proposals.id')->where('movements.reference_type', 'Proposal');
        })
        ->leftJoin('requirements as prop_req', 'proposals.requirement_id', '=', 'prop_req.id')
        ->select(
          'movements.*',
          DB::raw("DATE_FORMAT(movements.start_date, '%d-%b-%Y') as formatted_start_date"),
          DB::raw("DATE_FORMAT(movements.end_date, '%d-%b-%Y') as formatted_end_date"),
          DB::raw("DATE_FORMAT(movements.requested_at, '%d-%b-%Y %H:%i') as formatted_requested_at"),
          'employees.name',
          'employees.email',
          'employees.profile_pic',
          'designations.designation',
          'emp.name as action_by_name',
          DB::raw("DATE_FORMAT(movements.action_at, '%d-%b-%Y %H:%i') as formatted_action_at"),
          DB::raw("CASE
            WHEN movements.reference_type = 'Project' THEN projects.title
            WHEN movements.reference_type = 'Requirement' THEN requirements.project_title
            WHEN movements.reference_type = 'Proposal' THEN prop_req.project_title
            ELSE '' END as ref_title"),
          DB::raw("CASE
            WHEN movements.reference_type = 'Project' THEN projects.project_code
            WHEN movements.reference_type = 'Requirement' THEN requirements.temp_no
            WHEN movements.reference_type = 'Proposal' THEN prop_req.ref_no
            ELSE '' END as ref_code"),
          DB::raw("CASE
            WHEN movements.reference_type = 'Proposal' THEN 'Proposal'
             ELSE movements.reference_type END as ref_type_label")
        )
        ->where('employees.reporting_officer', $id)
        ->where('movements.report', '!=', null)
        ->orderBy('movements.status')
        ->orderBy('movements.start_date', 'DESC')
        ->get();
    } else {
      $list = Movement::join('employees', 'employees.user_id', '=', 'movements.user_id')
        ->leftjoin('designations', 'designations.id', '=', 'employees.designation')
        ->leftjoin('employees as emp', 'emp.user_id', '=', 'movements.action_by')
        ->leftJoin('projects', function ($join) {
          $join->on('movements.reference_id', '=', 'projects.id')->where('movements.reference_type', 'Project');
        })
        ->leftJoin('requirements', function ($join) {
          $join->on('movements.reference_id', '=', 'requirements.id')->where('movements.reference_type', 'Requirement');
        })
        ->leftJoin('proposals', function ($join) {
          $join->on('movements.reference_id', '=', 'proposals.id')->where('movements.reference_type', 'Proposal');
        })
        ->leftJoin('requirements as prop_req', 'proposals.requirement_id', '=', 'prop_req.id')
        ->select(
          'movements.*',
          DB::raw("DATE_FORMAT(movements.start_date, '%d-%b-%Y') as formatted_start_date"),
          DB::raw("DATE_FORMAT(movements.end_date, '%d-%b-%Y') as formatted_end_date"),
          DB::raw("DATE_FORMAT(movements.requested_at, '%d-%b-%Y %H:%i') as formatted_requested_at"),
          'employees.name',
          'employees.email',
          'employees.profile_pic',
          'designations.designation',
          'emp.name as action_by_name',
          DB::raw("DATE_FORMAT(movements.action_at, '%d-%b-%Y %H:%i') as formatted_action_at"),
          DB::raw("CASE
            WHEN movements.reference_type = 'Project' THEN projects.title
            WHEN movements.reference_type = 'Requirement' THEN requirements.project_title
            WHEN movements.reference_type = 'Proposal' THEN prop_req.project_title
            ELSE '' END as ref_title"),
          DB::raw("CASE
            WHEN movements.reference_type = 'Project' THEN projects.project_code
            WHEN movements.reference_type = 'Requirement' THEN requirements.temp_no
            WHEN movements.reference_type = 'Proposal' THEN prop_req.ref_no
            ELSE '' END as ref_code"),
          DB::raw("CASE
            WHEN movements.reference_type = 'Proposal' THEN 'Proposal'
             ELSE movements.reference_type END as ref_type_label")
        )
        ->where('employees.reporting_officer', $id)
        ->orderBy('movements.status')
        ->orderBy('movements.start_date', 'DESC')
        ->get();
    }

    //  $queries = DB::getQueryLog();
    //   $last_query = end($queries);
    //   dd($queries);
    return response()->json(['data' => $list]);
  }

  public function action(Request $request, $id)
  {
    //
    $this->validate($request, [
      'status' => 'required',
      'remark' => 'required',
    ]);

    $date = date('Y-m-d H:i:s');

    $designation = Movement::find($id);
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
        'body' =>
          Auth::user()->name .
          ' take an action against your Movement request for the period of ' .
          $designation->start_date .
          ' - ' .
          $designation->start_time .
          ' to ' .
          $designation->end_date .
          ' -' .
          $designation->end_time .
          '. Please login to your account for detailed view.',
      ];

      $user = User::find($designation->user_id);
      // Mail::to($user->email)->send(new LeaveRequestActionMail($mailData));

      return response()->json('Updated');
    } else {
      return response()->json(['message' => 'Internal Server Error'], 500);
    }
  }

  public function reportSubmit(Request $request, $id)
  {
    //
    $this->validate($request, [
      'report' => 'required',
    ]);

    $date = date('Y-m-d H:i:s');

    $designation = Movement::find($id);
    $designation->report = $request->input('report');
    $designation->report_updated_at = $date;
    $designation->save();
    if ($designation) {
      return response()->json('Updated');
    } else {
      return response()->json(['message' => 'Internal Server Error'], 500);
    }
  }

  public function downloadBulk(Request $request)
  {
    // $from = date('Y-m-d', strtotime(str_replace('-', '/', $request->input('fromDate'))));
    // $to = date('Y-m-d', strtotime(str_replace('-', '/', $request->input('toDate'))));

    try {
      $formattedDate_from = Carbon::createFromFormat('d/m/Y', $request->input('fromDate'))->format('Y-m-d');
    } catch (\Exception $e) {
      return response()->json(['error' => 'Invalid date format'], 400);
    }

    try {
      $formattedDate_to = Carbon::createFromFormat('d/m/Y', $request->input('toDate'))->format('Y-m-d');
    } catch (\Exception $e) {
      return response()->json(['error' => 'Invalid date format'], 400);
    }

    // return $formattedDate_to;

    if ($request->input('type') == 1) {
      $employees = [Auth::user()->id];
    } else {
      $employees = $request->input('employeeList');
    }

    $list = Movement::join('employees', 'employees.user_id', '=', 'movements.user_id')
      ->leftjoin('designations', 'designations.id', '=', 'employees.designation')
      ->leftjoin('employees as emp', 'emp.user_id', '=', 'movements.action_by')
      ->select(
        'movements.*',
        'employees.name',
        'employees.email',
        'employees.profile_pic',
        'designations.designation',
        'emp.name as action_by_name'
      )
      // ->where('movements.user_id',$id)
      // ->orderBy('movements.status')->get();
      ->whereIn('movements.user_id', $employees)
      // ->whereBetween('movements.start_date', [$formattedDate_from, $formattedDate_to])
      // ->whereBetween('movements.end_date', [$formattedDate_from, $formattedDate_to])
      ->where(function ($query) use ($formattedDate_from, $formattedDate_to) {
        $query
          ->where('start_date', '<=', $formattedDate_to) // Database start is before or on the selected end
          ->where('end_date', '>=', $formattedDate_from); // Database end is after or on the selected start
      })

      ->orderBy('movements.user_id', 'DESC')
      ->get();

    if ($request->input('view_type') == 'html') {
      return response()->json(['list' => $list]);
    } elseif ($request->input('view_type') == 'pdf') {
      $pdf = PDF::loadView('exports.movement-export-pdf', compact('list'));
      return $pdf->download('movement.pdf');
    } elseif ($request->input('view_type') == 'excel') {
      return Excel::download(new MovementExport($list), 'movement.xlsx');
    }
    // return response()->download(public_path('storage/AttendanceRepot01-09-2023To30-09-2023Bulk.pdf'));
  }
}
