@extends('layouts/layoutMaster')

@section('title', 'Create Venue')
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
  <h1>Create New Venue</h1>

  <form action="{{ route('venues.store') }}" method="POST">
    @csrf

    <div class="card">
      <div class="card-body">
        <div class="mb-3">
          <label for="name" class="form-label">Venue Name</label>
          <input type="text" class="form-control" id="name" name="name" required>
        </div>

        <div class="mb-3">
          <label for="description" class="form-label">Description</label>
          <textarea class="form-control" id="description" name="description" rows="3"></textarea>
        </div>

        <div class="mb-3">
          <label for="seating_capacity" class="form-label">Seating Capacity</label>
          <input type="number" class="form-control" id="seating_capacity" name="seating_capacity" min="1" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Amenities</label>
          <div class="row">
            <div class="col-md-4">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="amenities[]" id="amenity_internet"
                  value="internet">
                <label class="form-check-label" for="amenity_internet">Internet</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="amenities[]" id="amenity_projector"
                  value="projector">
                <label class="form-check-label" for="amenity_projector">Projector</label>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="amenities[]" id="amenity_pa_system"
                  value="pa_system">
                <label class="form-check-label" for="amenity_pa_system">PA System</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="amenities[]" id="amenity_ac" value="ac">
                <label class="form-check-label" for="amenity_ac">Air Conditioning</label>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="amenities[]" id="amenity_catering"
                  value="catering">
                <label class="form-check-label" for="amenity_catering">Catering</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="amenities[]" id="amenity_other" value="other">
                <label class="form-check-label" for="amenity_other">Other</label>
              </div>
            </div>
          </div>
        </div>

        <div class="mb-3 form-check">
          <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" checked>
          <label class="form-check-label" for="is_active">Active</label>
        </div>

        <button type="submit" class="btn btn-primary">Create Venue</button>
        <a href="{{ route('venues.index') }}" class="btn btn-secondary">Cancel</a>
      </div>
    </div>
  </form>
</div>
@endsection