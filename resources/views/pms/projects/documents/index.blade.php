@extends('layouts/layoutMaster')

@section('title', 'Project Documents')

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
  function formatBytess(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';

        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));

        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }
</script>
@endsection
@section('header', 'Project Documents: ' . $project->title)

@section('content')
<div class="card">
  <div class="card-header">
    <div class="d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Document Management</h5>
      <div>
        <a href="{{ route('pms.projects.show', $project->id) }}" class="btn btn-sm btn-secondary me-2">
          <i class="fas fa-arrow-left"></i> Back to Project
        </a>
        <a href="{{ route('pms.projects.documents.create', $project->id) }}" class="btn btn-sm btn-primary">
          <i class="fas fa-upload"></i> Upload Documents
        </a>
      </div>
    </div>
  </div>
  <div class="card-body">
    @if($documents->isEmpty())
    <div class="alert alert-info">No documents uploaded yet.</div>
    @else
    <div class="row mb-3">
      <div class="col-md-3">
        <div class="list-group">
          <a href="{{ route('pms.projects.documents.index', $project->id) }}"
            class="list-group-item list-group-item-action {{ request()->has('folder') ? '' : 'active' }}">
            All Folders
          </a>
          @foreach($folders as $folder)
          <a href="{{ route('pms.projects.documents.index', ['project' => $project->id, 'folder' => $folder]) }}"
            class="list-group-item list-group-item-action {{ request('folder') == $folder ? 'active' : '' }}">
            <i class="fas fa-folder me-2"></i> {{ $folder ?: 'Root' }}
          </a>
          @endforeach
        </div>
      </div>
      <div class="col-md-9">
        @foreach($documents as $folderName => $folderDocuments)
        @if(!request()->has('folder') || request('folder') == $folderName)
        @if($folderName)
        <h5 class="mb-3">
          <i class="fas fa-folder"></i> {{ $folderName }}
        </h5>
        @endif

        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th width="40px"></th>
                <th>Name</th>
                <th>Size</th>
                <th>Uploaded By</th>
                <th>Uploaded At</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @php
              if (!function_exists('formatBytes')) {
              function formatBytes($bytes, $decimals = 2)
              {
              if ($bytes == 0) return '0 Bytes';
              $k = 1024;
              $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
              $i = floor(log($bytes, $k));
              return round($bytes / pow($k, $i), $decimals) . ' ' . $sizes[$i];
              }
              }
              @endphp
              @foreach($folderDocuments as $document)
              <tr>
                <td>
                  <i class="fas {{ $document->file_icon }} fa-lg"></i>
                </td>
                <td>
                  {{ $document->name }}
                  @if($document->description)
                  <small class="d-block text-muted">{{ $document->description }}</small>
                  @endif
                </td>

                <td>{{ formatBytes($document->size) }}</td>
                <td>{{ $document->uploadedBy->name }}</td>
                <td>{{ $document->created_at->format('d M Y H:i') }}</td>
                <td>
                  <a href="{{ route('pms.projects.documents.show', [$project->id, $document->id]) }}"
                    class="btn btn-sm btn-info me-1" title="View">
                    <i class="fas fa-eye"></i>
                  </a>
                  <a href="{{ route('pms.projects.documents.download', [$project->id, $document->id]) }}"
                    class="btn btn-sm btn-primary me-1" title="Download">
                    <i class="fas fa-download"></i>
                  </a>
                  <form action="{{ route('pms.projects.documents.destroy', [$project->id, $document->id]) }}"
                    method="POST" class="d-inline"
                    onsubmit="return confirm('Are you sure you want to delete this document?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @endif
        @endforeach
      </div>
    </div>
    @endif
  </div>
</div>
@endsection