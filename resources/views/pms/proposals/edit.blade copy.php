@extends('layouts/layoutMaster')

@section('title', 'Edit Proposals')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/spinkit/spinkit.css')}}" />

@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js')}}"></script>
<script src="{{asset('assets/vendor/libs/block-ui/block-ui.js')}}"></script>

@endsection

@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Set end date minimum based on start date
    const startDateInput = document.getElementById('expected_start_date');
    const endDateInput = document.getElementById('expected_end_date');

    startDateInput.addEventListener('change', function() {
        endDateInput.min = this.value;

        // If end date is before start date, reset it
        if (endDateInput.value && endDateInput.value < this.value) {
            endDateInput.value = '';
        }
    });

    // Calculate end date based on tenure if start date is set
    const tenureYears = document.getElementById('tenure_years');
    const tenureMonths = document.getElementById('tenure_months');
    const tenureDays = document.getElementById('tenure_days');

    function calculateEndDate() {
        if (startDateInput.value) {
            const startDate = new Date(startDateInput.value);
            const years = parseInt(tenureYears.value) || 0;
            const months = parseInt(tenureMonths.value) || 0;
            const days = parseInt(tenureDays.value) || 0;

            const endDate = new Date(startDate);
            endDate.setFullYear(endDate.getFullYear() + years);
            endDate.setMonth(endDate.getMonth() + months);
            endDate.setDate(endDate.getDate() + days);

            // Format as YYYY-MM-DD
            const formattedDate = endDate.toISOString().split('T')[0];
            endDateInput.value = formattedDate;
        }
    }

    tenureYears.addEventListener('change', calculateEndDate);
    tenureMonths.addEventListener('change', calculateEndDate);
    tenureDays.addEventListener('change', calculateEndDate);


// Expense Components Management
    const container = document.getElementById('expense-components-container');
    const addButton = document.getElementById('add-component');
    const totalExpenseSpan = document.getElementById('total-expense');
    const estimatedExpenseInput = document.getElementById('estimated_expense');
    let componentCount = {{ $proposal->expenseComponents->count() }};

    // Add new component row
    addButton.addEventListener('click', function() {
        const newRow = document.querySelector('.expense-component').cloneNode(true);
        newRow.innerHTML = newRow.innerHTML.replace(/\[0\]/g, `[${componentCount}]`);

        // Clear values
        newRow.querySelector('.expense-category').value = '';
        newRow.querySelector('input[name*="component"]').value = '';
        newRow.querySelector('.expense-amount').value = '';

        // Show remove button
        newRow.querySelector('.remove-component').style.display = 'block';

        // Add remove functionality
        newRow.querySelector('.remove-component').addEventListener('click', function() {
            newRow.remove();
            calculateTotalExpense();
        });

        // Add input listener for amount
        newRow.querySelector('.expense-amount').addEventListener('input', calculateTotalExpense);

        container.appendChild(newRow);
        componentCount++;
    });

    // Remove component functionality
    document.querySelectorAll('.remove-component').forEach(button => {
        button.addEventListener('click', function() {
            if (document.querySelectorAll('.expense-component').length > 1) {
                this.closest('.expense-component').remove();
                calculateTotalExpense();
            }
        });
    });

    // Calculate total expense
    function calculateTotalExpense() {
        let total = 0;
        document.querySelectorAll('.expense-amount').forEach(input => {
            total += parseFloat(input.value) || 0;
        });

        totalExpenseSpan.textContent = total.toFixed(2);
        estimatedExpenseInput.value = total.toFixed(2);

        // Update revenue calculation
        calculateRevenue();
    }

    // Add event listeners to all amount inputs
    document.querySelectorAll('.expense-amount').forEach(input => {
        input.addEventListener('input', calculateTotalExpense);
    });

    // Update revenue calculation to use component total
    function calculateRevenue() {
        const budget = parseFloat(document.getElementById('budget').value) || 0;
        const expense = parseFloat(estimatedExpenseInput.value) || 0;
        const revenue = budget - expense;
        document.getElementById('revenue').value = revenue >= 0 ? revenue.toFixed(2) : 0;
    }

    // Update existing revenue calculation to use component total
    const budgetInput = document.getElementById('budget');
    if (budgetInput) {
        budgetInput.addEventListener('input', calculateRevenue);
    }

    // Initial calculation
    calculateTotalExpense();
});
</script>
@endsection

@section('header', 'Edit Proposal')

@section('content')
<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title">Edit Proposal for Requirement: {{ $proposal->requirement->temp_no }}</h5>
      </div>
      <div class="card-body">
        <form action="{{ route('pms.proposals.update', $proposal->id) }}" method="POST" enctype="multipart/form-data">
          @csrf
          @method('PUT')

          <div class="row mb-3">
            <div class="col-md-6">
              <label for="budget" class="form-label">Budget (₹ Without Tax)</label>
              <input type="number" step="0.01" min="0" name="budget" id="budget" class="form-control"
                value="{{ old('budget', $proposal->budget) }}" required>
              @error('budget')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Tenure</label>
              <div class="row g-2">
                <div class="col-4">
                  <input type="number" name="tenure_years" id="tenure_years" class="form-control" placeholder="Years"
                    value="{{ old('tenure_years', $proposal->tenure_years) }}" min="0">
                </div>
                <div class="col-4">
                  <input type="number" name="tenure_months" id="tenure_months" class="form-control" placeholder="Months"
                    value="{{ old('tenure_months', $proposal->tenure_months) }}" min="0" max="11">
                </div>
                <div class="col-4">
                  <input type="number" name="tenure_days" id="tenure_days" class="form-control" placeholder="Days"
                    value="{{ old('tenure_days', $proposal->tenure_days) }}" min="0" max="30">
                </div>
              </div>
              @error('tenure_years')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
              @error('tenure_months')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
              @error('tenure_days')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label for="expected_start_date" class="form-label">Expected Start Date</label>
              <input type="date" name="expected_start_date" id="expected_start_date" class="form-control"
                value="{{ old('expected_start_date', $proposal->expected_start_date ? $proposal->expected_start_date->format('Y-m-d') : '') }}"
                min="{{ date('Y-m-d') }}" required>
              @error('expected_start_date')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label for="expected_end_date" class="form-label">Expected End Date</label>
              <input type="date" name="expected_end_date" id="expected_end_date" class="form-control"
                value="{{ old('expected_end_date',$proposal->expected_end_date ? $proposal->expected_end_date->format('Y-m-d') : '') }}"
                required>
              @error('expected_end_date')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <!-- Expense Components Section -->
          <div class="row mb-3">
            <div class="col-md-12">
              <label class="form-label">Estimated Expense Components</label>
              <div id="expense-components-container">
                @foreach($proposal->expenseComponents as $index => $component)
                <div class="expense-component row g-3 mb-3">
                  <div class="col-md-4">
                    <label class="form-label">Category</label>
                    <select name="expense_components[{{ $index }}][category_id]" class="form-select expense-category"
                      required>
                      <option value="">Select Category</option>
                      @foreach($expenseCategories as $category)
                      <option value="{{ $category->id }}" {{ $component->expense_category_id == $category->id ?
                        'selected' : '' }}>
                        {{ $category->name }}
                      </option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Component</label>
                    <input type="text" name="expense_components[{{ $index }}][component]" class="form-control"
                      value="{{ old('expense_components.' . $index . '.component', $component->component) }}"
                      placeholder="Component name" required>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Amount (₹)</label>
                    <input type="number" step="0.01" min="0" name="expense_components[{{ $index }}][amount]"
                      class="form-control expense-amount"
                      value="{{ old('expense_components.' . $index . '.amount', $component->amount) }}"
                      placeholder="0.00" required>
                  </div>
                  <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-danger remove-component"
                      style="{{ $loop->first && $proposal->expenseComponents->count() == 1 ? 'display: none;' : 'display: block;' }}">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </div>
                @endforeach

                @if($proposal->expenseComponents->count() === 0)
                <div class="expense-component row g-3 mb-3">
                  <div class="col-md-4">
                    <label class="form-label">Category</label>
                    <select name="expense_components[0][category_id]" class="form-select expense-category" required>
                      <option value="">Select Category</option>
                      @foreach($expenseCategories as $category)
                      <option value="{{ $category->id }}">{{ $category->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Component</label>
                    <input type="text" name="expense_components[0][component]" class="form-control"
                      placeholder="Component name" required>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Amount (₹)</label>
                    <input type="number" step="0.01" min="0" name="expense_components[0][amount]"
                      class="form-control expense-amount" placeholder="0.00" required>
                  </div>
                  <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-danger remove-component" style="display: none;">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </div>
                @endif
              </div>

              <button type="button" id="add-component" class="btn btn-secondary btn-sm mt-2">
                <i class="fas fa-plus"></i> Add Component
              </button>

              <div class="mt-3">
                <strong>Total Estimated Expense: ₹<span id="total-expense">{{
                    number_format($proposal->estimated_expense, 2) }}</span></strong>
              </div>

              <input type="hidden" name="estimated_expense" id="estimated_expense"
                value="{{ old('estimated_expense', $proposal->estimated_expense) }}">

              @error('estimated_expense')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
              @error('expense_components.*')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>
          <div class="row mb-3">
            {{-- <div class="col-md-6">
              <label for="estimated_expense" class="form-label">Estimated Expense (₹ Without Tax)</label>
              <input type="number" step="0.01" min="0" name="estimated_expense" id="estimated_expense"
                class="form-control" value="{{ old('estimated_expense', $proposal->estimated_expense) }}" required>
              @error('estimated_expense')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div> --}}
            <div class="col-md-6">
              <label for="revenue" class="form-label">Expected Revenue (₹ Without Tax)</label>
              <input type="number" step="0.01" min="0" name="revenue" id="revenue" class="form-control"
                value="{{ old('revenue', $proposal->revenue) }}" required>
              @error('revenue')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-12">
              <label for="technical_details" class="form-label">Technical Details</label>
              <textarea name="technical_details" id="technical_details" class="form-control"
                rows="3">{{ old('technical_details', $proposal->technical_details) }}</textarea>
              @error('technical_details')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-12">
              <label for="methodology" class="form-label">Methodology</label>
              <textarea name="methodology" id="methodology" class="form-control"
                rows="3">{{ old('methodology', $proposal->methodology) }}</textarea>
              @error('methodology')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-12">
              <label for="documents" class="form-label">Additional Documents (Optional)</label>
              <input type="file" name="documents[]" id="documents" class="form-control" multiple>
              @error('documents')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
              <small class="text-muted">You can upload multiple files (Max 10MB each)</small>
            </div>
          </div>

          <div class="mt-4">
            <button type="submit" class="btn btn-primary me-2">Update Proposal</button>
            <a href="{{ route('pms.proposals.show', $proposal->id) }}" class="btn btn-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title">Current Documents</h5>
      </div>
      <div class="card-body">
        @if($proposal->documents->count() > 0)
        <div class="list-group">
          @foreach($proposal->documents as $document)
          <div class="list-group-item d-flex justify-content-between align-items-center">
            <div>
              <i class="fas fa-file me-2"></i>
              {{ $document->name }}
            </div>
            <a href="{{ Storage::url($document->path) }}" target="_blank" class="btn btn-sm btn-primary">
              <i class="fas fa-download"></i>
            </a>
          </div>
          @endforeach
        </div>
        @else
        <p>No documents attached</p>
        @endif
      </div>
    </div>

    <div class="card mt-4">
      <div class="card-header">
        <h5 class="card-title">Requirement Details</h5>
      </div>
      <div class="card-body">
        <p><strong>Title:</strong> {{ $proposal->requirement->project_title }}</p>
        <p><strong>Client:</strong> {{ $proposal->requirement->client->client_name }}</p>
        <p><strong>Category:</strong> {{ $proposal->requirement->category->name }}</p>
        <p><strong>Subcategory:</strong> {{ $proposal->requirement->subcategory->name ?? 'N/A' }}</p>
        <p><strong>Contact Person:</strong> {{ $proposal->requirement->contactPerson->name }}</p>
      </div>
    </div>
  </div>
</div>
@endsection