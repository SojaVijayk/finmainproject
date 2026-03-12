<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\PayItem;
use App\Models\PayItemSlab;
use Illuminate\Http\Request;

class PayItemMasterController extends Controller
{
    public function index($project_id = null)
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        $payItems = PayItem::with('slabs')->get();
        return view('content.projects.pay-item-master.index', compact('project_id', 'payItems', 'pageConfigs'));
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

    public function generateBillList(Request $request)
    {
        try {
            $request->validate([
                'pay_item_id' => 'required',
                'month'       => 'required|string',
                'year'        => 'required|integer',
                'to_month'    => 'nullable|string',
                'to_year'     => 'nullable|integer',
                'project_id'  => 'nullable'
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

            // Calculate Period End Date (Y-m-d)
            $endMonthNum = $monthOrder[$endMonth];
            $lastDay = date('t', strtotime("{$endYear}-{$endMonthNum}-01"));
            $endOfPeriod = "{$endYear}-" . str_pad($endMonthNum, 2, '0', STR_PAD_LEFT) . "-{$lastDay}";

            $periodText = $isRange ? "{$startMonth} {$startYear} - {$endMonth} {$endYear}" : "{$startMonth} {$startYear}";

            // Main Source: project_employee
            // The user requested to see ALL employees in this list, regardless of whether 
            // they have an active service mapping or not.
            $query = \DB::table('project_employee')
                ->leftJoin('service', function($join) {
                    // Pull the latest service block (or rely on generic) without enforcing status=1
                    $join->on('project_employee.p_id', '=', 'service.p_id');
                })
                ->leftJoin('employment_types', 'service.employment_type', '=', 'employment_types.id')
                ->leftJoin('salary', 'project_employee.p_id', '=', 'salary.p_id');
            
            if ($projectId) {
                $query->where('project_employee.project_id', $projectId);
            }

            // We use a raw 'pe_status' from project_employee directly so we don't accidentally drop anyone
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
                // Enforce that the employee MUST have at least one frozen salary record in the system
                // (Matches user's request: "only show i have freeze the employees in the salary management")
                ->whereExists(function ($subquery) {
                    $subquery->select(\DB::raw(1))
                             ->from('employee_payroll')
                             ->whereColumn('employee_payroll.p_id', 'project_employee.p_id')
                             ->where('employee_payroll.is_frozen', 1);
                })
                // Enforce distinct on the exact combination of selected columns to prevent duplication fallout 
                // from multiple service history joins without triggering ONLY_FULL_GROUP_BY MySQL mode
                ->distinct()
                ->orderBy('project_employee.name')
                ->get();
                
            // Due to distinct(), if a user somehow accumulated functionally identical service rows, they might 
            // slip through. We'll enforce one row per P_ID in PHP just to be absolutely bulletproof.
            $employees = $employees->unique('p_id')->values();

            if ($employees->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'pay_item' => ['name' => $payItem->name, 'type' => $payItem->type],
                    'employees' => [],
                    'period_text' => $periodText
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

            $startMonthNum = $monthOrder[$startMonth];
            $endMonthNum   = $monthOrder[$endMonth];
            $startVal = ($startYear * 100) + $startMonthNum;
            $endVal   = ($endYear * 100) + $endMonthNum;

            $salaryRecordsQuery->whereRaw("(year * 100 + CASE 
                WHEN paymonth = 'January' THEN 1 WHEN paymonth = 'February' THEN 2 
                WHEN paymonth = 'March' THEN 3 WHEN paymonth = 'April' THEN 4 
                WHEN paymonth = 'May' THEN 5 WHEN paymonth = 'June' THEN 6 
                WHEN paymonth = 'July' THEN 7 WHEN paymonth = 'August' THEN 8 
                WHEN paymonth = 'September' THEN 9 WHEN paymonth = 'October' THEN 10 
                WHEN paymonth = 'November' THEN 11 WHEN paymonth = 'December' THEN 12 
                END) BETWEEN ? AND ?", [$startVal, $endVal]);

            $salaries = $salaryRecordsQuery->get()->groupBy('p_id');
            
            // Also get the LATEST payroll record for EACH employee as a fallback projection base
            $latestPayroll = \DB::table('employee_payroll')
                ->whereIn('p_id', $pIds)
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
            'amount'      => 'required|array'
        ]);

        $payItem = PayItem::findOrFail($request->pay_item_id);
        $isRange = $request->filled('to_month') && $request->filled('to_year');

        $monthOrder = [
            'January' => 1, 'February' => 2, 'March' => 3, 'April' => 4,
            'May' => 5, 'June' => 6, 'July' => 7, 'August' => 8,
            'September' => 9, 'October' => 10, 'November' => 11, 'December' => 12
        ];
        $monthNames = array_flip($monthOrder);

        // Map pay item names to exact employee_payroll columns where possible.
        // Normalized entirely to lowercase for user typo resilience.
        $columnMap = [
            'pf tax'                      => 'professional_tax',
            'professional tax'            => 'professional_tax',
            'pt'                          => 'professional_tax',
            'festival allowance'          => 'festival_allowance',
            'festival'                    => 'festival_allowance',
            'festival '                   => 'festival_allowance',
            'bonus'                       => 'bonus',
            'bonus '                      => 'bonus',
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

        $normalizedPayItemName = strtolower(trim($payItem->name));
        $destColumn = $columnMap[$normalizedPayItemName] ?? (($payItem->type === 'Allowance') ? 'other_allowance' : 'others');

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

        foreach ($pIds as $pId) {
            $amt = (float)($amounts[$pId] ?? 0);
            
            foreach ($targetPeriods as $period) {
                $m = $period['month'];
                $y = $period['year'];

                // Update or Insert the specific column so that even if Salary Management 
                // hasn't generated the primary shell record yet, the deduction acts as a pre-loaded template.
                \DB::table('employee_payroll')->updateOrInsert(
                    ['p_id' => $pId, 'paymonth' => $m, 'year' => $y],
                    [$destColumn => $amt]
                );

                // Re-fetch record to recalculate total_deductions and net_salary
                $payroll = \DB::table('employee_payroll')
                    ->where('p_id', $pId)
                    ->where('paymonth', $m)
                    ->where('year', $y)
                    ->first();

                if ($payroll) {
                    $totalDeductions = (float)($payroll->tds ?? 0) +
                                       (float)($payroll->epf_employers_share ?? 0) +
                                       (float)($payroll->pf ?? 0) +
                                       (float)($payroll->edli_charges ?? 0) +
                                       (float)($payroll->tds_192_b ?? 0) +
                                       (float)($payroll->tds_194_j ?? 0) +
                                       (float)($payroll->professional_tax ?? 0) +
                                       (float)($payroll->esi_employer ?? 0) +
                                       (float)($payroll->lic_others ?? 0) +
                                       (float)($payroll->others ?? 0);

                    $grossSalary = (float)($payroll->gross_salary ?? 0);
                    $totalWorkingDays = (float)($payroll->total_working_days ?? 0);
                    $daysWorked = (float)($payroll->days_worked ?? 0);
                    $arrear = (float)($payroll->other_allowance ?? 0);
                    $festivalAllowance = (float)($payroll->festival_allowance ?? 0);
                    $bonus = (float)($payroll->bonus ?? 0);

                    // If the current bill IS for Festival Allowance or Bonus, ensure we use the NEW value $amt
                    if ($isFA) {
                        $festivalAllowance = $amt;
                    } elseif ($isBonus) {
                        $bonus = $amt;
                    }

                    $proratedSalary = ($totalWorkingDays > 0) ? ($grossSalary / $totalWorkingDays) * $daysWorked : $grossSalary;
                    // Festival Allowance and Bonus are earnings, so they add to calculated Gross Salary
                    $computedGross = $proratedSalary + $arrear + $festivalAllowance + $bonus;
                    $netSalary = $computedGross - $totalDeductions;

                    \DB::table('employee_payroll')
                        ->where('p_id', $pId)
                        ->where('paymonth', $m)
                        ->where('year', $y)
                        ->update([
                            'festival_allowance' => $festivalAllowance,
                            'bonus' => $bonus,
                            'total_deductions' => $totalDeductions,
                            'net_salary' => $netSalary,
                        ]);
                }
            }
        }

        $periodLabel = $isRange ? "{$request->month} {$request->year} to {$request->to_month} {$request->to_year}" : "{$request->month} {$request->year}";
        return redirect()->back()->with('success', "Pay Item Bill for {$payItem->name} ({$periodLabel}) saved and recomputed successfully!");
    }
}
