@extends('layouts/layoutMaster')

@section('title', 'Upload Project Documents')

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
  document.addEventListener('DOMContentLoaded', function() {
    const documentsInput = document.getElementById('documents');
    const descriptionsContainer = document.getElementById('descriptions-container');

    documentsInput.addEventListener('change', function() {
        descriptionsContainer.innerHTML = '';

        if (this.files.length > 0) {
            const heading = document.createElement('h6');
            heading.className = 'mt-3 mb-2';
            heading.textContent = 'Document Descriptions (Optional)';
            descriptionsContainer.appendChild(heading);

            for (let i = 0; i < this.files.length; i++) {
                const group = document.createElement('div');
                group.className = 'mb-3';

                const label = document.createElement('label');
                label.className = 'form-label';
                label.textContent = this.files[i].name;

                const input = document.createElement('input');
                input.type = 'text';
                input.name = 'descriptions[]';
                input.className = 'form-control';
                input.placeholder = 'Enter description for ' + this.files[i].name;

                group.appendChild(label);
                group.appendChild(input);
                descriptionsContainer.appendChild(group);
            }
        }
    });
});
</script>
@endsection
@section('header', 'Upload Documents: ' . $project->title)

@section('content')
<div class="card">
  <div class="card-header">
    <h5 class="card-title">Upload Documents</h5>
  </div>
  <div class="card-body">
    <form action="{{ route('pms.projects.documents.store', $project->id) }}" method="POST"
      enctype="multipart/form-data">
      @csrf

      <div class="row mb-3">
        <div class="col-md-6">
          <label for="folder" class="form-label">Select Existing Folder</label>
          <select name="folder" id="folder" class="form-select">
            <option value="">Root Folder</option>
            @foreach($existingFolders as $folder)
            <option value="{{ $folder }}" {{ old('folder')==$folder ? 'selected' : '' }}>
              {{ $folder }}
            </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-6">
          <label for="new_folder" class="form-label">Or Create New Folder</label>
          <input type="text" name="new_folder" id="new_folder" class="form-control" value="{{ old('new_folder') }}"
            placeholder="Enter new folder name">
        </div>
      </div>

      <div class="row mb-3">

        <div class="col-md-12">
          <label for="description" class="form-label">Description</label>
          <textarea name="description" id="new_folder" class="form-control"
            placeholder="Enter description"> {{ old('description') }}</textarea>
        </div>
      </div>

      <div class="mb-3">
        <label for="documents" class="form-label">Documents</label>
        <input type="file" name="documents[]" id="documents" class="form-control" multiple required>
        <small class="text-muted">You can upload multiple files (Max 10MB each)</small>
      </div>

      <div id="descriptions-container">
        <!-- Will be populated by JavaScript -->
      </div>

      <div class="mt-4">
        <button type="submit" class="btn btn-primary me-2">
          <i class="fas fa-upload"></i> Upload Documents
        </button>
        <a href="{{ route('pms.projects.documents.index', $project->id) }}" class="btn btn-secondary">
          Cancel
        </a>
      </div>
    </form>
  </div>
</div>
@endsection