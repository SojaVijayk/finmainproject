@extends('layouts/layoutMaster')

@section('title', 'Create Project')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/spinkit/spinkit.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
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

  .yearly-budget-row {
    background-color: #f0f9ff;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 10px;
  }

  .copy-buttons {
    margin-bottom: 15px;
  }

  .copy-buttons .btn-group {
    display: flex;
    gap: 10px;
  }

  .budgeted-section {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #3b82f6;
  }

  .estimated-section {
    background-color: #f0f9ff;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #10b981;
  }

  .yearly-estimate-section {
    margin-bottom: 20px;
    padding: 15px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
  }

  .yearly-estimate-section h6 {
    color: #374151;
    border-bottom: 2px solid #d1d5db;
    padding-bottom: 8px;
    margin-bottom: 15px;
  }

  .copy-checkbox {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
  }

  .copy-checkbox label {
    margin-left: 8px;
    font-weight: 500;
  }

  .yearly-revenue-section {
    margin-top: 15px;
    border-top: 1px solid #dee2e6;
    padding-top: 15px;
  }

  .yearly-revenue-section .card {
    border: 1px solid #e3e6f0;
  }

  .yearly-revenue-section h6 {
    font-size: 1.1rem;
    margin-top: 5px;
  }

  .readonly-input {
    background-color: #f8f9fa;
    border-color: #e9ecef;
    cursor: not-allowed;
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
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
@endsection

@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Initialize select2
    $('.select2').select2({ width: 'resolve' });

    // Set end date minimum based on start date
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');

    if (startDateInput && endDateInput) {
        startDateInput.addEventListener('change', function() {
            endDateInput.min = this.value;
            if (endDateInput.value && endDateInput.value < this.value) {
                endDateInput.value = '';
            }
        });
    }

    // ============ GLOBAL VARIABLES ============
    let yearlyBudgetCount = 0;
    let selectedFinancialYearIds = new Set();
    let teamMembers = [];

    // Store financial year data
    const financialYearData = {};
    @foreach($financialYears as $year)
    financialYearData[{{ $year->id }}] = {
        name: "{{ $year->display_name }}",
        start_date: "{{ $year->start_date->format('Y-m-d') }}",
        end_date: "{{ $year->end_date->format('Y-m-d') }}",
        short_name: "{{ $year->short_name }}"
    };
    @endforeach

    // Store existing yearly estimate sections data
    const existingYearlyEstimates = {};

    // ============ COPY FROM PROPOSAL FUNCTIONALITY ============
    const copyEstimatedCheckbox = document.getElementById('copy_estimated_expenses');
    const copyBudgetedCheckbox = document.getElementById('copy_budgeted_expenses');
    const useCustomCheckbox = document.getElementById('use_custom_expenses');

    // Auto-load proposal budgeted expenses
    function autoLoadProposalBudgetedExpenses() {
        if (copyBudgetedCheckbox && copyBudgetedCheckbox.checked) {
            loadProposalBudgetedData();
        }
    }

    // Load proposal budgeted data
    function loadProposalBudgetedData() {
        const container = document.getElementById('budgeted-expense-components-container');
        if (!container) return;

        container.innerHTML = '';

        // Check if we have proposal budgeted components
        const hasProposalBudgeted = {{ $proposalBudgetedComponents && count($proposalBudgetedComponents) > 0 ? 'true' : 'false' }};

        if (hasProposalBudgeted) {
            // Load from PHP data - using PHP to generate HTML string
            <?php
            if($proposalBudgetedComponents && count($proposalBudgetedComponents) > 0) {
                $budgetedHtml = '';
                foreach($proposalBudgetedComponents as $groupName => $components) {
                    if($groupName !== 'Custom') {
                        $budgetedHtml .= "<h6 class=\"mt-3 mb-2 text-primary fw-bold\">" . e($groupName) . "</h6>";
                    }

                    foreach($components as $index => $component) {
                        $budgetedHtml .= "<div class=\"expense-component row g-3 mb-2 budget-component\">";

                        // Hidden inputs
                        $budgetedHtml .= "<input type=\"hidden\" name=\"budgeted_expense_components[" . e($groupName) . "_" . e($index) . "][category_id]\" value=\"" . e($component->expense_category_id) . "\">";
                        $budgetedHtml .= "<input type=\"hidden\" name=\"budgeted_expense_components[" . e($groupName) . "_" . e($index) . "][group]\" value=\"" . e($groupName) . "\">";
                        $budgetedHtml .= "<input type=\"hidden\" name=\"budgeted_expense_components[" . e($groupName) . "_" . e($index) . "][component]\" value=\"" . e($component->component) . "\">";

                        // Component field
                        $budgetedHtml .= "<div class=\"col-md-4\">";
                        $budgetedHtml .= "<label class=\"form-label\">Component</label>";
                        $budgetedHtml .= "<input type=\"text\" class=\"form-control\" value=\"" . e($component->component) . "\" readonly>";
                        if($groupName === 'Custom') {
                            $budgetedHtml .= "<small class=\"text-muted\">Custom Component</small>";
                        }
                        $budgetedHtml .= "</div>";

                        // Conditional fields based on group
                        if($groupName === 'HR') {
                            // HR group - with mandays and rate (rate readonly)
                            $budgetedHtml .= "<div class=\"col-md-2\">";
                            $budgetedHtml .= "<label class=\"form-label\">Persondays</label>";
                            $budgetedHtml .= "<input type=\"number\" name=\"budgeted_expense_components[" . e($groupName) . "_" . e($index) . "][mandays]\" class=\"form-control budgeted-mandays-input\" value=\"" . e($component->mandays) . "\" min=\"0\" step=\"0.1\" data-target=\"" . e($groupName . '_' . $index) . "\">";
                            $budgetedHtml .= "</div>";

                            $budgetedHtml .= "<div class=\"col-md-2\">";
                            $budgetedHtml .= "<label class=\"form-label\">Rate (₹)</label>";
                            $budgetedHtml .= "<input type=\"number\" name=\"budgeted_expense_components[" . e($groupName) . "_" . e($index) . "][rate]\" class=\"form-control budgeted-rate-input\" value=\"" . e($component->rate) . "\" data-target=\"" . e($groupName . '_' . $index) . "\" readonly>";
                            $budgetedHtml .= "</div>";
                        } else if($groupName === 'Custom') {
                            // Custom group - with editable mandays and rate
                            $budgetedHtml .= "<div class=\"col-md-2\">";
                            $budgetedHtml .= "<label class=\"form-label\">Persondays</label>";
                            $budgetedHtml .= "<input type=\"number\" name=\"budgeted_expense_components[" . e($groupName) . "_" . e($index) . "][mandays]\" class=\"form-control budgeted-mandays-input\" value=\"" . e($component->mandays) . "\" min=\"0\" step=\"0.1\" data-target=\"" . e($groupName . '_' . $index) . "\">";
                            $budgetedHtml .= "</div>";

                            $budgetedHtml .= "<div class=\"col-md-2\">";
                            $budgetedHtml .= "<label class=\"form-label\">Rate (₹)</label>";
                            $budgetedHtml .= "<input type=\"number\" name=\"budgeted_expense_components[" . e($groupName) . "_" . e($index) . "][rate]\" class=\"form-control budgeted-rate-input\" value=\"" . e($component->rate) . "\" data-target=\"" . e($groupName . '_' . $index) . "\">";
                            $budgetedHtml .= "</div>";
                        } else {
                            // Other groups - description field
                            $budgetedHtml .= "<div class=\"col-md-4\">";
                            $budgetedHtml .= "<label class=\"form-label\">Description</label>";
                            $budgetedHtml .= "<input type=\"text\" name=\"budgeted_expense_components[" . e($groupName) . "_" . e($index) . "][description]\" class=\"form-control\" value=\"" . e($component->description ?? '') . "\">";
                            $budgetedHtml .= "</div>";
                        }

                        // Amount field
                        $budgetedHtml .= "<div class=\"col-md-2\">";
                        $budgetedHtml .= "<label class=\"form-label\">Amount (₹)</label>";
                        $budgetedHtml .= "<input type=\"number\" step=\"0.01\" min=\"0\" name=\"budgeted_expense_components[" . e($groupName) . "_" . e($index) . "][amount]\" class=\"form-control budgeted-expense-amount\" id=\"budgeted_amount_" . e($groupName . '_' . $index) . "\" value=\"" . e($component->amount) . "\">";
                        $budgetedHtml .= "</div>";

                        // Remove button (only for Custom group)
                        $budgetedHtml .= "<div class=\"col-md-1 d-flex align-items-end\">";
                        if($groupName === 'Custom') {
                            $budgetedHtml .= "<button type=\"button\" class=\"btn btn-danger btn-sm remove-budget-component\"><i class=\"fas fa-trash\"></i></button>";
                        }
                        $budgetedHtml .= "</div>";

                        $budgetedHtml .= "</div>"; // Close row
                    }
                }
                echo "container.innerHTML = `" . addslashes($budgetedHtml) . "`;";
            }
            ?>

            // Show budget section
            const budgetSection = document.getElementById('project-budgeted-expense-section');
            if (budgetSection) budgetSection.style.display = 'block';

            const addBudgetComponentBtn = document.getElementById('add-budget-component');
            if (addBudgetComponentBtn) {
                addBudgetComponentBtn.style.display = 'block';
                // Remove existing event listener and add new one
                addBudgetComponentBtn.replaceWith(addBudgetComponentBtn.cloneNode(true));
                document.getElementById('add-budget-component').addEventListener('click', addBudgetComponent);
            }

            // Add event listeners to loaded budget components
            setTimeout(() => {
                document.querySelectorAll('.budgeted-expense-amount').forEach(input => {
                    input.addEventListener('input', calculateAllTotals);
                });

                document.querySelectorAll('.budgeted-mandays-input').forEach(input => {
                    input.addEventListener('input', function() {
                        calculateBudgetAmount(this);
                        calculateAllTotals();
                    });
                });

                document.querySelectorAll('.remove-budget-component').forEach(btn => {
                    btn.addEventListener('click', function() {
                        this.closest('.expense-component').remove();
                        calculateAllTotals();
                    });
                });
            }, 100);
        } else {
            // Show empty state
            const message = document.createElement('div');
            message.className = 'alert alert-warning';
            message.innerHTML = `
                <i class="fas fa-exclamation-triangle me-2"></i>
                No budgeted expenses found in proposal.
            `;
            container.appendChild(message);

            const addBudgetComponentBtn = document.getElementById('add-budget-component');
            if (addBudgetComponentBtn) {
                addBudgetComponentBtn.style.display = 'block';
                // Remove existing event listener and add new one
                addBudgetComponentBtn.replaceWith(addBudgetComponentBtn.cloneNode(true));
                document.getElementById('add-budget-component').addEventListener('click', addBudgetComponent);
            }
        }

        calculateAllTotals();
    }

    // Add budget component function
    function addBudgetComponent() {
        console.log('Adding budget custom component...');
        const container = document.getElementById('budgeted-expense-components-container');
        if (!container) {
            console.error('Budget component container not found');
            return;
        }

        const template = document.getElementById('budget-custom-component-template');
        if (!template) {
            console.error('Budget custom component template not found');
            return;
        }

        const newComponent = template.cloneNode(true);
        newComponent.classList.remove('d-none');

        // Update index - count only custom components
        const customComponents = container.querySelectorAll('.expense-component input[name*="budgeted_expense_components"][value="Custom"][type="hidden"]');
        const componentCount = customComponents.length;

        // Update all names with new index
        newComponent.querySelectorAll('[name]').forEach(el => {
            const name = el.getAttribute('name');
            if (name) {
                el.setAttribute('name', name.replace('[0]', `[Custom_${componentCount}]`));
            }
        });

        // Add event listeners
        const amountInput = newComponent.querySelector('.budgeted-expense-amount');
        if (amountInput) {
            amountInput.addEventListener('input', calculateAllTotals);
        }

        const removeBtn = newComponent.querySelector('.remove-budget-component');
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                newComponent.remove();
                calculateAllTotals();
            });
        }

        container.appendChild(newComponent);

        // Focus on the first input
        setTimeout(() => {
            const firstInput = newComponent.querySelector('input, select');
            if (firstInput) firstInput.focus();
        }, 100);

        calculateAllTotals();
    }

    // Toggle visibility of expense sections based on checkbox
    function toggleExpenseSections() {
        const estimatedSection = document.getElementById('project-estimated-expense-section');
        const budgetedSection = document.getElementById('project-budgeted-expense-section');

        if (useCustomCheckbox.checked) {
            // Show both sections for custom entry
            if (estimatedSection) estimatedSection.style.display = 'block';
            if (budgetedSection) budgetedSection.style.display = 'block';

            // Clear and show empty budget components section
            const container = document.getElementById('budgeted-expense-components-container');
            if (container) {
                container.innerHTML = '<div class="alert alert-info">Enter custom budget components below.</div>';
                const addBudgetComponentBtn = document.getElementById('add-budget-component');
                if (addBudgetComponentBtn) {
                    addBudgetComponentBtn.style.display = 'block';
                    // Remove existing event listener and add new one
                    addBudgetComponentBtn.replaceWith(addBudgetComponentBtn.cloneNode(true));
                    document.getElementById('add-budget-component').addEventListener('click', addBudgetComponent);
                }
            }

            // Create empty yearly estimates for all selected financial years
            const yearlyEstimateContainer = document.getElementById('yearly-estimate-container');
            if (yearlyEstimateContainer) {
                yearlyEstimateContainer.innerHTML = '';
                const selectedFinancialYears = Array.from(document.querySelectorAll('.financial-year-select'))
                    .filter(select => select.value && select.value !== '');

                selectedFinancialYears.forEach((select, yearIndex) => {
                    if (select.value) {
                        createEmptyYearlyEstimateSection(select.value, yearIndex);
                    }
                });
            }
        } else {
            // Hide sections initially
            if (estimatedSection) estimatedSection.style.display = 'none';
            if (budgetedSection) budgetedSection.style.display = 'none';

            if (copyEstimatedCheckbox && copyEstimatedCheckbox.checked) {
                if (estimatedSection) estimatedSection.style.display = 'block';
                loadYearlyEstimatesFromExistingBudgets();
            }

            if (copyBudgetedCheckbox && copyBudgetedCheckbox.checked) {
                if (budgetedSection) budgetedSection.style.display = 'block';
                autoLoadProposalBudgetedExpenses();
            }
        }

        calculateAllTotals();
    }

    // Load yearly estimates from existing yearly budget rows on page load
    function loadYearlyEstimatesFromExistingBudgets() {
        console.log('Loading yearly estimates from existing budgets...');

        const yearlyEstimateContainer = document.getElementById('yearly-estimate-container');
        if (!yearlyEstimateContainer) return;

        // Get all existing yearly budget rows that have values
        const yearlyBudgetRows = Array.from(document.querySelectorAll('.yearly-budget-row'))
            .filter(row => {
                const select = row.querySelector('.financial-year-select');
                return select && select.value && select.value !== '';
            });

        console.log('Found existing yearly budget rows with values:', yearlyBudgetRows.length);

        // Check if there's exactly ONE financial year selected
        if (yearlyBudgetRows.length === 1) {
            console.log('Exactly one financial year selected, loading proposal data');
            // Get the financial year from the first (and only) row
            const firstRow = yearlyBudgetRows[0];
            const select = firstRow.querySelector('.financial-year-select');
            if (select && select.value) {
                createYearlyEstimateSection(select.value, 0);
            }
        } else if (yearlyBudgetRows.length > 1) {
            console.log('Multiple financial years selected, creating empty sections');
            // Create empty yearly estimate sections for each year
            yearlyBudgetRows.forEach((row, yearIndex) => {
                const select = row.querySelector('.financial-year-select');
                if (select && select.value) {
                    console.log(`Creating empty yearly section for financial year ${select.value} at index ${yearIndex}`);
                    createEmptyYearlyEstimateSection(select.value, yearIndex);
                }
            });
        }

        // Calculate all totals after a delay
        setTimeout(() => {
            console.log('Final calculation after loading from existing budgets');
            calculateAllTotals();
        }, 500);
    }

    // Load proposal estimated data into yearly estimates
    function loadProposalEstimatedDataToYearly() {
        console.log('Loading proposal estimated data to yearly...');

        const yearlyEstimateContainer = document.getElementById('yearly-estimate-container');
        if (!yearlyEstimateContainer) {
            console.error('Yearly estimate container not found');
            return;
        }

        // Get selected financial years that have values
        const selectedFinancialYears = Array.from(document.querySelectorAll('.financial-year-select'))
            .filter(select => select.value && select.value !== '');

        console.log('Selected financial years (with values):', selectedFinancialYears.length);

        // Check if we need to create new sections or preserve existing ones
        const existingSections = yearlyEstimateContainer.querySelectorAll('.yearly-estimated-section');
        console.log('Existing yearly sections:', existingSections.length);

        if (existingSections.length === 0) {
            // No existing sections, create new ones
            if (selectedFinancialYears.length === 1) {
                console.log('Exactly one financial year selected, loading proposal data');
                // Create yearly estimate section for the single year
                createYearlyEstimateSection(selectedFinancialYears[0].value, 0);
            } else if (selectedFinancialYears.length > 1) {
                console.log('Multiple financial years selected, creating empty sections');
                // Create empty yearly estimate sections for each year
                selectedFinancialYears.forEach((select, yearIndex) => {
                    if (select.value) {
                        console.log(`Creating empty yearly section for financial year ${select.value} at index ${yearIndex}`);
                        createEmptyYearlyEstimateSection(select.value, yearIndex);
                    }
                });
            }
        } else {
            // We have existing sections, preserve them
            console.log('Preserving existing yearly sections');
            // Just recalculate totals
            calculateAllTotals();
        }
    }

    // Load proposal data into yearly section components - ONLY if there's exactly ONE financial year
    function loadProposalDataIntoYearlySection(section, yearIndex) {
        console.log(`Loading proposal data into year ${yearIndex}...`);

        // Check how many financial years are selected (with values)
        const selectedFinancialYears = Array.from(document.querySelectorAll('.financial-year-select'))
            .filter(select => select.value && select.value !== '');
        const yearCount = selectedFinancialYears.length;

        console.log(`Total selected financial years with values: ${yearCount}`);

        // Load proposal data ONLY if there's exactly ONE financial year
        if (yearCount !== 1) {
            console.log(`Skipping - ${yearCount} years selected (need exactly 1)`);
            return; // Don't load proposal data for multiple years
        }

        // Also check that this is the first (and only) year
        if (yearIndex !== 0) {
            console.log(`Skipping year ${yearIndex} - not the first year`);
            return;
        }

        // Check if we have proposal estimated components
        const hasProposalEstimated = {{ $proposalEstimatedComponents && count($proposalEstimatedComponents) > 0 ? 'true' : 'false' }};

        console.log('Has proposal estimated:', hasProposalEstimated);

        if (!hasProposalEstimated) {
            console.log('No proposal estimated components found');
            return;
        }

        // Get all proposal estimated components
        <?php
        if($proposalEstimatedComponents && count($proposalEstimatedComponents) > 0) {
            echo "const proposalComponents = [];";

            // Prepare component data from proposal
            foreach($proposalEstimatedComponents as $groupName => $components) {
                foreach($components as $index => $component) {
                    $componentData = [
                        'group' => $groupName,
                        'component' => $component->component,
                        'mandays' => $component->mandays,
                        'rate' => $component->rate,
                        'amount' => $component->amount,
                        'description' => $component->description ?? ''
                    ];
                    echo "proposalComponents.push(" . json_encode($componentData) . ");";
                }
            }
        }
        ?>

        console.log('Total proposal components:', proposalComponents.length);

        // Get all components in this section
        const allComponents = section.querySelectorAll('.expense-component');
        console.log('Found components in section:', allComponents.length);

        allComponents.forEach((componentRow, rowIndex) => {
            // Get the component name from the readonly input
            const componentNameInput = componentRow.querySelector('input[readonly]');
            if (!componentNameInput) {
                console.log('No readonly input found in row', rowIndex);
                return;
            }

            const componentName = componentNameInput.value.trim();
            console.log(`Row ${rowIndex}: Looking for component "${componentName}"`);

            // Find matching proposal component
            const matchingProposalComponent = proposalComponents.find(pc => {
                const proposalComponentName = pc.component ? pc.component.trim() : '';
                return proposalComponentName === componentName;
            });

            if (matchingProposalComponent) {
                console.log(`Setting values for "${componentName}"`);

                // Convert all numeric values to numbers
                const proposalMandays = parseFloat(matchingProposalComponent.mandays) || 0;
                const proposalRate = parseFloat(matchingProposalComponent.rate) || 0;
                const proposalAmount = parseFloat(matchingProposalComponent.amount) || 0;
                const proposalDescription = matchingProposalComponent.description || '';

                // Update based on group type
                if (matchingProposalComponent.group === 'HR') {
                    // For HR group: update mandays and calculate amount from rate
                    const mandaysInput = componentRow.querySelector('.yearly-estimated-mandays');
                    const rateInput = componentRow.querySelector('input[readonly][type="number"]');
                    const amountInput = componentRow.querySelector('.yearly-estimated-amount');

                    if (mandaysInput && rateInput && amountInput) {
                        const rate = parseFloat(rateInput.value) || 0;

                        // If proposal has mandays, use them
                        if (proposalMandays > 0) {
                            mandaysInput.value = proposalMandays;

                            // Calculate amount based on mandays * rate
                            const calculatedAmount = proposalMandays * rate;
                            amountInput.value = calculatedAmount.toFixed(2);
                            console.log(`Set HR: mandays=${proposalMandays}, rate=${rate}, amount=${calculatedAmount}`);
                        }
                        // If proposal has amount but no mandays, calculate mandays from amount/rate
                        else if (proposalAmount > 0 && rate > 0) {
                            const calculatedMandays = proposalAmount / rate;
                            mandaysInput.value = calculatedMandays.toFixed(1);
                            amountInput.value = proposalAmount.toFixed(2);
                            console.log(`Calculated HR: mandays=${calculatedMandays} from amount=${proposalAmount}/rate=${rate}`);
                        }
                        // If proposal has both, use proposal values
                        else if (proposalMandays > 0 && proposalAmount > 0) {
                            mandaysInput.value = proposalMandays;
                            amountInput.value = proposalAmount.toFixed(2);
                            console.log(`Set both from proposal: mandays=${proposalMandays}, amount=${proposalAmount}`);
                        }

                        // Trigger input event to update calculations
                        setTimeout(() => {
                            mandaysInput.dispatchEvent(new Event('input'));
                            amountInput.dispatchEvent(new Event('input'));
                        }, 50);
                    }
                }
                else if (matchingProposalComponent.group === 'Travel' || matchingProposalComponent.group === 'Others') {
                    // For Travel and Others groups: update description and amount
                    const descriptionInput = componentRow.querySelector('input[name*="description"]');
                    const amountInput = componentRow.querySelector('.yearly-estimated-amount');

                    if (descriptionInput && proposalDescription) {
                        descriptionInput.value = proposalDescription;
                        console.log(`Set description: ${proposalDescription}`);
                    }

                    if (amountInput && proposalAmount > 0) {
                        amountInput.value = proposalAmount.toFixed(2);
                        console.log(`Set amount: ${proposalAmount}`);

                        // Trigger input event to update calculations
                        setTimeout(() => {
                            amountInput.dispatchEvent(new Event('input'));
                        }, 50);
                    }
                }
                else {
                    // For other groups or custom components
                    const amountInput = componentRow.querySelector('.yearly-estimated-amount');
                    if (amountInput && proposalAmount > 0) {
                        amountInput.value = proposalAmount.toFixed(2);
                        console.log(`Set generic amount: ${proposalAmount}`);

                        // Trigger input event to update calculations
                        setTimeout(() => {
                            amountInput.dispatchEvent(new Event('input'));
                        }, 50);
                    }
                }
            } else {
                console.log(`No matching proposal component found for "${componentName}"`);
            }
        });

        console.log('Finished loading proposal data into yearly section');

        // Also handle any custom components that might be added separately
        addProposalCustomComponents(section, yearIndex);
    }

    // Create yearly estimate section with proposal data (for single year)
    function createYearlyEstimateSection(financialYearId, yearIndex) {
        console.log(`Creating yearly estimate section ${yearIndex} for financial year ${financialYearId}`);

        // Check if section already exists
        const existingSection = document.getElementById(`yearly-estimate-${yearIndex}`);
        if (existingSection) {
            console.log(`Yearly section ${yearIndex} already exists, skipping creation`);
            return existingSection;
        }

        const template = document.getElementById('yearly-estimate-template');
        const newSection = template.cloneNode(true);
        newSection.id = `yearly-estimate-${yearIndex}`;
        newSection.classList.remove('d-none');

        // Update financial year information
        const yearInfo = newSection.querySelector('.financial-year-info');
        const yearData = financialYearData[financialYearId];

        if (yearData) {
            newSection.querySelector('.year-title').textContent = `Estimated Expenses for ${yearData.name}`;

            const start = new Date(yearData.start_date).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
            const end = new Date(yearData.end_date).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
            yearInfo.textContent = `${start} to ${end}`;
        }

        // Update all input names with year index
        newSection.querySelectorAll('input, select').forEach(el => {
            if (el.name) {
                el.name = el.name.replace('[0]', `[${yearIndex}]`);
                if (el.classList.contains('yearly-estimated-amount')) {
                    const componentIndex = el.getAttribute('data-component-index');
                    el.name = `yearly_estimates[${yearIndex}][components][${componentIndex}][amount]`;
                }
                if (el.classList.contains('yearly-estimated-mandays')) {
                    const componentIndex = el.getAttribute('data-component-index');
                    el.name = `yearly_estimates[${yearIndex}][components][${componentIndex}][mandays]`;
                }
                if (el.name.includes('[description]')) {
                    const componentIndex = el.getAttribute('data-component-index');
                    el.name = `yearly_estimates[${yearIndex}][components][${componentIndex}][description]`;
                }
            }
        });

        // Add hidden input for financial year ID
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = `yearly_estimates[${yearIndex}][financial_year_id]`;
        hiddenInput.value = financialYearId;
        newSection.appendChild(hiddenInput);

        // Add "Add Custom Component" button for each yearly section
        const addCustomBtn = document.createElement('button');
        addCustomBtn.type = 'button';
        addCustomBtn.className = 'btn btn-secondary btn-sm mt-2 add-yearly-custom-component';
        addCustomBtn.innerHTML = '<i class="fas fa-plus"></i> Add Custom Component';
        addCustomBtn.dataset.yearIndex = yearIndex;

        // Find where to insert the button (after the default components div)
        const defaultComponentsDiv = newSection.querySelector('.default-components');
        if (defaultComponentsDiv) {
            const totalDiv = newSection.querySelector('.mt-3'); // The "Total for this Year" div
            if (totalDiv) {
                // Insert button before the total div
                totalDiv.parentNode.insertBefore(addCustomBtn, totalDiv);
            } else {
                // Append to the end of default components
                defaultComponentsDiv.appendChild(addCustomBtn);
            }
        }

        // Add event listener for the custom component button
        addCustomBtn.addEventListener('click', function() {
            addYearlyCustomComponent(yearIndex);
        });

        // Load proposal data into this year's components (only for first year when copying from proposal)
        if (copyEstimatedCheckbox && copyEstimatedCheckbox.checked) {
            loadProposalDataIntoYearlySection(newSection, yearIndex);
        }

        // Add revenue calculation section for this year
        addYearlyRevenueSection(newSection, yearIndex, financialYearId);

        // Add event listeners for calculations
        newSection.querySelectorAll('.yearly-estimated-amount').forEach(input => {
            input.addEventListener('input', function() {
                console.log(`Yearly amount input changed for year ${yearIndex}`);
                calculateYearlyEstimatedTotal(yearIndex);
                calculateYearlyRevenue(yearIndex);
                updateProjectEstimatedTotal();
                calculateAllTotals();
            });
        });

        // Add event listeners for mandays calculation
        newSection.querySelectorAll('.yearly-estimated-mandays').forEach(input => {
            input.addEventListener('input', function() {
                console.log(`Yearly mandays input changed for year ${yearIndex}`);
                const componentIndex = this.getAttribute('data-component-index');
                const rateInput = newSection.querySelector(`input[name*="[components][${componentIndex}][rate]"]`);
                const amountInput = newSection.querySelector(`input[name*="[components][${componentIndex}][amount]"]`);

                if (rateInput && amountInput) {
                    const rate = parseFloat(rateInput.value) || 0;
                    const mandays = parseFloat(this.value) || 0;
                    const amount = mandays * rate;
                    amountInput.value = amount.toFixed(2);
                    calculateYearlyEstimatedTotal(yearIndex);
                    calculateYearlyRevenue(yearIndex);
                    updateProjectEstimatedTotal();
                    calculateAllTotals();
                }
            });
        });

        const yearlyEstimateContainer = document.getElementById('yearly-estimate-container');
        yearlyEstimateContainer.appendChild(newSection);

        // Calculate initial totals for this section
        setTimeout(() => {
            console.log(`Calculating initial totals for year ${yearIndex}`);
            calculateYearlyEstimatedTotal(yearIndex);
            calculateYearlyRevenue(yearIndex);
            updateProjectEstimatedTotal();
            calculateAllTotals();
        }, 300);

        return newSection;
    }

    // Create empty yearly estimate section (for multiple years)
    function createEmptyYearlyEstimateSection(financialYearId, yearIndex) {
        console.log(`Creating empty yearly estimate section ${yearIndex} for financial year ${financialYearId}`);

        // Check if section already exists
        const existingSection = document.getElementById(`yearly-estimate-${yearIndex}`);
        if (existingSection) {
            console.log(`Yearly section ${yearIndex} already exists, preserving data`);
            return existingSection;
        }

        const template = document.getElementById('yearly-estimate-template');
        const newSection = template.cloneNode(true);
        newSection.id = `yearly-estimate-${yearIndex}`;
        newSection.classList.remove('d-none');

        // Update financial year information
        const yearInfo = newSection.querySelector('.financial-year-info');
        const yearData = financialYearData[financialYearId];

        if (yearData) {
            newSection.querySelector('.year-title').textContent = `Estimated Expenses for ${yearData.name}`;

            const start = new Date(yearData.start_date).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
            const end = new Date(yearData.end_date).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
            yearInfo.textContent = `${start} to ${end}`;
        }

        // Update all input names with year index
        newSection.querySelectorAll('input, select').forEach(el => {
            if (el.name) {
                el.name = el.name.replace('[0]', `[${yearIndex}]`);
                if (el.classList.contains('yearly-estimated-amount')) {
                    const componentIndex = el.getAttribute('data-component-index');
                    el.name = `yearly_estimates[${yearIndex}][components][${componentIndex}][amount]`;
                }
                if (el.classList.contains('yearly-estimated-mandays')) {
                    const componentIndex = el.getAttribute('data-component-index');
                    el.name = `yearly_estimates[${yearIndex}][components][${componentIndex}][mandays]`;
                }
                if (el.name.includes('[description]')) {
                    const componentIndex = el.getAttribute('data-component-index');
                    el.name = `yearly_estimates[${yearIndex}][components][${componentIndex}][description]`;
                }
            }
        });

        // Add hidden input for financial year ID
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = `yearly_estimates[${yearIndex}][financial_year_id]`;
        hiddenInput.value = financialYearId;
        newSection.appendChild(hiddenInput);

        // Add "Add Custom Component" button for each yearly section
        const addCustomBtn = document.createElement('button');
        addCustomBtn.type = 'button';
        addCustomBtn.className = 'btn btn-secondary btn-sm mt-2 add-yearly-custom-component';
        addCustomBtn.innerHTML = '<i class="fas fa-plus"></i> Add Custom Component';
        addCustomBtn.dataset.yearIndex = yearIndex;

        // Find where to insert the button (after the default components div)
        const defaultComponentsDiv = newSection.querySelector('.default-components');
        if (defaultComponentsDiv) {
            const totalDiv = newSection.querySelector('.mt-3'); // The "Total for this Year" div
            if (totalDiv) {
                // Insert button before the total div
                totalDiv.parentNode.insertBefore(addCustomBtn, totalDiv);
            } else {
                // Append to the end of default components
                defaultComponentsDiv.appendChild(addCustomBtn);
            }
        }

        // Add event listener for the custom component button
        addCustomBtn.addEventListener('click', function() {
            addYearlyCustomComponent(yearIndex);
        });

        // Add revenue calculation section for this year
        addYearlyRevenueSection(newSection, yearIndex, financialYearId);

        // Add event listeners for calculations
        newSection.querySelectorAll('.yearly-estimated-amount').forEach(input => {
            input.addEventListener('input', function() {
                console.log(`Yearly amount input changed for year ${yearIndex}`);
                calculateYearlyEstimatedTotal(yearIndex);
                calculateYearlyRevenue(yearIndex);
                updateProjectEstimatedTotal();
                calculateAllTotals();
            });
        });

        // Add event listeners for mandays calculation
        newSection.querySelectorAll('.yearly-estimated-mandays').forEach(input => {
            input.addEventListener('input', function() {
                console.log(`Yearly mandays input changed for year ${yearIndex}`);
                const componentIndex = this.getAttribute('data-component-index');
                const rateInput = newSection.querySelector(`input[name*="[components][${componentIndex}][rate]"]`);
                const amountInput = newSection.querySelector(`input[name*="[components][${componentIndex}][amount]"]`);

                if (rateInput && amountInput) {
                    const rate = parseFloat(rateInput.value) || 0;
                    const mandays = parseFloat(this.value) || 0;
                    const amount = mandays * rate;
                    amountInput.value = amount.toFixed(2);
                    calculateYearlyEstimatedTotal(yearIndex);
                    calculateYearlyRevenue(yearIndex);
                    updateProjectEstimatedTotal();
                    calculateAllTotals();
                }
            });
        });

        const yearlyEstimateContainer = document.getElementById('yearly-estimate-container');
        yearlyEstimateContainer.appendChild(newSection);

        // Calculate initial totals for this section
        setTimeout(() => {
            console.log(`Calculating initial totals for year ${yearIndex}`);
            calculateYearlyEstimatedTotal(yearIndex);
            calculateYearlyRevenue(yearIndex);
            updateProjectEstimatedTotal();
            calculateAllTotals();
        }, 300);

        return newSection;
    }

    // Add a yearly custom component
    function addYearlyCustomComponent(yearIndex) {
        console.log(`Adding custom component to year ${yearIndex}`);

        const section = document.getElementById(`yearly-estimate-${yearIndex}`);
        if (!section) {
            console.error(`Section for year ${yearIndex} not found`);
            return;
        }

        const defaultComponentsDiv = section.querySelector('.default-components');
        if (!defaultComponentsDiv) {
            console.error('Default components div not found');
            return;
        }

        // Get the next available index for custom components
        const existingCustomComponents = section.querySelectorAll('.expense-component input[value="Custom"][type="hidden"]');
        const customIndex = existingCustomComponents.length;

        // Create custom component from template
        const template = document.getElementById('yearly-custom-component-template');
        if (!template) {
            console.error('Yearly custom component template not found!');
            return;
        }

        const newComponent = template.cloneNode(true);
        newComponent.classList.remove('d-none');

        // Update names with year index and custom index
        newComponent.querySelectorAll('input, select').forEach(el => {
            if (el.name) {
                el.name = el.name.replace('[0]', `[${yearIndex}]`);
                el.name = el.name.replace('custom_0', `custom_${customIndex}`);
            }
        });

        // Update the "data-component-index" for inputs
        const componentIndex = defaultComponentsDiv.querySelectorAll('.expense-component').length;
        newComponent.querySelectorAll('[data-component-index]').forEach(el => {
            el.setAttribute('data-component-index', componentIndex);
        });

        // Add event listeners
        const amountInput = newComponent.querySelector('.yearly-custom-amount');
        if (amountInput) {
            amountInput.classList.add('yearly-estimated-amount'); // Add the class for calculation
            amountInput.addEventListener('input', function() {
                console.log('Custom component amount changed');
                calculateYearlyEstimatedTotal(yearIndex);
                calculateYearlyRevenue(yearIndex);
                updateProjectEstimatedTotal();
                calculateAllTotals();
            });
        }

        const removeBtn = newComponent.querySelector('.remove-yearly-custom-component');
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                console.log('Removing custom component');
                newComponent.remove();
                calculateYearlyEstimatedTotal(yearIndex);
                calculateYearlyRevenue(yearIndex);
                updateProjectEstimatedTotal();
                calculateAllTotals();
            });
        }

        // Insert before the "Add Custom Component" button
        const addButton = defaultComponentsDiv.querySelector('.add-yearly-custom-component');
        if (addButton) {
            addButton.parentNode.insertBefore(newComponent, addButton);
        } else {
            defaultComponentsDiv.appendChild(newComponent);
        }

        // Focus on the amount input
        setTimeout(() => {
            if (amountInput) amountInput.focus();
        }, 100);

        // Recalculate totals
        setTimeout(() => {
            calculateYearlyEstimatedTotal(yearIndex);
            calculateYearlyRevenue(yearIndex);
            updateProjectEstimatedTotal();
            calculateAllTotals();
        }, 100);
    }

    // Add yearly revenue section
    function addYearlyRevenueSection(section, yearIndex, financialYearId) {
        // Check if revenue section already exists
        const existingRevenueSection = document.getElementById(`yearly-revenue-${yearIndex}`);
        if (existingRevenueSection) {
            console.log(`Revenue section for year ${yearIndex} already exists`);
            return;
        }

        const revenueSection = document.createElement('div');
        revenueSection.className = 'row mt-3 yearly-revenue-section';
        revenueSection.id = `yearly-revenue-${yearIndex}`;

        const yearData = financialYearData[financialYearId];
        const yearBudget = getYearlyBudgetAmount(financialYearId) || 0;

        revenueSection.innerHTML = `
            <div class="col-md-12">
                <div class="card bg-light">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-md-3">
                                <small class="text-muted">${yearData?.name || 'Financial Year'} Budget</small>
                                <h6 class="mb-0">₹<span id="yearly-budget-${yearIndex}">${yearBudget.toFixed(2)}</span></h6>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">Yearly Estimated Expense</small>
                                <h6 class="mb-0">₹<span id="yearly-expense-${yearIndex}">0.00</span></h6>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">Yearly Revenue</small>
                                <h6 class="mb-0 text-success">₹<span id="yearly-revenue-${yearIndex}">${yearBudget.toFixed(2)}</span></h6>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">Status</small>
                                <h6 class="mb-0">
                                    <span id="yearly-status-${yearIndex}" class="badge bg-success">Profitable</span>
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Insert after the total display
        const totalDisplay = section.querySelector('.yearly-estimated-total').closest('.mt-3');
        if (totalDisplay) {
            totalDisplay.parentNode.insertBefore(revenueSection, totalDisplay.nextSibling);
        }
    }

    // Add proposal custom components (ONLY if there's exactly ONE financial year)
    function addProposalCustomComponents(section, yearIndex) {
        // Check how many financial years are selected (with values)
        const selectedFinancialYears = Array.from(document.querySelectorAll('.financial-year-select'))
            .filter(select => select.value && select.value !== '');
        const yearCount = selectedFinancialYears.length;

        // Load custom components ONLY if there's exactly ONE financial year
        if (yearCount !== 1) {
            console.log(`Skipping custom components - ${yearCount} years selected (need exactly 1)`);
            return;
        }

        // Also check that this is the first (and only) year
        if (yearIndex !== 0) {
            console.log(`Skipping custom components for year ${yearIndex} - not the first year`);
            return;
        }

        const hasProposalCustomComponents = {{ $proposalEstimatedComponents && isset($proposalEstimatedComponents['Custom']) ? 'true' : 'false' }};

        if (!hasProposalCustomComponents) {
            console.log('No proposal custom components found');
            return;
        }

        const defaultComponentsDiv = section.querySelector('.default-components');
        if (!defaultComponentsDiv) return;

        console.log('Adding custom components from proposal to single year');

        @if($proposalEstimatedComponents && isset($proposalEstimatedComponents['Custom']))
            @foreach($proposalEstimatedComponents['Custom'] as $index => $component)
                // Get the next available index
                const existingComponents = defaultComponentsDiv.querySelectorAll('.expense-component');
                const nextIndex = existingComponents.length;

                // Convert proposal values to numbers
                const proposalMandays = parseFloat("{{ $component->mandays }}") || 0;
                const proposalRate = parseFloat("{{ $component->rate }}") || 0;
                const proposalAmount = parseFloat("{{ $component->amount }}") || 0;

                const customRow = document.createElement('div');
                customRow.className = 'expense-component row g-3 mb-2';
                customRow.innerHTML = `
                    <input type="hidden" name="yearly_estimates[${yearIndex}][components][${nextIndex}][group]" value="Custom">
                    <input type="hidden" name="yearly_estimates[${yearIndex}][components][${nextIndex}][component]" value="{{ $component->component }}">
                    <input type="hidden" name="yearly_estimates[${yearIndex}][components][${nextIndex}][category_id]" value="{{ $component->expense_category_id }}">
                    <input type="hidden" name="yearly_estimates[${yearIndex}][components][${nextIndex}][rate]" value="{{ $component->rate }}">

                    <div class="col-md-4">
                        <label class="form-label">Component</label>
                        <input type="text" class="form-control" value="{{ $component->component }}" readonly>
                        <small class="text-muted">Custom Component</small>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Persondays</label>
                        <input type="number" name="yearly_estimates[${yearIndex}][components][${nextIndex}][mandays]"
                               class="form-control yearly-estimated-mandays"
                               value="${proposalMandays}"
                               min="0" step="0.1"
                               data-component-index="${nextIndex}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Rate (₹)</label>
                        <input type="number" class="form-control" value="${proposalRate}" readonly>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Amount (₹)</label>
                        <input type="number" step="0.01" min="0"
                               name="yearly_estimates[${yearIndex}][components][${nextIndex}][amount]"
                               class="form-control yearly-estimated-amount"
                               data-component-index="${nextIndex}"
                               value="${proposalAmount}">
                    </div>

                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-danger btn-sm remove-yearly-custom-component">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;

                defaultComponentsDiv.appendChild(customRow);

                // Add event listeners
                setTimeout(() => {
                    const amountInput = customRow.querySelector('.yearly-estimated-amount');
                    const mandaysInput = customRow.querySelector('.yearly-estimated-mandays');

                    if (amountInput) {
                        amountInput.addEventListener('input', function() {
                            calculateYearlyEstimatedTotal(yearIndex);
                            calculateYearlyRevenue(yearIndex);
                            updateProjectEstimatedTotal();
                            calculateAllTotals();
                        });
                    }

                    if (mandaysInput) {
                        mandaysInput.addEventListener('input', function() {
                            const componentIndex = this.getAttribute('data-component-index');
                            const rateInput = customRow.querySelector(`input[name*="[components][${componentIndex}][rate]"]`);
                            const amountInput = customRow.querySelector(`input[name*="[components][${componentIndex}][amount]"]`);

                            if (rateInput && amountInput) {
                                const rate = parseFloat(rateInput.value) || 0;
                                const mandays = parseFloat(this.value) || 0;
                                const amount = mandays * rate;
                                amountInput.value = amount.toFixed(2);
                                calculateYearlyEstimatedTotal(yearIndex);
                                calculateYearlyRevenue(yearIndex);
                                updateProjectEstimatedTotal();
                                calculateAllTotals();
                            }
                        });
                    }

                    const removeBtn = customRow.querySelector('.remove-yearly-custom-component');
                    if (removeBtn) {
                        removeBtn.addEventListener('click', function() {
                            customRow.remove();
                            calculateYearlyEstimatedTotal(yearIndex);
                            calculateYearlyRevenue(yearIndex);
                            updateProjectEstimatedTotal();
                            calculateAllTotals();
                        });
                    }

                    // Trigger initial calculations
                    setTimeout(() => {
                        if (amountInput) amountInput.dispatchEvent(new Event('input'));
                    }, 100);
                }, 100);
            @endforeach
        @endif
    }

    // Calculate total for a specific yearly estimate
    function calculateYearlyEstimatedTotal(yearIndex) {
        console.log(`Calculating yearly total for year ${yearIndex}`);

        const section = document.getElementById(`yearly-estimate-${yearIndex}`);
        if (!section) {
            console.error(`Section for year ${yearIndex} not found`);
            return 0;
        }

        let total = 0;
        section.querySelectorAll('.yearly-estimated-amount').forEach(input => {
            const value = parseFloat(input.value) || 0;
            total += value;
            console.log(`Adding amount: ${value} (total so far: ${total})`);
        });

        const totalSpan = section.querySelector('.yearly-estimated-total');
        if (totalSpan) {
            totalSpan.textContent = total.toFixed(2);
            console.log(`Year ${yearIndex} total: ${total.toFixed(2)}`);
        }

        // Update yearly expense display
        const yearlyExpenseSpan = document.getElementById(`yearly-expense-${yearIndex}`);
        if (yearlyExpenseSpan) {
            yearlyExpenseSpan.textContent = total.toFixed(2);
            console.log(`Year ${yearIndex} expense display updated: ${total.toFixed(2)}`);
        }

        return total;
    }

    // Calculate yearly revenue
    function calculateYearlyRevenue(yearIndex) {
        const yearlyBudget = getYearlyBudgetForIndex(yearIndex);
        const yearlyExpense = calculateYearlyEstimatedTotal(yearIndex);
        const yearlyRevenue = yearlyBudget - yearlyExpense;

        const revenueSpan = document.getElementById(`yearly-revenue-${yearIndex}`);
        const statusSpan = document.getElementById(`yearly-status-${yearIndex}`);

        if (revenueSpan) {
            revenueSpan.textContent = yearlyRevenue.toFixed(2);
            if (yearlyRevenue >= 0) {
                revenueSpan.className = 'text-success';
                if (statusSpan) {
                    statusSpan.className = 'badge bg-success';
                    statusSpan.textContent = 'Profitable';
                }
            } else {
                revenueSpan.className = 'text-danger';
                if (statusSpan) {
                    statusSpan.className = 'badge bg-danger';
                    statusSpan.textContent = 'Loss';
                }
            }
        }

        return yearlyRevenue;
    }

    // Get yearly budget amount for specific year index
    function getYearlyBudgetForIndex(yearIndex) {
        const budgetInput = document.querySelector(`#financial_year_${yearIndex}`)?.closest('.yearly-budget-row')?.querySelector('.yearly-budget-amount');
        return budgetInput ? parseFloat(budgetInput.value) || 0 : 0;
    }

    // Get yearly budget amount by financial year ID
    function getYearlyBudgetAmount(financialYearId) {
        const budgetRows = document.querySelectorAll('.yearly-budget-row');
        for (const row of budgetRows) {
            const select = row.querySelector('.financial-year-select');
            if (select && select.value == financialYearId) {
                const amountInput = row.querySelector('.yearly-budget-amount');
                return amountInput ? parseFloat(amountInput.value) || 0 : 0;
            }
        }
        return 0;
    }

    // Update project estimated total (readonly)
    function updateProjectEstimatedTotal() {
        console.log('Updating project estimated total...');

        const totalYearlyEstimated = calculateTotalYearlyEstimatedExpense();
        console.log('Total yearly estimated:', totalYearlyEstimated);

        // Update the span in the yearly estimates section
        const totalEstimatedExpenseSpan = document.getElementById('total-estimated-expense');
        if (totalEstimatedExpenseSpan) {
            totalEstimatedExpenseSpan.textContent = totalYearlyEstimated.toFixed(2);
            console.log('Updated total estimated expense span:', totalYearlyEstimated.toFixed(2));
        }

        // Update the display input in the "Revenue Calculation Summary" section
        const totalEstimatedDisplay = document.getElementById('total-estimated-expense-display-summary');
        if (totalEstimatedDisplay) {
            totalEstimatedDisplay.value = totalYearlyEstimated.toFixed(2);
            console.log('Updated total estimated display:', totalYearlyEstimated.toFixed(2));
        }

        // Update the hidden input
        const estimatedExpenseInput = document.getElementById('estimated_expense');
        if (estimatedExpenseInput) {
            estimatedExpenseInput.value = totalYearlyEstimated.toFixed(2);
            console.log('Updated estimated expense input:', totalYearlyEstimated.toFixed(2));
        }

        // Also update the "Project Estimated Expenses" section
        const projectEstimatedDisplay = document.querySelector('#project-estimated-expense-section input[readonly]');
        if (projectEstimatedDisplay) {
            projectEstimatedDisplay.value = totalYearlyEstimated.toFixed(2);
        }
    }

    // Calculate total yearly estimated expenses
    function calculateTotalYearlyEstimatedExpense() {
        console.log('Calculating total yearly estimated expenses...');

        let total = 0;
        const sections = document.querySelectorAll('.yearly-estimated-section');
        console.log('Found yearly sections:', sections.length);

        sections.forEach(section => {
            const yearIndex = section.id.replace('yearly-estimate-', '');
            const yearTotal = calculateYearlyEstimatedTotal(yearIndex);
            total += yearTotal;
            console.log(`Year ${yearIndex} contributes: ${yearTotal}`);
        });

        console.log('Total yearly estimated expense:', total);
        return total;
    }

    // Calculate total yearly budget
    function calculateTotalYearlyBudget() {
        let total = 0;
        document.querySelectorAll('.yearly-budget-amount').forEach(input => {
            total += parseFloat(input.value) || 0;
        });

        const totalYearlyBudgetSpan = document.getElementById('total-yearly-budget');
        const totalYearlyBudgetDisplay = document.getElementById('total-yearly-budget-display');
        const budgetInput = document.getElementById('budget');

        if (totalYearlyBudgetSpan) {
            totalYearlyBudgetSpan.textContent = total.toFixed(2);
        }
        if (totalYearlyBudgetDisplay) {
            totalYearlyBudgetDisplay.value = total.toFixed(2);
        }
        if (budgetInput) {
            budgetInput.value = total.toFixed(2);
        }

        // Update yearly budget displays
        document.querySelectorAll('.yearly-budget-row').forEach((row, index) => {
            const select = row.querySelector('.financial-year-select');
            const amountInput = row.querySelector('.yearly-budget-amount');
            if (select && select.value && amountInput) {
                const yearlyBudgetSpan = document.getElementById(`yearly-budget-${index}`);
                if (yearlyBudgetSpan) {
                    yearlyBudgetSpan.textContent = (parseFloat(amountInput.value) || 0).toFixed(2);
                }
            }
        });

        return total;
    }

    // ============ REVENUE CALCULATION ============
    function calculateRevenue() {
        const yearlyBudget = calculateTotalYearlyBudget();
        const totalEstimatedExpense = calculateTotalYearlyEstimatedExpense();
        const revenue = yearlyBudget - totalEstimatedExpense;
        const revenueInput = document.getElementById('revenue');
        if (revenueInput) {
            revenueInput.value = revenue >= 0 ? revenue.toFixed(2) : 0;
        }
    }

    // Calculate all totals
    function calculateAllTotals() {
        calculateTotalYearlyBudget();
        updateProjectEstimatedTotal();
        calculateRevenue();

        // Calculate yearly revenues
        document.querySelectorAll('.yearly-estimated-section').forEach(section => {
            const yearIndex = section.id.replace('yearly-estimate-', '');
            calculateYearlyRevenue(yearIndex);
        });

        // Update budgeted expense total if exists
        const budgetedTotal = calculateProjectBudgetedExpense();
        const totalBudgetedExpenseSpan = document.getElementById('total-budgeted-expense');
        if (totalBudgetedExpenseSpan) {
            totalBudgetedExpenseSpan.textContent = budgetedTotal.toFixed(2);
        }
    }

    // Calculate project budgeted expenses
    function calculateProjectBudgetedExpense() {
        let total = 0;
        document.querySelectorAll('.budgeted-expense-amount').forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        return total;
    }

    // Calculate budget amount for HR components
    function calculateBudgetAmount(input) {
        const target = input.dataset.target;
        const rateField = document.querySelector(`.budgeted-rate-input[data-target="${target}"]`);
        const amountField = document.querySelector(`#budgeted_amount_${target}`);

        if (rateField && amountField) {
            const rate = parseFloat(rateField.value) || 0;
            const mandays = parseFloat(input.value) || 0;
            const amount = mandays * rate;
            amountField.value = amount.toFixed(2);
        }
    }

    // ============ YEARLY BUDGETS ============
    const yearlyBudgetContainer = document.getElementById('yearly-budget-container');
    const addYearlyBudgetBtn = document.getElementById('add-yearly-budget');

    // Update selected financial years set when page loads
    document.querySelectorAll('.financial-year-select').forEach(select => {
        const value = select.value;
        if (value) {
            selectedFinancialYearIds.add(value);
        }
    });

    // Function to update financial year dropdown options
    function updateFinancialYearOptions() {
        const allSelects = document.querySelectorAll('.financial-year-select');
        const availableYears = {!! json_encode($financialYears->pluck('id', 'name')) !!};

        allSelects.forEach(select => {
            const currentValue = select.value;

            // Clear existing options except the first one
            while (select.options.length > 1) {
                select.remove(1);
            }

            // Add options for all financial years
            Object.entries(availableYears).forEach(([name, id]) => {
                // Don't add option if it's already selected in another row (except current row)
                if (selectedFinancialYearIds.has(id.toString()) && id.toString() !== currentValue) {
                    return;
                }

                const option = document.createElement('option');
                option.value = id;
                option.textContent = name;
                select.appendChild(option);
            });

            // Restore current value
            select.value = currentValue;
        });
    }

    // Add new yearly budget row
    if (addYearlyBudgetBtn) {
        addYearlyBudgetBtn.addEventListener('click', function() {
            const template = document.getElementById('yearly-budget-template');
            const newRow = template.cloneNode(true);
            newRow.id = '';
            newRow.classList.remove('d-none');

            // Update names with new index
            newRow.querySelectorAll('input, select').forEach(el => {
                const oldName = el.getAttribute('name');
                if (oldName) {
                    const newName = oldName.replace('[0]', `[${yearlyBudgetCount}]`);
                    el.setAttribute('name', newName);

                    // Update ID for amount input
                    if (el.classList.contains('yearly-budget-amount')) {
                        el.id = 'yearly_budget_amount_' + yearlyBudgetCount;
                    }

                    // Update ID for financial year select
                    if (el.classList.contains('financial-year-select')) {
                        el.id = 'financial_year_' + yearlyBudgetCount;
                    }
                }
            });

            // Add remove button event
            const removeBtn = newRow.querySelector('.remove-yearly-budget');
            if (removeBtn) {
                removeBtn.style.display = 'block';
                removeBtn.addEventListener('click', function() {
                    // Remove from selected set
                    const select = newRow.querySelector('.financial-year-select');
                    if (select && select.value) {
                        selectedFinancialYearIds.delete(select.value);
                    }

                    // Also remove the corresponding yearly estimate section
                    const yearIndex = yearlyBudgetCount;
                    const yearlyEstimateSection = document.getElementById(`yearly-estimate-${yearIndex}`);
                    if (yearlyEstimateSection) {
                        yearlyEstimateSection.remove();
                    }

                    // Remove revenue section
                    const revenueSection = document.getElementById(`yearly-revenue-${yearIndex}`);
                    if (revenueSection) {
                        revenueSection.remove();
                    }

                    newRow.remove();
                    calculateAllTotals();
                    updateFinancialYearOptions();
                    reinitializeYearlyEstimates();
                });
            }

            // Add amount change listener
            newRow.querySelector('.yearly-budget-amount').addEventListener('input', function() {
                calculateAllTotals();
            });

            // Add financial year change listener
            const financialYearSelect = newRow.querySelector('.financial-year-select');
            if (financialYearSelect) {
                financialYearSelect.addEventListener('change', function() {
                    // Remove previous selection from set
                    const previousValue = this.getAttribute('data-previous-value');
                    if (previousValue) {
                        selectedFinancialYearIds.delete(previousValue);
                    }

                    // Add new selection to set
                    if (this.value) {
                        selectedFinancialYearIds.add(this.value);
                    }

                    // Update previous value
                    this.setAttribute('data-previous-value', this.value);

                    // Update other dropdowns
                    updateFinancialYearOptions();
                    reinitializeYearlyEstimates();
                });
            }

            // Append new row
            yearlyBudgetContainer.appendChild(newRow);
            updateFinancialYearOptions();

            // Create a new yearly estimate section for this new financial year
            const select = newRow.querySelector('.financial-year-select');
            if (select && select.value) {
                createEmptyYearlyEstimateSection(select.value, yearlyBudgetCount);
            }

            yearlyBudgetCount++;
            reinitializeYearlyEstimates();
        });
    }

    // Reinitialize yearly estimates - PRESERVE EXISTING DATA
    function reinitializeYearlyEstimates() {
        console.log('Reinitializing yearly estimates...');

        // Get all yearly budget rows
        const yearlyBudgetRows = Array.from(document.querySelectorAll('.yearly-budget-row'));

        // For each budget row, ensure there's a corresponding yearly estimate section
        yearlyBudgetRows.forEach((row, yearIndex) => {
            const select = row.querySelector('.financial-year-select');
            if (select && select.value) {
                const existingSection = document.getElementById(`yearly-estimate-${yearIndex}`);
                if (!existingSection) {
                    // Create a new empty section for this year
                    console.log(`Creating new yearly section for index ${yearIndex}`);
                    createEmptyYearlyEstimateSection(select.value, yearIndex);
                } else {
                    // Update financial year info if needed
                    const yearData = financialYearData[select.value];
                    if (yearData) {
                        existingSection.querySelector('.year-title').textContent = `Estimated Expenses for ${yearData.name}`;

                        const start = new Date(yearData.start_date).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
                        const end = new Date(yearData.end_date).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
                        const yearInfo = existingSection.querySelector('.financial-year-info');
                        if (yearInfo) {
                            yearInfo.textContent = `${start} to ${end}`;
                        }

                        // Update hidden financial year ID
                        const hiddenInput = existingSection.querySelector(`input[name="yearly_estimates[${yearIndex}][financial_year_id]"]`);
                        if (hiddenInput) {
                            hiddenInput.value = select.value;
                        }
                    }
                }
            }
        });

        // Calculate totals
        calculateAllTotals();
    }

    // Add event listeners to all amount inputs
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('yearly-budget-amount') ||
            e.target.classList.contains('budgeted-expense-amount') ||
            e.target.classList.contains('yearly-estimated-amount') ||
            e.target.classList.contains('yearly-estimated-mandays') ||
            e.target.classList.contains('budgeted-mandays-input')) {
            calculateAllTotals();
        }
    });

    // Monitor financial year selections to update yearly estimates
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('financial-year-select')) {
            setTimeout(function() {
                reinitializeYearlyEstimates();
            }, 100);
        }
    });

    // ============ TEAM MEMBERS ============
    $('#user_selector').on('change', function () {
        const userId = $(this).val();
        const userName = $(this).find('option:selected').text();

        if (!userId || teamMembers.some(m => m.user_id == userId)) return;

        const newMember = {
            user_id: userId,
            name: userName,
            role: 'member',
            expected_time: 0
        };

        teamMembers.push(newMember);
        renderTeamList();
        $(this).val('');
    });

    function renderTeamList() {
        let html = '';
        teamMembers.forEach((member, index) => {
            html += `
                <div class="row align-items-center mb-2" data-index="${index}">
                    <div class="col-md-4">${member.name}</div>
                    <div class="col-md-3">
                        <select class="form-select role-select">
                            <option value="lead" ${member.role == 'lead' ? 'selected' : ''}>Lead</option>
                            <option value="leadMember" ${member.role == 'leadMember' ? 'selected' : ''}>Lead Member (Data Entry Enabled Memebr)</option>
                            <option value="member" ${member.role == 'member' ? 'selected' : ''}>Member</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="project_investigator_id" class="form-label">Time Investment (Hours)</label>
                        <input type="number" step="0.1" min="0" class="form-control time-input" value="${member.expected_time}">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-sm remove-member">X</button>
                    </div>
                </div>
            `;
        });

        $('#selected_users_list').html(html);
        updateTeamJson();
    }

    $(document).on('change', '.role-select, .time-input', function () {
        const row = $(this).closest('.row');
        const index = row.data('index');
        const newRole = row.find('.role-select').val();
        const newTime = parseFloat(row.find('.time-input').val()) || 0;

        teamMembers[index].role = newRole;
        teamMembers[index].expected_time = newTime;
        updateTeamJson();
    });

    $(document).on('click', '.remove-member', function () {
        const index = $(this).closest('.row').data('index');
        teamMembers.splice(index, 1);
        renderTeamList();
    });

    function updateTeamJson() {
        $('#team_members_json').val(JSON.stringify(teamMembers));
    }

    // Add event listeners to checkboxes
    if (copyEstimatedCheckbox) {
        copyEstimatedCheckbox.addEventListener('change', toggleExpenseSections);
    }

    if (copyBudgetedCheckbox) {
        copyBudgetedCheckbox.addEventListener('change', toggleExpenseSections);
    }

    if (useCustomCheckbox) {
        useCustomCheckbox.addEventListener('change', toggleExpenseSections);
    }

    // ============ INITIALIZATION ============
    // Initialize financial year dropdowns
    updateFinancialYearOptions();

    // Auto-load proposal budgeted expenses if checkbox is checked
    autoLoadProposalBudgetedExpenses();

    // Initialize expense sections
    toggleExpenseSections();

    // Check if we should load yearly estimates on page load
    if (copyEstimatedCheckbox && copyEstimatedCheckbox.checked) {
        // Load yearly estimates after DOM is fully loaded
        setTimeout(() => {
            console.log('Initializing yearly estimates from proposal...');
            loadYearlyEstimatesFromExistingBudgets();
        }, 1000);
    }

    // Initialize add budget component button
    const addBudgetComponentBtn = document.getElementById('add-budget-component');
    if (addBudgetComponentBtn) {
        addBudgetComponentBtn.addEventListener('click', addBudgetComponent);
    }

    // Calculate initial totals
    setTimeout(() => {
        calculateAllTotals();
    }, 1500);
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
        <h5 class="card-title">From Proposal: {{ $proposal->requirement->temp_no }}</h5>
      </div>
      <div class="card-body">
        {{-- Hidden Template for New Estimated Components --}}
        <div class="expense-component row g-3 mb-2 d-none" id="estimated-custom-component-template">
          <input type="hidden" name="estimated_expense_components[0][group]" value="Custom">
          <div class="col-md-3">
            <label class="form-label">Category</label>
            <select name="estimated_expense_components[0][category_id]" class="form-select expense-category">
              <option value="">Select Category</option>
              @foreach($expenseCategories as $category)
              <option value="{{ $category->id }}">{{ $category->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Component</label>
            <input type="text" name="estimated_expense_components[0][component]" class="form-control"
              placeholder="Component name">
          </div>
          <div class="col-md-3">
            <label class="form-label">Amount (₹)</label>
            <input type="number" step="0.01" min="0" name="estimated_expense_components[0][amount]"
              class="form-control estimated-expense-amount" placeholder="0.00">
          </div>
          <div class="col-md-1 d-flex align-items-end">
            <button type="button" class="btn btn-danger remove-estimated-component">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </div>

        {{-- Hidden Template for New Budget Components --}}
        <div class="expense-component row g-3 mb-2 d-none" id="budget-custom-component-template">
          <input type="hidden" name="budgeted_expense_components[0][group]" value="Custom">
          <div class="col-md-3">
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
              class="form-control budgeted-expense-amount" placeholder="0.00">
          </div>
          <div class="col-md-1 d-flex align-items-end">
            <button type="button" class="btn btn-danger remove-budget-component">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </div>

        {{-- Hidden Template for Yearly Budget --}}
        <div class="row g-3 mb-2 d-none yearly-budget-row" id="yearly-budget-template">
          <div class="col-md-4">
            <label class="form-label">Financial Year *</label>
            <select name="yearly_budgets[0][financial_year_id]" class="form-select financial-year-select" required>
              <option value="">Select Financial Year</option>
              {{-- Options will be populated dynamically --}}
            </select>
            <div class="financial-year-info">
              <small class="text-muted" id="yearly_budget_period_0"></small>
            </div>
          </div>
          <div class="col-md-3">
            <label class="form-label">Budget Amount (₹) *</label>
            <input type="number" step="0.01" min="0" name="yearly_budgets[0][amount]"
              class="form-control yearly-budget-amount" placeholder="0.00" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Notes</label>
            <input type="text" name="yearly_budgets[0][notes]" class="form-control" placeholder="Optional">
          </div>
          <div class="col-md-2 d-flex align-items-end">
            <button type="button" class="btn btn-danger remove-yearly-budget">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </div>


        {{-- Hidden Template for Yearly Estimated Expense Section --}}
        <div class="yearly-estimated-section d-none yearly-estimate-section" id="yearly-estimate-template">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 year-title">Estimated Expenses for Financial Year</h6>
            <small class="text-muted financial-year-info"></small>
          </div>

          {{-- Default components for each financial year --}}
          <div class="default-components">
            @php
            $defaultComponents = [
            ['group' => 'HR', 'component' => 'Manpower-Faculty Cost', 'rate' => 14000, 'min' => 0.5],
            ['group' => 'HR', 'component' => 'Manpower-Sr Faculty Associate Cost', 'rate' => 8000, 'min' => 0],
            ['group' => 'HR', 'component' => 'Manpower-Faculty Associate Cost', 'rate' => 6000, 'min' => 0],
            ['group' => 'HR', 'component' => 'Manpower-Project Staff', 'rate' => 3200, 'min' => 0],
            ['group' => 'HR', 'component' => 'Manpower-Consultants', 'rate' => 8000, 'min' => 0],
            ['group' => 'Travel', 'component' => 'Travel-Faculty Cost'],
            ['group' => 'Travel', 'component' => 'Travel-Sr Faculty Associate Cost'],
            ['group' => 'Travel', 'component' => 'Travel-Faculty Associate Cost'],
            ['group' => 'Travel', 'component' => 'Travel-Project Staff'],
            ['group' => 'Travel', 'component' => 'Travel-Consultants'],
            ['group' => 'Others', 'component' => 'Laptop'],
            ['group' => 'Others', 'component' => 'Reports'],
            ['group' => 'Others', 'component' => 'Printing'],
            ['group' => 'Others', 'component' => 'Stationary'],
            ];
            @endphp

            @foreach($defaultComponents as $i => $component)
            <div class="expense-component row g-3 mb-2">
              <input type="hidden" name="yearly_estimates[0][components][{{ $i }}][group]"
                value="{{ $component['group'] }}">
              <input type="hidden" name="yearly_estimates[0][components][{{ $i }}][component]"
                value="{{ $component['component'] }}">

              @if(isset($component['rate']))
              <input type="hidden" name="yearly_estimates[0][components][{{ $i }}][rate]"
                value="{{ $component['rate'] }}">
              @endif

              <div class="col-md-5">
                <label class="form-label">Component</label>
                <input type="text" class="form-control" value="{{ $component['component'] }}" readonly>
                <small class="text-muted">Group: {{ $component['group'] }}</small>
              </div>

              @if($component['group'] === 'HR')
              <div class="col-md-2">
                <label class="form-label">Persondays</label>
                <input type="number" name="yearly_estimates[0][components][{{ $i }}][mandays]"
                  class="form-control yearly-estimated-mandays" min="{{ $component['min'] }}" step="0.1"
                  value="{{ $component['min'] }}" data-component-index="{{ $i }}">
              </div>

              <div class="col-md-2">
                <label class="form-label">Rate (₹)</label>
                <input type="number" class="form-control" value="{{ $component['rate'] }}" readonly>
              </div>
              @else
              <div class="col-md-4">
                <label class="form-label">Description</label>
                <input type="text" name="yearly_estimates[0][components][{{ $i }}][description]" class="form-control"
                  placeholder="Enter description">
              </div>
              @endif

              <div class="col-md-2">
                <label class="form-label">Amount (₹)</label>
                <input type="number" step="0.01" min="0" name="yearly_estimates[0][components][{{ $i }}][amount]"
                  class="form-control yearly-estimated-amount" data-component-index="{{ $i }}" value="0">
              </div>
            </div>
            @endforeach
          </div>

          <div class="mt-3">
            <strong>Total for this Year: ₹<span class="yearly-estimated-total">0.00</span></strong>
          </div>
        </div>



        <form action="{{ route('pms.projects.store', $proposal->id) }}" method="POST">
          @csrf
          <input type="hidden" name="copy_to_budget" id="copy_to_budget" value="0">

          {{-- Copy Options Section --}}
          <div class="row mb-4">
            <div class="col-md-12">
              <div class="card">
                <div class="card-header">
                  <h6 class="mb-0">Expense Configuration Options</h6>
                </div>
                <div class="card-body">
                  <div class="copy-checkbox">
                    <input type="checkbox" id="copy_estimated_expenses" name="copy_estimated_expenses" value="1"
                      checked>
                    <label for="copy_estimated_expenses">Copy Estimated Expenses from Proposal</label>
                  </div>

                  <div class="copy-checkbox">
                    <input type="checkbox" id="copy_budgeted_expenses" name="copy_budgeted_expenses" value="1" checked>
                    <label for="copy_budgeted_expenses">Copy Budgeted Expenses from Proposal</label>
                  </div>

                  <div class="copy-checkbox">
                    <input type="checkbox" id="use_custom_expenses" name="use_custom_expenses" value="1">
                    <label for="use_custom_expenses">Use Custom Expenses (Ignore proposal data)</label>
                  </div>
                </div>
              </div>
            </div>
          </div>



          <div class="row mb-3">
            <div class="col-md-6">
              <label for="title" class="form-label">Project Title *</label>
              <input type="text" name="title" id="title" class="form-control"
                value="{{ old('title', $proposal->requirement->project_title) }}" required>
              @error('title')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label for="project_investigator_id" class="form-label">Principal investigator</label>
              <select name="project_investigator_id" id="project_investigator_id" class="form-select"
                style="pointer-events: none; background-color: #e9ecef;" tabindex="-1" required>
                <option value="">Select Investigator *</option>
                @foreach($faculty as $user)
                <option value="{{ $user->id }}" @if($proposal->requirement->allocated_to == $user->id) Selected
                  @endif>{{ $user->name }}</option>
                @endforeach
              </select>
              @error('project_investigator_id')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
              <div class="mb-3">
                <label class="form-label">Principal investigator Expected Time (in hours)</label>
                <input type="number" step="0.1" min="0" name="pi_expected_time" class="form-control" required>
              </div>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label for="start_date" class="form-label">Start Date *</label>
              <input type="date" name="start_date" id="start_date" class="form-control"
                value="{{ old('start_date', $proposal->expected_start_date ? $proposal->expected_start_date->format('Y-m-d') : '') }}"
                required>
              @error('start_date')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label for="end_date" class="form-label">End Date *</label>
              <input type="date" name="end_date" id="end_date" class="form-control"
                value="{{ old('end_date', $proposal->expected_end_date ? $proposal->expected_end_date->format('Y-m-d') : '') }}"
                required>
              @error('end_date')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>

          {{-- Yearly Budget Section --}}
          <div class="row mb-4">
            <div class="col-md-12">
              <div class="card">
                <div class="card-header">
                  <h6 class="mb-0">Year-wise Budget Allocation</h6>
                </div>
                <div class="card-body">
                  <p class="text-muted small">Select financial years applicable for project period:
                    {{ $proposal->expected_start_date ? $proposal->expected_start_date->format('d M Y') : '' }}
                    to
                    {{ $proposal->expected_end_date ? $proposal->expected_end_date->format('d M Y') : '' }}
                  </p>

                  <div id="yearly-budget-container">
                    @php
                    $applicableYears = $financialYears->filter(function($year) use ($proposal) {
                    return $year->containsDate($proposal->expected_start_date) ||
                    $year->containsDate($proposal->expected_end_date) ||
                    ($proposal->expected_start_date <= $year->end_date && $proposal->expected_end_date >=
                      $year->start_date);
                      });
                      @endphp

                      @foreach($applicableYears as $index => $financialYear)
                      <div class="row g-3 mb-2 yearly-budget-row">
                        <div class="col-md-4">
                          <label class="form-label">Financial Year *</label>
                          <select name="yearly_budgets[{{ $index }}][financial_year_id]"
                            class="form-select financial-year-select" id="financial_year_{{ $index }}" required>
                            <option value="">Select Financial Year</option>
                            @foreach($financialYears as $year)
                            <option value="{{ $year->id }}" {{ $year->id == $financialYear->id ? 'selected' : '' }}
                              data-start="{{ $year->start_date->format('Y-m-d') }}"
                              data-end="{{ $year->end_date->format('Y-m-d') }}">
                              {{ $year->display_name }}
                            </option>
                            @endforeach
                          </select>
                          <div class="financial-year-info">
                            <small class="text-muted">
                              {{ $financialYear->start_date->format('d M Y') }} - {{ $financialYear->end_date->format('d
                              M Y') }}
                            </small>
                          </div>
                        </div>
                        <div class="col-md-3">
                          <label class="form-label">Budget Amount (₹) *</label>
                          <input type="number" step="0.01" min="0" name="yearly_budgets[{{ $index }}][amount]"
                            class="form-control yearly-budget-amount" id="yearly_budget_amount_{{ $index }}"
                            placeholder="0.00" value="{{ $proposal->budget / $applicableYears->count() }}" required>
                        </div>
                        <div class="col-md-3">
                          <label class="form-label">Notes</label>
                          <input type="text" name="yearly_budgets[{{ $index }}][notes]" class="form-control"
                            placeholder="Optional">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                          <button type="button" class="btn btn-danger remove-yearly-budget">
                            <i class="fas fa-trash"></i>
                          </button>
                        </div>
                      </div>
                      @endforeach
                  </div>

                  <button type="button" id="add-yearly-budget" class="btn btn-secondary btn-sm mt-2">
                    <i class="fas fa-plus"></i> Add Another Financial Year
                  </button>

                  <div class="mt-3">
                    <strong>Total Project Budget: ₹<span id="total-yearly-budget">
                        {{ number_format($proposal->budget, 2) }}
                      </span></strong>
                  </div>
                  <input type="hidden" name="budget" id="budget" value="{{ $proposal->budget }}">
                </div>
              </div>
            </div>
          </div>

          {{-- Hidden Template for Yearly Custom Components --}}
          <div class="expense-component row g-3 mb-2 d-none" id="yearly-custom-component-template">
            <input type="hidden" name="yearly_estimates[0][components][custom_0][group]" value="Custom">
            <div class="col-md-4">
              <label class="form-label">Category</label>
              <select name="yearly_estimates[0][components][custom_0][category_id]"
                class="form-select expense-category">
                <option value="">Select Category</option>
                @foreach($expenseCategories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Component</label>
              <input type="text" name="yearly_estimates[0][components][custom_0][component]" class="form-control"
                placeholder="Component name">
            </div>
            <div class="col-md-3">
              <label class="form-label">Amount (₹)</label>
              <input type="number" step="0.01" min="0" name="yearly_estimates[0][components][custom_0][amount]"
                class="form-control yearly-custom-amount yearly-estimated-amount" placeholder="0.00">
            </div>
            <div class="col-md-1 d-flex align-items-end">
              <button type="button" class="btn btn-danger remove-yearly-custom-component">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>

          {{-- Year-wise Estimated Expenses Section --}}
          <div class="row mb-4">
            <div class="col-md-12">
              <div class="card">
                <div class="card-header">
                  <h6 class="mb-0">Year-wise Estimated Expenses</h6>
                  <p class="text-muted small mb-0">Estimated expenses for each financial year</p>
                </div>
                <div class="card-body">
                  <div id="yearly-estimate-container">
                    {{-- Yearly estimate sections will be dynamically added here --}}
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- Project Estimated Expenses Section (Readonly) --}}
          <div class="row mb-4" id="project-estimated-expense-section">
            <div class="col-md-12">
              <div class="card estimated-section">
                <div class="card-header bg-transparent">
                  <h6 class="mb-0">Project Estimated Expenses (Auto-calculated)</h6>
                  <p class="text-muted small mb-0">Sum of all yearly estimated expenses</p>
                </div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-6">
                      <div class="mb-3">
                        <label class="form-label">Total Estimated Expense</label>
                        <div class="input-group">
                          <span class="input-group-text">₹</span>
                          <input type="text" class="form-control readonly-input" id="total-estimated-expense-display"
                            value="0.00" readonly>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        This amount is automatically calculated from yearly estimated expenses breakdown.
                      </div>
                    </div>
                  </div>
                  <input type="hidden" name="estimated_expense" id="estimated_expense" value="0">
                </div>
              </div>
            </div>
          </div>

          {{-- Project Budgeted Expenses Section --}}
          <div class="row mb-4" id="project-budgeted-expense-section">
            <div class="col-md-12">
              <div class="card budgeted-section">
                <div class="card-header bg-transparent">
                  <h6 class="mb-0">Project Budgeted Expenses</h6>
                  <p class="text-muted small mb-0">Budgeted expense allocation</p>
                </div>
                <div class="card-body">
                  <div id="budgeted-expense-components-container">
                    {{-- This will be populated based on checkbox selection --}}
                  </div>

                  <button type="button" id="add-budget-component" class="btn btn-secondary btn-sm mt-2"
                    style="display: none;">
                    <i class="fas fa-plus"></i> Add Custom Component
                  </button>

                  <div class="mt-3">
                    <strong>Total Project Budgeted Expense: ₹<span id="total-budgeted-expense">0.00</span></strong>
                  </div>
                </div>
              </div>
            </div>
          </div>
          {{-- Revenue Section --}}
          <div class="row mb-4">
            <div class="col-md-12">
              <div class="card">
                <div class="card-header">
                  <h6 class="mb-0">Revenue Calculation Summary</h6>
                </div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-4">
                      <div class="mb-3">
                        <label class="form-label">Total Project Budget</label>
                        <div class="input-group">
                          <span class="input-group-text">₹</span>
                          <input type="text" class="form-control readonly-input" id="total-yearly-budget-display"
                            value="0.00" readonly>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="mb-3">
                        <label class="form-label">Total Estimated Expense</label>
                        <div class="input-group">
                          <span class="input-group-text">₹</span>
                          <input type="text" class="form-control readonly-input"
                            id="total-estimated-expense-display-summary" value="0.00" readonly>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="mb-3">
                        <label class="form-label">Expected Revenue</label>
                        <div class="input-group">
                          <span class="input-group-text">₹</span>
                          <input type="text" class="form-control readonly-input" name="revenue" id="revenue" value="0"
                            readonly>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="alert alert-success">
                    <i class="fas fa-calculator me-2"></i>
                    Revenue = Total Budget - Total Estimated Expense
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-12">
              <label for="description" class="form-label">Description *</label>
              <textarea name="description" id="description" class="form-control"
                rows="3">{{ old('description', $proposal->technical_details) }}</textarea>
              @error('description')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>

          {{-- Team Members Section --}}
          @php
          use App\Models\User;
          @endphp
          <div class="mb-3">
            <label class="form-label">Add Team Member *</label>
            <select id="user_selector" class="form-select select2">
              <option value="">Select a user</option>
              @foreach(User::where('id', '!=', auth()->id())->where('active',1)->orderBy('name', 'asc')->get() as $user)
              <option value="{{ $user->id }}">{{ $user->name }}</option>
              @endforeach
            </select>
          </div>

          <div id="selected_users_list">
            <!-- Dynamic list will be appended here -->
          </div>
          <input type="hidden" name="team_members_json" id="team_members_json">

          <div class="mt-4">
            <button type="submit" class="btn btn-primary me-2">Create Project</button>
            <a href="{{ route('pms.proposals.show', $proposal->id) }}" class="btn btn-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Right Sidebar --}}
  {{-- <div class="col-md-4">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title">Proposal Details</h5>
      </div>
      <div class="card-body">
        <p><strong>Client:</strong> {{ $proposal->requirement->client->client_name }}</p>
        <p><strong>Budget:</strong> ₹{{ number_format($proposal->budget, 2) }}</p>
        <p><strong>Duration:</strong> From:
          @if(!is_null($proposal->expected_start_date))
          {{$proposal->expected_start_date->format('d M Y') }}
          @endif to:
          @if(!is_null($proposal->expected_end_date))
          {{ $proposal->expected_end_date->format('d M Y') }}
          @endif
        </p>
        <p><strong>Work Order:</strong>
          @if($proposal->workOrderDocuments->count() > 0)
          <span class="badge bg-success">Uploaded</span>
          @else
          <span class="badge bg-danger">Missing</span>
          @endif
        </p>
      </div>
    </div>

    <div class="card mt-4">
      <div class="card-header">
        <h5 class="card-title">Work Order Documents</h5>
      </div>
      <div class="card-body">
        @if($proposal->workOrderDocuments->count() > 0)
        <div class="list-group">
          @foreach($proposal->workOrderDocuments as $document)
          <div class="list-group-item d-flex justify-content-between align-items-center">
            <div>
              <i class="fas fa-file-contract me-2"></i>
              {{ $document->name }}
            </div>
            <a href="{{ Storage::url($document->path) }}" target="_blank" class="btn btn-sm btn-primary">
              <i class="fas fa-download"></i>
            </a>
          </div>
          @endforeach
        </div>
        @else
        <div class="alert alert-warning">No work order documents uploaded yet.</div>
        @endif
      </div>
    </div>
  </div> --}}
</div>
@endsection