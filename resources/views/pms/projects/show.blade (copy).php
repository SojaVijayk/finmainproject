@extends('layouts/layoutMaster')

@section('title', 'View Project')

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

@section('content')
<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">Project Details: {{ $project->project_code }}</h5>
          <div class="badge bg-{{ $project->status_badge_color }}">
            {{ $project->status_name }}
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <p><strong>Title:</strong> {{ $project->title }}</p>
            <p><strong>Start Date:</strong> {{ $project->start_date->format('d M Y') }}</p>
            <p><strong>End Date:</strong> {{ $project->end_date->format('d M Y') }}</p>
            <p><strong>Budget:</strong> {{ number_format($project->budget, 2) }}</p>
          </div>
          <div class="col-md-6">
            <p><strong>Investigator:</strong> {{ $project->investigator->name }}</p>
            <p><strong>Estimated Expense:</strong> {{ number_format($project->estimated_expense, 2) }}</p>
            <p><strong>Expected Revenue:</strong> {{ number_format($project->revenue, 2) }}</p>
            <p><strong>Completion:</strong>
            <div class="progress">
              <div class="progress-bar" role="progressbar" style="width: {{ $project->completion_percentage }}%"
                aria-valuenow="{{ $project->completion_percentage }}" aria-valuemin="0" aria-valuemax="100">
                {{ $project->completion_percentage }}%
              </div>
            </div>
            </p>
          </div>

          <a href="{{ route('pms.projects.gantt', $project->id) }}" class="btn btn-sm btn-info ms-2">
            <i class="fas fa-project-diagram"></i> View Gantt Chart
          </a>

          <a href="{{ route('pms.projects.documents.index', $project->id) }}" class="btn btn-sm btn-info ms-2">
            <i class="fas fa-folder-open"></i> Documents
          </a>

        </div>

        @if($project->description)
        <div class="mt-3">
          <h6>Description</h6>
          <p>{{ $project->description }}</p>
        </div>
        @endif
      </div>
    </div>

    <div class="card mt-4">
      <div class="card-header">
        <h5 class="card-title">Team Members</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Name</th>
                <th>Role</th>
                <th>Time Investment</th>
                <th>Cost Share</th>
              </tr>
            </thead>
            <tbody>
              @foreach($project->teamMembers as $member)
              <tr>
                <td>{{ $member->user->name }}</td>
                <td>
                  <span class="badge bg-{{ $member->role == 'lead' ? 'primary' : 'secondary' }}">
                    {{ ucfirst($member->role) }}
                  </span>
                </td>
                <td>{{ $member->expected_time_investment_hours }} hours</td>
                <td>{{ $member->cost_share }}%</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="card mt-4">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">Milestones</h5>
          @if($project->status != \App\Models\PMS\Project::STATUS_COMPLETED)
          <a href="{{ route('pms.milestones.create', $project->id) }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i> Add Milestone
          </a>
          @endif
        </div>
      </div>
      <div class="card-body">
        @if($project->milestones->count() > 0)
        <div class="accordion" id="milestonesAccordion">
          @foreach($project->milestones as $milestone)
          <div class="accordion-item">
            <h2 class="accordion-header" id="heading{{ $milestone->id }}">
              <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button"
                data-bs-toggle="collapse" data-bs-target="#collapse{{ $milestone->id }}"
                aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="collapse{{ $milestone->id }}">
                <div class="d-flex justify-content-between w-100 me-3">
                  <span>
                    {{ $milestone->name }}
                    <span class="badge bg-{{ $milestone->status_badge_color }} ms-2">
                      {{ $milestone->status_name }}
                    </span>
                  </span>
                  <span>
                    {{ $milestone->weightage }}% Weightage
                    @if($milestone->invoice_trigger)
                    <span class="badge bg-info ms-2">Invoice Trigger</span>
                    @endif
                  </span>
                </div>
              </button>
            </h2>
            <div id="collapse{{ $milestone->id }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
              aria-labelledby="heading{{ $milestone->id }}" data-bs-parent="#milestonesAccordion">
              <div class="accordion-body">
                <div class="mb-3">
                  <p><strong>Start Date:</strong> {{ $milestone->start_date->format('d M Y') }}</p>
                  <p><strong>End Date:</strong> {{ $milestone->end_date->format('d M Y') }}</p>
                  <p><strong>Completion:</strong>
                  <div class="progress">
                    <div class="progress-bar" role="progressbar"
                      style="width: {{ $milestone->task_completion_percentage }}%"
                      aria-valuenow="{{ $milestone->task_completion_percentage }}" aria-valuemin="0"
                      aria-valuemax="100">
                      {{ $milestone->task_completion_percentage }}%
                    </div>
                  </div>
                  </p>
                  @if($milestone->description)
                  <p><strong>Description:</strong> {{ $milestone->description }}</p>
                  @endif
                </div>

                <div class="d-flex justify-content-between mb-3">
                  <h6>Tasks</h6>
                  @if($milestone->status != \App\Models\PMS\Milestone::STATUS_COMPLETED &&
                  $project->status != \App\Models\PMS\Project::STATUS_COMPLETED)
                  <a href="{{ route('pms.tasks.create', [$project->id, $milestone->id]) }}"
                    class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> Add Task
                  </a>
                  @endif
                </div>

                @if($milestone->tasks->count() > 0)
                <div class="list-group">
                  @foreach($milestone->tasks as $task)
                  <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center">
                      <div>
                        <strong>{{ $task->name }}</strong>
                        <span class="badge bg-{{ $task->priority_badge_color }} ms-2">
                          {{ $task->priority_name }}
                        </span>
                        <span class="badge bg-{{ $task->status_badge_color }} ms-2">
                          {{ $task->status_name }}
                        </span>
                      </div>
                      <a href="{{ route('pms.tasks.show', [$project->id, $milestone->id, $task->id]) }}"
                        class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-eye"></i>
                      </a>
                    </div>
                    <div class="mt-2">
                      <small class="text-muted">
                        <i class="fas fa-calendar-alt"></i>
                        {{ $task->start_date->format('d M Y') }} -
                        {{ $task->end_date->format('d M Y') }}
                      </small>
                    </div>
                    @if($task->assignments->count() > 0)
                    <div class="mt-2">
                      <small>
                        <strong>Assigned To:</strong>
                        {{ $task->assignments->map(function($a) { return $a->user->name; })->implode(', ') }}
                      </small>
                    </div>
                    @endif
                  </div>
                  @endforeach
                </div>
                @else
                <p>No tasks added yet</p>
                @endif

                <div class="mt-3 d-flex justify-content-end">
                  @if($milestone->status == \App\Models\PMS\Milestone::STATUS_NOT_STARTED &&
                  $project->status == \App\Models\PMS\Project::STATUS_ONGOING)
                  <form action="{{ route('pms.milestones.start', [$project->id, $milestone->id]) }}" method="POST"
                    class="me-2">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success">
                      <i class="fas fa-play"></i> Start
                    </button>
                  </form>
                  @endif

                  @if($milestone->status == \App\Models\PMS\Milestone::STATUS_IN_PROGRESS)
                  <form action="{{ route('pms.milestones.complete', [$project->id, $milestone->id]) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-primary">
                      <i class="fas fa-check"></i> Complete
                    </button>
                  </form>
                  @endif

                  @if($milestone->status == \App\Models\PMS\Milestone::STATUS_COMPLETED &&
                  $milestone->invoice_trigger &&
                  !$milestone->invoice)
                  <form action="{{ route('pms.milestones.request-invoice', [$project->id, $milestone->id]) }}"
                    method="POST" class="ms-2">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-info">
                      <i class="fas fa-file-invoice"></i> Request Invoice
                    </button>
                  </form>
                  @endif
                </div>
              </div>
            </div>
          </div>
          @endforeach
        </div>
        @else
        <p>No milestones added yet</p>
        @endif
      </div>
    </div>

    <div class="card mt-4">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">Invoices</h5>
          @if($project->status != \App\Models\PMS\Project::STATUS_COMPLETED)
          <a href="{{ route('pms.invoices.create', $project->id) }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i> Add Invoice
          </a>
          @endif
        </div>
      </div>
      <div class="card-body">
        @if($project->invoices->count() > 0)
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Invoice No</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($project->invoices as $invoice)
              <tr>
                <td>{{ $invoice->invoice_number ?? 'Draft' }}</td>
                <td>{{ $invoice->invoice_date->format('d M Y') }}</td>
                <td>{{ number_format($invoice->amount, 2) }}</td>
                <td>
                  <span class="badge bg-{{ $invoice->status_badge_color }}">
                    {{ $invoice->status_name }}
                  </span>
                </td>
                <td>
                  <a href="{{ route('pms.invoices.show', [$project->id, $invoice->id]) }}" class="btn btn-sm btn-info">
                    <i class="fas fa-eye"></i>
                  </a>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @else
        <p>No invoices added yet</p>
        @endif
      </div>
    </div>

    <div class="card mt-4">
      <div class="card-header">
        <h5 class="card-title">Documents</h5>
      </div>
      <div class="card-body">
        @if($project->documents->count() > 0)
        <div class="list-group">
          @foreach($project->documents as $document)
          <div class="list-group-item d-flex justify-content-between align-items-center">
            <div>
              <i class="fas fa-file me-2"></i>
              {{ $document->name }}
              <small class="text-muted ms-2">{{ $document->type }}</small>
            </div>
            <div>
              <small class="text-muted me-2">
                Uploaded by {{ $document->uploaded_by->name }}
              </small>
              <a href="{{ Storage::url($document->path) }}" target="_blank" class="btn btn-sm btn-primary">
                <i class="fas fa-download"></i> Download
              </a>
            </div>
          </div>
          @endforeach
        </div>
        @else
        <p>No documents attached</p>
        @endif

        @if($project->status != \App\Models\PMS\Project::STATUS_COMPLETED)
        <form action="{{ route('pms.projects.documents.create', $project->id) }}" method="POST" class="mt-3"
          enctype="multipart/form-data">
          @csrf
          <div class="input-group">
            <input type="file" name="document" class="form-control" required>
            <select name="type" class="form-select" style="max-width: 150px;" required>
              <option value="">Select Type</option>
              <option value="Proposal">Proposal</option>
              <option value="Work Order">Work Order</option>
              <option value="Report">Report</option>
              <option value="Other">Other</option>
            </select>
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-upload"></i> Upload
            </button>
          </div>
        </form>
        @endif
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title">Project Actions</h5>
      </div>
      <div class="card-body">
        @if($project->status == \App\Models\PMS\Project::STATUS_INITIATED)
        <form action="{{ route('pms.projects.start', $project->id) }}" method="POST" class="mb-3">
          @csrf
          <button type="submit" class="btn btn-success w-100">
            <i class="fas fa-play"></i> Start Project
          </button>
        </form>
        @endif

        @if($project->status == \App\Models\PMS\Project::STATUS_ONGOING)
        <form action="{{ route('pms.projects.complete', $project->id) }}" method="POST" class="mb-3">
          @csrf
          <button type="submit" class="btn btn-primary w-100">
            <i class="fas fa-check-circle"></i> Mark as Completed
          </button>
        </form>
        @endif

        <div class="list-group mt-3">
          <div class="list-group-item">
            <strong>Created At:</strong> {{ $project->created_at->format('d M Y H:i') }}
          </div>
          <div class="list-group-item">
            <strong>Last Updated:</strong> {{ $project->updated_at->format('d M Y H:i') }}
          </div>
        </div>
      </div>
    </div>

    <div class="card mt-4">
      <div class="card-header">
        <h5 class="card-title">Linked Requirement</h5>
      </div>
      <div class="card-body">
        <p><strong>Temp No:</strong> {{ $project->requirement->temp_no }}</p>
        <p><strong>Client:</strong> {{ $project->requirement->client->client_name }}</p>
        <p><strong>Category:</strong> {{ $project->requirement->category->name }}</p>
        <a href="{{ route('pms.requirements.show', $project->requirement->id) }}"
          class="btn btn-sm btn-info w-100 mt-2">
          <i class="fas fa-eye"></i> View Requirement
        </a>
      </div>
    </div>

    <div class="card mt-4">
      <div class="card-header">
        <h5 class="card-title">Linked Proposal</h5>
      </div>
      <div class="card-body">
        <p><strong>Budget:</strong> {{ number_format($project->proposal->budget, 2) }}</p>
        <p><strong>Tenure:</strong> {{ $project->proposal->tenure }}</p>
        <p><strong>Expected Start:</strong> {{ $project->proposal->expected_start_date->format('d M Y') }}</p>
        <a href="{{ route('pms.proposals.show', $project->proposal->id) }}" class="btn btn-sm btn-info w-100 mt-2">
          <i class="fas fa-eye"></i> View Proposal
        </a>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
</script>
@endsection