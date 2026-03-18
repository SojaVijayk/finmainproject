@extends('layouts/layoutMaster')

@section('title', 'Pay Item Master')
@section('page-style')
<style>
    .statement-header {
        border-bottom: 2px solid #696cff;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
    }
    .amount-column {
        font-family: 'Courier New', Courier, monospace;
        font-weight: 600;
        text-align: right;
    }
    .summary-card {
        border: 1px solid #e6e6e8;
        box-shadow: 0 0.125rem 0.25rem rgba(161, 172, 184, 0.4);
    }
</style>
@endsection

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
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title mb-0">Generate Pay Item Bill</h5>
            <small class="text-muted">Select a pay item and processing period to calculate and save bulk deductions or allowances.</small>
        </div>
        @if(isset($statementData))
            <a href="{{ route('pms.pay-item-master.index', $project_id) }}" class="btn btn-label-primary btn-sm">
                <i class="ti ti-plus me-1"></i> New Bill
            </a>
        @endif
    </div>
    <div class="card-body mt-4">
        @if(isset($statementData))
            {{-- Non-editable Statement Summary (From Page Refresh/Redirect) --}}
            <div class="summary-card p-4 rounded mb-4 border-success">
                <div class="statement-header d-flex justify-content-between align-items-start">
                    <div>
                        <h4 class="fw-bold mb-1 text-primary">{{ $project->title ?? 'Main Project' }}</h4>
                        <p class="text-muted mb-0"><i class="ti ti-map-pin me-1"></i>{{ $project->location ?? 'Site Office' }}</p>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-label-success mb-2">EXISTING PAY ITEM BILL</span>
                        <p class="mb-0 text-muted small">Processed: {{ date('d-M-Y H:i') }}</p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-1"><span class="fw-bold text-dark">Pay Item:</span> <span class="text-primary">{{ $payItem->name }}</span></div>
                        <div class="mb-1"><span class="fw-bold text-dark">Type:</span> <span class="badge bg-label-info">{{ $payItem->type }}</span></div>
                        <div class="mb-1"><span class="fw-bold text-dark">Batch ID:</span> <span class="text-primary">{{ $salaryId ?? 'N/A' }}</span></div>
                        <div class="mb-1"><span class="fw-bold text-dark">Employment Type:</span> <span class="text-success fw-bold">{{ $selectedEmploymentType ?? 'All Types' }}</span></div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="mb-1"><span class="fw-bold text-dark">Period:</span> <span class="text-secondary">{{ $periodLabel }}</span></div>
                        <div class="mb-1"><span class="fw-bold text-dark">Total Recipients:</span> <span class="badge bg-primary rounded-pill">{{ count($statementData) }}</span></div>
                    </div>
                </div>

                <div class="table-responsive border rounded bg-white">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 50px;">#</th>
                                <th>Employee Details</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Type</th>
                                <th class="text-end">Base Salary</th>
                                <th class="text-end text-success">Actual</th>
                                <th class="text-end text-primary">Period Salary</th>
                                <th class="text-end bg-label-dark fw-bold text-white">Saved Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $grandTotal = 0; @endphp
                            @foreach($statementData as $index => $row)
                                @php $grandTotal += $row->amount; @endphp
                                <tr>
                                    <td class="text-center text-muted">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $row->name }}</div>
                                        <small class="text-muted">ID: {{ $row->p_id }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-label-{{ $row->status == 'Active' ? 'success' : 'danger' }} small">
                                            {{ $row->status }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-label-secondary small">{{ $row->type }}</span>
                                    </td>
                                    <td class="text-end amount-column">
                                        ₹{{ number_format($row->base_salary, 2) }}
                                    </td>
                                    <td class="text-end amount-column text-success">
                                        ₹{{ number_format($row->actual_salary, 2) }}
                                    </td>
                                    <td class="text-end amount-column text-primary">
                                        ₹{{ number_format($row->total_gross, 2) }}
                                    </td>
                                    <td class="text-end amount-column fw-bold bg-label-dark">
                                        ₹{{ number_format($row->amount, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light border-top">
                            <tr>
                                <th colspan="7" class="text-end py-3 fw-bold">Grand Total Saved:</th>
                                <th class="text-end py-3 text-primary h5 mb-0 amount-column fw-bold">
                                    ₹{{ number_format($grandTotal, 2) }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif

        {{-- Two Column Layout for Filters and Existing Bills --}}
        <div class="row">
            <!-- Left Column: Filters -->
            <div class="col-md-5">
                <div class="card mb-4 shadow-none border">
                    <div class="card-header border-bottom pb-2">
                        <h5 class="card-title mb-0">Generate Bill Form</h5>
                    </div>
                    <div class="card-body pt-3">
                        <form action="{{ route('pms.pay-item-master.save-batch') }}" method="POST" id="generateBillForm" class="row g-3">
                            @csrf
                            <input type="hidden" name="project_id" id="bill_project_id" value="{{ $project_id }}">
                            
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Pay Item <span class="text-danger">*</span></label>
                                <select name="pay_item_id" id="bill_pay_item_id" class="form-select" required>
                                    <option value="">— Select Pay Item —</option>
                                    @foreach($payItems as $item)
                                        @if($item->status)
                                            <option value="{{ $item->id }}" data-type="{{ $item->type }}" {{ request('pay_item_id') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 text-truncate" id="month_container">
                                <label class="form-label fw-semibold" id="bill_month_label">From Month <span class="text-danger">*</span></label>
                                <select name="month" id="bill_month" class="form-select" required>
                                    <option value="">— Month —</option>
                                    @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $m)
                                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ $m }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold" id="bill_year_label">From Year <span class="text-danger">*</span></label>
                                <select name="year" id="bill_year" class="form-select" required>
                                    <option value="">— Year —</option>
                                    @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                                        <option value="{{ $y }}" {{ (request('year') ? request('year') == $y : $y == date('Y')) ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-md-6 d-none" id="to_month_container">
                                <label class="form-label fw-semibold">To Month <span class="text-danger">*</span></label>
                                <select name="to_month" id="bill_to_month" class="form-select">
                                    <option value="">— To Month —</option>
                                    @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $m)
                                        <option value="{{ $m }}" {{ request('to_month') == $m ? 'selected' : '' }}>{{ $m }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 d-none" id="to_year_container">
                                <label class="form-label fw-semibold">To Year <span class="text-danger">*</span></label>
                                <select name="to_year" id="bill_to_year" class="form-select">
                                    <option value="">— To Year —</option>
                                    @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                                        <option value="{{ $y }}" {{ (request('to_year') ? request('to_year') == $y : $y == date('Y')) ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Employment Type</label>
                                <select name="employment_type" id="bill_employment_type" class="form-select">
                                    <option value="">— All Types —</option>
                                    @foreach($employmentTypes as $type)
                                        <option value="{{ $type->employment_type }}" {{ request('employment_type') == $type->employment_type ? 'selected' : '' }}>{{ $type->employment_type }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">ID <span class="text-danger">*</span></label>
                                <input type="text" name="salary_id" id="bill_salary_id" class="form-control" placeholder="ID" required value="{{ request('salary_id') }}">
                            </div>

                            <div class="col-md-12 d-flex gap-2" id="generate_btn_container_wrapper">
                                <button type="button" class="btn btn-success flex-grow-1" id="btnDirectSaveBill">
                                    <i class="ti ti-device-floppy me-1 ti-xs"></i> Save
                                </button>
                                <button type="button" class="btn btn-primary flex-grow-1" id="btnGenerateList" disabled>
                                    <i class="ti ti-list me-1 ti-xs"></i> List
                                </button>
                            </div>
                        </form>
                        
                        <!-- Add to Pay Bill Action (Relocated below form for visibility) -->
                        <div class="mt-4 pt-3 border-top d-none text-center" id="add-pay-bill-action-container">
                            <div class="alert alert-label-primary mb-3 text-start" role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="ti ti-info-circle ti-sm me-2"></i>
                                    <span class="small fw-semibold">Action Locked! Click below to confirm and save.</span>
                                </div>
                            </div>
                            <button type="button" class="btn btn-primary btn-lg w-100 shadow-sm" id="btnTriggerAddPayBill">
                                <i class="ti ti-plus me-1"></i> Add to pay bill
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Existing Bills List -->
            <div class="col-md-7">
                <div id="existing-bills-card" class="card mb-4 shadow-none border">
                    <div class="card-header d-flex justify-content-between align-items-center border-bottom pb-2">
                        <h5 class="mb-0">Existing Pay Item Bill</h5>
                        <span class="badge bg-label-info">Period Summary</span>
                    </div>
                    <div class="card-body pt-3">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-truncate">Period</th>
                                        <th class="text-truncate">Pay Item</th>
                                        <th class="text-truncate">Employment Type</th>
                                        <th class="text-truncate">Id</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="existing-bills-table-body">
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                            <span>Fetching existing bills...</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="existing-bills-info" class="form-text mt-3 text-info d-flex align-items-center d-none">
                            <i class="ti ti-info-circle ti-xs me-2"></i> 
                            <span>Click a Batch ID to copy it to the form.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Results Container (Initially Hidden) --}}
        <form action="{{ route('pms.pay-item-master.store-bill') }}" method="POST" id="storeBillForm">
            @csrf
            <input type="hidden" name="pay_item_id" id="store_pay_item_id">
            <input type="hidden" name="month" id="store_month">
            <input type="hidden" name="year" id="store_year">
            <input type="hidden" name="to_month" id="store_to_month">
            <input type="hidden" name="to_year" id="store_to_year">
            <input type="hidden" name="salary_id" id="store_salary_id">


            <div id="billResultsContainer" style="display: none;">
                <hr class="my-4">

                <div id="editableListArea">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="fw-bold mb-0 text-dark"><i class="ti ti-users me-2"></i> Employee List</h4>
                        <button type="button" class="btn btn-primary" id="btnSaveFromList">
                            <i class="ti ti-device-floppy me-1 ti-xs"></i> Save & Pay Item Bill
                        </button>
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
                                    <th class="text-uppercase small text-muted fw-bold">Actual Salary</th>
                                    <th class="text-uppercase small text-muted fw-bold">Total Period Salary (6-Month)</th>
                                    <th class="text-uppercase small text-muted fw-bold" style="width: 180px;">Adjusted Amount</th>
                                </tr>
                            </thead>
                            <tbody id="billEmployeeListBody">
                                <!-- Populated via AJAX -->
                            </tbody>
                            <tfoot class="table-light border-top-0">
                                <tr>
                                    <th colspan="6" class="text-end fw-semibold text-dark">Total Amount:</th>
                                    <th class="fw-bold text-primary" id="footerTotalAmount">₹0.00</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>

        </form>

        {{-- AJAX Summary Container (Now OUTSIDE the hidden results form) --}}
        <div id="ajaxSummaryWrapper" class="mt-5" style="display: none;">
            <hr class="my-5 border-2">
            <div id="ajaxSummaryContainer"></div>
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
<!-- Confirmation Modal -->
<div class="modal fade" id="addPayBillModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold text-primary"><i class="ti ti-file-certificate me-2"></i>Confirm Pay Bill</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <div class="alert alert-info d-flex align-items-center mb-3" role="alert">
                    <span class="alert-icon text-info me-2">
                        <i class="ti ti-info-circle ti-xs"></i>
                    </span>
                    <span class="small">Please confirm the following details:</span>
                </div>
                
                <div class="detail-row mb-2">
                    <span class="text-muted small d-block">Pay Item</span>
                    <span class="fw-bold text-dark" id="modal_pay_item_val">-</span>
                </div>
                <div class="detail-row mb-2">
                    <span class="text-muted small d-block">Period</span>
                    <span class="fw-bold text-secondary" id="modal_period_val">-</span>
                </div>
                <div class="detail-row mb-2">
                    <span class="text-muted small d-block">Employment Type</span>
                    <span class="badge bg-label-info" id="modal_type_val">All Types</span>
                </div>
                <div class="detail-row mb-3">
                    <span class="text-muted small d-block">Id</span>
                    <span class="fw-bold text-primary fs-5" id="modal_id_val">-</span>
                </div>
            </div>
            <div class="modal-footer border-top bg-light">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="btnModalSaveBill">
                    <i class="ti ti-device-floppy me-1"></i> Save & Pay Item Bill
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(function () {
    // Session state
    var draftBills = [];

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
        const itemName = $(this).find('option:selected').text().toUpperCase();
        const isPF = itemName.includes('PF') || itemName.includes('PROVIDENT') || itemName.includes('FUND');
        const isFestBonus = itemName.includes('FESTIVAL') || itemName.includes('BONUS');

        if (isPF && !isFestBonus) {
            // Show range fields
            $('#to_month_container, #to_year_container').removeClass('d-none').show();
            $('#bill_to_month, #bill_to_year').prop('required', true);
            $('#bill_month_label').html('From Month <span class="text-danger">*</span>');
            $('#bill_year_label').html('From Year <span class="text-danger">*</span>');
            
            // Auto-fill To Year if empty
            if (!$('#bill_to_year').val()) {
                $('#bill_to_year').val($('#bill_year').val());
            }
            
            $('#generate_btn_container_wrapper').removeClass('ms-auto').addClass('col-md-12 mt-3');
        } else {
            // Single month fields
            $('#to_month_container, #to_year_container').addClass('d-none').hide();
            $('#bill_to_month, #bill_to_year').prop('required', false).val('');
            $('#bill_month_label').html('Month <span class="text-danger">*</span>');
            $('#bill_year_label').html('Year <span class="text-danger">*</span>');
            
            $('#generate_btn_container_wrapper').addClass('col-md-12').removeClass('col-md-12 mt-3');
        }
        fetchExistingBills();
    });
    // Trigger on load to ensure PF fields are correct if already selected
    $('#bill_pay_item_id').trigger('change');

    // Auto-fetch on change
    $(document).on('change', '#bill_month, #bill_year, #bill_employment_type, #bill_pay_item_id, #bill_to_month, #bill_to_year', function() {
        // Require new draft save
        $('#btnGenerateList').prop('disabled', true);
        $('#btnDirectSaveBill').prop('disabled', false).removeClass('btn-secondary').addClass('btn-success').html('<i class="ti ti-device-floppy me-1 ti-xs"></i> Save');
        
        // Hide previous results
        $('#billResultsContainer').slideUp();
        $('#ajaxSummaryWrapper').slideUp();

        // Just refresh the DB batches, don't update current draft in real-time
        fetchExistingBills();
    });

    // Trigger on load to ensure PF fields are correct if already selected
    $('#bill_pay_item_id').trigger('change');

    function fetchExistingBills() {
        const month = $('#bill_month').val();
        const year = $('#bill_year').val();
        const payItemId = $('#bill_pay_item_id').val();
        const employmentType = $('#bill_employment_type').val();
        const projectId = $('#bill_project_id').val();

        if (!month || !year) {
            $('#existing-bills-table-body').html('<tr><td colspan="5" class="text-center py-4 text-muted">Select Period and Type to see existing bills</td></tr>');
            $('#existing-bills-info').addClass('d-none');
            return;
        }

        $('#existing-bills-table-body').html('<tr><td colspan="5" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary me-2"></div><span>Loading history...</span></td></tr>');

        $.ajax({
            url: `/pms/pay-item-master/fetch-batches/${projectId}`,
            method: 'GET',
            data: {
                month: month,
                year: year,
                employment_type: employmentType,
                pay_item_id: payItemId
            },
            success: function(res) {
                if (res.success) {
                    window.lastFetchedBatches = res.batches; // Store for re-rendering
                    renderExistingBillsTable();
                }
            }
        });
    }

    function renderExistingBillsTable() {
        const batches = window.lastFetchedBatches || [];
        let rowsHtml = '';

        if (batches.length > 0) {
            batches.forEach(batch => {
                let badgeClass = 'bg-label-secondary';
                if (batch.status === 'In Progress') badgeClass = 'bg-label-warning';
                if (batch.status === 'Saved') badgeClass = 'bg-label-success';
                if (batch.status === 'Finalized') badgeClass = 'bg-label-primary';
                
                let actionsHtml = '';
                if (batch.status !== 'Finalized') {
                    actionsHtml += `
                        <button type="button" class="btn btn-outline-warning edit-batch-btn mb-1 w-100"
                            data-salary-id="${batch.salary_id}"
                            data-raw-month="${batch.raw_month}"
                            data-raw-year="${batch.raw_year}"
                            data-raw-to-month="${batch.raw_to_month || ''}"
                            data-raw-to-year="${batch.raw_to_year || ''}"
                            data-raw-item="${batch.raw_pay_item_id}">
                            <i class="ti ti-edit me-1"></i> Edit
                        </button>
                    `;
                } else {
                    actionsHtml += `
                        <button type="button" class="btn btn-outline-secondary d-none view-batch-btn mb-1 w-100"
                            data-salary-id="${batch.salary_id}">
                            <i class="ti ti-eye me-1"></i> View
                        </button>
                    `;
                }

                if (batch.status === 'Saved') {
                    actionsHtml += `
                        <button type="button" class="btn btn-primary finalize-batch-btn w-100"
                            data-salary-id="${batch.salary_id}">
                            <i class="ti ti-check me-1"></i> Finalize
                        </button>
                    `;
                }

                rowsHtml += `
                    <tr class="existing-batch-row db-batch" data-id="${batch.salary_id}">
                        <td class="align-middle">
                            <span class="badge ${badgeClass} small mb-1">${batch.status}</span><br>
                            <small class="fw-semibold text-muted">${batch.period_label}</small>
                        </td>
                        <td class="align-middle">
                            <span class="badge bg-label-primary small d-block mb-1">${batch.pay_item_name}</span>
                            <span class="badge bg-label-info small">${batch.employment_type}</span>
                        </td>
                        <td class="align-middle text-primary fw-bold">${batch.salary_id}</td>
                        <td class="text-center align-middle">
                            <div class="d-flex flex-column gap-1">
                                ${actionsHtml}
                            </div>
                        </td>
                    </tr>
                `;
            });
        }

        if (batches.length > 0) {
            $('#existing-bills-info').removeClass('d-none');
        } else {
            rowsHtml = '<tr><td colspan="5" class="text-center py-4 text-muted">No existing bills found for this selection</td></tr>';
            $('#existing-bills-info').addClass('d-none');
        }
        $('#existing-bills-table-body').html(rowsHtml);
    }

    // Handle "Edit" button click on existing batch
    $(document).on('click', '.edit-batch-btn', function() {
        const btn = $(this);
        const salaryId = btn.data('salary-id');
        const rawMonth = btn.data('raw-month');
        const rawYear = btn.data('raw-year');
        const rawToMonth = btn.data('raw-to-month');
        const rawToYear = btn.data('raw-to-year');
        const rawItemId = btn.data('raw-item');

        // Populate Form
        if (rawItemId) $('#bill_pay_item_id').val(rawItemId);
        if (rawMonth) $('#bill_month').val(rawMonth);
        if (rawYear) $('#bill_year').val(rawYear);
        
        if (rawToMonth) {
            $('#bill_to_month').val(rawToMonth);
            $('#to_month_container').removeClass('d-none').show();
        } else {
            $('#bill_to_month').val('');
        }
        
        if (rawToYear) {
            $('#bill_to_year').val(rawToYear);
            $('#to_year_container').removeClass('d-none').show();
        } else {
            $('#bill_to_year').val('');
        }

        // Trigger change once all fields are populated correctly to refresh right side context
        if (rawItemId) $('#bill_pay_item_id').trigger('change');

        $('#bill_salary_id').val(salaryId).addClass('is-valid');
        setTimeout(() => $('#bill_salary_id').removeClass('is-valid'), 1500);

        // Visual feedback
        $('.existing-batch-row').removeClass('table-warning');
        $(this).closest('tr').addClass('table-warning');
        
        // Hide existing list if open
        $('#billResultsContainer').slideUp();
        $('#ajaxSummaryWrapper').slideUp();
    });

    // Handle "List" button click on existing batch
    $(document).on('click', '.list-batch-btn', function() {
        // First simulate an Edit click to populate the form parameters
        $(this).siblings('.edit-batch-btn').trigger('click');
        
        // Then click Generate List to immediately load the employees
        setTimeout(() => {
            $('#btnGenerateList').trigger('click');
        }, 100);
    });

    $(document).on('click', '.finalize-batch-btn', function() {
        if (!confirm('Are you sure you want to Finalize this bill? This will lock it from further editing and automatically apply the amounts to the core payroll module.')) {
            return;
        }

        const btn = $(this);
        const salaryId = btn.data('salary-id');
        const originalHtml = btn.html();
        
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Finalizing...');

        $.ajax({
            url: "{{ route('pms.pay-item-master.finalize-bill') }}",
            method: 'POST',
            data: {
                _token: $('input[name="_token"]').val() || $('meta[name="csrf-token"]').attr('content'),
                salary_id: salaryId
            },
            success: function(res) {
                if (res.success) {
                    alert(res.message);
                    fetchExistingBills();
                } else {
                    alert(res.message || 'Error finalizing bill.');
                }
            },
            error: function(xhr) {
                alert('Failed to finalize bill. ' + (xhr.responseJSON ? xhr.responseJSON.message : ''));
            },
            complete: function() {
                btn.prop('disabled', false).html(originalHtml);
            }
        });
    });

    // Handle "View" button click on existing finalized batch (Read-Only Summary)
    $(document).on('click', '.view-batch-btn', function() {
        const salaryId = $(this).data('salary-id');
        window.location.href = "{{ route('pms.pay-item-master.index') }}?show_summary=1" + 
                               "&pay_item_id=" + $('#bill_pay_item_id').val() +
                               "&salary_id=" + salaryId;
    });

    $('#btnGenerateList').on('click', function (e) {
        const itemId   = $('#bill_pay_item_id').val();
        const month    = $('#bill_month').val();
        const year     = $('#bill_year').val();
        const toMonth  = $('#bill_to_month').val();
        const toYear   = $('#bill_to_year').val();
        const projectId = $('#bill_project_id').val();
        const employmentType = $('#bill_employment_type').val();
        
        if (!itemId || !month || !year) {
            alert('Please select Pay Item, Month, and Year.');
            return;
        }

        // Hide previous summary/bill when generating fresh list
        $('#ajaxSummaryWrapper').slideUp();

        const btn = $(this);
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
                project_id: projectId,
                employment_type: employmentType,
                salary_id: $('#bill_salary_id').val()
            },
            success: function (res) {
                // Focus reset: Hide previous results and show history again when generating new list
                $('#ajaxSummaryWrapper').slideUp();
                $('#add-pay-bill-action-container').addClass('d-none');
                $('#existing-bills-card').closest('.col-md-7').show();
                $('.col-md-5').show(); // Ensure form is visible
                if (res.success) {
                    $('#store_pay_item_id').val(itemId);
                    $('#store_month').val(month);
                    $('#store_year').val(year);
                    $('#store_to_month').val(toMonth);
                    $('#store_to_year').val(toYear);
                    $('#store_salary_id').val($('#bill_salary_id').val());
                    
                    // renderExistingBillsTable(); // No longer needed here as list is only for saved bills
                    
                    const $body = $('#billEmployeeListBody').empty();
                    
                    if (res.employees.length === 0) {
                        let debugHtml = '';
                        if (res.debug) {
                            debugHtml = `
                                <div class="mt-4 p-3 bg-light border rounded text-start mx-auto" style="max-width: 500px; font-size: 0.85rem;">
                                    <h6 class="mb-2"><i class="ti ti-bug me-1"></i> Debug Information:</h6>
                                    <ul class="list-unstyled mb-0">
                                        <li><strong>Project ID Filter:</strong> ${res.debug.project_id_used}</li>
                                        <li><strong>Total Employees in this Project:</strong> ${res.debug.total_employees_assigned_to_project}</li>
                                        <li><strong>Freeze Employees Found (Any Month):</strong> <span class="text-success fw-bold">${res.debug.total_freeze_employees_in_project}</span></li>
                                        <li><strong>Selected Month for Calculation:</strong> ${res.debug.period_selected_on_page.start_month} ${res.debug.period_selected_on_page.start_year} ${res.debug.period_selected_on_page.is_range ? ' to ' + res.debug.period_selected_on_page.end_month + ' ' + res.debug.period_selected_on_page.end_year : ''}</li>
                                        <li class="mt-2 text-info"><i class="ti ti-info-circle small"></i> Show all freeze employees, don't use their month for filtering.</li>
                                    </ul>
                                </div>
                            `;
                        }

                        $body.append('<tr><td colspan="9" class="text-center py-5">' +
                            '<i class="ti ti-info-circle text-info fs-1 mb-2 d-block"></i>' +
                            '<div class="h5 mb-1">No Freeze Employees Found</div>' +
                            '<p class="text-muted mb-0">Ensure that employees for this project have their salary **Freeze** in the <a href="/pms/salary-management/' + (res.debug ? res.debug.project_id_used : projectId) + '" class="fw-bold">Salary Management</a> section for the selected period.</p>' +
                            debugHtml +
                        '</td></tr>');
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
                                    <td><span class="fw-semibold text-secondary">₹${parseFloat(emp.base_salary).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                                        <input type="hidden" name="base_salary[${emp.p_id}]" value="${emp.base_salary}">
                                        <input type="hidden" name="actual_salary[${emp.p_id}]" value="${emp.actual_salary}">
                                        <input type="hidden" name="total_period_salary[${emp.p_id}]" value="${emp.total_gross}">
                                    </td>
                                    <td><span class="fw-semibold text-success">₹${parseFloat(emp.actual_salary).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></td>
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
                    $('#editableListArea').show();
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
                btn.prop('disabled', false).html('<i class="ti ti-list me-1 ti-xs"></i> List');
                // Hide the "Add to pay bill" section as the list has changed
                $('#add-pay-bill-action-container').addClass('d-none');
            }
        });
    });

    // ---- Save Pay Item Bill (Updated to Multi-Step flow) ----
    $(document).on('click', '#btnDirectSaveBill, #btnSaveFromList', function () {
        const btnId = $(this).attr('id');
        window.lastSaveTrigger = btnId; // Track which button opened the modal
        
        const itemId = $('#bill_pay_item_id').val();
        const month = $('#bill_month').val();
        const year = $('#bill_year').val();
        const salaryId = $('#bill_salary_id').val();

        if (btnId === 'btnDirectSaveBill') {
            if (!itemId || !month || !year || !salaryId) {
                alert('Please fill in Pay Item, Month, Year, and Id before saving.');
                if (!salaryId) $('#bill_salary_id').focus();
                return;
            }
        } else {
            if (!itemId || !month || !year) {
                alert('Please fill in Pay Item, Month, and Year before saving.');
                return;
            }
        }

        if (btnId === 'btnSaveFromList') {
            // Dynamic Label for Modal Button
            $('#btnModalSaveBill').html('<i class="ti ti-device-floppy me-1"></i> Save & Pay Item Bill');
            
            // SYNC main form to the hidden store form
            $('#store_pay_item_id').val(itemId);
            $('#store_month').val(month);
            $('#store_year').val(year);
            $('#store_to_month').val($('#bill_to_month').val());
            $('#store_to_year').val($('#bill_to_year').val());
            $('#store_salary_id').val(salaryId);

            window.lastSaveBtn = $(this);
            // STREAMLINED: Open modal directly for list-based saves
            $('#btnTriggerAddPayBill').trigger('click');
        } else {
            // DRAFT SAVE: Form Persistence Step
            const btn = $(this);
            const originalHtml = btn.html();
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving Draft...');

            let batchData = $('#generateBillForm').serialize();
            
            $.ajax({
                url: "{{ route('pms.pay-item-master.draft-bill') }}",
                method: 'POST',
                data: batchData,
                success: function(res) {
                    if (res.success) {
                        // 1. Enable List Button
                        $('#btnGenerateList').prop('disabled', false);
                        
                        // 2. Change Save button to Draft Saved visually
                        btn.removeClass('btn-success').addClass('btn-secondary').html('<i class="ti ti-check me-1"></i> Draft Saved');
                        
                        // 3. Update sidebar history if requested (optional logic)
                        fetchExistingBills();
                        
                        // 4. Notification
                        alert('Draft saved. You can now List the employees.');
                    } else {
                        alert(res.message || 'Error saving draft.');
                        btn.html(originalHtml).prop('disabled', false);
                    }
                },
                error: function(xhr) {
                    alert('Failed to save draft. ' + (xhr.responseJSON ? xhr.responseJSON.message : ''));
                    btn.html(originalHtml).prop('disabled', false);
                }
            });
        }
    });

    // ---- Render Save Summary ----
    function renderSummary(res) {
        try {
            if (!res.success || !res.summary) {
                console.error("Summary failed:", res);
                return;
            }

        const s = res.summary;
        let rowsHtml = '';
        let grandTotal = 0;
        
        s.statementData.forEach((row, i) => {
            grandTotal += parseFloat(row.amount);
            rowsHtml += `
                <tr>
                    <td class="text-center text-muted small">${i + 1}</td>
                    <td>
                        <div class="fw-bold text-dark mb-0">${row.name}</div>
                        <small class="text-muted">ID: ${row.p_id}</small>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-label-${row.status === 'Active' ? 'success' : 'danger'} small">${row.status}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-label-info small">${row.type}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-label-secondary small">${s.periodLabel}</span>
                    </td>
                    <td class="text-end amount-column">₹${parseFloat(row.base_salary).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td class="text-end amount-column text-success">₹${parseFloat(row.actual_salary).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td class="text-end amount-column text-primary">₹${parseFloat(row.total_gross).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td class="text-end amount-column fw-bold bg-label-dark text-dark">₹${parseFloat(row.amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                </tr>
            `;
        });

        const summaryHtml = `
            <div class="summary-card p-4 rounded mb-4 border-success shadow-sm bg-white">
                <div class="statement-header d-flex justify-content-between align-items-start border-bottom pb-3 mb-4">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded bg-label-success"><i class="ti ti-file-analytics ti-md"></i></span>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-1 text-primary">${res.summary.projectTitle || 'Main Project'}</h4>
                            <p class="text-dark mb-0 fw-bold fs-3 text-uppercase letter-spacing-1"><i class="ti ti-file-text text-primary me-2"></i> BILL</p>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-success mb-2 px-3 py-2"><i class="ti ti-lock me-1 ti-xs"></i> OFFICIAL RECORD</span>
                        <p class="mb-2 text-muted small">Processed: ${new Date().toLocaleString()}</p>
                        <a href="{{ route('pms.pay-item-master.index', $project_id) }}" class="btn btn-primary btn-sm mt-1 shadow-sm">
                            <i class="ti ti-plus ti-xs me-1"></i> New Bill
                        </a>
                    </div>
                </div>

                <div class="row mb-4 bg-light p-3 rounded mx-0">
                    <div class="col-md-6 border-end">
                        <div class="mb-2"><span class="text-muted small d-block">PAY ITEM</span> <span class="fw-bold text-dark fs-5">${res.summary.payItem.name}</span> <span class="badge bg-label-info ms-1">${res.summary.payItem.type}</span></div>
                        <div class="mb-0"><span class="text-muted small d-block">BATCH ID</span> <span class="fw-bold text-primary">${res.summary.salaryId || 'N/A'}</span></div>
                    </div>
                    <div class="col-md-6 ps-4">
                        <div class="mb-2"><span class="text-muted small d-block">PERIOD</span> <span class="fw-bold text-secondary fs-5">${res.summary.periodLabel}</span></div>
                        <div class="mb-0"><span class="text-muted small d-block">EMPLOYMENT TYPE</span> <span class="fw-bold text-success">${res.summary.selectedEmploymentType || 'All Types'}</span></div>
                    </div>
                </div>

                <div class="table-responsive border rounded shadow-none">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center" style="width: 50px;">#</th>
                                <th>EMPLOYEE</th>
                                <th class="text-center">STATUS</th>
                                <th class="text-center">TYPE</th>
                                <th class="text-center">PERIOD</th>
                                <th class="text-end">BASE SALARY</th>
                                <th class="text-end">ACTUAL SALARY</th>
                                <th class="text-end">PERIOD SALARY</th>
                                <th class="text-end bg-label-dark text-white fw-bold">BILL AMOUNT</th>
                            </tr>
                        </thead>
                        <tbody>${rowsHtml}</tbody>
                        <tfoot class="table-light border-top border-dark">
                            <tr>
                                <th colspan="8" class="text-end py-3 fw-bold fs-5">TOTAL BILL AMOUNT:</th>
                                <th class="text-end py-3 text-primary h4 mb-0 amount-column fw-bold">
                                    ₹${grandTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        `;

        $('#ajaxSummaryContainer').html(summaryHtml);
        $('#ajaxSummaryWrapper').slideDown(function() {
            // Focus on results with auto-scroll
            $('html, body').animate({
                scrollTop: $("#ajaxSummaryWrapper").offset().top - 20
            }, 600);
        });

        // Hide employee list + reveal button immediately after save
        $('#billResultsContainer').slideUp();
        $('#add-pay-bill-action-container').addClass('d-none');

        // DEDICATED BILL VIEW: Hide Form and History to focus on results
        $('#generate-bill-card').closest('.col-md-5').fadeOut();
        $('#existing-bills-card').closest('.col-md-7').fadeOut();
        
        // Update history sidebar in background
        fetchExistingBills();

        // Safe history tracking
        if (typeof draftBills !== 'undefined') {
            draftBills.push({
                salary_id: s.salaryId,
                pay_item_name: s.payItem.name,
                period_label: s.periodLabel,
                employment_type: s.selectedEmploymentType || 'All Types',
                timestamp: new Date().getTime()
            });
        }
    } catch (err) {
        console.error("renderSummary Error:", err);
        alert("Error showing bill summary: " + err.message);
    }
}

    // ---- AJAX Save Pay Item Bill Handler ----
    $('#storeBillForm').on('submit', function(e) {
        e.preventDefault();
        
        // Ensure unchecked employees are cleared out if overwritten
        $('.hidden-unchecked-pid').remove();
        $('.emp-checkbox:not(:checked)').each(function() {
            $(this).after(`<input type="hidden" name="p_id[]" value="${$(this).val()}" class="hidden-unchecked-pid">`);
            $(this).closest('tr').find('.bill-amount-input').val(0);
        });

        const form = $(this);
        const btn = window.lastSaveBtn || $('#btnDirectSaveBill');
        const originalHtml = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function(res) {
                if (res.success && res.summary) {
                    renderSummary(res);

                    // draftBills push removed to prevent duplicates
                } else {
                    alert(res.message || 'Error saving bill.');
                }
            },
            error: function(xhr) {
                alert('Failed to save bill. ' + (xhr.responseJSON ? xhr.responseJSON.message : ''));
            },
            complete: function() {
                btn.prop('disabled', false).html(originalHtml);
                window.lastSaveBtn = null;
            }
        });
    });

    // ---- Save Logic (Redundant logic removed) ----
    $(document).on('click', '#btnAddAnother', function() {
        // This function is now mostly bypassed but kept for edge cases or future use
        $('#storeBillForm').submit();
    });

    $('#generateBillForm').on('submit', function() {
        $('#btnDirectSaveBill').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
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

    $('#selectAllEmp').on('change', function () {
        $('.emp-checkbox').prop('checked', $(this).is(':checked'));
        calculateTotalBill();
    });

    // ---- Trigger Add Pay Bill Modal ----
    $('#btnTriggerAddPayBill').on('click', function() {
        const itemId = $('#bill_pay_item_id').val();
        const month = $('#bill_month').val();
        const year = $('#bill_year').val();
        const toMonth = $('#bill_to_month').val();
        const toYear = $('#bill_to_year').val();
        const id = $('#bill_salary_id').val();
        
        if (!itemId || !month || !year) {
            alert('Please fill in Pay Item, Month, and Year details.');
            return;
        }

        let period = `${month} ${year}`;
        if (toMonth && toYear) {
            period = `${month} ${year} - ${toMonth} ${toYear}`;
        }
        
        $('#modal_pay_item_val').text($('#bill_pay_item_id option:selected').text());
        $('#modal_period_val').text(period);
        $('#modal_type_val').text($('#bill_employment_type option:selected').text() || 'All Types');
        $('#modal_id_val').text(id || '(New Bill)');
        
        // Add Project Detail for completeness
        const projectText = $('#bill_project_id option:selected').text();
        if (!$('#modal_project_row').length) {
            $('.detail-row:first-child').before(`
                <div class="detail-row mb-2 border-bottom pb-2" id="modal_project_row">
                    <span class="text-muted small d-block">Project</span>
                    <span class="fw-bold text-primary">${projectText}</span>
                </div>
            `);
        } else {
            $('#modal_project_row span:last-child').text(projectText);
        }
        
        const modal = new bootstrap.Modal(document.getElementById('addPayBillModal'));
        modal.show();
    });

    // ---- Save from Modal (The Actual Persistence Step) ----
    $('#btnModalSaveBill').on('click', function() {
        const modalEl = document.getElementById('addPayBillModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        const btn = $(this);
        const originalHtml = btn.html();
        
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        
        const salaryId = $('#bill_salary_id').val();
        const isListGenerated = $('#billResultsContainer').is(':visible') && $('.emp-checkbox:checked').length > 0;

        // Simply bind the exact draft salary ID for backend submission
        $('#store_salary_id').val(salaryId);

        if (isListGenerated) {
            // Traditional List Save -> Generates Official BILL
            const formData = $('#storeBillForm').serialize();
            $.ajax({
                url: $('#storeBillForm').attr('action'),
                method: 'POST',
                data: formData,
                success: function(res) {
                    if (res.success && res.summary) {
                        modal.hide();
                        // Path 2: Show the official BILL document
                        renderSummary(res);
                        $('#add-pay-bill-action-container').addClass('d-none');
                    } else {
                        alert(res.message || 'Error saving bill.');
                    }
                },
                error: function(xhr) {
                    alert('Failed to save bill. ' + (xhr.responseJSON ? xhr.responseJSON.message : ''));
                },
                complete: function() {
                    btn.prop('disabled', false).html(originalHtml);
                }
            });
        } else {
            // Direct Batch Save (Fast path) -> Simple Save to History
            let batchData = $('#generateBillForm').serialize();
            $.ajax({
                url: "{{ route('pms.pay-item-master.save-batch') }}",
                method: 'POST',
                data: batchData,
                success: function(res) {
                    if (res.success && res.summary) {
                        modal.hide();
                        // Path 1: Just update history in background
                        alert('Bill saved to history successfully');
                        fetchExistingBills();
                        $('#add-pay-bill-action-container').addClass('d-none');
                    } else {
                        alert(res.message || 'Error saving batch.');
                    }
                },
                error: function(xhr) {
                    alert('Failed to save batch. ' + (xhr.responseJSON ? xhr.responseJSON.message : ''));
                },
                complete: function() {
                    btn.prop('disabled', false).html(originalHtml);
                }
            });
        }
    });

});
</script>
@endsection
