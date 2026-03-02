@extends('layouts.app')

@section('title', 'View Requirement')
@section('header', 'View Requirement')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Requirement Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Temp No:</strong> {{ $requirement->temp_no }}</p>
                        <p><strong>Type:</strong> {{ $requirement->type_name }}</p>
                        <p><strong>Category:</strong> {{ $requirement->category->name }}</p>
                        <p><strong>Subcategory:</strong> {{ $requirement->subcategory->name }}</p>
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
                </div>

                @if($requirement->allocated_to)
                <div class="mt-3">
                    <h6>Allocation Details</h6>
                    <p><strong>Allocated To:</strong> {{ $requirement->allocatedTo->name }}</p>
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
                <div class="list-group">
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
                @if($requirement->status == \App\Models\PMS\Requirement::STATUS_INITIATED  )
                <form action="{{ route('pms.requirements.submit', $requirement->id) }}" method="POST" class="mb-3">
                    @csrf
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-paper-plane"></i> Submit for Approval
                    </button>
                </form>
                @endif

                @if(in_array($requirement->status, [
                \App\Models\PMS\Requirement::STATUS_SENT_TO_DIRECTOR,
                \App\Models\PMS\Requirement::STATUS_SENT_TO_PAC
                ]) && auth()->user()->can('approve_requirements'))
                <form action="{{ route('pms.requirements.approve', $requirement->id) }}" method="POST" class="mb-3">
                    @csrf
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-check"></i> Approve
                    </button>
                </form>

                <form action="{{ route('pms.requirements.reject', $requirement->id) }}" method="POST" class="mb-3">
                    @csrf
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="fas fa-times"></i> Reject
                    </button>
                </form>
                @endif

                @if($requirement->status == \App\Models\PMS\Requirement::STATUS_APPROVED_BY_DIRECTOR &&
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

                @if($requirement->status == \App\Models\PMS\Requirement::STATUS_APPROVED_BY_DIRECTOR &&
                $requirement->allocated_to == auth()->id())
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
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
