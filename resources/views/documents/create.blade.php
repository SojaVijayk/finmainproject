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
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const previewBtn = document.getElementById('previewBtn');
    const numberType = document.getElementById('number_type');
    const documentType = document.getElementById('document_type_id');
    const codeId = document.getElementById('code_id');
    const previewDiv = document.getElementById('numberPreview');
    const DocumentNumberPreview = document.getElementById('DocumentNumberPreview');
    const sequenceNumber = document.getElementById('sequence_number');
    const yearInput = document.getElementById('year');

    previewBtn.addEventListener('click', function() {
        if (!numberType.value || !documentType.value || !codeId.value) {
            alert('Please select Number Type, Document Type and Code first');
            return;
        }

        fetch("{{ route('documents.generate.preview') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                number_type: numberType.value,
                document_type_id: documentType.value,
                code_id: codeId.value
            })
        })
        .then(response => response.json())
        .then(data => {
            DocumentNumberPreview.textContent = data.preview;
            sequenceNumber.value = data.sequence_number;
            yearInput.value = data.year;
        })
        .catch(error => {
            console.error('Error:', error);
            previewDiv.textContent = 'Error generating preview. Please try again.';
        });
    });
});
</script>
@endsection

@section('content')
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card">
        <div class="card-header">Generate New Letter/Document Number</div>
        <div class="card-body">
          <form method="POST" action="{{ route('documents.store') }}">
            @csrf

            <div class="row mb-3">
              <label for="number_type" class="col-md-4 col-form-label text-md-end">Number Type *</label>
              <div class="col-md-6">
                <select id="number_type" class="form-control @error('number_type') is-invalid @enderror"
                  name="number_type" required>
                  <option value="">Select Type *</option>
                  <option value="DS" {{ old('number_type')=='DS' ? 'selected' : '' }}>DS</option>
                  <option value="General" {{ old('number_type')=='General' ? 'selected' : '' }}>General</option>
                </select>
                @error('number_type')
                <span class="invalid-feedback" role="alert">
                  <strong>{{ $message }}</strong>
                </span>
                @enderror
              </div>
            </div>

            <div class="row mb-3">
              <label for="document_type_id" class="col-md-4 col-form-label text-md-end">Document Type *</label>
              <div class="col-md-6">
                <select id="document_type_id" class="form-control @error('document_type_id') is-invalid @enderror"
                  name="document_type_id" required>
                  <option value="">Select Document Type</option>
                  @foreach($documentTypes as $type)
                  <option value="{{ $type->id }}" {{ old('document_type_id')==$type->id ? 'selected' : '' }}>{{
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
              <label for="code_id" class="col-md-4 col-form-label text-md-end">Code *</label>
              <div class="col-md-6">
                <select id="code_id" class="form-control @error('code_id') is-invalid @enderror" name="code_id"
                  required>
                  <option value="">Select Code</option>
                  @foreach($codes as $code)
                  <option value="{{ $code->id }}" {{ old('code_id')==$code->id ? 'selected' : '' }}>{{ $code->code }}
                    ({{ $code->user->name }})</option>
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
              <label for="authorized_person_id" class="col-md-4 col-form-label text-md-end">Authorized Person *</label>
              <div class="col-md-6">
                <select id="authorized_person_id"
                  class="form-control @error('authorized_person_id') is-invalid @enderror" name="authorized_person_id"
                  required>
                  <option value="">Select Authorized Person</option>
                  @foreach($users as $user)
                  <option value="{{ $user->id }}" {{ old('authorized_person_id')==$user->id ? 'selected' : '' }}>{{
                    $user->name }}</option>
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
              <label for="to_address_details" class="col-md-4 col-form-label text-md-end">Recipient Details *</label>
              <div class="col-md-6">
                <textarea id="to_address_details" class="form-control @error('to_address_details') is-invalid @enderror"
                  name="to_address_details" required>{{ old('to_address_details') }}</textarea>
                @error('to_address_details')
                <span class="invalid-feedback" role="alert">
                  <strong>{{ $message }}</strong>
                </span>
                @enderror
              </div>
            </div>

            <div class="row mb-3">
              <label for="subject" class="col-md-4 col-form-label text-md-end">Subject *</label>
              <div class="col-md-6">
                <input id="subject" type="text" class="form-control @error('subject') is-invalid @enderror"
                  name="subject" value="{{ old('subject') }}" required>
                @error('subject')
                <span class="invalid-feedback" role="alert">
                  <strong>{{ $message }}</strong>
                </span>
                @enderror
              </div>
            </div>

            <div class="row mb-3">
              <label for="project_details" class="col-md-4 col-form-label text-md-end">Project Details (if
                applicable)</label>
              <div class="col-md-6">
                <input id="project_details" type="text"
                  class="form-control @error('project_details') is-invalid @enderror" name="project_details"
                  value="{{ old('project_details') }}">
                @error('project_details')
                <span class="invalid-feedback" role="alert">
                  <strong>{{ $message }}</strong>
                </span>
                @enderror
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-8 offset-md-4">
                <button type="button" id="previewBtn" class="btn btn-secondary">
                  Preview Document Number
                </button>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-8 offset-md-4">
                <div class="alert alert-primary" id="numberPreview">

                  Document number will appear here after you fill the required fields and click Preview.
                  <hr>
                  <span id="DocumentNumberPreview"></span>
                  <div id="floatingInputHelp" class="form-text  text-warning">Note: the preview is merely for reference;
                    the actual number will be issued after it has been successfully submitted..</div>
                </div>
              </div>

            </div>

            <input type="hidden" id="sequence_number" name="sequence_number" value="{{ old('sequence_number') }}">
            <input type="hidden" id="year" name="year" value="{{ old('year') }}">

            <div class="row mb-0">
              <div class="col-md-8 offset-md-4">
                <button type="submit" class="btn btn-primary">
                  Generate Number
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection