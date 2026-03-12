@extends('layouts/layoutMaster')

@section('title', 'Deduction Master - Frozen Employees')

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">PMS / Deduction Master /</span> Frozen Employees
</h4>

<div class="card mb-4">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title mb-0">Step 2: Edit Deductions <span class="badge bg-success ms-2" style="font-size: 0.65rem;">Math V2 + Logging Active</span></h5>
            <small class="text-muted">
                Displaying all active frozen employees across all months. Only deductions enabled in the employee's Deduction Master profile are shown as editable.
            </small>
        </div>
        <div>
             <a href="{{ route('pms.pay-item-master.index', $project_id ?? '') }}" class="btn btn-label-primary me-2">
                <i class="ti ti-settings me-1"></i> Pay Item Master
            </a>
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
                
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Employee Name</th>
                            <th>ID</th>
                            <th>Month/Year</th>
                            <th>Type</th>
                            <th>Role</th>
                            <th>Bank Name</th>
                            <th>Account No.</th>
                            <th>IFSC Code</th>
                            <th>Branch</th>
                            <th style="width: 80px;">CL</th>
                            <th style="width: 80px;">SL</th>
                            <th style="width: 80px;">PL</th>
                            <th style="width: 80px;">LOP</th>
                            <th style="width: 100px;">Employer Contribution</th>
                            <th style="width: 100px;">Arrear (+)</th>
                            <th style="width: 180px;">TDS</th>
                            <th style="width: 180px;">EPF</th>
                            <th style="width: 180px;">PF</th>
                            <th style="width: 180px;">EDLI</th>
                            <th style="width: 180px;">TDS 192 B</th>
                            <th style="width: 180px;">TDS 194 J</th>
                            <th style="width: 180px;">ESI EMPLOYER</th>
                            <th style="width: 180px;">LIC</th>
                            <th style="width: 150px;">Prof. Tax</th>
                            <th style="width: 150px;">Festival Allowance</th>
                            <th style="width: 150px;">Bonus</th>
                            <th style="width: 180px;">Other</th>
                            <th>Base Salary</th>
                            <th>Gross Salary</th>
                            <th>Net Salary</th>
                            <th>Current Net Salary</th>
                            <th>Summary %</th>
                            <th>Salary Slip</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            if (!function_exists('resolveDeductionUI')) {
                                function resolveDeductionUI($savedAmount, $dmValue, $dmType, $dmAmount) {
                                    $savedAmount = (float)($savedAmount ?? 0);
                                    $dmAmount = (float)($dmAmount ?? 0);
                                    $dmValue = (float)($dmValue ?? 0);
                                    
                                    // PRIORITY 1: If a specific value already exists in the payroll record (e.g. from Pay Item Master),
                                    // we MUST show that as the active value.
                                    if ($savedAmount > 0) {
                                        return ['value' => $savedAmount, 'type' => 'amount', 'amt' => $savedAmount];
                                    }
                                    
                                    // PRIORITY 2: If no monthly override exists, fall back to the Master Profile projection.
                                    if ($dmAmount > 0 || $dmValue > 0) {
                                        return ['value' => $dmValue, 'type' => $dmType ?: 'amount', 'amt' => $dmAmount];
                                    }
                                    
                                    // PRIORITY 3: Absolute fallback to zero.
                                    return ['value' => 0, 'type' => 'amount', 'amt' => 0];
                                }
                            }
                        @endphp
                        @foreach($frozenPayrolls as $index => $payroll)
                            @php
                                // Resolve UI for all 10 deduction types
                                $uiTds = resolveDeductionUI($payroll->tds ?? 0, $payroll->dm_tds_value, $payroll->dm_tds_type, $payroll->dm_tds_amount);
                                $uiEpf = resolveDeductionUI($payroll->epf_employers_share ?? 0, $payroll->dm_epf_value, $payroll->dm_epf_type, $payroll->dm_epf_amount);
                                $uiPf = resolveDeductionUI($payroll->pf ?? 0, $payroll->dm_pf_value, $payroll->dm_pf_type, $payroll->dm_pf_amount);
                                $uiEdli = resolveDeductionUI($payroll->edli_charges ?? 0, $payroll->dm_edli_value, $payroll->dm_edli_type, $payroll->dm_edli_amount);
                                $ui192 = resolveDeductionUI($payroll->tds_192_b ?? 0, $payroll->dm_tds_192_b_value, $payroll->dm_tds_192_b_type, $payroll->dm_tds_192_b_amount);
                                $ui194 = resolveDeductionUI($payroll->tds_194_j ?? 0, $payroll->dm_tds_194_j_value, $payroll->dm_tds_194_j_type, $payroll->dm_tds_194_j_amount);
                                $uiPt = resolveDeductionUI($payroll->payroll_professional_tax ?? 0, $payroll->dm_professional_tax_value, $payroll->dm_professional_tax_type, $payroll->dm_professional_tax_amount);
                                $uiEsi = resolveDeductionUI($payroll->esi_employer ?? 0, $payroll->dm_esi_employer_value, $payroll->dm_esi_employer_type, $payroll->dm_esi_employer_amount);
                                $uiLic = resolveDeductionUI($payroll->lic_others ?? 0, $payroll->dm_lic_value, $payroll->dm_lic_type, $payroll->dm_lic_amount);
                                $uiOther = resolveDeductionUI($payroll->others ?? 0, $payroll->dm_other_value, $payroll->dm_other_type, $payroll->dm_other_amount);
                                $uiFa = resolveDeductionUI($payroll->payroll_festival_allowance ?? 0, $payroll->dm_festival_value, $payroll->dm_festival_type, $payroll->dm_festival_amount);
                                $uiBonus = resolveDeductionUI($payroll->payroll_bonus ?? 0, $payroll->dm_bonus_value, $payroll->dm_bonus_type, $payroll->dm_bonus_amount);
                                
                                // Compute the PRORATED salary from frozen components
                                $totalWorkingDays = (float)($payroll->total_working_days ?? 0);
                                $daysWorked = (float)($payroll->days_worked ?? 0);
                                $grossSalary = (float)($payroll->gross_salary ?? 0);
                                $arrear = (float)($payroll->other_allowance ?? 0);
                                
                                // Prorated base is already computed as gross_salary in Salary Management
                                $proratedSalary = $grossSalary;
                                
                                // Gross Salary (before deductions) = prorated + arrear + festival allowance (earning) + bonus (earning)
                                $computedGrossSalary = $proratedSalary + $arrear + $uiFa['amt'] + $uiBonus['amt'];
                                
                                // Sum ALL deduction amounts for net salary computation
                                $allDeductionAmts = $uiTds['amt'] + $uiEpf['amt'] + $uiPf['amt'] + $uiEdli['amt'] +
                                                    $ui192['amt'] + $ui194['amt'] + $uiPt['amt'] + $uiEsi['amt'] + 
                                                    $uiLic['amt'] + $uiOther['amt'];
                                
                                $computedNetSalary = $computedGrossSalary - $allDeductionAmts;
                                $displayedPercentage = ($grossSalary > 0) ? ($computedNetSalary / $grossSalary) * 100 : 0;
                            @endphp
                            <tr>
                                <input type="hidden" name="p_id[]" value="{{ $payroll->p_id }}">
                                <input type="hidden" name="months[{{ $index }}]" value="{{ $payroll->paymonth }}">
                                <input type="hidden" name="years[{{ $index }}]" value="{{ $payroll->year }}">
                                <input type="hidden" class="gross-salary-val" value="{{ $payroll->gross_salary ?? 0 }}">
                                <input type="hidden" class="computed-gross-salary-val" value="{{ $computedGrossSalary }}">
                                <input type="hidden" class="base-computed-gross-val" value="{{ $proratedSalary + $arrear }}">
                                <input type="hidden" class="basic-pay-val" value="{{ $payroll->basic_pay ?? 0 }}">
                                <input type="hidden" class="da-val" value="{{ $payroll->da ?? 0 }}">
                                <input type="hidden" class="employment-type-val" value="{{ $payroll->employment_type ?? '' }}">
                                
                                <td class="fw-semibold">{{ $payroll->name }}</td>
                                <td class="small text-muted">{{ $payroll->p_id }}</td>
                                <td><span class="badge bg-label-primary">{{ $payroll->paymonth }} {{ $payroll->year }}</span></td>
                                <td><span class="badge bg-label-info">{{ $payroll->employment_type }}</span></td>
                                <td>{{ $payroll->role ?? 'N/A' }}</td>
                                <td>{{ $payroll->bank_name ?? 'N/A' }}</td>
                                <td>{{ $payroll->account_no ?? 'N/A' }}</td>
                                <td>{{ $payroll->ifsc_code ?? 'N/A' }}</td>
                                <td>{{ $payroll->branch ?? 'N/A' }}</td>
                                <td class="text-center">{{ $payroll->cl_days ?? 0 }}</td>
                                <td class="text-center">{{ $payroll->sl_days ?? 0 }}</td>
                                <td class="text-center">{{ $payroll->pl_days ?? 0 }}</td>
                                <td class="text-center"><span class="badge bg-label-danger">{{ $payroll->lop_days ?? 0 }}</span></td>
                                
                                <td class="text-end">₹{{ number_format((float)($payroll->employer_contribution ?? 0), 2) }}</td>
                                <td class="text-end text-success">₹{{ number_format((float)($payroll->other_allowance ?? 0), 2) }}</td>

                                {{-- ===== DYNAMIC DEDUCTION COLUMNS ===== --}}
                                @php
                                    // Define all deduction columns with their config
                                    $deductionColumns = [
                                        ['key' => 'tds',              'label' => 'TDS',              'flag' => $payroll->dm_tds_flag || $uiTds['amt'] > 0,              'ui' => $uiTds],
                                        ['key' => 'epf',              'label' => 'EPF',              'flag' => $payroll->dm_epf_flag || $uiEpf['amt'] > 0,              'ui' => $uiEpf],
                                        ['key' => 'pf_ded',           'label' => 'PF',               'flag' => $payroll->dm_pf_flag || $uiPf['amt'] > 0,               'ui' => $uiPf],
                                        ['key' => 'edli',             'label' => 'EDLI',             'flag' => $payroll->dm_edli_flag || $uiEdli['amt'] > 0,             'ui' => $uiEdli],
                                        ['key' => 'tds_192_b',        'label' => 'TDS 192 B',        'flag' => $payroll->dm_tds_192_b_flag || $ui192['amt'] > 0,        'ui' => $ui192],
                                        ['key' => 'tds_194_j',        'label' => 'TDS 194 J',        'flag' => $payroll->dm_tds_194_j_flag || $ui194['amt'] > 0,        'ui' => $ui194],
                                        // Professional Tax is explicitly excluded here as it's governed by Pay Item Master natively
                                        ['key' => 'esi_employer',     'label' => 'ESI EMPLOYER',     'flag' => $payroll->dm_esi_employer_flag || $uiEsi['amt'] > 0,     'ui' => $uiEsi],
                                        ['key' => 'lic_others',       'label' => 'LIC',              'flag' => $payroll->dm_lic_flag || $uiLic['amt'] > 0,              'ui' => $uiLic],
                                        ['key' => 'other_ded',        'label' => 'Other',            'flag' => $payroll->dm_other_flag || $uiOther['amt'] > 0,            'ui' => $uiOther],
                                    ];
                                @endphp

                                @php
                                    // Split deductions to insert Prof Tax and Festival Allowance after LIC
                                    $mainDeductions = array_slice($deductionColumns, 0, 8); // TDS to LIC
                                    $otherDeduction = array_slice($deductionColumns, 8, 1); // Other
                                @endphp

                                @foreach($mainDeductions as $ded)
                                <td style="min-width: 180px;" class="align-middle">
                                    @if($ded['flag'])
                                        <div class="input-group input-group-sm">
                                            <input type="number" class="form-control text-end ded-val" data-target="{{ $ded['key'] }}" data-pid="{{ $payroll->p_id }}" value="{{ $ded['ui']['value'] }}" min="0" step="0.01">
                                            <select class="form-select ded-type" data-target="{{ $ded['key'] }}" data-pid="{{ $payroll->p_id }}" style="max-width: 45px; padding: 0 5px;">
                                                <option value="amount" {{ $ded['ui']['type'] == 'amount' ? 'selected' : '' }}>₹</option>
                                                <option value="percentage" {{ $ded['ui']['type'] == 'percentage' ? 'selected' : '' }}>%</option>
                                            </select>
                                        </div>
                                        <input type="hidden" name="{{ $ded['key'] }}[{{ $index }}]" class="ded-hidden-amt" id="hidden_{{ $ded['key'] }}_{{ $payroll->p_id }}" value="{{ $ded['ui']['amt'] }}">
                                        <small class="text-muted d-block mt-1 text-center">Amt: <strong id="calc_{{ $ded['key'] }}_{{ $payroll->p_id }}">₹{{ number_format($ded['ui']['amt'], 2) }}</strong></small>
                                    @else
                                        <div class="text-center"><span class="text-muted">-</span></div>
                                        <input type="hidden" name="{{ $ded['key'] }}[{{ $index }}]" class="ded-hidden-amt" id="hidden_{{ $ded['key'] }}_{{ $payroll->p_id }}" value="0">
                                    @endif
                                </td>
                                @endforeach

                                <td class="text-center align-middle" style="background-color: #f8f9fa;">
                                    <input type="number" name="professional_tax[{{ $index }}]" class="form-control form-control-sm text-end attr-professional-tax bg-white" value="{{ $uiPt['amt'] }}" style="min-width: 90px; border-color: #7367f0;" min="0" step="0.01" title="Value from Pay Item Master (editable)">
                                </td>
                                <td class="text-center align-middle" style="background-color: #f8f9fa;">
                                    <input type="number" name="festival_allowance[{{ $index }}]" class="form-control form-control-sm text-end attr-festival-allowance bg-white" value="{{ $uiFa['amt'] }}" style="min-width: 90px; border-color: #28c76f;" min="0" step="0.01" title="Value from Pay Item Master (editable)">
                                </td>
                                <td class="text-center align-middle" style="background-color: #f8f9fa;">
                                    <input type="number" name="bonus[{{ $index }}]" class="form-control form-control-sm text-end attr-bonus bg-white" value="{{ $uiBonus['amt'] }}" style="min-width: 90px; border-color: #ff9f43;" min="0" step="0.01" title="Value from Pay Item Master (editable)">
                                </td>

                                @foreach($otherDeduction as $ded)
                                <td style="min-width: 180px;" class="align-middle">
                                    @if($ded['flag'])
                                        <div class="input-group input-group-sm">
                                            <input type="number" class="form-control text-end ded-val" data-target="{{ $ded['key'] }}" data-pid="{{ $payroll->p_id }}" value="{{ $ded['ui']['value'] }}" min="0" step="0.01">
                                            <select class="form-select ded-type" data-target="{{ $ded['key'] }}" data-pid="{{ $payroll->p_id }}" style="max-width: 45px; padding: 0 5px;">
                                                <option value="amount" {{ $ded['ui']['type'] == 'amount' ? 'selected' : '' }}>₹</option>
                                                <option value="percentage" {{ $ded['ui']['type'] == 'percentage' ? 'selected' : '' }}>%</option>
                                            </select>
                                        </div>
                                        <input type="hidden" name="{{ $ded['key'] }}[{{ $index }}]" class="ded-hidden-amt" id="hidden_{{ $ded['key'] }}_{{ $payroll->p_id }}" value="{{ $ded['ui']['amt'] }}">
                                        <small class="text-muted d-block mt-1 text-center">Amt: <strong id="calc_{{ $ded['key'] }}_{{ $payroll->p_id }}">₹{{ number_format($ded['ui']['amt'], 2) }}</strong></small>
                                    @else
                                        <div class="text-center"><span class="text-muted">-</span></div>
                                        <input type="hidden" name="{{ $ded['key'] }}[{{ $index }}]" class="ded-hidden-amt" id="hidden_{{ $ded['key'] }}_{{ $payroll->p_id }}" value="0">
                                    @endif
                                </td>
                                @endforeach


                                <td class="text-end fw-semibold">₹{{ number_format($grossSalary, 2) }}</td>
                                <td class="text-end fw-semibold">₹{{ number_format($computedGrossSalary, 2) }}</td>
                                <td class="text-end fw-semibold text-secondary">
                                    ₹{{ number_format($computedNetSalary, 2) }}
                                </td>
                                <td class="text-end fw-bold text-success">
                                    ₹<span class="current-net-salary-display">{{ number_format($computedNetSalary, 2) }}</span>
                                </td>
                                <td class="text-center fw-bold text-primary">
                                    <span class="summary-percentage-display">{{ number_format($displayedPercentage, 1) }}%</span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        @if($payroll->is_frozen == 1)
                                            <span class="badge bg-label-success" title="Salary Issued"><i class="ti ti-check ti-xs"></i></span>
                                        @else
                                            <span class="badge bg-label-warning" title="Pending"><i class="ti ti-clock ti-xs"></i></span>
                                        @endif
                                        
                                        <div class="btn-group">
                                            <a href="{{ route('pms.deduction-master.salary-slip', [$payroll->p_id, $payroll->paymonth, $payroll->year]) }}" 
                                               class="btn btn-sm btn-icon btn-label-primary" 
                                               target="_blank" 
                                               title="View Salary Slip">
                                                <i class="ti ti-eye"></i>
                                            </a>
                                            <a href="{{ route('pms.deduction-master.salary-slip-pdf', [$payroll->p_id, $payroll->paymonth, $payroll->year]) }}" 
                                               class="btn btn-sm btn-icon btn-label-danger" 
                                               title="Download PDF">
                                                <i class="ti ti-file-type-pdf"></i>
                                            </a>
                                            <a href="{{ route('pms.deduction-master.salary-slip-word', [$payroll->p_id, $payroll->paymonth, $payroll->year]) }}" 
                                               class="btn btn-sm btn-icon btn-label-info" 
                                               title="Download Word">
                                                <i class="ti ti-file-text"></i>
                                            </a>
                                        </div>
                                    </div>
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
        console.log("--- Starting Net Salary Calculation ---");
        $('tbody tr').each(function() {
            var row = $(this);
            var empName = row.find('td:first').text().trim();
            
            // 1. EARNINGS
            // Base Gross = Prorated Salary + Arrears (from hidden fields calculated by PHP)
            var baseGross = parseFloat(row.find('.base-computed-gross-val').val()) || 0;
            var festivalAllowance = Math.abs(parseFloat(row.find('.attr-festival-allowance').val()) || 0);
            var bonus = Math.abs(parseFloat(row.find('.attr-bonus').val()) || 0);
            
            var totalEarnings = baseGross + festivalAllowance + bonus;
            
            // 2. DEDUCTIONS
            var totalDeductions = 0;
            
            // Dynamic deductions
            row.find('.ded-hidden-amt').each(function() {
                totalDeductions += parseFloat($(this).val()) || 0;
            });
            
            // Professional Tax (governed by Pay Item Master)
            var profTax = parseFloat(row.find('.attr-professional-tax').val()) || 0;
            totalDeductions += profTax;
            
            // 3. FINAL NET
            var currentNetSalary = totalEarnings - totalDeductions;
            
            // Percentage based on Original Gross Salary
            var originalGross = parseFloat(row.find('.gross-salary-val').val()) || 0;
            var percentage = (originalGross > 0) ? (currentNetSalary / originalGross) * 100 : 0;
            
            // LOGGING
            console.log(`Employee: ${empName}`);
            console.log(`  > Base Gross (Prorated+Arrear): ${baseGross}`);
            console.log(`  > Festival Allowance (+): ${festivalAllowance}`);
            console.log(`  > Bonus (+): ${bonus}`);
            console.log(`  > Total Earnings: ${totalEarnings}`);
            console.log(`  > Total Deductions (-): ${totalDeductions}`);
            console.log(`  > Computed Net: ${currentNetSalary}`);
            
            // Update UI
            row.find('.current-net-salary-display').text(currentNetSalary.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            row.find('.summary-percentage-display').text(percentage.toFixed(1) + '%');
        });
        console.log("--- Calculation Complete ---");
    }

    // Trigger calculation on input change for readonly generated allowances
    $(document).on('input change', '.attr-festival-allowance, .attr-bonus, .attr-professional-tax', function() {
        calculateNetSalary();
    });

    // Handle editable value and type fields for active deductions
    $('.ded-val, .ded-type').on('input change', function() {
        var row = $(this).closest('tr');
        var pid = $(this).data('pid');
        var target = $(this).data('target');
        
        var valInput = row.find('.ded-val[data-target="' + target + '"]');
        var typeDropdown = row.find('.ded-type[data-target="' + target + '"]');
        var hiddenAmountInput = $('#hidden_' + target + '_' + pid);
        var calcText = $('#calc_' + target + '_' + pid);
        
        var val = parseFloat(valInput.val()) || 0;
        var type = typeDropdown.val();
        var grossSalary = parseFloat(row.find('.gross-salary-val').val()) || 0;
        
        var calculatedAmount = 0;
        if (type === 'percentage') {
            var empType = row.find('.employment-type-val').val() || '';
            var baseAmount = grossSalary;
            
            // For PF calculation of Deputation employees (% of Gross selected) -> Only DA + Basic Salary, excluding HRA
            if (empType.toLowerCase() === 'deputation' && target === 'pf_ded') {
                 var basic = parseFloat(row.find('.basic-pay-val').val()) || 0;
                 var da = parseFloat(row.find('.da-val').val()) || 0;
                 baseAmount = basic + da;
            }
            
            calculatedAmount = (val / 100) * baseAmount;
        } else {
            calculatedAmount = val;
        }
        
        // Update hidden form field
        hiddenAmountInput.val(calculatedAmount.toFixed(2));
        
        // Update visible Amt text
        calcText.text('₹' + calculatedAmount.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        
        // Trigger net salary recalculation
        calculateNetSalary();
    });

    // Run calculation once on page load to sync percentage amounts with the CURRENT base salary
    $('.ded-val').trigger('change');
});
</script>
@endsection
