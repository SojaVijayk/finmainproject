@extends('layouts/layoutMaster')

@section('title', 'View Proposals')

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
@section('page-style')
<style>
  .timeline {
    position: relative;
    padding-left: 1rem;
  }

  .timeline-item {
    position: relative;
    padding-bottom: 1.5rem;
    border-left: 1px solid #e9ecef;
  }

  .timeline-item:last-child {
    padding-bottom: 0;
    border-left: 1px solid transparent;
  }

  .timeline-item-marker {
    position: absolute;
    left: -0.5rem;
    width: 1rem;
    height: 1rem;
    margin-top: 0.25rem;
  }

  .timeline-item-marker-indicator {
    width: 100%;
    height: 100%;
    border-radius: 100%;
    border: 2px solid #fff;
  }

  .timeline-item-content {
    padding-left: 1.5rem;
  }
</style>
@endsection
@section('page-script')
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const clientStatus = document.getElementById("client_status");
    const workorderField = document.getElementById("workorder_field");
    const workorderInput = document.getElementById("documents");

    function toggleWorkorder() {
      if (clientStatus.value === "accepted") {
        workorderField.style.display = "block";
        {{--  workorderInput.setAttribute("required", "required");  --}}
      } else {
        workorderField.style.display = "none";
        {{--  workorderInput.removeAttribute("required");
        workorderInput.value = ""; // clear file input when hidden  --}}
      }
    }

    clientStatus.addEventListener("change", toggleWorkorder);
     clientStatus.addEventListener("change", toggleRequired);
    toggleWorkorder(); // run once on load (useful if editing existing)
    toggleRequired();

    function toggleRequired() {
    const hasSelectedFiles = document.querySelectorAll('input[name="selected_files[]"]').length > 0;
    workorderInput.required = !hasSelectedFiles;
  }


   ['tapal', 'documents'].forEach(type => {
  fetch(`/pms/attachments/${type}`)
    .then(res => res.text())
    .then(html => {
      const target = document.getElementById(`${type}-documents-list`);
      if (target) target.innerHTML = html;
    })
    .catch(err => console.error('Attachment fetch failed for', type, err));
});
    document.getElementById('selectDocumentsBtn').addEventListener('click', function() {
  const selectedFiles = [];
   setTimeout(toggleRequired, 300);
  document.querySelectorAll('.tapal-checkbox:checked, .document-checkbox:checked, .custom-checkbox:checked')
    .forEach(el => selectedFiles.push(el.dataset.file));

  if (selectedFiles.length === 0) {
    alert('Please select at least one file.');
    return;
  }

  const form = document.getElementById('clientUpdateform');
  const list = document.getElementById('attachedDocumentsList');
  const wrapper = document.getElementById('attachedDocumentsWrapper');
  wrapper.style.display = 'block';

  // Add to form and list
  selectedFiles.forEach(filePath => {
    // Avoid duplicates
    if (form.querySelector(`input[value="${filePath}"]`)) return;

    // Hidden input for submission
    const hidden = document.createElement('input');
    hidden.type = 'hidden';
    hidden.name = 'selected_files[]';
    hidden.value = filePath;
    form.appendChild(hidden);

    // Display in visible list
    const li = document.createElement('li');
    li.className = 'list-group-item d-flex justify-content-between align-items-center';
    li.innerHTML = `
     <small> <span>${filePath.replace('public/', '')}</span><br>
      <button type="button" class="btn btn-sm btn-outline-danger remove-attached">&times;</button></small>
    `;
    list.appendChild(li);
  });

  // Allow removing attachments
  list.querySelectorAll('.remove-attached').forEach(btn => {
    btn.addEventListener('click', function() {
      setTimeout(toggleRequired, 300);
      const li = this.closest('li');
      const filePath = li.querySelector('span').textContent.trim();
      li.remove();
      form.querySelectorAll('input[name="selected_files[]"]').forEach(input => {
        if (input.value.includes(filePath)) input.remove();
      });
      if (!list.querySelectorAll('li').length) wrapper.style.display = 'none';
    });
  });

  // Close modal
  document.querySelector('#browseDocumentsModal .btn-close').click();
});



  });
</script>
@endsection


@section('content')
<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">Proposal Details - {{ $proposal->requirement->project_title }}</h5>
          <span class="badge bg-{{ $proposal->status_badge_color }}">
            {{ $proposal->status_name }}
          </span>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-4">
            <p><strong>Requirement:</strong> {{ $proposal->requirement->temp_no }}</p>
            <p><strong>Budget:</strong> ₹{{ number_format($proposal->budget, 2) }}</p>
            <p><strong>Tenure:</strong> {{ $proposal->tenure }}</p>
            <p><strong>Start Date:</strong> @if(!is_null($proposal->expected_start_date)) {{
              $proposal->expected_start_date->format('d M Y') }} @endif</p>
          </div>
        </div>
        <div class="col-md-12">
          <p><strong>End Date:</strong> @if(!is_null($proposal->expected_end_date)){{
            $proposal->expected_end_date->format('d M Y') }} @endif</p>
          <p><strong>Estimated Expense:</strong> ₹{{ number_format($proposal->estimated_expense, 2) }}</p>
          {{-- @if($proposal->expenseComponents->count() > 0)
          <div class="card mt-4">
            <div class="card-header">
              <h5 class="card-title">Estimated Expense Breakdown</h5>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-striped">
                  <thead>
                    <tr>
                      <th>Category</th>
                      <th>Component</th>
                      <th>Amount (₹)</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($proposal->expenseComponents as $component)
                    <tr>
                      <td>{{ $component->category->name }}</td>
                      <td>{{ $component->component }}</td>
                      <td>₹{{ number_format($component->amount, 2) }}</td>
                    </tr>
                    @endforeach
                  </tbody>
                  <tfoot>
                    <tr>
                      <th colspan="2" class="text-end">Total Estimated Expense:</th>
                      <th>₹{{ number_format($proposal->estimated_expense, 2) }}</th>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>
          @endif --}}

          @if($proposal->expenseComponents->count() > 0)
          <div class="card mt-4">
            <div class="card-header">
              <h5 class="card-title mb-0">Estimated Expense Breakdown</h5>
            </div>
            <div class="card-body">
              @php
              $grouped = $proposal->expenseComponents->groupBy('group_name');
              $grandTotal = 0;
              @endphp

              <div class="accordion" id="proposalExpenseAccordion">
                @foreach($grouped as $groupName => $components)
                @php

                $groupId = Str::slug($groupName ?? 'ungrouped', '_');
                $groupTotal = $components->sum('amount');
                $grandTotal += $groupTotal;
                @endphp

                <div class="accordion-item border rounded mb-2 shadow-sm">
                  <h2 class="accordion-header" id="heading_{{ $groupId }}">
                    <button class="accordion-button collapsed d-flex justify-content-between" type="button"
                      data-bs-toggle="collapse" data-bs-target="#collapse_{{ $groupId }}" aria-expanded="false"
                      aria-controls="collapse_{{ $groupId }}">
                      <div class="d-flex flex-column">
                        <span class="fw-bold text-primary">{{ $groupName ?? 'Ungrouped' }}</span>
                        <small class="text-muted">Subtotal: ₹{{ number_format($groupTotal, 2) }}</small>
                      </div>
                    </button>
                  </h2>
                  <div id="collapse_{{ $groupId }}" class="accordion-collapse collapse"
                    aria-labelledby="heading_{{ $groupId }}" data-bs-parent="#proposalExpenseAccordion">
                    <div class="accordion-body p-2">
                      <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0">
                          <thead class="table-light">
                            <tr>
                              <th style="width:25%">Category</th>
                              <th style="width:35%">Component</th>
                              @if($groupName === 'HR')
                              <th style="width:10%" class="text-end">Persondays</th>
                              <th style="width:15%" class="text-end">Rate (₹)</th>
                              @endif
                              <th style="width:20%" class="text-end">Amount (₹)</th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach($components as $component)
                            <tr>
                              <td>{{ $component->category->name ?? 'N/A' }}</td>
                              <td>{{ $component->component }}</td>
                              @if($groupName === 'HR')
                              <td class="text-end">{{ $component->mandays ?? '-' }}</td>
                              <td class="text-end">{{ $component->rate ? number_format($component->rate, 2) : '-' }}
                              </td>
                              @endif
                              <td class="text-end">₹{{ number_format($component->amount, 2) }}</td>
                            </tr>
                            @endforeach
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
                @endforeach
              </div>

              <div class="border-top pt-3 mt-3 text-end">
                <strong>Grand Total Estimated Expense: ₹{{ number_format($grandTotal, 2) }}</strong>
              </div>
            </div>
          </div>
          @endif


          <p><strong>Expected Revenue:</strong> ₹{{ number_format($proposal->revenue, 2) }}</p>
          <p><strong>Created By:</strong> {{ $proposal->creator->name }}</p>
        </div>


        @if($proposal->technical_details)
        <div class="mt-3">
          <h6>Technical Details</h6>
          <p>{{ $proposal->technical_details }}</p>
        </div>
        @endif

        @if($proposal->methodology)
        <div class="mt-3">
          <h6>Methodology</h6>
          <p>{{ $proposal->methodology }}</p>
        </div>
        @endif
      </div>
    </div>

    <div class="card mt-4">
      <div class="card-header">
        <h5 class="card-title">Documents</h5>
      </div>
      <div class="card-body">
        @if($proposal->documents->count() > 0)
        <div class="list-group">
          @foreach($proposal->documents->where('category','proposal') as $document)
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
        <hr>
        <h3>Work Order</h3>
        <div class="list-group">
          @foreach($proposal->documents->where('category','Work order') as $document)
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

    @if($proposal->statusLogs->count() > 0)
    <div class="card mt-4">
      <div class="card-header">
        <h5 class="card-title">Status History</h5>
      </div>
      <div class="card-body">
        <div class="timeline">
          @foreach($proposal->statusLogs as $log)
          <div class="timeline-item">
            <div class="timeline-item-marker">
              <div
                class="timeline-item-marker-indicator bg-{{ $log->to_status == \App\Models\PMS\Proposal::STATUS_APPROVED_BY_DIRECTOR ? 'success' : ($log->to_status == \App\Models\PMS\Proposal::STATUS_REJECTED ? 'danger' : 'info') }}">
              </div>
            </div>
            <div class="timeline-item-content">
              <div class="d-flex justify-content-between">
                <strong>{{ $log->to_status_name }}</strong>
                <small class="text-muted">{{ $log->created_at->format('d M Y H:i') }}</small>
              </div>
              <p class="mb-0">Changed by: {{ $log->changedBy->name }}</p>
              @if($log->comments)
              <p class="mt-2">{{ $log->comments }}</p>
              @endif
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
    @endif
  </div>

  <div class="col-md-4">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title">Actions</h5>
      </div>
      <div class="card-body">
        @if(($proposal->status == \App\Models\PMS\Proposal::STATUS_CREATED || $proposal->status ==
        \App\Models\PMS\Proposal::STATUS_RETURNED_FOR_CLARIFICATION) &&
        $proposal->created_by == auth()->id())
        <form action="{{ route('pms.proposals.submit', $proposal->id) }}" method="POST" class="mb-3">
          @csrf
          <button type="submit" class="btn btn-primary w-100">
            <i class="fas fa-paper-plane"></i> Submit for Approval
          </button>
        </form>
        @endif

        @if($proposal->status == \App\Models\PMS\Proposal::STATUS_SENT_TO_DIRECTOR &&
        auth()->user()->can('approve_proposals'))
        <form action="{{ route('pms.proposals.approve', $proposal->id) }}" method="POST" class="mb-3">
          @csrf
          <button type="submit" class="btn btn-success w-100">
            <i class="fas fa-check"></i> Approve
          </button>
        </form>

        <form action="{{ route('pms.proposals.reject', $proposal->id) }}" method="POST" class="mb-3">
          @csrf
          <button type="submit" class="btn btn-danger w-100">
            <i class="fas fa-times"></i> Reject
          </button>
        </form>

        <div class="mb-3">
          <form action="{{ route('pms.proposals.return', $proposal->id) }}" method="POST">
            @csrf
            <div class="mb-3">
              <label for="comments" class="form-label">Comments</label>
              <textarea name="comments" id="comments" class="form-control" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-warning w-100">
              <i class="fas fa-undo"></i> Return for Clarification
            </button>
          </form>
        </div>
        @endif

        @if($proposal->status == \App\Models\PMS\Proposal::STATUS_RETURNED_FOR_CLARIFICATION &&
        $proposal->created_by == auth()->id())
        <a href="{{ route('pms.proposals.edit', $proposal->id) }}" class="btn btn-warning w-100 mb-3">
          <i class="fas fa-edit"></i> Update Proposal
        </a>
        @endif

        @if($proposal->status == \App\Models\PMS\Proposal::STATUS_APPROVED_BY_DIRECTOR &&
        $proposal->requirement->allocated_to == auth()->id())

        @if($proposal->client_status ==NULL || $proposal->client_status == 'resubmit_requested')
        <form action="{{ route('pms.proposals.send', $proposal->id) }}" method="POST" class="mb-3">
          @csrf
          <button type="submit" class="btn btn-info w-100">
            <i class="fas fa-envelope"></i> Send to Client
          </button>
        </form>

        <div class="mb-3">
          <form id="clientUpdateform" action="{{ route('pms.proposals.client-status', $proposal->id) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
              <label for="client_status" class="form-label">Client Status</label>
              <select name="client_status" id="client_status" class="form-select" required>
                <option value="">Select Status</option>
                <option value="accepted">Accepted</option>
                <option value="rejected">Rejected</option>
                <option value="resubmit_requested">Resubmit Requested</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="client_comments" class="form-label">Comments</label>
              <textarea name="client_comments" id="client_comments" class="form-control" rows="3"></textarea>
            </div>
            {{-- <div class="mb-3 " id="workorder_field" style="display: none;">
              <label for="documents" class="form-label">Documents (Workorder)*</label>
              <input type="file" id="documents" name="documents[]" class="form-control" multiple>
            </div>
            --}}

            <div class="mb-3 " id="workorder_field" style="display: none;">
              <div class="row mb-3">
                <div class="col-md-12">
                  <label for="documents" class="form-label">Documents (Workorder)*</label>
                  <div class="input-group">
                    <input type="file" name="documents[]" id="documents" name="documents[]" class="form-control"
                      multiple>

                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal"
                      data-bs-target="#browseDocumentsModal">
                      Browse Existing
                    </button>
                  </div>
                  <small class="text-muted">Upload new files or select from Tapal/Document system</small>
                </div>
                <div class="col-md-12">
                  <div id="attachedDocumentsWrapper" class="mt-2" style="display:none;">
                    <small>Attached Workorder Files From Tapals/Documents</small>

                    <ul id="attachedDocumentsList" class="list-group"></ul>

                  </div>
                </div>
              </div>

            </div>





            <button type="submit" class="btn btn-success w-100">
              <i class="fas fa-check-circle"></i> Update Client Status
            </button>
          </form>
        </div>
        @include('pms/attachments/partials/browse-modal')
        @endif
        @endif


        @if($proposal->client_status == 'accepted' && $proposal->workOrderDocuments()->count() > 0 &&
        $proposal->requirement->allocated_to == auth()->id() &&
        $proposal->project_status== 0)
        <a href="{{ route('pms.projects.create', $proposal->id) }}" class="btn btn-success w-100">
          <i class="fas fa-project-diagram"></i> Create Project
        </a>
        @endif
      </div>
    </div>

    <div class="card mt-4">
      <div class="card-header">
        <h5 class="card-title">Requirement Details</h5>
      </div>
      <div class="card-body">
        <p><strong>Client:</strong> {{ $proposal->requirement->client->client_name }}</p>
        <p><strong>Category:</strong> {{ $proposal->requirement->category->name }}</p>
        <p><strong>Subcategory:</strong> {{ $proposal->requirement->subcategory->name ?? 'N/A' }}</p>
        <a href="{{ route('pms.requirements.show', $proposal->requirement->id) }}"
          class="btn btn-sm btn-info w-100 mt-2">
          <i class="fas fa-eye"></i> View Requirement
        </a>
      </div>
    </div>

    @if($proposal->clientStatusLogs->count() > 0)
    {{-- Client Status Timeline --}}
    <div class="card mt-4">
      <div class="card-header">
        <h5 class="mb-0">Client Status Timeline</h5>
      </div>
      <div class="card-body">
        @if($proposal->clientStatusLogs->isEmpty())
        <p class="text-muted">No client status updates yet.</p>
        @else
        <ul class="timeline list-unstyled">
          @foreach($proposal->clientStatusLogs->sortByDesc('created_at') as $log)
          <li class="timeline-item mb-5 position-relative">
            <span class="timeline-dot bg-primary"></span>
            <div class="ms-4">
              <h6 class="fw-bold mb-1">
                {{ ucfirst($log->to_status) }}
                <small class="text-muted">({{ $log->created_at->format('d M Y, h:i A') }})</small>
              </h6>
              @if($log->from_status)
              <p class="text-muted small mb-1">
                <i class="bi bi-arrow-right"></i> From: <strong>{{ ucfirst($log->from_status) }}</strong>
              </p>
              @endif
              <p class="mb-1">{{ $log->comments ?? 'No comments' }}</p>
              <p class="text-muted small">Updated by: {{ $log->changedBy->name ?? 'System' }}</p>
            </div>
          </li>
          @endforeach
        </ul>
        @endif
      </div>
    </div>
    @endif
  </div>

</div>
@endsection