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
                <option value="{{ $month }}" {{ $month == date('F') ? 'selected' : '' }}>{{ $month }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label" for="year">Select Year</label>
            <select id="year" name="year" class="form-select" required>
              @foreach($years as $year)
                <option value="{{ $year }}">{{ $year }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label" for="employment_type">Employment Type</label>
            <select id="employment_type" name="employment_type" class="form-select" required>
              <option value="">Select Type</option>
              @foreach($employmentTypes as $type)
                <option value="{{ $type }}">{{ ucfirst($type) }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label" for="default_salary_id">Salary ID / Batch Reference (Required)</label>
            <input type="text" id="default_salary_id" name="default_salary_id" class="form-control" placeholder="e.g. SAL-FEB-2024" required>
            <div class="form-text">This value will be pre-filled for all employees.</div>
          </div>
          <div class="text-center">
            <button type="submit" class="btn btn-primary d-grid w-100">Confirm & Continue</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
