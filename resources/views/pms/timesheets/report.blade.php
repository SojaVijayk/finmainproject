@extends('layouts/layoutMaster')

@section('title', 'Timesheet Report')

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
@section('header', 'Timesheet Report')

@section('content')
<div class="card">
  <div class="card-header">
    <div class="d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Timesheet Report</h5>
      <div>
        {{-- <a
          href="{{ route('pms.timesheets.export', ['type' => 'timesheet']) }}?start_date={{ request('start_date') }}&end_date={{ request('end_date') }}&user_id={{ request('user_id') }}"
          class="btn btn-sm btn-success me-2">
          <i class="fas fa-file-export"></i> Export
        </a> --}}
      </div>
    </div>
  </div>
  <div class="card-body">
    <form method="GET" class="mb-4">
      <div class="row">
        @if(auth()->user()->can('view_all_timesheets'))
        <div class="col-md-3">
          <label for="user_id" class="form-label">User</label>
          <select name="user_id" id="user_id" class="form-select">
            <option value="all">All Users</option>
            @foreach($users as $user)
            <option value="{{ $user->id }}" {{ $selectedUserId==$user->id ? 'selected' : '' }}>
              {{ $user->name }}
            </option>
            @endforeach
          </select>
        </div>
        @endif
        <div class="col-md-3">
          <label for="start_date" class="form-label">Start Date</label>
          <input type="date" name="start_date" id="start_date" class="form-control"
            value="{{ $startDate->format('Y-m-d') }}" required>
        </div>
        <div class="col-md-3">
          <label for="end_date" class="form-label">End Date</label>
          <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}"
            required>
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <button type="submit" class="btn btn-primary">Generate Report</button>
        </div>
      </div>
    </form>

    @if($timesheets->count() > 0)
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Date</th>
            <th>User</th>
            <th>Category</th>
            <th>Project</th>
            <th>Time</th>
            <th>Description</th>
          </tr>
        </thead>
        <tbody>
          @foreach($timesheets as $timesheet)
          <tr>
            <td>{{ $timesheet->date->format('d M Y') }}</td>
            <td>{{ $timesheet->user->name }}</td>
            <td>{{ $timesheet->category->name }}</td>
            <td>{{ $timesheet->project ? $timesheet->project->title : 'N/A' }}</td>
            <td>{{ $timesheet->formatted_time }}</td>
            <td>{{ Str::limit($timesheet->description, 50) }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="mt-4">
      <h5>Summary by Category</h5>
      <div class="table-responsive">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Category</th>
              <th>Total Hours</th>
              <th>Percentage</th>
            </tr>
          </thead>
          <tbody>
            @php
            $totalHours = $timesheets->sum('hours');
            @endphp
            @foreach($categories as $category)
            @php
            $categoryHours = $timesheets->where('category_id', $category->id)->sum('hours');
            $percentage = $totalHours > 0 ? ($categoryHours / $totalHours) * 100 : 0;
            @endphp
            @if($categoryHours > 0)
            <tr>
              <td>{{ $category->name }}</td>
              <td>{{ number_format($categoryHours, 1) }}</td>
              <td>
                <div class="progress">
                  <div class="progress-bar" role="progressbar" style="width: {{ $percentage }}%"
                    aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100">
                    {{ number_format($percentage, 1) }}%
                  </div>
                </div>
              </td>
            </tr>
            @endif
            @endforeach
            <tr class="table-active">
              <td><strong>Total</strong></td>
              <td><strong>{{ number_format($totalHours, 1) }}</strong></td>
              <td><strong>100%</strong></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    @else
    <div class="alert alert-info">No timesheet entries found for the selected criteria.</div>
    @endif
  </div>
</div>
@endsection