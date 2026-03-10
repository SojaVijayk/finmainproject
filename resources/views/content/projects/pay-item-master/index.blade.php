@extends('layouts/layoutMaster')

@section('title', 'Pay Item Master')

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">PMS /</span> Pay Item Master
</h4>

<div class="card mb-4">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title mb-0">Pay Item Management</h5>
            <small class="text-muted">Configure pay items, types, and dynamic salary slab rules.</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ $project_id ? route('pms.employees.index', $project_id) : route('pms.employees.index') }}" class="btn btn-label-secondary">
                <i class="ti ti-arrow-left me-1 ti-xs"></i> Back
            </a>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPayItemModal">
                <i class="ti ti-plus me-0 me-sm-1 ti-xs"></i> Add Pay Item
            </button>
        </div>
    </div>

    <div class="card-body mt-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible d-flex align-items-center mb-4" role="alert">
                <i class="ti ti-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible mb-4">
                <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-responsive text-nowrap">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Pay Item Name</th>
                        <th>Type</th>
                        <th>Slab Based</th>
                        <th>Slabs Configured</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payItems as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="fw-semibold text-primary">{{ $item->name }}</td>
                        <td>
                            @php
                                $typeColors = ['Deduction' => 'danger', 'Allowance' => 'success', 'Recovery' => 'warning'];
                                $color = $typeColors[$item->type] ?? 'secondary';
                            @endphp
                            <span class="badge bg-label-{{ $color }}">{{ $item->type }}</span>
                        </td>
                        <td>
                            @if($item->is_slab_based)
                                <span class="badge bg-label-success"><i class="ti ti-check ti-xs me-1"></i>Yes</span>
                            @else
                                <span class="badge bg-label-secondary"><i class="ti ti-x ti-xs me-1"></i>No</span>
                            @endif
                        </td>
                        <td>
                            @if($item->slabs->count())
                                <span class="badge bg-label-info">{{ $item->slabs->count() }} slab(s)</span>
                                <small class="text-muted d-block" style="font-size:0.75rem;">
                                    ₹{{ number_format($item->slabs->first()->salary_from) }} – ₹{{ number_format($item->slabs->last()->salary_to) }}
                                </small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($item->status)
                                <span class="badge bg-label-success">Active</span>
                            @else
                                <span class="badge bg-label-danger">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                <button class="btn btn-icon btn-label-info btn-sm edit-record"
                                        data-id="{{ $item->id }}"
                                        data-name="{{ $item->name }}"
                                        data-type="{{ $item->type }}"
                                        data-slab="{{ $item->is_slab_based }}"
                                        data-status="{{ $item->status }}"
                                        data-slabs="{{ $item->slabs->toJson() }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editPayItemModal"
                                        title="Edit">
                                    <i class="ti ti-edit"></i>
                                </button>
                                <form action="{{ route('pms.pay-item-master.destroy', $item->id) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this Pay Item and all its slabs?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-icon btn-label-danger btn-sm" title="Delete">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="ti ti-inbox ti-lg mb-2 d-block"></i>
                            No Pay Items configured. Click <strong>Add Pay Item</strong> to get started.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ==================== GENERATE PAY ITEM BILL ==================== --}}
<div class="card mb-4">
    <div class="card-header border-bottom">
        <h5 class="card-title mb-0">Generate Pay Item Bill</h5>
        <small class="text-muted">Select a pay item and processing period to calculate and save bulk deductions or allowances.</small>
    </div>
    <div class="card-body mt-4">
        {{-- Filters --}}
        <form id="generateBillForm" class="row g-3 align-items-end mb-4">
            <input type="hidden" id="bill_project_id" value="{{ $project_id }}">
            <div class="col-md-3">
                <label class="form-label fw-semibold">Pay Item <span class="text-danger">*</span></label>
                <select id="bill_pay_item_id" class="form-select" required>
                    <option value="">— Select Pay Item —</option>
                    @foreach($payItems as $item)
                        @if($item->status)
                            <option value="{{ $item->id }}" data-type="{{ $item->type }}">{{ $item->name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">From Month <span class="text-danger">*</span></label>
                <select id="bill_month" class="form-select" required>
                    <option value="">— Month —</option>
                    @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $m)
                        <option value="{{ $m }}">{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">From Year <span class="text-danger">*</span></label>
                <select id="bill_year" class="form-select" required>
                    <option value="">— Year —</option>
                    @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                        <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            {{-- Range selection for PF Tax --}}
            <div class="col-md-3 d-none" id="to_month_container">
                <label class="form-label fw-semibold">To Month <span class="text-danger">*</span></label>
                <select id="bill_to_month" class="form-select">
                    <option value="">— To Month —</option>
                    @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $m)
                        <option value="{{ $m }}">{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-none" id="to_year_container">
                <label class="form-label fw-semibold">To Year <span class="text-danger">*</span></label>
                <select id="bill_to_year" class="form-select">
                    <option value="">— To Year —</option>
                    @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                        <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div class="col-md-3 ms-auto" id="generate_btn_container">
                <button type="submit" class="btn btn-primary w-100" id="btnGenerateList">
                    <i class="ti ti-list-search me-1 ti-xs"></i> Generate List
                </button>
            </div>
        </form>

        {{-- Results Container (Initially Hidden) --}}
        <div id="billResultsContainer" style="display: none;">
            <hr class="my-4">
            <form action="{{ route('pms.pay-item-master.store-bill') }}" method="POST" id="storeBillForm">
                @csrf
                <input type="hidden" name="pay_item_id" id="store_pay_item_id">
            <input type="hidden" name="month" id="store_month">
            <input type="hidden" name="year" id="store_year">
            <input type="hidden" name="to_month" id="store_to_month">
            <input type="hidden" name="to_year" id="store_to_year">
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="mb-0 fw-semibold">Employee List</h6>
                        <small class="text-muted">Calculated amounts can be adjusted manually before saving.</small>
                    </div>
                </div>

                <div class="table-responsive text-nowrap mb-4">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;"><input type="checkbox" id="selectAllEmp" class="form-check-input" checked></th>
                                <th class="text-uppercase small text-muted fw-bold">Employee</th>
                                <th class="text-uppercase small text-muted fw-bold">Status</th>
                                <th class="text-uppercase small text-muted fw-bold">Type</th>
                                <th class="text-uppercase small text-muted fw-bold">Period</th>
                                <th class="text-uppercase small text-muted fw-bold">Base Salary</th>
                                <th class="text-uppercase small text-muted fw-bold">Total Period Salary (6-Month)</th>
                                <th class="text-uppercase small text-muted fw-bold" style="width: 180px;">Adjusted Amount</th>
                            </tr>
                        </thead>
                        <tbody id="billEmployeeListBody">
                            <!-- Populated via AJAX -->
                        </tbody>
                        <tfoot class="table-light border-top-0">
                            <tr>
                                <th colspan="5" class="text-end fw-semibold text-dark">Total Amount:</th>
                                <th class="fw-bold text-primary" id="footerTotalAmount">₹0.00</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success" id="btnSaveBill">
                        <i class="ti ti-device-floppy me-1 ti-xs"></i> Save Pay Item Bill
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ==================== ADD MODAL ==================== --}}
<div class="modal fade" id="addPayItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('pms.pay-item-master.store') }}" method="POST" id="addPayItemForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti ti-settings me-2"></i>Add Pay Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        {{-- Name --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="add_name">
                                Pay Item Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="add_name" name="name" class="form-control"
                                   list="nameOptions" placeholder="e.g. PF Tax, Festival Allowance" required>
                            <datalist id="nameOptions">
                                <option value="PF Tax">
                                <option value="Festival Allowance">
                                <option value="Bonus Allowance">
                            </datalist>
                        </div>
                        {{-- Type --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="add_type">
                                Type <span class="text-danger">*</span>
                            </label>
                            <select id="add_type" name="type" class="form-select" required>
                                <option value="">— Select Type —</option>
                                <option value="Deduction">Deduction</option>
                                <option value="Allowance">Allowance</option>
                                <option value="Recovery">Recovery</option>
                            </select>
                        </div>
                        {{-- Toggles --}}
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" id="add_is_slab_based" name="is_slab_based" value="1" checked>
                                <label class="form-check-label" for="add_is_slab_based">Slab Based?</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" id="add_status" name="status" value="1" checked>
                                <label class="form-check-label" for="add_status">Active Status</label>
                            </div>
                        </div>
                    </div>

                    {{-- Slab Configuration --}}
                    <hr class="my-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="mb-0 fw-semibold">Slab Configuration</h6>
                            <small class="text-muted">Define salary range → fixed amount rules.</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-label-success add-slab-btn" data-target="#add-slab-table">
                            <i class="ti ti-plus me-1 ti-xs"></i> Add Slab
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" id="add-slab-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Salary From (₹)</th>
                                    <th>Salary To (₹)</th>
                                    <th>Amount (₹)</th>
                                    <th style="width:50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="add-slab-body">
                                <tr class="slab-row">
                                    <td><input type="number" name="slab_from[]" class="form-control form-control-sm" placeholder="0" min="0" step="0.01"></td>
                                    <td><input type="number" name="slab_to[]"   class="form-control form-control-sm" placeholder="1000" min="0" step="0.01"></td>
                                    <td><input type="number" name="slab_amount[]" class="form-control form-control-sm" placeholder="10" min="0" step="0.01"></td>
                                    <td class="text-center"><button type="button" class="btn btn-icon btn-sm btn-label-danger remove-slab-btn" title="Remove"><i class="ti ti-trash ti-xs"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <small class="text-muted"><i class="ti ti-info-circle me-1"></i>Example: 0–1000 → ₹10 &nbsp;|&nbsp; 1001–5000 → ₹50 &nbsp;|&nbsp; 5001–10000 → ₹100</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1 ti-xs"></i>Save Pay Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ==================== EDIT MODAL ==================== --}}
<div class="modal fade" id="editPayItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('pms.pay-item-master.store') }}" method="POST" id="editPayItemForm">
                @csrf
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti ti-edit me-2"></i>Edit Pay Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="edit_name">
                                Pay Item Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="edit_name" name="name" class="form-control"
                                   list="nameOptions" placeholder="e.g. PF Tax" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="edit_type">
                                Type <span class="text-danger">*</span>
                            </label>
                            <select id="edit_type" name="type" class="form-select" required>
                                <option value="">— Select Type —</option>
                                <option value="Deduction">Deduction</option>
                                <option value="Allowance">Allowance</option>
                                <option value="Recovery">Recovery</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" id="edit_is_slab_based" name="is_slab_based" value="1">
                                <label class="form-check-label" for="edit_is_slab_based">Slab Based?</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" id="edit_status" name="status" value="1">
                                <label class="form-check-label" for="edit_status">Active Status</label>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="mb-0 fw-semibold">Slab Configuration</h6>
                            <small class="text-muted">Existing slabs will be fully replaced on save.</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-label-success add-slab-btn" data-target="#edit-slab-table">
                            <i class="ti ti-plus me-1 ti-xs"></i> Add Slab
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" id="edit-slab-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Salary From (₹)</th>
                                    <th>Salary To (₹)</th>
                                    <th>Amount (₹)</th>
                                    <th style="width:50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="edit-slab-body">
                                {{-- Rows injected via JS on modal open --}}
                            </tbody>
                        </table>
                    </div>
                    <small class="text-muted"><i class="ti ti-info-circle me-1"></i>Leave slab table empty to remove all slabs.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1 ti-xs"></i>Update Pay Item</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
$(function () {

    // ---- Shared slab row template ----
    function slabRowHtml(from, to, amt) {
        from = from ?? '';  to = to ?? '';  amt = amt ?? '';
        return `<tr class="slab-row">
            <td><input type="number" name="slab_from[]"   class="form-control form-control-sm" value="${from}" placeholder="0"    min="0" step="0.01"></td>
            <td><input type="number" name="slab_to[]"     class="form-control form-control-sm" value="${to}"   placeholder="1000" min="0" step="0.01"></td>
            <td><input type="number" name="slab_amount[]" class="form-control form-control-sm" value="${amt}"  placeholder="10"   min="0" step="0.01"></td>
            <td class="text-center"><button type="button" class="btn btn-icon btn-sm btn-label-danger remove-slab-btn" title="Remove"><i class="ti ti-trash ti-xs"></i></button></td>
        </tr>`;
    }

    // ---- Add Slab button (works for both modals via data-target) ----
    $(document).on('click', '.add-slab-btn', function () {
        var target = $(this).data('target');
        $(target + ' tbody').append(slabRowHtml());
    });

    // ---- Remove Slab row ----
    $(document).on('click', '.remove-slab-btn', function () {
        var $tbody = $(this).closest('tbody');
        if ($tbody.find('.slab-row').length > 1) {
            $(this).closest('.slab-row').remove();
        } else {
            // Just clear values if it's the last row
            $(this).closest('.slab-row').find('input').val('');
        }
    });

    // ---- Populate Edit Modal ----
    $(document).on('click', '.edit-record', function () {
        var $btn   = $(this);
        var slabs  = $btn.data('slabs');

        $('#edit_id').val($btn.data('id'));
        $('#edit_name').val($btn.data('name'));
        $('#edit_type').val($btn.data('type'));
        $('#edit_is_slab_based').prop('checked', $btn.data('slab') == 1);
        $('#edit_status').prop('checked',        $btn.data('status') == 1);

        // Populate slab rows
        var $tbody = $('#edit-slab-body').empty();
        if (slabs && slabs.length) {
            $.each(slabs, function (i, s) {
                $tbody.append(slabRowHtml(s.salary_from, s.salary_to, s.amount));
            });
        } else {
            $tbody.append(slabRowHtml()); // at least one blank row
        }
    });

    // ---- Reset Add Modal on close ----
    $('#addPayItemModal').on('hidden.bs.modal', function () {
        $('#addPayItemForm')[0].reset();
        // Leave the first slab row but clear it
        $('#add-slab-body .slab-row').not(':first').remove();
        $('#add-slab-body .slab-row:first input').val('');
    });

    // ---- Generate Pay Item Bill via AJAX ----
    $('#bill_pay_item_id').on('change', function() {
        const itemName = $(this).find('option:selected').text();
        if (itemName === 'PF Tax') {
            $('#to_month_container, #to_year_container').removeClass('d-none');
            $('#bill_to_month, #bill_to_year').prop('required', true);
            $('#generate_btn_container').removeClass('ms-auto').addClass('col-md-12 mt-3');
        } else {
            $('#to_month_container, #to_year_container').addClass('d-none');
            $('#bill_to_month, #bill_to_year').prop('required', false).val('');
            $('#generate_btn_container').addClass('ms-auto').removeClass('col-md-12 mt-3');
        }
    });

    $('#generateBillForm').on('submit', function (e) {
        e.preventDefault();
        
        const itemId   = $('#bill_pay_item_id').val();
        const month    = $('#bill_month').val();
        const year     = $('#bill_year').val();
        const toMonth  = $('#bill_to_month').val();
        const toYear   = $('#bill_to_year').val();
        const projectId = $('#bill_project_id').val();
        
        if (!itemId || !month || !year) return;

        const btn = $('#btnGenerateList');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Processing...');

        $.ajax({
            url: "{{ route('pms.pay-item-master.generate-bill') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                pay_item_id: itemId,
                month: month,
                year: year,
                to_month: toMonth,
                to_year: toYear,
                project_id: projectId
            },
            success: function (res) {
                if (res.success) {
                    $('#store_pay_item_id').val(itemId);
                    $('#store_month').val(month);
                    $('#store_year').val(year);
                    $('#store_to_month').val(toMonth);
                    $('#store_to_year').val(toYear);
                    
                    const $body = $('#billEmployeeListBody').empty();
                    
                    if (res.employees.length === 0) {
                        $body.append('<tr><td colspan="7" class="text-center py-4">No payroll records found for selected period.</td></tr>');
                        $('#btnSaveBill').prop('disabled', true);
                    } else {
                        res.employees.forEach(function (emp) {
                            let periodText = res.period_text;
                            let statusBadge = '';
                            if (emp.current_status === 'Active') {
                                statusBadge = '<span class="badge bg-label-success">Active</span>';
                            } else if (emp.current_status === 'Inactive' || emp.current_status === 'Terminated') {
                                statusBadge = '<span class="badge bg-label-danger">' + emp.current_status + '</span>';
                            } else {
                                statusBadge = '<span class="badge bg-label-secondary">' + (emp.current_status || 'Unknown') + '</span>';
                            }

                            $body.append(`
                                <tr>
                                    <td><input type="checkbox" name="p_id[]" value="${emp.p_id}" class="form-check-input emp-checkbox" checked></td>
                                    <td>
                                        <div class="fw-semibold text-dark">${emp.employee_name}</div>
                                        <small class="text-muted">ID: ${emp.p_id}</small>
                                    </td>
                                    <td>${statusBadge}</td>
                                    <td><span class="badge bg-label-info">${emp.employment_type || 'N/A'}</span></td>
                                    <td><span class="badge bg-label-secondary">${periodText}</span></td>
                                    <td><span class="fw-semibold text-secondary">₹${parseFloat(emp.base_salary).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></td>
                                    <td><span class="fw-bold text-primary">₹${parseFloat(emp.total_gross).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" name="amount[${emp.p_id}]" class="form-control bill-amount-input" 
                                                   value="${emp.calculated_amount}" step="0.01" min="0">
                                        </div>
                                    </td>
                                </tr>
                            `);
                        });
                        $('#btnSaveBill').prop('disabled', false);
                    }
                    
                    $('#billResultsContainer').slideDown();
                    calculateTotalBill();
                } else {
                    alert(res.message || 'Error generating list.');
                }
            },
            error: function (xhr) {
                let msg = 'Failed to fetch employee list.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg += '\n\nError: ' + xhr.responseJSON.message;
                }
                alert(msg);
            },
            complete: function () {
                btn.prop('disabled', false).html('<i class="ti ti-list-search me-1 ti-xs"></i> Generate List');
            }
        });
    });

    // ---- Calculation Logic ----
    function calculateTotalBill() {
        let total = 0;
        $('.emp-checkbox:checked').each(function () {
            const pId = $(this).val();
            const amt = parseFloat($(`input[name="amount[${pId}]"]`).val()) || 0;
            total += amt;
        });
        $('#footerTotalAmount').text('₹' + total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    }

    $(document).on('change', '.emp-checkbox, .bill-amount-input', function () {
        calculateTotalBill();
    });

    $('#selectAllEmployees').on('change', function () {
        $('.emp-checkbox').prop('checked', $(this).is(':checked'));
        calculateTotalBill();
    });

});
</script>
@endsection
