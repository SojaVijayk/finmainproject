<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DeductionMasterController extends Controller
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

        return view('content.projects.deduction-master.index', compact('employmentTypes', 'years', 'months', 'pageConfigs', 'project_id'));
    }

    public function selectEmployees(Request $request, $project_id = null)
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        $month = $request->month;
        $year = $request->year;
        $employmentType = $request->employment_type;
        $salaryId = $request->default_salary_id;

        // Calculate Month Start and End for overlapping service fetch
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

        $validPIds = $overlappingServices->pluck('p_id')->unique()->toArray();

        // Fetch frozen payroll records for those valid P_IDs
        $frozenPayrolls = \DB::table('employee_payroll')
            ->join('project_employee', 'project_employee.p_id', '=', 'employee_payroll.p_id')
            ->leftJoin('designations', 'designations.id', '=', 'project_employee.designation_id')
            ->whereIn('employee_payroll.p_id', $validPIds)
            ->where('employee_payroll.paymonth', $month)
            ->where('employee_payroll.year', $year)
            ->where('employee_payroll.is_frozen', 1) // Only show frozen records
            ->select(
                'employee_payroll.*',
                'project_employee.name',
                'project_employee.bank_name',
                'project_employee.account_no',
                'project_employee.ifsc_code',
                'project_employee.branch',
                'designations.designation'
            )
            ->get();

        return view('content.projects.deduction-master.select-employees', compact(
            'frozenPayrolls', 'month', 'year', 'employmentType', 'salaryId', 'pageConfigs', 'project_id'
        ));
    }
}
