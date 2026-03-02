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
<link rel="stylesheet" href="{{asset('assets/vendor/css/pages/page-faq.css')}}" />

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
  // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
</script>
@endsection

@section('content')
@php

$userIsInvestigator = auth()->id() === $project->project_investigator_id;
$userIsTeamLead = $project->teamMembers()->where('user_id',
auth()->id())->whereIn('role',['lead','leadMember'])->exists();
@endphp
<div class="faq-header d-flex flex-column justify-content-center align-items-center rounded ">
  <h3 class="text-center text-white">{{ $project->title }} - CODE : {{ $project->project_code }}<span class="text-bold">

    </span></h3>
  <div class="client-info">
    {{-- @foreach($project_details->clients as $key => $value) --}}
    <span class="text-muted  ">
      <div class="badge bg-label-primary me-3 rounded p-2">
        <i class="ti ti-user ti-sm"></i>{{ $project->requirement->client->client_name }}
      </div>

    </span>
    {{-- @endforeach --}}
  </div>
  <p class="text-center text-white mb-0 px-3">
  <h6 class="mb-0 p-2 text-white">Start Date: <span class="text-white fw-normal">{{ $project->start_date->format('d M
      Y') }}</span>
    Deadline: <span class="text-white fw-normal">{{ $project->end_date->format('d M Y') }}
  </h6>

  </p>

  <a href="{{ route('pms.projects.dashboard', $project->id) }}" class="btn btn-sm btn-info">
    <i class="fas fa-tachometer-alt"></i> Dashboard
  </a>


</div>


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
            <p><strong>Principal Investigator:</strong> {{ $project->investigator->name }}</p>
            <p><strong>Estimated Expense:</strong> {{ number_format($project->estimated_expense, 2) }}</p>
          </div>
        </div>
        <div class="col-md-12">
          {{-- @if($project->expenseComponents->count() > 0)
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
                    @foreach($project->expenseComponents as $component)
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
                      <th>₹{{ number_format($project->estimated_expense, 2) }}</th>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>

          @endif --}}
          @if($project->proposal && $project->proposal->expenseComponents->count() > 0)
          <!-- Button trigger modal -->
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
            View Approved Expense
          </button>
          @endif

          <!-- Modal -->
          <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">Approved Estimated Expense</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                  </button>
                </div>
                <div class="modal-body">
                  @if($project->proposal && $project->proposal->expenseComponents->count() > 0)
                  <div class="card mt-4">
                    <div class="card-header">
                      <h5 class="card-title mb-0 badge bg-label-success">Project Approved Estimated Expense Breakdown
                      </h5>
                    </div>
                    <div class="card-body">
                      @php
                      $grouped = $project->proposal->expenseComponents->groupBy('group_name');
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
                                      <td class="text-end">{{ $component->rate ? number_format($component->rate, 2) :
                                        '-' }}
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
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>

                </div>
              </div>
            </div>
          </div>
          @if($project->expenseComponents->count() > 0)
          <div class="card mt-4">
            <div class="card-header">
              <h5 class="card-title mb-0">Current Estimated Expense Breakdown</h5>
            </div>
            <div class="card-body">
              @php
              $grouped = $project->expenseComponents->groupBy('group_name');
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



        @if($project->description)
        <div class="mt-3">
          <h6>Description</h6>
          <p>{{ $project->description }}</p>
        </div>
        @endif

        <div class="row">
          <div class="col-md-6"> <a href="{{ route('pms.projects.gantt', $project->id) }}"
              class="btn  btn-label-danger ms-2">
              <i class="fas fa-project-diagram"></i> View Gantt Chart
            </a></div>

        </div>


      </div>
    </div>

    <div class="card mt-4">
      <div class="card-header">
        <h5 class="card-title">Team Members</h5>
      </div>
      {{-- <div class="card-body">
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
      </div> --}}



      <div class="card-body">
        <div class="row">
          @foreach($project->teamMembers as $member)
          <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 shadow-sm">
              <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                  <div
                    class="avatar bg-{{ $member->role == 'lead' ? 'danger' : 'primary' }} text-white rounded-circle me-3 d-flex align-items-center justify-content-center"
                    style="width: 50px; height: 50px;">
                    {{ substr($member->user->name, 0, 1) }}
                  </div>
                  <div>
                    <h5 class="mb-0">{{ $member->user->name }}</h5>
                    <span
                      class="badge bg-{{ $member->role == 'lead' ? 'danger' : ($member->role == 'leadMember' ? 'warning' : 'primary') }}">
                      {{ ucfirst($member->role) }}
                    </span>
                  </div>
                </div>

                <div class="mb-3">
                  <h6 class="small mb-1">Time Investment</h6>
                  <div class="progress" style="height: 20px;">
                    <div class="progress-bar bg-success" role="progressbar"
                      style="width: {{ min($member->expected_time_investment_hours / 40 * 100, 100) }}%"
                      aria-valuenow="{{ $member->expected_time_investment_hours }}" aria-valuemin="0"
                      aria-valuemax="40">
                      {{ $member->expected_time_investment_hours }} hours
                    </div>
                  </div>
                </div>

                {{-- <div>
                  <h6 class="small mb-1">Cost Share</h6>
                  <div class="progress" style="height: 20px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $member->cost_share }}%"
                      aria-valuenow="{{ $member->cost_share }}" aria-valuemin="0" aria-valuemax="100">
                      {{ $member->cost_share }}%
                    </div>
                  </div>
                </div> --}}
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>

    {{-- <div class="card mt-4">
      <div class="card-header">
        <h5 class="card-title">Documents</h5>

        <div class="col-md-6">
          <a href="{{ route('pms.projects.documents.index', $project->id) }}" class="btn btn-sm btn-label-dark ms-2">
            <i class="fas fa-folder-open"></i> Manage Documents
          </a>
        </div>
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
                Uploaded by {{ $document->uploadedBy->name }}
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


      </div>
    </div> --}}
  </div>

  <div class="col-md-4">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title">Project Actions</h5>
      </div>
      <div class="card-body">
        @if($project->status == \App\Models\PMS\Project::STATUS_INITIATED && ($userIsInvestigator ||
        $userIsTeamLead))
        <form action="{{ route('pms.projects.start', $project->id) }}" method="POST" class="mb-3">
          @csrf
          <button type="submit" class="btn btn-success w-100">
            <i class="fas fa-play"></i> Start Project
          </button>
        </form>
        @endif

        @if($project->status == \App\Models\PMS\Project::STATUS_ONGOING && ($userIsInvestigator ||
        $userIsTeamLead))
        <form action="{{ route('pms.projects.complete', $project->id) }}" method="POST" class="mb-3">
          @csrf
          <button type="submit" class="btn btn-label-primary w-100">
            <i class="fas fa-check-circle"></i> Mark as Completed
          </button>
        </form>
        @endif

        @if($project->status == \App\Models\PMS\Project::STATUS_COMPLETED && ($userIsInvestigator ||
        $userIsTeamLead))
        <form action="{{ route('pms.projects.archive', $project->id) }}" method="POST" class="mb-3">
          @csrf
          <button type="submit" class="btn btn-label-primary w-100">
            <i class="fas fa-check-circle"></i> Mark as Archived
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
          class="btn  btn-label-primary w-100 mt-2">
          <i class="fas fa-eye"></i> View Requirement
        </a>
      </div>
    </div>

    <div class="card mt-4">
      <div class="card-header">
        <h5 class="card-title">Linked Proposal</h5>
      </div>
      <div class="card-body">
        <p><strong>Budget:</strong> {{ number_format($project->proposal->budget?? 0, 2) }}</p>
        <p><strong>Tenure:</strong> {{ $project->proposal->tenure ?? '' }}</p>
        <p><strong>Expected Start:</strong> @if($project->proposal && !is_null(
          $project->proposal->expected_start_date))
          {{
          $project->proposal->expected_start_date->format('d M Y') }} @endif</p>
        @if($project->proposal)
        <a href="{{ route('pms.proposals.show', $project->proposal->id) }}" class="btn  btn-label-primary w-100 mt-2">
          <i class="fas fa-eye"></i> View Proposal
        </a>@endif
      </div>
    </div>
  </div>
</div>
<div class="row mt-4">
  <div class="col-md-12">
    <div class="card mt-4">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">Milestones</h5>
          <a href="{{ route('pms.milestones.index', $project->id) }}" class="btn btn-sm btn-dark">
            <i class="fas fa-eye"></i> Milestone List
          </a>
          @if($project->status != \App\Models\PMS\Project::STATUS_COMPLETED && ($userIsInvestigator ||
          $userIsTeamLead))
          <a href="{{ route('pms.milestones.create', $project->id) }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i> Add Milestone
          </a>
          @endif
        </div>
      </div>
      <div class="card-body">
        @if($project->milestones->count() > 0)
        <div class="row g-4">
          @foreach($project->milestones as $milestone)
          <!-- Orders tabs-->
          <div class="col-md-6 col-lg-4 col-xl-4 mb-4">
            <div class="card h-100">
              <div class="card-header d-flex justify-content-between pb-2 mb-1">
                <div class="card-title mb-1">
                  <h5 class="m-0 me-2">{{ $milestone->name }}</h5>
                  <small class="text-muted">
                    <p class="text-primary"><strong>Start Date:</strong> {{ $milestone->start_date->format('d M Y') }}
                    </p>
                    <p class="text-primary"><strong>End Date:</strong> {{ $milestone->end_date->format('d M Y') }}</p>

                  </small>
                  <small>
                    <span class="badge bg-{{ $milestone->status_badge_color }} ms-2">
                      {{ $milestone->status_name }}
                    </span>
                  </small>
                </div>
                <div class="dropdown">
                  <button class="btn p-0" type="button" id="salesByCountryTabs" data-bs-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                    <i class="ti ti-dots-vertical ti-sm text-muted"></i>
                  </button>
                  <div class="dropdown-menu dropdown-menu-end" aria-labelledby="salesByCountryTabs">

                    @if($milestone->status != \App\Models\PMS\Milestone::STATUS_COMPLETED &&
                    $project->status != \App\Models\PMS\Project::STATUS_COMPLETED && ($userIsInvestigator ||
                    $userIsTeamLead))
                    <a href="{{ route('pms.tasks.create', [$project->id, $milestone->id]) }}" class="dropdown-item">
                      <i class="fas fa-plus"></i> Add Task
                    </a>
                    @endif

                  </div>
                </div>
              </div>
              <div class="card-body">
                <div class="nav-align-top">
                  <ul class="nav nav-tabs nav-fill" role="tablist">
                    <li class="nav-item">
                      <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                        data-bs-target="#navs-justified-link-about{{ $milestone->id }}"
                        aria-controls="navs-justified-link-about" aria-selected="false">About</button>
                    </li>
                    <li class="nav-item">
                      <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                        data-bs-target="#navs-justified-link-project{{ $milestone->id }}"
                        aria-controls="navs-justified-link-project" aria-selected="false">Task</button>
                    </li>

                  </ul>
                  <div class="tab-content pb-0">

                    <div class="tab-pane fade active show" id="navs-justified-link-about{{ $milestone->id }}"
                      role="tabpanel">


                      @if($milestone->description)
                      <p><strong>Description:</strong> {{ $milestone->description }}</p>
                      @endif
                      <div class="border-bottom border-bottom-dashed mt-0 mb-4"></div>
                      <span class="mb-2">
                        {{ $milestone->weightage }}% Weightage
                        @if($milestone->invoice_trigger)
                        <span class="badge bg-info ms-2 mb-2">Invoice Trigger</span>
                        @endif
                      </span>
                      <div class="border-bottom border-bottom-dashed mt-0 mb-4"></div>

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

                      <div class="mt-3 d-flex justify-content-end">
                        @if($milestone->status == \App\Models\PMS\Milestone::STATUS_NOT_STARTED &&
                        $project->status == \App\Models\PMS\Project::STATUS_ONGOING && ($userIsInvestigator ||
                        $userIsTeamLead))
                        <form action="{{ route('pms.milestones.start', [$project->id, $milestone->id]) }}" method="POST"
                          class="me-2">
                          @csrf
                          <button type="submit" class="btn btn-sm btn-success">
                            <i class="fas fa-play"></i> Start
                          </button>
                        </form>
                        @endif

                        @if($milestone->status == \App\Models\PMS\Milestone::STATUS_IN_PROGRESS &&
                        $milestone->isAllTasksCompleted() && ($userIsInvestigator || $userIsTeamLead))
                        <form action="{{ route('pms.milestones.complete', [$project->id, $milestone->id]) }}"
                          method="POST">
                          @csrf
                          <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-check"></i> Complete
                          </button>
                        </form>
                        @endif

                        @if($milestone->status == \App\Models\PMS\Milestone::STATUS_COMPLETED &&
                        $milestone->invoice_trigger &&
                        !$milestone->invoice && ($userIsInvestigator || $userIsTeamLead))
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

                    <div class="tab-pane fade" id="navs-justified-link-project{{ $milestone->id }}" role="tabpanel">
                      @if($milestone->tasks->count() > 0)
                      <div class="list-group">
                        @foreach($milestone->tasks as $task)
                        <div class="list-group-item">
                          <div class="d-flex justify-content-between align-items-center">
                            <div>
                              <strong>{{ $task->name }}</strong>

                            </div>
                            <a href="{{ route('pms.tasks.show', [$project->id, $milestone->id, $task->id]) }}"
                              class="btn btn-sm btn-outline-primary">
                              <i class="fas fa-eye"></i>
                            </a>
                          </div>
                          <small>Priority: <span class="mt-2 badge bg-{{ $task->priority_badge_color }} ms-2">
                              {{ $task->priority_name }}
                            </span></small>
                          <small><span class="mt-2  badge bg-{{ $task->status_badge_color }} ms-2">
                              {{ $task->status_name }}
                            </span></small>
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
                    </div>

                  </div>
                </div>
              </div>
            </div>
          </div>
          <!--/ Orders tabs -->
          @endforeach
        </div>
        @else
        <p>No milestones added yet</p>
        @endif
      </div>
    </div>
  </div>
</div>

<div class="row mt-4">
  <div class="col-md-8">
    <div class="card mt-4">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">Invoices</h5>
          @if($project->status != \App\Models\PMS\Project::STATUS_ARCHIVED && ($userIsInvestigator ||
          $userIsTeamLead))
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
                <th>Type</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($project->invoices as $invoice)
              <tr>
                <td>{{ $invoice->invoice_number ?? 'Draft' }}</td>
                <td>{{ $invoice->invoice_date->format('d M Y') }}</td>
                <td>{{ number_format($invoice->total_amount, 2) }}</td>
                <td>
                  <span class="badge bg-{{$invoice->invoice_type == 1 ? 'primary' : 'success'}}">
                    {{ $invoice->invoice_type == 1 ? 'Proforma Invoice' : 'Tax Invoice' }}
                  </span>
                </td>
                <td>
                  <span class="badge bg-{{ $invoice->status_badge_color }}">
                    {{ $invoice->status_name }}
                  </span>
                </td>
                <td>
                  <a href="{{ route('pms.invoices.show', [$project->id, $invoice->id]) }}" class="btn btn-sm btn-info">
                    <i class="fas fa-eye"></i>
                  </a>
                  @if($invoice->status == \App\Models\PMS\Invoice::STATUS_DRAFT)
                  <a href="{{ route('pms.invoices.edit', [$project->id, $invoice->id]) }}"
                    class="btn btn-sm btn-primary">
                    <i class="fas fa-edit"></i>
                  </a>
                  @endif
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
  </div>

  <div class="col-md-4">
    <div class="card mt-4">
      <div class="card-header">
        <h5 class="card-title">Documents</h5>

        <div class="col-md-6">
          <a href="{{ route('pms.projects.documents.index', $project->id) }}" class="btn btn-sm btn-label-dark ms-2">
            <i class="fas fa-folder-open"></i> Manage Documents
          </a>
        </div>
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
                Uploaded by {{ $document->uploadedBy->name }}
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

        {{-- @if($project->status != \App\Models\PMS\Project::STATUS_COMPLETED)
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
        @endif --}}
      </div>
    </div>
  </div>

</div>
@endsection