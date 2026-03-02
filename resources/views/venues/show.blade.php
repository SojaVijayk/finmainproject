@extends('layouts/layoutMaster')

@section('title', 'Venue Details')
@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/spinkit/spinkit.css')}}" />

<link rel="stylesheet" href="{{asset('assets/vendor/libs/chartjs/chartjs.css')}}" />
@endsection

@section('page-style')

@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>

<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js')}}"></script>
<script src="{{asset('assets/vendor/libs/block-ui/block-ui.js')}}"></script>
<script src="{{asset('assets/vendor/libs/chartjs/chartjs.js')}}"></script>

@endsection

@section('page-script')

@endsection

@section('content')
<div class="container">
  <div class="row mb-4">
    <div class="col-md-6">
      <h1>Venue Details</h1>
    </div>
    <div class="col-md-6 text-end">
      <a href="{{ route('venues.edit', $venue) }}" class="btn btn-primary me-2">Edit</a>
      <form action="{{ route('venues.destroy', $venue) }}" method="POST" class="d-inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
      </form>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <table class="table table-bordered">
            <tr>
              <th>Name</th>
              <td>{{ $venue->name }}</td>
            </tr>
            <tr>
              <th>Capacity</th>
              <td>{{ $venue->seating_capacity }}</td>
            </tr>
            <tr>
              <th>Status</th>
              <td>
                @if($venue->is_active)
                <span class="badge bg-success">Active</span>
                @else
                <span class="badge bg-secondary">Inactive</span>
                @endif
              </td>
            </tr>
          </table>
        </div>
        <div class="col-md-6">
          <table class="table table-bordered">
            <tr>
              <th>Amenities</th>
              <td>
                @if($venue->amenities)
                {{ implode(', ', $venue->amenities) }}
                @else
                None
                @endif
              </td>
            </tr>
          </table>
        </div>
      </div>

      @if($venue->description)
      <div class="mt-3">
        <h5>Description</h5>
        <p>{{ $venue->description }}</p>
      </div>
      @endif
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h5>Upcoming Bookings</h5>
    </div>
    <div class="card-body">
      @if($venue->bookings->count() > 0)
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Event Name</th>
              <th>Date/Time</th>
              <th>Host</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            @foreach($venue->bookings as $booking)
            <tr>
              <td><a href="{{ route('bookings.show', $booking) }}">{{ $booking->event_name }}</a></td>
              <td>
                {{ $booking->start_time->format('M d, Y h:i A') }}<br>
                to {{ $booking->end_time->format('M d, Y h:i A') }}
              </td>
              <td>{{ $booking->hostedBy->name }}</td>
              <td>
                <span class="badge
                                    @if($booking->status === 'confirmed') bg-success
                                    @elseif($booking->status === 'cancelled') bg-danger
                                    @else bg-warning text-dark @endif">
                  {{ ucfirst($booking->status) }}
                </span>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @else
      <p>No upcoming bookings for this venue.</p>
      @endif
    </div>
  </div>
</div>
@endsection