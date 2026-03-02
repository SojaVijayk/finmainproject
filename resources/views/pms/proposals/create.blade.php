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
<style>
  .difference-positive {
    background-color: #d1fae5 !important;
    color: #065f46 !important;
  }

  .difference-negative {
    background-color: #fee2e2 !important;
    color: #991b1b !important;
  }

  .budget-section {
    border-left: 4px solid #3b82f6;
    padding-left: 15px;
    margin-top: 20px;
  }

  .comparison-table {
    font-size: 0.875rem;
  }

  .budget-component {
    background-color: #f8fafc;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 10px;
  }
</style>
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
    let componentCount = 1;

    // Add new component row
    addButton.addEventListener('click', function() {
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
         const removeBtn = newRow.querySelector('.remove-component');
  if (removeBtn) removeBtn.style.display = 'block';

        // Add remove functionality
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

    // ============ BUDGETED EXPENSE FUNCTIONALITY ============
    const copyToBudgetBtn = document.getElementById('copy-to-budget');
    const budgetExpenseContainer = document.getElementById('budget-expense-components-container');
    const comparisonContainer = document.getElementById('expense-comparison-container');
    const copyToBudgetInput = document.getElementById('copy_to_budget');
    const addBudgetComponentBtn = document.getElementById('add-budget-component');

    let hasBudgetComponents = false;
    let budgetComponentCount = 0;

    // Copy estimated to budgeted
    if (copyToBudgetBtn) {
        copyToBudgetBtn.addEventListener('click', function() {
            if (hasBudgetComponents) {
                Swal.fire({
                    title: 'Already Copied',
                    text: 'Budgeted expenses have already been created from estimated expenses.',
                    icon: 'info'
                });
                return;
            }

            // Clone all estimated components to budgeted section
            const estimatedComponents = document.querySelectorAll('#expense-components-container .expense-component');

            estimatedComponents.forEach((component, index) => {
                const clone = component.cloneNode(true);

                // Update names for budgeted components
                clone.querySelectorAll('input, select').forEach(el => {
                    if (el.name) {
                        el.name = el.name.replace('expense_components', 'budgeted_expense_components');
                        // Remove readonly from amount inputs for budgeted
                        if (el.classList.contains('expense-amount') && !el.classList.contains('rate-input')) {
                            el.readOnly = false;
                            el.classList.add('budget-amount');
                        }
                        // Remove readonly from mandays inputs for budgeted
                        if (el.classList.contains('mandays-input')) {
                            el.readOnly = false;
                            el.classList.add('budget-mandays');
                        }
                    }
                });

                // Update ID for budget amount fields
                const amountField = clone.querySelector('.budget-amount');
                if (amountField && amountField.id) {
                    amountField.id = 'budget_' + amountField.id;
                }

                // Add budget-specific classes and attributes
                clone.classList.add('budget-component');

                // Add remove button functionality for budget components
                const removeBtn = clone.querySelector('.remove-component');
                if (removeBtn) {
                    removeBtn.style.display = 'block';
                    removeBtn.addEventListener('click', function() {
                        clone.remove();
                        calculateDifferences();
                    });
                }

                if (budgetExpenseContainer) {
                    budgetExpenseContainer.appendChild(clone);
                }
            });

            // Show budget section and comparison
            const budgetSection = document.getElementById('budget-expense-section');
            if (budgetSection) budgetSection.style.display = 'block';
            if (comparisonContainer) comparisonContainer.style.display = 'block';
            if (addBudgetComponentBtn) addBudgetComponentBtn.style.display = 'block';

            if (copyToBudgetInput) copyToBudgetInput.value = '1';
            hasBudgetComponents = true;
            copyToBudgetBtn.disabled = true;
            copyToBudgetBtn.innerHTML = '<i class="fas fa-check"></i> Copied to Budget';

            // Add event listeners to budget amount inputs
            document.querySelectorAll('.budget-amount').forEach(input => {
                input.addEventListener('input', calculateDifferences);
            });

            // Add event listeners to budget mandays inputs
            document.querySelectorAll('.budget-mandays').forEach(input => {
                input.addEventListener('input', function() {
                    calculateBudgetAmount(this);
                    calculateDifferences();
                });
            });

            calculateDifferences();
        });
    }

    // Add custom budget component
    if (addBudgetComponentBtn) {
        addBudgetComponentBtn.addEventListener('click', function() {
            if (!hasBudgetComponents) {
                Swal.fire({
                    title: 'Copy First',
                    text: 'Please copy estimated expenses to budgeted first.',
                    icon: 'warning'
                });
                return;
            }

            const template = document.getElementById('budget-custom-component-template');
            if (!template) return;

            const newRow = template.cloneNode(true);
            newRow.id = '';
            newRow.classList.remove('d-none');
            newRow.classList.add('budget-component');

            // Update input names with index
            newRow.querySelectorAll('input, select').forEach(el => {
                el.name = el.name.replace('[0]', `[custom_budget_${budgetComponentCount}]`);
                if (el.classList.contains('budget-amount')) {
                    el.value = '';
                }
            });

            // Add remove functionality
            const removeBtn = newRow.querySelector('.remove-component');
            if (removeBtn) {
                removeBtn.style.display = 'block';
                removeBtn.addEventListener('click', function() {
                    newRow.remove();
                    calculateDifferences();
                });
            }

            // Add input listener for amount
            const amountInput = newRow.querySelector('.budget-amount');
            if (amountInput) {
                amountInput.addEventListener('input', calculateDifferences);
            }

            if (budgetExpenseContainer) {
                budgetExpenseContainer.appendChild(newRow);
            }
            budgetComponentCount++;
        });
    }

    // Calculate budget amount for HR components
    function calculateBudgetAmount(input) {
        const target = input.dataset.target;
        const rateField = document.querySelector(`.rate-input[data-target="${target}"]`);
        const amountField = document.querySelector(`#budget_amount_${target}`);

        if (rateField && amountField) {
            const rate = parseFloat(rateField.value) || 0;
            const mandays = parseFloat(input.value) || 0;
            const amount = mandays * rate;
            amountField.value = amount.toFixed(2);

            // Validation
            const min = parseFloat(input.getAttribute('min')) || 0;
            const error = input.parentElement.querySelector('.error-message');

            if (mandays < min) {
                input.classList.add('is-invalid');
                if (error) error.style.display = 'block';
            } else {
                input.classList.remove('is-invalid');
                if (error) error.style.display = 'none';
            }
        }
    }

    // Calculate differences between estimated and budgeted
    function calculateDifferences() {
        const estimatedComponents = document.querySelectorAll('#expense-components-container .expense-component');
        const budgetComponents = document.querySelectorAll('#budget-expense-components-container .budget-component');

        let totalEstimated = 0;
        let totalBudgeted = 0;

        // Calculate estimated total
        estimatedComponents.forEach(comp => {
            const estimatedAmount = parseFloat(comp.querySelector('.expense-amount')?.value) || 0;
            totalEstimated += estimatedAmount;
        });

        // Calculate budgeted total
        budgetComponents.forEach(comp => {
            const budgetAmount = parseFloat(comp.querySelector('.budget-amount')?.value) || 0;
            totalBudgeted += budgetAmount;
        });

        // Update totals
        const totalBudgetExpenseSpan = document.getElementById('total-budget-expense');
        const totalEstimatedDisplay = document.getElementById('total-estimated-display');
        const totalBudgetDisplay = document.getElementById('total-budget-display');
        const totalDifference = document.getElementById('total-difference');

        if (totalBudgetExpenseSpan) totalBudgetExpenseSpan.textContent = totalBudgeted.toFixed(2);
        if (totalEstimatedDisplay) totalEstimatedDisplay.textContent = totalEstimated.toFixed(2);
        if (totalBudgetDisplay) totalBudgetDisplay.textContent = totalBudgeted.toFixed(2);
        if (totalDifference) {
            totalDifference.textContent = (totalBudgeted - totalEstimated).toFixed(2);

            // Style total difference
            const difference = totalBudgeted - totalEstimated;
            totalDifference.className = difference >= 0 ? 'difference-positive' : 'difference-negative';
        }

        // Update comparison table
        updateComparisonTable();
    }

    function updateComparisonTable() {
        const tbody = document.querySelector('#expense-comparison-table tbody');
        if (!tbody) return;

        tbody.innerHTML = '';

        const estimatedComponents = document.querySelectorAll('#expense-components-container .expense-component');
        const budgetComponents = document.querySelectorAll('#budget-expense-components-container .budget-component');

        let hasVisibleRows = false;

        // Match components by index for predefined ones
        estimatedComponents.forEach((comp, index) => {
            // Get component name
            const componentNameInput = comp.querySelector('input[name*="component"]');
            const componentNameReadonly = comp.querySelector('input[readonly]');
            let componentName = 'Component';

            if (componentNameInput && componentNameInput.value) {
                componentName = componentNameInput.value;
            } else if (componentNameReadonly && componentNameReadonly.value) {
                componentName = componentNameReadonly.value;
            }

            // Get group name from hidden input
            const groupInput = comp.querySelector('input[name*="group"]');
            let groupName = 'Custom';
            if (groupInput && groupInput.value) {
                groupName = groupInput.value;
            }

            const estimatedAmount = parseFloat(comp.querySelector('.expense-amount')?.value) || 0;
            const budgetAmount = parseFloat(budgetComponents[index]?.querySelector('.budget-amount')?.value) || 0;
            const difference = budgetAmount - estimatedAmount;

            // Skip if both amounts are zero
            if (estimatedAmount === 0 && budgetAmount === 0) {
                return;
            }

            hasVisibleRows = true;

            const row = document.createElement('tr');

            // Add style class based on difference
            if (difference > 0) {
                row.classList.add('difference-positive');
            } else if (difference < 0) {
                row.classList.add('difference-negative');
            }

            row.innerHTML = `
                <td>
                    <strong>${componentName}</strong><br>
                    <small class="text-muted">Group: ${groupName}</small>
                </td>
                <td>₹${estimatedAmount.toFixed(2)}</td>
                <td>₹${budgetAmount.toFixed(2)}</td>
                <td>₹${difference.toFixed(2)}</td>
            `;

            tbody.appendChild(row);
        });

        // Handle custom budget components that don't have estimated counterparts
        if (budgetComponents.length > estimatedComponents.length) {
            for (let i = estimatedComponents.length; i < budgetComponents.length; i++) {
                const budgetComp = budgetComponents[i];

                // Get component name for custom budget component
                const componentNameInput = budgetComp.querySelector('input[name*="component"]');
                let componentName = 'Custom Component';
                if (componentNameInput && componentNameInput.value) {
                    componentName = componentNameInput.value;
                }

                // Get group name for custom budget component
                const groupInput = budgetComp.querySelector('input[name*="group"]');
                let groupName = 'Custom';
                if (groupInput && groupInput.value) {
                    groupName = groupInput.value;
                }

                const budgetAmount = parseFloat(budgetComp.querySelector('.budget-amount')?.value) || 0;
                const difference = budgetAmount; // No estimated counterpart, so difference is the full amount

                // Skip if budget amount is zero
                if (budgetAmount === 0) {
                    continue;
                }

                hasVisibleRows = true;

                const row = document.createElement('tr');
                row.classList.add('difference-positive'); // Custom components are always positive difference

                row.innerHTML = `
                    <td>
                        <strong>${componentName}</strong><br>
                        <small class="text-muted">Group: ${groupName} (Custom Budget)</small>
                    </td>
                    <td>₹0.00</td>
                    <td>₹${budgetAmount.toFixed(2)}</td>
                    <td>₹${difference.toFixed(2)}</td>
                `;

                tbody.appendChild(row);
            }
        }

        // Show message if no rows to display
        if (!hasVisibleRows) {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td colspan="4" class="text-center text-muted py-3">
                    No differences to display. All components have zero values.
                </td>
            `;
            tbody.appendChild(row);
        }
    }

    // Initialize calculations
    calculateTotalExpense();
});
</script>
@endsection

@section('content')
<div class="row">
  <div class="col-md-12">
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
        {{-- Hidden Template for New Estimated Components --}}
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

        {{-- Hidden Template for New Budget Components --}}
        <div class="expense-component row g-3 mb-2 d-none" id="budget-custom-component-template">
          <input type="hidden" name="budgeted_expense_components[0][group]" value="Custom">
          <div class="col-md-4">
            <label class="form-label">Category</label>
            <select name="budgeted_expense_components[0][category_id]" class="form-select expense-category">
              <option value="">Select Category</option>
              @foreach($expenseCategories as $category)
              <option value="{{ $category->id }}">{{ $category->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Component</label>
            <input type="text" name="budgeted_expense_components[0][component]" class="form-control"
              placeholder="Component name">
          </div>
          <div class="col-md-3">
            <label class="form-label">Amount (₹)</label>
            <input type="number" step="0.01" min="0" name="budgeted_expense_components[0][amount]"
              class="form-control budget-amount" placeholder="0.00">
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
          <input type="hidden" name="copy_to_budget" id="copy_to_budget" value="0">

          {{-- Basic Information Section --}}
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="budget" class="form-label">Budget (₹ Without Tax)</label>
              <input type="number" step="0.01" min="0" name="budget" id="budget" class="form-control"
                value="{{ old('budget') }}" required>
              @error('budget')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
              <label for="expected_start_date" class="form-label">Expected Start Date</label>
              <input type="date" name="expected_start_date" id="expected_start_date" class="form-control"
                value="{{ old('expected_start_date') }}">
              @error('expected_start_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
          </div>

          {{-- Date and Tenure Section --}}
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="expected_end_date" class="form-label">Expected End Date</label>
              <input type="date" name="expected_end_date" id="expected_end_date" class="form-control"
                value="{{ old('expected_end_date') }}">
              @error('expected_end_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Tenure</label>
              <div class="row g-2">
                <div class="col-4"><input type="number" name="tenure_years" id="tenure_years" class="form-control"
                    placeholder="Years" value="{{ old('tenure_years', 0) }}" min="0"></div>
                <div class="col-4"><input type="number" name="tenure_months" id="tenure_months" class="form-control"
                    placeholder="Months" value="{{ old('tenure_months', 0) }}" min="0" max="11"></div>
                <div class="col-4"><input type="number" name="tenure_days" id="tenure_days" class="form-control"
                    placeholder="Days" value="{{ old('tenure_days', 0) }}" min="0" max="30"></div>
              </div>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              {{-- Estimated Expense Components Section --}}
              <div class="d-flex justify-content-between align-items-center">
                <label class="form-label">Estimated Expense Components</label>
                <button type="button" id="copy-to-budget" class="btn btn-outline-primary btn-sm">
                  <i class="fas fa-copy"></i> Copy to Budgeted Expense
                </button>
              </div>

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
                  <input type="hidden" name="expense_components[hr_{{ $i }}][category_id]"
                    value="{{ $expenseCategories->first()->id ?? 1 }}">
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
                      class="form-control mandays-input" min="{{ $item['min'] }}" step="0.1" value="{{ $item['min'] }}"
                      data-target="hr_{{ $i }}" required>
                    <small class="text-danger error-message" style="display:none;">Must be at least {{ $item['min'] }}
                      days.</small>
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
                $travelComponents = [
                ['component' => 'Travel-Faculty Cost', 'rate' => 5000],
                ['component' => 'Travel-Sr Faculty Associate Cost', 'rate' => 3000],
                ['component' => 'Travel-Faculty Associate Cost', 'rate' => 2000],
                ['component' => 'Travel-Project Staff', 'rate' => 1000],
                ['component' => 'Travel-Consultants', 'rate' => 3000],
                ];
                @endphp

                @foreach($travelComponents as $i => $item)
                <div class="expense-component row g-3 mb-2">
                  <input type="hidden" name="expense_components[travel_{{ $i }}][category_id]"
                    value="{{ $expenseCategories->first()->id ?? 1 }}">
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
                  <input type="hidden" name="expense_components[other_{{ $i }}][category_id]"
                    value="{{ $expenseCategories->first()->id ?? 1 }}">
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

              {{-- Add custom component button for estimated --}}
              <button type="button" id="add-component" class="btn btn-secondary btn-sm mt-2">
                <i class="fas fa-plus"></i> Add Custom Component
              </button>

              <div class="mt-3">
                <strong>Total Estimated Expense: ₹<span id="total-expense">0.00</span></strong>
              </div>
              <input type="hidden" name="estimated_expense" id="estimated_expense" value="0">
            </div>

            <div class="col-md-6">
              {{-- Budgeted Expense Components Section --}}
              <div id="budget-expense-section" style="display: none;">
                <div class="row mb-3">
                  <div class="col-md-12 budget-section">
                    <h6 class="text-primary fw-bold">Budgeted Expense Components</h6>
                    <p class="text-muted">These values can be modified from the estimated expenses</p>

                    <div id="budget-expense-components-container">
                      {{-- Budgeted components will be dynamically inserted here --}}
                    </div>

                    {{-- Add custom component button for budgeted --}}
                    <button type="button" id="add-budget-component" class="btn btn-secondary btn-sm mt-2"
                      style="display: none;">
                      <i class="fas fa-plus"></i> Add Custom Budget Component
                    </button>

                    <div class="mt-3">
                      <strong>Total Budgeted Expense: ₹<span id="total-budget-expense">0.00</span></strong>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- Expense Comparison Section --}}
          <div id="expense-comparison-container" style="display: none;">
            <div class="row mb-3">
              <div class="col-md-12">
                <h6 class="text-primary fw-bold">Expense Comparison</h6>
                <div class="table-responsive">
                  <table class="table table-bordered comparison-table" id="expense-comparison-table">
                    <thead>
                      <tr>
                        <th style="width: 40%">Component Details</th>
                        <th style="width: 20%">Estimated (₹)</th>
                        <th style="width: 20%">Budgeted (₹)</th>
                        <th style="width: 20%">Difference (₹)</th>
                      </tr>
                    </thead>
                    <tbody>
                      {{-- Comparison rows will be dynamically inserted here --}}
                    </tbody>
                    <tfoot>
                      <tr class="fw-bold">
                        <td>Total</td>
                        <td>₹<span id="total-estimated-display">0.00</span></td>
                        <td>₹<span id="total-budget-display">0.00</span></td>
                        <td>₹<span id="total-difference">0.00</span></td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
            </div>
          </div>

          {{-- Revenue and Other Sections --}}
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="revenue" class="form-label">Expected Revenue (₹ Without Tax)</label>
              <input type="number" step="0.01" min="0" name="revenue" id="revenue" class="form-control"
                value="{{ old('revenue') }}" required>
              @error('revenue')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
          </div>

          {{-- Technical Details and Methodology --}}
          <div class="row mb-3">
            <div class="col-md-12">
              <label for="technical_details" class="form-label">Technical Details</label>
              <textarea name="technical_details" id="technical_details" class="form-control"
                rows="3">{{ old('technical_details') }}</textarea>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-12">
              <label for="methodology" class="form-label">Methodology</label>
              <textarea name="methodology" id="methodology" class="form-control"
                rows="3">{{ old('methodology') }}</textarea>
            </div>
          </div>

          {{-- Documents --}}
          <div class="row mb-3">
            <div class="col-md-12">
              <label for="documents" class="form-label">Supporting Documents (Optional)</label>
              <input type="file" name="documents[]" id="documents" class="form-control" multiple>
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
</div>
@endsection