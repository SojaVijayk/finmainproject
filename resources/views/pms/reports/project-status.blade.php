@extends('layouts/layoutMaster')

@section('title', 'Project Status Report')

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

@endsection
@section('header', 'Project Status Report')

@section('content')
<div class="card">
  <div class="card-header">
    <div class="d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Project Status Report</h5>
      <div>
        <a href="{{ route('pms.reports.export', ['type' => 'project-status']) }}?status={{ $statusFilter }}&date_range={{ $dateRange }}"
          class="btn btn-sm btn-success me-2">
          <i class="fas fa-file-export"></i> Export
        </a>
      </div>
    </div>
  </div>
  <div class="card-body">
    <form method="GET" class="mb-4">
      <div class="row">
        <div class="col-md-4">
          <label for="status" class="form-label">Status</label>
          <select name="status" id="status" class="form-select">
            @foreach($statuses as $key => $value)
            <option value="{{ $key }}" {{ $statusFilter==$key ? 'selected' : '' }}>{{ $value }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-4">
          <label for="date_range" class="form-label">Date Range</label>
          <select name="date_range" id="date_range" class="form-select">
            @foreach($dateRanges as $key => $value)
            <option value="{{ $key }}" {{ $dateRange==$key ? 'selected' : '' }}>{{ $value }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-4 d-flex align-items-end">
          <button type="submit" class="btn btn-primary">Apply Filters</button>
        </div>
      </div>
    </form>

    @if($projects->count() > 0)
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Project Code</th>
            <th>Title</th>
            <th>Client</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Status</th>
            <th>Completion</th>
            <th>Budget (₹)</th>
            <th>Dashboard</th>
          </tr>
        </thead>
        <tbody>
          @foreach($projects as $project)
          <tr>
            <td>{{ $project->project_code }}</td>
            <td>{{ $project->title }}</td>
            <td>{{ $project->requirement->client->client_name }}</td>
            <td>{{ $project->start_date->format('d M Y') }}</td>
            <td>{{ $project->end_date->format('d M Y') }}</td>
            <td>
              <span class="badge bg-{{ $project->status_badge_color }}">
                {{ $project->status_name }}
              </span>
            </td>
            <td>
              <div class="progress" style="height: 20px;">
                <div class="progress-bar" role="progressbar" style="width: {{ $project->completion_percentage }}%"
                  aria-valuenow="{{ $project->completion_percentage }}" aria-valuemin="0" aria-valuemax="100">
                  {{ $project->completion_percentage }}%
                </div>
              </div>
            </td>
            <td>{{ number_format($project->budget, 2) }}</td>
            <td><a href="{{ route('pms.projects.dashboard', $project->id) }}" class="btn btn-sm btn-info">
                <i class="fas fa-tachometer-alt"></i> Dashboard
              </a></td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @else
    <div class="alert alert-info">No projects found matching the selected criteria.</div>
    @endif
  </div>
</div>
@endsection