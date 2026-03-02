@extends('layouts/layoutMaster')

@section('title', 'Salary Management - Final Review')

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">PMS / Salary Management /</span> Step 4: Final Review & Submission
</h4>

{{-- Success Message Alert --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <div class="d-flex">
    <i class="ti ti-check me-2"></i>
    <div>{{ session('success') }}</div>
  </div>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <div>
      <h5 class="mb-0">Review & Select Employees for Payroll Processing</h5>
      <small class="text-muted">{{ $month }} {{ $year }}</small>
    </div>
  </div>
  <div class="card-body">
    <form action="{{ route('pms.salary-management.store', $project_id) }}" method="POST" id="summary-form">
      @csrf
      <input type="hidden" name="month" value="{{ $month }}">
      <input type="hidden" name="year" value="{{ $year }}">
      <input type="hidden" name="employment_type" value="{{ $employmentType }}">
      <input type="hidden" name="freeze" id="freeze-input" value="0">



      <div class="table-responsive text-nowrap mb-4">
        <table class="table table-hover">
          <thead>
            <tr>
              <th style="width: 50px;">
                <input type="checkbox" id="select_all" class="form-check-input" checked>
              </th>
              <th>Employee Name</th>
              <th>Role</th>
              <th>Department</th>
              <th>Bank Name</th>
              <th>Account Number</th>
              <th>IFSC Code</th>
              <th class="text-end">Basic Salary</th>
              <th class="text-end">Employer Contribution</th>
              <th class="text-end">Total Calculated Salary</th>
            </tr>
          </thead>
          <tbody>
            @php $grandTotal = 0; @endphp
            @foreach($summaryData as $data)
            @php $isFrozen = $data['is_frozen'] ?? 0; @endphp
            <tr class="employee-row {{ $isFrozen ? 'table-secondary text-muted' : '' }}">
              <td>
                <input type="checkbox" class="form-check-input row-checkbox" value="{{ $data['p_id'] }}" {{ $isFrozen ? 'disabled' : 'checked' }}>
              </td>
              <td>
                <div class="d-flex flex-column">
                    <span class="fw-bold">
                      {{ $data['name'] }}
                      @if($isFrozen)
                        <span class="badge bg-label-secondary ms-2"><i class="ti ti-lock" style="font-size: 10px;"></i> Frozen</span>
                      @endif
                    </span>
                    <small class="text-muted">{{ $data['salary_id'] }}</small>
                </div>
                
                {{-- Hidden Inputs for this employee --}}
                @if(!$isFrozen)
                <div class="hidden-inputs">
                    <input type="hidden" name="p_id[]" value="{{ $data['p_id'] }}">
                    <input type="hidden" name="salary_id[]" value="{{ $data['salary_id'] }}">
                    <input type="hidden" name="monthly_working_days[]" value="{{ $data['working_days'] }}">
                    <input type="hidden" name="days_worked[]" value="{{ $data['days_worked'] }}">
                    <input type="hidden" name="cl_days[]" value="{{ $data['cl_days'] }}">
                    <input type="hidden" name="sl_days[]" value="{{ $data['sl_days'] }}">
                    <input type="hidden" name="pl_days[]" value="{{ $data['pl_days'] }}">
                    <input type="hidden" name="lop_days[]" value="{{ $data['lop_days'] }}">
                    <input type="hidden" name="other_leave_days[]" value="{{ $data['other_leave_days'] }}">
                    <input type="hidden" name="arrear[]" value="{{ str_replace(',', '', $data['arrear']) }}">
                    <input type="hidden" name="employer_contribution[]" value="{{ str_replace(',', '', $data['employer_contribution']) }}">
                    <input type="hidden" name="epf_employers_share[]" value="{{ str_replace(',', '', $data['epf_employers_share'] ?? 0) }}">
                    <input type="hidden" name="edli_charges[]" value="{{ str_replace(',', '', $data['edli_charges'] ?? 0) }}">
                    <input type="hidden" name="pf[]" value="{{ str_replace(',', '', $data['pf'] ?? 0) }}">
                    <input type="hidden" name="base_salary[]" value="{{ $data['base_salary'] }}">
                    <input type="hidden" name="total_salary[]" value="{{ str_replace(',', '', $data['total_salary']) }}">
                </div>
                @endif
              </td>
              <td>{{ $data['role'] }}</td>
              <td>{{ $data['department'] }}</td>
              <td>
                @if(!empty($data['bank_name']))
                    <span class="badge bg-label-primary"><i class="ti ti-building-bank ti-xs me-1"></i> {{ $data['bank_name'] }}</span>
                @else
                    <span class="text-muted">N/A</span>
                @endif
              </td>
              <td>{{ !empty($data['account_no']) ? $data['account_no'] : 'N/A' }}</td>
              <td>{{ !empty($data['ifsc_code']) ? $data['ifsc_code'] : 'N/A' }}</td>
              <td class="text-end">₹{{ number_format($data['base_salary'], 2) }}</td>
              <td class="text-end">₹{{ number_format($data['employer_contribution'], 2) }}</td>
              <td class="text-end fw-bold text-primary">₹{{ number_format($data['total_salary'], 2) }}</td>
            </tr>
            @php $grandTotal += $data['total_salary']; @endphp
            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <th colspan="9" class="text-end align-middle fw-bold">Total Calculated Salary:</th>
              <th class="text-end fw-bold" id="total_salary_sum">₹0.00</th>
            </tr>
            <tr>
              <th colspan="9" class="text-end align-middle fw-bold">Administrative Charge / Service Charge (%):</th>
              <th class="d-flex align-items-center justify-content-end gap-2">
                <input type="number" id="admin_charge_percent" name="admin_charge_percent" class="form-control form-control-sm text-end" value="7.5" step="0.01" style="max-width: 80px;">
                <span id="admin_charge_amount" class="fw-bold text-muted" style="min-width: 80px;">₹0.00</span>
              </th>
            </tr>
            <tr class="table-primary">
              <th colspan="9" class="text-end align-middle fw-bold">Grand Total:</th>
              <th class="text-end fw-bold fs-5" id="grand_total_amount">₹0.00</th>
            </tr>
            <tr class="table-light">
              <th colspan="9" class="text-end align-middle fw-bold">Note for Statement:</th>
              <th>
                <textarea id="statement_note" name="statement_note" class="form-control form-control-sm" rows="2" placeholder="Enter sentence to appear on PDF..."></textarea>
              </th>
            </tr>
          </tfoot>
        </table>
      </div>

      <div class="d-flex justify-content-between align-items-center">
        <div>
            <a href="{{ route('pms.salary-management.calculation', ['project_id' => $project_id, 'month' => $month, 'year' => $year, 'employment_type' => $employmentType]) }}" class="btn btn-label-secondary">Back</a>
            <button type="button" id="btn-generate-statement-modal" data-bs-toggle="modal" data-bs-target="#columnSelectionModal" class="btn btn-info disabled" style="pointer-events: none; opacity: 0.6;">
                <i class="ti ti-file-invoice me-1"></i> Generate Statement
            </button>
        </div>
        <div>
            <button type="button" class="btn btn-primary me-2" id="btn-freeze">
                <i class="ti ti-lock me-1"></i> Freeze (Save Draft)
            </button>
            <button type="button" class="btn btn-success" id="btn-process">
                <i class="ti ti-check me-1"></i> Submit & Process Selected
            </button>
        </div>
      </div>
    </form>
  </div>
</div>

@php
  $hasEpfShare = false;
  $hasEdli = false;
  $hasPf = false;
  $hasEmployerContrib = false;

  foreach($summaryData as $row) {
    if(($row['epf_employers_share'] ?? 0) > 0) $hasEpfShare = true;
    if(($row['edli_charges'] ?? 0) > 0) $hasEdli = true;
    if(($row['pf'] ?? 0) > 0) $hasPf = true;
    if(($row['employer_contribution'] ?? 0) > 0) $hasEmployerContrib = true;
  }
@endphp

<!-- Column Selection Modal -->
<div class="modal fade" id="columnSelectionModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="columnSelectionModalTitle">Select Statement Columns</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="mb-3">Select the columns to include in the generated PDF statement:</p>
        <div class="row">
          <div class="col-md-6 mb-2">
            <div class="form-check">
              <input class="form-check-input column-chk" type="checkbox" value="slno" id="col_slno" checked disabled>
              <label class="form-check-label" for="col_slno">Sl. No (Required)</label>
            </div>
          </div>
          <div class="col-md-6 mb-2">
            <div class="form-check">
              <input class="form-check-input column-chk" type="checkbox" value="name" id="col_name" checked disabled>
              <label class="form-check-label" for="col_name">Name (Required)</label>
            </div>
          </div>
          <div class="col-md-6 mb-2">
            <div class="form-check">
              <input class="form-check-input column-chk" type="checkbox" value="designation" id="col_designation" checked disabled>
              <label class="form-check-label" for="col_designation">Designation (Required)</label>
            </div>
          </div>
          <div class="col-md-6 mb-2">
            <div class="form-check">
              <input class="form-check-input column-chk" type="checkbox" value="doj" id="col_doj" checked>
              <label class="form-check-label" for="col_doj">Date of Joining</label>
            </div>
          </div>
          <div class="col-md-6 mb-2">
            <div class="form-check">
              <input class="form-check-input column-chk" type="checkbox" value="remuneration" id="col_remuneration">
              <label class="form-check-label" for="col_remuneration">Remuneration</label>
            </div>
          </div>
          <div class="col-md-6 mb-2">
            <div class="form-check">
              <input class="form-check-input column-chk" type="checkbox" value="arrear" id="col_arrear" checked>
              <label class="form-check-label" for="col_arrear">Arrears</label>
            </div>
          </div>
          <div class="col-md-6 mb-2">
            <div class="form-check">
              <input class="form-check-input column-chk" type="checkbox" value="epf" id="col_epf">
              <label class="form-check-label" for="col_epf">EPF</label>
            </div>
          </div>
          <div class="col-md-6 mb-2">
            <div class="form-check">
              <input class="form-check-input column-chk" type="checkbox" value="employer_contribution" id="col_empr_contribution" {{ $hasEmployerContrib ? 'checked' : '' }}>
              <label class="form-check-label" for="col_empr_contribution">Employer Contrib.</label>
            </div>
          </div>
          <div class="col-md-6 mb-2">
            <div class="form-check">
              <input class="form-check-input column-chk" type="checkbox" value="epf_employers_share" id="col_epf_employers_share" {{ $hasEpfShare ? 'checked' : '' }}>
              <label class="form-check-label" for="col_epf_employers_share">EPF Employers share</label>
            </div>
          </div>
          <div class="col-md-6 mb-2">
            <div class="form-check">
              <input class="form-check-input column-chk" type="checkbox" value="edli_charges" id="col_edli_charges" {{ $hasEdli ? 'checked' : '' }}>
              <label class="form-check-label" for="col_edli_charges">EDLI & EPF Admin</label>
            </div>
          </div>
          <div class="col-md-6 mb-2">
            <div class="form-check">
              <input class="form-check-input column-chk" type="checkbox" value="pf" id="col_pf" {{ $hasPf ? 'checked' : '' }}>
              <label class="form-check-label" for="col_pf">PF</label>
            </div>
          </div>
          <div class="col-md-6 mb-2">
            <div class="form-check">
              <input class="form-check-input column-chk" type="checkbox" value="deduction" id="col_deduction" checked>
              <label class="form-check-label" for="col_deduction">Deductions</label>
            </div>
          </div>
          <div class="col-md-6 mb-2">
            <div class="form-check">
              <input class="form-check-input column-chk" type="checkbox" value="payable" id="col_payable" checked>
              <label class="form-check-label" for="col_payable">Payable Remuneration</label>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="btn-confirm-generate" data-url="{{ route('pms.salary-management.statement', ['project_id' => $project_id, 'month' => $month, 'year' => $year, 'employment_type' => $employmentType]) }}">Generate PDF</button>
      </div>
    </div>
  </div>
</div>

@section('page-script')
<script>
$(function() {
    // 1. Select All Functionality
    $('#select_all').on('change', function() {
        var isChecked = $(this).is(':checked');
        $('.row-checkbox').prop('checked', isChecked);
        calculateGrandTotal();
    });

    // 2. Update Select All state logic
    $('.row-checkbox').on('change', function() {
        var allChecked = $('.row-checkbox').length === $('.row-checkbox:checked').length;
        $('#select_all').prop('checked', allChecked);
        calculateGrandTotal();
    });

    // Calculate Grand Total logic
    function calculateGrandTotal() {
        var baseTotal = 0;
        $('.row-checkbox:checked').each(function() {
            var row = $(this).closest('tr');
            var salary = parseFloat(row.find('.hidden-inputs input[name="total_salary[]"]').val()) || 0;
            var empContrib = parseFloat(row.find('.hidden-inputs input[name="employer_contribution[]"]').val()) || 0;
            baseTotal += (salary + empContrib);
        });

        var adminPercent = parseFloat($('#admin_charge_percent').val()) || 0;
        var adminAmount = (baseTotal * adminPercent) / 100;
        var grandTotal = baseTotal + adminAmount;

        $('#total_salary_sum').text('₹' + baseTotal.toFixed(2));
        $('#admin_charge_amount').text('₹' + adminAmount.toFixed(2));
        $('#grand_total_amount').text('₹' + grandTotal.toFixed(2));
    }

    $('#admin_charge_percent').on('input change', calculateGrandTotal);
    
    // Initial call
    calculateGrandTotal();

    // 3. Freeze vs Process Logic
    $('#btn-freeze').on('click', function() {
        $('#freeze-input').val('1');
        submitForm();
    });

    $('#btn-process').on('click', function() {
        $('#freeze-input').val('0');
        submitForm();
    });

    // 4. Generate Statement Logic - Precheck before opening Modal
    $('#btn-generate-statement-modal').on('click', function(e) {
        if ($('.row-checkbox:checked').length === 0) {
            e.preventDefault();
            e.stopPropagation();
            alert('Please select at least one employee to generate the statement.');
            $('#columnSelectionModal').modal('hide');
        }
    });

    // 4b. Execute Generation from Modal
    $('#btn-confirm-generate').on('click', function() {
        var selectedIds = [];
        $('.row-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            alert('Please select at least one employee to generate the statement.');
            return;
        }

        // Harvest selected columns
        var selectedColumns = [];
        $('.column-chk:checked').each(function() {
            selectedColumns.push($(this).val());
        });

        var adminCharge = $('#admin_charge_percent').val() || 0;
        var note = $('#statement_note').val() || '';
        var baseUrl = $(this).data('url');
        var fullUrl = baseUrl + '?p_ids=' + selectedIds.join(',') + '&admin_charge=' + encodeURIComponent(adminCharge) + '&columns=' + encodeURIComponent(selectedColumns.join(',')) + '&note=' + encodeURIComponent(note);
        
        // Hide modal
        var modalEl = document.getElementById('columnSelectionModal');
        var modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) {
            modal.hide();
        }

        window.open(fullUrl, '_blank');
    });

    // 5. AJAX Submission Function
    function submitForm() {
        var form = $('#summary-form');
        
        // We need to disable inputs for unchecked rows so they are not submitted
        $('.employee-row').each(function() {
            var row = $(this);
            if (!row.find('.row-checkbox').is(':checked')) {
                row.find('.hidden-inputs input').prop('disabled', true);
            } else {
                row.find('.hidden-inputs input').prop('disabled', false);
            }
        });
        
        // Check if at least one is selected
        if ($('.row-checkbox:checked').length === 0) {
            alert('Please select at least one employee to process.');
            // Re-enable inputs just in case they want to correct it
            $('.hidden-inputs input').prop('disabled', false);
            return;
        }

        // Prepare data
        var formData = form.serialize();

        // Send AJAX Request
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Check for Redirect (Freeze action)
                    if (response.redirect_url) {
                        window.location.href = response.redirect_url;
                        return;
                    }

                    // Show Success Alert
                    var alertHtml = `
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                          <div class="d-flex">
                            <i class="ti ti-check me-2"></i>
                            <div>${response.message}</div>
                          </div>
                          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                    
                    // Remove existing alerts if any
                    $('.alert').remove();
                    
                    // Prepend new alert to content area
                    $('.fw-bold.py-3.mb-4').after(alertHtml);
                    
                    // Scroll to top
                    $('html, body').animate({ scrollTop: 0 }, 'fast');

                    // Enable Generate Statement Button if not frozen
                    if ($('#freeze-input').val() == '0') {
                        $('#btn-generate-statement-modal').removeClass('disabled').css({
                            'pointer-events': 'auto',
                            'opacity': '1'
                        });
                    }

                } else {
                    alert('Error: ' + response.message);
                }
                
                // Re-enable inputs for potential next action
                $('.hidden-inputs input').prop('disabled', false);
            },
            error: function(xhr) {
                var msg = 'An error occurred.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                alert(msg);
                $('.hidden-inputs input').prop('disabled', false);
            }
        });
    }

    // Prevent default form submit if triggered differently
    $('#summary-form').on('submit', function(e) {
        e.preventDefault();
        // Determine which button triggered? Default to process if enter key
        $('#freeze-input').val('0'); 
        submitForm();
    });
});
</script>
@endsection
@endsection
