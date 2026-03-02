@extends('layouts.audit')

@section('page-style')
<style>
    .pdf-frame { width: 100%; height: 600px; border: 1px solid #e0e0e0; }
    .doc-preview-img { max-width: 100%; height: auto; border: 1px solid #e0e0e0; }
</style>
@endsection

@section('content')
<div class="mb-3">
    <a href="{{ route('audit.documents.index') }}" class="text-muted">&larr; Back to List</a>
</div>

<div class="row">
    <!-- Left Column: Details -->
    <div class="col-md-9">
        <div class="audit-card mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                 <h4 class="mb-0">Document #{{ $document->document_number }}</h4>
                 <span class="badge {{ $document->status == 'active' ? 'bg-success' : 'bg-secondary' }} fs-6">
                     {{ $document->status == 'created' ? 'Initiated' : (ucfirst($document->status)) }}
                 </span>
            </div>

            <div class="table-responsive mb-4">
                <table class="table table-borderless">
                    <tbody>
                        <tr>
                            <td class="text-muted" width="20%">Subject</td>
                            <td class="fw-bold fs-5">{{ $document->subject }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Document Type</td>
                            <td>{{ $document->documentType->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Code</td>
                            <td>{{ $document->code->code ?? '-' }} ({{ $document->code->user->name ?? '' }})</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created By</td>
                            <td>{{ $document->creator->name ?? 'Unknown' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Authorized Person</td>
                            <td>{{ $document->authorizedPerson->name ?? 'Unknown' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Project Details</td>
                            <td>{{ $document->project_details ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">To Address</td>
                            <td>{!! nl2br(e($document->to_address_details)) !!}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created At</td>
                            <td>{{ $document->created_at->format('d-m-Y H:i') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Cancellation Details -->
            @if($document->status == 'cancelled')
            <div class="alert alert-danger">
                 <h5 class="alert-heading">Document Cancelled</h5>
                 <p class="mb-1"><strong>Reason:</strong> {{ $document->cancellation_reason }}</p>
                 <hr>
                 <small>Cancelled by {{ $document->cancelledBy->name ?? 'Unknown' }} on {{ $document->cancelled_at }}</small>
            </div>
            @endif

            <!-- Attachments & Preview -->
            @if($document->attachments && count($document->attachments) > 0)
             <hr class="mt-4">
             <h5 class="mb-3">Document Preview</h5>

             <ul class="nav nav-tabs" id="docTabs" role="tablist">
                @foreach($document->attachments as $index => $att)
                 <li class="nav-item" role="presentation">
                     <button class="nav-link {{ $index === 0 ? 'active' : '' }}" id="tab-{{ $att->id }}" data-bs-toggle="tab" data-bs-target="#content-{{ $att->id }}" type="button" role="tab">
                         {{ $att->original_name }}
                         @if($index === 0) <span class="badge bg-label-primary ms-1">Primary</span> @endif
                     </button>
                 </li>
                 @endforeach
             </ul>

             <div class="tab-content border border-top-0 p-3" id="docTabsContent">
                 @foreach($document->attachments as $index => $att)
                 <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="content-{{ $att->id }}" role="tabpanel">
                     <div class="float-end mb-2">
                         <a href="{{ route('audit.documents.download', $att->id) }}" class="btn btn-sm btn-primary"><i class="ti ti-download me-1"></i> Download</a>
                     </div>
                     <div class="clearfix"></div>

                     <!-- Preview Area -->
                     @php
                        $extension = strtolower(pathinfo($att->original_name, PATHINFO_EXTENSION));
                        $isPdf = $extension === 'pdf';
                        $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif']);
                        // Use Storage::url for direct public access if mapped
                        $fileUrl = Storage::url($att->file_path);
                     @endphp

                     @if($isPdf)
                        <iframe src="{{ $fileUrl }}" class="pdf-frame">
                            <p>Your browser does not support PDFs. <a href="{{ $fileUrl }}">Download the PDF</a>.</p>
                        </iframe>
                     @elseif($isImage)
                        <div class="text-center">
                            <img src="{{ $fileUrl }}" class="doc-preview-img" alt="Document Preview">
                        </div>
                     @else
                        <div class="alert alert-secondary text-center py-5">
                            <h4 class="alert-heading"><i class="ti ti-file-unknown fs-1"></i></h4>
                            <p>Preview not available for {{ $extension }} files.</p>
                            <a href="{{ route('audit.documents.download', $att->id) }}" class="btn btn-outline-secondary">Download File</a>
                        </div>
                     @endif
                 </div>
                 @endforeach
             </div>
             @endif

             <!-- Despatch Details -->
             @if($document->despatches && count($document->despatches) > 0)
             <hr class="mt-5">
             <h5 class="mb-3">Despatch Records</h5>
             <div class="table-responsive">
                 <table class="table table-bordered table-striped">
                     <thead class="table-light">
                         <tr>
                             <th>#</th>
                             <th>Type</th>
                             <th>Date</th>
                             <th>Tracking Details</th>
                             <th>Ack</th>
                         </tr>
                     </thead>
                     <tbody>
                        @foreach($document->despatches as $index => $d)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="fw-bold text-primary">{{ $d->type->name ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($d->despatch_date)->format('d-M-Y') }}</td>
                            <td>
                                <div><strong>Courier:</strong> {{ $d->courier_name ?? '-' }}</div>
                                <div><strong>Tracking:</strong> {{ $d->tracking_number ?? '-' }}</div>
                                <div><strong>Sent By:</strong> {{ $d->send_by ?? '-' }}</div>
                            </td>
                            <td>
                                @if($d->acknowledgement_file)
                                <a href="{{ Storage::url($d->acknowledgement_file) }}" target="_blank" class="badge bg-success text-white text-decoration-none">
                                    <i class="ti ti-eye"></i> View Ack
                                </a>
                                @else
                                <span class="badge bg-secondary">Pending</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                     </tbody>
                 </table>
             </div>
             @endif

        </div>
    </div>

    <!-- Right Column: History -->
    <div class="col-md-3">
        <div class="audit-card">
            <h5 class="mb-3">History</h5>
             <div class="timeline">
                @foreach($document->histories as $hist)
                <div class="mb-3 border-bottom pb-2">
                    <small class="text-muted d-block">{{ $hist->created_at->format('d M Y, h:i A') }}</small>
                    <div class="fw-bold">{{ ucfirst(str_replace('_', ' ', $hist->action)) }}</div>
                    @if($hist->details)
                    <div class="small text-muted">{{ $hist->details }}</div>
                    @endif
                    <div class="small mt-1 text-primary">By: {{ $hist->user->name ?? 'System' }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
