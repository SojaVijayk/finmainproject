@extends('layouts/layoutMaster')

@section('title', 'View Requirements')

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
  document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    });
</script>
@endsection

@section('content')
<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title">Requirement Details - {{ $requirement->project_title }}</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <p><strong>Temp No:</strong> {{ $requirement->temp_no }}</p>
            <p><strong>Type:</strong> {{ $requirement->type_name }}</p>
            <p><strong>Category:</strong> {{ $requirement->category->name }}</p>
            <p><strong>Subcategory:</strong> {{ $requirement->subcategory->name ?? 'N/A' }}</p>
            {{-- <p><strong>Project Title:</strong> {{ $requirement->project_title }}</p> --}}
          </div>
          <div class="col-md-6">
            <p><strong>Client:</strong> {{ $requirement->client->client_name }}</p>
            <p><strong>Contact Person:</strong> {{ $requirement->contactPerson->name }}</p>
            <p><strong>Reference No:</strong> {{ $requirement->ref_no ?? 'N/A' }}</p>
            <p><strong>Status:</strong>
              <span class="badge bg-{{ $requirement->status_badge_color }}">
                {{ $requirement->status_name }}
              </span>
            </p>
          </div>
          <div class="accordion accordion-header-primary" id="accordionStyle1">
            <div class="accordion-item">
              <h2 class="accordion-header">
                <button type="button" class="accordion-button " data-bs-toggle="collapse"
                  data-bs-target="#accordionStyle1-1" aria-expanded="false">Project Description</button>
              </h2>

              <div id="accordionStyle1-1" class="accordion-collapse collapse show" data-bs-parent="#accordionStyle1">
                <div class="accordion-body">{{ $requirement->project_description }}</div>
              </div>
            </div>
          </div>
        </div>

        @if($requirement->allocated_to)
        <div class="mt-3">
          <h6>Allocation Details</h6>
          <p><strong>Principal investigator:</strong> {{ $requirement->allocatedTo->name }}</p>
          <p><strong>Allocated By:</strong> {{ $requirement->allocatedBy->name }}</p>
          <p><strong>Allocated At:</strong> {{ $requirement->allocated_at->format('d M Y H:i') }}</p>
        </div>
        @endif
      </div>
    </div>

    <div class="card mt-4">
      <div class="card-header">
        <h5 class="card-title">Documents</h5>
      </div>
      <div class="card-body">
        @if($requirement->documents->count() > 0)
        {{-- <div class="list-group">
          @foreach($requirement->documents as $document)
          <div class="list-group-item d-flex justify-content-between align-items-center">
            <div>
              <i class="fas fa-file me-2"></i>
              {{ $document->name }}
            </div>
            <a href="{{ Storage::url($document->path) }}" target="_blank" class="btn btn-sm btn-primary">
              <i class="fas fa-download"></i> Download
            </a>
          </div>
          @endforeach
        </div> --}}

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
          @foreach($requirement->documents as $document)
          @php
          $fileExtension = pathinfo($document->name, PATHINFO_EXTENSION);
          $iconClass = match(strtolower($fileExtension)) {
          'pdf' => 'fas fa-file-pdf text-danger',
          'doc', 'docx' => 'fas fa-file-word text-primary',
          'xls', 'xlsx' => 'fas fa-file-excel text-success',
          'ppt', 'pptx' => 'fas fa-file-powerpoint text-warning',
          'jpg', 'jpeg', 'png', 'gif' => 'fas fa-file-image text-info',
          'zip', 'rar' => 'fas fa-file-archive text-secondary',
          default => 'fas fa-file text-muted'
          };

          $fileSize = Storage::exists($document->path) ? Storage::size($document->path) : 0;
          $fileSizeFormatted = $fileSize >= 1048576
          ? round($fileSize / 1048576, 2) . ' MB'
          : round($fileSize / 1024, 2) . ' KB';
          @endphp

          <div class="col">
            <div
              class="d-flex align-items-center justify-content-between border p-3 rounded shadow-sm bg-light position-relative"
              data-bs-toggle="tooltip" data-bs-placement="top" title="Size: {{ $fileSizeFormatted }}">
              <div class="d-flex align-items-center">
                <i class="{{ $iconClass }} fs-3 me-3"></i>
                <span class="text-truncate" style="max-width: 180px;">{{ $document->name }}</span>
              </div>
              <a href="{{ Storage::url($document->path) }}" target="_blank" class="btn btn-sm btn-outline-primary"
                title="Download">
                <i class="fas fa-download"></i>
              </a>
            </div>
          </div>
          @endforeach
        </div>

        @else
        <p>No documents attached</p>
        @endif
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title">Actions</h5>
      </div>
      <div class="card-body">
        @if($requirement->status == \App\Models\PMS\Requirement::STATUS_INITIATED && auth()->user()->id ==
        $requirement->created_by)
        <form action="{{ route('pms.requirements.submit', $requirement->id) }}" method="POST" class="mb-3">
          @csrf
          <button type="submit" class="btn btn-primary w-100">
            <i class="fas fa-paper-plane"></i> Submit for Approval
          </button>
        </form>
        @endif

        @if(in_array($requirement->status, [
        \App\Models\PMS\Requirement::STATUS_SENT_TO_DIRECTOR
        ]) && auth()->user()->can('approve_requirements'))
        <form action="{{ route('pms.requirements.approve', $requirement->id) }}" method="POST" class="mb-3">
          @csrf
          <button type="submit" class="btn btn-success w-100">
            <i class="fas fa-check"></i> Approve
          </button>
        </form>

        <form action="{{ route('pms.requirements.sent-to-pac', $requirement->id) }}" method="POST" class="mb-3">
          @csrf
          <button type="submit" class="btn btn-primary w-100">
            <i class="fas fa-paper-plane"></i> Sent to PAC
          </button>
        </form>

        <form action="{{ route('pms.requirements.reject', $requirement->id) }}" method="POST" class="mb-3">
          @csrf
          <button type="submit" class="btn btn-danger w-100">
            <i class="fas fa-times"></i> Reject
          </button>
        </form>
        @endif
        `
        @if($requirement->status == \App\Models\PMS\Requirement::STATUS_SENT_TO_PAC && $requirement->allocated_to ==
        null
        &&
        auth()->user()->can('allocate_requirements'))
        <div class="mb-3">
          <form action="{{ route('pms.requirements.allocate', $requirement->id) }}" method="POST">
            @csrf
            <div class="mb-3">
              <label for="allocated_to" class="form-label">Allocate To</label>
              <select name="allocated_to" id="allocated_to" class="form-select" required>
                <option value="">Select User</option>
                @foreach($users as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
              </select>
            </div>
            <button type="submit" class="btn btn-info w-100">
              <i class="fas fa-user-plus"></i> Allocate
            </button>
          </form>
        </div>
        @endif

        @if(in_array($requirement->status,
        [\App\Models\PMS\Requirement::STATUS_APPROVED_BY_DIRECTOR,\App\Models\PMS\Requirement::STATUS_APPROVED_BY_PAC])
        &&
        $requirement->allocated_to == auth()->id() &&
        $requirement->proposal_status== 0)
        <a href="{{ route('pms.proposals.create', $requirement->id) }}" class="btn btn-success w-100 mb-3">
          <i class="fas fa-file-alt"></i> Create Proposal
        </a>
        @endif

        <div class="list-group mt-3">
          <div class="list-group-item">
            <strong>Created By:</strong> {{ $requirement->creator->name }}
          </div>
          <div class="list-group-item">
            <strong>Created At:</strong> {{ $requirement->created_at->format('d M Y H:i') }}
          </div>
          @if( $requirement->allocated_to > 0)
          <div class="list-group-item">
            <strong>Principal investigator:</strong> {{ $requirement->allocatedTo->name }}
          </div>
          <div class="list-group-item">
            <strong>Allocated At:</strong> {{ $requirement->allocated_at->format('d M Y H:i') }}
          </div>
          <div class="list-group-item">
            <strong>Allocated By:</strong> {{ $requirement->allocatedBy->name }}
          </div>
          @endif
          @if( $requirement->proposal_status== 1)
          <div class="list-group-item">
            <span class="badge text-bg-success">Proposal Created</span>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection