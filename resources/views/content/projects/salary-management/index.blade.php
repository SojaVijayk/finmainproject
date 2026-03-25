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
              @foreach($months as $m)
                <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ $m }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label" for="year">Select Year</label>
            <select id="year" name="year" class="form-select" required>
              @foreach($years as $y)
                <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label" for="employment_type">Employment Type</label>
            <select id="employment_type" name="employment_type" class="form-select" required>
              <option value="">Select Type</option>
              @foreach($employmentTypes as $type)
                <option value="{{ $type->id }}" {{ $type->id == $employmentTypeId ? 'selected' : '' }}>{{ $type->employment_type }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label" for="default_salary_id">Salary ID / Batch Reference (Required)</label>
            <input type="text" id="default_salary_id" name="default_salary_id" class="form-control" placeholder="e.g. SAL-FEB-2024" value="{{ $defaultSalaryId }}" required>
            <div id="salary-id-feedback" class="form-text mt-1">This value will be pre-filled for all employees.</div>
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
    @if(session('error'))
      <div class="alert alert-danger d-flex align-items-center mb-3" role="alert">
          <i class="ti ti-alert-circle me-2 flex-shrink-0"></i>
          <span>{{ session('error') }}</span>
      </div>
    @endif
    @if(session('success'))
      <div class="alert alert-success d-flex align-items-center mb-3" role="alert">
          <i class="ti ti-check me-2 flex-shrink-0"></i>
          <span>{{ session('success') }}</span>
      </div>
    @endif
    <div id="batch-results" class="card mb-4 d-none">
      <div class="card-header border-bottom pb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h5 class="mb-0">Existing Salary Management Bills</h5>
        </div>
        <div class="d-flex justify-content-end align-items-center gap-2">
            <label class="mb-0 fw-semibold" style="font-size: 13px;">Filter:</label>
            <select id="filter-year" class="form-select form-select-sm w-auto">
              <option value="All">All Years</option>
              @foreach($years as $y)
                <option value="{{ $y }}">{{ $y }}</option>
              @endforeach
            </select>
            <select id="filter-type" class="form-select form-select-sm w-auto">
              <option value="All">All Types</option>
              @foreach($employmentTypes as $type)
                <option value="{{ $type->employment_type }}">{{ $type->employment_type }}</option>
              @endforeach
            </select>
        </div>
      </div>
      <div class="card-body">
        <ul class="nav nav-tabs mt-3" role="tablist">
          <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#navs-processing" role="tab" type="button">
              Processing <span id="count-processing" class="badge rounded-pill bg-warning ms-1">0</span>
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#navs-frozen" role="tab" type="button">
              Frozen <span id="count-frozen" class="badge rounded-pill bg-success ms-1">0</span>
            </button>
          </li>
        </ul>
        <div class="tab-content px-0 pb-0">
          <!-- Processing Tab -->
          <div class="tab-pane fade show active" id="navs-processing" role="tabpanel">
            <div class="table-responsive">
              <table class="table table-sm table-bordered">
                <thead class="table-light">
                  <tr>
                    <th class="text-center" style="width: 40px;">
                      <input type="checkbox" id="check-all-processing" class="form-check-input check-all-batches">
                    </th>
                    <th>Month/Year</th>
                    <th>Type</th>
                    <th>Salary ID</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Actions</th>
                  </tr>
                </thead>
                <tbody id="batch-table-body-processing">
                </tbody>
              </table>
            </div>
          </div>

          <!-- Frozen Tab -->
          <div class="tab-pane fade" id="navs-frozen" role="tabpanel">
            <div class="table-responsive">
              <table class="table table-sm table-bordered">
                <thead class="table-light">
                  <tr>
                    <th class="text-center" style="width: 40px;">
                      <input type="checkbox" id="check-all-frozen" class="form-check-input check-all-batches">
                    </th>
                    <th>Month/Year</th>
                    <th>Type</th>
                    <th>Salary ID</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Actions</th>
                  </tr>
                </thead>
                <tbody id="batch-table-body-frozen">
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="form-text mt-3 text-info d-flex align-items-center">
          <i class="ti ti-info-circle ti-xs me-2"></i> 
          <span>Tick bills to filter employees.</span>
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
    const tableBodyProcessing = document.getElementById('batch-table-body-processing');
    const tableBodyFrozen = document.getElementById('batch-table-body-frozen');
    const checkAllBtns = document.querySelectorAll('.check-all-batches');
    const addBillBtn = document.getElementById('btn-add-bill');
    const salaryIdInput = document.getElementById('default_salary_id');
    const filterYear = document.getElementById('filter-year');
    const filterType = document.getElementById('filter-type');

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

    // Validation function
    function validateSalaryId(id, month, year, typeName) {
        const batches = window.lastFetchedBatches || [];
        const existing = batches.find(b => b.salary_id.toLowerCase() === id.toLowerCase());
        
        if (existing) {
            if (existing.paymonth === month && existing.year == year && existing.employment_type === typeName) {
                return { valid: true, isExisting: true, conflict: null }; 
            } else {
                return { valid: false, isExisting: false, conflict: existing }; 
            }
        }
        
        // Check draft bills too
        const draft = draftBills.find(b => b.id.toLowerCase() === id.toLowerCase());
        if (draft) {
            if (draft.month === month && draft.year == year && draft.typeName === typeName) {
                return { valid: true, isExisting: true, conflict: null };
            } else {
                return { valid: false, isExisting: false, conflict: {paymonth: draft.month, year: draft.year, employment_type: draft.typeName} };
            }
        }

        return { valid: true, isExisting: false, conflict: null };
    }

    // Real-time detection on the main input
    const feedbackEl = document.getElementById('salary-id-feedback');
    salaryIdInput.addEventListener('input', function() {
        const idVal = this.value.trim();
        if(!idVal) {
            feedbackEl.className = 'form-text mt-1 text-muted';
            feedbackEl.innerHTML = 'This value will be pre-filled for all employees.';
            return;
        }

        const month = monthSelect.value;
        const year = yearSelect.value;
        const typeSelectIdx = employmentTypeSelect.selectedIndex;
        const employmentTypeName = typeSelectIdx >= 0 ? employmentTypeSelect.options[typeSelectIdx].text : null;

        if (!month || !year || !employmentTypeName) return;

        const validation = validateSalaryId(idVal, month, year, employmentTypeName);
        if (!validation.valid) {
            feedbackEl.className = 'form-text mt-1 text-danger fw-bold';
            feedbackEl.innerHTML = `<i class="ti ti-alert-circle"></i> Error: ID already in use by ${validation.conflict.paymonth} ${validation.conflict.year} (${validation.conflict.employment_type}).`;
            salaryIdInput.classList.add('is-invalid');
        } else if (validation.isExisting) {
            feedbackEl.className = 'form-text mt-1 text-success fw-bold';
            feedbackEl.innerHTML = `<i class="ti ti-check"></i> Opening existing batch for this period.`;
            salaryIdInput.classList.remove('is-invalid');
            salaryIdInput.classList.add('is-valid');
        } else {
            feedbackEl.className = 'form-text mt-1 text-primary fw-bold';
            feedbackEl.innerHTML = `<i class="ti ti-check"></i> Unique ID available for new batch.`;
            salaryIdInput.classList.remove('is-invalid', 'is-valid');
        }
    });

    // Form submission validation
    document.getElementById('selection-form').addEventListener('submit', function(e) {
        const idVal = salaryIdInput.value.trim();
        const month = monthSelect.value;
        const year = yearSelect.value;
        const employmentTypeName = employmentTypeSelect.options[employmentTypeSelect.selectedIndex].text;
        
        const validation = validateSalaryId(idVal, month, year, employmentTypeName);
        if (!validation.valid) {
            e.preventDefault();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'ID Already in Use',
                    text: `The Salary ID "${idVal}" is already registered for ${validation.conflict.paymonth} ${validation.conflict.year} (${validation.conflict.employment_type}). Please enter a unique ID for this period.`
                });
            } else {
                alert(`Error: The Salary ID belongs to ${validation.conflict.paymonth} ${validation.conflict.year}. Please use a unique ID.`);
            }
            salaryIdInput.focus();
        }
    });

    // Handle Modal Confirm
    modalConfirmBtn.addEventListener('click', function() {
        const newId = modalSalaryIdInput.value.trim();
        if (!newId) {
            modalError.innerHTML = 'Salary ID is required!';
            modalError.classList.remove('d-none');
            return;
        }

        const month = monthSelect.value;
        const year = yearSelect.value;
        const employmentTypeName = employmentTypeSelect.options[employmentTypeSelect.selectedIndex].text;

        const validation = validateSalaryId(newId, month, year, employmentTypeName);
        if (!validation.valid) {
            modalError.innerHTML = `This ID is already used by ${validation.conflict.paymonth} ${validation.conflict.year} (${validation.conflict.employment_type}). Please use a unique ID.`;
            modalError.classList.remove('d-none');
            return;
        }

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
        
        // Trigger the input event to update the main form feedback
        salaryIdInput.dispatchEvent(new Event('input'));

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
            window.lastFetchedBatches = fetchedBatches;
        }
        
        const batches = window.lastFetchedBatches || [];
        
        // Right side filters
        const selectedYear = filterYear.value;
        const selectedType = filterType.value;

        tableBodyProcessing.innerHTML = '';
        tableBodyFrozen.innerHTML = '';
        checkAllBtns.forEach(btn => btn.checked = false);

        let countProcessing = 0;
        let countFrozen = 0;

        // 1. Render batches
        if (batches.length > 0) {
            batches.forEach(batch => {
                // Apply Right Side Filters
                if (selectedYear !== 'All' && String(batch.year) !== String(selectedYear)) return;
                if (selectedType !== 'All' && String(batch.employment_type) !== String(selectedType)) return;

                const isFrozen = !!batch.is_frozen;
                const statusBadge = isFrozen 
                    ? '<span class="badge bg-label-success">Frozen</span>' 
                    : '<span class="badge bg-label-warning">Processing</span>';

                const actionBtn = !isFrozen
                    ? `<a href="{{ route('pms.salary-management.resume-edit', $project_id) }}?salary_id=${encodeURIComponent(batch.salary_id)}"
                          class="btn btn-xs btn-warning py-0 px-2" style="font-size:11px;"
                          title="Reload this bill and continue editing">
                          <i class="ti ti-edit me-1"></i>Edit
                       </a>`
                    : '<span class="text-muted" style="font-size:11px;">—</span>';
                
                const row = `
                    <tr>
                        <td class="text-center">
                            <input type="checkbox" name="filter_salary_ids[]" value="${batch.salary_id}" class="form-check-input batch-checkbox">
                        </td>
                        <td><small>${batch.paymonth} ${batch.year}</small></td>
                        <td><small>${batch.employment_type}</small></td>
                        <td class="fw-semibold">${batch.salary_id && batch.salary_id !== 'null' ? batch.salary_id : 'Unnamed Batch'}</td>
                        <td class="text-center">${statusBadge}</td>
                        <td class="text-center">${actionBtn}</td>
                    </tr>
                `;
                if (isFrozen) {
                    tableBodyFrozen.insertAdjacentHTML('beforeend', row);
                    countFrozen++;
                } else {
                    tableBodyProcessing.insertAdjacentHTML('beforeend', row);
                    countProcessing++;
                }
            });
        }

        // 2. Render draft bills (Processing - Draft)
        draftBills.forEach(draft => {
            // Apply Right Side Filters
            if (selectedYear !== 'All' && String(draft.year) !== String(selectedYear)) return;
            if (selectedType !== 'All' && String(draft.typeName) !== String(selectedType)) return;

            // Don't duplicate if already in fetched list
            if (batches.find(b => b.salary_id === draft.id)) return;

            const row = `
                <tr class="table-primary">
                    <td class="text-center">
                        <input type="checkbox" name="filter_salary_ids[]" value="${draft.id}" class="form-check-input batch-checkbox" checked>
                    </td>
                    <td><small>${draft.month} ${draft.year}</small></td>
                    <td><small>${draft.typeName}</small></td>
                    <td class="fw-semibold">${draft.id}</td>
                    <td class="text-center"><span class="badge bg-label-info">Draft</span></td>
                    <td class="text-center"><span class="text-muted" style="font-size:11px;">—</span></td>
                </tr>
            `;
            tableBodyProcessing.insertAdjacentHTML('afterbegin', row);
            countProcessing++;
        });

        // Handle empty states
        if (tableBodyProcessing.innerHTML === '') {
            tableBodyProcessing.innerHTML = '<tr><td colspan="6" class="text-center">No processing/draft bills found.</td></tr>';
        }
        if (tableBodyFrozen.innerHTML === '') {
            tableBodyFrozen.innerHTML = '<tr><td colspan="6" class="text-center">No frozen bills found.</td></tr>';
        }

        document.getElementById('count-processing').textContent = countProcessing;
        document.getElementById('count-frozen').textContent = countFrozen;
    }

    function fetchBills() {
        const loadingHtml = '<tr><td colspan="6" class="text-center"><span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Fetching Bills...</td></tr>';
        tableBodyProcessing.innerHTML = loadingHtml;
        tableBodyFrozen.innerHTML = loadingHtml;
        resultsDiv.classList.remove('d-none');

        const url = "{{ route('pms.salary-management.fetch-batches', $project_id) }}";

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderBillsList(data.batches);
                } else {
                    const errHtml = '<tr><td colspan="6" class="text-center text-danger">Error loading bills.</td></tr>';
                    tableBodyProcessing.innerHTML = errHtml;
                    tableBodyFrozen.innerHTML = errHtml;
                }
            })
            .catch(error => {
                const errHtml = '<tr><td colspan="6" class="text-center text-danger">Unexpected error occurred.</td></tr>';
                tableBodyProcessing.innerHTML = errHtml;
                tableBodyFrozen.innerHTML = errHtml;
            });
    }

    // Auto-fetch and re-validate on changes from left form
    [monthSelect, yearSelect, employmentTypeSelect].forEach(el => {
        el.addEventListener('change', () => {
            fetchBills();
            salaryIdInput.dispatchEvent(new Event('input'));
        });
    });

    // Handle Right-Side Filters (only re-render locally)
    [filterYear, filterType].forEach(el => {
        el.addEventListener('change', () => renderBillsList());
    });

    // Initial load fetch
    fetchBills();

    // Handle "Check All" functionality for each tab
    checkAllBtns.forEach(checkAll => {
        checkAll.addEventListener('change', function() {
            // Find the closest table and get its checkboxes
            const closestTable = this.closest('table');
            if (closestTable) {
                const checkboxes = closestTable.querySelectorAll('.batch-checkbox');
                checkboxes.forEach(cb => cb.checked = this.checked);
            }
        });
    });
});
</script>
@endsection
