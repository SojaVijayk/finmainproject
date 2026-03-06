<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ProjectEmployee;
use App\Models\Designation;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use App\Models\Service;
use App\Models\Payroll;
use App\Models\DeductionMaster;
use App\Models\EmployeeDynamicDeduction;

class ProjectEmployeeController extends Controller
{
    /**
     * Display project selection.
     */
    public function projectList()
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        $projects = Project::all();
        return view('content.projects.project-selection', compact('projects', 'pageConfigs'));
    }

    /**
     * Display a listing of the resource for a specific project.
     */
  public function index($id, Request $request)
  {
    $request->session()->forget('project');
    $pageConfigs = ['myLayout' => 'horizontal'];
    
    // Filter by project_id
    $employees = ProjectEmployee::where('project_id', $id)->get();
    $employeeCount = $employees->count();
    
    // Fix: These columns don't exist in project_employee, using join or setting to 0 for now
    // as per user's note that senior will handle properly later.
    $contract = 0;
    $consultant = 0;
    $dw = 0;

    $designations = Designation::where('status', 1)->get();
    $roles = Role::where('name', '!=', "Admin")->get();
    $user_types = DB::table("usertype_role")->where('status', 1)->get();
    $project = Project::find($id);

    $request->session()->put('project', $id);
    
    return view('content.projects.project-employee-management', [
      'totalEmployee' => $employeeCount,
      'contract' => $contract,
      'dw' => $dw,
      'consultant' => $consultant,
      'designations' => $designations,
      'user_types' => $user_types,
      'project_details' => $project,
      'is_global' => false
    ], ['pageConfigs' => $pageConfigs]);
  }

  public function globalIndex(Request $request)
  {
    $pageConfigs = ['myLayout' => 'horizontal'];
    $employees = ProjectEmployee::all();
    $employeeCount = $employees->count();
    
    $designations = Designation::where('status', 1)->get();
    $roles = Role::where('name', '!=', "Admin")->get();
    $user_types = DB::table("usertype_role")->where('status', 1)->get();

    return view('content.projects.project-employee-management', [
      'totalEmployee' => $employeeCount,
      'contract' => 0, // Placeholder or calculate if needed
      'dw' => 0,
      'consultant'=> 0,
      'designations' => $designations,
      'user_types' => $user_types,
      'project_details' => null,
      'is_global' => true
    ],['pageConfigs'=> $pageConfigs]);
  }

  public function globalList(Request $request)
  {
    $query = ProjectEmployee::leftJoin("users", "users.id", "=", "project_employee.user_id")
    ->leftJoin("usertype_role","usertype_role.id","=","users.user_role")
    ->leftJoin("designations","designations.id","=","project_employee.designation_id")
    ->select('project_employee.id','project_employee.name','project_employee.last_name',
      'project_employee.mobile', 'project_employee.email', 'project_employee.status', 
      'project_employee.employee_code', 'project_employee.age', 'project_employee.dob', 'project_employee.date_of_joining', 'project_employee.address',
      'usertype_role.usertype_role as user_type','designations.designation');

    if ($request->has('project_id')) {
        $query->where('project_employee.project_id', $request->project_id);
    } elseif ($request->session()->has('project') && !$request->has('is_global')) {
        $query->where('project_employee.project_id', $request->session()->get('project'));
    }

    $list = $query->get();

    return response()->json(['data'=> $list]);
  }

  public function employeeList(Request $request)
  {

    // DB::connection()->enableQueryLog();
    $id = $request->session()->get('project');
    $list = ProjectEmployee::join("usertype_role","usertype_role.id","=","project_employee.user_type")
    ->leftjoin("designations","designations.id","=","project_employee.designation_id")
    ->leftjoin("gender","gender.id","=","project_employee.gender_id")
    ->select('project_employee.id','project_employee.prefix','project_employee.name','project_employee.last_name',
      'project_employee.profile_pic','project_employee.mobile_pri','project_employee.email_pri','project_employee.status','project_employee.empId',
      'usertype_role.usertype_role as user_type','gender.gender_name','designations.designation')
    ->where('project_id',$id)->get();

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






  public function globalDetails($id)
  {
    $pageConfigs = ['myLayout' => 'horizontal'];
    $employee = ProjectEmployee::where('id', $id)
    ->with(['services.audits', 'payroll', 'designation', 'project', 'deductionMaster', 'dynamicDeductions'])
      ->firstOrFail();

    $designations = Designation::where('status', 1)->get();
    $user_types = DB::table("usertype_role")->where('status', 1)->get();

    $employmentTypes = \App\Models\EmploymentType::where('status', 1)->get();
    $payTypes = \App\Models\PayType::where('status', 1)->get();
    $masterDeductions = \App\Models\MasterDynamicDeduction::where('status', 1)->get();

    return view('content.projects.employee-details-v2', compact('employee', 'pageConfigs', 'designations', 'user_types', 'employmentTypes', 'payTypes', 'masterDeductions'));
  }

    public function updateMaster(Request $request, $id)
  {
      $employee = ProjectEmployee::findOrFail($id);

      // Validation identifying unique fields excluding current record
      // Note: email is on Users table, so we need to check User table uniqueness
      // mobile is on project_employee table
      
      $userId = $employee->user_id;

      $request->validate([
          'email' => 'required|email|unique:users,email,' . $userId,
          'mobile' => 'required|numeric|unique:project_employee,mobile,' . $id,
          'name' => 'required',
          'joining_date' => 'required',
          'address' => 'required',
      ]);

      $allowed_fields = [
          'name', 'mobile', 'pan_number', 'age', 'dob', 'address', 
          'bank_name', 'account_no', 'account_name', 'branch', 'ifsc_code'
      ];
      $data = $request->only($allowed_fields);
      
      // Map mismatched keys
      if ($request->has('joining_date')) {
          $data['date_of_joining'] = $request->joining_date;
      }
      
      // Handle Email Update (Sync with User table)
      if ($request->has('email') && $request->email !== $employee->email) {
          $data['email'] = $request->email;
          if ($employee->user) {
              $employee->user->update(['email' => $request->email]);
          }
      }

      if ($request->hasFile('documents')) {
          $file = $request->file('documents');
          $fileName = time() . '_' . $file->getClientOriginalName();
          $file->move(public_path('uploads/employee_documents'), $fileName);
          $data['documents'] = $fileName;
      }
      
      // If updating an inactive user, we might want to keep them inactive UNLESS explicitly changed?
      // For now, updateMaster just updates details. Status change is usually a separate flow (service add/end).
      // But if we want to allow reactivating here, we'd need a status field in the form.

      $employee->update($data);
      return response()->json(['success' => true, 'message' => 'Master info updated successfully']);
  }

  public function updateService(Request $request, $id) // $id is the Route Param (ProjectEmployee ID)
  {
      // Explicit Validation
      $request->validate([
          'department' => 'required',
          'role' => 'required',
          'employment_type' => 'required',
          'pay_type' => 'required',
          'consolidated_pay' => 'required|numeric',
          'basic_pay' => 'required_if:employment_type,Deputation|nullable|numeric',
          'da' => 'required_if:employment_type,Deputation|nullable|numeric',
          'hra' => 'required_if:employment_type,Deputation|nullable|numeric',
          'start_date' => 'required|date',
      ]);

      // 0. Resolve the correct P_ID from the ProjectEmployee Table
      $employee = ProjectEmployee::findOrFail($id);
      $p_id = $employee->p_id;

      $allowedFields = ['department', 'employment_type', 'role', 'pay_type', 'consolidated_pay', 'basic_pay', 'da', 'hra', 'start_date', 'end_date', 'status', 'pf_available', 'pf_uan'];
      $data = $request->only($allowedFields);

      // Handle checkbox for PF Available
      $data['pf_available'] = $request->has('pf_available') && $request->pf_available !== 'false' ? 1 : 0;
      if ($data['pf_available'] == 0) {
          $data['pf_uan'] = null;
      }

      $serviceId = $request->id ?? $request->service_id;
      $isNewRecord = ($request->has('new_record') && $request->new_record == '1');

      if ($isNewRecord) {
          // 1. Terminate the CURRENT active service (if exists)
          $currentActive = Service::where('p_id', $p_id)->where('status', 1)->first();
          
          if ($currentActive) {
              $newStartDate = \Carbon\Carbon::parse($request->start_date);
              $endDate = $newStartDate->copy()->subDay()->format('Y-m-d');
              
              $currentActive->update([
                  'status' => 0,
                  'end_date' => $endDate
              ]);
          }

          // 2. Create the NEW service record
          $data['p_id'] = $p_id;
          $data['status'] = 1; // New record is Active
          $data['end_date'] = null; 
          
          Service::create($data);
          
      } else {
          // 3. Edit Existing Record OR Update/Create Current
          if ($serviceId) {
               $service = Service::findOrFail($serviceId);
               
               // AUDIT LOGGING START
               $original = $service->getOriginal();
               $service->fill($data); // Fill changes to compare, but don't save yet
               
               if ($service->isDirty()) { // Check if something changed
                   foreach ($allowedFields as $field) {
                       if ($service->isDirty($field)) {
                           // Handle edge case: Null vs Empty String might be considered change, filter if needed
                           $old = $original[$field] ?? null;
                           $new = $data[$field] ?? null;
                           
                           // Don't log if effectively same (e.g., 1 vs "1")
                           if ($old != $new) {
                               \App\Models\ServiceAudit::create([
                                   'service_id' => $service->id,
                                   'field_name' => $field,
                                   'old_value' => $old,
                                   'new_value' => $new,
                                   'updated_by' => auth()->id() ?? 1 // Fallback to 1 if no auth
                               ]);
                           }
                       }
                   }
                   $service->save(); // Now Save
               }
               // AUDIT LOGGING END
               
          } else {
               $service = Service::updateOrCreate(
                  ['p_id' => $p_id, 'status' => 1],
                  $data
              );
              $serviceId = $service->id;
          }

          // 4. SAFETEY: If we just set this record to ACTIVE, deactivate all others
          if (isset($data['status']) && $data['status'] == 1) {
              Service::where('p_id', $p_id)
                     ->where('id', '!=', $serviceId) // Exclude self
                     ->where('status', 1)
                     ->update(['status' => 0]); // Deactivate others
          }
      }
      
      // 5. Sync ProjectEmployee Status
      $hasActiveService = Service::where('p_id', $p_id)->where('status', 1)->exists();
      
      if ($employee) {
          $employee->status = $hasActiveService ? 1 : 2;
          $employee->save();
      }

      // 6. Handle Dynamic Deductions
      if ($request->has('dynamic_deductions')) {
          $deductionsJson = $request->input('dynamic_deductions');
          $deductionsArray = json_decode($deductionsJson, true);

          if (is_array($deductionsArray)) {
              // Delete existing deductions for this employee to replace them
              EmployeeDynamicDeduction::where('p_id', $p_id)->delete();

              foreach ($deductionsArray as $ded) {
                  EmployeeDynamicDeduction::create([
                      'p_id' => $p_id,
                      'deduction_name' => $ded['deduction_name'] ?? $ded['name'], // Fallback depending on JS mapping
                      'calculation_type' => $ded['calculation_type'] ?? $ded['type'],
                      'percentage' => $ded['percentage'],
                      'base_amount' => $ded['base_amount'],
                      'amount' => $ded['amount']
                  ]);
              }
          }
      }
      
      return response()->json(['success' => true, 'message' => 'Service info updated successfully']);
  }

  public function updatePayroll(Request $request, $p_id)
  {
    $data = $request->all();
    
    // Calculate Totals
    $gross = ($request->basic_pay ?? 0) + 
             ($request->da ?? 0) + 
             ($request->hra ?? 0) + 
             ($request->conveyance_allowance ?? 0) + 
             ($request->medical_allowance ?? 0) + 
             ($request->special_allowance ?? 0) + 
             ($request->other_allowance ?? 0) + 
             ($request->bonus ?? 0) + 
             ($request->overtime_pay ?? 0) + 
             ($request->attendance_bonus ?? 0);
             
    $deductions = ($request->pf ?? 0) + 
                  ($request->epf ?? 0) + 
                  ($request->esi ?? 0) + 
                  ($request->lic ?? 0) + 
                  ($request->professional_tax ?? 0) + 
                  ($request->tds ?? 0) + 
                  ($request->loan_deduction ?? 0) + 
                  ($request->gdf ?? 0) + 
                  ($request->gpf ?? 0) + 
                  ($request->others ?? 0);
                  
    $data['gross_salary'] = $gross;
    $data['total_deductions'] = $deductions;
    $data['net_salary'] = $gross - $deductions;
    
    // Use updateOrCreate with p_id, paymonth, and year as keys for monthly uniqueness
    $payroll = Payroll::updateOrCreate(
        ['p_id' => $p_id, 'paymonth' => $request->paymonth, 'year' => $request->year],
        $data
    );
    
    return response()->json(['success' => true, 'message' => 'Payroll info updated successfully']);
  }

  public function updateDeduction(Request $request, $p_id)
  {
      $employee = ProjectEmployee::where('p_id', $p_id)->firstOrFail();

      $data = $request->only([
          'tds', 'tds_value', 'tds_type',
          'epf', 'epf_value', 'epf_type',
          'pf', 'pf_value', 'pf_type',
          'lic', 'lic_value', 'lic_type',
          'edli', 'edli_value', 'edli_type',
          'other', 'other_value', 'other_type',
          'tds_192_b', 'tds_192_b_value', 'tds_192_b_type',
          'tds_194_j', 'tds_194_j_value', 'tds_194_j_type',
          'professional_tax', 'professional_tax_value', 'professional_tax_type',
          'esi_employer', 'esi_employer_value', 'esi_employer_type',
          'other_details'
      ]);

      // Convert checkbox values to boolean safely
      $data['tds'] = $request->has('tds') ? 1 : 0;
      $data['epf'] = $request->has('epf') ? 1 : 0;
      $data['pf'] = $request->has('pf') ? 1 : 0;
      $data['lic'] = $request->has('lic') ? 1 : 0;
      $data['edli'] = $request->has('edli') ? 1 : 0;
      $data['other'] = $request->has('other') ? 1 : 0;
      $data['tds_192_b'] = $request->has('tds_192_b') ? 1 : 0;
      $data['tds_194_j'] = $request->has('tds_194_j') ? 1 : 0;
      $data['professional_tax'] = $request->has('professional_tax') ? 1 : 0;
      $data['esi_employer'] = $request->has('esi_employer') ? 1 : 0;
      
      // Default types to 'amount' if not present
      $data['tds_type'] = $data['tds_type'] ?? 'amount';
      $data['epf_type'] = $data['epf_type'] ?? 'amount';
      $data['pf_type'] = $data['pf_type'] ?? 'amount';
      $data['lic_type'] = $data['lic_type'] ?? 'amount';
      $data['edli_type'] = $data['edli_type'] ?? 'amount';
      $data['other_type'] = $data['other_type'] ?? 'amount';
      $data['tds_192_b_type'] = $data['tds_192_b_type'] ?? 'amount';
      $data['tds_194_j_type'] = $data['tds_194_j_type'] ?? 'amount';
      $data['professional_tax_type'] = $data['professional_tax_type'] ?? 'amount';
      $data['esi_employer_type'] = $data['esi_employer_type'] ?? 'amount';

      // Get employee's active base pay
      $basePay = 0;
      $activeService = Service::where('p_id', $p_id)->where('status', 1)->first();
      if ($activeService) {
          $basePay = $activeService->consolidated_pay ?? 0;
      }

      // Calculate amounts
      $deductions = ['tds', 'epf', 'pf', 'lic', 'edli', 'tds_192_b', 'tds_194_j', 'professional_tax', 'esi_employer', 'other'];
      foreach ($deductions as $key) {
          $val = floatval($data[$key . '_value'] ?? 0);
          $type = $data[$key . '_type'];
          
          if ($type === 'percentage') {
              $data[$key . '_amount'] = ($val / 100) * $basePay;
          } else {
              $data[$key . '_amount'] = $val;
          }
      }

      $data['p_id'] = $p_id;

      $deduction = DeductionMaster::updateOrCreate(
          ['p_id' => $p_id],
          $data
      );

      return response()->json(['success' => true, 'message' => 'Deduction Master info updated successfully']);
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
  public function globalStore(Request $request)
    {
        // 1. Validate Basic Fields (Remove unique:users,email for custom handling)
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email',
            'mobile' => 'required|numeric',
            'age' => 'required',
            'dob' => 'required',
            'joining_date' => 'required',
            'address' => 'required',
            'documents' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
            'basic_pay' => 'required_if:employment_type,Deputation|nullable|numeric',
            'da' => 'required_if:employment_type,Deputation|nullable|numeric',
            'hra' => 'required_if:employment_type,Deputation|nullable|numeric',
        ]);

        $input = $request->all();
        $email = $request->email;

        // 2. Check for Existing User
        $user = User::where('email', $email)->first();
        $employee = null;

        if ($user) {
            // User Exists - Check for Employee Record
            $employee = ProjectEmployee::where('user_id', $user->id)->first();

            if ($employee) {
                // Employee Record Exists
                if ($employee->status == 1) {
                    // ALREADY ACTIVE - Block
                    return response()->json(['message' => "Employee with this email is already active."], 422);
                } else {
                    // INACTIVE - REHIRE / REACTIVATE
                    // Update master details
                    $employee->update([
                        'name' => $request->name,
                        'mobile' => $request->mobile,
                        'age' => $request->age,
                        'dob' => $request->dob,
                        'date_of_joining' => $request->joining_date, // New Joining Date
                        'address' => $request->address,
                        'status' => 1, // Reactivate
                        'project_id' => $request->project_id ?? $employee->project_id,
                        'bank_name' => $request->bank_name,
                        'account_no' => $request->account_no,
                        'account_name' => $request->account_name,
                        'branch' => $request->branch,
                        'ifsc_code' => $request->ifsc_code
                    ]);
                    
                    // Note: We do NOT reset p_id. Keep history linked.
                }
            } else {
                // User exists but NO Employee record (Rare, but possible if user created manually)
                $p_id = "EMP-" . time() . rand(100, 999);
                $employee = ProjectEmployee::create([
                    'user_id' => $user->id,
                    'name' => $request->name,
                    'last_name' => '',
                    'mobile' => $request->mobile,
                    'pan_number' => $request->pan_number,
                    'email' => $request->email,
                    'designation_id' => null,
                    'age' => $request->age,
                    'dob' => $request->dob,
                    'date_of_joining' => $request->joining_date,
                    'address' => $request->address,
                    'p_id' => $p_id,
                    'employee_code' => $p_id,
                    'status' => 1,
                    'project_id' => $request->project_id ?? null,
                    'bank_name' => $request->bank_name,
                    'account_no' => $request->account_no,
                    'account_name' => $request->account_name,
                    'branch' => $request->branch,
                    'ifsc_code' => $request->ifsc_code
                ]);
            }
        } else {
            // 3. New User Creation
            $username = $request->email;
            $password = Hash::make("12345678");
            $usertype_role = 2; // Employee

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $username,
                'password' => $password,
                'user_role' => $usertype_role,
            ]);

            $p_id = "EMP-" . time() . rand(100, 999);
            $employee = ProjectEmployee::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'last_name' => '',
                'mobile' => $request->mobile,
                'pan_number' => $request->pan_number,
                'email' => $request->email,
                'designation_id' => null,
                'age' => $request->age,
                'dob' => $request->dob,
                'date_of_joining' => $request->joining_date,
                'address' => $request->address,
                'p_id' => $p_id,
                'employee_code' => $p_id,
                'status' => 1,
                'project_id' => $request->project_id ?? null,
                'bank_name' => $request->bank_name,
                'account_no' => $request->account_no,
                'account_name' => $request->account_name,
                'branch' => $request->branch,
                'ifsc_code' => $request->ifsc_code
            ]);
        }
        
        // At this point, $employee is guaranteed to exist and be Active (or just updated)
        $p_id = $employee->p_id;

        // 4. Handle Documents
        if ($request->hasFile('documents')) {
            $file = $request->file('documents');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/employee_documents'), $fileName);
            $employee->documents = $fileName;
            $employee->save();
        }

        // 5. Create Service Record (Always create NEW service for New Hire or Rehire)
        // If Rehiring, the old service records are already there with status 0 (presumably)
        // We ensure previous active services are closed just in case
        Service::where('p_id', $p_id)->where('status', 1)->update(['status' => 0, 'end_date' => date('Y-m-d')]);

        Service::create([
            'p_id' => $p_id,
            'department' => $request->department,
            'employment_type' => $request->employment_type,
            'role' => $request->role,
            'pay_type' => $request->pay_type,
            'consolidated_pay' => $request->consolidated_pay,
            'basic_pay' => $request->basic_pay,
            'da' => $request->da,
            'hra' => $request->hra,
            'start_date' => $request->joining_date, // Start Date = Joining Date
            'status' => 1, // Active
            'pf_available' => $request->has('pf_available') && $request->pf_available !== 'false' ? 1 : 0,
            'pf_uan' => ($request->has('pf_available') && $request->pf_available !== 'false') ? $request->pf_uan : null,
        ]);

        // 6. Initialize Payroll (Idempotent-ish, or just create new if needed)
        // Check if payroll exists for this month to avoid dups? 
        // For now, simpler to just create as requested, maybe unique constraint handles it or we allow history.
        // The original code created it blindly, so we'll stick to that but maybe check first.
        $existingPayroll = Payroll::where('p_id', $p_id)->where('paymonth', date('F'))->where('year', date('Y'))->first();
        if (!$existingPayroll) {
            Payroll::create([
                'p_id' => $p_id,
                'paymonth' => date('F'),
                'year' => date('Y'),
                'basic_salary' => 0,
                'hra' => 0,
                'other_allowance' => 0,
                'gross_salary' => 0,
                'pf' => 0,
                'esi' => 0,
                'professional_tax' => 0,
                'total_deductions' => 0,
                'net_salary' => 0,
            ]);
        }

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
    $employee = ProjectEmployee::where('id', $id)
      ->with(['salary', 'deduction', 'designation', 'user'])
      ->first();

    if ($employee) {
        $employee->service = $employee->services()->orderBy('id', 'desc')->first();
    }

    return response()->json($employee);
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