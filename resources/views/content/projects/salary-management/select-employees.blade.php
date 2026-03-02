@extends('layouts/layoutMaster')

@section('title', 'Salary Management - Select Employees')

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">PMS / Salary Management /</span> Step 2: Employee Selection
</h4>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <div>
      <h5 class="mb-0">Select Employees for {{ $month }} {{ $year }}</h5>
      <span class="badge bg-label-info">{{ ucfirst($employmentType) }}</span>
    </div>
    <small class="text-muted">{{ $employees->count() }} Employees Found</small>
  </div>
  <div class="card-body">
    <form action="{{ route('pms.salary-management.calculation', $project_id) }}" method="POST">
      @csrf
      <input type="hidden" name="month" value="{{ $month }}">
      <input type="hidden" name="year" value="{{ $year }}">
      <input type="hidden" name="employment_type" value="{{ $employmentType }}">
      <input type="hidden" name="default_salary_id" value="{{ $defaultSalaryId }}">

      <div class="table-responsive text-nowrap mb-4">
        <table class="table table-hover">
          <thead>
            <tr>
              <th style="width: 50px;">
                <input type="checkbox" id="select_all" class="form-check-input">
              </th>
              <th>Name</th>
              <th>Role</th>
              <th>Department</th>
              <th>Current Pay</th>
            </tr>
          </thead>
          <tbody>
            @forelse($employees as $employee)
            <tr class="{{ $employee->is_frozen ? 'table-secondary text-muted' : '' }}">
              <td>
                @if($employee->is_frozen)
                  <input type="checkbox" class="form-check-input" disabled title="Salary already frozen for this month">
                @else
                  <input type="checkbox" name="selected_employees[]" value="{{ $employee->p_id }}" class="form-check-input employee-checkbox">
                @endif
              </td>
              <td>
                {{ $employee->name }}
                @if($employee->is_frozen)
                  <span class="badge bg-label-secondary ms-2"><i class="ti ti-lock" style="font-size: 10px;"></i> Frozen</span>
                @endif
              </td>
              <td>{{ $employee->role ?? 'N/A' }}</td>
              <td>{{ $employee->department ?? 'N/A' }}</td>
              <td>{{ number_format($employee->consolidated_pay, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center">No active employees found for this type.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="d-flex justify-content-between">
        <a href="{{ route('pms.salary-management.index', $project_id) }}" class="btn btn-label-secondary">Back</a>
        <button type="submit" class="btn btn-primary" id="btn-confirm-selection" {{ $employees->isEmpty() ? 'disabled' : '' }}>Confirm Selection</button>
      </div>
    </form>
  </div>
</div>

@endsection

@section('page-script')
<script>
$(function() {
    // 1. Select All -> Toggle Rows
    $('#select_all').on('change', function() {
        var isChecked = $(this).is(':checked');
        $('.employee-checkbox').prop('checked', isChecked);
        toggleConfirmButton();
    });

    // 2. Row Change -> Update Select All & Button
    $('.employee-checkbox').on('change', function() {
        var allChecked = $('.employee-checkbox').length === $('.employee-checkbox:checked').length;
        $('#select_all').prop('checked', allChecked);
        toggleConfirmButton();
    });

    // 3. Toggle Confirm Button State
    function toggleConfirmButton() {
        var anyChecked = $('.employee-checkbox:checked').length > 0;
        $('#btn-confirm-selection').prop('disabled', !anyChecked);
    }
    
    // Initial Check
    toggleConfirmButton();
});
</script>
@endsection
