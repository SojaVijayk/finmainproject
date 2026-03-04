<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DeductionMasterController extends Controller
{
    public function index($project_id = null)
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        
        // Fetch ALL frozen payroll records across all months/years/types
        $frozenPayrolls = \DB::table('employee_payroll')
            ->join('project_employee', 'project_employee.p_id', '=', 'employee_payroll.p_id')
            ->join('service', 'service.p_id', '=', 'employee_payroll.p_id')
            ->where('employee_payroll.is_frozen', 1)
            ->select(
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

        // Fetch ALL frozen payroll records, joining with service and project_employee to get all necessary details
        $frozenPayrolls = \DB::table('employee_payroll')
            ->join('project_employee', 'project_employee.p_id', '=', 'employee_payroll.p_id')
            ->leftJoin('designations', 'designations.id', '=', 'project_employee.designation_id')
            ->leftJoin('service', 'service.p_id', '=', 'employee_payroll.p_id')
            ->where('employee_payroll.is_frozen', 1)
            ->select(
                'employee_payroll.*',
                'project_employee.name',
                'project_employee.bank_name',
                'project_employee.account_no',
                'project_employee.ifsc_code',
                'project_employee.branch',
                'designations.designation',
                'service.employment_type'
            )
            // Ensure we use the latest service record for each employee to prevent duplicates
            ->whereRaw('service.id = (SELECT MAX(id) FROM service WHERE service.p_id = employee_payroll.p_id)')
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

        foreach ($request->p_id as $pId) {
            // Get the specific context for this row from the new array inputs
            $rowMonth = $request->months[$pId] ?? null;
            $rowYear = $request->years[$pId] ?? null;

            if (!$rowMonth || !$rowYear) {
                continue; // Cannot update without exact period context
            }

            \DB::table('employee_payroll')
                ->where('p_id', $pId)
                ->where('paymonth', $rowMonth)
                ->where('year', $rowYear)
                ->update([
                    'tds_192_b' => $request->tds_192_b[$pId] ?? 0,
                    'tds_194_j' => $request->tds_194_j[$pId] ?? 0,
                    'professional_tax' => $request->professional_tax[$pId] ?? 0,
                    'esi_employer' => $request->esi_employer[$pId] ?? 0,
                    'lic_others' => $request->lic_others[$pId] ?? 0,
                    'festival_allowance' => $request->festival_allowance[$pId] ?? 0,
                    'net_salary' => $request->calculated_net_salary[$pId] ?? 0,
                    'total_deductions' => $request->calculated_total_deductions[$pId] ?? 0,
                ]);
        }

        return redirect()->route('pms.deduction-master.index', $project_id)->with('success', 'Deductions successfully updated and Net Salary recalculated!');
    }
}
