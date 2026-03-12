<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DeductionMasterController extends Controller
{
    public function index($project_id = null)
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        
        // Fetch ALL frozen payroll records across all months/years/types for this specific project
        $query = \DB::table('employee_payroll')
            ->join('project_employee', 'project_employee.p_id', '=', 'employee_payroll.p_id')
            ->join('service', 'service.p_id', '=', 'employee_payroll.p_id')
            ->where('employee_payroll.is_frozen', 1);

        if ($project_id) {
            $query->where('project_employee.project_id', $project_id);
        }

        $frozenPayrolls = $query->select(
                'employee_payroll.*',
                'project_employee.name',
                'service.employment_type'
            )
            ->distinct()
            ->orderBy('employee_payroll.year', 'desc')
            ->orderBy('employee_payroll.paymonth', 'desc')
            ->get();

        // The user wants to see specifically: Name, Month, Year, Employment Type, and Salary ID.
        // We will pass this structured payload back to the index view.

        return view('content.projects.deduction-master.index', compact('frozenPayrolls', 'pageConfigs', 'project_id'));
    }

    public function selectEmployees(Request $request, $project_id = null)
    {
        $pageConfigs = ['myLayout' => 'horizontal'];

        // Fetch ALL frozen payroll records, joining with service, project_employee, and deduction_masters
        $frozenPayrolls = \DB::table('employee_payroll')
            ->join('project_employee', 'project_employee.p_id', '=', 'employee_payroll.p_id')
            ->leftJoin('designations', 'designations.id', '=', 'project_employee.designation_id')
            ->leftJoin('service', 'service.p_id', '=', 'employee_payroll.p_id')
            ->leftJoin('deduction_masters', 'deduction_masters.p_id', '=', 'employee_payroll.p_id');

        if ($project_id) {
            $frozenPayrolls->where('project_employee.project_id', $project_id);
        }

        // Apply month/year filter if present
        if ($request->filled('month') && $request->filled('year')) {
            $frozenPayrolls->where('employee_payroll.paymonth', $request->month)
                           ->where('employee_payroll.year', $request->year);
        }

        // Strictly show only frozen records as per user request
        $frozenPayrolls->where('employee_payroll.is_frozen', 1);

        $frozenPayrolls = $frozenPayrolls->select(
                'employee_payroll.*',
                'employee_payroll.professional_tax as payroll_professional_tax',
                'employee_payroll.festival_allowance as payroll_festival_allowance',
                'employee_payroll.bonus as payroll_bonus',
                'project_employee.name',
                'project_employee.bank_name',
                'project_employee.account_no',
                'project_employee.ifsc_code',
                'project_employee.branch',
                'service.role',
                'service.employment_type',
                'service.basic_pay',
                'service.da',
                'deduction_masters.tds as dm_tds_flag',
                'deduction_masters.tds_value as dm_tds_value',
                'deduction_masters.tds_type as dm_tds_type',
                'deduction_masters.tds_amount as dm_tds_amount',
                'deduction_masters.epf as dm_epf_flag',
                'deduction_masters.epf_value as dm_epf_value',
                'deduction_masters.epf_type as dm_epf_type',
                'deduction_masters.epf_amount as dm_epf_amount',
                'deduction_masters.pf as dm_pf_flag',
                'deduction_masters.pf_value as dm_pf_value',
                'deduction_masters.pf_type as dm_pf_type',
                'deduction_masters.pf_amount as dm_pf_amount',
                'deduction_masters.edli as dm_edli_flag',
                'deduction_masters.edli_value as dm_edli_value',
                'deduction_masters.edli_type as dm_edli_type',
                'deduction_masters.edli_amount as dm_edli_amount',
                'deduction_masters.lic as dm_lic_flag',
                'deduction_masters.lic_value as dm_lic_value',
                'deduction_masters.lic_type as dm_lic_type',
                'deduction_masters.lic_amount as dm_lic_amount',
                'deduction_masters.other as dm_other_flag',
                'deduction_masters.other_value as dm_other_value',
                'deduction_masters.other_type as dm_other_type',
                'deduction_masters.other_amount as dm_other_amount',
                'deduction_masters.tds_192_b as dm_tds_192_b_flag',
                'deduction_masters.tds_192_b_value as dm_tds_192_b_value',
                'deduction_masters.tds_192_b_type as dm_tds_192_b_type',
                'deduction_masters.tds_192_b_amount as dm_tds_192_b_amount',
                'deduction_masters.tds_194_j as dm_tds_194_j_flag',
                'deduction_masters.tds_194_j_value as dm_tds_194_j_value',
                'deduction_masters.tds_194_j_type as dm_tds_194_j_type',
                'deduction_masters.tds_194_j_amount as dm_tds_194_j_amount',
                'deduction_masters.professional_tax as dm_professional_tax_flag',
                'deduction_masters.professional_tax_value as dm_professional_tax_value',
                'deduction_masters.professional_tax_type as dm_professional_tax_type',
                'deduction_masters.professional_tax_amount as dm_professional_tax_amount',
                'deduction_masters.esi_employer as dm_esi_employer_flag',
                'deduction_masters.esi_employer_value as dm_esi_employer_value',
                'deduction_masters.esi_employer_type as dm_esi_employer_type',
                'deduction_masters.esi_employer_amount as dm_esi_employer_amount',
                'deduction_masters.festival_allowance as dm_festival_flag',
                'deduction_masters.festival_allowance_value as dm_festival_value',
                'deduction_masters.festival_allowance_type as dm_festival_type',
                'deduction_masters.festival_allowance_amount as dm_festival_amount',
                'deduction_masters.bonus as dm_bonus_flag',
                'deduction_masters.bonus_value as dm_bonus_value',
                'deduction_masters.bonus_type as dm_bonus_type',
                'deduction_masters.bonus_amount as dm_bonus_amount'
            )
            // Ensure we use the latest service record for each employee to prevent duplicates
            ->whereRaw('service.id = (SELECT MAX(id) FROM service WHERE service.p_id = employee_payroll.p_id)')
            ->orderBy('project_employee.name', 'asc')
            ->orderBy('employee_payroll.year', 'desc')
            ->orderBy('employee_payroll.paymonth', 'desc')
            ->get();

        return view('content.projects.deduction-master.select-employees', compact(
            'frozenPayrolls', 
            'pageConfigs', 
            'project_id'
        ));
    }
    public function storeDeductions(Request $request, $project_id = null)
    {
        if (!$request->has('p_id')) {
            return redirect()->back()->with('error', 'No employees found to update.');
        }

        foreach ($request->p_id as $key => $pId) {
            // Data is passed with indices corresponding to the p_id[] array index to avoid month-based collisions
            $rowMonth = $request->months[$key] ?? null;
            $rowYear = $request->years[$key] ?? null;

            if (!$rowMonth || !$rowYear) {
                continue;
            }

            // Get deduction amounts from indexed arrays
            $tds = (float)($request->tds[$key] ?? 0);
            $epf = (float)($request->epf[$key] ?? 0);
            $pf = (float)($request->pf_ded[$key] ?? 0);
            $edli = (float)($request->edli[$key] ?? 0);
            $tds192b = (float)($request->tds_192_b[$key] ?? 0);
            $tds194j = (float)($request->tds_194_j[$key] ?? 0);
            $pt = (float)($request->professional_tax[$key] ?? 0);
            $esiEmployer = (float)($request->esi_employer[$key] ?? 0);
            $licOthers = (float)($request->lic_others[$key] ?? 0);
            $otherDed = (float)($request->other_ded[$key] ?? 0);
            $festivalAllowance = (float)($request->festival_allowance[$key] ?? 0);
            $bonus = (float)($request->bonus[$key] ?? 0);

            // Calculate total deductions (excluding Festival Allowance which is an earning)
            $totalDeductions = $tds + $epf + $pf + $edli + $tds192b + $tds194j + $pt + $esiEmployer + $licOthers + $otherDed;

            // Recompute net salary from prorated salary
            $payroll = \DB::table('employee_payroll')
                ->where('p_id', $pId)
                ->where('paymonth', $rowMonth)
                ->where('year', $rowYear)
                ->first();

            $netSalary = 0;
            if ($payroll) {
                $grossSalary = (float)($payroll->gross_salary ?? 0);
                $totalWorkingDays = (float)($payroll->total_working_days ?? 0);
                $daysWorked = (float)($payroll->days_worked ?? 0);
                $arrear = (float)($payroll->other_allowance ?? 0);

                $proratedSalary = ($totalWorkingDays > 0) ? ($grossSalary / $totalWorkingDays) * $daysWorked : $grossSalary;
                // Festival Allowance and Bonus are earnings, so they add to calculated Gross Salary
                $computedGross = $proratedSalary + $arrear + $festivalAllowance + $bonus;
                $netSalary = $computedGross - $totalDeductions;
            }

            \DB::table('employee_payroll')
                ->where('p_id', $pId)
                ->where('paymonth', $rowMonth)
                ->where('year', $rowYear)
                ->update([
                    'tds' => $tds,
                    'epf_employers_share' => $epf,
                    'pf' => $pf,
                    'edli_charges' => $edli,
                    'tds_192_b' => $tds192b,
                    'tds_194_j' => $tds194j,
                    'professional_tax' => $pt,
                    'esi_employer' => $esiEmployer,
                    'lic_others' => $licOthers,
                    'others' => $otherDed,
                    'festival_allowance' => $festivalAllowance,
                    'bonus' => $bonus,
                    'total_deductions' => $totalDeductions,
                    'net_salary' => $netSalary,
                ]);
        }

        return redirect()->route('pms.deduction-master.index', $project_id)->with('success', 'Deductions successfully updated and Net Salary recalculated!');
    }
}
