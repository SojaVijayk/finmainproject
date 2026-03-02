@extends('layouts.audit')

@section('content')
<div class="mb-3">
    <a href="{{ route('audit.tapals.index') }}" class="text-muted">&larr; Back to List</a>
</div>

<div class="row">
    <!-- Left Column: Details -->
    <div class="col-md-8">
        <div class="audit-card">
            <h4 class="mb-4">Tapal Details #{{ $tapal->tapal_number }}</h4>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="text-muted small">Subject</label>
                    <div class="fw-bold">{{ $tapal->subject }}</div>
                </div>
                <div class="col-md-6">
                     <label class="text-muted small">Status</label>
                    <div><span class="badge bg-secondary">{{ $tapal->status }}</span></div>
                </div>
            </div>

             <div class="row mb-3">
                <div class="col-md-6">
                    <label class="text-muted small">Inward Mode</label>
                    <div>{{ $tapal->inward_mode }}</div>
                </div>
                 <div class="col-md-6">
                    <label class="text-muted small">Reference Number</label>
                    <div>{{ $tapal->ref_number ?? '-' }}</div>
                </div>
            </div>

             <div class="row mb-3">
                 <div class="col-md-6">
                    <label class="text-muted small">Letter Date</label>
                    <div>{{ $tapal->letter_date ? \Carbon\Carbon::parse($tapal->letter_date)->format('d-m-Y') : '-' }}</div>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small">Received Date</label>
                    <div>{{ $tapal->received_date ? \Carbon\Carbon::parse($tapal->received_date)->format('d-m-Y') : '-' }}</div>
                </div>
            </div>

            <hr>

            <h5 class="mt-4">Sender Details</h5>
            <div class="row mb-3">
                 <div class="col-md-6">
                    <label class="text-muted small">Name</label>
                    <div>{{ $tapal->from_name }}</div>
                </div>
                 <div class="col-md-6">
                    <label class="text-muted small">Department</label>
                    <div>{{ $tapal->from_department ?? '-' }}</div>
                </div>
            </div>
             <div class="row mb-3">
                 <div class="col-md-12">
                    <label class="text-muted small">Address</label>
                    <div>{{ $tapal->from_address ?? '-' }}</div>
                </div>
            </div>

            <hr>
             <h5 class="mt-4">Description</h5>
             <p class="text-justify">{{ $tapal->description ?? 'No description provided.' }}</p>

             @if($tapal->attachments && count($tapal->attachments) > 0)
             <hr>
             <h5 class="mt-4">Attachments</h5>
             <ul class="list-group">
                 @foreach($tapal->attachments as $att)
                 <li class="list-group-item d-flex justify-content-between align-items-center">
                     <div>
                         <i class="ti ti-file me-2"></i> {{ $att->file_name }} ({{ number_format($att->file_size / 1024, 2) }} KB)
                     </div>
                     <a href="{{ Storage::url($att->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                 </li>
                 @endforeach
             </ul>
             @endif
        </div>
    </div>

    <!-- Right Column: History -->
    <div class="col-md-4">
        <div class="audit-card">
            <h5 class="mb-3">Movement History</h5>
            <div class="timeline">
                @foreach($tapal->movements as $movement)
                <div class="mb-4 pb-2 border-bottom">
                    <div class="d-flex justify-content-between">
                        <strong>{{ $movement->toUser->name ?? 'Unknown' }}</strong>
                        <small class="text-muted">{{ $movement->created_at->format('d M, h:i A') }}</small>
                    </div>
                    <div class="small text-muted mb-1">
                        Status: <span class="badge bg-label-info">{{ $movement->status }}</span>
                    </div>
                    @if($movement->remarks)
                    <div class="fst-italic small bg-light p-2 rounded">
                        "{{ $movement->remarks }}"
                    </div>
                    @endif
                     <div class="small text-muted mt-1">
                        From: {{ $movement->fromUser->name ?? 'System' }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
