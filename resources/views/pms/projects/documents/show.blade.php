@extends('layouts/layoutMaster')

@section('title', 'View Project Documents')

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
@section('header', 'View Document: ' . $document->name)

@section('content')
<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title">Document Details</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <p><strong>Name:</strong> {{ $document->name }}</p>
            <p><strong>Type:</strong> {{ $document->type }}</p>
            @php
            function formatBytes($bytes, $decimals = 2)
            {
            if ($bytes == 0) return '0 Bytes';
            $k = 1024;
            $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            $i = floor(log($bytes, $k));
            return round($bytes / pow($k, $i), $decimals) . ' ' . $sizes[$i];
            }
            @endphp
            <p><strong>Size:</strong> {{ formatBytes($document->size) }}</p>
            <p><strong>Folder:</strong> {{ $document->folder ?: 'Root' }}</p>
          </div>
          <div class="col-md-6">
            <p><strong>Uploaded By:</strong> {{ $document->uploadedBy->name }}</p>
            <p><strong>Uploaded At:</strong> {{ $document->created_at->format('d M Y H:i') }}</p>
            <p><strong>Project:</strong> {{ $project->title }}</p>
          </div>
        </div>

        @if($document->description)
        <div class="mt-3">
          <h6>Description</h6>
          <p>{{ $document->description }}</p>
        </div>
        @endif

        <div class="mt-4">
          <a href="{{ route('pms.projects.documents.download', [$project->id, $document->id]) }}"
            class="btn btn-primary me-2">
            <i class="fas fa-download"></i> Download Document
          </a>

          @if(in_array(strtolower(pathinfo($document->name, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'pdf']))
          <a href="{{ Storage::url($document->path) }}" target="_blank" class="btn btn-info me-2">
            <i class="fas fa-eye"></i> View Document
          </a>
          @endif
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title">Document Preview</h5>
      </div>
      <div class="card-body text-center">
        @if(in_array(strtolower(pathinfo($document->name, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif']))
        <img src="{{ Storage::url($document->path) }}" alt="Document Preview" class="img-fluid">
        @elseif(strtolower(pathinfo($document->name, PATHINFO_EXTENSION)) === 'pdf')
        <iframe src="{{ Storage::url($document->path) }}" width="100%" height="300px"></iframe>
        @else
        <div class="display-1 text-muted">
          <i class="fas {{ $document->file_icon }}"></i>
        </div>
        <p class="text-muted">Preview not available</p>
        @endif
      </div>
    </div>

    <div class="card mt-4">
      <div class="card-header">
        <h5 class="card-title">Actions</h5>
      </div>
      <div class="card-body">
        <form action="{{ route('pms.projects.documents.destroy', [$project->id, $document->id]) }}" method="POST"
          onsubmit="return confirm('Are you sure you want to delete this document?')">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger w-100 mb-2">
            <i class="fas fa-trash"></i> Delete Document
          </button>
        </form>
        <a href="{{ route('pms.projects.documents.index', $project->id) }}" class="btn btn-secondary w-100">
          <i class="fas fa-arrow-left"></i> Back to Documents
        </a>
      </div>
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
  iframe {
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
  }
</style>
@endpush