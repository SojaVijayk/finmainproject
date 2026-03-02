@extends('layouts/layoutMaster')

@section('title', 'Salary Management - Calculation')

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">PMS / Salary Management /</span> Step 3: Calculation & Processing
</h4>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <div>
      <h5 class="mb-0">Payroll Calculation for {{ $month }} {{ $year }}</h5>
      <span class="badge bg-label-info">{{ ucfirst($employmentType) }}</span>
    </div>
  </div>
  <div class="card-body">
    <form action="{{ route('pms.salary-management.store', $project_id) }}" method="POST" id="payroll-form">
      @csrf
      <input type="hidden" name="month" value="{{ $month }}">
      <input type="hidden" name="year" value="{{ $year }}">
      <input type="hidden" name="employment_type" value="{{ $employmentType }}">
      <input type="hidden" name="freeze" id="freeze-input" value="0">

      <div class="table-responsive text-nowrap mb-4">
        <table class="table table-bordered table-sm">
          <thead>
            <tr class="text-center">
              <th>Employee Name</th>
              <th>Designation</th>
              <th style="width: 150px;">Salary ID</th>
              <th style="width: 100px;">Total Days in Month</th>
              <th style="width: 120px;">Total Working Days</th>
              <th style="width: 120px;">Days Worked</th>
              <th style="width: 100px;">CL</th>
              <th style="width: 100px;">SL</th>
              <th style="width: 100px;">PL</th>
              <th style="width: 100px;">LOP</th>
              <th style="width: 120px;">EPF Employers share @ 12%</th>
              <th style="width: 120px;">EDLI contribution and EPF admin charge</th>
              <th style="width: 100px;">PF</th>
              <th style="width: 100px;">Employer Contribution</th>
              <th style="width: 100px;">Arrear (+)</th>
              <th style="width: 100px;">Other</th>
              <th style="width: 80px;">Payable Days</th>
              <th>Base Salary</th>
              <th>Total Calculated Salary</th>
            </tr>
          </thead>
          <tbody>
            @foreach($employees as $employee)
            @php $isFrozen = $employee->is_frozen; @endphp
            <tr class="{{ $isFrozen ? 'table-secondary text-muted' : '' }}">
              <td>
                {{ $employee->name }}
                @if($isFrozen)
                  <span class="badge bg-label-secondary ms-2"><i class="ti ti-lock" style="font-size: 10px;"></i> Frozen</span>
                @endif
                <input type="hidden" name="p_id[]" value="{{ $employee->p_id }}">
              </td>
              <td>{{ $employee->designation ?? $employee->role ?? 'N/A' }}</td>
              <td>
                <input type="text" name="salary_id[]" class="form-control form-control-sm" placeholder="e.g. SAL-001" value="{{ $defaultSalaryId ?? '' }}" {{ $isFrozen ? 'readonly' : '' }}>
              </td>
              <td>
                <input type="number" class="form-control form-control-sm text-center month-days-val" value="{{ $actualDaysInMonth }}" readonly disabled>
                <input type="hidden" class="month-days-val" value="{{ $actualDaysInMonth }}">
              </td>
              <td>
                <input type="number" name="monthly_working_days[]" class="form-control form-control-sm text-center working-days" value="{{ $totalDays }}" min="1" max="40" required {{ $isFrozen ? 'readonly tabindex=-1' : '' }}>
              </td>
              <td>
                <input type="number" name="days_worked[]" class="form-control form-control-sm text-center days-worked" value="{{ $actualDaysInMonth - $employee->lop_days }}" min="0" max="{{ $actualDaysInMonth }}" step="0.5" required {{ $isFrozen ? 'readonly tabindex=-1' : '' }}>
              </td>
              <td>
                <input type="number" name="cl_days[]" class="form-control form-control-sm text-center cl-days" value="{{ $employee->cl_days }}" min="0" step="0.5" style="min-width: 80px;" {{ $isFrozen ? 'readonly tabindex=-1' : '' }}>
              </td>
              <td>
                <input type="number" name="sl_days[]" class="form-control form-control-sm text-center sl-days" value="{{ $employee->sl_days }}" min="0" step="1" style="min-width: 80px;" {{ $isFrozen ? 'readonly tabindex=-1' : '' }}>
              </td>
              <td>
                <input type="number" name="pl_days[]" class="form-control form-control-sm text-center pl-days" value="{{ $employee->pl_days }}" min="0" step="0.5" style="min-width: 80px;" {{ $isFrozen ? 'readonly tabindex=-1' : '' }}>
              </td>
              <td>
                <input type="number" name="lop_days[]" class="form-control form-control-sm text-center lop-days" value="{{ $employee->lop_days }}" min="0" step="0.5" style="min-width: 80px;" {{ $isFrozen ? 'readonly tabindex=-1' : '' }}>
              </td>
              <td>
                <input type="number" name="epf_employers_share[]" class="form-control form-control-sm text-end epf-employers-share" value="{{ $employee->epf_employers_share ?? 0 }}" min="0" step="0.01" style="min-width: 80px;" {{ $isFrozen ? 'readonly tabindex=-1' : '' }}>
              </td>
              <td>
                <input type="number" name="edli_charges[]" class="form-control form-control-sm text-end edli-charges" value="{{ $employee->edli_charges ?? 0 }}" min="0" step="0.01" style="min-width: 80px;" {{ $isFrozen ? 'readonly tabindex=-1' : '' }}>
              </td>
              <td>
                <input type="number" name="pf[]" class="form-control form-control-sm text-end pf-contribution" value="{{ $employee->pf ?? 0 }}" min="0" step="0.01" style="min-width: 80px;" {{ $isFrozen ? 'readonly tabindex=-1' : '' }}>
              </td>
              <td>
                <input type="number" name="employer_contribution[]" class="form-control form-control-sm text-end employer-contribution" value="{{ $employee->employer_contribution ?? 0 }}" min="0" step="0.01" style="min-width: 80px;" {{ $isFrozen ? 'readonly tabindex=-1' : '' }}>
              </td>
              <td>
                <input type="number" name="arrear[]" class="form-control form-control-sm text-end arrear" value="0" min="0" step="0.01" style="min-width: 80px;" {{ $isFrozen ? 'readonly tabindex=-1' : '' }}>
              </td>
              <td>
                <input type="number" name="other_leave_days[]" class="form-control form-control-sm text-center other-leaves" value="{{ $employee->other_leave_days }}" min="0" step="0.5" style="min-width: 80px;" {{ $isFrozen ? 'readonly tabindex=-1' : '' }}>
              </td>
              <td>
                <input type="number" class="form-control form-control-sm text-center payable-days" value="{{ ($actualDaysInMonth - $employee->lop_days) + $employee->cl_days + $employee->sl_days + $employee->pl_days + $employee->other_leave_days }}" readonly disabled>
              </td>
              <td>
                <input type="number" name="base_salary[]" class="form-control form-control-sm text-end base-salary" value="{{ $employee->consolidated_pay }}" readonly>
              </td>
              <td>
                @php
                  $initialTotal = ($totalDays > 0) ? ($employee->consolidated_pay / $totalDays) * ($actualDaysInMonth - $employee->lop_days) : 0;
                @endphp
                <input type="number" step="0.01" name="total_salary[]" class="form-control form-control-sm text-end total-salary" value="{{ number_format($initialTotal, 2, '.', '') }}" readonly>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="d-flex justify-content-between align-items-center">
        <a href="{{ route('pms.salary-management.select-employees', ['project_id' => $project_id, 'month' => $month, 'year' => $year, 'employment_type' => $employmentType]) }}" class="btn btn-label-secondary">Back</a>
        <div class="d-flex gap-2">

          <button type="button" class="btn btn-success" onclick="submitForm('{{ route('pms.salary-management.summary', $project_id) }}', '0')">
            <i class="ti ti-arrow-right me-1"></i> Process (Next)
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@section('page-script')
<script>
function submitForm(action, freezeValue) {
  const form = document.getElementById('payroll-form');
  document.getElementById('freeze-input').value = freezeValue;
  form.action = action;
  form.submit();
}
$(function() {
    function calculate(triggerSource) {
        $('tbody tr').each(function() {
            var row = $(this);
            // 1. Get Base Values
            var workingDays = parseFloat(row.find('.working-days').val());
            var baseSalary = parseFloat(row.find('.base-salary').val());
           
            // Safeguard
            if (isNaN(workingDays)) workingDays = 0;
            if (isNaN(baseSalary)) baseSalary = 0;

            // 2. Get Leave Values
            var cl = parseFloat(row.find('.cl-days').val()) || 0;
            var sl = parseFloat(row.find('.sl-days').val()) || 0;
            var pl = parseFloat(row.find('.pl-days').val()) || 0;
            var other = parseFloat(row.find('.other-leaves').val()) || 0;
            var lop = parseFloat(row.find('.lop-days').val()) || 0;

            // Get Arrear/Contributions/Detailed fields
            var epf_employers_share = parseFloat(row.find('.epf-employers-share').val()) || 0;
            var edli_charges = parseFloat(row.find('.edli-charges').val()) || 0;
            var pf = parseFloat(row.find('.pf-contribution').val()) || 0;
            var arrear = parseFloat(row.find('.arrear').val()) || 0;
            var employer_contribution = parseFloat(row.find('.employer-contribution').val()) || 0;

            // 3. Calculate Physical Attendance
            // Logic: If user MANUALLY edited 'days-worked', respect it.
            // Also: If the user is changing 'Total Working Days', do NOT change 'Days Worked' (keep it independent)
            var daysWorkedInput = row.find('.days-worked');
            var isManual = daysWorkedInput.data('manual') === true;
            
            // If trigger matches days-worked, mark as manual
            if (triggerSource && $(triggerSource).is(daysWorkedInput)) {
                isManual = true;
                daysWorkedInput.data('manual', true);
            }

            var physicalDays = parseFloat(daysWorkedInput.val()) || 0;

            // Only auto-calculate if:
            // 1. Not Manual
            // 2. The trigger is NOT the working-days input (changing denominator shouldn't change numerator)
            var isWorkingDaysTrigger = triggerSource && $(triggerSource).hasClass('working-days');
            
            if (!isManual && !isWorkingDaysTrigger) {
                physicalDays = Math.max(0, workingDays - lop - cl - sl - pl - other);
                daysWorkedInput.val(physicalDays.toFixed(1));
            }

             // 4. Calculate Payable Days (Physical + Paid Leaves)
            var payableDays = 0;
            if (isManual) {
                // If Manual Mode: Trust the "Days Worked" as Physical attendance.
                // But if user enters LOP, they expect a deduction from that manual baseline.
                // Also adding Paid Leaves (CL/SL/PL) normally.
                payableDays = Math.max(0, physicalDays + cl + sl + pl + other - lop);
            } else {
                // Auto Mode: physicalDays already has LOP subtracted (Working - LOP - Leaves)
                // So we just add back the paid leaves.
                payableDays = physicalDays + cl + sl + pl + other;
            }
            
            row.find('.payable-days').val(payableDays.toFixed(1));

            // 5. Calculate Total Salary
            if (workingDays > 0) {
                // Basic Prorated Calcluation
                var prorated = (baseSalary / workingDays) * payableDays;

                // Net Payable = Prorated + Arrear + EPF Employer Share + EDLI
                // User requirement: "change the total calculated salary except the employer contribution"
                // (We exclude the old employer_contribution field from this math)
                var total = prorated + arrear + epf_employers_share + edli_charges;
                
                // Ensure non-negative
                total = Math.max(0, total);

                row.find('.total-salary').val(total.toFixed(2));
            } else {
                row.find('.total-salary').val("0.00");
            }
        });
    }

    // Listen to inputs
    $(document).on('input change', '.working-days, .days-worked, .base-salary, .cl-days, .sl-days, .pl-days, .other-leaves, .lop-days, .arrear, .employer-contribution, .epf-employers-share, .edli-charges, .pf-contribution', function() {
        calculate(this);
    });

    // Initial calculation
    calculate();
});
</script>
@endsection
