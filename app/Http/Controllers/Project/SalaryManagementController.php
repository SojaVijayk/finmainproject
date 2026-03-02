<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\ProjectEmployee;
use Illuminate\Support\Facades\DB;

class SalaryManagementController extends Controller
{
    public function index($project_id = null)
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        
        $employmentTypes = [
            'Apprentice', 'Daily Wages', 'Interns', 'Contract', 
            'Full Time', 'Part Time', 'Freelance', 'Temporary', 'Permanent', 'Deputation'
        ];
            
        $years = range(date('Y'), date('Y') - 5);
        $months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        return view('content.projects.salary-management.index', compact('employmentTypes', 'years', 'months', 'pageConfigs', 'project_id'));
    }

    public function selectEmployees(Request $request, $project_id = null)
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        $month = $request->month;
        $year = $request->year;
        $employmentType = $request->employment_type;
        $defaultSalaryId = $request->default_salary_id;

        if (empty($month) || empty($year) || empty($employmentType)) {
            return redirect()->route('pms.salary-management.index', $project_id)->with('error', 'Payroll details are missing. Please enter them again.');
        }

        // Calculate Month Start and End
        $mStart = \Carbon\Carbon::parse("1 $month $year")->startOfMonth();
        $mEnd = \Carbon\Carbon::parse("1 $month $year")->endOfMonth();
        $monthStartStr = $mStart->format('Y-m-d');
        $monthEndStr = $mEnd->format('Y-m-d');

        // Fetch all service records that overlap with this month for the given employment type
        $overlappingServices = \App\Models\Service::where('employment_type', $employmentType)
            ->where('start_date', '<=', $monthEndStr)
            ->where(function($q) use ($monthStartStr) {
                $q->where('end_date', '>=', $monthStartStr)
                  ->orWhereNull('end_date');
            })
            ->get();

        // Group by p_id and find the record with the MAXIMUM overlap days in this month
        $bestServiceIds = [];
        foreach ($overlappingServices->groupBy('p_id') as $p_id => $services) {
            $maxOverlap = -1;
            $bestId = null;

            foreach ($services as $srv) {
                $sStart = \Carbon\Carbon::parse($srv->start_date);
                $sEnd = $srv->end_date ? \Carbon\Carbon::parse($srv->end_date) : $mEnd;

                $overlapStart = $sStart->gt($mStart) ? $sStart : $mStart;
                $overlapEnd = $sEnd->lt($mEnd) ? $sEnd : $mEnd;
                
                $overlapDays = $overlapStart->diffInDays($overlapEnd) + 1;
                
                $isStatus1 = ($srv->status == 1);
                $isCurrentBestStatus1 = ($bestId && \App\Models\Service::find($bestId)->status == 1);

                if ($isStatus1 && !$isCurrentBestStatus1) {
                    $maxOverlap = $overlapDays;
                    $bestId = $srv->id;
                } elseif ($isStatus1 == $isCurrentBestStatus1) {
                    if ($overlapDays > $maxOverlap) {
                        $maxOverlap = $overlapDays;
                        $bestId = $srv->id;
                    } elseif ($overlapDays == $maxOverlap && $bestId && $srv->id > $bestId) {
                        $bestId = $srv->id;
                    }
                }
            }
            if ($bestId) $bestServiceIds[] = $bestId;
        }

        // Fetch employees using the specific filtered service records
        $query = ProjectEmployee::join('service', 'service.p_id', '=', 'project_employee.p_id')
            ->whereIn('service.id', $bestServiceIds);

        if ($project_id) {
            $query->where('project_employee.project_id', $project_id);
        }

        $employees = $query->select('project_employee.*', 'service.role', 'service.department', 'service.consolidated_pay')
            ->get();

        // Fetch frozen status for this month/year for the filtered employees
        $pIds = $employees->pluck('p_id');
        $payrolls = \DB::table('employee_payroll')
            ->whereIn('p_id', $pIds)
            ->where('paymonth', $month)
            ->where('year', $year)
            ->select('p_id', 'is_frozen')
            ->get()
            ->keyBy('p_id');

        foreach ($employees as $employee) {
            $employee->is_frozen = $payrolls->has($employee->p_id) ? $payrolls->get($employee->p_id)->is_frozen : 0;
        }

        return view('content.projects.salary-management.select-employees', compact('employees', 'month', 'year', 'employmentType', 'pageConfigs', 'project_id', 'defaultSalaryId'));
    }

    public function calculation(Request $request, $project_id = null)
    {
        set_time_limit(300); // Increase execution time to 5 minutes
        $pageConfigs = ['myLayout' => 'horizontal'];
        $month = $request->month;
        $year = $request->year;
        $employmentType = $request->employment_type;
        $defaultSalaryId = $request->default_salary_id;
        $selectedIds = $request->selected_employees ?? [];

        if (empty($month) || empty($year) || empty($employmentType)) {
            return redirect()->route('pms.salary-management.index', $project_id)->with('error', 'Payroll details (Month, Year, Type) are missing. Please start over.');
        }

        // Standardize divisor to 31 for the user's specific proration model (Feb = Jan * 28/31)
        // Add "1" to ensure we parse the 1st of the month, avoiding overflow issues
        $actualDaysInMonth = \Carbon\Carbon::parse("1 $month $year")->daysInMonth;
        $totalDays = $actualDaysInMonth; // Default to actual days for 100% pay on full month

        if (empty($selectedIds)) {
            return redirect()->back()->with('error', 'Please select at least one employee.');
        }

        $mStart = \Carbon\Carbon::parse("$month $year")->startOfMonth();
        $mEnd = \Carbon\Carbon::parse("$month $year")->endOfMonth();
        $monthStartStr = $mStart->format('Y-m-d');
        $monthEndStr = $mEnd->format('Y-m-d');

        // Same Max Overlap Filtering Logic
        $overlappingServices = \App\Models\Service::where('employment_type', $employmentType)
            ->where('start_date', '<=', $monthEndStr)
            ->where(function($q) use ($monthStartStr) {
                $q->where('end_date', '>=', $monthStartStr)
                  ->orWhereNull('end_date');
            })
            ->get();

        $bestServiceIds = [];
        foreach ($overlappingServices->groupBy('p_id') as $p_id => $services) {
            $maxOverlap = -1;
            $bestId = null;

            foreach ($services as $srv) {
                $sStart = \Carbon\Carbon::parse($srv->start_date);
                $sEnd = $srv->end_date ? \Carbon\Carbon::parse($srv->end_date) : $mEnd;

                $overlapStart = $sStart->gt($mStart) ? $sStart : $mStart;
                $overlapEnd = $sEnd->lt($mEnd) ? $sEnd : $mEnd;
                
                $overlapDays = $overlapStart->diffInDays($overlapEnd) + 1;
                
                $isStatus1 = ($srv->status == 1);
                $isCurrentBestStatus1 = ($bestId && \App\Models\Service::find($bestId)->status == 1);

                if ($isStatus1 && !$isCurrentBestStatus1) {
                    $maxOverlap = $overlapDays;
                    $bestId = $srv->id;
                } elseif ($isStatus1 == $isCurrentBestStatus1) {
                    if ($overlapDays > $maxOverlap) {
                        $maxOverlap = $overlapDays;
                        $bestId = $srv->id;
                    } elseif ($overlapDays == $maxOverlap && $bestId && $srv->id > $bestId) {
                        $bestId = $srv->id;
                    }
                }
            }
            if ($bestId) $bestServiceIds[] = $bestId;
        }

        $query = ProjectEmployee::join('service', 'service.p_id', '=', 'project_employee.p_id')
            ->whereIn('service.id', $bestServiceIds)
            ->whereIn('project_employee.p_id', $selectedIds);

        if ($project_id) {
            $query->where('project_employee.project_id', $project_id);
        }

        $employees = $query->leftJoin('designations', 'designations.id', '=', 'project_employee.designation_id')
            ->select('project_employee.*', 'service.role', 'service.department', 'service.consolidated_pay', 'designations.designation')
            ->get();

        // Batch Fetch Leaves for all employees
        $pIds = $employees->pluck('p_id');
        $allLeaves = \DB::table('leave_request_details')
            ->whereIn('user_id', $pIds)
            ->where('status', 1) // Approved
            ->whereBetween('date', [$monthStartStr, $monthEndStr])
            ->select('user_id', 'leave_type_id', \DB::raw('SUM(leave_duration) as total_days'))
            ->groupBy('user_id', 'leave_type_id')
            ->get()
            ->groupBy('user_id');

        // Map leaves to employees
        foreach ($employees as $employee) {
            $leaveStats = $allLeaves->get($employee->p_id, collect())->pluck('total_days', 'leave_type_id');

            $employee->cl_days = $leaveStats[1] ?? 0;
            $employee->sl_days = $leaveStats[2] ?? 0;
            $employee->pl_days = $leaveStats[3] ?? 0;
            $employee->lop_days = $leaveStats[5] ?? 0;
            $employee->other_leave_days = ($leaveStats[4] ?? 0); 
            
            // Any other leave types not mapped explicitly
            foreach($leaveStats as $tid => $dur) {
                if (!in_array($tid, [1,2,3,5])) {
                    if ($tid != 4) $employee->other_leave_days += (float)$dur;
                }
            }
        }

        // Fetch is_frozen status for the calculation view
        $payrolls = \DB::table('employee_payroll')
            ->whereIn('p_id', $pIds)
            ->where('paymonth', $month)
            ->where('year', $year)
            ->select('p_id', 'is_frozen')
            ->get()
            ->keyBy('p_id');

        foreach ($employees as $employee) {
            $employee->is_frozen = $payrolls->has($employee->p_id) ? $payrolls->get($employee->p_id)->is_frozen : 0;
        }

        return view('content.projects.salary-management.calculation', compact('employees', 'month', 'year', 'employmentType', 'pageConfigs', 'project_id', 'defaultSalaryId', 'totalDays', 'actualDaysInMonth'));
    }

    public function summary(Request $request, $project_id = null)
    {
        if ($request->isMethod('get')) {
            return redirect()->route('pms.salary-management.index', $project_id);
        }

        $pageConfigs = ['myLayout' => 'horizontal'];
        $month = $request->month;
        $year = $request->year;
        $employmentType = $request->employment_type;
        $p_ids = $request->p_id ?? [];
        $workingDays = $request->monthly_working_days ?? [];
        $daysWorked = $request->days_worked ?? [];
        $clDays = $request->cl_days ?? [];
        $slDays = $request->sl_days ?? [];
        $plDays = $request->pl_days ?? [];
        $lopDays = $request->lop_days ?? [];
        $otherLeaveDays = $request->other_leave_days ?? [];
        $salaryIds = $request->salary_id ?? [];
        $arrears = $request->arrear ?? [];
        $employeeContributions = [];
        $employerContributions = $request->employer_contribution ?? [];
        $epfEmployersShare = $request->epf_employers_share ?? [];
        $edliCharges = $request->edli_charges ?? [];
        $pfs = $request->pf ?? [];
        $totalSalaries = $request->total_salary ?? [];

        $monthStart = \Carbon\Carbon::parse("1 $month $year")->startOfMonth();
        $monthEnd = \Carbon\Carbon::parse("1 $month $year")->endOfMonth();
        $monthStartStr = $monthStart->format('Y-m-d');
        $monthEndStr = $monthEnd->format('Y-m-d');
        $actualDaysInMonth = $monthStart->daysInMonth;

        // Fetch is_frozen status for the summary view
        $payrolls = \DB::table('employee_payroll')
            ->whereIn('p_id', $p_ids)
            ->where('paymonth', $month)
            ->where('year', $year)
            ->select('p_id', 'is_frozen')
            ->get()
            ->keyBy('p_id');

        $summaryData = [];
        foreach ($p_ids as $index => $p_id) {
            $employee = ProjectEmployee::where('p_id', $p_id)->first();
            
            // Re-fetch the BEST service record to ensure base_salary is accurate
            $overlappingServices = \App\Models\Service::where('p_id', $p_id)
                ->where('employment_type', $employmentType)
                ->where('start_date', '<=', $monthEndStr)
                ->where(function($q) use ($monthStartStr) {
                    $q->where('end_date', '>=', $monthStartStr)
                      ->orWhereNull('end_date');
                })
                ->get();

            $maxOverlap = -1;
            // Find employee directly
            $employee = \App\Models\ProjectEmployee::where('p_id', $p_id)->first();
            
            // Re-fetch best service for role/department (simplified here assuming Calculation already used the right base)
            $bestService = \App\Models\Service::where('p_id', $p_id)
                ->where('employment_type', $employmentType)
                ->where('status', 1)
                ->first();

            $baseSalary = $bestService ? (float)$bestService->consolidated_pay : 0;
            $workingDayCount = (float)($workingDays[$index] ?? $actualDaysInMonth);
            if ($workingDayCount <= 0) $workingDayCount = $actualDaysInMonth;
            
            $daysWorkedCount = (float)($daysWorked[$index] ?? 0);
            $cl = (float)($clDays[$index] ?? 0);
            $sl = (float)($slDays[$index] ?? 0);
            $pl = (float)($plDays[$index] ?? 0);
            $other = (float)($otherLeaveDays[$index] ?? 0);
            $lop = (float)($lopDays[$index] ?? 0);
            $arrear = (float)($arrears[$index] ?? 0);
            $employee_contribution = 0; // Deprecated
            $employer_contribution = (float)($employerContributions[$index] ?? 0);

            // Trust the calculated total from the frontend JavaScript (handles manual overrides) 
            // The user explicitly requested that Employee Contribution NOT be deducted from the Total Salary anywhere.
            $finalTotal = (float)($totalSalaries[$index] ?? 0);

            $summaryData[] = [
                'p_id' => $p_id,
                'name' => $employee->name ?? 'N/A',
                'role' => $bestService->role ?? 'N/A',
                'department' => $bestService->department ?? 'N/A',
                'working_days' => $workingDayCount,
                'days_worked' => $daysWorkedCount,
                'cl_days' => $cl,
                'sl_days' => $sl,
                'pl_days' => $pl,
                'lop_days' => $lop,
                'other_leave_days' => $other,
                'salary_id' => $salaryIds[$index] ?? '',
                'arrear' => $arrear,
                'employee_contribution' => $employee_contribution,
                'employer_contribution' => $employer_contribution,
                'epf_employers_share' => (float)($epfEmployersShare[$index] ?? 0),
                'edli_charges' => (float)($edliCharges[$index] ?? 0),
                'pf' => (float)($pfs[$index] ?? 0),
                'base_salary' => $baseSalary,
                'total_salary' => $finalTotal,
                'is_frozen' => $payrolls->has($p_id) ? $payrolls->get($p_id)->is_frozen : 0,
                'bank_name' => collect(explode('|', $employee->bank_name))->last() ?? '',
                'account_no' => $employee->account_no ?? '',
                'ifsc_code' => $employee->ifsc_code ?? ''
            ];
        }

        return view('content.projects.salary-management.summary', compact('summaryData', 'month', 'year', 'employmentType', 'pageConfigs', 'project_id', 'actualDaysInMonth'));
    }

    public function store(Request $request, $project_id = null)
    {
        set_time_limit(300); // Increase execution time to 5 minutes

        $month = $request->month;
        $year = $request->year;
        $employeeIds = $request->p_id ?? [];
        $workingDays = $request->monthly_working_days ?? [];
        $daysWorked = $request->days_worked ?? [];
        $clDays = $request->cl_days ?? [];
        $slDays = $request->sl_days ?? [];
        $plDays = $request->pl_days ?? [];
        $lopDays = $request->lop_days ?? [];
        $otherLeaveDays = $request->other_leave_days ?? [];
        $arrears = $request->arrear ?? [];
        $employeeContributions = [];
        $employerContributions = $request->employer_contribution ?? [];
        $totalSalaries = $request->total_salary ?? [];
        $isFrozen = $request->has('freeze') && $request->freeze == '1' ? 1 : 0;

        $monthStart = \Carbon\Carbon::parse("1 $month $year")->startOfMonth();
        $monthEnd = \Carbon\Carbon::parse("1 $month $year")->endOfMonth();
        $monthStartStr = $monthStart->format('Y-m-d');
        $monthEndStr = $monthEnd->format('Y-m-d');
        $employmentType = $request->employment_type;

        DB::beginTransaction();
        try {
            // Batch fetch all services for all selected employees
            $allServices = \App\Models\Service::whereIn('p_id', $employeeIds)
                ->where('employment_type', $employmentType)
                ->where('start_date', '<=', $monthEndStr)
                ->where(function($q) use ($monthStartStr) {
                    $q->where('end_date', '>=', $monthStartStr)
                      ->orWhereNull('end_date');
                })
                ->get()
                ->groupBy('p_id');

            // Pre-fetch existing payrolls to strictly enforce the freeze lock at the DB level
            $existingPayrolls = \App\Models\Payroll::whereIn('p_id', $employeeIds)
                ->where('paymonth', $month)
                ->where('year', $year)
                ->get()
                ->keyBy('p_id');

            foreach ($employeeIds as $index => $p_id) {
                // HARD LOCK: Skip processing if already frozen
                if ($existingPayrolls->has($p_id) && $existingPayrolls->get($p_id)->is_frozen == 1) {
                    continue; 
                }

                // Re-fetch the BEST service record (from memory)
                $overlappingServices = $allServices->get($p_id, collect());

                $maxOverlap = -1;
                $bestService = null;
                foreach ($overlappingServices as $srv) {
                    $sStart = \Carbon\Carbon::parse($srv->start_date);
                    $sEnd = $srv->end_date ? \Carbon\Carbon::parse($srv->end_date) : $monthEnd;
                    $overlapStart = $sStart->gt($monthStart) ? $sStart : $monthStart;
                    $overlapEnd = $sEnd->lt($monthEnd) ? $sEnd : $monthEnd;
                    $overlapDays = $overlapStart->diffInDays($overlapEnd) + 1;
                    
                    $isStatus1 = ($srv->status == 1);
                    $isCurrentBestStatus1 = ($bestService && $bestService->status == 1);

                    if ($isStatus1 && !$isCurrentBestStatus1) {
                        $maxOverlap = $overlapDays;
                        $bestService = $srv;
                    } elseif ($isStatus1 == $isCurrentBestStatus1) {
                        if ($overlapDays > $maxOverlap) {
                            $maxOverlap = $overlapDays;
                            $bestService = $srv;
                        } elseif ($overlapDays == $maxOverlap && $bestService && $srv->id > $bestService->id) {
                            $bestService = $srv;
                        }
                    }
                }

                $baseSalary = $bestService ? (float)$bestService->consolidated_pay : 0;
                $workingDayCount = (float)($workingDays[$index] ?? $actualDaysInMonth);
                if ($workingDayCount <= 0) $workingDayCount = $actualDaysInMonth; // Prevent division by zero

                // --- UPDATED LOGIC TO MATCH FRONTEND MANIPULATIONS ---
                $daysWorkedCount = (float)($daysWorked[$index] ?? 0);
                $cl = (float)($clDays[$index] ?? 0);
                $sl = (float)($slDays[$index] ?? 0);
                $pl = (float)($plDays[$index] ?? 0);
                $other = (float)($otherLeaveDays[$index] ?? 0);
                $lop = (float)($lopDays[$index] ?? 0);
                $arrear = (float)($arrears[$index] ?? 0);
                $employee_contribution = 0; // Deprecated
                $employer_contribution = (float)($employerContributions[$index] ?? 0);

                // New Detailed Calculation Fields
                $epf_employers_share = (float)($request->epf_employers_share[$index] ?? 0);
                $edli_charges = (float)($request->edli_charges[$index] ?? 0);
                $pf = (float)($request->pf[$index] ?? 0);

                // Trust the calculated total from the frontend JavaScript
                // User explicitly requested NO deductions to the total salary.
                $netSalary = (float)($totalSalaries[$index] ?? 0);

                \App\Models\Payroll::updateOrCreate(
                    [
                        'p_id' => $p_id,
                        'paymonth' => $month,
                        'year' => $year,
                    ],
                    [
                        'salary_id' => $request->salary_id[$index] ?? null,
                        'total_working_days' => $workingDayCount,
                        'days_worked' => $daysWorkedCount,
                        'cl_days' => $cl,
                        'sl_days' => $sl,
                        'pl_days' => $pl,
                        'lop_days' => $lop,
                        'other_leave_days' => $other,
                        'gross_salary' => $baseSalary,
                        'net_salary' => $netSalary,
                        'pf' => $pf, 
                        'employee_contribution' => $employee_contribution,
                        'employer_contribution' => $employer_contribution,
                        'epf_employers_share' => $epf_employers_share,
                        'edli_charges' => $edli_charges,
                        'other_allowance' => $arrear, // Mapping Arrear to Other Allowance
                        'is_frozen' => $isFrozen,
                    ]
                );
            }
            DB::commit();
            
            $redirect = route('pms.salary-management.index', $project_id);
            $msg = $isFrozen ? 'Payroll frozen successfully ' : 'Payroll processed successfully ';
            
            if ($request->ajax()) {
                $response = ['success' => true, 'message' => $msg . 'for ' . $month . ' ' . $year];
                if ($isFrozen) {
                    $response['redirect_url'] = route('pms.employees.project-index', $project_id);
                }
                return response()->json($response);
            }

            return redirect($redirect)->with('success', $msg . 'for ' . $month . ' ' . $year);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Error processing payroll: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Error processing payroll: ' . $e->getMessage());
        }
    }
    public function statement(Request $request, $project_id, $month, $year, $employment_type)
    {
        $pageConfigs = ['myLayout' => 'blank'];
        $project = \App\Models\Project::find($project_id);

        $query = \App\Models\Payroll::where('paymonth', $month)
            ->where('year', $year);

        if ($request->has('p_ids')) {
            $pIds = explode(',', $request->query('p_ids'));
            $query->whereIn('p_id', $pIds);
        }

        // Determine which columns to show
        $columns = ['slno', 'name', 'designation', 'doj', 'arrear', 'deduction', 'payable']; // Defaults updated to match requested order
        if ($request->has('columns')) {
            $columns = explode(',', $request->query('columns'));
        }

        $note = $request->query('note', '');

        $payrolls = $query->get();

        $statementData = [];
        $mStart = \Carbon\Carbon::parse("$month $year")->startOfMonth();
        $mEnd = \Carbon\Carbon::parse("$month $year")->endOfMonth();
        
        $startDateStr = $mStart->format('d.m.Y');
        $endDateStr = $mEnd->format('d.m.Y');

        foreach ($payrolls as $payroll) {
            // Check Project
            $emp = \App\Models\ProjectEmployee::where('p_id', $payroll->p_id)
                ->where('project_id', $project_id)
                ->first();

            if (!$emp) continue;

            // Check Employment Type & Get Designation (Best Service)
            $service = \App\Models\Service::where('p_id', $payroll->p_id)
                ->where('employment_type', $employment_type)
                ->where('start_date', '<=', $mEnd->format('Y-m-d'))
                ->where(function($q) use ($mStart) {
                    $q->where('end_date', '>=', $mStart->format('Y-m-d'))
                      ->orWhereNull('end_date');
                })
                ->orderBy('id', 'desc')
                ->first();

            if (!$service) continue;

            // Calculate Remuneration (Prorated Base)
            $net = $payroll->net_salary;
            $arrear = $payroll->other_allowance;
            $employer_contribution = $payroll->employer_contribution ?? 0;
            $employee_contribution = $payroll->employee_contribution ?? 0; // NEW
            $epf = $payroll->epf ?? 0; // NEW
            $epf_employers_share = $payroll->epf_employers_share ?? 0;
            $edli_charges = $payroll->edli_charges ?? 0;
            $pf = $payroll->pf ?? 0;
            $doj = $emp->date_of_joining ? \Carbon\Carbon::parse($emp->date_of_joining)->format('d-m-Y') : '-';
            
            // Remuneration is fundamentally the Net Pay minus any added Arrears, 
            // since deductions are no longer actively recorded or subtracted.
            $remuneration = round($net - $arrear, 2);

            $statementData[] = (object)[
                'name' => $emp->name,
                'designation' => $service->role,
                'doj' => $doj,
                'arrears' => $arrear,
                'remuneration' => $remuneration,
                'epf_employers_share' => $epf_employers_share,
                'edli_charges' => $edli_charges,
                'pf' => $pf,
                'epf' => $epf, // NEW
                'deductions' => 0, // No active deductions
                'employee_contribution' => $employee_contribution, // NEW
                'employer_contribution' => $employer_contribution,
                'payable' => $net
            ];
        }

        return view('content.projects.salary-management.salary-statement', compact('statementData', 'month', 'year', 'pageConfigs', 'project', 'columns', 'startDateStr', 'endDateStr', 'note'));
    }
}
