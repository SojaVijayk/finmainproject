@extends('layouts/layoutMaster')

@section('title', 'Document Show')
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
        <div class="card-header d-flex justify-content-between align-items-center">
          <span>Document Details</span>
          <span class="badge
                        @if($document->status == 'created') bg-warning
                        @elseif($document->status == 'active') bg-success
                        @else bg-danger @endif">
            @if($document->status == 'created') Initiated @elseif($document->status == 'active')
            Created @else {{ ucfirst($document->status) }} @endif
          </span>

          @if($document->status == 'created' && (auth()->user()->id === $document->user_id ||
          auth()->user()->id === $document->authorized_person_id ||
          auth()->user()->id === $document->code->user_id))
          <a href="{{ route('documents.edit', $document) }}" class="btn btn-sm btn-warning">Edit</a>
          @endif

        </div>
        <div class="card-body">
          <div class="row mb-3">
            <label class="col-md-4 col-form-label text-md-end">Document Number</label>
            <div class="col-md-6">
              <p class="form-control-plaintext font-weight-bold">{{ $document->document_number }}</p>
            </div>
          </div>

          <div class="row mb-3">
            <label class="col-md-4 col-form-label text-md-end">Document Type</label>
            <div class="col-md-6">
              <p class="form-control-plaintext">{{ $document->documentType->name }}</p>
            </div>
          </div>

          @if($showFullDetails)
          {{-- @if($document->status == 'created' && (auth()->user()->id === $document->user_id ||
          auth()->user()->id === $document->authorized_person_id ||
          auth()->user()->id === $document->code->user_id)) --}}
          <div class="row mb-3">
            <label class="col-md-4 col-form-label text-md-end">To Address Details</label>
            <div class="col-md-6">
              <p class="form-control-plaintext">{{ $document->to_address_details }}</p>
            </div>
          </div>

          <div class="row mb-3">
            <label class="col-md-4 col-form-label text-md-end">Subject</label>
            <div class="col-md-6">
              <p class="form-control-plaintext">{{ $document->subject }}</p>
            </div>
          </div>

          <div class="row mb-3">
            <label class="col-md-4 col-form-label text-md-end">Project Details</label>
            <div class="col-md-6">
              <p class="form-control-plaintext">{{ $document->project_details ?? 'N/A' }}</p>
            </div>
          </div>

          <div class="row mb-3">
            <label class="col-md-4 col-form-label text-md-end">Code</label>
            <div class="col-md-6">
              <p class="form-control-plaintext">{{ $document->code->code }} ({{ $document->code->user->name }})</p>
            </div>
          </div>

          <div class="row mb-3">
            <label class="col-md-4 col-form-label text-md-end">Authorized Person</label>
            <div class="col-md-6">
              <p class="form-control-plaintext">{{ $document->authorizedPerson->name }}</p>
            </div>
          </div>

          <div class="row mb-3">
            <label class="col-md-4 col-form-label text-md-end">Created By</label>
            <div class="col-md-6">
              <p class="form-control-plaintext">{{ $document->creator->name }}</p>
            </div>
          </div>

          <div class="row mb-3">
            <label class="col-md-4 col-form-label text-md-end">Created At</label>
            <div class="col-md-6">
              <p class="form-control-plaintext">{{ $document->created_at->format('d-m-Y H:i') }}</p>
            </div>
          </div>

          @if($document->status == 'cancelled')
          <div class="row mb-3">
            <label class="col-md-4 col-form-label text-md-end">Cancellation Reason</label>
            <div class="col-md-6">
              <p class="form-control-plaintext">{{ $document->cancellation_reason }}</p>
            </div>
          </div>

          <div class="row mb-3">
            <label class="col-md-4 col-form-label text-md-end">Cancelled By</label>
            <div class="col-md-6">
              <p class="form-control-plaintext">{{ $document->cancelledBy->name }}</p>
            </div>
          </div>

          <div class="row mb-3">
            <label class="col-md-4 col-form-label text-md-end">Cancelled At</label>
            <div class="col-md-6">
              <p class="form-control-plaintext">{{ $document->cancelled_at }}</p>
            </div>
          </div>
          @endif

          <hr>

          <h5>Attachments</h5>
          @if($document->attachments->count() > 0)
          <ul class="list-group mb-3">
            @foreach($document->attachments as $attachment)
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <div>
                {{ $attachment->original_name }} - <span class="badge bg-dark">@if($loop->iteration == 1)Primary
                  Document @else
                  Revised Document @endif</span>
                <br>
                <small class="text-muted">
                  Uploaded: {{ $attachment->created_at->format('d-m-Y H:i') }}
                </small>

              </div>
              <div>
                {{-- <a href="{{ route('documents.attachment.download', $attachment) }}" class="btn btn-sm btn-primary">
                  Download
                </a> --}}
                <a href="{{ asset('storage/' . str_replace('public/', '', $attachment->file_path)) }}" target="_blank"
                  class="btn btn-sm btn-primary mr-2">
                  <i class="fas fa-eye"></i> Download
                </a>
                @if($document->status == 'created' || $document->status == 'revised' && $attachment->status == 0 )
                <form
                  action="{{ route('documents.attachment.remove', ['document' => $document, 'attachment' => $attachment]) }}"
                  method="POST" style="display: inline-block;"
                  onsubmit="return confirm('Are you sure you want to remove this attachment?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger">
                    Remove
                  </button>
                </form>
                @endif
              </div>
            </li>
            @endforeach
          </ul>
          @else
          <p>No attachments found for this document.</p>
          @endif



          @if(($document->status == 'created' || $document->status == 'revised') &&
          $document->attachments->where('status', 0)->count() ==0)
          <hr>
          <h5>Upload Attachment</h5>
          <form method="POST" action="{{ route('documents.upload', $document) }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
              <input type="file" class="form-control" name="attachment" required>
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
          </form>
          @endif
          @if(($document->status == 'created' || $document->status == 'revised') &&
          $document->attachments->where('status',0)->count() > 0)
          <hr>
          <form method="POST" action="{{ route('documents.confirm', $document) }}">
            @csrf
            <button type="submit" class="btn btn-success">Confirm Document</button>
          </form>
          @endif

          @if($document->status != 'cancelled')
          <hr>
          <div class="accordion accordion-header-primary" id="accordionStyle1">
            <div class="accordion-item active">
              <h2 class="accordion-header d-flex align-items-center">
                <button type="button" class="accordion-button collapsed text-danger" data-bs-toggle="collapse"
                  data-bs-target="#accordionStyle1-1" aria-expanded="false"> Cancel Document Number</button>
              </h2>

              <div id="accordionStyle1-1" class="accordion-collapse collapse" data-bs-parent="#accordionStyle1">
                <div class="accordion-body">
                  <form method="POST" action="{{ route('documents.cancel', $document) }}">
                    @csrf
                    <div class="mb-3">
                      <textarea class="form-control" name="cancellation_reason" placeholder="Cancellation reason"
                        required></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger">Cancel Document number</button>
                  </form>
                </div>
              </div>
            </div>
          </div>
          {{-- <h5>Cancel Document</h5>
          <form method="POST" action="{{ route('documents.cancel', $document) }}">
            @csrf
            <div class="mb-3">
              <textarea class="form-control" name="cancellation_reason" placeholder="Cancellation reason"
                required></textarea>
            </div>
            <button type="submit" class="btn btn-danger">Cancel Document number</button>
          </form> --}}
          @endif

          @if($document->status == 'active')
          <hr>
          <div class="accordion accordion-header-primary" id="accordionStyle1">
            <div class="accordion-item">
              <h2 class="accordion-header d-flex align-items-center">
                <button type="button" class="accordion-button collapsed text-warning" data-bs-toggle="collapse"
                  data-bs-target="#accordionStyle1-2" aria-expanded="false">Revise Document Attachment</button>
              </h2>
              <div id="accordionStyle1-2" class="accordion-collapse collapse" data-bs-parent="#accordionStyle1">
                <div class="accordion-body">
                  <form method="POST" action="{{ route('documents.revise', $document) }}">
                    @csrf
                    <div class="mb-3">
                      <textarea class="form-control" name="revision_reason" placeholder="Revision reason"
                        required></textarea>
                    </div>
                    <button type="submit" class="btn btn-dark">Revise Document Attachment</button>
                  </form>
                </div>
              </div>
            </div>
          </div>

          {{-- <h5>Revise Document Attachment</h5>
          <form method="POST" action="{{ route('documents.revise', $document) }}">
            @csrf
            <div class="mb-3">
              <textarea class="form-control" name="revision_reason" placeholder="Revision reason" required></textarea>
            </div>
            <button type="submit" class="btn btn-dark">Revise Document Attachment</button>
          </form> --}}
          @endif


          @if($document->status == 'active')
          <hr>
          <div class="accordion accordion-header-primary" id="accordionStyle5">
            <div class="accordion-item">
              <h2 class="accordion-header d-flex align-items-center">
                <button type="button" class="accordion-button collapsed text-warning" data-bs-toggle="collapse"
                  data-bs-target="#accordionStyle1-5" aria-expanded="false">Upload Part file Document
                  Attachment</button>
              </h2>
              <div id="accordionStyle1-5" class="accordion-collapse collapse" data-bs-parent="#accordionStyle5">
                <div class="accordion-body">

                  <h5>Upload Attachment</h5>
                  <form method="POST" action="{{ route('documents.part.upload', $document) }}"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                      <input class="form-control" name="document_no"
                        placeholder="new document number eg:{{$document->document_number}}-1"
                        value="{{$document->document_number.'-'.$document->attachments->count()+1}}" readonly
                        required></input>
                    </div>
                    <div class="mb-3">
                      <input type="file" class="form-control" name="attachment" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Upload</button>
                  </form>

                </div>
              </div>
            </div>
          </div>
          @endif

          {{-- @if($document->despatches->count())
          <hr>
          <div class="accordion" id="accordionWithIcon1">
            <div class="accordion-item">
              <h2 class="accordion-header d-flex align-items-center">
                <button type="button" class="accordion-button collapsed text-primary" data-bs-toggle="collapse"
                  data-bs-target="#accordionWithIcon-4" aria-expanded="false">
                  Despatch Records
                </button>
              </h2>
              <div id="accordionWithIcon-4" class="accordion-collapse collapse">
                <div class="accordion-body">
                  <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                      <thead class="table-light">
                        <tr>
                          <th>#</th>
                          <th>Type</th>
                          <th>Date</th>
                          <th>Tracking No.</th>
                          <th>Courier</th>
                          <th>Send By</th>
                          <th>Mail ID</th>
                          <th>Ack File</th>
                          <th>Receipt</th>
                          <th>Created By</th>
                          <th>Created At</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($document->despatches as $index => $d)
                        <tr>
                          <td>{{ $index + 1 }}</td>
                          <td>{{ $d->type->name }}</td>
                          <td>{{ $d->despatch_date }}</td>
                          <td>{{ $d->tracking_number ?? '-' }}</td>
                          <td>{{ $d->courier_name ?? '-' }}</td>
                          <td>{{ $d->send_by ?? '-' }}</td>
                          <td>{{ $d->mail_id ?? '-' }}</td>
                          <td>
                            @if($d->acknowledgement_file)
                            <a href="{{ Storage::url($d->acknowledgement_file) }}" target="_blank"
                              class="btn btn-sm btn-success">
                              <i class="fas fa-eye"></i> View
                            </a>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                          </td>
                          <td>
                            @if($d->despatch_receipt)
                            <a href="{{ Storage::url($d->despatch_receipt) }}" target="_blank"
                              class="btn btn-sm btn-info">
                              <i class="fas fa-eye"></i> View
                            </a>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                          </td>
                          <td>{{ $d->creator->name ?? '-' }}</td>
                          <td>{{ $d->created_at->format('d-M-Y') }}</td>
                          <td>
                            <div class="btn-group">
                              <a href="{{ route('despatch.edit', $d->id) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                              </a>

                              <form action="{{ route('despatch.destroy', $d->id) }}" method="POST"
                                onsubmit="return confirm('Are you sure you want to delete this despatch?')"
                                style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                  <i class="fas fa-trash-alt"></i>
                                </button>
                              </form>

                              <form action="{{ route('despatch.uploadAck', $d->id) }}" method="POST"
                                enctype="multipart/form-data" class="d-inline-flex align-items-center">
                                @csrf
                                <input type="file" name="acknowledgement_file" accept=".pdf,.jpg,.jpeg,.png"
                                  class="form-control form-control-sm me-1" style="width: 120px;">
                                <button type="submit" class="btn btn-sm btn-primary">
                                  <i class="fas fa-upload"></i>
                                </button>
                              </form>
                            </div>
                          </td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
          @endif --}}
          @if($document->despatches->count())
          <hr>
          <div class="accordion" id="accordionWithIcon1">
            <div class="accordion-item">
              <h2 class="accordion-header d-flex align-items-center">
                <button type="button" class="accordion-button collapsed text-success" data-bs-toggle="collapse"
                  data-bs-target="#accordionWithIcon-4" aria-expanded="false">
                  Despatch Records
                </button>
              </h2>
              <div id="accordionWithIcon-4" class="accordion-collapse collapse">
                <div class="accordion-body">
                  <div class="row g-3">
                    @foreach($document->despatches as $index => $d)
                    <div class="col-12 col-md-6 col-lg-4">
                      <div class="card shadow-sm border-primary h-100">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                          <strong class="text-primary">#{{ $index + 1 }} - {{ $d->type->name }}</strong>
                          <span class="badge bg-primary">{{ \Carbon\Carbon::parse($d->despatch_date)->format('d-M-Y')
                            }}</span>
                        </div>

                        <div class="card-body p-3">
                          <div class="mb-2">
                            <small class="text-muted d-block">Tracking No:</small>
                            <span class="fw-semibold">{{ $d->tracking_number ?? '-' }}</span>
                          </div>

                          <div class="mb-2">
                            <small class="text-muted d-block">Courier:</small>
                            <span class="fw-semibold">{{ $d->courier_name ?? '-' }}</span>
                          </div>

                          <div class="mb-2">
                            <small class="text-muted d-block">Send By:</small>
                            <span class="fw-semibold">{{ $d->send_by ?? '-' }}</span>
                          </div>

                          <div class="mb-2">
                            <small class="text-muted d-block">Mail ID:</small>
                            <span class="fw-semibold">{{ $d->mail_id ?? '-' }}</span>
                          </div>

                          <div class="d-flex flex-wrap gap-2 mt-3">
                            @if($d->acknowledgement_file)
                            <a href="{{ Storage::url($d->acknowledgement_file) }}" target="_blank"
                              class="btn btn-sm btn-success">
                              <i class="fas fa-eye"></i> Ack
                            </a>
                            @else
                            <span class="badge bg-secondary">No Ack</span>
                            @endif

                            @if($d->despatch_receipt)
                            <a href="{{ Storage::url($d->despatch_receipt) }}" target="_blank"
                              class="btn btn-sm btn-info">
                              <i class="fas fa-eye"></i> Receipt
                            </a>
                            @else
                            <span class="badge bg-secondary">No Receipt</span>
                            @endif
                          </div>

                          <hr>

                          <div class="small text-muted">
                            <div><strong>Created By:</strong> {{ $d->creator->name ?? '-' }}</div>
                            <div><strong>Created:</strong> {{ $d->created_at->format('d-M-Y H:i') }}</div>
                          </div>
                        </div>

                        <div
                          class="card-footer bg-light d-flex flex-wrap justify-content-between align-items-center gap-2">
                          {{-- <a href="{{ route('despatch.edit', $d->id) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i> Edit
                          </a> --}}

                          <form action="{{ route('despatch.destroy', $d->id) }}" method="POST"
                            onsubmit="return confirm('Are you sure you want to delete this despatch?')"
                            style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                              <i class="fas fa-trash-alt"></i> Delete
                            </button>
                          </form>
                          <small>Upload Acknowledgment</small>
                          <form action="{{ route('despatch.uploadAck', $d->id) }}" method="POST"
                            enctype="multipart/form-data" class="d-flex align-items-center gap-1">
                            @csrf
                            <input type="file" name="acknowledgement_file" accept=".pdf,.jpg,.jpeg,.png"
                              class="form-control form-control-sm" style="width: 130px;">
                            <button type="submit" class="btn btn-sm btn-primary">
                              <i class="fas fa-upload"></i> Ack
                            </button>
                          </form>
                        </div>
                      </div>
                    </div>
                    @endforeach
                  </div>
                </div>
              </div>
            </div>
          </div>
          @endif



          @else
          <div class="alert alert-primary">
            You don't have permission to view all details of this document.
          </div>
          @endif



          <hr>
          <div class="accordion" id="accordionWithIcon">
            <div class="accordion-item">
              <h2 class="accordion-header d-flex align-items-center">
                <button type="button" class="accordion-button collapsed text-primary" data-bs-toggle="collapse"
                  data-bs-target="#accordionWithIcon-3" aria-expanded="false">
                  History
                </button>
              </h2>
              <div id="accordionWithIcon-3" class="accordion-collapse collapse">
                <div class="accordion-body">
                  <ul class="list-group">
                    @foreach($document->histories as $history)
                    <li class="list-group-item">
                      <strong>{{ $history->user->name }}</strong> -
                      {{ ucfirst($history->action) }} -
                      {{ $history->created_at->format('d-m-Y H:i') }}
                      @if($history->details)
                      <br><small>{{ $history->details }}</small>
                      @endif
                    </li>
                    @endforeach
                  </ul>
                </div>
              </div>
            </div>
          </div>


        </div>
      </div>
    </div>
  </div>
</div>
@endsection