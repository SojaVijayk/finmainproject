<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\PayItem;
use App\Models\PayItemSlab;
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

    public function fetchExistingBills(Request $request, $project_id = null)
    {
        $month = $request->month;
        $year = $request->year;
        $employmentTypeId = $request->employment_type;
        $project_id = $project_id ?? $request->project_id ?? 1;

        // Resolve Employment Type Name
        $employmentType = $employmentTypeId;
        if (is_numeric($employmentTypeId)) {
            $et = \App\Models\EmploymentType::find($employmentTypeId);
            $employmentType = $et ? $et->employment_type : $employmentTypeId;
        }

        if (!$month || !$year || !$employmentType) {
            return response()->json(['success' => false, 'message' => 'Missing filters']);
        }

        $monthOrder = [
            'January' => 1, 'February' => 2, 'March' => 3, 'April' => 4,
            'May' => 5, 'June' => 6, 'July' => 7, 'August' => 8,
            'September' => 9, 'October' => 10, 'November' => 11, 'December' => 12
        ];
        $monthNames = array_flip($monthOrder);

        $monthCase = "CASE paymonth ";
        foreach($monthOrder as $mName => $mVal) { $monthCase .= "WHEN '{$mName}' THEN {$mVal} "; }
        $monthCase .= "END";

        // Fetch all active pay items to use for batch identification
        $allPayItems = PayItem::where('status', 1)->get();
        $colMap = [];
        foreach ($allPayItems as $item) {
            $colMap[$item->name] = $this->resolveDestinationColumn($item);
        }

        // We group by salary_id to show unique batches
        $batches = \DB::table('employee_payroll')
            ->join('project_employee', 'project_employee.p_id', '=', 'employee_payroll.p_id')
            ->join('service', 'service.p_id', '=', 'employee_payroll.p_id')
            ->where('project_employee.project_id', $project_id)
            ->where('service.employment_type', $employmentType)
            ->where(function($q) use ($month, $year) {
                // Show batches that have at least one record in this month or year
                // to be more inclusive while still filtering by context
                $q->where('employee_payroll.paymonth', $month)
                  ->where('employee_payroll.year', $year);
            })
            ->select(
                \DB::raw("COALESCE(NULLIF(employee_payroll.salary_id, ''), 'Unnamed Batch') as salary_id"),
                \DB::raw('COUNT(DISTINCT employee_payroll.p_id) as employee_count'),
                \DB::raw('MAX(employee_payroll.is_frozen) as is_frozen'),
                \DB::raw("MIN(employee_payroll.year) as min_year"),
                \DB::raw("MAX(employee_payroll.year) as max_year"),
                \DB::raw("MIN($monthCase) as min_m_val"),
                \DB::raw("MAX($monthCase) as max_m_val"),
                'service.employment_type'
            )
            ->groupBy('employee_payroll.salary_id', 'service.employment_type')
            ->orderBy('min_year', 'desc')
            ->orderBy('min_m_val', 'desc')
            ->get();

        $requestedPayItem = $request->pay_item_id ? PayItem::find($request->pay_item_id) : null;

        foreach ($batches as $batch) {
            // Construct Period Label
            $startM = $monthNames[$batch->min_m_val];
            $endM   = $monthNames[$batch->max_m_val];
            
            if ($batch->min_year == $batch->max_year && $batch->min_m_val == $batch->max_m_val) {
                $batch->period_label = "{$startM} {$batch->min_year}";
            } else {
                $batch->period_label = "{$startM} {$batch->min_year} - {$endM} {$batch->max_year}";
            }

            // Identify Pay Item Name
            $batch->pay_item_name = $requestedPayItem ? $requestedPayItem->name : 'Pay Item Bill'; 
            
            // Try to find the specific pay item if it wasn't filtered
            $sampleRec = \DB::table('employee_payroll')
                ->where('salary_id', $batch->salary_id)
                ->where('paymonth', $startM)
                ->where('year', $batch->min_year)
                ->first();
            
            if ($sampleRec) {
                foreach ($colMap as $name => $col) {
                    if (isset($sampleRec->$col) && $sampleRec->$col > 0) {
                        $batch->pay_item_name = $name;
                        break;
                    }
                }
            }
        }
        
        return response()->json(['success' => true, 'batches' => $batches]);
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
                    'year'
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

                // The user explicitly requested that "Total Period Salary" should simply be 
                // a 6-month calculation of the employee's raw base salary for Pay Item generation purposes.
                // We bypass actual summed payroll historical records for this requirement.
                $cumulativeGross = $projBase * 6;

                $calculatedAmount = 0;
                if ($payItem->is_slab_based && $payItem->slabs->isNotEmpty()) {
                    $matched = false;
                    $maxSlab = $payItem->slabs->sortByDesc('salary_to')->first();
                    
                    foreach ($payItem->slabs as $slab) {
                        if ($cumulativeGross >= $slab->salary_from && $cumulativeGross <= $slab->salary_to) {
                            $calculatedAmount = $slab->amount;
                            $matched = true;
                            break;
                        }
                    }
                    
                    // Fallback: If salary exceeds all defined slabs, pick the highest slab's amount
                    if (!$matched && $cumulativeGross > $maxSlab->salary_to) {
                        $calculatedAmount = $maxSlab->amount;
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
            'salary_id'   => 'nullable|string|max:255'
        ]);

        $salaryId = $request->salary_id ?: 'PAY-' . strtoupper(uniqid());
        $request->merge(['salary_id' => $salaryId]);

        $payItem = PayItem::findOrFail($request->pay_item_id);
        $isRange = $request->filled('to_month') && $request->filled('to_year');

        $monthOrder = [
            'January' => 1, 'February' => 2, 'March' => 3, 'April' => 4,
            'May' => 5, 'June' => 6, 'July' => 7, 'August' => 8,
            'September' => 9, 'October' => 10, 'November' => 11, 'December' => 12
        ];
        $monthNames = array_flip($monthOrder);

        $destColumn = $this->resolveDestinationColumn($payItem);
        $normalizedPayItemName = strtolower(trim($payItem->name));

        // Check if the item is a bonus or festival allowance for Net Salary math
        $isFA = ($normalizedPayItemName === 'festival allowance' || $normalizedPayItemName === 'festival');
        $isBonus = ($normalizedPayItemName === 'bonus' || $normalizedPayItemName === 'bonus allowance' || $normalizedPayItemName === 'salary bonus');

        $pIds    = $request->p_id;
        $amounts = $request->amount;

        // Determine the list of (month, year) pairs to update
        $targetPeriods = [];
        if (!$isRange) {
            $targetPeriods[] = ['month' => $request->month, 'year' => $request->year];
        } else {
            $currMonthVal = ($request->year * 100) + $monthOrder[$request->month];
            $endMonthVal  = ($request->to_year * 100) + $monthOrder[$request->to_month];

            while ($currMonthVal <= $endMonthVal) {
                $y = (int)($currMonthVal / 100);
                $m = $currMonthVal % 100;
                $targetPeriods[] = ['month' => $monthNames[$m], 'year' => $y];

                // Increment month
                if ($m == 12) {
                    $currMonthVal = (($y + 1) * 100) + 1;
                } else {
                    $currMonthVal++;
                }
            }
        }

        return \DB::transaction(function() use ($request, $payItem, $destColumn, $isFA, $isBonus, $targetPeriods, $pIds, $amounts) {
            // 1. Bulk Ensure Existence (Optimized)
            // Fetch what already exists to avoid 600+ redundant 'updateOrInsert' queries
            $existing = \DB::table('employee_payroll')
                ->whereIn('p_id', $pIds)
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
            foreach ($pIds as $pId) {
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

            // Fetch all records for these employees/periods in ONE query
            $payrollRecords = \DB::table('employee_payroll')
                ->whereIn('p_id', $pIds)
                ->where(function($q) use ($targetPeriods) {
                    foreach ($targetPeriods as $p) {
                        $q->orWhere(function($sub) use ($p) {
                            $sub->where('paymonth', $p['month'])->where('year', $p['year']);
                        });
                    }
                })
                ->get()
                ->groupBy('p_id');

            foreach ($pIds as $pId) {
                $amt = (float)($amounts[$pId] ?? 0);
                $empRecords = $payrollRecords->get($pId) ?? collect();

                foreach ($targetPeriods as $period) {
                    $payroll = $empRecords->where('paymonth', $period['month'])->where('year', $period['year'])->first();
                    if ($payroll) {
                        // Perform memory calculation based on current state + new amount
                        $this->applyRecalculation($payroll, $destColumn, $amt, $isFA, $isBonus, $request->salary_id);
                    }
                }
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Pay Item Bill for {$payItem->name} saved and recomputed successfully!",
                    'summary' => $this->getStatementData(new Request([
                        'pay_item_id' => $request->pay_item_id,
                        'month'        => $request->month,
                        'year'         => $request->year,
                        'to_month'     => $request->to_month,
                        'to_year'      => $request->to_year,
                        'p_ids'        => implode(',', $pIds),
                        'employment_type' => $request->employment_type,
                        'project_id'   => $payItem->project_id ?? 1
                    ]))
                ]);
            }

            return redirect()->route('pms.pay-item-master.index', [
                'project_id'   => $payItem->project_id ?? 1,
                'pay_item_id'  => $request->pay_item_id,
                'month'        => $request->month,
                'year'         => $request->year,
                'to_month'     => $request->to_month,
                'to_year'      => $request->to_year,
                'salary_id'    => $request->salary_id,
                'employment_type' => $request->employment_type,
                'show_summary' => 1,
                'p_ids'        => implode(',', $pIds)
            ])->with('success', "Pay Item Bill for {$payItem->name} saved and recomputed successfully!");
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
            $actualGross = (float)($emp->master_gross ?? 0);
            if ($actualGross <= 0) $actualGross = (float)($emp->consolidated_pay ?? 0);
            if ($actualGross <= 0) $actualGross = (float)($emp->master_basic ?? 0) + (float)($emp->master_da ?? 0);

            $amount = 0;
            if ($payItem->is_slab_based) {
                $slab = $payItem->slabs->where('salary_from', '<=', $actualGross)->where('salary_to', '>=', $actualGross)->first();
                if (!$slab) { $slab = $payItem->slabs->sortByDesc('salary_to')->first(); }
                $amount = $slab ? (float)$slab->amount : 0;
            } else { $amount = 0; }

            if ($amount > 0) {
                $pIdsToUpdate[] = $emp->p_id;
                $amountsMap[$emp->p_id] = $amount;
            }
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
                $amt = $amountsMap[$pId];
                $empRecords = $payrollRecords->get($pId) ?? collect();

                foreach ($targetPeriods as $period) {
                    $payroll = $empRecords->where('paymonth', $period['month'])->where('year', $period['year'])->first();
                    if ($payroll) {
                        $this->applyRecalculation($payroll, $destColumn, $amt, $isFA, $isBonus, $request->salary_id);
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
        $payrollAmounts = \DB::table('employee_payroll')
            ->whereIn('p_id', $pIds)
            ->where('paymonth', $month)
            ->where('year', $year)
            ->select('p_id', $destColumn, 'salary_id')
            ->get()
            ->keyBy('p_id');

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

            $payRecord = $payrollAmounts->get($pId);
            $totalAmount = (float)($payRecord?->$destColumn ?? 0);

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
                'total_gross' => $projBase * 6,
                'amount' => $totalAmount,
            ];
        }

        $periodLabel = $isRange ? "{$month} {$year} to {$toMonth} {$toYear}" : "{$month} {$year}";

        $project = \App\Models\Project::find($payItem->project_id ?: 1);

        $salaryId = null;
        if (!empty($pIds)) {
            $salaryId = $payrollAmounts->get($pIds[0])?->salary_id;
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
            'pf tax'                      => 'professional_tax',
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
    private function applyRecalculation($payroll, $destColumn, $amt, $isFA, $isBonus, $salaryId)
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
                'salary_id' => $salaryId,
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
