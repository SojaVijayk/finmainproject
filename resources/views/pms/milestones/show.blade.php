@extends('layouts/layoutMaster')

@section('title', 'Milestones Details')

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
@section('header', 'Milestone Details: ' . $milestone->name)
@php
$userIsInvestigator = auth()->id() === $project->project_investigator_id;
$userIsTeamLead = $project->teamMembers()->where('user_id',
auth()->id())->where('role','lead')->exists();
@endphp
@section('content')
<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">Milestone Details</h5>
          <span class="badge bg-{{ $milestone->status_badge_color }}">
            {{ $milestone->status_name }}
          </span>
          <div><a href="{{ route('pms.projects.show', $project->id) }}" class="btn btn-sm btn-secondary me-2">
              <i class="fas fa-arrow-left"></i> Back to Project
            </a>
            <a href="{{ route('pms.milestones.index', $project->id) }}" class="btn btn-sm btn-secondary me-2">
              <i class="fas fa-arrow-left"></i> Back to Milestones
            </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <p><strong>Start Date:</strong> {{ $milestone->start_date->format('d M Y') }}</p>
            <p><strong>End Date:</strong> {{ $milestone->end_date->format('d M Y') }}</p>
            <p><strong>Duration:</strong> {{ $milestone->start_date->diffInDays($milestone->end_date) + 1 }} days</p>
          </div>
          <div class="col-md-6">
            <p><strong>Weightage:</strong> {{ $milestone->weightage }}%</p>
            <p><strong>Invoice Trigger:</strong>
              {!! $milestone->invoice_trigger ? '<span class="badge bg-success">Yes</span>' : '<span
                class="badge bg-secondary">No</span>' !!}
            </p>
            <p><strong>Completion:</strong>
            <div class="progress" style="height: 20px;">
              <div class="progress-bar" role="progressbar" style="width: {{ $milestone->task_completion_percentage }}%"
                aria-valuenow="{{ $milestone->task_completion_percentage }}" aria-valuemin="0" aria-valuemax="100">
                {{ $milestone->task_completion_percentage }}%
              </div>
            </div>
            </p>
          </div>
        </div>

        @if($milestone->description)
        <div class="mt-3">
          <h6>Description</h6>
          <p>{{ $milestone->description }}</p>
        </div>
        @endif
      </div>
    </div>

    <div class="card mt-4">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">Tasks</h5>
          @if($milestone->status != \App\Models\PMS\Milestone::STATUS_COMPLETED &&
          $project->status != \App\Models\PMS\Project::STATUS_COMPLETED && ($userIsInvestigator ||
          $userIsTeamLead))
          <a href="{{ route('pms.tasks.create', [$project->id, $milestone->id]) }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i> Add Task
          </a>
          @endif
        </div>
      </div>
      <div class="card-body">
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
        <div class="alert alert-info">No tasks added yet for this milestone.</div>
        @endif
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title">Milestone Actions</h5>
      </div>
      <div class="card-body">
        @if($milestone->status == \App\Models\PMS\Milestone::STATUS_NOT_STARTED &&
        $project->status == \App\Models\PMS\Project::STATUS_ONGOING && ($userIsInvestigator ||
        $userIsTeamLead))
        <form action="{{ route('pms.milestones.start', [$project->id, $milestone->id]) }}" method="POST" class="mb-3">
          @csrf
          <button type="submit" class="btn btn-success w-100">
            <i class="fas fa-play"></i> Start Milestone
          </button>
        </form>
        @endif

        @if($milestone->status == \App\Models\PMS\Milestone::STATUS_IN_PROGRESS && $milestone->isAllTasksCompleted() &&
        ($userIsInvestigator ||
        $userIsTeamLead))
        <form action="{{ route('pms.milestones.complete', [$project->id, $milestone->id]) }}" method="POST"
          class="mb-3">
          @csrf
          <button type="submit" class="btn btn-primary w-100">
            <i class="fas fa-check"></i> Complete Milestone
          </button>
        </form>
        @endif

        @if($milestone->status == \App\Models\PMS\Milestone::STATUS_COMPLETED &&
        $milestone->invoice_trigger &&
        !$milestone->invoice)
        <form action="{{ route('pms.milestones.request-invoice', [$project->id, $milestone->id]) }}" method="POST"
          class="mb-3">
          @csrf
          <button type="submit" class="btn btn-info w-100">
            <i class="fas fa-file-invoice"></i> Request Invoice
          </button>
        </form>
        @endif

        @if($milestone->status != \App\Models\PMS\Milestone::STATUS_COMPLETED &&
        $project->status != \App\Models\PMS\Project::STATUS_COMPLETED &&
        ($userIsInvestigator ||
        $userIsTeamLead))
        <a href="{{ route('pms.milestones.edit', [$project->id, $milestone->id]) }}" class="btn btn-warning w-100 mb-3">
          <i class="fas fa-edit"></i> Edit Milestone
        </a>
        @endif

        <div class="list-group mt-3">
          <div class="list-group-item">
            <strong>Project:</strong> {{ $project->title }}
          </div>
          <div class="list-group-item">
            <strong>Project Code:</strong> {{ $project->project_code }}
          </div>
          <div class="list-group-item">
            <strong>Project Status:</strong>
            <span class="badge bg-{{ $project->status_badge_color }}">
              {{ $project->status_name }}
            </span>
          </div>
        </div>


      </div>



    </div>
  </div>
</div>
@endsection