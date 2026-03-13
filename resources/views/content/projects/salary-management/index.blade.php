@extends('layouts/layoutMaster')

@section('title', 'Salary Management - Selection')

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">PMS /</span> Salary Management
</h4>

<div class="row">
  <div class="col-md-6 mx-auto">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Step 1: Selection</h5>
        <small class="text-muted float-end">Payroll Period & Type</small>
      </div>
      <div class="card-body">
        <form action="{{ route('pms.salary-management.select-employees', $project_id) }}" method="POST">
          @csrf
          <div class="mb-3">
            <label class="form-label" for="month">Select Month</label>
            <select id="month" name="month" class="form-select" required>
              @foreach($months as $month)
                @php 
                  $selectedMonth = session('payroll_month', date('F'));
                @endphp
                <option value="{{ $month }}" {{ $month == $selectedMonth ? 'selected' : '' }}>{{ $month }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label" for="year">Select Year</label>
            <select id="year" name="year" class="form-select" required>
              @foreach($years as $year)
                @php
                  $selectedYear = session('payroll_year', date('Y'));
                @endphp
                <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>{{ $year }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label" for="employment_type">Employment Type</label>
            <select id="employment_type" name="employment_type" class="form-select" required>
              <option value="">Select Type</option>
              @foreach($employmentTypes as $type)
                <option value="{{ $type->id }}" {{ (old('employment_type') == $type->id || session('payroll_employment_type_id') == $type->id) ? 'selected' : '' }}>{{ $type->employment_type }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label" for="default_salary_id">Salary ID / Batch Reference (Required)</label>
            <input type="text" id="default_salary_id" name="default_salary_id" class="form-control" placeholder="e.g. SAL-FEB-2024" value="{{ session('payroll_default_salary_id') }}" required>
            <div class="form-text">This value will be pre-filled for all employees.</div>
          </div>
          <div class="d-flex justify-content-between gap-3">
            <a href="{{ route('pms.employees.project-index', ['id' => $project_id]) }}" class="btn btn-label-secondary w-100">Back</a>
            <button type="submit" class="btn btn-primary w-100">Confirm & Continue</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
