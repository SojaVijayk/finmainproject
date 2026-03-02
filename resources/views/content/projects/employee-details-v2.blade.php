@extends('layouts/layoutMaster')

@section('title', 'DEBUG MODE ACTIVE')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
@endsection

@section('vendor-script')
<script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
@endsection



@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">Project Management / Employees /</span> Details
</h4>

<div class="row">
  <!-- Employee Master Details (Left Sidebar) -->
  <div class="col-xl-4 col-lg-5 col-md-5 order-1 order-md-0">
    <div class="card mb-4">
      <div class="card-body">
        <div class="user-avatar-section">
          <div class="d-flex align-items-center flex-column">
            <div class="user-info text-center">
              <h4 class="mb-2 mt-4">{{ $employee->name }} {{ $employee->last_name }}</h4>
              <span class="badge bg-label-secondary mt-1">{{ $employee->designation->designation ?? 'N/A' }}</span><br>
              <span class="badge bg-label-success mt-1">{{ $employee->empId }}</span>
            </div>
          </div>
        </div>
        <p class="mt-4 small text-uppercase text-muted">Master Details</p>
        <div class="info-container">
          <ul class="list-unstyled">
            <li class="mb-2">
              <span class="fw-semibold me-1">Email:</span>
              <span>{{ $employee->email }}</span>
            </li>
            <li class="mb-2 pt-1">
              <span class="fw-semibold me-1">Mobile:</span>
              <span>{{ $employee->mobile }}</span>
            </li>
            <li class="mb-2 pt-1">
              <span class="fw-semibold me-1">Age:</span>
              <span>{{ $employee->age }}</span>
            </li>
            <li class="mb-2 pt-1">
              <span class="fw-semibold me-1">DOB:</span>
              <span>{{ $employee->dob }}</span>
            </li>
            <li class="mb-2 pt-1">
              <span class="fw-semibold me-1">Project:</span>
              <span>{{ $employee->project->name ?? 'N/A' }}</span>
            </li>
            <li class="mb-2 pt-1">
              <span class="fw-semibold me-1">Joining Date:</span>
              <span>{{ $employee->date_of_joining }}</span>
            </li>
            <li class="mb-2 pt-1">
              <span class="fw-semibold me-1">Address:</span>
              <span>{{ $employee->address }}</span>
            </li>
            <li class="mb-2 pt-1">
                <span class="fw-semibold me-1">Status:</span>
                <span class="badge bg-label-{{ $employee->status == 1 ? 'success' : 'danger' }}">{{ $employee->status == 1 ? 'Active' : 'Inactive' }}</span>
              </li>
          </ul>
          <div class="d-flex justify-content-center">
            <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#editMasterModal">Edit Master Info</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Service, Salary, Deduction Details (Right Content) -->
  <div class="col-xl-8 col-lg-7 col-md-7 order-0 order-md-1">
    <!-- Service Details -->
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Current Active Service</h5>
        <div class="d-flex gap-2">
          @if($employee->service && $employee->service->status == 1)
              {{-- Active Service Exists: Show Edit, Hide Add --}}
              <button class="btn btn-sm btn-primary btn-populate-service" 
                  data-bs-toggle="modal" data-bs-target="#editServiceModal" 
                  data-service="{{ json_encode($employee->service, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) }}" 
                  data-new="false">
                <i class="ti ti-edit me-1"></i> Edit Current Service
              </button>
          @else
              {{-- No Active Service: Show Add --}}
              <button class="btn btn-sm btn-success btn-populate-service" data-bs-toggle="modal" data-bs-target="#editServiceModal" data-service="{}" data-new="true">
                <i class="ti ti-plus me-1"></i> Add New Service
              </button>
          @endif
        </div>
      </div>
      <div class="card-body">
        @if($employee->service && $employee->service->status == 1)
        <div class="row">
          <div class="col-md-6 mb-3">
            <span class="fw-semibold d-block">Employment Type:</span>
            <span id="view_type">{{ $employee->service->employment_type ?? 'N/A' }}</span>
          </div>
          <div class="col-md-6 mb-3">
            <span class="fw-semibold d-block">Department:</span>
            <span id="view_department">{{ $employee->service->department ?? 'N/A' }}</span>
          </div>
          <div class="col-md-6 mb-3">
            <span class="fw-semibold d-block">Role:</span>
            <span id="view_role">{{ $employee->service->role ?? 'N/A' }}</span>
          </div>
          <div class="col-md-6 mb-3">
            <span class="fw-semibold d-block">Pay Type:</span>
            <span id="view_pay_type">{{ $employee->service->pay_type ?? 'N/A' }}</span>
          </div>
          <div class="col-md-6 mb-3">
            <span class="fw-semibold d-block" id="view_pay_label">{{ $employee->service->pay_type ?? 'Consolidated Pay' }}:</span>
            <span id="view_pay">{{ number_format($employee->service->consolidated_pay ?? 0, 2) }}</span>
          </div>
          <div class="col-md-6 mb-3">
            <span class="fw-semibold d-block">Status:</span>
            <span class="badge bg-label-success">Active</span>
          </div>
          <div class="col-md-6 mb-3">
            <span class="fw-semibold d-block">Start Date:</span>
            <span id="view_start">{{ $employee->service->start_date ?? 'N/A' }}</span>
          </div>
        </div>
        @else
        <div class="text-center py-5">
            <h4 class="text-muted">No Active Service Record</h4>
            <p class="mb-0">This employee is currently not assigned to an active service.</p>
        </div>
        @endif
      </div>
    </div>
    
    <!-- Service History -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Service History</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-sm table-hover">
            <thead>
              <tr>
                <th>Period</th>
                <th>Type</th>
                <th>Role</th>
                <th>Department</th>
                <th>Pay</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($employee->services as $hist)
              <tr>
                @php
                    // Defensive formatting if not cast
                    $start = $hist->start_date instanceof \DateTime ? $hist->start_date->format('Y-m-d') : $hist->start_date;
                    $end = $hist->end_date ? ($hist->end_date instanceof \DateTime ? $hist->end_date->format('Y-m-d') : $hist->end_date) : 'Present';
                @endphp
                <td>{{ $start }} to {{ $end }}</td>
                <td>{{ $hist->employment_type }}</td>
                <td>{{ $hist->role ?? 'N/A' }}</td>
                <td>{{ $hist->department }}</td>
                <td>{{ number_format($hist->consolidated_pay, 2) }} ({{ $hist->pay_type }})</td>
                <td>
                  <span class="badge bg-label-{{ $hist->status == 1 ? 'success' : 'secondary' }}">
                    {{ $hist->status == 1 ? 'Current' : 'Previous' }}
                  </span>
                </td>
                <td>
                  {{-- View Button --}}
                  <button class="btn btn-sm btn-icon btn-label-secondary btn-view-history-detail" title="View details" 
                      data-service="{{ json_encode($hist, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) }}">
                    <i class="ti ti-eye"></i>
                  </button>
                  
                  {{-- Edit Button --}}
                  <button class="btn btn-sm btn-icon btn-label-primary btn-populate-service" title="Edit this record" 
                      data-bs-toggle="modal" data-bs-target="#editServiceModal" 
                      data-service="{{ json_encode($hist, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) }}" 
                      data-new="false" 
                      data-hide-toggle="true">
                    <i class="ti ti-edit"></i>
                  </button>
                </td>
              </tr>
              @empty
              <tr><td colspan="5" class="text-center">No service history found.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Modals -->
<!-- ... (Existing Modals) ... -->




<!-- Modals -->

<!-- Edit Master Modal -->
<div class="modal fade" id="editMasterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-simple modal-edit-user">
    <div class="modal-content p-3 p-md-5">
      <div class="modal-body">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-center mb-4">
          <h3 class="mb-2">Edit Master Information</h3>
          <p class="text-muted">Update employee primary details.</p>
        </div>
        <form id="editMasterForm" class="row g-3" action="{{ route('pms.employees.update-master', $employee->id) }}" onsubmit="return false">
          @csrf
          <div class="col-12 col-md-6">
            <label class="form-label" for="edit_name">First Name</label>
            <input type="text" id="edit_name" name="name" class="form-control" value="{{ $employee->name }}" />
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="edit_last_name">Last Name</label>
            <input type="text" id="edit_last_name" name="last_name" class="form-control" value="{{ $employee->last_name }}" />
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="edit_email">Email</label>
            <input type="text" id="edit_email" name="email" class="form-control" value="{{ $employee->email }}" />
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="edit_mobile">Mobile</label>
            <input type="text" id="edit_mobile" name="mobile" class="form-control" value="{{ $employee->mobile }}" />
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label" for="edit_age">Age</label>
            <input type="number" id="edit_age" name="age" class="form-control" value="{{ $employee->age }}" />
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label" for="edit_dob">DOB</label>
            <input type="date" id="edit_dob" name="dob" class="form-control" value="{{ $employee->dob }}" />
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label" for="edit_joining_date">Joining Date</label>
            <input type="date" id="edit_joining_date" name="joining_date" class="form-control" value="{{ $employee->date_of_joining }}" />
          </div>
          <div class="col-12">
            <label class="form-label" for="edit_address">Address</label>
            <textarea id="edit_address" name="address" class="form-control" rows="2">{{ $employee->address }}</textarea>
          </div>
          <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary me-sm-3 me-1 btn-submit-edit" data-form="editMasterForm">Submit</button>
            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Edit Service Modal -->
<div class="modal fade" id="editServiceModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-simple">
    <div class="modal-content p-3 p-md-5">
      <div class="modal-body">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-center mb-4">
          <h3 class="mb-2 modal-title">Edit Service Information</h3>
        </div>
        <form id="editServiceForm" class="row g-3" action="{{ route('pms.employees.update-service', $employee->id) }}" onsubmit="return false">
          @csrf
          <input type="hidden" name="service_id" id="edit_service_id">
          <div class="col-12 col-md-6">
            <label class="form-label" for="edit_employment_type">Employment Type</label>
            <select id="edit_employment_type" name="employment_type" class="form-select">
              <option value="Full Time">Full Time</option>
              <option value="Daily Wages">Daily Wages</option>
              <option value="Interns">Interns</option>
              <option value="Contract">Contract</option>
              <option value="Part Time">Part Time</option>
              <option value="Freelance">Freelance</option>
              <option value="Temporary">Temporary</option>
              <option value="Permanent">Permanent</option>
              <option value="Apprentice">Apprentice</option>
              <option value="Deputation">Deputation</option>
            </select>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="edit_department">Department</label>
            <input type="text" id="edit_department" name="department" class="form-control" placeholder="Engineering" />
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="edit_role">Role</label>
            <input type="text" id="edit_role" name="role" class="form-control" placeholder="System Administrator" />
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="edit_pay_type">Pay Type</label>
            <select id="edit_pay_type" name="pay_type" class="form-select">
              <option value="Hourly pay">Hourly pay</option>
              <option value="Daily wage">Daily wage</option>
              <option value="Weekly pay">Weekly pay</option>
              <option value="Bi-weekly">Bi-weekly</option>
              <option value="Monthly" selected>Monthly</option>
              <option value="Annual">Annual</option>
              <option value="Per diem">Per diem</option>
              <option value="Shift based pay">Shift based pay</option>
              <option value="Consolidated pay">Consolidated pay</option>
            </select>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="edit_consolidated_pay" id="pay_label">Consolidated Pay</label>
            <input type="number" step="0.01" id="edit_consolidated_pay" name="consolidated_pay" class="form-control" />
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label d-block">Status</label>
            <div class="form-check form-switch mb-2">
              <input class="form-check-input" type="checkbox" id="edit_service_status_toggle" checked>
              <label class="form-check-label" for="edit_service_status_toggle" id="status_label">Active</label>
            </div>
            <input type="hidden" name="status" id="edit_service_status" value="1">
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="edit_start_date">Start Date</label>
            <input type="date" id="edit_start_date" name="start_date" class="form-control" />
          </div>
          <div class="col-12 col-md-6" id="end_date_container" style="display: none;">
            <label class="form-label" for="edit_end_date">End Date</label>
            <input type="date" id="edit_end_date" name="end_date" class="form-control" />
          </div>
          <div class="col-12" id="new_record_toggle_container">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="new_record" value="1" id="check_new_record">
              <label class="form-check-label" for="check_new_record">
                Create as new service record? (Select this for promotions or role changes to preserve history)
              </label>
            </div>
          </div>
          <div class="col-12 text-center">
            <button type="button" class="btn btn-primary me-sm-3 me-1 btn-submit-edit" data-form="editServiceForm">Submit</button>
            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>


@endsection

@section('page-script')
<script>
$(function() {
    // Initialize Select2
    var select2 = $('.select2');
    if (select2.length) {
        select2.each(function() {
            var $this = $(this);
            $this.wrap('<div class="position-relative"></div>').select2({
                placeholder: 'Select value',
                dropdownParent: $this.parent()
            });
        });
    }


    // Pay Type Label Sync
    function updatePayLabel(payType) {
        var label = payType && payType !== 'Consolidated pay' ? payType : 'Consolidated Pay';
        $('#pay_label').text(label);
        $('#edit_consolidated_pay').attr('placeholder', label);
    }

    $(document).on('change', '#edit_pay_type', function() {
        updatePayLabel($(this).val());
    });

    // Reset Modal on show (if needed for fresh 'Add')
    $('#editServiceModal').on('show.bs.modal', function() {
        updatePayLabel($('#edit_pay_type').val());
    });

    // Service Status Toggle Logic
    $(document).on('change', '#edit_service_status_toggle', function() {
        var isChecked = $(this).is(':checked');
        var statusValue = isChecked ? 1 : 0;
        $('#edit_service_status').val(statusValue);
        $('#status_label').text(isChecked ? 'Active' : 'Deactive');
        
        if (isChecked) {
            $('#end_date_container').slideUp();
        } else {
            $('#end_date_container').slideDown();
        }
    });

    // View Overall Service Details
    $(document).on('click', '.view-service-details', function() {
        const data = $(this).data('service');
        if (!data) {
            Swal.fire('Info', 'No current service details found.', 'info');
            return;
        }
        
        Swal.fire({
            title: 'Service Details',
            html: `<div class="text-start"><strong>Project:</strong> {{ $employee->project->name ?? 'N/A' }}
                  <br><strong>Department:</strong> ${data.department || 'N/A'}
                  <br><strong>Role:</strong> ${data.role || 'N/A'}
                  <br><strong>Type:</strong> ${data.employment_type || 'N/A'}
                  <br><strong>Pay Type:</strong> ${data.pay_type || 'N/A'}
                  <br><strong>Pay:</strong> ${data.consolidated_pay ? parseFloat(data.consolidated_pay).toLocaleString('en-US', {minimumFractionDigits: 2}) : '0.00'}
                  <br><strong>Status:</strong> ${data.status == 1 ? 'Active' : 'Deactive'}
                  <br><strong>Start Date:</strong> ${data.start_date || 'N/A'}
                  ${data.status == 0 ? '<br><strong>End Date:</strong> ' + (data.end_date || 'N/A') : ''}</div>`,
            confirmButtonText: 'Close'
        });
    });

    // View Service Details & Audit History
    $(document).on('click', '.btn-view-history-detail', function() {
        const current = $(this).data('service');
        if (!current) {
            console.error('Service data not found');
            return;
        }

        const audits = current.audits || []; // Audits from eager loading

        // helper to format currency
        const formatCurrency = (val) => {
             if(val === null || val === undefined) return '-';
             return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(val);
        };

        // 1. Current Snapshot
        let detailsHtml = `<table class="table table-bordered table-sm text-start mb-3">
            <thead class="table-light"><tr><th colspan="2">Service Snapshot</th></tr></thead>
            <tbody>
                <tr><td style="width: 30%"><strong>Employment Type</strong></td><td>${current.employment_type || '-'}</td></tr>
                <tr><td><strong>Department</strong></td><td>${current.department || '-'}</td></tr>
                <tr><td><strong>Role</strong></td><td>${current.role || '-'}</td></tr>
                <tr><td><strong>Pay Type</strong></td><td>${current.pay_type || '-'}</td></tr>
                <tr><td><strong>Pay Amount</strong></td><td>${formatCurrency(current.consolidated_pay)}</td></tr>
                <tr><td><strong>Status</strong></td><td>${current.status == 1 ? '<span class="badge bg-label-success">Active</span>' : '<span class="badge bg-label-secondary">Inactive</span>'}</td></tr>
                <tr><td><strong>Start Date</strong></td><td>${current.start_date || '-'}</td></tr>
                <tr><td><strong>End Date</strong></td><td>${current.end_date || 'Present'}</td></tr>
            </tbody>
        </table>`;

        // 2. Audit Log (Edits to THIS record)
        let auditHtml = '';
        if (audits.length > 0) {
            auditHtml = `<h6 class="text-start mt-4 mb-2">Edit History <small class="text-muted">(Changes made to this record)</small></h6>
            <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
            <table class="table table-bordered table-sm text-start table-striped">
                <thead class="table-light position-sticky top-0">
                    <tr>
                        <th>Field</th>
                        <th>Old Value</th>
                        <th>New Value</th>
                        <th>Changed At</th>
                    </tr>
                </thead>
                <tbody>`;
            
            audits.forEach(audit => {
                let oldVal = audit.old_value || '-';
                let newVal = audit.new_value || '-';
                // Format pay if needed
                if(audit.field_name === 'consolidated_pay') {
                    oldVal = formatCurrency(oldVal);
                    newVal = formatCurrency(newVal);
                }

                auditHtml += `<tr>
                    <td>${audit.field_name.replace('_', ' ').toUpperCase()}</td>
                    <td class="text-danger">${oldVal}</td>
                    <td class="text-success">${newVal}</td>
                    <td>${new Date(audit.created_at).toLocaleString()}</td>
                </tr>`;
            });
            auditHtml += `</tbody></table></div>`;
        } else {
            auditHtml = `<div class="alert alert-secondary mt-3">No edits found for this record.</div>`;
        }

        Swal.fire({
            title: 'Service Record Details',
            html: detailsHtml + auditHtml,
            width: '700px',
            confirmButtonText: 'Close'
        });
    });

    // Populate Modal for Edit or Add
    $(document).on('click', '.btn-populate-service', function() {
        console.log('Populate button clicked');
        
        let data = $(this).data('service');
        const isNew = $(this).data('new') === true;
        const hideToggle = $(this).data('hide-toggle') === true;
        
        // Handle parsing if it comes as string (shouldn't with correct json_encode but safety first)
        if (typeof data === 'string') {
             try { data = JSON.parse(data); } catch(e) { console.error('Parse error', e); data = {}; }
        }
        if (!data) data = {}; // Safety
        
        // Debug
        console.log('Populate Data:', data, 'isNew:', isNew);

        // UI Reset
        $('#editServiceForm')[0].reset(); 
        $('#editServiceForm input[type="hidden"]').val('');
        
        // Manual Field Clearing
        $('#edit_department').val('');
        $('#edit_role').val('');
        $('#edit_consolidated_pay').val('');
        $('#edit_start_date').val('');
        $('#edit_end_date').val('');

        // Set Title
        $('#editServiceModal .modal-title').text(isNew ? 'Add New Service' : 'Edit Service Information');

        // Populate Fields
        $('#edit_service_id').val(data.id || '');
        
        // Defaults for Selects
        $('#edit_employment_type').val(data.employment_type || 'Full Time').trigger('change');
        $('#edit_pay_type').val(data.pay_type || 'Monthly').trigger('change');

        $('#edit_department').val(data.department || '');
        $('#edit_role').val(data.role || '');
        
        $('#edit_consolidated_pay').val(data.consolidated_pay || '');
        $('#edit_start_date').val(data.start_date || new Date().toISOString().split('T')[0]); // Default to today for new
        $('#edit_end_date').val(data.end_date || '');
        
        // Status Logic
        // For New: Always Active (1).
        // For Edit: Use data.status (default 1).
        const status = (data.status === undefined || data.status === null) ? 1 : data.status;
        const isActive = (status == 1) || isNew;
        
        $('#edit_service_status_toggle').prop('checked', isActive).trigger('change');
        
        // "New Record" Checkbox Logic
        // If isNew = TRUE (Add New Button): Check it, but HIDE it. (Implicitly new).
        // If isNew = FALSE (Edit Button): Uncheck it, SHOW it (Option to create new).
        
        $('#check_new_record').prop('checked', isNew);
        
        if (isNew || hideToggle) {
            $('#new_record_toggle_container').hide();
        } else {
            $('#new_record_toggle_container').show();
        }
    });

    // AJAX Form Submission
    $(document).on('click', '.btn-submit-edit', function(e) {
        e.preventDefault();
        console.log('Submit clicked');
        
        const formId = $(this).data('form');
        const form = $('#' + formId);
        const url = form.attr('action');
        const formData = new FormData(form[0]);

        // Append checkbox manually if needed (not needed for FormData usually, but for serialized)
        // FormData handles inputs well.
        
        // Debug
        for (var pair of formData.entries()) {
            console.log(pair[0]+ ', ' + pair[1]); 
        }

        const submitBtn = $(this);
        submitBtn.prop('disabled', true).text('Processing...');

        $.ajax({
            url: url,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Success:', response);
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Error', response.message || 'Operation failed', 'error');
                    submitBtn.prop('disabled', false).text('Submit');
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                submitBtn.prop('disabled', false).text('Submit');
                
                let errorMsg = 'Something went wrong.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMsg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                
                Swal.fire('Validation Error', errorMsg, 'error');
            }
        });
    });

});
</script>
@endsection
