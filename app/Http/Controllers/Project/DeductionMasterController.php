<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class DeductionMasterController extends Controller
{
    public function index($project_id = null)
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        
        // Fetch ALL frozen payroll records across all months/years/types for this specific project
        $frozenPayrolls = \DB::table('employee_payroll')
            ->join('project_employee', 'project_employee.p_id', '=', 'employee_payroll.p_id')
            ->join('service', 'service.p_id', '=', 'employee_payroll.p_id')
            ->where('employee_payroll.is_frozen', 1)
            ->whereRaw('service.id = (SELECT MAX(id) FROM service WHERE service.p_id = employee_payroll.p_id)');

        if ($project_id) {
            $frozenPayrolls->where('project_employee.project_id', $project_id);
        }

        $frozenPayrolls = $frozenPayrolls->select(
                'employee_payroll.*',
                'project_employee.name',
                'service.employment_type'
            )
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

        // Apply filters if present; otherwise show all frozen records for the project
        if ($request->filled('month')) {
            $frozenPayrolls->where('employee_payroll.paymonth', $request->month);
        }
        if ($request->filled('year')) {
            $frozenPayrolls->where('employee_payroll.year', $request->year);
        }
        if ($request->filled('employment_type')) {
            $frozenPayrolls->where('service.employment_type', $request->employment_type);
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
                'deduction_masters.bonus_amount as dm_bonus_amount',
                'deduction_masters.medisep as dm_medisep_flag',
                'deduction_masters.medisep_value as dm_medisep_value',
                'deduction_masters.medisep_type as dm_medisep_type',
                'deduction_masters.medisep_amount as dm_medisep_amount',
                'deduction_masters.gpf as dm_gpf_flag',
                'deduction_masters.gpf_value as dm_gpf_value',
                'deduction_masters.gpf_type as dm_gpf_type',
                'deduction_masters.gpf_amount as dm_gpf_amount',
                'deduction_masters.sli1 as dm_sli1_flag',
                'deduction_masters.sli1_value as dm_sli1_value',
                'deduction_masters.sli1_type as dm_sli1_type',
                'deduction_masters.sli1_amount as dm_sli1_amount',
                'deduction_masters.sli2 as dm_sli2_flag',
                'deduction_masters.sli2_value as dm_sli2_value',
                'deduction_masters.sli2_type as dm_sli2_type',
                'deduction_masters.sli2_amount as dm_sli2_amount',
                'deduction_masters.sli3 as dm_sli3_flag',
                'deduction_masters.sli3_value as dm_sli3_value',
                'deduction_masters.sli3_type as dm_sli3_type',
                'deduction_masters.sli3_amount as dm_sli3_amount',
                'deduction_masters.gis as dm_gis_flag',
                'deduction_masters.gis_value as dm_gis_value',
                'deduction_masters.gis_type as dm_gis_type',
                'deduction_masters.gis_amount as dm_gis_amount',
                'deduction_masters.gpais as dm_gpais_flag',
                'deduction_masters.gpais_value as dm_gpais_value',
                'deduction_masters.gpais_type as dm_gpais_type',
                'deduction_masters.gpais_amount as dm_gpais_amount'
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
            $medisep = (float)($request->medisep[$key] ?? 0);
            $gpf = (float)($request->gpf[$key] ?? 0);
            $sli1 = (float)($request->sli1[$key] ?? 0);
            $sli2 = (float)($request->sli2[$key] ?? 0);
            $sli3 = (float)($request->sli3[$key] ?? 0);
            $gis = (float)($request->gis[$key] ?? 0);
            $gpais = (float)($request->gpais[$key] ?? 0);
            $otherDed = (float)($request->other_ded[$key] ?? 0);
            $festivalAllowance = (float)($request->festival_allowance[$key] ?? 0);
            $bonus = (float)($request->bonus[$key] ?? 0);

            // Calculate total deductions (excluding Festival Allowance which is an earning)
            $totalDeductions = $tds + $epf + $pf + $edli + $tds192b + $tds194j + $pt + $esiEmployer + $licOthers + 
                               $medisep + $gpf + $sli1 + $sli2 + $sli3 + $gis + $gpais + $otherDed;

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
                    'medisep' => $medisep,
                    'gpf' => $gpf,
                    'sli1' => $sli1,
                    'sli2' => $sli2,
                    'sli3' => $sli3,
                    'gis' => $gis,
                    'gpais' => $gpais,
                    'others' => $otherDed,
                    'festival_allowance' => $festivalAllowance,
                    'bonus' => $bonus,
                    'total_deductions' => $totalDeductions,
                    'net_salary' => $netSalary,
                ]);
        }

        return redirect()->route('pms.deduction-master.index', $project_id)->with('success', 'Deductions successfully updated and Net Salary recalculated!');
    }
    public function generateSalarySlip(Request $request, $id, $month, $year)
    {
        $data = $this->getSalarySlipData($id, $month, $year);
        if (is_string($data)) {
            return redirect()->back()->with('error', $data);
        }

        $payroll = $data['payroll'];
        $netInWords = $data['netInWords'];
        $dynamicDeductions = $data['dynamicDeductions'];
        $pageConfigs = ['myLayout' => 'blank'];

        $isDeputation = $this->isDeputationSalary($payroll);

        $viewName = $isDeputation 
            ? 'content.projects.deduction-master.deputation-salary-slip' 
            : 'content.projects.deduction-master.salary-slip';

        return view($viewName, compact('payroll', 'pageConfigs', 'netInWords', 'dynamicDeductions'));
    }

    public function downloadSalarySlipPdf(Request $request, $id, $month, $year)
    {
        $data = $this->getSalarySlipData($id, $month, $year);
        if (is_string($data)) {
            return redirect()->back()->with('error', $data);
        }

        $payroll = $data['payroll'];
        $netInWords = $data['netInWords'];
        $dynamicDeductions = $data['dynamicDeductions'];
        $isExport = true;

        $isDeputation = $this->isDeputationSalary($payroll);

        $viewName = $isDeputation 
            ? 'content.projects.deduction-master.deputation-salary-slip' 
            : 'content.projects.deduction-master.salary-slip';

        $pdf = Pdf::loadView($viewName, compact('payroll', 'netInWords', 'isExport', 'dynamicDeductions'))
                  ->setPaper('a4', 'portrait');
        
        return $pdf->download("Salary_Slip_{$payroll->name}_{$month}_{$year}.pdf");
    }

    public function downloadSalarySlipWord(Request $request, $id, $month, $year)
    {
        $data = $this->getSalarySlipData($id, $month, $year);
        if (is_string($data)) {
            return redirect()->back()->with('error', $data);
        }

        $payroll = $data['payroll'];
        $netInWords = $data['netInWords'];
        $dynamicDeductions = $data['dynamicDeductions'];
        $isExport = true;

        $isDeputation = $this->isDeputationSalary($payroll);

        $viewName = $isDeputation 
            ? 'content.projects.deduction-master.deputation-salary-slip' 
            : 'content.projects.deduction-master.salary-slip';

        $view = view($viewName, compact('payroll', 'netInWords', 'isExport', 'dynamicDeductions'))->render();
        
        $filename = "Salary_Slip_{$payroll->name}_{$month}_{$year}.doc";
        
        return response($view)
            ->header('Content-Type', 'application/msword')
            ->header('Content-Disposition', 'attachment; filename=' . $filename);
    }

    private function getSalarySlipData($id, $month, $year)
    {
        $payroll = \DB::table('employee_payroll')
            ->join('project_employee', 'project_employee.p_id', '=', 'employee_payroll.p_id')
            ->leftJoin('designations', 'designations.id', '=', 'project_employee.designation_id')
            ->leftJoin('service', 'service.p_id', '=', 'employee_payroll.p_id')
            ->leftJoin('projects', 'projects.id', '=', 'project_employee.project_id')
            ->where('employee_payroll.p_id', $id)
            ->where('employee_payroll.paymonth', $month)
            ->where('employee_payroll.year', $year)
            ->select(
                'employee_payroll.*',
                'project_employee.name',
                'project_employee.pan_number',
                'project_employee.bank_name',
                'project_employee.account_no',
                'project_employee.ifsc_code',
                'project_employee.branch',
                'designations.designation as designation_name',
                'service.role',
                'service.employment_type',
                'service.basic_pay as service_basic',
                'service.da as service_da',
                'service.consolidated_pay',
                'service.hra as service_hra',
                'projects.title as project_name',
                'project_employee.project_id',
                'project_employee.employment_type as pe_employment_type'
            )
            ->whereRaw('service.id = (SELECT MAX(id) FROM service WHERE service.p_id = employee_payroll.p_id)')
            ->first();

        if (!$payroll) {
            return 'Salary slip record not found.';
        }

        // Fetch all dynamic deductions for this period
        // Note: In some systems, dynamic deductions are stored in employee_dynamic_deductions
        // or mapped to master_dynamic_deductions.
        $dynamicDeductions = \DB::table('employee_dynamic_deductions')
            ->where('p_id', $id)
            ->pluck('amount', 'deduction_name')
            ->toArray();

        return [
            'payroll' => $payroll,
            'netInWords' => $this->numberToWords($payroll->net_salary, $this->isDeputationSalary($payroll)),
            'dynamicDeductions' => $dynamicDeductions
        ];
    }

    private function numberToWords($number, $isDeputation = false)
    {
        $number = round($number, 2);
        if ($number == 0) return 'Zero Rupees Only';
        
        $isNegative = $number < 0;
        $number = abs($number);

        $no = (int)floor($number);
        $decimal = (int)round(($number - $no) * 100);
        
        // If decimal rounds up to 100, add 1 to $no and set decimal to 0
        if ($decimal == 100) {
            $no += 1;
            $decimal = 0;
        }

        $hundred = null;
        $digits_length = strlen((string)$no);
        $i = 0;
        $str = array();
        $words = array(
            0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
            7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve',
            13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen',
            18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty', 40 => 'Forty',
            50 => 'Fifty', 60 => 'Sixty', 70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety'
        );
        $digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
        
        $tempNo = $no;
        while ($i < $digits_length) {
            $divider = ($i == 2) ? 10 : 100;
            $currentSegment = floor($tempNo % $divider);
            $tempNo = floor($tempNo / $divider);
            $i += ($divider == 10) ? 1 : 2;
            
            if ($currentSegment) {
                $plural = (($counter = count($str)) && $currentSegment > 9) ? 's' : null;
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                $str[] = ($currentSegment < 21) 
                    ? $words[$currentSegment] . ' ' . $digits[$counter] . $plural . ' ' . $hundred 
                    : $words[floor($currentSegment / 10) * 10] . ' ' . $words[$currentSegment % 10] . ' ' . $digits[$counter] . $plural . ' ' . $hundred;
            } else {
                $str[] = null;
            }
        }
        
        $Rupees = implode('', array_reverse(array_filter($str)));
        $paise = ($decimal > 0) ? " and " . (isset($words[(int)($decimal / 10) * 10]) ? $words[(int)($decimal / 10) * 10] : '') . " " . (isset($words[$decimal % 10]) ? $words[$decimal % 10] : '') . ' Paise' : '';
        
        $prefix = $isNegative ? 'Minus ' : '';
        if ($isDeputation) {
            return ($Rupees || $paise) ? $prefix . 'Rupees ' . trim($Rupees) . $paise . ' Only' : 'Zero Rupees Only';
        } else {
            return ($Rupees || $paise) ? $prefix . trim($Rupees) . $paise . ' Rupees Only' : 'Zero Rupees Only';
        }
    }

    private function isDeputationSalary($payroll)
    {
        if (!$payroll) return false;
        
        $employmentType = trim(strtolower($payroll->employment_type ?? $payroll->pe_employment_type ?? ''));
        $role = trim(strtolower($payroll->role ?? ''));

        return $employmentType === 'deputation' || 
               in_array($role, ['deputation offier', 'deputation officer', 'deputation officerr']);
    }
}
