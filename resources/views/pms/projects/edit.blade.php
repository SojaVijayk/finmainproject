@extends('layouts/layoutMaster')

@section('title', 'Edit Project')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}">
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
@endsection

@section('page-script')
<script>
  // Pass PHP Data to JS
    const projectData = @json($projectData);
    console.log(projectData);
    const expenseCategories = @json($expenseCategories);
    const financialYears = @json($financialYears);
    const teamMembersInitial = @json($teamMembersData);

    // Main Vue-like Logic (Vanilla JS)
    const App = {
        state: {
            yearlyBudgetCount: 0,
            budgetedComponentCount: 0,
            yearlyComponentCounts: {}, // Track component index per year
            teamMembers: teamMembersInitial || [],
            initialized: false
        },

        init() {
            this.bindEvents();
            this.renderTeamList();
            this.initEditMode();
        },

        initEditMode() {
            // Populate Yearly Sections and Estimates
            if(projectData.yearly_estimates && projectData.yearly_estimates.length > 0) {
                projectData.yearly_estimates.forEach(year => {
                     const index = this.addYearlySection(year.financial_year_id, year.amount, year.notes);
                     if(year.components) {
                         year.components.forEach(comp => {
                             this.addYearlyComponent(index, comp);
                         });
                     }
                });
            }

            // Populate Budgeted Components
            if(projectData.budgeted_components && projectData.budgeted_components.length > 0) {
                 projectData.budgeted_components.forEach(comp => {
                     this.addBudgetedComponent(comp);
                 });
            }

            this.state.initialized = true;
            this.calculateTotals();
            this.updateFYDisabledStates();
        },

        bindEvents() {
            // Add Financial Year Button
            const addYearlyBtn = document.getElementById('add-yearly-budget-btn');
            if(addYearlyBtn) addYearlyBtn.addEventListener('click', () => this.addYearlySection());

            // Add Budgeted Component Button
            const addBudgetBtn = document.getElementById('add-budgeted-component-btn');
            if(addBudgetBtn) addBudgetBtn.addEventListener('click', () => this.addBudgetedComponent());

            // Team Member Logic
            $('#user_selector').on('change', (e) => this.addTeamMember($(e.target).val(), $(e.target).find('option:selected').text()));

            // Watch Start Date for Auto-Init (Optional in Edit, but good for validation)
            const startDateInput = document.getElementById('start_date');
            if(startDateInput) {
                startDateInput.addEventListener('change', () => {
                    this.updateFYDisabledStates();
                });
            }

            // Global Event Delegation for Dynamic Elements
            document.addEventListener('click', (e) => {
                const target = e.target;

                if (target.closest('.remove-yearly-section')) {
                    if(!confirm('Are you sure you want to remove this financial year and all its estimates?')) return;
                    target.closest('.yearly-section-card').remove();
                    this.calculateTotals();
                    this.updateFYDisabledStates();
                }
                if (target.closest('.remove-component')) {
                    if(target.closest('.remove-component').disabled) return;
                    target.closest('.component-row').remove();
                    this.calculateTotals();
                }
                if (target.closest('.remove-budget-component')) {
                    target.closest('.budget-component-row').remove();
                    this.calculateTotals();
                }
                if (target.closest('.add-yearly-component-btn')) {
                    const btn = target.closest('.add-yearly-component-btn');
                    const yearIndex = btn.getAttribute('data-year-index');
                    this.addYearlyComponent(yearIndex);
                }
                if (target.closest('.remove-team-member')) {
                    const idx = target.closest('.remove-team-member').getAttribute('data-index');
                    this.removeTeamMember(idx);
                }
            });

            // Change delegation for FY Selects
            document.addEventListener('change', (e) => {
                if(e.target.classList.contains('fy-select')) {
                    this.updateFYDisabledStates();
                }
            });

            // Input Change Delegation for Totals
            document.addEventListener('input', (e) => {
                if (e.target.matches('.amount-input') || e.target.matches('.yearly-budget-input')) {
                    this.calculateTotals();
                }
            });
        },

        // --- Helper: Reactive FY Disabling ---
        updateFYDisabledStates() {
            const selects = document.querySelectorAll('.fy-select');
            const selectedValues = Array.from(selects).map(s => s.value).filter(v => v);

            selects.forEach(select => {
                const currentVal = select.value;
                Array.from(select.options).forEach(option => {
                    // Skip placeholder
                    if(!option.value) return;

                    // Disable if selected elsewhere AND not self
                    if (selectedValues.includes(option.value) && option.value !== currentVal) {
                        option.disabled = true;
                        option.style.color = '#ccc';
                    } else {
                        option.disabled = false;
                        option.style.color = '';
                    }
                });
            });
        },

        // --- Helper: Get Valid FYs based on Dates ---
        getValidFinancialYears() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;

            if (!startDate || !endDate) return financialYears; // Return all if no dates selected

            return financialYears.filter(fy => {
                // Check overlap: (StartA <= EndB) and (EndA >= StartB)
                return (fy.start_date <= endDate && fy.end_date >= startDate);
            });
        },

        // --- Yearly Estimates Logic ---

        addYearlySection(preselectedFyId = null, prefilledAmount = '', prefilledNotes = '') {
            const index = this.state.yearlyBudgetCount; // Current Count (0-based index for next item)

            // 1. Get List of Already Used Financial Years
            const usedFyIds = Array.from(document.querySelectorAll('select[name^="yearly_estimates"]'))
                                   .map(select => select.value)
                                   .filter(val => val);

            // 2. Generate Valid Options
            const validFYs = this.getValidFinancialYears();

            // 3. Auto-Select Logic (if not preselected)
            let selectedFyId = preselectedFyId || '';
            if (!selectedFyId) {
                const nextAvailable = validFYs.find(y => !usedFyIds.includes(String(y.id)));
                if (nextAvailable) {
                    selectedFyId = nextAvailable.id;
                }
            }

            // 4. Create Options HTML (initial render)
            const optionsHtml = validFYs.map(y => {
                const isSelected = y.id == selectedFyId ? 'selected' : '';
                return `<option value="${y.id}" ${isSelected}>${y.display_name || y.name} (${y.start_date} - ${y.end_date})</option>`;
            }).join('');

            const finalOptions = optionsHtml || '<option value="">No available years match project dates</option>';

            this.state.yearlyComponentCounts[index] = 0;
            this.state.yearlyBudgetCount++;

            const html = `
                <div class="card mb-3 yearly-section-card border border-primary shadow-sm" id="yearly-section-${index}">
                    <div class="card-header bg-label-primary d-flex justify-content-between align-items-center py-2">
                        <h6 class="mb-0 text-primary">Financial Year Allocation</h6>
                        <button type="button" class="btn btn-sm btn-label-danger remove-yearly-section">
                            <i class="fas fa-trash me-1"></i> Remove Year
                        </button>
                    </div>
                    <div class="card-body pt-3">
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Select Financial Year *</label>
                                <select name="yearly_estimates[${index}][financial_year_id]" class="form-select fy-select" required>
                                    <option value="">Choose Year</option>
                                    ${finalOptions}
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Total Budget for This Year (₹) *</label>
                                <input type="number" step="0.01" name="yearly_estimates[${index}][amount]" class="form-control yearly-budget-input" required placeholder="0.00" value="${prefilledAmount}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Notes</label>
                                <input type="text" name="yearly_estimates[${index}][notes]" class="form-control" placeholder="Optional notes" value="${prefilledNotes || ''}">
                            </div>
                        </div>

                        <hr class="my-3">

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0 text-secondary">Estimated Expense Components</h6>
                             <button type="button" class="btn btn-xs btn-outline-primary add-yearly-component-btn" data-year-index="${index}">
                                <i class="fas fa-plus me-1"></i> Add Component
                            </button>
                        </div>

                        <div class="yearly-components-container" id="yearly-components-${index}">
                            <!-- Dynamic Components Go Here -->
                        </div>
                    </div>
                    <!-- Year Footer Highlights -->
                    <div class="card-footer bg-lighter py-2 border-top">
                        <div class="d-flex justify-content-around text-small fw-bold">
                            <span>Budget: ₹<span class="year-budget-display" id="year-budget-${index}">0.00</span></span>
                            <span>Expenses: ₹<span class="year-expense-display text-warning" id="year-expense-${index}">0.00</span></span>
                            <span>Revenue: ₹<span class="year-revenue-display text-success" id="year-revenue-${index}">0.00</span></span>
                        </div>
                    </div>
                </div>
            `;

            document.getElementById('yearly-sections-container').insertAdjacentHTML('beforeend', html);

            // Trigger reactive disable check immediately to ensure state is consistent
            this.updateFYDisabledStates();

            // If this is a new year being added manually (not during initialization with preselectedFyId)
            // and there are no components being passed, add default components
            if (!preselectedFyId) {
                // Add comprehensive default components for new year
                const defaultComponents = [
                    // HR Components with correct rates
                    { group: 'HR', component: 'Manpower-Faculty Cost', mandays: 0.5, rate: 14000, amount: 7000 },
                    { group: 'HR', component: 'Manpower-Sr Faculty Associate Cost', mandays: 0, rate: 8000, amount: 0 },
                    { group: 'HR', component: 'Manpower-Faculty Associate Cost', mandays: 0, rate: 6000, amount: 0 },
                    { group: 'HR', component: 'Manpower-Project Staff', mandays: 0, rate: 3200, amount: 0 },
                    { group: 'HR', component: 'Manpower-Consultants', mandays: 0, rate: 8000, amount: 0 },

                    // Travel Components
                    { group: 'Travel', component: 'Domestic Travel', amount: 0 },
                    { group: 'Travel', component: 'International Travel', amount: 0 },
                    { group: 'Travel', component: 'Local Conveyance', amount: 0 },

                    // Others Components
                    { group: 'Others', component: 'Consumables', amount: 0 },
                    { group: 'Others', component: 'Contingency', amount: 0 },
                    { group: 'Others', component: 'Equipment', amount: 0 },
                    { group: 'Others', component: 'Overhead', amount: 0 }
                ];
                defaultComponents.forEach(comp => {
                    this.addYearlyComponent(index, comp);
                });
            }

            return index;
        },

        addYearlyComponent(yearIndex, data = null) {
            const compIndex = this.state.yearlyComponentCounts[yearIndex]++;
            const isCustom = !data;
            const prefix = `yearly_estimates[${yearIndex}][components][${compIndex}]`;

            const group = data ? data.group : '';
            const component = data ? data.component : '';
            const amount = data ? data.amount : '';
            const categoryId = data ? data.category_id : '';
            // Use data.mandays if it exists (including 0), otherwise default to 0
            // Handle both numeric and string values from database
            let mandays = 0;
            if (data && data.mandays !== undefined && data.mandays !== null) {
                mandays = parseFloat(data.mandays) || 0;
            }
            console.log('Component:', component, 'Data mandays:', data?.mandays, 'Final mandays:', mandays);

            // Default rates for HR components
            const defaultRates = {
                'Manpower-Faculty Cost': 14000,
                'Manpower-Sr Faculty Associate Cost': 8000,
                'Manpower-Faculty Associate Cost': 6000,
                'Manpower-Project Staff': 3200,
                'Manpower-Consultants': 8000
            };

            // Use data rate if provided, otherwise lookup default rate by component name
            let rate = '';
            if (data && data.rate !== undefined && data.rate !== null && data.rate !== '') {
                rate = data.rate;
            } else if (component && defaultRates[component]) {
                rate = defaultRates[component];
            } else {
                rate = data ? (data.rate || '') : '';
            }

            // Mandatory Logic
            const mandatoryGroups = ['HR', 'Travel', 'Others'];
            const isMandatory = mandatoryGroups.includes(group);
            const defaultGroup = isCustom ? 'Custom' : group;

            const categoryOptions = expenseCategories.map(c =>
                `<option value="${c.id}" ${c.id == categoryId ? 'selected' : ''}>${c.name}</option>`
            ).join('');

            const removeBtnHtml = isMandatory
                ? `<button type="button" class="btn btn-sm btn-icon btn-secondary" disabled title="Mandatory Component"><i class="fas fa-lock"></i></button>`
                : `<button type="button" class="btn btn-sm btn-icon btn-label-danger remove-component"><i class="fas fa-times"></i></button>`;

            // For HR components, show mandays and rate fields
            const isHRComponent = group === 'HR';

            let componentFieldsHtml = '';
            if (isHRComponent) {
                // HR components: mandays (editable) + rate (editable) = amount (readonly, calculated)
                componentFieldsHtml = `
                    <div class="col-md-2">
                        <label class="form-label small">Mandays</label>
                        <input type="number" step="0.01" name="${prefix}[mandays]"
                               class="form-control form-control-sm mandays-input"
                               value="${mandays}"
                               data-prefix="${prefix}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Rate (₹)</label>
                        <input type="number" step="0.01" name="${prefix}[rate]"
                               class="form-control form-control-sm rate-input"
                               value="${rate}"
                               data-prefix="${prefix}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Amount (₹)</label>
                        <input type="number" step="0.01" name="${prefix}[amount]"
                               class="form-control form-control-sm amount-input"
                               value="${amount}"
                               readonly
                               style="background-color: #e9ecef;">
                    </div>
                `;
            } else {
                // Non-HR components: only amount field (editable)
                componentFieldsHtml = `
                    <input type="hidden" name="${prefix}[mandays]" value="">
                    <input type="hidden" name="${prefix}[rate]" value="">
                    <div class="col-md-2">
                        <label class="form-label small">Amount (₹)</label>
                        <input type="number" step="0.01" name="${prefix}[amount]"
                               class="form-control form-control-sm amount-input"
                               value="${amount}"
                               required>
                    </div>
                `;
            }

            const html = `
                <div class="row g-2 mb-2 align-items-end component-row bg-light p-2 rounded">
                    <div class="col-md-2">
                        <label class="form-label small">Group</label>
                        <input type="text" name="${prefix}[group]" class="form-control form-control-sm" value="${defaultGroup}" required ${isMandatory ? 'readonly' : ''}>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Component Name</label>
                        <input type="text" name="${prefix}[component]" class="form-control form-control-sm" value="${component}" required placeholder="e.g. Faculty" ${isMandatory ? 'readonly' : ''}>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Category</label>
                        <select name="${prefix}[category_id]" class="form-select form-select-sm">
                            <option value="">Select</option>
                            ${categoryOptions}
                        </select>
                    </div>
                    ${componentFieldsHtml}
                    <div class="col-md-1 text-center">
                        ${removeBtnHtml}
                    </div>
                </div>
            `;

            document.getElementById(`yearly-components-${yearIndex}`).insertAdjacentHTML('beforeend', html);

            // Add event listeners for auto-calculation (for HR components)
            if (isHRComponent) {
                const lastRow = document.getElementById(`yearly-components-${yearIndex}`).lastElementChild;
                const mandaysInput = lastRow.querySelector('.mandays-input');
                const rateInput = lastRow.querySelector('.rate-input');
                const amountInput = lastRow.querySelector('.amount-input');

                const calculateAmount = () => {
                    const mandays = parseFloat(mandaysInput.value) || 0;
                    const rate = parseFloat(rateInput.value) || 0;
                    const amount = mandays * rate;
                    amountInput.value = amount.toFixed(2);
                    this.calculateTotals();
                };

                mandaysInput.addEventListener('input', calculateAmount);
                rateInput.addEventListener('input', calculateAmount);
            }

            this.calculateTotals();
        },

        // --- Budgeted Expenses Logic ---

        addBudgetedComponent(data = null) {
            const index = this.state.budgetedComponentCount++;
            const prefix = `budgeted_components[${index}]`;

            const group = data ? data.group : '';
            const component = data ? data.component : '';
            const amount = data ? data.amount : '';
            const categoryId = data ? data.category_id : '';
            // Use data.mandays if it exists (including 0), otherwise default to 0
            // Handle both numeric and string values from database
            let mandays = 0;
            if (data && data.mandays !== undefined && data.mandays !== null) {
                mandays = parseFloat(data.mandays) || 0;
            }
            console.log('Budgeted Component:', component, 'Data mandays:', data?.mandays, 'Final mandays:', mandays);

            // Default rates for HR components
            const defaultRates = {
                'Manpower-Faculty Cost': 14000,
                'Manpower-Sr Faculty Associate Cost': 8000,
                'Manpower-Faculty Associate Cost': 6000,
                'Manpower-Project Staff': 3200,
                'Manpower-Consultants': 8000
            };

            // Use data rate if provided, otherwise lookup default rate by component name
            let rate = '';
            if (data && data.rate !== undefined && data.rate !== null && data.rate !== '') {
                rate = data.rate;
            } else if (component && defaultRates[component]) {
                rate = defaultRates[component];
            } else {
                rate = data ? (data.rate || '') : '';
            }

            // Mandatory Logic
            const mandatoryGroups = ['HR', 'Travel', 'Others'];
            const isMandatory = mandatoryGroups.includes(group);
            const defaultGroup = !data ? 'Custom' : group; // If adding empty, default to Custom (editable)

            const categoryOptions = expenseCategories.map(c =>
                `<option value="${c.id}" ${c.id == categoryId ? 'selected' : ''}>${c.name}</option>`
            ).join('');

            const removeBtnHtml = isMandatory
                ? `<button type="button" class="btn btn-sm btn-icon btn-secondary" disabled title="Mandatory Component"><i class="fas fa-lock"></i></button>`
                : `<button type="button" class="btn btn-sm btn-icon btn-label-danger remove-budget-component"><i class="fas fa-times"></i></button>`;

            // For HR components, show mandays and rate fields
            const isHRComponent = group === 'HR';

            let componentFieldsHtml = '';
            if (isHRComponent) {
                // HR components: mandays (editable) + rate (editable) = amount (readonly, calculated)
                componentFieldsHtml = `
                    <div class="col-md-2">
                        <label class="form-label small">Mandays</label>
                        <input type="number" step="0.01" name="${prefix}[mandays]"
                               class="form-control form-control-sm budget-mandays-input"
                               value="${mandays}"
                               data-prefix="${prefix}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Rate (₹)</label>
                        <input type="number" step="0.01" name="${prefix}[rate]"
                               class="form-control form-control-sm budget-rate-input"
                               value="${rate}"
                               data-prefix="${prefix}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Amount (₹)</label>
                        <input type="number" step="0.01" name="${prefix}[amount]"
                               class="form-control form-control-sm amount-input budget-amount-input"
                               value="${amount}"
                               readonly
                               style="background-color: #e9ecef;">
                    </div>
                `;
            } else {
                // Non-HR components: only amount field (editable)
                componentFieldsHtml = `
                    <input type="hidden" name="${prefix}[mandays]" value="">
                    <input type="hidden" name="${prefix}[rate]" value="">
                    <div class="col-md-2">
                        <label class="form-label small">Amount (₹)</label>
                        <input type="number" step="0.01" name="${prefix}[amount]"
                               class="form-control form-control-sm amount-input budget-amount-input"
                               value="${amount}"
                               required>
                    </div>
                `;
            }

            const html = `
                <div class="row g-2 mb-2 align-items-end budget-component-row border-bottom pb-2">
                    <div class="col-md-2">
                        <label class="form-label small">Group</label>
                        <input type="text" name="${prefix}[group]" class="form-control form-control-sm" value="${defaultGroup}" required ${isMandatory ? 'readonly' : ''}>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Component</label>
                        <input type="text" name="${prefix}[component]" class="form-control form-control-sm" value="${component}" required ${isMandatory ? 'readonly' : ''}>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Category</label>
                        <select name="${prefix}[category_id]" class="form-select form-select-sm">
                            <option value="">Select</option>
                            ${categoryOptions}
                        </select>
                    </div>
                    ${componentFieldsHtml}
                    <div class="col-md-1 text-center">
                        ${removeBtnHtml}
                    </div>
                </div>
            `;

            document.getElementById('budgeted-components-container').insertAdjacentHTML('beforeend', html);

            // Add event listeners for auto-calculation (for HR components)
            if (isHRComponent) {
                const lastRow = document.getElementById('budgeted-components-container').lastElementChild;
                const mandaysInput = lastRow.querySelector('.budget-mandays-input');
                const rateInput = lastRow.querySelector('.budget-rate-input');
                const amountInput = lastRow.querySelector('.budget-amount-input');

                const calculateAmount = () => {
                    const mandays = parseFloat(mandaysInput.value) || 0;
                    const rate = parseFloat(rateInput.value) || 0;
                    const amount = mandays * rate;
                    amountInput.value = amount.toFixed(2);
                    this.calculateTotals();
                };

                mandaysInput.addEventListener('input', calculateAmount);
                rateInput.addEventListener('input', calculateAmount);
            }

            this.calculateTotals();
        },

        // --- Totals Logic ---

        calculateTotals() {
            let totalYearlyBudget = 0;
            let totalEstimated = 0;
            let totalBudgeted = 0;

            // Iterate through each Yearly Section to calc local totals
            const sections = document.querySelectorAll('.yearly-section-card');
            sections.forEach(section => {
                const yearIndex = section.id.replace('yearly-section-', '');

                // Local Totals
                const budgetInput = section.querySelector('.yearly-budget-input');
                const localBudget = parseFloat(budgetInput.value) || 0;

                let localExpense = 0;
                section.querySelectorAll('.yearly-components-container .amount-input').forEach(input => {
                    localExpense += parseFloat(input.value) || 0;
                });

                const localRevenue = localBudget - localExpense;

                // Update Local Highlights
                const elLocalBudget = document.getElementById(`year-budget-${yearIndex}`);
                const elLocalExpense = document.getElementById(`year-expense-${yearIndex}`);
                const elLocalRevenue = document.getElementById(`year-revenue-${yearIndex}`);

                if(elLocalBudget) elLocalBudget.textContent = localBudget.toFixed(2);
                if(elLocalExpense) elLocalExpense.textContent = localExpense.toFixed(2);
                if(elLocalRevenue) {
                     elLocalRevenue.textContent = localRevenue.toFixed(2);
                     elLocalRevenue.className = `year-revenue-display ${localRevenue < 0 ? 'text-danger' : 'text-success'}`;
                }

                // Add to Global Totals
                totalYearlyBudget += localBudget;
                totalEstimated += localExpense;
            });

            // Budgeted Total
            document.querySelectorAll('.budget-amount-input').forEach(input => {
                totalBudgeted += parseFloat(input.value) || 0;
            });

            // Update Budgeted Section Footer
            const elBudgetedSectionTotal = document.getElementById('budgeted-section-total');
            if(elBudgetedSectionTotal) elBudgetedSectionTotal.textContent = totalBudgeted.toFixed(2);

            // Update Global Summary Sidebar
            const elTotalBudget = document.getElementById('display-total-budget');
            if(elTotalBudget) elTotalBudget.textContent = totalYearlyBudget.toFixed(2);

            const elTotalEstimated = document.getElementById('display-total-estimated');
            if(elTotalEstimated) elTotalEstimated.textContent = totalEstimated.toFixed(2);

            const elTotalBudgeted = document.getElementById('display-total-budgeted');
            if(elTotalBudgeted) elTotalBudgeted.textContent = totalBudgeted.toFixed(2);

            const revenue = totalYearlyBudget - totalEstimated;

            const elRevenue = document.getElementById('display-revenue');
            if(elRevenue) elRevenue.textContent = revenue.toFixed(2);

            const elRevenueInput = document.getElementById('revenue_input');
            if(elRevenueInput) elRevenueInput.value = revenue.toFixed(2);

            const elBudgetInput = document.getElementById('budget_input');
            if(elBudgetInput) elBudgetInput.value = totalYearlyBudget.toFixed(2);
        },

        // --- Team Member Logic ---
        addTeamMember(userId, userName) {
            if(!userId) return;
            if(this.state.teamMembers.some(m => m.user_id == userId)) return;

            this.state.teamMembers.push({
                user_id: userId,
                name: userName,
                role: 'member',
                expected_time: 0
            });
            this.renderTeamList();
            $('#user_selector').val(null).trigger('change');
        },

        removeTeamMember(index) {
            this.state.teamMembers.splice(index, 1);
            this.renderTeamList();
        },

        renderTeamList() {
            const html = this.state.teamMembers.map((member, idx) => `
                <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-lighter rounded">
                    <div>
                        <strong>${member.name}</strong>
                    </div>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" onchange="App.updateMember(${idx}, 'role', this.value)">
                            <option value="lead" ${member.role == 'lead' ? 'selected' : ''}>Lead</option>
                                <option value="leadMember" ${member.role == 'leadMember' ? 'selected' : ''}>leadMember (Data Entry Enabled Memebr)</option>
                            <option value="member" ${member.role == 'member' ? 'selected' : ''}>Member</option>
                        </select>
                        <input type="number" class="form-control form-control-sm" style="width: 80px" placeholder="Hours"
                            value="${member.expected_time}" onchange="App.updateMember(${idx}, 'expected_time', this.value)">
                        <button type="button" class="btn btn-sm btn-icon btn-danger remove-team-member" data-index="${idx}">&times;</button>
                    </div>
                </div>
            `).join('');
            document.getElementById('team-list-container').innerHTML = html;
            document.getElementById('team_members_json').value = JSON.stringify(this.state.teamMembers);
        },

        updateMember(index, field, value) {
            this.state.teamMembers[index][field] = value;
            document.getElementById('team_members_json').value = JSON.stringify(this.state.teamMembers);
        }
    };

    // Initialize on Load
    document.addEventListener('DOMContentLoaded', () => App.init());
</script>
@endsection

@section('content')
<div class="row">
  <div class="col-md-9">
    @if ($errors->any())
    <div class="alert alert-danger">
      <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
    @endif
    <form action="{{ route('pms.projects.update', $project->id) }}" method="POST" enctype="multipart/form-data">
      @csrf
      @method('PUT')

      <!-- Project Header -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="card-title mb-0">Edit Project: {{ $project->title }}</h5>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Project Title *</label>
              <input type="text" name="title" class="form-control" value="{{ old('title', $project->title) }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Principal Investigator *</label>
              <select name="project_investigator_id" class="form-select"
                style="pointer-events: none; background-color: #e9ecef;">
                @foreach($faculty as $user)
                <option value="{{ $user->id }}" {{ $project->project_investigator_id == $user->id ? 'selected' : ''
                  }}>{{ $user->name }}</option>
                @endforeach
              </select>
              <div class="mt-2">
                <label class="form-label small">PI Expected Time (Hours) *</label>
                <input type="number" step="0.1" name="pi_expected_time" class="form-control form-control-sm" required
                  min="0" value="{{ $projectData['pi_expected_time'] }}">
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Start Date *</label>
              <input type="date" name="start_date" id="start_date" class="form-control"
                value="{{ old('start_date', $projectData['start_date']) }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">End Date *</label>
              <input type="date" name="end_date" id="end_date" class="form-control"
                value="{{ old('end_date', $projectData['end_date']) }}" required>
            </div>
          </div>
        </div>
      </div>

      <!-- Yearly Estimates & Budgets (Dynamic) -->
      <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0">Yearly Estimates & Budgets</h5>
          <button type="button" class="btn btn-primary" id="add-yearly-budget-btn">
            <i class="fas fa-plus me-1"></i> Add Financial Year
          </button>
        </div>

        <div id="yearly-sections-container">
          <!-- Dynamic Yearly Sections will appear here via JS -->
        </div>
        <div class="alert alert-info small">
          <i class="fas fa-info-circle me-1"></i> Manage financial year budgets and expense estimates.
        </div>
      </div>

      <!-- Budgeted Expenses (Overall) -->
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">Approved Budget Breakdown (Overall)</h5>
          <button type="button" class="btn btn-sm btn-outline-primary" id="add-budgeted-component-btn">
            <i class="fas fa-plus me-1"></i> Add Item
          </button>
        </div>
        <div class="card-body">
          <div id="budgeted-components-container">
            <!-- Dynamic Budgeted Components -->
          </div>
          <div class="d-flex justify-content-end mt-3 pt-2 border-top">
            <h6 class="mb-0">Total Budgeted: <span class="text-primary">₹<span
                  id="budgeted-section-total">0.00</span></span></h6>
          </div>
        </div>
      </div>

      <!-- Description & Team -->
      <div class="card mb-4">
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">Description / Technical Details</label>
            <textarea name="description" class="form-control"
              rows="3">{{ old('description', $project->description) }}</textarea>
          </div>
          @php
          use App\Models\User;
          @endphp
          <div class="mb-3">
            <label class="form-label">Add Team Members</label>
            <select id="user_selector" class="form-select select2">
              <option value="">Search User...</option>
              @foreach(User::where('id', '!=', auth()->id())->where('active', 1)->orderBy('name')->get() as $u)
              <option value="{{ $u->id }}">{{ $u->name }}</option>
              @endforeach
            </select>
            <div id="team-list-container" class="mt-2"></div>
            <input type="hidden" name="team_members_json" id="team_members_json">
          </div>
        </div>
      </div>

      <!-- Hidden Totals for Submission -->
      <input type="hidden" name="budget" id="budget_input" value="0">
      <input type="hidden" name="revenue" id="revenue_input" value="0">

      <div class="d-flex justify-content-end mb-5">
        <a href="{{ route('pms.projects.show', $project->id) }}" class="btn btn-label-secondary me-3">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">Update Project</button>
      </div>
    </form>
  </div>

  <!-- Right Sidebar Summary -->
  <div class="col-md-3">
    <div class="card mb-3 sticky-top" style="top: 20px; z-index: 100;">
      <div class="card-header bg-primary text-white">
        <h5 class="card-title mb-0 text-white">Project Summary</h5>
      </div>
      <div class="card-body pt-3">
        <div class="mb-2 d-flex justify-content-between">
          <span>Total Budget:</span>
          <strong class="text-primary">₹<span id="display-total-budget">0.00</span></strong>
        </div>
        <div class="mb-2 d-flex justify-content-between">
          <span>Est. Expenses:</span>
          <strong class="text-warning">₹<span id="display-total-estimated">0.00</span></strong>
        </div>
        <hr>
        <div class="mb-2 d-flex justify-content-between">
          <span>Revenue:</span>
          <strong class="text-success">₹<span id="display-revenue">0.00</span></strong>
        </div>

        <div class="mt-4 pt-4 border-top">
          <small class="text-muted d-block mb-1">Budgeted Breakdown Total:</small>
          <strong>₹<span id="display-total-budgeted">0.00</span></strong>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection