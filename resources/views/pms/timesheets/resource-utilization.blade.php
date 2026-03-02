@extends('layouts/layoutMaster')

@section('title', 'Resource Utilization Report')

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
@section('header', 'Resource Utilization Report')

@section('content')
<div class="card">
  <div class="card-header">
    <div class="d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Resource Utilization Report</h5>
      <div>
        <a href="{{ route('pms.timesheets.export', ['type' => 'resource-utilization']) }}?start_date={{ request('start_date') }}&end_date={{ request('end_date') }}"
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
          <label for="start_date" class="form-label">Start Date</label>
          <input type="date" name="start_date" id="start_date" class="form-control"
            value="{{ $startDate->format('Y-m-d') }}" required>
        </div>
        <div class="col-md-4">
          <label for="end_date" class="form-label">End Date</label>
          <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}"
            required>
        </div>
        <div class="col-md-4 d-flex align-items-end">
          <button type="submit" class="btn btn-primary">Generate Report</button>
        </div>
      </div>
    </form>

    <div class="mb-4">
      <div class="row">
        <div class="col-md-6">
          <div class="card bg-light">
            <div class="card-body">
              <h5 class="card-title">Report Period</h5>
              <p class="card-text">
                {{ $startDate->format('d M Y') }} to {{ $endDate->format('d M Y') }}
              </p>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card bg-light">
            <div class="card-body">
              <h5 class="card-title">Working Days</h5>
              <p class="card-text">
                {{ $workingDays }} days ({{ $workingDays * 8 }} available hours)
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    @if(count($utilizationData) > 0)
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>User</th>
            <th>Total Hours</th>
            <th>Utilization</th>
            <th>Projects</th>
          </tr>
        </thead>
        <tbody>
          @foreach($utilizationData as $data)
          <tr>
            <td>{{ $data['user']->name }}</td>
            <td>{{ number_format($data['total_hours'], 1) }}</td>
            <td>
              <div class="progress" style="height: 25px;">
                <div class="progress-bar {{ $data['utilization_percentage'] > 100 ? 'bg-danger' : 'bg-success' }}"
                  role="progressbar" style="width: {{ min($data['utilization_percentage'], 100) }}%"
                  aria-valuenow="{{ $data['utilization_percentage'] }}" aria-valuemin="0" aria-valuemax="100">
                  {{ number_format($data['utilization_percentage'], 1) }}%
                </div>
              </div>
              <small class="text-muted">
                {{ number_format($data['total_hours'], 1) }} of {{ $workingDays * 8 }} hours
              </small>
            </td>
            <td>
              @foreach($data['projects'] as $project)
              <div class="mb-1">
                <strong>
                  {{-- {{ $project['project'] ? $project['project']->title : 'Non-Project' }}: --}}
                  @if($project['project'])
                  {{ $project['project']->title }}
                  @elseif($project['category'])
                  {{ $project['category']->name }}
                  @else
                  Non-Project
                  @endif
                </strong>
                {{ number_format($project['hours'], 1) }} hours
              </div>
              @endforeach
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @else
    <div class="alert alert-info">No timesheet data found for the selected period.</div>
    @endif
  </div>
</div>
@endsection