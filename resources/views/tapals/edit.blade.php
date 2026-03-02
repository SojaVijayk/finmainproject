@extends('layouts/layoutMaster')

@section('title', 'Tapal - Edit')

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
<script>
  $(document).ready(function() {
         $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });
        });
   document.addEventListener('DOMContentLoaded', function() {
        const inwardMode = document.getElementById('inward_mode');
        const mailFields = document.getElementById('mail_fields');
        const addressFields = document.getElementById('address_fields');

        function toggleFields() {
            if (inwardMode.value === 'By Mail') {
                mailFields.style.display = 'block';
                addressFields.style.display = 'none';
                document.getElementById('mail_id').required = true;
                document.getElementById('from_name').required = false;
                document.getElementById('from_address').required = false;
            } else {
                mailFields.style.display = 'none';
                addressFields.style.display = 'block';
                document.getElementById('mail_id').required = false;
                document.getElementById('from_name').required = true;
                document.getElementById('from_address').required = true;
            }
        }

        inwardMode.addEventListener('change', toggleFields);
    });
</script>
@endsection

@section('content')


<div class="card">
  <div class="card-header">
    <h3 class="card-title">Edit Tapal - {{ $tapal->tapal_number }}</h3>
  </div>
  <form action="{{ route('tapals.update', $tapal->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="type">Type*</label>
            <select name="type" id="type" class="form-control" required disabled>
              <option value="inward" {{ $tapal->type == 'inward' ? 'selected' : '' }}>Inward</option>
              <option value="outward" {{ $tapal->type == 'outward' ? 'selected' : '' }}>Outward</option>
            </select>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="inward_mode">Mode*</label>
            <select name="inward_mode" id="inward_mode" class="form-control" required>
              <option value="Speed Post" {{ $tapal->inward_mode == 'Speed Post' ? 'selected' : '' }}>Speed Post</option>
              <option value="Post" {{ $tapal->inward_mode == 'Post' ? 'selected' : '' }}>Post</option>
              <option value="Courier" {{ $tapal->inward_mode == 'Courier' ? 'selected' : '' }}>Courier</option>
              <option value="By Mail" {{ $tapal->inward_mode == 'By Mail' ? 'selected' : '' }}>By Mail</option>
              <option value="By Hand" {{ $tapal->inward_mode == 'By Hand' ? 'selected' : '' }}>By Hand</option>
            </select>
          </div>
        </div>
      </div>

      <div class="row mt-3">
        <div class="col-md-6">
          <div class="form-group">
            <label for="inward_date">Inward Date*</label>
            <input type="text" name="inward_date" id="inward_date" class="form-control datepicker"
              value="{{ $tapal->inward_date }}" required>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="received_date">Received Date*</label>
            <input type="text" name="received_date" id="received_date" class="form-control datepicker"
              value="{{ $tapal->received_date }}" required>
          </div>
        </div>
      </div>

      <div id="mail_fields" class="row mt-3"
        style="display: {{ $tapal->inward_mode == 'By Mail' ? 'block' : 'none' }};">
        <div class="col-md-12">
          <div class="form-group">
            <label for="mail_id">Mail ID*</label>
            <input type="email" name="mail_id" id="mail_id" class="form-control" value="{{ $tapal->mail_id }}">
          </div>
        </div>
      </div>

      <div id="address_fields" class="row mt-3"
        style="display: {{ $tapal->inward_mode != 'By Mail' ? 'block' : 'none' }};">
        <div class="col-md-6">
          <div class="form-group">
            <label for="from_name">From Name*</label>
            <input type="text" name="from_name" id="from_name" class="form-control" value="{{ $tapal->from_name }}">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="from_department">Department</label>
            <input type="text" name="from_department" id="from_department" class="form-control"
              value="{{ $tapal->from_department }}">
          </div>
        </div>
        <div class="col-md-6 mt-3">
          <div class="form-group">
            <label for="from_mobile">Mobile</label>
            <input type="text" name="from_mobile" id="from_mobile" class="form-control"
              value="{{ $tapal->from_mobile }}">
          </div>
        </div>
        <div class="col-md-12 mt-3">
          <div class="form-group">
            <label for="from_address">Address*</label>
            <textarea name="from_address" id="from_address" class="form-control"
              rows="2">{{ $tapal->from_address }}</textarea>
          </div>
        </div>
        <div class="col-md-12 mt-3">
          <div class="form-group">
            <label for="from_person_details">Person Details</label>
            <textarea name="from_person_details" id="from_person_details" class="form-control"
              rows="2">{{ $tapal->from_person_details }}</textarea>
          </div>
        </div>
      </div>

      <div class="row mt-3">
        <div class="col-md-6">
          <div class="form-group">
            <label for="ref_number">Reference Number</label>
            <input type="text" name="ref_number" id="ref_number" class="form-control" value="{{ $tapal->ref_number }}">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="letter_date">Letter Date</label>
            <input type="text" name="letter_date" id="letter_date" class="form-control datepicker"
              value="{{ $tapal->letter_date  }}">
          </div>
        </div>
      </div>

      <div class="row mt-3">
        <div class="col-md-12">
          <div class="form-group">
            <label for="subject">Subject*</label>
            <input type="text" name="subject" id="subject" class="form-control" value="{{ $tapal->subject }}" required>
          </div>
        </div>
      </div>

      <div class="row mt-3">
        <div class="col-md-12">
          <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" class="form-control"
              rows="3">{{ $tapal->description }}</textarea>
          </div>
        </div>
      </div>

      {{-- <div class="row mt-3">
        <div class="col-md-12">
          <div class="form-group">
            <label for="attachments">Add More Attachments</label>
            <input type="file" name="attachments[]" id="attachments" class="form-control" multiple>
            <small class="text-muted">You can upload multiple files (PDF, DOC, JPG, PNG)</small>
          </div>
        </div>
      </div> --}}

      @if($tapal->attachments->count() > 0)
      <div class="row mt-3">
        <div class="col-md-12">
          <div class="form-group">
            <label>Existing Attachments</label>
            <div class="list-group">
              @foreach($tapal->attachments as $attachment)
              <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <i class="fas fa-file-alt mr-2"></i>
                    {{ $attachment->file_name }}
                    <small class="text-muted ml-2">
                      ({{ $attachment->file_type }}, {{ round($attachment->file_size / 1024) }} KB)
                    </small>
                  </div>
                  <div>

                    {{-- <a href="{{ Storage::url($attachment->file_path) }}" --}} <a
                      href="{{ asset('storage/'.$attachment->file_path) }}" target="_blank"
                      class="btn btn-sm btn-info mr-2">
                      <i class="fas fa-eye"></i> View
                    </a>
                    {{-- <form action="{{ route('attachments.destroy', $attachment->id) }}" method="POST"
                      class="d-inline" onsubmit="return confirm('Are you sure you want to delete this attachment?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-danger">
                        <i class="fas fa-trash"></i> Delete
                      </button>
                    </form> --}}
                  </div>
                </div>
              </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
      @endif

      <!-- Add New Attachments Section -->
      <div class="row mt-3">
        <div class="col-md-12">
          <div class="form-group">
            <label for="new_attachments">Add More Documents</label>
            <input type="file" name="new_attachments[]" id="new_attachments" class="form-control" multiple>
            <small class="text-muted">
              Max 5 files (PDF, DOC, DOCX, JPG, JPEG, PNG) up to 2MB each
            </small>
          </div>
        </div>
      </div>
    </div>
    <div class="card-footer">
      <button type="submit" class="btn btn-primary">Update Tapal</button>
      <a href="{{ route('tapals.show', $tapal->id) }}" class="btn btn-default">Cancel</a>
    </div>
  </form>
</div>
@endsection