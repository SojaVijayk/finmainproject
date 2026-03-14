@extends('layouts/layoutMaster')

@section('title', 'Salary Management - Selection')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
@endsection

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">PMS /</span> Salary Management
</h4>

<form action="{{ route('pms.salary-management.select-employees', $project_id) }}" method="POST" id="selection-form">
  @csrf
  <div class="row">
  <div class="col-md-5">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('pms.employees.project-index', ['id' => $project_id]) }}" class="btn btn-sm btn-label-secondary">
                <i class="ti ti-arrow-left me-1"></i> Back
            </a>
            <h5 class="mb-0">Step 1: Selection</h5>
        </div>
        <small class="text-muted float-end">Payroll Period & Type</small>
      </div>
      <div class="card-body">
          <div class="mb-3">
            <label class="form-label" for="month">Select Month</label>
            <select id="month" name="month" class="form-select" required>
              @foreach($months as $month)
                @php 
                  $selectedMonth = session('payroll_month', date('F'));
                @endphp
                <option value="{{ $month }}" {{ $month == $selectedMonth ? 'selected' : '' }}>{{ $month }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label" for="year">Select Year</label>
            <select id="year" name="year" class="form-select" required>
              @foreach($years as $year)
                @php
                  $selectedYear = session('payroll_year', date('Y'));
                @endphp
                <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>{{ $year }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label" for="employment_type">Employment Type</label>
            <select id="employment_type" name="employment_type" class="form-select" required>
              <option value="">Select Type</option>
              @foreach($employmentTypes as $type)
                <option value="{{ $type->id }}" {{ (old('employment_type') == $type->id || session('payroll_employment_type_id') == $type->id) ? 'selected' : '' }}>{{ $type->employment_type }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label" for="default_salary_id">Salary ID / Batch Reference (Required)</label>
            <input type="text" id="default_salary_id" name="default_salary_id" class="form-control" placeholder="e.g. SAL-FEB-2024" value="{{ session('payroll_default_salary_id') }}" required>
            <div class="form-text">This value will be pre-filled for all employees.</div>
          </div>

          <div class="d-flex justify-content-between gap-3">
            <button type="button" id="btn-add-bill" class="btn btn-outline-primary w-100">
                <i class="ti ti-plus me-1"></i> Add Salary Bill
            </button>
            <button type="submit" class="btn btn-primary w-100">Confirm & Continue</button>
          </div>
      </div>
    </div>
  </div>

  <div class="col-md-7">
    <div id="batch-results" class="card mb-4 d-none">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Existing Salary Management Bills</h5>
        <span class="badge bg-label-info">Period Summary</span>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-sm table-bordered">
            <thead class="table-light">
              <tr>
                <th class="text-center" style="width: 40px;">
                  <input type="checkbox" id="check-all-batches" class="form-check-input">
                </th>
                <th>Month/Year</th>
                <th>Type</th>
                <th>Salary ID</th>
                <th class="text-center">Status</th>
              </tr>
            </thead>
            <tbody id="batch-table-body">
            </tbody>
          </table>
        </div>
        <div class="form-text mt-3 text-info d-flex align-items-center">
          <i class="ti ti-info-circle ti-xs me-2"></i> 
          <span>Tick bills to filter employees OR click a Salary ID to copy it to the form.</span>
        </div>
      </div>
    </div>
  </div>
</form>

<!-- Modal for Adding Salary Bill -->
<div class="modal fade" id="addBillModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create New Salary Bill</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Selected Period</label>
          <div id="modal-period-display" class="form-control-plaintext fw-bold text-primary"></div>
        </div>
        <div class="mb-3">
          <label class="form-label">Employment Type</label>
          <div id="modal-type-display" class="form-control-plaintext fw-bold text-primary"></div>
        </div>
        <div class="mb-3">
          <label class="form-label" for="modal-salary-id">Salary ID / Batch Reference</label>
          <input type="text" id="modal-salary-id" class="form-control" placeholder="e.g. SAL-FEB-2024">
          <div id="modal-error" class="text-danger mt-1 small d-none">Salary ID is required!</div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="btn-modal-confirm" class="btn btn-primary">Confirm & Add to List</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const monthSelect = document.getElementById('month');
    const yearSelect = document.getElementById('year');
    const employmentTypeSelect = document.getElementById('employment_type');
    
    const resultsDiv = document.getElementById('batch-results');
    const tableBody = document.getElementById('batch-table-body');
    const checkAll = document.getElementById('check-all-batches');
    const addBillBtn = document.getElementById('btn-add-bill');
    const salaryIdInput = document.getElementById('default_salary_id');

    // Modal elements
    const addBillModal = new bootstrap.Modal(document.getElementById('addBillModal'));
    const modalPeriodDisplay = document.getElementById('modal-period-display');
    const modalTypeDisplay = document.getElementById('modal-type-display');
    const modalSalaryIdInput = document.getElementById('modal-salary-id');
    const modalConfirmBtn = document.getElementById('btn-modal-confirm');
    const modalError = document.getElementById('modal-error');

    // Client-side storage for new bills added during this session
    let draftBills = [];

    // Handle "Add Salary Bill" button
    addBillBtn.addEventListener('click', function() {
        const month = monthSelect.value;
        const year = yearSelect.value;
        const employmentTypeId = employmentTypeSelect.value;
        const employmentTypeName = employmentTypeSelect.options[employmentTypeSelect.selectedIndex].text;

        if (!month || !year || !employmentTypeId || employmentTypeId === "") {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Selection',
                    text: 'Please select Month, Year, and Employment Type first.'
                });
            } else {
                alert('Please select Month, Year, and Employment Type first.');
            }
            return;
        }

        // Setup modal
        modalPeriodDisplay.textContent = `${month} ${year}`;
        modalTypeDisplay.textContent = employmentTypeName;
        modalSalaryIdInput.value = salaryIdInput.value;
        modalError.classList.add('d-none');
        
        addBillModal.show();
    });

    // Handle Modal Confirm
    modalConfirmBtn.addEventListener('click', function() {
        const newId = modalSalaryIdInput.value.trim();
        if (!newId) {
            modalError.classList.remove('d-none');
            return;
        }

        const month = monthSelect.value;
        const year = yearSelect.value;
        const employmentTypeName = employmentTypeSelect.options[employmentTypeSelect.selectedIndex].text;

        // Save to draft bills so it persists across fetches
        if (!draftBills.find(b => b.id === newId)) {
            draftBills.push({
                id: newId,
                month: month,
                year: year,
                typeName: employmentTypeName
            });
        }

        salaryIdInput.value = newId;
        addBillModal.hide();

        // Refresh list to show the new item
        renderBillsList();
        
        // Ensure visibility
        resultsDiv.classList.remove('d-none');

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
                icon: 'success',
                title: 'Added!',
                text: `Salary bill "${newId}" added to the list.`
            });
        }
    });

    function renderBillsList(fetchedBatches = null) {
        if (fetchedBatches) {
            // If we have fresh data from server, we cache it in a global or just redraw
            window.lastFetchedBatches = fetchedBatches;
        }
        
        const batches = window.lastFetchedBatches || [];
        const month = monthSelect.value;
        const year = yearSelect.value;
        const employmentTypeName = employmentTypeSelect.options[employmentTypeSelect.selectedIndex].text;

        tableBody.innerHTML = '';
        checkAll.checked = false;

        // 1. Render all fetched batches
        if (batches.length > 0) {
            batches.forEach(batch => {
                const statusBadge = batch.is_frozen 
                    ? '<span class="badge bg-label-success">Frozen</span>' 
                    : '<span class="badge bg-label-warning">Processing</span>';
                
                const row = `
                    <tr>
                        <td class="text-center">
                            <input type="checkbox" name="filter_salary_ids[]" value="${batch.salary_id}" class="form-check-input batch-checkbox">
                        </td>
                        <td><small>${month} ${year}</small></td>
                        <td><small>${employmentTypeName}</small></td>
                        <td class="fw-semibold text-primary salary-id-link" style="cursor: pointer;" title="Click to use this ID">${batch.salary_id}</td>
                        <td class="text-center">${statusBadge}</td>
                    </tr>
                `;
                tableBody.insertAdjacentHTML('beforeend', row);
            });
        }

        // 2. Render all draft bills for THIS period and THIS type
        draftBills.filter(b => b.month === month && b.year === year && b.typeName === employmentTypeName).forEach(draft => {
            // Don't duplicate if already in fetched list
            if (batches.find(b => b.salary_id === draft.id)) return;

            const row = `
                <tr class="table-primary">
                    <td class="text-center">
                        <input type="checkbox" name="filter_salary_ids[]" value="${draft.id}" class="form-check-input batch-checkbox" checked>
                    </td>
                    <td><small>${month} ${year}</small></td>
                    <td><small>${draft.typeName}</small></td>
                    <td class="fw-semibold text-primary salary-id-link" style="cursor: pointer;" title="Click to use this ID">${draft.id}</td>
                    <td class="text-center"><span class="badge bg-label-info">New/Pending</span></td>
                </tr>
            `;
            tableBody.insertAdjacentHTML('afterbegin', row);
        });

        if (tableBody.innerHTML === '') {
            tableBody.innerHTML = '<tr><td colspan="5" class="text-center">No existing bills found for this period.</td></tr>';
        }
    }

    // Function to fetch bills
    function fetchBills() {
        const month = monthSelect.value;
        const year = yearSelect.value;
        const employmentTypeId = employmentTypeSelect.value;

        if (!month || !year || !employmentTypeId || employmentTypeId === "") {
            resultsDiv.classList.add('d-none');
            return;
        }

        tableBody.innerHTML = '<tr><td colspan="5" class="text-center"><span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Fetching Bills...</td></tr>';
        resultsDiv.classList.remove('d-none');

        const url = "{{ route('pms.salary-management.fetch-batches', $project_id) }}?month=" + month + "&year=" + year + "&employment_type=" + employmentTypeId;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderBillsList(data.batches);
                } else {
                    tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error loading bills.</td></tr>';
                }
            })
            .catch(error => {
                tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Unexpected error occurred.</td></tr>';
            });
    }

    // Auto-fetch on changes
    monthSelect.addEventListener('change', fetchBills);
    yearSelect.addEventListener('change', fetchBills);
    employmentTypeSelect.addEventListener('change', fetchBills);

    // Initial load fetch
    fetchBills();

    // Handle clicking on Salary ID to fill the input
    tableBody.addEventListener('click', function(e) {
        if (e.target.classList.contains('salary-id-link')) {
            const idVal = e.target.textContent.trim();
            salaryIdInput.value = (idVal === 'Unnamed Batch') ? '' : idVal;
            salaryIdInput.classList.add('is-valid'); // Visual feedback
            setTimeout(() => salaryIdInput.classList.remove('is-valid'), 2000);
            
            // Scroll to input for mobile users/long pages
            salaryIdInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // If it was unnamed, focus it so they can type
            if (idVal === 'Unnamed Batch') salaryIdInput.focus();
        }
    });

    // Handle "Check All" functionality
    checkAll.addEventListener('change', function() {
        const checkboxes = tableBody.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });
});
</script>
@endsection
