@extends('layouts/layoutMaster')

@section('title', 'Tapal - Create')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/spinkit/spinkit.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />


@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>

<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js')}}"></script>
<script src="{{asset('assets/vendor/libs/block-ui/block-ui.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>

@endsection

@section('page-script')
{{-- <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> --}}
<script>
  $(document).ready(function() {
         $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });
        $('#inward_mode').change(function() {
            if ($(this).val() === 'By Mail') {
                $('#mail_fields').show();
                $('#address_fields').hide();
            } else {
                $('#mail_fields').hide();
                $('#address_fields').show();
            }
        });

        // Initialize Select2
        $('#assigned_user_id').select2({
            placeholder: "Select user to assign",
            allowClear: true
        });

        $('#notify_users').select2({
            placeholder: "Select users to notify",
            allowClear: true
        });
    });
</script>
@endsection

@section('content')
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Create New Tapal</h3>
  </div>
  <form action="{{ route('tapals.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="card-body">
      <div class="row">
        <div class="col-md-2">
          <div class="form-group">
            <label for="type">Type*</label>
            <select name="type" id="type" class="form-control" required>
              <option value="inward">Inward</option>
              {{-- <option value="outward">Outward</option> --}}
            </select>
          </div>
        </div>
        <div class="col-md-2">
          <div class="form-group">
            <label for="inward_mode">Mode*</label>
            <select name="inward_mode" id="inward_mode" class="form-control" required>
              <option value="Speed Post">Speed Post</option>
              <option value="Post">Post</option>
              <option value="Courier">Courier</option>
              <option value="By Mail">By Mail</option>
              <option value="By Hand">By Hand</option>
            </select>
          </div>
        </div>


        <div class="col-md-4">
          <div class="form-group">
            <label for="inward_date">Inward Date*</label>
            <input type="text" name="inward_date" id="inward_date" class="form-control datepicker" required>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label for="received_date">Received Date*</label>
            <input type="text" name="received_date" id="received_date" class="form-control datepicker" required>
          </div>
        </div>
      </div>

      <div id="mail_fields" class="row mt-3" style="display: none;">
        <div class="col-md-12">
          <div class="form-group">
            <label for="mail_id">Mail ID*</label>
            <input type="email" name="mail_id" id="mail_id" class="form-control">
          </div>
        </div>
      </div>

      <div id="address_fields" class="row mt-3">
        <div class="col-md-4 mt-3">
          <div class="form-group">
            <label for="from_name">From Name*</label>
            <input type="text" name="from_name" id="from_name" class="form-control">
          </div>
        </div>
        <div class="col-md-4 mt-3">
          <div class="form-group">
            <label for="from_department">Department</label>
            <input type="text" name="from_department" id="from_department" class="form-control">
          </div>
        </div>
        <div class="col-md-4 mt-3">
          <div class="form-group">
            <label for="from_mobile">Mobile</label>
            <input type="text" name="from_mobile" id="from_mobile" class="form-control">
          </div>
        </div>
        <div class="col-md-6 mt-3">
          <div class="form-group">
            <label for="from_address">Address*</label>
            <textarea name="from_address" id="from_address" class="form-control" rows="2"></textarea>
          </div>
        </div>
        <div class="col-md-6 mt-3">
          <div class="form-group">
            <label for="from_person_details">Person Details</label>
            <textarea name="from_person_details" id="from_person_details" class="form-control" rows="2"></textarea>
          </div>
        </div>
      </div>

      <div class="row mt-3">
        <div class="col-md-4">
          <div class="form-group">
            <label for="ref_number">Reference Number</label>
            <input type="text" name="ref_number" id="ref_number" class="form-control">
          </div>
        </div>
        <div class="col-md-2">
          <div class="form-group">
            <label for="letter_date">Letter Date</label>
            <input type="text" name="letter_date" id="letter_date" class="form-control datepicker">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="subject">Subject*</label>
            <input type="text" name="subject" id="subject" class="form-control" required>
          </div>
        </div>
      </div>



      <div class="row mt-3">
        <div class="col-md-12">
          <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" class="form-control" rows="3"></textarea>
          </div>
        </div>
      </div>
      <div class="card mt-4">
        <div class="card-header  text-white">
          <h4 class="card-title">Assign Tapal</h4>
        </div>
        <div class="card-body">
          <div class="form-group">
            <label for="assigned_user_id">Assign To*</label>
            <select name="assigned_user_id[]" id="assigned_user_id" class="form-control select2" multiple required>
              <option value="">Select User</option>
              @foreach($users as $user)
              {{-- @if($user->id != Auth::id()) --}}
              <option value="{{ $user->id }}">{{ $user->name }}</option>
              {{-- @endif --}}
              @endforeach
            </select>
          </div>
          <div class="form-group mt-3">
            <label for="assignment_remarks">Assignment Remarks</label>
            <textarea name="assignment_remarks" id="assignment_remarks" class="form-control" rows="2"></textarea>
          </div>
        </div>
      </div>

      <!-- Notification Section -->
      <div class="card mt-4">
        <div class="card-header  text-white">
          <h4 class="card-title">Send Notification (Optional)</h4>
        </div>
        <div class="card-body">
          <div class="form-group">
            <label for="notify_users">Notify Users</label>
            <select name="notify_users[]" id="notify_users" class="form-control select2" multiple>
              @foreach($users as $user)
              @if($user->id != Auth::id())
              <option value="{{ $user->id }}">{{ $user->name }}</option>
              @endif
              @endforeach
            </select>
            <small class="text-muted">Select multiple users who should receive notification emails</small>
          </div>
        </div>
      </div>

      <!-- Attachments Section -->
      <div class="row mt-3">
        <div class="col-md-12">
          <div class="form-group">
            <label for="attachments">Attachments</label>
            <input type="file" name="attachments[]" id="attachments" class="form-control" multiple>
            <small class="text-muted">You can upload multiple files (PDF, DOC, JPG, PNG)</small>
          </div>
        </div>
      </div>
    </div>
    <div class="card-footer">
      <button type="submit" class="btn btn-primary">Create Tapal</button>
      <a href="{{ route('tapals.index') }}" class="btn btn-default">Cancel</a>
    </div>
  </form>
</div>
@endsection

{{-- @push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush --}}