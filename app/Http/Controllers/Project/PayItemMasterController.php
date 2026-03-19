<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\PayItem;
use App\Models\PayItemSlab;
use App\Models\EmployeePayBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class PayItemMasterController extends Controller
{
    public function index($project_id = null)
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        
        // Defensive check: if project_id is not numeric (e.g. "statement" due to route collision)
        // or if it's null, default to project 1.
        if (!$project_id || !is_numeric($project_id)) {
            $project_id = 1;
        }

        $project = \App\Models\Project::findOrFail($project_id);
        $payItems = PayItem::with('slabs')->get();
        
        $statementData = null;
        $payItem = null;
        $periodLabel = null;
        $salaryId = null;
        $selectedEmploymentType = null;

        // --- Session Persistence Logic ---
        $filterFields = ['pay_item_id', 'month', 'year', 'to_month', 'to_year', 'salary_id', 'employment_type'];
        foreach ($filterFields as $field) {
            if (request()->has($field)) {
                session(["pay_item_filter_{$field}" => request($field)]);
            } elseif (session()->has("pay_item_filter_{$field}")) {
                request()->merge([$field => session("pay_item_filter_{$field}")]);
            }
        }
        // ---------------------------------
        
        // Check for embedded statement request
        if (request()->has('show_summary') && request()->has('p_ids')) {
            $summaryRes = $this->getStatementData(request());
            $statementData = $summaryRes['statementData'];
            $payItem = $summaryRes['payItem'];
            $periodLabel = $summaryRes['periodLabel'];
            $salaryId = $summaryRes['salaryId'];
            $selectedEmploymentType = $summaryRes['selectedEmploymentType'];
        }

        $employmentTypes = \App\Models\EmploymentType::where('status', 1)->get();
        
        return view('content.projects.pay-item-master.index', compact(
            'project_id', 'project', 'payItems', 'pageConfigs', 
            'statementData', 'payItem', 'periodLabel', 'salaryId',
            'employmentTypes', 'selectedEmploymentType'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'type'          => 'required|in:Deduction,Allowance,Recovery',
            'is_slab_based' => 'nullable',
            'status'        => 'nullable',
        ]);

        $data = [
            'name'          => $request->name,
            'type'          => $request->type,
            'is_slab_based' => $request->has('is_slab_based') ? 1 : 0,
            'status'        => $request->has('status') ? 1 : 0,
        ];

        // Separate create vs update to avoid updateOrCreate id=null matching all rows
        if ($request->filled('id')) {
            $payItem = PayItem::findOrFail($request->id);
            $payItem->update($data);
        } else {
            $payItem = PayItem::create($data);
        }

        // Sync slabs — delete existing and re-insert submitted ones
        $payItem->slabs()->delete();

        $slabFroms = $request->input('slab_from', []);
        $slabTos   = $request->input('slab_to', []);
        $slabAmts  = $request->input('slab_amount', []);

        foreach ($slabFroms as $i => $from) {
            $to  = $slabTos[$i]  ?? '';
            $amt = $slabAmts[$i] ?? '';
            // Only save rows that have all three values filled
            if ($from !== '' && $from !== null && $to !== '' && $to !== null && $amt !== '' && $amt !== null) {
                PayItemSlab::create([
                    'pay_item_id' => $payItem->id,
                    'salary_from' => (float) $from,
                    'salary_to'   => (float) $to,
                    'amount'      => (float) $amt,
                ]);
            }
        }

        $message = $request->filled('id') ? 'Pay Item updated successfully.' : 'Pay Item created successfully.';
        return redirect()->back()->with('success', $message);
    }

    public function destroy($id)
    {
        PayItem::findOrFail($id)->delete(); // slabs cascade via FK
        return redirect()->back()->with('success', 'Pay Item deleted successfully.');
    }

    public function storeDraftBill(Request $request)
    {
        $request->validate([
            'pay_item_id' => 'required|exists:pay_items,id',
            'month'       => 'required|string',
            'year'        => 'required|integer',
            'to_month'    => 'nullable|string',
            'to_year'     => 'nullable|integer',
            'project_id'  => 'nullable',
            'employment_type' => 'nullable|string',
            'salary_id'   => 'required|string|max:255'
        ]);

        // FK constraint on project_id dropped in migration 2026_03_18_151000
        // Just store the numeric project_id as-is, null if blank
        $projectId = is_numeric($request->project_id) ? (int)$request->project_id : null;

        $bill = EmployeePayBill::updateOrCreate(
            ['salary_id' => $request->salary_id],
            [
                'pay_item_id'     => $request->pay_item_id,
                'project_id'      => $projectId,
                'month'           => $request->month,
                'year'            => $request->year,
                'to_month'        => $request->to_month,
                'to_year'         => $request->to_year,
                'employment_type' => $request->employment_type,
                'status'          => 'Draft'
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Draft Pay Item Bill saved successfully.',
            'bill'    => $bill
        ]);
    }

    public function fetchExistingBills(Request $request, $project_id = null)
    {
        $project_id = $project_id ?? $request->project_id ?? 1;

        // Resolve Employment Type Name
        $employmentType = $request->employment_type;
        if (is_numeric($employmentType)) {
            $et = \App\Models\EmploymentType::find($employmentType);
            $employmentType = $et ? $et->employment_type : $employmentType;
        }

        $resolvedProjectId = is_numeric($project_id) ? (int)$project_id : null;

        $query = \App\Models\EmployeePayBill::with(['payItem', 'project'])
                    ->where('project_id', $resolvedProjectId);

        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }
        if ($employmentType) {
            $query->where('employment_type', $employmentType);
        }

        $batches = $query->orderBy('created_at', 'desc')->get();

        $formattedBatches = $batches->map(function ($bill) {
            $periodLabel = $bill->month . ' ' . $bill->year;
            if ($bill->to_month && $bill->to_year) {
                $periodLabel .= ' - ' . $bill->to_month . ' ' . $bill->to_year;
            }

            return [
                'salary_id'       => $bill->salary_id ?: 'Draft-' . $bill->id,
                'pay_item_name'   => $bill->payItem ? $bill->payItem->name : 'Unknown',
                'raw_pay_item_id' => $bill->pay_item_id,
                'period_label'    => $periodLabel,
                'raw_month'       => $bill->month,
                'raw_year'        => $bill->year,
                'raw_to_month'    => $bill->to_month,
                'raw_to_year'     => $bill->to_year,
                'employment_type' => $bill->employment_type ?: 'All Types',
                'status'          => $bill->status,
                'employee_count'  => $bill->details()->count()
            ];
        });

        return response()->json(['success' => true, 'batches' => $formattedBatches]);
    }

    public function generateBillList(Request $request)
    {
        try {
            $request->validate([
                'pay_item_id' => 'required',
                'month'       => 'required|string',
                'year'        => 'required|integer',
                'to_month'    => 'nullable|string',
                'to_year'     => 'nullable|integer',
                'project_id'  => 'nullable',
                'employment_type' => 'nullable|string'
            ]);

            $payItem = PayItem::with('slabs')->findOrFail($request->pay_item_id);
            $isRange = $request->filled('to_month') && $request->filled('to_year');
            $projectId = $request->project_id;
            $destColumn = $this->resolveDestinationColumn($payItem);
            
            $monthOrder = [
                'January' => 1, 'February' => 2, 'March' => 3, 'April' => 4,
                'May' => 5, 'June' => 6, 'July' => 7, 'August' => 8,
                'September' => 9, 'October' => 10, 'November' => 11, 'December' => 12
            ];

            $startMonth = $request->month;
            $startYear  = $request->year;
            $endMonth   = $isRange ? $request->to_month : $startMonth;
            $endYear    = $isRange ? $request->to_year : $startYear;

            if (!isset($monthOrder[$startMonth]) || !isset($monthOrder[$endMonth])) {
                return response()->json(['success' => false, 'message' => 'Invalid month selected.']);
            }

            $startMonthNum = $monthOrder[$startMonth];
            $endMonthNum   = $monthOrder[$endMonth];
            $startVal = ($startYear * 100) + $startMonthNum;
            $endVal   = ($endYear * 100) + $endMonthNum;

            $periodText = $isRange ? "{$startMonth} {$startYear} - {$endMonth} {$endYear}" : "{$startMonth} {$startYear}";

            \Log::info('PayItem Generate List Params', [
                'pay_item_id' => $request->pay_item_id,
                'project_id'  => $projectId,
                'period'      => $periodText
            ]);

            $query = \DB::table('project_employee')
                ->leftJoin('service', 'project_employee.p_id', '=', 'service.p_id')
                ->leftJoin('employment_types', 'service.employment_type', '=', 'employment_types.employment_type')
                ->leftJoin('salary', 'project_employee.p_id', '=', 'salary.p_id')
                ->whereExists(function ($q) {
                    $q->select(\DB::raw(1))
                      ->from('employee_payroll')
                      ->whereRaw('employee_payroll.p_id = project_employee.p_id')
                      ->where('employee_payroll.is_frozen', 1);
                });
            
            if ($projectId) {
                // Ensure we cast to integer if it looks like one, to be safe with DB types
                $filterId = is_numeric($projectId) ? (int)$projectId : $projectId;
                $query->where('project_employee.project_id', $filterId);
            }

            if ($request->filled('employment_type')) {
                $query->where('service.employment_type', $request->employment_type);
            }

            // The list is now based on ANY freeze record in the project history, 
            // as requested: "show all freeze employees, don't use their month".

            $debugInfo = [
                'total_employees_assigned_to_project' => \DB::table('project_employee')
                    ->where('project_id', is_numeric($projectId) ? (int)$projectId : $projectId)
                    ->count(),
                'project_id_used' => $projectId,
                'period_selected_on_page' => [
                    'start_month' => $startMonth,
                    'start_year'  => $startYear,
                    'end_month'   => $endMonth,
                    'end_year'    => $endYear,
                    'is_range'    => $isRange
                ],
                'sql_logic' => 'Listing all Project Employees who have at least ONE Freeze record at any time.',
                'total_freeze_employees_in_project' => \DB::table('project_employee')
                    ->whereExists(function ($q) {
                        $q->select(\DB::raw(1))
                          ->from('employee_payroll')
                          ->whereRaw('employee_payroll.p_id = project_employee.p_id')
                          ->where('employee_payroll.is_frozen', 1);
                    })
                    ->where('project_id', is_numeric($projectId) ? (int)$projectId : $projectId)
                    ->count()
            ];

            $employees = $query->select(
                    'project_employee.p_id',
                    'project_employee.name as employee_name',
                    'project_employee.status as current_status',
                    'project_employee.employment_type as pe_type',
                    'service.employment_type as svc_type',
                    'employment_types.employment_type as et_label',
                    'project_employee.date_of_joining',
                    'salary.gross_salary as master_gross',
                    'salary.basic_pay as master_basic',
                    'salary.da as master_da',
                    'service.consolidated_pay'
                )
                ->distinct()
                ->orderBy('project_employee.name')
                ->get();
            
            $debugInfo['freeze_employees_found'] = $employees->count();
            
            \Log::info('PayItem Generate List Results', [
                'raw_count' => $employees->count()
            ]);
                
            // Due to distinct(), if a user somehow accumulated functionally identical service rows, they might 
            // slip through. We'll enforce one row per P_ID in PHP just to be absolutely bulletproof.
            $employees = $employees->unique('p_id')->values();

            if ($employees->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'pay_item' => ['name' => $payItem->name, 'type' => $payItem->type],
                    'employees' => [],
                    'period_text' => $periodText,
                    'debug' => $debugInfo
                ]);
            }

            $pIds = $employees->pluck('p_id')->toArray();

            // Fetch existing payroll records for the range
            $salaryRecordsQuery = \DB::table('employee_payroll')
                ->whereIn('p_id', $pIds)
                ->select(
                    'p_id',
                    'salary_id',
                    'gross_salary',
                    'basic_pay',
                    'da',
                    'total_working_days',
                    'days_worked',
                    'other_allowance',
                    'paymonth',
                    'year',
                    $destColumn
                );

            $salaryRecordsQuery->whereRaw("(year * 100 + CASE 
                WHEN paymonth = 'January' THEN 1 WHEN paymonth = 'February' THEN 2 
                WHEN paymonth = 'March' THEN 3 WHEN paymonth = 'April' THEN 4 
                WHEN paymonth = 'May' THEN 5 WHEN paymonth = 'June' THEN 6 
                WHEN paymonth = 'July' THEN 7 WHEN paymonth = 'August' THEN 8 
                WHEN paymonth = 'September' THEN 9 WHEN paymonth = 'October' THEN 10 
                WHEN paymonth = 'November' THEN 11 WHEN paymonth = 'December' THEN 12 
                END) BETWEEN ? AND ?", [$startVal, $endVal]);

            $salaries = $salaryRecordsQuery->get()->groupBy('p_id');
            
            // Also get the LATEST FREEZE payroll record for EACH employee as the "Actual Salary" detail
            $latestPayroll = \DB::table('employee_payroll')
                ->whereIn('p_id', $pIds)
                ->where('is_frozen', 1)
                ->orderBy('year', 'desc')
                ->orderByRaw("CASE 
                    WHEN paymonth = 'January' THEN 1 WHEN paymonth = 'February' THEN 2 
                    WHEN paymonth = 'March' THEN 3 WHEN paymonth = 'April' THEN 4 
                    WHEN paymonth = 'May' THEN 5 WHEN paymonth = 'June' THEN 6 
                    WHEN paymonth = 'July' THEN 7 WHEN paymonth = 'August' THEN 8 
                    WHEN paymonth = 'September' THEN 9 WHEN paymonth = 'October' THEN 10 
                    WHEN paymonth = 'November' THEN 11 WHEN paymonth = 'December' THEN 12 
                    END DESC")
                ->get()
                ->unique('p_id')
                ->keyBy('p_id');

            // Find all months in range for projection
            $rangeMonths = [];
            $currY = $startYear;
            $currM = $startMonthNum;
            while (($currY * 100 + $currM) <= $endVal) {
                $rangeMonths[] = ['year' => $currY, 'month_name' => array_search($currM, $monthOrder)];
                $currM++;
                if ($currM > 12) {
                    $currM = 1;
                    $currY++;
                }
            }

            $processedEmployees = [];

            foreach ($employees as $emp) {
                $empSalaries = $salaries[$emp->p_id] ?? collect();
                
                $cumulativeGross = 0;
                $latestSalaryId = 'N/A';
                
                // Determine "Base Salary" strictly from structural master records 
                // Ignore processed payrolls so prorated months (LOP) don't corrupt the projection
                $projBase = (float)($emp->master_gross ?? 0);
                
                if ($projBase <= 0) {
                    $projBase = (float)($emp->consolidated_pay ?? 0);
                }
                if ($projBase <= 0) {
                    $projBase = (float)($emp->master_basic ?? 0) + (float)($emp->master_da ?? 0);
                }
                
                $latestSalaryId = 'N/A';
                $lp = $latestPayroll->get($emp->p_id);
                if ($lp) {
                    $latestSalaryId = $lp->salary_id;
                }

                $numMonths = count($rangeMonths);
                $numMonths = $numMonths > 0 ? $numMonths : 1;
                $cumulativeGross = $projBase * $numMonths;

                $calculatedAmount = 0;
                $hasExistingAmount = false;

                // 1. Check existing draft details first
                if ($request->salary_id) {
                    $draftDetailQuery = \DB::table('employee_pay_bill_details')
                        ->join('employee_pay_bills', 'employee_pay_bills.id', '=', 'employee_pay_bill_details.employee_pay_bill_id')
                        ->where('employee_pay_bills.salary_id', $request->salary_id)
                        ->where('employee_pay_bill_details.p_id', $emp->p_id);
                    
                    if ($draftDetailQuery->exists()) {
                        $detail = $draftDetailQuery->first();
                        $calculatedAmount = (float)$detail->adjusted_amount;
                        $hasExistingAmount = true;
                    }
                }

                // Step 2 fallback removed, we exclusively rely on draft state OR fresh calculations.
                if (!$hasExistingAmount) {
                    if ($payItem->calculation_type === 'percentage') {
                        $pct = (float)$payItem->calculation_value;
                        if ($pct > 0) {
                            $calculatedAmount = round(($cumulativeGross * $pct) / 100, 2);
                        }
                    } elseif ($payItem->calculation_type === 'fixed') {
                        $calculatedAmount = (float)$payItem->calculation_value * $numMonths;
                    } elseif ($payItem->calculation_type === 'slab' || $payItem->is_slab_based) {
                        if ($payItem->slabs->isNotEmpty()) {
                            $matched = false;
                            $maxSlab = $payItem->slabs->sortByDesc('salary_to')->first();
                            
                            foreach ($payItem->slabs as $slab) {
                                if ($projBase >= $slab->salary_from && $projBase <= $slab->salary_to) {
                                    $calculatedAmount = $slab->amount * $numMonths;
                                    $matched = true;
                                    break;
                                }
                            }
                            
                            if (!$matched && $projBase > $maxSlab->salary_to) {
                                $calculatedAmount = $maxSlab->amount * $numMonths;
                            }
                        }
                    }
                }

                $statusLabel = $emp->current_status;
                if (is_numeric($statusLabel)) {
                    $statusLabel = ($statusLabel == 1) ? 'Active' : 'Inactive';
                }
                
                // Robust employment type label
                $typeLabel = $emp->et_label;
                if (!$typeLabel) {
                    $typeLabel = $emp->svc_type ?: ($emp->pe_type ?: 'N/A');
                }

                $processedEmployees[] = [
                    'p_id'             => $emp->p_id,
                    'salary_id'        => $latestSalaryId,
                    'employee_name'    => $emp->employee_name,
                    'current_status'   => $statusLabel,
                    'employment_type'  => $typeLabel,
                    'base_salary'      => $projBase,
                    'actual_salary'    => (float)($lp?->net_salary ?? 0),
                    'total_gross'      => $cumulativeGross,
                    'calculated_amount'=> $calculatedAmount,
                ];
            }

            if ($request->salary_id) {
                \App\Models\EmployeePayBill::where('salary_id', $request->salary_id)
                    ->where('status', 'Draft')
                    ->update(['status' => 'In Progress']);
            }

            return response()->json([
                'success'     => true,
                'pay_item'    => ['name' => $payItem->name, 'type' => $payItem->type],
                'employees'   => $processedEmployees,
                'period_text' => $periodText
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function storeBill(Request $request)
    {
        $request->validate([
            'pay_item_id' => 'required|exists:pay_items,id',
            'month'       => 'required|string',
            'year'        => 'required|integer',
            'to_month'    => 'nullable|string',
            'to_year'     => 'nullable|integer',
            'p_id'        => 'required|array',
            'amount'      => 'required|array',
            'base_salary' => 'required|array',
            'actual_salary' => 'required|array',
            'total_period_salary' => 'required|array',
            'salary_id'   => 'required|string|max:255'
        ]);

        $draftBill = \App\Models\EmployeePayBill::where('salary_id', $request->salary_id)->firstOrFail();
        
        if ($draftBill->status === 'Finalized') {
            return response()->json(['success' => false, 'message' => 'This bill is already finalized and cannot be modified.'], 403);
        }

        return \DB::transaction(function() use ($request, $draftBill) {
            $pIds = $request->p_id;
            $amounts = $request->amount;
            $baseSalaries = $request->base_salary;
            $actualSalaries = $request->actual_salary;
            $totalPeriodSalaries = $request->total_period_salary;

            $draftBill->details()->delete();

            $empDetails = \DB::table('project_employee')
                ->leftJoin('service', 'project_employee.p_id', '=', 'service.p_id')
                ->leftJoin('employment_types', 'service.employment_type', '=', 'employment_types.id')
                ->whereIn('project_employee.p_id', $pIds)
                ->select(
                    'project_employee.p_id',
                    'project_employee.name as employee_name',
                    'project_employee.status as current_status',
                    'project_employee.employment_type as pe_type',
                    'service.employment_type as svc_type',
                    'employment_types.employment_type as et_label'
                )
                ->get()
                ->keyBy('p_id');

            $insertData = [];
            $statementData = [];
            $now = now();
            foreach ($pIds as $pId) {
                // Ensure no negative values are saved
                $adjusted = max(0, (float)($amounts[$pId] ?? 0));
                $insertData[] = [
                    'employee_pay_bill_id' => $draftBill->id,
                    'p_id'                 => $pId,
                    'base_salary'          => max(0, (float)($baseSalaries[$pId] ?? 0)),
                    'actual_salary'        => max(0, (float)($actualSalaries[$pId] ?? 0)),
                    'total_period_salary'  => max(0, (float)($totalPeriodSalaries[$pId] ?? 0)),
                    'adjusted_amount'      => $adjusted,
                    'created_at'           => $now,
                    'updated_at'           => $now,
                ];

                $emp = $empDetails->get($pId);
                $statusLabel = $emp?->current_status ?? 'Unknown';
                if (is_numeric($statusLabel)) {
                    $statusLabel = ($statusLabel == 1) ? 'Active' : 'Inactive';
                }
                
                $typeLabel = $emp?->et_label;
                if (!$typeLabel) {
                    $typeLabel = $emp?->svc_type ?: ($emp?->pe_type ?: 'N/A');
                }

                $statementData[] = [
                    'p_id' => $pId,
                    'name' => $emp?->employee_name ?? 'Unknown Employee',
                    'status' => $statusLabel,
                    'type' => $typeLabel,
                    'base_salary' => max(0, (float)($baseSalaries[$pId] ?? 0)),
                    'actual_salary' => max(0, (float)($actualSalaries[$pId] ?? 0)),
                    'total_gross' => max(0, (float)($totalPeriodSalaries[$pId] ?? 0)),
                    'amount' => $adjusted
                ];
            }
            
            if (!empty($insertData)) {
                foreach (array_chunk($insertData, 500) as $chunk) {
                    \DB::table('employee_pay_bill_details')->insert($chunk);
                }
            }

            $draftBill->update(['status' => 'Saved']);

            if ($request->ajax()) {
                $totalAmt = array_sum(array_column($insertData, 'adjusted_amount'));
                $periodLabel = $request->month . ' ' . $request->year;
                if ($request->filled('to_month') && $request->filled('to_year')) {
                    $periodLabel .= ' - ' . $request->to_month . ' ' . $request->to_year;
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Pay Item Bill details saved successfully.',
                    'summary' => [
                        'salaryId' => $draftBill->salary_id,
                        'payItem' => ['name' => $draftBill->payItem->name, 'type' => $draftBill->payItem->type],
                        'periodLabel' => $periodLabel,
                        'selectedEmploymentType' => $draftBill->employment_type,
                        'projectTitle' => $draftBill->project ? $draftBill->project->project_title : 'N/A',
                        'totalAmount' => $totalAmt,
                        'statementData' => $statementData
                    ]
                ]);
            }

            return redirect()->back()->with('success', 'Bill saved successfully.');
        });
    }

    public function applyToDeductions(Request $request)
    {
        $request->validate(['salary_id' => 'required|string']);

        $bill = \App\Models\EmployeePayBill::with('details', 'payItem')
                    ->where('salary_id', $request->salary_id)
                    ->firstOrFail();

        if ($bill->status !== 'Finalized') {
            return response()->json([
                'success' => false,
                'message' => 'Only Finalized bills can be applied to Salary Deductions.'
            ], 422);
        }

        $payItem        = $bill->payItem;
        $destColumn     = $this->resolveDestinationColumn($payItem);
        $normalizedName = strtolower(trim($payItem->name));
        $isFA    = in_array($normalizedName, ['festival allowance', 'festival']);
        $isBonus = in_array($normalizedName, ['bonus', 'bonus allowance', 'salary bonus', 'incentive']);

        $monthOrder = [
            'January'=>1,'February'=>2,'March'=>3,'April'=>4,
            'May'=>5,'June'=>6,'July'=>7,'August'=>8,
            'September'=>9,'October'=>10,'November'=>11,'December'=>12
        ];
        $monthNames = array_flip($monthOrder);

        // Build the exact period months from the bill
        $billPeriods = [];
        if (!$bill->to_month || !$bill->to_year) {
            $billPeriods[] = ['month' => $bill->month, 'year' => $bill->year];
        } else {
            $curr = ($bill->year * 100) + $monthOrder[$bill->month];
            $end  = ($bill->to_year * 100) + $monthOrder[$bill->to_month];
            while ($curr <= $end) {
                $y = (int)($curr / 100); $m = $curr % 100;
                $billPeriods[] = ['month' => $monthNames[$m], 'year' => $y];
                $curr = ($m === 12) ? (($y + 1) * 100 + 1) : $curr + 1;
            }
        }
        $monthsCount = count($billPeriods);
        $now         = now();

        $pIds = $bill->details->pluck('p_id')->toArray();

        // Build a map: p_id => per-month-amount
        $perMonthMap = [];
        foreach ($bill->details as $detail) {
            $totalAmt = (float)$detail->adjusted_amount;
            $perMonthMap[$detail->p_id] = $monthsCount > 0 ? round($totalAmt / $monthsCount, 2) : 0;
        }

        $frozenUpdated  = 0;
        $periodUpdated  = 0;
        $periodCreated  = 0;

        return \DB::transaction(function () use (
            $bill, $destColumn, $isFA, $isBonus, $billPeriods, $pIds, $perMonthMap, $now,
            &$frozenUpdated, &$periodUpdated, &$periodCreated
        ) {
            // ── PASS 1: Update ALL currently-frozen records for these employees ──────
            // This makes the value appear IMMEDIATELY on the Frozen Employees screen
            // regardless of which month those rows are from.
            $frozenRecords = \DB::table('employee_payroll')
                ->whereIn('p_id', $pIds)
                ->where('is_frozen', 1)
                ->get();

            $alreadyUpdatedKeys = [];
            foreach ($frozenRecords as $payroll) {
                $amt = $perMonthMap[$payroll->p_id] ?? 0;
                $this->applyRecalculation($payroll, $destColumn, $amt, $isFA, $isBonus);
                $alreadyUpdatedKeys[] = $payroll->p_id . '-' . $payroll->paymonth . '-' . $payroll->year;
                $frozenUpdated++;
            }

            // ── PASS 2: Upsert each bill-period month row (frozen or not) ───────────
            // Ensures future Salary Management freezes also pick up the value.
            foreach ($pIds as $pId) {
                $amt = $perMonthMap[$pId] ?? 0;
                foreach ($billPeriods as $period) {
                    $skipKey = $pId . '-' . $period['month'] . '-' . $period['year'];
                    if (in_array($skipKey, $alreadyUpdatedKeys)) {
                        continue; // Already updated in Pass 1
                    }

                    $payroll = \DB::table('employee_payroll')
                        ->where('p_id', $pId)
                        ->where('paymonth', $period['month'])
                        ->where('year', $period['year'])
                        ->first();

                    if (!$payroll) {
                        // Create a minimal stub row; is_frozen=0, salary carries over later
                        // We set salary_id to the bill's because there is no monthly salary yet.
                        \DB::table('employee_payroll')->insert([
                            'p_id'           => $pId,
                            'paymonth'       => $period['month'],
                            'year'           => $period['year'],
                            'salary_id'      => $bill->salary_id,
                            'is_frozen'      => 0,
                            'created_at'     => $now,
                            'updated_at'     => $now,
                        ]);
                        $payroll = \DB::table('employee_payroll')
                            ->where('p_id', $pId)
                            ->where('paymonth', $period['month'])
                            ->where('year', $period['year'])
                            ->first();
                        
                        $this->applyRecalculation($payroll, $destColumn, $amt, $isFA, $isBonus);
                        $periodCreated++;
                    } else {
                        // exists but not frozen — update the target column only
                        $this->applyRecalculation($payroll, $destColumn, $amt, $isFA, $isBonus);
                        $periodUpdated++;
                    }
                }
            }

            $empCount = count($pIds);
            return response()->json([
                'success' => true,
                'message' => "Applied to Salary Deductions! "
                           . "{$frozenUpdated} frozen record(s) updated (visible now). "
                           . "{$periodUpdated} bill-period record(s) updated, {$periodCreated} new rows created. ",
            ]);
        });
    }

    public function viewBill(Request $request)
    {
        $request->validate(['salary_id' => 'required|string']);

        $bill = \App\Models\EmployeePayBill::with(['details', 'payItem'])
                    ->where('salary_id', $request->salary_id)
                    ->firstOrFail();

        $pIds = $bill->details->pluck('p_id')->toArray();
        $empDetails = \DB::table('project_employee')
            ->leftJoin('service', 'project_employee.p_id', '=', 'service.p_id')
            ->leftJoin('employment_types', 'service.employment_type', '=', 'employment_types.id')
            ->whereIn('project_employee.p_id', $pIds)
            ->select('project_employee.p_id', 'project_employee.name as employee_name',
                     'project_employee.status as current_status', 'project_employee.employment_type as pe_type',
                     'service.employment_type as svc_type', 'employment_types.employment_type as et_label')
            ->get()->keyBy('p_id');

        $statementData = [];
        $totalAmt = 0;
        foreach ($bill->details as $detail) {
            $emp = $empDetails->get($detail->p_id);
            $statusLabel = $emp?->current_status ?? 'Unknown';
            if (is_numeric($statusLabel)) { $statusLabel = $statusLabel == 1 ? 'Active' : 'Inactive'; }
            $typeLabel = $emp?->et_label ?: ($emp?->svc_type ?: ($emp?->pe_type ?: 'N/A'));
            $statementData[] = [
                'p_id'         => $detail->p_id,
                'name'         => $emp?->employee_name ?? 'Unknown Employee',
                'status'       => $statusLabel,
                'type'         => $typeLabel,
                'base_salary'  => $detail->base_salary,
                'actual_salary'=> $detail->actual_salary,
                'total_gross'  => $detail->total_period_salary,
                'amount'       => $detail->adjusted_amount
            ];
            $totalAmt += $detail->adjusted_amount;
        }

        $periodLabel = $bill->month . ' ' . $bill->year;
        if ($bill->to_month && $bill->to_year) {
            $periodLabel .= ' - ' . $bill->to_month . ' ' . $bill->to_year;
        }

        return response()->json([
            'success' => true,
            'summary' => [
                'salaryId'              => $bill->salary_id,
                'payItem'               => ['name' => $bill->payItem->name, 'type' => $bill->payItem->type],
                'periodLabel'           => $periodLabel,
                'selectedEmploymentType'=> $bill->employment_type,
                'projectTitle'          => 'N/A',
                'totalAmount'           => $totalAmt,
                'statementData'         => $statementData
            ]
        ]);
    }

    public function finalizeBill(Request $request)
    {
        $request->validate([
            'salary_id' => 'required|string'
        ]);

        $draftBill = \App\Models\EmployeePayBill::with('details', 'payItem')
                        ->where('salary_id', $request->salary_id)
                        ->firstOrFail();

        if ($draftBill->status === 'Finalized') {
            return response()->json(['success' => false, 'message' => 'Bill is already finalized.'], 400);
        }
        
        // Allow finalizing from any state as long as the bill has employee details saved
        if ($draftBill->details()->count() === 0) {
            return response()->json(['success' => false, 'message' => 'Please add employee data to the bill before finalizing. Click "List" then "Save Pay Item Bill" first.'], 400);
        }

        $payItem = $draftBill->payItem;
        $destColumn = $this->resolveDestinationColumn($payItem);
        $normalizedPayItemName = strtolower(trim($payItem->name));
        $isFA = ($normalizedPayItemName === 'festival allowance' || $normalizedPayItemName === 'festival');
        $isBonus = ($normalizedPayItemName === 'bonus' || $normalizedPayItemName === 'bonus allowance' || $normalizedPayItemName === 'salary bonus');

        $monthOrder = [
            'January' => 1, 'February' => 2, 'March' => 3, 'April' => 4,
            'May' => 5, 'June' => 6, 'July' => 7, 'August' => 8,
            'September' => 9, 'October' => 10, 'November' => 11, 'December' => 12
        ];
        $monthNames = array_flip($monthOrder);

        $targetPeriods = [];
        if (!$draftBill->to_month || !$draftBill->to_year) {
            $targetPeriods[] = ['month' => $draftBill->month, 'year' => $draftBill->year];
        } else {
            $currMonthVal = ($draftBill->year * 100) + $monthOrder[$draftBill->month];
            $endMonthVal  = ($draftBill->to_year * 100) + $monthOrder[$draftBill->to_month];
            while ($currMonthVal <= $endMonthVal) {
                $y = (int)($currMonthVal / 100); $m = $currMonthVal % 100;
                $targetPeriods[] = ['month' => $monthNames[$m], 'year' => $y];
                if ($m == 12) { $currMonthVal = (($y + 1) * 100) + 1; } else { $currMonthVal++; }
            }
        }

        return \DB::transaction(function() use ($draftBill, $destColumn, $isFA, $isBonus, $targetPeriods) {
            $pIdsToUpdate = $draftBill->details->pluck('p_id')->toArray();
            
            $existing = \DB::table('employee_payroll')
                ->whereIn('p_id', $pIdsToUpdate)
                ->where(function($q) use ($targetPeriods) {
                    foreach ($targetPeriods as $p) {
                        $q->orWhere(function($sub) use ($p) {
                            $sub->where('paymonth', $p['month'])->where('year', $p['year']);
                        });
                    }
                })
                ->select('p_id', 'paymonth', 'year')
                ->get();
            
            $existingMap = [];
            foreach($existing as $r) { $existingMap["{$r->p_id}-{$r->paymonth}-{$r->year}"] = true; }

            $toInsert = [];
            $now = now();
            foreach ($pIdsToUpdate as $pId) {
                foreach ($targetPeriods as $period) {
                    $key = "{$pId}-{$period['month']}-{$period['year']}";
                    if (!isset($existingMap[$key])) {
                        $toInsert[] = [
                            'p_id' => $pId, 
                            'paymonth' => $period['month'], 
                            'year' => $period['year'],
                            'salary_id' => $draftBill->salary_id,
                            'created_at' => $now,
                            'updated_at' => $now
                        ];
                    }
                }
            }
            if (!empty($toInsert)) {
                \DB::table('employee_payroll')->insert($toInsert);
            }

            $payrollRecords = \DB::table('employee_payroll')
                ->whereIn('p_id', $pIdsToUpdate)
                ->where(function($q) use ($targetPeriods) {
                    foreach ($targetPeriods as $p) {
                        $q->orWhere(function($sub) use ($p) {
                            $sub->where('paymonth', $p['month'])->where('year', $p['year']);
                        });
                    }
                })
                ->get()
                ->groupBy('p_id');

            $monthsCount = count($targetPeriods);
            
            foreach ($draftBill->details as $detail) {
                $totalAmt = $detail->adjusted_amount;
                $perMonthAmt = $monthsCount > 0 ? ($totalAmt / $monthsCount) : 0;
                
                $empRecords = $payrollRecords->get($detail->p_id) ?? collect();

                foreach ($targetPeriods as $period) {
                    $payroll = $empRecords->where('paymonth', $period['month'])->where('year', $period['year'])->first();
                    if ($payroll) {
                        $this->applyRecalculation($payroll, $destColumn, round($perMonthAmt, 2), $isFA, $isBonus);
                    }
                }
            }

            $draftBill->update(['status' => 'Finalized']);

            $pIds = $draftBill->details->pluck('p_id')->toArray();
            $empDetails = \DB::table('project_employee')
                ->leftJoin('service', 'project_employee.p_id', '=', 'service.p_id')
                ->leftJoin('employment_types', 'service.employment_type', '=', 'employment_types.id')
                ->whereIn('project_employee.p_id', $pIds)
                ->select(
                    'project_employee.p_id',
                    'project_employee.name as employee_name',
                    'project_employee.status as current_status',
                    'project_employee.employment_type as pe_type',
                    'service.employment_type as svc_type',
                    'employment_types.employment_type as et_label'
                )
                ->get()
                ->keyBy('p_id');

            $statementData = [];
            $totalAmt = 0;
            foreach ($draftBill->details as $detail) {
                $pId = $detail->p_id;
                $emp = $empDetails->get($pId);

                $statusLabel = $emp?->current_status ?? 'Unknown';
                if (is_numeric($statusLabel)) {
                    $statusLabel = ($statusLabel == 1) ? 'Active' : 'Inactive';
                }
                
                $typeLabel = $emp?->et_label;
                if (!$typeLabel) {
                    $typeLabel = $emp?->svc_type ?: ($emp?->pe_type ?: 'N/A');
                }

                $statementData[] = [
                    'p_id' => $pId,
                    'name' => $emp?->employee_name ?? 'Unknown Employee',
                    'status' => $statusLabel,
                    'type' => $typeLabel,
                    'base_salary' => $detail->base_salary,
                    'actual_salary' => $detail->actual_salary,
                    'total_gross' => $detail->total_period_salary,
                    'amount' => $detail->adjusted_amount
                ];
                $totalAmt += $detail->adjusted_amount;
            }

            $periodLabel = $draftBill->month . ' ' . $draftBill->year;
            if ($draftBill->to_month && $draftBill->to_year) {
                $periodLabel .= ' - ' . $draftBill->to_month . ' ' . $draftBill->to_year;
            }

            return response()->json([
                'success' => true,
                'message' => "Bill Finalized! Amounts injected to Core Payroll successfully.",
                'summary' => [
                    'salaryId' => $draftBill->salary_id,
                    'payItem' => ['name' => $draftBill->payItem->name, 'type' => $draftBill->payItem->type],
                    'periodLabel' => $periodLabel,
                    'selectedEmploymentType' => $draftBill->employment_type,
                    'projectTitle' => $draftBill->project ? $draftBill->project->project_title : 'N/A',
                    'totalAmount' => $totalAmt,
                    'statementData' => $statementData
                ]
            ]);
        });
    }

    public function saveAndGenerateBatch(Request $request)
    {
        $request->validate([
            'pay_item_id' => 'required',
            'month'       => 'required|string',
            'year'        => 'required|integer',
            'to_month'    => 'nullable|string',
            'to_year'     => 'nullable|integer',
            'project_id'  => 'nullable',
            'employment_type' => 'nullable|string',
            'salary_id'   => 'nullable|string|max:255'
        ]);

        $salaryId = $request->salary_id ?: 'PAY-' . strtoupper(uniqid());
        $request->merge(['salary_id' => $salaryId]);

        $payItem = PayItem::with('slabs')->findOrFail($request->pay_item_id);
        
        if (!$payItem->is_slab_based) {
            $msg = "Direct save is only supported for slab-based pay items. Please use the 'List' button to enter amounts manually.";
            return $request->ajax() ? response()->json(['success' => false, 'message' => $msg]) : redirect()->back()->with('error', $msg);
        }

        $isRange = $request->filled('to_month') && $request->filled('to_year');
        $projectId = $request->project_id;
        
        $monthOrder = [
            'January' => 1, 'February' => 2, 'March' => 3, 'April' => 4,
            'May' => 5, 'June' => 6, 'July' => 7, 'August' => 8,
            'September' => 9, 'October' => 10, 'November' => 11, 'December' => 12
        ];
        $monthNames = array_flip($monthOrder);

        $destColumn = $this->resolveDestinationColumn($payItem);
        $normalizedPayItemName = strtolower(trim($payItem->name));
        $isFA = ($normalizedPayItemName === 'festival allowance' || $normalizedPayItemName === 'festival');
        $isBonus = ($normalizedPayItemName === 'bonus' || $normalizedPayItemName === 'bonus allowance' || $normalizedPayItemName === 'salary bonus');

        $targetPeriods = [];
        if (!$isRange) {
            $targetPeriods[] = ['month' => $request->month, 'year' => $request->year];
        } else {
            $currMonthVal = ($request->year * 100) + $monthOrder[$request->month];
            $endMonthVal  = ($request->to_year * 100) + $monthOrder[$request->to_month];
            while ($currMonthVal <= $endMonthVal) {
                $y = (int)($currMonthVal / 100); $m = $currMonthVal % 100;
                $targetPeriods[] = ['month' => $monthNames[$m], 'year' => $y];
                if ($m == 12) { $currMonthVal = (($y + 1) * 100) + 1; } else { $currMonthVal++; }
            }
        }

        $query = \DB::table('project_employee')
            ->leftJoin('salary', 'project_employee.p_id', '=', 'salary.p_id')
            ->leftJoin('service', 'project_employee.p_id', '=', 'service.p_id')
            ->whereExists(function ($q) {
                $q->select(\DB::raw(1))
                  ->from('employee_payroll')
                  ->whereRaw('employee_payroll.p_id = project_employee.p_id')
                  ->where('employee_payroll.is_frozen', 1);
            });
        
        if ($projectId && is_numeric($projectId)) {
            $query->where('project_employee.project_id', (int)$projectId);
        }

        if ($request->filled('employment_type')) {
            $query->where('service.employment_type', $request->employment_type);
        }

        $employees = $query->select(
            'project_employee.p_id',
            'salary.gross_salary as master_gross',
            'salary.basic_pay as master_basic',
            'salary.da as master_da',
            'service.consolidated_pay'
        )->distinct()->get()->unique('p_id');

        $pIdsToUpdate = [];
        $amountsMap = [];

        foreach ($employees as $emp) {
            $baseSalary = (float)($emp->master_gross ?? 0);
            if ($baseSalary <= 0) $baseSalary = (float)($emp->consolidated_pay ?? 0);
            if ($baseSalary <= 0) $baseSalary = (float)($emp->master_basic ?? 0) + (float)($emp->master_da ?? 0);

            $numMonths = count($targetPeriods);
            $numMonths = $numMonths > 0 ? $numMonths : 1;
            $cumulativeGross = $baseSalary * $numMonths;

            $amount = 0;
            if ($payItem->is_slab_based && $payItem->slabs->isNotEmpty()) {
                $matched = false;
                $maxSlab = $payItem->slabs->sortByDesc('salary_to')->first();
                
                foreach ($payItem->slabs as $slab) {
                    if ($cumulativeGross >= $slab->salary_from && $cumulativeGross <= $slab->salary_to) {
                        $amount = $slab->amount;
                        $matched = true;
                        break;
                    }
                }
                
                if (!$matched && $cumulativeGross > $maxSlab->salary_to) {
                    $amount = $maxSlab->amount;
                }
            }

            // Always add to map so old values get zeroed out if this is an update
            $pIdsToUpdate[] = $emp->p_id;
            $amountsMap[$emp->p_id] = $amount / $numMonths;
        }

        if (empty($pIdsToUpdate)) {
            $msg = "No eligible employees found for this batch.";
            return $request->ajax() ? response()->json(['success' => false, 'message' => $msg]) : redirect()->back()->with('error', $msg);
        }

        return \DB::transaction(function() use ($request, $payItem, $destColumn, $isFA, $isBonus, $targetPeriods, $pIdsToUpdate, $amountsMap, $projectId) {
            // 1. Bulk Ensure Existence
            $existing = \DB::table('employee_payroll')
                ->whereIn('p_id', $pIdsToUpdate)
                ->where(function($q) use ($targetPeriods) {
                    foreach ($targetPeriods as $p) {
                        $q->orWhere(function($sub) use ($p) {
                            $sub->where('paymonth', $p['month'])->where('year', $p['year']);
                        });
                    }
                })
                ->select('p_id', 'paymonth', 'year')
                ->get();
            
            $existingMap = [];
            foreach($existing as $r) { $existingMap["{$r->p_id}-{$r->paymonth}-{$r->year}"] = true; }

            $toInsert = [];
            $now = now();
            foreach ($pIdsToUpdate as $pId) {
                foreach ($targetPeriods as $period) {
                    $key = "{$pId}-{$period['month']}-{$period['year']}";
                    if (!isset($existingMap[$key])) {
                        $toInsert[] = [
                            'p_id' => $pId, 
                            'paymonth' => $period['month'], 
                            'year' => $period['year'],
                            'salary_id' => $request->salary_id,
                            'created_at' => $now,
                            'updated_at' => $now
                        ];
                    }
                }
            }
            if (!empty($toInsert)) {
                \DB::table('employee_payroll')->insert($toInsert);
            }

            // 2. Fetch all records for processing
            $payrollRecords = \DB::table('employee_payroll')
                ->whereIn('p_id', $pIdsToUpdate)
                ->where(function($q) use ($targetPeriods) {
                    foreach ($targetPeriods as $p) {
                        $q->orWhere(function($sub) use ($p) {
                            $sub->where('paymonth', $p['month'])->where('year', $p['year']);
                        });
                    }
                })
                ->get()
                ->groupBy('p_id');

            // 3. Sequential processing
            foreach ($pIdsToUpdate as $pId) {
                $totalAmt = $amountsMap[$pId];
                
                $monthsCount = count($targetPeriods);
                $perMonthAmt = $monthsCount > 0 ? ($totalAmt / $monthsCount) : 0;
                
                $empRecords = $payrollRecords->get($pId) ?? collect();

                foreach ($targetPeriods as $period) {
                    $payroll = $empRecords->where('paymonth', $period['month'])->where('year', $period['year'])->first();
                    if ($payroll) {
                        $this->applyRecalculation($payroll, $destColumn, round($perMonthAmt, 2), $isFA, $isBonus, $request->salary_id);
                    }
                }
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Pay Item Batch for {$payItem->name} generated and saved successfully!",
                    'summary' => $this->getStatementData(new Request([
                        'pay_item_id' => $request->pay_item_id,
                        'month'        => $request->month,
                        'year'         => $request->year,
                        'to_month'     => $request->to_month,
                        'to_year'      => $request->to_year,
                        'p_ids'        => implode(',', $pIdsToUpdate),
                        'employment_type' => $request->employment_type,
                        'project_id'   => $projectId ?: 1
                    ]))
                ]);
            }

            return redirect()->route('pms.pay-item-master.index', [
                'project_id'   => $projectId ?: 1,
                'pay_item_id'  => $request->pay_item_id,
                'month'        => $request->month,
                'year'         => $request->year,
                'to_month'     => $request->to_month,
                'to_year'      => $request->to_year,
                'salary_id'    => $request->salary_id,
                'employment_type' => $request->employment_type,
                'show_summary' => 1,
                'p_ids'        => implode(',', $pIdsToUpdate)
            ])->with('success', "Pay Item Batch for {$payItem->name} generated successfully!");
        });
    }

    private function getStatementData(Request $request)
    {
        $payItem = PayItem::findOrFail($request->pay_item_id);
        
        // Find project name for professional summary
        $projectId = $request->project_id ?? 1;
        $projectObj = \App\Models\Project::find($projectId);
        $projectTitle = $projectObj ? $projectObj->name : 'Main Project';

        $month = $request->month;
        $year = $request->year;
        $toMonth = $request->to_month;
        $toYear = $request->to_year;
        $pIds = explode(',', $request->p_ids);
        
        $isRange = !empty($toMonth) && !empty($toYear);

        $monthOrder = [
            'January' => 1, 'February' => 2, 'March' => 3, 'April' => 4,
            'May' => 5, 'June' => 6, 'July' => 7, 'August' => 8,
            'September' => 9, 'October' => 10, 'November' => 11, 'December' => 12
        ];
        $monthNames = array_flip($monthOrder);

        $targetPeriods = [];
        if (!$isRange) {
            $targetPeriods[] = ['month' => $month, 'year' => $year];
        } else {
            $currMonthVal = ($year * 100) + $monthOrder[$month];
            $endMonthVal  = ($toYear * 100) + $monthOrder[$toMonth];

            while ($currMonthVal <= $endMonthVal) {
                $y = (int)($currMonthVal / 100);
                $m = $currMonthVal % 100;
                $targetPeriods[] = ['month' => $monthNames[$m], 'year' => $y];

                if ($m == 12) {
                    $currMonthVal = (($y + 1) * 100) + 1;
                } else {
                    $currMonthVal++;
                }
            }
        }

        $destColumn = $this->resolveDestinationColumn($payItem);

        // --- Bulk Fetch Start ---
        // 1. Fetch Master Data for all employees in ONE query
        $employees = \DB::table('project_employee')
            ->leftJoin('service', 'project_employee.p_id', '=', 'service.p_id')
            ->leftJoin('employment_types', 'service.employment_type', '=', 'employment_types.employment_type')
            ->leftJoin('salary', 'project_employee.p_id', '=', 'salary.p_id')
            ->whereIn('project_employee.p_id', $pIds)
            ->select(
                'project_employee.p_id',
                'project_employee.name',
                'project_employee.status as pe_status',
                'project_employee.employment_type as pe_type',
                'service.employment_type as svc_type',
                'employment_types.employment_type as et_label',
                'service.consolidated_pay',
                'salary.gross_salary as master_gross',
                'salary.basic_pay as master_basic',
                'salary.da as master_da'
            )
            ->get()
            ->unique('p_id')
            ->keyBy('p_id');

        // 2. Fetch all payroll amounts for the current month in ONE query
        $payrollAmountsQuery = \DB::table('employee_payroll')
            ->whereIn('p_id', $pIds)
            ->where(function($q) use ($targetPeriods) {
                foreach ($targetPeriods as $p) {
                    $q->orWhere(function($sub) use ($p) {
                        $sub->where('paymonth', $p['month'])->where('year', $p['year']);
                    });
                }
            })
            ->select('p_id', $destColumn, 'salary_id')
            ->get()
            ->groupBy('p_id');

        // 3. Fetch LATEST FREEZE record for each employee in ONE query
        $latestFreezes = \DB::table('employee_payroll')
            ->whereIn('p_id', $pIds)
            ->where('is_frozen', 1)
            ->orderBy('year', 'desc')
            ->orderByRaw("CASE
                WHEN paymonth = 'January' THEN 1 WHEN paymonth = 'February' THEN 2
                WHEN paymonth = 'March' THEN 3 WHEN paymonth = 'April' THEN 4
                WHEN paymonth = 'May' THEN 5 WHEN paymonth = 'June' THEN 6
                WHEN paymonth = 'July' THEN 7 WHEN paymonth = 'August' THEN 8
                WHEN paymonth = 'September' THEN 9 WHEN paymonth = 'October' THEN 10
                WHEN paymonth = 'November' THEN 11 WHEN paymonth = 'December' THEN 12
                END DESC")
            ->get()
            ->unique('p_id')
            ->keyBy('p_id');
        // --- Bulk Fetch End ---

        $statementData = [];
        foreach ($pIds as $pId) {
            $emp = $employees->get($pId);
            if (!$emp) continue;

            $payRecords = $payrollAmountsQuery->get($pId) ?? collect();
            $totalAmount = $payRecords->sum($destColumn);

            $projBase = (float)($emp->master_gross ?? 0);
            if ($projBase <= 0) $projBase = (float)($emp->consolidated_pay ?? 0);
            if ($projBase <= 0) $projBase = (float)($emp->master_basic ?? 0) + (float)($emp->master_da ?? 0);

            $lp = $latestFreezes->get($pId);
            $typeLabel = $emp->et_label ?: ($emp->svc_type ?: ($emp->pe_type ?: 'N/A'));

            $statementData[] = (object)[
                'p_id' => $pId,
                'name' => $emp->name,
                'status' => ($emp->pe_status == 1) ? 'Active' : 'Inactive',
                'type' => $typeLabel,
                'base_salary' => $projBase,
                'actual_salary' => (float)($lp?->net_salary ?? 0),
                'total_gross' => $projBase * count($targetPeriods),
                'amount' => $totalAmount,
            ];
        }

        $periodLabel = $isRange ? "{$month} {$year} to {$toMonth} {$toYear}" : "{$month} {$year}";

        $project = \App\Models\Project::find($payItem->project_id ?: 1);

        $salaryId = null;
        if (!empty($pIds)) {
            $firstEmpRecords = $payrollAmountsQuery->get($pIds[0]) ?? collect();
            $firstRecord = $firstEmpRecords->first();
            $salaryId = $firstRecord ? $firstRecord->salary_id : null;
        }

        return [
            'statementData' => $statementData,
            'payItem' => $payItem,
            'periodLabel' => $periodLabel,
            'projectTitle' => ($project && $project->name) ? $project->name : 'Main Project',
            'salaryId' => $salaryId ?: 'N/A',
            'selectedEmploymentType' => $request->employment_type ?: 'All Types'
        ];
    }

    private function resolveDestinationColumn($payItem)
    {
        $columnMap = [
            'pf tax'                      => 'pf',
            'professional tax'            => 'professional_tax',
            'pt'                          => 'professional_tax',
            'festival allowance'          => 'festival_allowance',
            'festival'                    => 'festival_allowance',
            'bonus'                       => 'bonus',
            'bonus allowance'             => 'bonus',
            'salary bonus'                => 'bonus',
            'incentive'                   => 'bonus',
            'prof tax'                    => 'professional_tax',
            'p.tax'                       => 'professional_tax',
            'tds'                         => 'tds',
            'tds 192 b'                   => 'tds_192_b',
            'tds 192b'                    => 'tds_192_b',
            'tds 194 j'                   => 'tds_194_j',
            'tds 194j'                    => 'tds_194_j',
            'esi employer'                => 'esi_employer',
            'esi'                         => 'esi_employer',
            'lic'                         => 'lic_others',
            'lic others'                  => 'lic_others',
            'epf employers share @ 12%'   => 'epf_employers_share',
            'epf employer'                => 'epf_employers_share',
            'epf'                         => 'epf_employers_share',
            'edli contribution and admin' => 'edli_charges',
            'edli'                        => 'edli_charges',
            'pf'                          => 'pf',
            'employer contribution'       => 'employer_contribution',
            'arrear'                      => 'other_allowance',
            'arrears'                     => 'other_allowance',
        ];

        $name = strtolower(trim($payItem->name));
        
        // Try exact match first
        if (isset($columnMap[$name])) {
            return $columnMap[$name];
        }

        // Try partial match
        foreach ($columnMap as $key => $col) {
            if (str_contains($name, $key)) {
                return $col;
            }
        }

        // Fallback based on type
        return ($payItem->type === 'Allowance') ? 'other_allowance' : 'others';
    }
    private function applyRecalculation($payroll, $destColumn, $amt, $isFA, $isBonus)
    {
        $totalDeductions = (float)($payroll->tds ?? 0) + (float)($payroll->epf_employers_share ?? 0) +
                           (float)($payroll->pf ?? 0) + (float)($payroll->edli_charges ?? 0) +
                           (float)($payroll->tds_192_b ?? 0) + (float)($payroll->tds_194_j ?? 0) +
                           (float)($payroll->professional_tax ?? 0) + (float)($payroll->esi_employer ?? 0) +
                           (float)($payroll->lic_others ?? 0) + (float)($payroll->others ?? 0);

        // If the new amount is a deduction, adjust the sum correctly
        if ($destColumn !== 'festival_allowance' && $destColumn !== 'bonus' && $destColumn !== 'other_allowance') {
            // It's a deduction column. We need to re-sum with the NEW amount for this column.
            $totalDeductions = 0;
            $deductionCols = ['tds', 'epf_employers_share', 'pf', 'edli_charges', 'tds_192_b', 'tds_194_j', 'professional_tax', 'esi_employer', 'lic_others', 'others'];
            foreach($deductionCols as $col) {
                $totalDeductions += ($col === $destColumn) ? $amt : (float)($payroll->$col ?? 0);
            }
        }

        $grossSalary = (float)($payroll->gross_salary ?? 0);
        $totalWorkingDays = (float)($payroll->total_working_days ?? 0);
        $daysWorked = (float)($payroll->days_worked ?? 0);
        $arrear = (float)($payroll->other_allowance ?? 0);
        $festivalAllowance = (float)($payroll->festival_allowance ?? 0);
        $bonus = (float)($payroll->bonus ?? 0);

        // Update the specific one
        if ($isFA) { $festivalAllowance = $amt; } 
        elseif ($isBonus) { $bonus = $amt; }
        elseif ($destColumn === 'other_allowance') { $arrear = $amt; }

        $proratedSalary = ($totalWorkingDays > 0) ? ($grossSalary / $totalWorkingDays) * $daysWorked : $grossSalary;
        $computedGross = $proratedSalary + $arrear + $festivalAllowance + $bonus;
        $netSalary = $computedGross - $totalDeductions;

        \DB::table('employee_payroll')
            ->where('id', $payroll->id)
            ->update([
                $destColumn => $amt,
                'festival_allowance' => $festivalAllowance,
                'bonus' => $bonus,
                'other_allowance' => $arrear,
                'total_deductions' => $totalDeductions,
                'net_salary' => $netSalary,
            ]);
    }

    private function recalculatePayroll($pId, $month, $year, $destColumn, $amt, $isFA, $isBonus)
    {
        $payroll = \DB::table('employee_payroll')
            ->where('p_id', $pId)->where('paymonth', $month)->where('year', $year)->first();

        if ($payroll) {
            $this->applyRecalculation($payroll, $destColumn, $amt, $isFA, $isBonus, $payroll->salary_id);
        }
    }
}
