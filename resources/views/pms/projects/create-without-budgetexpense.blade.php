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
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}">

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
    // Initialize select2 for team members
    {{--  $('#team_members').select2({
        placeholder: 'Select team members',
        width: '100%'
    });  --}}

    // Set end date minimum based on start date
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');

    startDateInput.addEventListener('change', function() {
        endDateInput.min = this.value;

        // If end date is before start date, reset it
        if (endDateInput.value && endDateInput.value < this.value) {
            endDateInput.value = '';
        }
    });


    // Expense Components Management
    const container = document.getElementById('expense-components-container');
    const addButton = document.getElementById('add-component');
    const totalExpenseSpan = document.getElementById('total-expense');
    const estimatedExpenseInput = document.getElementById('estimated_expense');
    {{--  let componentCount = {{ $proposal->expenseComponents->count() }};  --}}
     let componentCount = 1;
    // Add new component row
    // ✅ Add Custom Component Row
  addButton.addEventListener('click', function () {
    const template = document.getElementById('custom-component-template');
    const newRow = template.cloneNode(true);
    newRow.id = '';
    newRow.classList.remove('d-none');

    // Update names with new index
    newRow.querySelectorAll('input, select').forEach(el => {
      el.name = el.name.replace('[0]', `[custom_${componentCount}]`);
      el.value = '';
    });

    // Add remove button event
    const removeBtn = newRow.querySelector('.remove-component');
    if (removeBtn) {
      removeBtn.style.display = 'block';
      removeBtn.addEventListener('click', function () {
        newRow.remove();
        calculateTotalExpense();
      });
    }

    // Add amount change listener
    newRow.querySelector('.expense-amount').addEventListener('input', calculateTotalExpense);

    // Append new row
    container.appendChild(newRow);
    componentCount++;
  });

    // ✅ Prevent removal of non-Custom components
  document.querySelectorAll('.expense-component').forEach(row => {
    const group = row.querySelector('input[name*="[group]"]')?.value;
    const removeBtn = row.querySelector('.remove-component');
    if (group !== 'Custom' && removeBtn) removeBtn.style.display = 'none';
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
     {{--  document.querySelectorAll('.mandays-input').forEach(input => {
        input.addEventListener('input', calculateTotalExpense);
    });  --}}


    document.querySelectorAll('.mandays-input').forEach(input => {
  input.addEventListener('input', function() {
    const target = this.dataset.target;
    const rateField = document.querySelector(`.rate-input[data-target="${target}"]`);
    const amountField = document.getElementById(`amount_${target}`);
    const rate = parseFloat(rateField.value) || 0;
    const mandays = parseFloat(this.value) || 0;
    const amount = mandays * rate;
    amountField.value = amount.toFixed(2);
    calculateTotalExpense();
  });

});

// Disable remove button for non-Custom
document.querySelectorAll('.expense-component').forEach(row => {
  const group = row.querySelector('input[name*="[group]"]').value;
  if (group !== 'Custom') {
    const removeBtn = row.querySelector('.remove-component');
    if (removeBtn) removeBtn.style.display = 'none';
  }
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

    {{--  const expenseInput = document.getElementById('estimated_expense');
    const revenueInput = document.getElementById('revenue');
    function calculateRevenue() {
      const budget = parseFloat(budgetInput.value) || 0;
      const expense = parseFloat(expenseInput.value) || 0;
      const revenue = budget - expense;
      revenueInput.value = revenue >= 0 ? revenue.toFixed(2) : 0;
    }

    budgetInput.addEventListener('input', calculateRevenue);
    expenseInput.addEventListener('input', calculateRevenue);  --}}


});

let teamMembers = [];

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
 $('.select2').select2({ width: 'resolve' });
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
        <h5 class="card-title">From Proposal: {{ $proposal->requirement->temp_no }}</h5>
      </div>
      <div class="card-body">
        {{-- Hidden Template for New Custom Components --}}
        <div class="expense-component row g-3 mb-2 d-none" id="custom-component-template">
          <input type="hidden" name="expense_components[0][group]" value="Custom">
          <div class="col-md-3">
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
          <div class="col-md-1 d-flex align-items-end">
            <button type="button" class="btn btn-danger remove-component">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </div>
        <form action="{{ route('pms.projects.store', $proposal->id) }}" method="POST">
          @csrf

          <div class="row mb-3">
            <div class="col-md-6">
              <label for="title" class="form-label">Project Title *</label>
              {{-- <input type="text" name="title" id="title" class="form-control"
                value="{{ old('title', $proposal->requirement->client->client_name . ' - ' . $proposal->requirement->category->name) }}"
                required> --}}
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
                  @endif
                  {{ old('project_investigator_id')==$user->id ? 'selected' : '' }}>{{
                  $user->name }}</option>
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

          <div class="row mb-3">
            <div class="col-md-4">
              <label for="budget" class="form-label">Budget (₹ Without Tax) *</label>
              <input type="number" step="0.01" min="0" name="budget" id="budget" class="form-control"
                value="{{ old('budget', $proposal->budget) }}" required>
              @error('budget')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
            <!-- Expense Components Section -->
            <div class="row mb-3">
              <div class="col-md-12">
                <label class="form-label">Estimated Expense Components *</label>
                <div id="expense-components-container">

                  @php
                  $groupedComponents = $proposal->expenseComponents->groupBy('group_name');
                  @endphp

                  @foreach($groupedComponents as $groupName => $components)
                  <h6 class="mt-3 mb-2 text-primary fw-bold border-bottom pb-1">{{ $groupName }}</h6>

                  @foreach($components as $index => $component)
                  <div class="expense-component row g-3 mb-2">
                    <input type="hidden" name="expense_components[{{ $groupName }}_{{ $index }}][category_id]"
                      value="{{ $component->expense_category_id }}">
                    <input type="hidden" name="expense_components[{{ $groupName }}_{{ $index }}][group]"
                      value="{{ $groupName }}">
                    <input type="hidden" name="expense_components[{{ $groupName }}_{{ $index }}][component]"
                      value="{{ $component->component }}">

                    {{-- Readonly Component Name --}}
                    <div class="col-md-3">
                      <label class="form-label">Component</label>
                      <input type="text" class="form-control" value="{{ $component->component }}" readonly>
                    </div>

                    {{-- HR Group: show Mandays & Rate --}}
                    @if($groupName === 'HR')
                    <div class="col-md-2">
                      <label class="form-label">Persondays</label>
                      <input type="number" name="expense_components[{{ $groupName }}_{{ $index }}][mandays]"
                        class="form-control mandays-input" value="{{ $component->mandays }}" min="0" step="0.1"
                        data-target="{{ $groupName }}_{{ $index }}">
                    </div>

                    <div class="col-md-2">
                      <label class="form-label">Rate (₹)</label>
                      <input type="number" name="expense_components[{{ $groupName }}_{{ $index }}][rate]"
                        class="form-control rate-input" value="{{ $component->rate }}"
                        data-target="{{ $groupName }}_{{ $index }}" readonly>
                    </div>
                    @endif

                    {{-- Amount --}}
                    <div class="col-md-2">
                      <label class="form-label">Amount (₹)</label>
                      <input type="number" step="0.01" min="0"
                        name="expense_components[{{ $groupName }}_{{ $index }}][amount]"
                        class="form-control expense-amount" id="amount_{{ $groupName }}_{{ $index }}"
                        value="{{ $component->amount }}">
                    </div>

                    {{-- Remove button only for Custom --}}
                    <div class="col-md-1 d-flex align-items-end">
                      @if($groupName === 'Custom')
                      <button type="button" class="btn btn-danger remove-component"><i
                          class="fas fa-trash"></i></button>
                      @endif
                    </div>
                  </div>
                  @endforeach
                  @endforeach

                </div>

                {{-- Add button only for Custom group --}}
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
            </div>
            {{-- <div class="col-md-4">
              <label for="estimated_expense" class="form-label">Estimated Expense (₹ Without Tax)</label>
              <input type="number" step="0.01" min="0" name="estimated_expense" id="estimated_expense"
                class="form-control" value="{{ old('estimated_expense', $proposal->estimated_expense) }}" required>
              @error('estimated_expense')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div> --}}
            <div class="col-md-4">
              <label for="revenue" class="form-label">Expected Revenue (₹ Without Tax) </label>
              <input type="number" step="0.01" min="0" name="revenue" id="revenue" class="form-control"
                value="{{ old('revenue', $proposal->revenue) }}" required>
              @error('revenue')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
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

          {{-- <div class="row mb-3">
            <div class="col-md-12">
              <label for="team_members" class="form-label">Team Members</label>
              <select name="team_members[]" id="team_members" class="form-select" multiple>
                @php
                use App\Models\User;
                @endphp
                @foreach(User::where('id', '!=', auth()->id())->get() as $user)
                <option value="{{ $user->id }}" {{ in_array($user->id, old('team_members', [])) ? 'selected' : '' }}>{{
                  $user->name }}</option>
                @endforeach
              </select>
              @error('team_members')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div> --}}
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

  <div class="col-md-4">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title">Proposal Details </h5>
      </div>
      <div class="card-body">
        <p><strong>Client:</strong> {{ $proposal->requirement->client->client_name }}</p>
        <p><strong>Budget:</strong> ₹{{ number_format($proposal->budget, 2) }}</p>
        <p><strong>Duration:</strong> From: @if(!is_null($proposal->expected_start_date))
          {{$proposal->expected_start_date->format('d M Y') }}@endif to:
          @if(!is_null($proposal->expected_end_date)) {{ $proposal->expected_end_date->format('d M Y') }} @endif</p>
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
  </div>
</div>
@endsection