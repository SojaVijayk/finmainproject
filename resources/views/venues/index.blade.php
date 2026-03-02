@extends('layouts/layoutMaster')

@section('title', 'Venues')
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
      <h1>Venues</h1>
    </div>
    <div class="col-md-6 text-end">
      <a href="{{ route('venues.create') }}" class="btn btn-primary">Add Venue</a>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Name</th>
              <th>Capacity</th>
              <th>Amenities</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($venues as $venue)
            <tr>
              <td>{{ $venue->name }}</td>
              <td>{{ $venue->seating_capacity }}</td>
              <td>
                @if($venue->amenities)
                {{ implode(', ', $venue->amenities) }}
                @else
                None
                @endif
              </td>
              <td>
                @if($venue->is_active)
                <span class="badge bg-success">Active</span>
                @else
                <span class="badge bg-secondary">Inactive</span>
                @endif
              </td>
              <td>
                <a href="{{ route('venues.show', $venue) }}" class="btn btn-sm btn-info">View</a>
                <a href="{{ route('venues.edit', $venue) }}" class="btn btn-sm btn-primary">Edit</a>
                <form action="{{ route('venues.destroy', $venue) }}" method="POST" class="d-inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger"
                    onclick="return confirm('Are you sure?')">Delete</button>
                </form>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection