@extends('layouts/layoutMaster')

@section('title', 'Tapal - Details')

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
<style>
  .timeline {
    position: relative;
    padding: 0 0 0 1rem;
    margin: 0 0 1rem 0;
  }

  .timeline::before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    width: 4px;
    background: #ddd;
    left: 31px;
    margin: 0;
    border-radius: 2px;
  }

  .time-label {
    position: relative;
    margin-bottom: 1rem;
  }

  .time-label span {
    padding: 0.5rem 1rem;
    color: white;
    border-radius: 4px;
    font-weight: 600;
  }

  .timeline-item {
    position: relative;
    margin-bottom: 1rem;
  }

  .timeline-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: -1.5rem;
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    background: #007bff;
    border: 3px solid white;
  }

  .timeline-header {
    margin: 0;
    padding: 0.5rem 1rem;
    background: #f4f4f4;
    border-radius: 4px;
  }

  .timeline-body {
    padding: 1rem;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-top: 0.5rem;
  }

  .time {
    color: #999;
    font-size: 0.8rem;
  }
</style>
@endsection

@section('content')
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Tapal Tracing - {{ $tapal->tapal_number }}</h3>
    <div class="card-tools">
      <a href="{{ route('tapals.tracing') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Tracing
      </a>
    </div>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="col-md-6">
        <h5>Basic Information</h5>
        <table class="table table-bordered">
          <tr>
            <th>Type</th>
            <td>{{ ucfirst($tapal->type) }}</td>
          </tr>
          <tr>
            <th>Inward Date</th>
            <td>{{ $tapal->inward_date }}</td>
          </tr>
          <tr>
            <th>Received Date</th>
            <td>{{ $tapal->received_date }}</td>
          </tr>
          <tr>
            <th>Mode</th>
            <td>{{ $tapal->inward_mode }}</td>
          </tr>
          @if($tapal->type == 'inward')
          @if($tapal->inward_mode == 'By Mail')
          <tr>
            <th>Mail ID</th>
            <td>{{ $tapal->mail_id }}</td>
          </tr>
          @else
          <tr>
            <th>From Name</th>
            <td>{{ $tapal->from_name }}</td>
          </tr>
          <tr>
            <th>Department</th>
            <td>{{ $tapal->from_department }}</td>
          </tr>
          @endif
          @endif
        </table>
      </div>
      <div class="col-md-6">
        <h5>Document Details</h5>
        <table class="table table-bordered">
          <tr>
            <th>Reference Number</th>
            <td>{{ $tapal->ref_number ?? 'N/A' }}</td>
          </tr>
          <tr>
            <th>Letter Date</th>
            <td>{{ $tapal->letter_date ? $tapal->letter_date : 'N/A' }}</td>
          </tr>
          <tr>
            <th>Subject</th>
            <td>{{ $tapal->subject }}</td>
          </tr>
          <tr>
            <th>Created By</th>
            <td>{{ $tapal->creator->name }}</td>
          </tr>
        </table>
      </div>
    </div>

    @if($tapal->attachments->count() > 0)
    <div class="row mt-4">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title">Attachments</h5>
          </div>
          <div class="card-body">
            <div class="list-group">
              @foreach($tapal->attachments as $attachment)
              <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <i
                      class="fas fa-file-{{ $attachment->file_type == 'application/pdf' ? 'pdf' : (str_contains($attachment->file_type, 'image') ? 'image' : 'alt') }} mr-2"></i>
                    {{ $attachment->file_name }}
                    <small class="text-muted ml-2">
                      ({{ $attachment->file_type }}, {{ round($attachment->file_size / 1024) }} KB)
                    </small>
                  </div>
                  <a href="{{ asset('storage/' . str_replace('public/', '', $attachment->file_path)) }}" target="_blank"
                    class="btn btn-sm btn-info">
                    <i class="fas fa-eye"></i> View
                  </a>
                </div>
              </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>
    @endif

    <div class="row mt-4">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title">Movement History</h5>
          </div>
          <div class="card-body">
            <div class="timeline">
              @foreach($tapal->movements as $movement)
              <div class="time-label">
                <span
                  class="bg-{{ $movement->status == 'Completed' ? 'success' : ($movement->status == 'Accepted' ? 'primary' : 'info') }}">
                  {{ $movement->created_at }}
                </span>
              </div>
              <div>
                <i class="fas fa-arrow-right bg-blue"></i>
                <div class="timeline-item">
                  <span class="time">
                    <i class="fas fa-clock"></i> {{ $movement->created_at }}
                    @if($movement->is_assignment)
                    <span class="badge bg-success ml-2">Assignment</span>
                    @endif
                  </span>
                  <h3 class="timeline-header">
                    {{ $movement->fromUser->name }}
                    <i class="fas fa-arrow-right mx-2"></i>
                    {{ $movement->toUser->name }}
                  </h3>

                  <div class="timeline-body">
                    @if($movement->status === 'Completed')
                    <div class="alert alert-success">
                      <div class="d-flex justify-content-between">
                        <h5 class="mb-1"><i class="fas fa-check-circle"></i> Completion Details</h5>
                        <small class="text-muted">
                          Completed on: {{ $movement->completed_at }}
                        </small>
                      </div>
                      <p class="mt-2 mb-1">{{ $movement->remarks }}</p>
                    </div>
                    @else
                    <p><strong>Status:</strong>
                      <span class="badge bg-{{ $movement->status == 'Pending' ? 'warning' : 'primary' }}">
                        {{ $movement->status }}
                      </span>
                    </p>
                    @if($movement->remarks)
                    <p><strong>Remarks:</strong> {{ $movement->remarks }}</p>
                    @endif
                    @endif
                  </div>
                </div>
              </div>
              @endforeach
              <div>
                <i class="fas fa-clock bg-gray"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
  .timeline {
    position: relative;
    padding: 0 0 0 1rem;
    margin: 0 0 1rem 0;
  }

  .timeline::before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    width: 4px;
    background: #ddd;
    left: 31px;
    margin: 0;
    border-radius: 2px;
  }

  .time-label {
    position: relative;
    margin-bottom: 1rem;
  }

  .time-label span {
    padding: 0.5rem 1rem;
    color: white;
    border-radius: 4px;
    font-weight: 600;
  }

  .timeline-item {
    position: relative;
    margin-bottom: 1rem;
  }

  .timeline-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: -1.5rem;
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    background: #007bff;
    border: 3px solid white;
  }

  .timeline-header {
    margin: 0;
    padding: 0.5rem 1rem;
    background: #f4f4f4;
    border-radius: 4px;
  }

  .timeline-body {
    padding: 1rem;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-top: 0.5rem;
  }

  .time {
    color: #999;
    font-size: 0.8rem;
  }
</style>
@endpush