@extends('layouts/layoutMaster')

@section('title', 'Deduction Master - Frozen Employees')

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">PMS / Deduction Master /</span> Frozen Employees
</h4>

<div class="card mb-4">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title mb-0">Step 2: Frozen Employee List</h5>
            <h5 class="card-title mb-0">Step 2: Edit Deductions</h5>
            <small class="text-muted">
                Displaying all active frozen employees across all months.
            </small>
        </div>
        <div>
             <a href="{{ route('pms.deduction-master.index', $project_id) }}" class="btn btn-label-secondary">
                <i class="ti ti-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>
    
    <div class="card-body mt-4">
        @if($frozenPayrolls->isEmpty())
            <div class="alert alert-warning" role="alert">
                <h6 class="alert-heading fw-bold mb-1"><i class="ti ti-alert-triangle me-1"></i> No Frozen Records Found!</h6>
                <p class="mb-0">There are no salary records that have been "Frozen" for this specific Month, Year, and Employment Type combination. Please ensure the salary cycle has been completed and frozen in Salary Management first.</p>
            </div>
        @else
            <form action="{{ route('pms.deduction-master.store', $project_id) }}" method="POST">
                @csrf
                <!-- Hidden inputs for Month/Year removed as they are now row-specific -->
                
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Employee Name</th>
                            <th>Month/Year</th>
                            <th>Type</th>
                            <th>Designation</th>
                            <th>Bank Name</th>
                            <th>Account No.</th>
                            <th>IFSC Code</th>
                            <th>Branch</th>
                            <th style="width: 100px;">CL</th>
                            <th style="width: 100px;">SL</th>
                            <th style="width: 100px;">PL</th>
                            <th style="width: 100px;">LOP</th>
                            <th style="width: 120px;">EPF Employers share @ 12%</th>
                            <th style="width: 120px;">EDLI contribution and admin</th>
                            <th style="width: 100px;">PF</th>
                            <th style="width: 100px;">Employer Contribution</th>
                            <th style="width: 100px;">Arrear (+)</th>
                            <th style="width: 100px;">Other</th>
                            <th style="width: 150px;">TDS 192 B</th>
                            <th style="width: 150px;">TDS 194 J</th>
                            <th style="width: 150px;">PROFESSIONAL TAX</th>
                            <th style="width: 150px;">ESI EMPLOYER</th>
                            <th style="width: 150px;">LIC</th>
                            <th style="width: 150px;">Festival Allowance</th>
                            <th>Base Salary</th>
                            <th>Gross Salary</th>
                            <th>Net Salary</th>
                            <th>Current Net Salary</th>
                            <th>Summary %</th>
                            <th>Salary Slip</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($frozenPayrolls as $index => $payroll)
                            <tr>
                                <input type="hidden" name="p_id[]" value="{{ $payroll->p_id }}">
                                <input type="hidden" name="months[{{ $payroll->p_id }}]" value="{{ $payroll->paymonth }}">
                                <input type="hidden" name="years[{{ $payroll->p_id }}]" value="{{ $payroll->year }}">
                                
                                <td class="fw-semibold">{{ $payroll->name }}</td>
                                <td><span class="badge bg-label-primary">{{ $payroll->paymonth }} {{ $payroll->year }}</span></td>
                                <td><span class="badge bg-label-info">{{ $payroll->employment_type }}</span></td>
                                <td>{{ $payroll->designation ?? 'N/A' }}</td>
                                <td>{{ $payroll->bank_name ?? 'N/A' }}</td>
                                <td>{{ $payroll->account_no ?? 'N/A' }}</td>
                                <td>{{ $payroll->ifsc_code ?? 'N/A' }}</td>
                                <td>{{ $payroll->branch ?? 'N/A' }}</td>
                                <td class="text-center">{{ $payroll->cl_days ?? 0 }}</td>
                                <td class="text-center">{{ $payroll->sl_days ?? 0 }}</td>
                                <td class="text-center">{{ $payroll->pl_days ?? 0 }}</td>
                                <td class="text-center"><span class="badge bg-label-danger">{{ $payroll->lop_days ?? 0 }}</span></td>
                                <td class="text-end">₹{{ number_format((float)($payroll->epf_employers_share ?? 0), 2) }}</td>
                                <td class="text-end">₹{{ number_format((float)($payroll->edli_charges ?? 0), 2) }}</td>
                                <td class="text-end">₹{{ number_format((float)($payroll->pf ?? 0), 2) }}</td>
                                <td class="text-end">₹{{ number_format((float)($payroll->employer_contribution ?? 0), 2) }}</td>
                                <td class="text-end text-success">₹{{ number_format((float)($payroll->arrear ?? 0), 2) }}</td>
                                <td class="text-end">₹{{ number_format((float)($payroll->others ?? 0), 2) }}</td>
                                <td style="width: 150px;">
                                    <input type="number" name="tds_192_b[{{ $payroll->p_id }}]" class="form-control form-control-sm text-end tds-192-b" value="{{ $payroll->tds_192_b ?? 0 }}" min="0" step="0.01">
                                </td>
                                <td style="width: 150px;">
                                    <input type="number" name="tds_194_j[{{ $payroll->p_id }}]" class="form-control form-control-sm text-end tds-194-j" value="{{ $payroll->tds_194_j ?? 0 }}" min="0" step="0.01">
                                </td>
                                <td style="width: 150px;">
                                    <input type="number" name="professional_tax[{{ $payroll->p_id }}]" class="form-control form-control-sm text-end attr-pt" value="{{ $payroll->professional_tax ?? 0 }}" min="0" step="0.01">
                                </td>
                                <td style="width: 150px;">
                                    <input type="number" name="esi_employer[{{ $payroll->p_id }}]" class="form-control form-control-sm text-end attr-esi-employer" value="{{ $payroll->esi_employer ?? 0 }}" min="0" step="0.01">
                                </td>
                                <td style="width: 150px;">
                                    <input type="number" name="lic_others[{{ $payroll->p_id }}]" class="form-control form-control-sm text-end attr-lic-others" value="{{ $payroll->lic_others ?? 0 }}" min="0" step="0.01">
                                </td>
                                <td style="width: 150px;">
                                    <input type="number" name="festival_allowance[{{ $payroll->p_id }}]" class="form-control form-control-sm text-end attr-festival-allowance" value="{{ $payroll->festival_allowance ?? 0 }}" min="0" step="0.01">
                                </td>
                                <td class="text-end fw-semibold">₹{{ number_format((float)($payroll->basic_pay ?? 0), 2) }}</td>
                                <td class="text-end fw-semibold">₹{{ number_format((float)($payroll->gross_salary ?? 0), 2) }}</td>
                                <td class="text-end fw-semibold text-secondary">
                                    ₹{{ number_format((float)($payroll->net_salary ?? 0), 2) }}
                                    <input type="hidden" class="original-net-salary-val" value="{{ $payroll->net_salary ?? 0 }}">
                                </td>
                                <td class="text-end fw-semibold text-success">
                                    ₹<span class="current-net-salary-display">
                                    {{ number_format((float)(
                                        ($payroll->net_salary ?? 0) - 
                                        ($payroll->tds_192_b ?? 0) - 
                                        ($payroll->tds_194_j ?? 0) - 
                                        ($payroll->professional_tax ?? 0) - 
                                        ($payroll->esi_employer ?? 0) - 
                                        ($payroll->lic_others ?? 0) -
                                        ($payroll->festival_allowance ?? 0)
                                    ), 2) }}
                                    </span>
                                </td>
                                <td class="text-center fw-bold text-primary">
                                    <span class="summary-percentage-display">
                                        {{ $payroll->gross_salary > 0 ? number_format((float)((
                                            ($payroll->net_salary ?? 0) - 
                                            ($payroll->tds_192_b ?? 0) - 
                                            ($payroll->tds_194_j ?? 0) - 
                                            ($payroll->professional_tax ?? 0) - 
                                            ($payroll->esi_employer ?? 0) - 
                                            ($payroll->lic_others ?? 0) -
                                            ($payroll->festival_allowance ?? 0)
                                        ) / $payroll->gross_salary) * 100, 1) : 0 }}%
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($payroll->is_frozen == 1)
                                        <span class="badge bg-label-success"><i class="ti ti-check me-1 ti-xs"></i> Issued</span>
                                    @else
                                        <span class="badge bg-label-warning"><i class="ti ti-clock me-1 ti-xs"></i> Pending</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-primary" title="Save Deductions">Save & Proceed</button>
            </div>
            </form>
        @endif
    </div>
</div>
@endsection

@section('page-script')
<script>
$(document).ready(function() {
    function calculateNetSalary() {
        $('tbody tr').each(function() {
            var row = $(this);
            
            // Get original net salary and gross salary
            var originalNetSalary = parseFloat(row.find('.original-net-salary-val').val()) || 0;
            var grossSalary = parseFloat(row.find('.gross-salary-val').val()) || 0;
            
            // Get new input deductions
            var tds192 = parseFloat(row.find('.tds-192-b').val()) || 0;
            var tds194 = parseFloat(row.find('.tds-194-j').val()) || 0;
            var pt = parseFloat(row.find('.attr-pt').val()) || 0;
            var esiEmp = parseFloat(row.find('.attr-esi-employer').val()) || 0;
            var licOther = parseFloat(row.find('.attr-lic-others').val()) || 0;
            var festivalAllowance = parseFloat(row.find('.attr-festival-allowance').val()) || 0;
            
            // New deductions sum
            var newDeductions = tds192 + tds194 + pt + esiEmp + licOther + festivalAllowance;
            
            // Current Net Salary
            var currentNetSalary = originalNetSalary - newDeductions;
            
            // Calculate Percentage (Current Net / Gross * 100)
            var percentage = 0;
            if (grossSalary > 0) {
                percentage = (currentNetSalary / grossSalary) * 100;
            }
            
            // Update UI
            row.find('.current-net-salary-display').text(currentNetSalary.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            row.find('.summary-percentage-display').text(percentage.toFixed(1) + '%');
        });
    }

    // Trigger calculation on input change
    $(document).on('input', '.tds-192-b, .tds-194-j, .attr-pt, .attr-esi-employer, .attr-lic-others, .attr-festival-allowance', function() {
        calculateNetSalary();
    });
});
</script>
@endsection
