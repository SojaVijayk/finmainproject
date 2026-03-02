@extends('layouts/layoutMaster')

@section('title', 'Leave - Request')

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

@section('content')
<div class="container">
  <h3 class="mb-4">Cancel Requests - HR Approval</h3>

  <table class="table table-bordered">
    <thead>
      <tr>
        <th>Employee</th>
        <th>Leave Type</th>
        <th>Dates Requested to Cancel</th>
        <th>Status</th>
        <th>Attendance</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      @foreach($cancelRequests as $detail)
      <tr>
        <td>{{ $detail->leaveRequest->user->name }}</td>
        <td>{{ $detail->leaveRequest->leaveType->leave_type }}</td>
        <td>{{ $detail->date }}
          @if($detail->leave_day_type == 1) Full Day
          @elseif($detail->leave_day_type == 2) AN
          @else
          FN @endif
        </td>
        <td><span class="badge bg-warning">Pending HR</span></td>
        <td>
          @if($detail->attendance_exists)
          <span class="badge bg-success">Punched</span>
          @else
          <span class="badge bg-danger">Not Punched</span>
          @endif
        </td>
        <td>
          <form method="POST" action="{{ route('leave.cancel.hr.action', $detail->id) }}" class="d-inline">
            @csrf
            <input type="hidden" name="status" value="4">
            <button type="submit" class="btn btn-sm btn-success">Approve</button>
          </form>
          <form method="POST" action="{{ route('leave.cancel.hr.action', $detail->id) }}" class="d-inline">
            @csrf
            <input type="hidden" name="status" value="6">
            <button type="submit" class="btn btn-sm btn-danger">Reject</button>
          </form>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endsection