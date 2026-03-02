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
    // ============ EXISTING FUNCTIONALITY ============
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

    if (tenureYears) tenureYears.addEventListener('change', calculateEndDate);
    if (tenureMonths) tenureMonths.addEventListener('change', calculateEndDate);
    if (tenureDays) tenureDays.addEventListener('change', calculateEndDate);

    // --- Function 2: Calculate Tenure based on Start & End Dates ---
    function calculateTenure() {
        if (startDateInput.value && endDateInput.value) {
            const startDate = new Date(startDateInput.value);
            const endDate = new Date(endDateInput.value);

            if (endDate < startDate) {
                if (tenureYears) tenureYears.value = 0;
                if (tenureMonths) tenureMonths.value = 0;
                if (tenureDays) tenureDays.value = 0;
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

            if (tenureYears) tenureYears.value = years;
            if (tenureMonths) tenureMonths.value = months;
            if (tenureDays) tenureDays.value = days;
        }
    }

    if (startDateInput) {
        startDateInput.addEventListener('change', function() {
            if (endDateInput && endDateInput.value) calculateTenure();
        });
    }

    if (endDateInput) {
        endDateInput.addEventListener('change', calculateTenure);
    }

    // --- Revenue Calculation ---
    function calculateRevenue() {
        if (budgetInput && expenseInput && revenueInput) {
            const budget = parseFloat(budgetInput.value) || 0;
            const expense = parseFloat(expenseInput.value) || 0;
            const revenue = budget - expense;
            revenueInput.value = revenue >= 0 ? revenue.toFixed(2) : 0;
        }
    }

    if (budgetInput) budgetInput.addEventListener('input', calculateRevenue);
    if (expenseInput) expenseInput.addEventListener('input', calculateRevenue);

    // --- Estimated Expense Components Management ---
    const container = document.getElementById('expense-components-container');
    const addButton = document.getElementById('add-component');
    const totalExpenseSpan = document.getElementById('total-expense');
    let componentCount = {{ $proposal->estimatedExpenseComponents->count() }};

    // Add new estimated component row
    if (addButton) {
        addButton.addEventListener('click', function() {
            const template = document.getElementById('custom-component-template');
            if (!template) return;

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

            // Remove functionality
            removeBtn?.addEventListener('click', function() {
                newRow.remove();
                calculateTotalEstimatedExpense();
                if (hasBudgetComponents) calculateDifferences();
            });

            // Listen to new amount inputs
            const amountInput = newRow.querySelector('.expense-amount');
            if (amountInput) {
                amountInput.addEventListener('input', function() {
                    calculateTotalEstimatedExpense();
                    if (hasBudgetComponents) calculateDifferences();
                });
            }

            container.appendChild(newRow);
            componentCount++;
        });
    }

    // Remove component functionality for custom rows
    document.querySelectorAll('#expense-components-container .remove-component').forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('.expense-component');
            // Only allow deleting if not part of HR / Travel / Others
            if (row && !['HR', 'Travel', 'Others'].includes(row.dataset.group)) {
                row.remove();
                calculateTotalEstimatedExpense();
                if (hasBudgetComponents) calculateDifferences();
            }
        });
    });

    // Calculate total estimated expense
    function calculateTotalEstimatedExpense() {
        let total = 0;
        document.querySelectorAll('#expense-components-container .expense-amount').forEach(input => {
            total += parseFloat(input.value) || 0;
        });

        if (totalExpenseSpan) {
            totalExpenseSpan.textContent = total.toFixed(2);
        }
        if (expenseInput) {
            expenseInput.value = total.toFixed(2);
        }

        calculateRevenue();
        return total;
    }

    // Calculate total budgeted expense
    function calculateTotalBudgetedExpense() {
        let total = 0;
        document.querySelectorAll('#budget-expense-components-container .budget-amount').forEach(input => {
            total += parseFloat(input.value) || 0;
        });

        const totalBudgetExpenseSpan = document.getElementById('total-budget-expense');
        if (totalBudgetExpenseSpan) {
            totalBudgetExpenseSpan.textContent = total.toFixed(2);
        }

        return total;
    }

    // Add event listeners to all amount inputs
    document.querySelectorAll('#expense-components-container .expense-amount').forEach(input => {
        input.addEventListener('input', function() {
            calculateTotalEstimatedExpense();
            if (hasBudgetComponents) calculateDifferences();
        });
    });

    // Mandays × Rate auto-calc for estimated
    document.querySelectorAll('.mandays-input').forEach(input => {
        input.addEventListener('input', function() {
            const target = this.dataset.target;
            const rateField = document.querySelector(`.rate-input[data-target="${target}"]`);
            const amountField = document.getElementById(`amount_${target}`);

            const rate = parseFloat(rateField?.value) || 0;
            const mandays = parseFloat(this.value) || 0;
            const amount = mandays * rate;

            if (amountField) {
                amountField.value = amount.toFixed(2);
            }

            const min = parseFloat(this.getAttribute('min')) || 0;
            const value = parseFloat(this.value);
            if (isNaN(value)) return;

            const error = this.parentElement.querySelector('.error-message');

            if (value < min) {
                this.classList.add('is-invalid');
                if (error) error.style.display = 'block';
            } else {
                this.classList.remove('is-invalid');
                if (error) error.style.display = 'none';
            }

            calculateTotalEstimatedExpense();
            if (hasBudgetComponents) calculateDifferences();
        });
    });

    // ============ BUDGETED EXPENSE FUNCTIONALITY ============
    const copyToBudgetBtn = document.getElementById('copy-to-budget');
    const budgetExpenseContainer = document.getElementById('budget-expense-components-container');
    const comparisonContainer = document.getElementById('expense-comparison-container');
    const copyToBudgetInput = document.getElementById('copy_to_budget');
    const addBudgetComponentBtn = document.getElementById('add-budget-component');

    let hasBudgetComponents = {{ $proposal->budgetedExpenseComponents->count() > 0 ? 'true' : 'false' }};
    let budgetComponentCount = {{ $proposal->budgetedExpenseComponents->where('group_name', 'Custom')->count() }};

    // Show budget section if budget components exist
    if (hasBudgetComponents) {
        const budgetSection = document.getElementById('budget-expense-section');
        if (budgetSection) budgetSection.style.display = 'block';
        if (comparisonContainer) comparisonContainer.style.display = 'block';
        if (addBudgetComponentBtn) addBudgetComponentBtn.style.display = 'block';
        if (copyToBudgetInput) copyToBudgetInput.value = '1';
        if (copyToBudgetBtn) {
            copyToBudgetBtn.disabled = true;
            copyToBudgetBtn.innerHTML = '<i class="fas fa-check"></i> Already Copied';
        }
    }

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
                        updateAllCalculations();
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
                input.addEventListener('input', updateAllCalculations);
            });

            // Add event listeners to budget mandays inputs
            document.querySelectorAll('.budget-mandays').forEach(input => {
                input.addEventListener('input', function() {
                    calculateBudgetAmount(this);
                    updateAllCalculations();
                });
            });

            updateAllCalculations();
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
                    updateAllCalculations();
                });
            }

            // Add input listener for amount
            const amountInput = newRow.querySelector('.budget-amount');
            if (amountInput) {
                amountInput.addEventListener('input', updateAllCalculations);
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

    // NEW: Function to update all calculations at once
    function updateAllCalculations() {
        calculateTotalEstimatedExpense();
        calculateTotalBudgetedExpense();
        calculateDifferences();
        calculateRevenue();
    }

    // Calculate differences between estimated and budgeted
    function calculateDifferences() {
        if (!hasBudgetComponents) return;

        const estimatedComponents = document.querySelectorAll('#expense-components-container .expense-component');
        const budgetComponents = document.querySelectorAll('#budget-expense-components-container .budget-component');

        let totalEstimated = calculateTotalEstimatedExpense();
        let totalBudgeted = calculateTotalBudgetedExpense();

        // Update comparison table
        updateComparisonTable(totalEstimated, totalBudgeted);
    }

    function updateComparisonTable(totalEstimated, totalBudgeted) {
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

        // Update totals in table footer
        const totalEstimatedDisplay = document.getElementById('total-estimated-display');
        const totalBudgetDisplay = document.getElementById('total-budget-display');
        const totalDifference = document.getElementById('total-difference');

        if (totalEstimatedDisplay) {
            totalEstimatedDisplay.textContent = totalEstimated.toFixed(2);
        }

        if (totalBudgetDisplay) {
            totalBudgetDisplay.textContent = totalBudgeted.toFixed(2);
        }

        if (totalDifference) {
            const difference = totalBudgeted - totalEstimated;
            totalDifference.textContent = difference.toFixed(2);
            totalDifference.className = difference >= 0 ? 'difference-positive' : 'difference-negative';
        }
    }

    // Add event listeners to existing budget amount inputs
    document.querySelectorAll('.budget-amount').forEach(input => {
        input.addEventListener('input', updateAllCalculations);
    });

    // Add event listeners to existing budget mandays inputs
    document.querySelectorAll('.budget-mandays').forEach(input => {
        input.addEventListener('input', function() {
            calculateBudgetAmount(this);
            updateAllCalculations();
        });
    });

    // Add event listeners to existing budget remove buttons
    document.querySelectorAll('#budget-expense-components-container .remove-component').forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('.expense-component');
            if (row) {
                row.remove();
                updateAllCalculations();
            }
        });
    });

    // Initial calculations
    calculateTotalEstimatedExpense();
    if (hasBudgetComponents) {
        updateAllCalculations();
    }
});
</script>
@endsection

@section('header', 'Edit Proposal')

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
        <h5 class="card-title">Edit Proposal for Requirement: {{ $proposal->requirement->temp_no }}</h5>
      </div>
      <div class="card-body">
        {{-- Hidden template for new custom estimated component --}}
        <div id="custom-component-template" class="expense-component row g-3 mb-2 d-none">
          <input type="hidden" name="expense_components[0][group]" value="Custom">
          <div class="col-md-4">
            <select name="expense_components[0][category_id]" class="form-select expense-category">
              <option value="">Select Category</option>
              @foreach($expenseCategories as $category)
              <option value="{{ $category->id }}">{{ $category->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4">
            <input type="text" name="expense_components[0][component]" class="form-control"
              placeholder="Component name">
          </div>
          <div class="col-md-3">
            <input type="number" step="0.01" min="0" name="expense_components[0][amount]"
              class="form-control expense-amount" placeholder="Amount (₹)">
          </div>
          <div class="col-md-1">
            <button type="button" class="btn btn-danger remove-component" style="display:none;">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </div>

        {{-- Hidden template for new custom budget component --}}
        <div id="budget-custom-component-template" class="expense-component row g-3 mb-2 d-none">
          <input type="hidden" name="budgeted_expense_components[0][group]" value="Custom">
          <div class="col-md-4">
            <select name="budgeted_expense_components[0][category_id]" class="form-select expense-category">
              <option value="">Select Category</option>
              @foreach($expenseCategories as $category)
              <option value="{{ $category->id }}">{{ $category->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4">
            <input type="text" name="budgeted_expense_components[0][component]" class="form-control"
              placeholder="Component name">
          </div>
          <div class="col-md-3">
            <input type="number" step="0.01" min="0" name="budgeted_expense_components[0][amount]"
              class="form-control budget-amount" placeholder="Amount (₹)">
          </div>
          <div class="col-md-1">
            <button type="button" class="btn btn-danger remove-component" style="display:none;">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </div>

        <form action="{{ route('pms.proposals.update', $proposal->id) }}" method="POST" enctype="multipart/form-data">
          @csrf
          @method('PUT')
          <input type="hidden" name="copy_to_budget" id="copy_to_budget"
            value="{{ $proposal->budgetedExpenseComponents->count() > 0 ? '1' : '0' }}">

          {{-- Basic Information Section --}}
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="budget" class="form-label">Budget (₹ Without Tax)</label>
              <input type="number" step="0.01" min="0" name="budget" id="budget" class="form-control"
                value="{{ old('budget', $proposal->budget) }}" required>
              @error('budget')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
              <label for="expected_start_date" class="form-label">Expected Start Date</label>
              <input type="date" name="expected_start_date" id="expected_start_date" class="form-control"
                value="{{ old('expected_start_date', $proposal->expected_start_date ? $proposal->expected_start_date->format('Y-m-d') : '') }}">
              @error('expected_start_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label for="expected_end_date" class="form-label">Expected End Date</label>
              <input type="date" name="expected_end_date" id="expected_end_date" class="form-control"
                value="{{ old('expected_end_date',$proposal->expected_end_date ? $proposal->expected_end_date->format('Y-m-d') : '') }}">
              @error('expected_end_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
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
            </div>
          </div>

          {{-- Expense Components Section --}}
          <div class="row mb-3">
            <div class="col-md-6">
              <div class="d-flex justify-content-between align-items-center">
                <label class="form-label fw-bold">Estimated Expense Components</label>
                @if($proposal->budgetedExpenseComponents->count() == 0)
                <button type="button" id="copy-to-budget" class="btn btn-outline-primary btn-sm">
                  <i class="fas fa-copy"></i> Copy to Budgeted Expense
                </button>
                @else
                <button type="button" id="copy-to-budget" class="btn btn-outline-primary btn-sm" disabled>
                  <i class="fas fa-check"></i> Already Copied
                </button>
                @endif
              </div>

              <div id="expense-components-container">
                {{-- HR GROUP --}}
                <h6 class="mt-3 mb-2 text-primary fw-bold">HR</h6>
                @php
                $hrComponents = [
                ['component' => 'Manpower-Faculty Cost', 'rate' => 14000, 'min'=>0.5],
                ['component' => 'Manpower-Sr Faculty Associate Cost', 'rate' => 8000,'min'=>0],
                ['component' => 'Manpower-Faculty Associate Cost', 'rate' => 6000,'min'=>0],
                ['component' => 'Manpower-Project Staff', 'rate' => 3200,'min'=>0],
                ['component' => 'Manpower-Consultants', 'rate' => 8000,'min'=>0],
                ];

                $existingEstimatedHr = $proposal->estimatedExpenseComponents->where('group_name',
                'HR')->keyBy('component');
                @endphp

                @foreach($hrComponents as $i => $item)
                @php $existing = $existingEstimatedHr->get($item['component']); @endphp
                <div class="expense-component row g-3 mb-2">
                  <input type="hidden" name="expense_components[hr_{{ $i }}][category_id]"
                    value="{{ $existing->expense_category_id ?? $expenseCategories->first()->id ?? 1 }}">
                  <input type="hidden" name="expense_components[hr_{{ $i }}][group]" value="HR">
                  <input type="hidden" name="expense_components[hr_{{ $i }}][component]"
                    value="{{ $item['component'] }}">

                  <div class="col-md-3">
                    <input type="text" class="form-control" value="{{ $item['component'] }}" readonly>
                  </div>

                  <div class="col-md-2">
                    <input type="number" class="form-control mandays-input"
                      name="expense_components[hr_{{ $i }}][mandays]" value="{{ $existing->mandays ?? $item['min'] }}"
                      step="0.1" data-target="hr_{{ $i }}" min="{{ $item['min'] }}">
                    <small class="text-danger error-message" style="display:none;">
                      Must be at least {{ $item['min'] }} days.
                    </small>
                  </div>

                  <div class="col-md-2">
                    <input type="number" class="form-control rate-input" name="expense_components[hr_{{ $i }}][rate]"
                      value="{{ $existing->rate ?? $item['rate'] }}" data-target="hr_{{ $i }}" readonly>
                  </div>

                  <div class="col-md-2">
                    <input type="number" class="form-control expense-amount"
                      name="expense_components[hr_{{ $i }}][amount]" id="amount_hr_{{ $i }}"
                      value="{{ $existing->amount ?? 0 }}" readonly>
                  </div>
                </div>
                @endforeach

                {{-- TRAVEL GROUP --}}
                <h6 class="mt-3 mb-2 text-primary fw-bold">Travel</h6>
                @php
                $travelComponents = [
                'Travel-Faculty Cost',
                'Travel-Sr Faculty Associate Cost',
                'Travel-Faculty Associate Cost',
                'Travel-Project Staff',
                'Travel-Consultants',
                ];
                $existingEstimatedTravel = $proposal->estimatedExpenseComponents->where('group_name',
                'Travel')->keyBy('component');
                @endphp

                @foreach($travelComponents as $i => $component)
                @php $existing = $existingEstimatedTravel->get($component); @endphp
                <div class="expense-component row g-3 mb-2">
                  <input type="hidden" name="expense_components[travel_{{ $i }}][category_id]"
                    value="{{ $existing->expense_category_id ?? $expenseCategories->first()->id ?? 1 }}">
                  <input type="hidden" name="expense_components[travel_{{ $i }}][group]" value="Travel">
                  <input type="hidden" name="expense_components[travel_{{ $i }}][component]" value="{{ $component }}">

                  <div class="col-md-3">
                    <input type="text" class="form-control" value="{{ $component }}" readonly>
                  </div>

                  <div class="col-md-3">
                    <input type="number" class="form-control expense-amount"
                      name="expense_components[travel_{{ $i }}][amount]" value="{{ $existing->amount ?? 0 }}"
                      placeholder="Amount (₹)">
                  </div>
                </div>
                @endforeach

                {{-- OTHERS GROUP --}}
                <h6 class="mt-3 mb-2 text-primary fw-bold">Others</h6>
                @php
                $otherComponents = [
                'Laptop',
                'Reports',
                'Printing',
                'Stationary',
                ];
                $existingEstimatedOthers = $proposal->estimatedExpenseComponents->where('group_name',
                'Others')->keyBy('component');
                @endphp

                @foreach($otherComponents as $i => $component)
                @php $existing = $existingEstimatedOthers->get($component); @endphp
                <div class="expense-component row g-3 mb-2">
                  <input type="hidden" name="expense_components[other_{{ $i }}][category_id]"
                    value="{{ $existing->expense_category_id ?? $expenseCategories->first()->id ?? 1 }}">
                  <input type="hidden" name="expense_components[other_{{ $i }}][group]" value="Others">
                  <input type="hidden" name="expense_components[other_{{ $i }}][component]" value="{{ $component }}">

                  <div class="col-md-3">
                    <input type="text" class="form-control" value="{{ $component }}" readonly>
                  </div>

                  <div class="col-md-3">
                    <input type="number" class="form-control expense-amount"
                      name="expense_components[other_{{ $i }}][amount]" value="{{ $existing->amount ?? 0 }}"
                      placeholder="Amount (₹)">
                  </div>
                </div>
                @endforeach

                {{-- CUSTOM ESTIMATED COMPONENTS --}}
                @php
                $customEstimatedComponents = $proposal->estimatedExpenseComponents->where('group_name', 'Custom');
                @endphp

                @if($customEstimatedComponents->count())
                <h6 class="mt-3 mb-2 text-primary fw-bold">Custom Components</h6>
                @endif

                @foreach($customEstimatedComponents as $i => $custom)
                <div class="expense-component row g-3 mb-2">
                  <input type="hidden" name="expense_components[custom_{{ $i }}][group]" value="Custom">
                  <div class="col-md-4">
                    <select name="expense_components[custom_{{ $i }}][category_id]"
                      class="form-select expense-category">
                      <option value="">Select Category</option>
                      @foreach($expenseCategories as $category)
                      <option value="{{ $category->id }}" {{ $custom->expense_category_id == $category->id ? 'selected'
                        : '' }}>
                        {{ $category->name }}
                      </option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-4">
                    <input type="text" name="expense_components[custom_{{ $i }}][component]" class="form-control"
                      value="{{ $custom->component }}">
                  </div>
                  <div class="col-md-3">
                    <input type="number" step="0.01" min="0" name="expense_components[custom_{{ $i }}][amount]"
                      class="form-control expense-amount" value="{{ $custom->amount }}">
                  </div>
                  <div class="col-md-1">
                    <button type="button" class="btn btn-danger remove-component"><i class="fas fa-trash"></i></button>
                  </div>
                </div>
                @endforeach
              </div>

              {{-- Add new custom estimated component button --}}
              <button type="button" id="add-component" class="btn btn-secondary btn-sm mt-2">
                <i class="fas fa-plus"></i> Add Custom Component
              </button>

              <div class="mt-3">
                <strong>Total Estimated Expense: ₹<span id="total-expense">{{
                    number_format($proposal->estimated_expense, 2) }}</span></strong>
              </div>
              <input type="hidden" name="estimated_expense" id="estimated_expense"
                value="{{ $proposal->estimated_expense }}">
            </div>

            <div class="col-md-6">
              {{-- Budgeted Expense Components Section --}}
              <div id="budget-expense-section"
                style="display: {{ $proposal->budgetedExpenseComponents->count() > 0 ? 'block' : 'none' }};">
                <div class="budget-section">
                  <h6 class="text-primary fw-bold">Budgeted Expense Components</h6>
                  <p class="text-muted">These values can be modified from the estimated expenses</p>

                  <div id="budget-expense-components-container">
                    {{-- Budgeted components will be dynamically inserted here --}}
                    @php
                    $existingBudgetedHr = $proposal->budgetedExpenseComponents->where('group_name',
                    'HR')->keyBy('component');
                    $existingBudgetedTravel = $proposal->budgetedExpenseComponents->where('group_name',
                    'Travel')->keyBy('component');
                    $existingBudgetedOthers = $proposal->budgetedExpenseComponents->where('group_name',
                    'Others')->keyBy('component');
                    $existingBudgetedCustom = $proposal->budgetedExpenseComponents->where('group_name', 'Custom');
                    @endphp

                    {{-- Budgeted HR Components --}}
                    @foreach($hrComponents as $i => $item)
                    @php $existing = $existingBudgetedHr->get($item['component']); @endphp
                    @if($existing || $proposal->budgetedExpenseComponents->count() > 0)
                    <div class="budget-component expense-component row g-3 mb-2">
                      <input type="hidden" name="budgeted_expense_components[hr_{{ $i }}][category_id]"
                        value="{{ $existing->expense_category_id ?? $expenseCategories->first()->id ?? 1 }}">
                      <input type="hidden" name="budgeted_expense_components[hr_{{ $i }}][group]" value="HR">
                      <input type="hidden" name="budgeted_expense_components[hr_{{ $i }}][component]"
                        value="{{ $item['component'] }}">

                      <div class="col-md-3">
                        <input type="text" class="form-control" value="{{ $item['component'] }}" readonly>
                      </div>

                      <div class="col-md-2">
                        <input type="number" class="form-control budget-mandays mandays-input"
                          name="budgeted_expense_components[hr_{{ $i }}][mandays]"
                          value="{{ $existing->mandays ?? ($existingEstimatedHr->get($item['component'])->mandays ?? $item['min']) }}"
                          step="0.1" data-target="hr_{{ $i }}" min="{{ $item['min'] }}">
                        <small class="text-danger error-message" style="display:none;">
                          Must be at least {{ $item['min'] }} days.
                        </small>
                      </div>

                      <div class="col-md-2">
                        <input type="number" class="form-control rate-input"
                          name="budgeted_expense_components[hr_{{ $i }}][rate]"
                          value="{{ $existing->rate ?? $item['rate'] }}" data-target="hr_{{ $i }}" readonly>
                      </div>

                      <div class="col-md-2">
                        <input type="number" class="form-control budget-amount"
                          name="budgeted_expense_components[hr_{{ $i }}][amount]" id="budget_amount_hr_{{ $i }}"
                          value="{{ $existing->amount ?? 0 }}">
                      </div>
                    </div>
                    @endif
                    @endforeach

                    {{-- Budgeted Travel Components --}}
                    @foreach($travelComponents as $i => $component)
                    @php $existing = $existingBudgetedTravel->get($component); @endphp
                    @if($existing || $proposal->budgetedExpenseComponents->count() > 0)
                    <div class="budget-component expense-component row g-3 mb-2">
                      <input type="hidden" name="budgeted_expense_components[travel_{{ $i }}][category_id]"
                        value="{{ $existing->expense_category_id ?? $expenseCategories->first()->id ?? 1 }}">
                      <input type="hidden" name="budgeted_expense_components[travel_{{ $i }}][group]" value="Travel">
                      <input type="hidden" name="budgeted_expense_components[travel_{{ $i }}][component]"
                        value="{{ $component }}">

                      <div class="col-md-3">
                        <input type="text" class="form-control" value="{{ $component }}" readonly>
                      </div>

                      <div class="col-md-3">
                        <input type="number" class="form-control budget-amount"
                          name="budgeted_expense_components[travel_{{ $i }}][amount]"
                          value="{{ $existing->amount ?? 0 }}" placeholder="Amount (₹)">
                      </div>
                    </div>
                    @endif
                    @endforeach

                    {{-- Budgeted Others Components --}}
                    @foreach($otherComponents as $i => $component)
                    @php $existing = $existingBudgetedOthers->get($component); @endphp
                    @if($existing || $proposal->budgetedExpenseComponents->count() > 0)
                    <div class="budget-component expense-component row g-3 mb-2">
                      <input type="hidden" name="budgeted_expense_components[other_{{ $i }}][category_id]"
                        value="{{ $existing->expense_category_id ?? $expenseCategories->first()->id ?? 1 }}">
                      <input type="hidden" name="budgeted_expense_components[other_{{ $i }}][group]" value="Others">
                      <input type="hidden" name="budgeted_expense_components[other_{{ $i }}][component]"
                        value="{{ $component }}">

                      <div class="col-md-3">
                        <input type="text" class="form-control" value="{{ $component }}" readonly>
                      </div>

                      <div class="col-md-3">
                        <input type="number" class="form-control budget-amount"
                          name="budgeted_expense_components[other_{{ $i }}][amount]"
                          value="{{ $existing->amount ?? 0 }}" placeholder="Amount (₹)">
                      </div>
                    </div>
                    @endif
                    @endforeach

                    {{-- Budgeted Custom Components --}}
                    @foreach($existingBudgetedCustom as $i => $custom)
                    <div class="budget-component expense-component row g-3 mb-2">
                      <input type="hidden" name="budgeted_expense_components[custom_budget_{{ $i }}][group]"
                        value="Custom">
                      <div class="col-md-4">
                        <select name="budgeted_expense_components[custom_budget_{{ $i }}][category_id]"
                          class="form-select expense-category">
                          <option value="">Select Category</option>
                          @foreach($expenseCategories as $category)
                          <option value="{{ $category->id }}" {{ $custom->expense_category_id == $category->id ?
                            'selected' : '' }}>
                            {{ $category->name }}
                          </option>
                          @endforeach
                        </select>
                      </div>
                      <div class="col-md-4">
                        <input type="text" name="budgeted_expense_components[custom_budget_{{ $i }}][component]"
                          class="form-control" value="{{ $custom->component }}">
                      </div>
                      <div class="col-md-3">
                        <input type="number" step="0.01" min="0"
                          name="budgeted_expense_components[custom_budget_{{ $i }}][amount]"
                          class="form-control budget-amount" value="{{ $custom->amount }}">
                      </div>
                      <div class="col-md-1">
                        <button type="button" class="btn btn-danger remove-component"><i
                            class="fas fa-trash"></i></button>
                      </div>
                    </div>
                    @endforeach
                  </div>

                  {{-- Add custom budget component button --}}
                  <button type="button" id="add-budget-component" class="btn btn-secondary btn-sm mt-2"
                    style="display: {{ $proposal->budgetedExpenseComponents->count() > 0 ? 'block' : 'none' }};">
                    <i class="fas fa-plus"></i> Add Custom Budget Component
                  </button>

                  <div class="mt-3">
                    <strong>Total Budgeted Expense: ₹<span id="total-budget-expense">{{
                        number_format($proposal->total_budgeted_expense, 2) }}</span></strong>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- Expense Comparison Section --}}
          <div id="expense-comparison-container"
            style="display: {{ $proposal->budgetedExpenseComponents->count() > 0 ? 'block' : 'none' }};">
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
                        <td>₹<span id="total-estimated-display">{{ number_format($proposal->estimated_expense, 2)
                            }}</span></td>
                        <td>₹<span id="total-budget-display">{{ number_format($proposal->total_budgeted_expense, 2)
                            }}</span></td>
                        <td>₹<span id="total-difference">{{ number_format($proposal->total_budgeted_expense -
                            $proposal->estimated_expense, 2) }}</span></td>
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
                value="{{ old('revenue', $proposal->revenue) }}" required>
              @error('revenue')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-12">
              <label for="technical_details" class="form-label">Technical Details</label>
              <textarea name="technical_details" id="technical_details" class="form-control"
                rows="3">{{ old('technical_details', $proposal->technical_details) }}</textarea>
              @error('technical_details')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-12">
              <label for="methodology" class="form-label">Methodology</label>
              <textarea name="methodology" id="methodology" class="form-control"
                rows="3">{{ old('methodology', $proposal->methodology) }}</textarea>
              @error('methodology')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-12">
              <label for="documents" class="form-label">Additional Documents (Optional)</label>
              <input type="file" name="documents[]" id="documents" class="form-control" multiple>
              @error('documents')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
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
</div>

<div class="row mt-4">
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