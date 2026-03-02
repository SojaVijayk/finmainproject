@extends('layouts/layoutMaster')

@section('title', 'Edit Requirements')

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
@section('header', 'Edit Requirement')

@section('content')
<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-body">
        <form action="{{ route('pms.requirements.update', $requirement->id) }}" method="POST"
          enctype="multipart/form-data">
          @csrf
          @method('PUT')

          <div class="row mb-3">
            <div class="col-md-6">
              <label for="type_id" class="form-label">Type</label>
              <select name="type_id" id="type_id" class="form-select" required>
                <option value="">Select Type</option>
                @foreach($types as $key => $value)
                <option value="{{ $key }}" {{ $requirement->type_id == $key ? 'selected' : '' }}>{{ $value }}</option>
                @endforeach
              </select>
              @error('type_id')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label for="project_category_id" class="form-label">Category</label>
              <select name="project_category_id" id="project_category_id" class="form-select" required>
                <option value="">Select Category</option>
                @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ $requirement->project_category_id == $category->id ? 'selected' :
                  '' }}>{{ $category->name }}</option>
                @endforeach
              </select>
              @error('project_category_id')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label for="project_subcategory_id" class="form-label">Subcategory</label>
              <select name="project_subcategory_id" id="project_subcategory_id" class="form-select">
                <option value="">Select Subcategory</option>
                @foreach($subcategories as $subcategory)
                <option value="{{ $subcategory->id }}" {{ $requirement->project_subcategory_id == $subcategory->id ?
                  'selected' : '' }}>{{ $subcategory->name }}</option>
                @endforeach
              </select>
              @error('project_subcategory_id')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="ref_no" class="form-label">Project Title</label>
              <input type="text" name="project_title" id="project_title" class="form-control"
                value="{{ $requirement->project_title }}">
              @error('project_title')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label for="client_id" class="form-label">Client</label>
              <select name="client_id" id="client_id" class="form-select" required>
                <option value="">Select Client</option>
                @foreach($clients as $client)
                <option value="{{ $client->id }}" {{ $requirement->client_id == $client->id ? 'selected' : '' }}>{{
                  $client->client_name }}</option>
                @endforeach
              </select>
              @error('client_id')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label for="client_contact_person_id" class="form-label">Contact Person</label>
              <select name="client_contact_person_id" id="client_contact_person_id" class="form-select" required>
                <option value="">Select Contact Person</option>
                @foreach($contacts as $contact)
                <option value="{{ $contact->id }}" {{ $requirement->client_contact_person_id == $contact->id ?
                  'selected' : '' }}>{{ $contact->name }}</option>
                @endforeach
              </select>
              @error('client_contact_person_id')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label for="ref_no" class="form-label">Reference No (Optional)</label>
              <input type="text" name="ref_no" id="ref_no" class="form-control" value="{{ $requirement->ref_no }}">
              @error('ref_no')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-12">
              <label for="documents" class="form-label">Additional Documents (Optional)</label>
              <input type="file" name="documents[]" id="documents" class="form-control" multiple>
              @error('documents')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
              <small class="text-muted">You can upload multiple files (Max 10MB each)</small>
            </div>
          </div>

          <div class="mt-4">
            <button type="submit" class="btn btn-primary me-2">Update Requirement</button>
            <a href="{{ route('pms.requirements.show', $requirement->id) }}" class="btn btn-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection