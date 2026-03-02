@extends('layouts/layoutMaster')

@section('title', 'Create Proposals')

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
    const startDateInput = document.getElementById('expected_start_date');
    const endDateInput = document.getElementById('expected_end_date');

    const tenureYears = document.getElementById('tenure_years');
    const tenureMonths = document.getElementById('tenure_months');
    const tenureDays = document.getElementById('tenure_days');

    const budgetInput = document.getElementById('budget');
    const expenseInput = document.getElementById('estimated_expense');
    const revenueInput = document.getElementById('revenue');

    // --- Function 1: Calculate End Date based on Tenure ---
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

        endDateInput.value = endDate.toISOString().split('T')[0];
      }
    }

    tenureYears.addEventListener('change', calculateEndDate);
    tenureMonths.addEventListener('change', calculateEndDate);
    tenureDays.addEventListener('change', calculateEndDate);

    // --- Function 2: Calculate Tenure based on Start & End Dates ---
    function calculateTenure() {
      if (startDateInput.value && endDateInput.value) {
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(endDateInput.value);

        if (endDate < startDate) {
          tenureYears.value = 0;
          tenureMonths.value = 0;
          tenureDays.value = 0;
          return;
        }

        let years = endDate.getFullYear() - startDate.getFullYear();
        let months = endDate.getMonth() - startDate.getMonth();
        let days = endDate.getDate() - startDate.getDate();

        if (days < 0) {
          months -= 1;
          const prevMonth = new Date(endDate.getFullYear(), endDate.getMonth(), 0).getDate();
          days += prevMonth;
        }

        if (months < 0) {
          years -= 1;
          months += 12;
        }

        tenureYears.value = years;
        tenureMonths.value = months;
        tenureDays.value = days;
      }
    }

    startDateInput.addEventListener('change', function() {
      if (endDateInput.value) calculateTenure();
    });

    endDateInput.addEventListener('change', calculateTenure);

    function calculateRevenue() {
      const budget = parseFloat(budgetInput.value) || 0;
      const expense = parseFloat(expenseInput.value) || 0;
      const revenue = budget - expense;
      revenueInput.value = revenue >= 0 ? revenue.toFixed(2) : 0;
    }

    budgetInput.addEventListener('input', calculateRevenue);
    expenseInput.addEventListener('input', calculateRevenue);


     const container = document.getElementById('expense-components-container');
    const addButton = document.getElementById('add-component');
    const totalExpenseSpan = document.getElementById('total-expense');
    {{--  const estimatedExpenseInput = document.getElementById('estimated_expense');  --}}
    let componentCount = 1;

    // Add new component row
    addButton.addEventListener('click', function() {
        {{--  const newRow = document.querySelector('.expense-component').cloneNode(true);
        newRow.innerHTML = newRow.innerHTML.replace(/\[0\]/g, `[${componentCount}]`);

        // Clear values
        newRow.querySelector('.expense-category').value = '';
        newRow.querySelector('input[name*="component"]').value = '';
        newRow.querySelector('.expense-amount').value = '';  --}}
         const template = document.getElementById('custom-component-template');
  const newRow = template.cloneNode(true);
  newRow.id = ''; // remove id
  newRow.classList.remove('d-none');

  // Update input names with index
  newRow.querySelectorAll('input, select').forEach(el => {
    el.name = el.name.replace('[0]', `[custom_${componentCount}]`);
    el.value = '';
  });

        // Show remove button
        {{--  newRow.querySelector('.remove-component').style.display = 'block';  --}}
         const removeBtn = newRow.querySelector('.remove-component');
  if (removeBtn) removeBtn.style.display = 'block';

        // Add remove functionality
        {{--  newRow.querySelector('.remove-component').addEventListener('click', function() {
            newRow.remove();
            calculateTotalExpense();
        });  --}}
          removeBtn?.addEventListener('click', function() {
    newRow.remove();
    calculateTotalExpense();
  });

        // Add input listener for amount
        newRow.querySelector('.expense-amount').addEventListener('input', calculateTotalExpense);

        container.appendChild(newRow);
        componentCount++;
    });

    // Remove component functionality for first row
    document.querySelector('.remove-component')?.addEventListener('click', function() {
        if (document.querySelectorAll('.expense-component').length > 1) {
            this.closest('.expense-component').remove();
            calculateTotalExpense();
        }
    });

    // Calculate total expense
    function calculateTotalExpense() {
        let total = 0;
        document.querySelectorAll('.expense-amount').forEach(input => {
            total += parseFloat(input.value) || 0;
        });

        totalExpenseSpan.textContent = total.toFixed(2);
        expenseInput.value = total.toFixed(2);

        // Update revenue calculation
        calculateRevenue();
    }

    // Add event listener to initial amount input
    document.querySelector('.expense-amount').addEventListener('input', calculateTotalExpense);

    // Update revenue calculation to use component total
    function calculateRevenue() {
        const budget = parseFloat(document.getElementById('budget').value) || 0;
        const expense = parseFloat(expenseInput.value) || 0;
        const revenue = budget - expense;
        document.getElementById('revenue').value = revenue >= 0 ? revenue.toFixed(2) : 0;
    }

    // Update existing revenue calculation to use component total
    {{--  const budgetInput = document.getElementById('budget');  --}}
    if (budgetInput) {
        budgetInput.addEventListener('input', calculateRevenue);
    }

document.querySelectorAll('.mandays-input').forEach(input => {
  input.addEventListener('input', function() {
    const target = this.dataset.target;
    const rateField = document.querySelector(`.rate-input[data-target="${target}"]`);
    const amountField = document.getElementById(`amount_${target}`);

    const rate = parseFloat(rateField.value) || 0;
    const mandays = parseFloat(this.value) || 0;
    const amount = mandays * rate;

    amountField.value = amount.toFixed(2);

     const min = parseFloat(this.getAttribute('min')) || 0;
    const value = parseFloat(this.value) || 0;
    const error = this.parentElement.querySelector('.error-message');

    if (value < min) {
      this.classList.add('is-invalid');     // Red border (Bootstrap)
      error.style.display = 'block';        // Show warning text
    } else {
      this.classList.remove('is-invalid');
      error.style.display = 'none';
    }


    calculateTotalExpense();
  });
});

  });
</script>
@endsection


@section('content')
<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header">
        @if ($errors->any())
        <div class="alert alert-danger">
          <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
        @endif
        <h5 class="card-title">Project:{{ $requirement->project_title }}- ( {{ $requirement->temp_no }} )</h5>
      </div>
      <div class="card-body">
        {{-- Hidden Template for New Components --}}
        <div class="expense-component row g-3 mb-2 d-none" id="custom-component-template">
          <input type="hidden" name="expense_components[0][group]" value="Custom">
          <div class="col-md-4">
            <label class="form-label">Category</label>
            <select name="expense_components[0][category_id]" class="form-select expense-category">
              <option value="">Select Category</option>
              @foreach($expenseCategories as $category)
              <option value="{{ $category->id }}">{{ $category->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Component</label>
            <input type="text" name="expense_components[0][component]" class="form-control"
              placeholder="Component name">
          </div>
          <div class="col-md-3">
            <label class="form-label">Amount (₹)</label>
            <input type="number" step="0.01" min="0" name="expense_components[0][amount]"
              class="form-control expense-amount" placeholder="0.00">
          </div>
          <div class="col-md-1">
            <label class="form-label">&nbsp;</label>
            <button type="button" class="btn btn-danger remove-component">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </div>
        <form action="{{ route('pms.proposals.store', $requirement->id) }}" method="POST" enctype="multipart/form-data">
          @csrf

          <div class="row mb-3">
            <div class="col-md-6">
              <label for="budget" class="form-label">Budget (₹ Without Tax)</label>
              <input type="number" step="0.01" min="0" name="budget" id="budget" class="form-control"
                value="{{ old('budget') }}" required>
              @error('budget')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label for="expected_start_date" class="form-label">Expected Start Date</label>
              <input type="date" name="expected_start_date" id="expected_start_date" class="form-control"
                value="{{ old('expected_start_date') }}">

              @error('expected_start_date')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

          </div>

          <div class="row mb-3">


            <div class="col-md-6">
              <label for="expected_end_date" class="form-label">Expected End Date</label>
              <input type="date" name="expected_end_date" id="expected_end_date" class="form-control"
                value="{{ old('expected_end_date') }}">
              @error('expected_end_date')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label class="form-label">Tenure</label>
              <div class="row g-2">
                <div class="col-4">
                  <label for="tenure_years" class="form-label">Years</label>
                  <input type="number" name="tenure_years" id="tenure_years" class="form-control" placeholder="Years"
                    value="{{ old('tenure_years', 0) }}" min="0">
                </div>
                <div class="col-4">
                  <label for="tenure_months" class="form-label">Months</label>
                  <input type="number" name="tenure_months" id="tenure_months" class="form-control" placeholder="Months"
                    value="{{ old('tenure_months', 0) }}" min="0" max="11">
                </div>
                <div class="col-4">
                  <label for="tenure_days" class="form-label">Days</label>
                  <input type="number" name="tenure_days" id="tenure_days" class="form-control" placeholder="Days"
                    value="{{ old('tenure_days', 0) }}" min="0" max="30">
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
            <div class="col-md-12">
              <label class="form-label">Estimated Expense Components</label>
              <div id="expense-components-container">

                {{-- HR GROUP --}}
                <h6 class="mt-3 mb-2 text-primary fw-bold">HR</h6>
                @php
                $hrComponents = [
                ['component' => 'Manpower-Faculty Cost', 'rate' => 14000, 'min'=>0.5, 'amount' => 7000],
                ['component' => 'Manpower-Sr Faculty Associate Cost', 'rate' => 8000,'min'=>0,'amount' => 0],
                ['component' => 'Manpower-Faculty Associate Cost', 'rate' => 6000,'min'=>0,'amount' => 0],
                ['component' => 'Manpower-Project Staff', 'rate' => 3200,'min'=>0,'amount' => 0],
                ['component' => 'Manpower-Consultants', 'rate' => 8000,'min'=>0,'amount' => 0],
                ];
                @endphp

                @foreach($hrComponents as $i => $item)
                <div class="expense-component row g-3 mb-2">
                  <input type="hidden" name="expense_components[hr_{{ $i }}][category_id]" value="1">
                  <input type="hidden" name="expense_components[hr_{{ $i }}][group]" value="HR">
                  <input type="hidden" name="expense_components[hr_{{ $i }}][component]"
                    value="{{ $item['component'] }}">

                  <div class="col-md-3">
                    <label class="form-label">Component</label>
                    <input type="text" class="form-control" value="{{ $item['component'] }}" readonly>
                  </div>

                  <div class="col-md-2">
                    <label class="form-label">Persondays</label>
                    <input type="number" name="expense_components[hr_{{ $i }}][mandays]"
                      class="form-control mandays-input" min="{{ $item['min'] }}" value="{{ $item['min'] }}"
                      data-target="hr_{{ $i }}" required>
                    <small class="text-danger error-message" style="display:none;">
                      Must be at least {{ $item['min'] }} days.
                    </small>
                  </div>

                  <div class="col-md-2">
                    <label class="form-label">Rate (₹)</label>
                    <input type="number" name="expense_components[hr_{{ $i }}][rate]" class="form-control rate-input"
                      value="{{ $item['rate'] }}" data-target="hr_{{ $i }}" readonly>
                  </div>

                  <div class="col-md-2">
                    <label class="form-label">Amount (₹)</label>
                    <input type="number" step="0.01" min="0" name="expense_components[hr_{{ $i }}][amount]"
                      class="form-control expense-amount" id="amount_hr_{{ $i }}" value="{{ $item['amount'] }}"
                      readonly>
                  </div>
                </div>
                @endforeach

                {{-- TRAVEL GROUP --}}
                <h6 class="mt-3 mb-2 text-primary fw-bold">Travel</h6>
                @php
                $travelComponents = [['component' => 'Travel-Faculty Cost', 'rate' => 5000],
                ['component' => 'Travel-Sr Faculty Associate Cost', 'rate' => 3000],
                ['component' => 'Travel-Faculty Associate Cost', 'rate' => 2000],
                ['component' => 'Travel-Project Staff', 'rate' => 1000],
                ['component' => 'Travel-Consultants', 'rate' => 3000],
                ];

                @endphp

                @foreach($travelComponents as $i => $item)
                <div class="expense-component row g-3 mb-2">
                  <input type="hidden" name="expense_components[travel_{{ $i }}][category_id]" value="1">
                  <input type="hidden" name="expense_components[travel_{{ $i }}][group]" value="Travel">
                  <input type="hidden" name="expense_components[travel_{{ $i }}][component]"
                    value="{{ $item['component'] }}">

                  <div class="col-md-3">
                    <label class="form-label">Component</label>
                    <input type="text" class="form-control" value="{{ $item['component'] }}" readonly>
                  </div>

                  <div class="col-md-3">
                    <label class="form-label">Amount (₹)</label>
                    <input type="number" step="0.01" min="0" name="expense_components[travel_{{ $i }}][amount]"
                      class="form-control expense-amount" value="0" required>
                  </div>
                </div>
                @endforeach

                {{-- OTHERS GROUP --}}
                <h6 class="mt-3 mb-2 text-primary fw-bold">Others</h6>
                @php
                $otherComponents = [
                ['component' => 'Laptop', 'rate' => 250],
                ['component' => 'Reports', 'rate' => 0],
                ['component' => 'Printing', 'rate' => 0],
                ['component' => 'Stationary', 'rate' => 0],
                ];
                @endphp

                @foreach($otherComponents as $i => $item)
                <div class="expense-component row g-3 mb-2">
                  <input type="hidden" name="expense_components[other_{{ $i }}][category_id]" value="1">
                  <input type="hidden" name="expense_components[other_{{ $i }}][group]" value="Others">
                  <input type="hidden" name="expense_components[other_{{ $i }}][component]"
                    value="{{ $item['component'] }}">

                  <div class="col-md-3">
                    <label class="form-label">Component</label>
                    <input type="text" class="form-control" value="{{ $item['component'] }}" readonly>
                  </div>

                  <div class="col-md-3">
                    <label class="form-label">Amount (₹)</label>
                    <input type="number" step="0.01" min="0" name="expense_components[other_{{ $i }}][amount]"
                      class="form-control expense-amount" value="0" required>
                  </div>
                </div>
                @endforeach

              </div>

              {{-- Add custom component button --}}
              <button type="button" id="add-component" class="btn btn-secondary btn-sm mt-2">
                <i class="fas fa-plus"></i> Add Custom Component
              </button>

              <div class="mt-3">
                <strong>Total Estimated Expense: ₹<span id="total-expense">0.00</span></strong>
              </div>

              <input type="hidden" name="estimated_expense" id="estimated_expense" value="0">
            </div>
          </div>
          <div class="row mb-3">
            {{-- <div class="col-md-6">
              <label for="estimated_expense" class="form-label">Estimated Expense (₹ Without Tax)</label>
              <input type="number" step="0.01" min="0" name="estimated_expense" id="estimated_expense"
                class="form-control" value="{{ old('estimated_expense') }}" required>
              @error('estimated_expense')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div> --}}
            <div class="col-md-6">
              <label for="revenue" class="form-label">Expected Revenue (₹ Without Tax)</label>
              <input type="number" step="0.01" min="0" name="revenue" id="revenue" class="form-control"
                value="{{ old('revenue') }}" required>
              @error('revenue')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-12">
              <label for="technical_details" class="form-label">Technical Details</label>
              <textarea name="technical_details" id="technical_details" class="form-control"
                rows="3">{{ old('technical_details') }}</textarea>
              @error('technical_details')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-12">
              <label for="methodology" class="form-label">Methodology</label>
              <textarea name="methodology" id="methodology" class="form-control"
                rows="3">{{ old('methodology') }}</textarea>
              @error('methodology')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-12">
              <label for="documents" class="form-label">Supporting Documents (Optional)</label>
              <input type="file" name="documents[]" id="documents" class="form-control" multiple>
              @error('documents')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
              <small class="text-muted">You can upload multiple files (Max 10MB each)</small>
            </div>
          </div>

          <div class="mt-4">
            <button type="submit" class="btn btn-primary me-2">Create Proposal</button>
            <a href="{{ route('pms.requirements.show', $requirement->id) }}" class="btn btn-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title">Requirement Details</h5>
      </div>
      <div class="card-body">
        {{-- <p><strong>Title:</strong> {{ $requirement->requirement->title }}</p> --}}
        <p><strong>Client:</strong> {{ $requirement->client->client_name }}</p>
        <p><strong>Category:</strong> {{ $requirement->category->name }}</p>
        <p><strong>Subcategory:</strong> {{ $requirement->subcategory->name ?? 'N/A' }}</p>
        <p><strong>Contact Person:</strong> {{ $requirement->contactPerson->name }}</p>
      </div>
    </div>
  </div>
</div>
@endsection
