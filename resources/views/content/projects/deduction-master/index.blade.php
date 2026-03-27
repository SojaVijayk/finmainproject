@extends('layouts/layoutMaster')

@section('title', 'Salary Deduction Management - Active Employees')

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">PMS /</span> Salary Deduction Management
</h4>

<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Active Frozen Employees</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('pms.employees.project-index', ['id' => $project_id]) }}" class="btn btn-label-secondary btn-sm">
                        <i class="ti ti-arrow-left me-1 ti-xs"></i> Back
                    </a>
                    <a href="{{ route('pms.pay-item-master.index', $project_id ?? '') }}" class="btn btn-label-secondary btn-sm" title="Pay Item Master">
                        <i class="ti ti-settings me-1 ti-xs"></i> Pay Item Master
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($frozenBatches->isEmpty())
                    <div class="alert alert-warning" role="alert">
                        No frozen salary bills available for deduction processing. Please freeze employees in Salary Management first.
                    </div>
                @else
                    <div class="table-responsive text-nowrap mt-3">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Batch / Salary ID</th>
                                    <th>Period</th>
                                    <th>Employment Type</th>
                                    <th class="text-center">Total Employees</th>
                                    <th class="text-end">Total Net (Base)</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($frozenBatches as $batch)
                                    <tr>
                                        <td>
                                            <span class="fw-semibold text-primary">
                                                <i class="ti ti-file-invoice me-1"></i> {{ $batch->salary_id }}
                                            </span>
                                        </td>
                                        <td>{{ $batch->paymonth }} {{ $batch->year }}</td>
                                        <td><span class="badge bg-label-info">{{ $batch->employment_type }}</span></td>
                                        <td class="text-center">
                                            <span class="badge rounded-pill bg-label-secondary">{{ $batch->employee_count }}</span>
                                        </td>
                                        <td class="text-end fw-semibold">₹{{ number_format($batch->total_net, 2) }}</td>
                                        <td class="text-center">
                                            <form action="{{ route('pms.deduction-master.select-employees', $project_id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="salary_id" value="{{ $batch->salary_id }}">
                                                <input type="hidden" name="month" value="{{ $batch->paymonth }}">
                                                <input type="hidden" name="year" value="{{ $batch->year }}">
                                                <input type="hidden" name="employment_type" value="{{ $batch->employment_type }}">
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    <i class="ti ti-edit me-1 ti-xs"></i> Process Deductions
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info py-2 mb-0 mt-3" role="alert">
                        <i class="ti ti-info-circle me-1 ti-xs"></i> Only frozen salary bills are listed. Select a bill to view and edit deductions for the employees within that batch.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header border-bottom">
                <h5 class="mb-0">Confirmed / Proceeded Bills</h5>
            </div>
            <div class="card-body">
                @if($proceededBatches->isEmpty())
                    <div class="alert alert-info mt-3" role="alert">
                        No proceeded salary bills available. Once you process and confirm deductions for a frozen batch, it will appear here.
                    </div>
                @else
                    <div class="table-responsive text-nowrap mt-3">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Batch / Salary ID</th>
                                    <th>Period</th>
                                    <th>Employment Type</th>
                                    <th class="text-center">Total Employees</th>
                                    <th class="text-end">Total Net (Base)</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($proceededBatches as $batch)
                                    <tr>
                                        <td>
                                            <span class="fw-semibold text-success">
                                                <i class="ti ti-check me-1"></i> {{ $batch->salary_id }}
                                            </span>
                                        </td>
                                        <td>{{ $batch->paymonth }} {{ $batch->year }}</td>
                                        <td><span class="badge bg-label-info">{{ $batch->employment_type }}</span></td>
                                        <td class="text-center">
                                            <span class="badge rounded-pill bg-label-secondary">{{ $batch->employee_count }}</span>
                                        </td>
                                        <td class="text-end fw-semibold">₹{{ number_format($batch->total_net, 2) }}</td>
                                        <td class="text-center">
                                            <form action="{{ route('pms.deduction-master.select-employees', $project_id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="salary_id" value="{{ $batch->salary_id }}">
                                                <input type="hidden" name="month" value="{{ $batch->paymonth }}">
                                                <input type="hidden" name="year" value="{{ $batch->year }}">
                                                <input type="hidden" name="employment_type" value="{{ $batch->employment_type }}">
                                                <button type="submit" class="btn btn-sm btn-outline-success">
                                                    <i class="ti ti-eye me-1 ti-xs"></i> View Details
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-sm btn-outline-info ms-1" onclick="openStatementModal('{{ $batch->salary_id }}', '{{ $batch->paymonth }}', '{{ $batch->year }}', '{{ $batch->employment_type }}')">
                                                <i class="ti ti-download me-1 ti-xs"></i> Statement
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Column Selection Modal for Statement -->
<div class="modal fade" id="columnSelectionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCenterTitle">Select Columns for Statement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="statementForm" action="{{ route('pms.deduction-master.statement-preview', $project_id) }}" method="GET" target="_blank">
                <input type="hidden" name="salary_id" id="modal_salary_id">
                <input type="hidden" name="month" id="modal_month">
                <input type="hidden" name="year" id="modal_year">
                <input type="hidden" name="employment_type" id="modal_employment_type">
                
                <div class="modal-body">
                    <p class="text-muted small mb-3">Check the columns you want to include in the generated Excel statement.</p>
                    <div class="row">
                        <div class="col-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="slno" id="col_slno" checked>
                                <label class="form-check-label" for="col_slno">Sl. No</label>
                            </div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="name" id="col_name" checked>
                                <label class="form-check-label" for="col_name">Names</label>
                            </div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="designation" id="col_designation" checked>
                                <label class="form-check-label" for="col_designation">Designation</label>
                            </div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="gross_salary" id="col_gross" checked>
                                <label class="form-check-label" for="col_gross">Gross Salary</label>
                            </div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="date_of_joining" id="col_doj" checked>
                                <label class="form-check-label" for="col_doj">Date of Joining</label>
                            </div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="bank_name" id="col_bank_name" checked>
                                <label class="form-check-label" for="col_bank_name">Bank Name</label>
                            </div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="account_no" id="col_account_no" checked>
                                <label class="form-check-label" for="col_account_no">Account No</label>
                            </div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="ifsc_code" id="col_ifsc_code" checked>
                                <label class="form-check-label" for="col_ifsc_code">IFSC Code</label>
                            </div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="branch" id="col_branch" checked>
                                <label class="form-check-label" for="col_branch">Branch</label>
                            </div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="pan_number" id="col_pan_number" checked>
                                <label class="form-check-label" for="col_pan_number">PAN Number</label>
                            </div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="address" id="col_address" checked>
                                <label class="form-check-label" for="col_address">Address</label>
                            </div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="email" id="col_email" checked>
                                <label class="form-check-label" for="col_email">Email</label>
                            </div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="mobile" id="col_mobile" checked>
                                <label class="form-check-label" for="col_mobile">Phone Number</label>
                            </div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="festival_allowance" id="col_festival" checked>
                                <label class="form-check-label" for="col_festival">Festival Allowance</label>
                            </div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="bonus" id="col_bonus" checked>
                                <label class="form-check-label" for="col_bonus">Bonus</label>
                            </div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="tds" id="col_tds" checked>
                                <label class="form-check-label" for="col_tds">TDS</label>
                            </div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="professional_tax" id="col_pt" checked>
                                <label class="form-check-label" for="col_pt">Professional Tax</label>
                            </div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="pf" id="col_pf" checked>
                                <label class="form-check-label" for="col_pf">PF</label>
                            </div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="total_deductions" id="col_total_ded" checked>
                                <label class="form-check-label" for="col_total_ded">Total Deductions</label>
                            </div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="net_salary" id="col_net_salary" checked>
                                <label class="form-check-label" for="col_net_salary">Net Salary (Payable)</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" onclick="closeModal()">Generate Preview</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openStatementModal(salaryId, month, year, employmentType) {
        document.getElementById('modal_salary_id').value = salaryId;
        document.getElementById('modal_month').value = month;
        document.getElementById('modal_year').value = year;
        document.getElementById('modal_employment_type').value = employmentType;
        
        var myModal = new bootstrap.Modal(document.getElementById('columnSelectionModal'));
        myModal.show();
    }
    
    function closeModal() {
        var myModalEl = document.getElementById('columnSelectionModal');
        var modal = bootstrap.Modal.getInstance(myModalEl);
        if(modal) {
            setTimeout(function() { modal.hide(); }, 500); // give time for form to submit
        }
    }
</script>
@endsection
