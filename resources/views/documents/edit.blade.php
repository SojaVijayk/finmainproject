@extends('layouts/layoutMaster')

@section('title', 'Document Number Create')
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

<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/spinkit/spinkit.css')}}" />



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
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@6.1.15/index.global.min.js'></script>

@endsection

@section('page-script')
@endsection

@section('content')
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card">
        <div class="card-header">Edit Document</div>
        <div class="card-body">
          <form method="POST" action="{{ route('documents.update', $document) }}">
            @csrf
            @method('PUT')

            <div class="row mb-3">
              <label class="col-md-4 col-form-label text-md-end">Document Number</label>
              <div class="col-md-6">
                <p class="form-control-plaintext font-weight-bold">{{ $document->document_number }}</p>
              </div>
            </div>

            <div class="row mb-3">
              <label for="document_type_id" class="col-md-4 col-form-label text-md-end">Document Type</label>
              <div class="col-md-6">
                <select id="document_type_id" class="form-control @error('document_type_id') is-invalid @enderror"
                  name="document_type_id" required>
                  @foreach($documentTypes as $type)
                  <option value="{{ $type->id }}" {{ $document->document_type_id == $type->id ? 'selected' : '' }}>{{
                    $type->name }}</option>
                  @endforeach
                </select>
                @error('document_type_id')
                <span class="invalid-feedback" role="alert">
                  <strong>{{ $message }}</strong>
                </span>
                @enderror
              </div>
            </div>

            <div class="row mb-3">
              <label for="code_id" class="col-md-4 col-form-label text-md-end">Code</label>
              <div class="col-md-6">
                <select id="code_id" class="form-control @error('code_id') is-invalid @enderror" name="code_id"
                  required>
                  @foreach($codes as $code)
                  <option value="{{ $code->id }}" {{ $document->code_id == $code->id ? 'selected' : '' }}>{{ $code->code
                    }} ({{ $code->user->name }})</option>
                  @endforeach
                </select>
                @error('code_id')
                <span class="invalid-feedback" role="alert">
                  <strong>{{ $message }}</strong>
                </span>
                @enderror
              </div>
            </div>

            <div class="row mb-3">
              <label for="authorized_person_id" class="col-md-4 col-form-label text-md-end">Authorized Person</label>
              <div class="col-md-6">
                <select id="authorized_person_id"
                  class="form-control @error('authorized_person_id') is-invalid @enderror" name="authorized_person_id"
                  required>
                  @foreach($users as $user)
                  <option value="{{ $user->id }}" {{ $document->authorized_person_id == $user->id ? 'selected' : ''
                    }}>{{ $user->name }}</option>
                  @endforeach
                </select>
                @error('authorized_person_id')
                <span class="invalid-feedback" role="alert">
                  <strong>{{ $message }}</strong>
                </span>
                @enderror
              </div>
            </div>

            <div class="row mb-3">
              <label for="to_address_details" class="col-md-4 col-form-label text-md-end">To Address Details</label>
              <div class="col-md-6">
                <textarea id="to_address_details" class="form-control @error('to_address_details') is-invalid @enderror"
                  name="to_address_details"
                  required>{{ old('to_address_details', $document->to_address_details) }}</textarea>
                @error('to_address_details')
                <span class="invalid-feedback" role="alert">
                  <strong>{{ $message }}</strong>
                </span>
                @enderror
              </div>
            </div>

            <div class="row mb-3">
              <label for="subject" class="col-md-4 col-form-label text-md-end">Subject</label>
              <div class="col-md-6">
                <input id="subject" type="text" class="form-control @error('subject') is-invalid @enderror"
                  name="subject" value="{{ old('subject', $document->subject) }}" required>
                @error('subject')
                <span class="invalid-feedback" role="alert">
                  <strong>{{ $message }}</strong>
                </span>
                @enderror
              </div>
            </div>

            <div class="row mb-3">
              <label for="project_details" class="col-md-4 col-form-label text-md-end">Project Details</label>
              <div class="col-md-6">
                <input id="project_details" type="text"
                  class="form-control @error('project_details') is-invalid @enderror" name="project_details"
                  value="{{ old('project_details', $document->project_details) }}">
                @error('project_details')
                <span class="invalid-feedback" role="alert">
                  <strong>{{ $message }}</strong>
                </span>
                @enderror
              </div>
            </div>

            <div class="row mb-0">
              <div class="col-md-8 offset-md-4">
                <button type="submit" class="btn btn-primary">
                  Update Document
                </button>
                <a href="{{ route('documents.show', $document) }}" class="btn btn-secondary">Cancel</a>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection