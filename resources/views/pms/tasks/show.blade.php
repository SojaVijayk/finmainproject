@extends('layouts/layoutMaster')

@section('title', 'View Task')

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
@section('header', 'View Task: ' . $task->name)
@php
$userIsInvestigator = auth()->id() === $project->project_investigator_id;
$userIsTeamLead = $project->teamMembers()->where('user_id',
auth()->id())->where('role','lead')->exists();
$userIsAssigned = $task->assignments->pluck('user_id')->contains(auth()->id());
@endphp
@section('content')
<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">Task Details</h5>
          <span class="badge bg-{{ $task->status_badge_color }}">
            {{ $task->status_name }}
          </span>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <p><strong>Start Date:</strong> {{ $task->start_date->format('d M Y') }}</p>
            <p><strong>End Date:</strong> {{ $task->end_date->format('d M Y') }}</p>
            <p><strong>Duration:</strong> {{ $task->start_date->diffInDays($task->end_date) + 1 }} days</p>
          </div>
          <div class="col-md-6">
            <p><strong>Priority:</strong>
              <span class="badge bg-{{ $task->priority_badge_color }}">
                {{ $task->priority_name }}
              </span>
            </p>
            <p><strong>Milestone:</strong> {{ $milestone->name }}</p>
            <p><strong>Project:</strong> {{ $project->title }}</p>
          </div>
        </div>

        @if($task->description)
        <div class="mt-3">
          <h6>Description</h6>
          <p>{{ $task->description }}</p>
        </div>
        @endif

        <div class="mt-3">
          <h6>Assigned To</h6>
          @if($task->assignments->count() > 0)
          <div class="list-group">
            @foreach($task->assignments as $assignment)
            <div class="list-group-item">
              {{ $assignment->user->name }}
            </div>
            @endforeach
          </div>
          @else
          <p>No one assigned to this task</p>
          @endif
        </div>
      </div>
      <div class="card-footer">
        @if(($userIsInvestigator || $userIsAssigned) && $task->status == \App\Models\PMS\Task::STATUS_NOT_STARTED )
        <form action="{{ route('pms.tasks.start', [$project->id, $milestone->id, $task->id]) }}" method="POST"
          class="d-inline">
          @csrf
          <button type="submit" class="btn btn-success">
            <i class="fas fa-play"></i> Start Task
          </button>
        </form>
        @endif

        @if(($userIsInvestigator || $userIsAssigned) && $task->status == \App\Models\PMS\Task::STATUS_IN_PROGRESS)
        <form action="{{ route('pms.tasks.complete', [$project->id, $milestone->id, $task->id]) }}" method="POST"
          class="d-inline">
          @csrf
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-check"></i> Mark as Completed
          </button>
        </form>
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
        @if($task->status != \App\Models\PMS\Task::STATUS_COMPLETED && ($userIsInvestigator ||
        $userIsTeamLead))
        <a href="{{ route('pms.tasks.edit', [$project->id, $milestone->id, $task->id]) }}"
          class="btn btn-primary w-100 mb-3">
          <i class="fas fa-edit"></i> Edit Task
        </a>
        @endif

        <a href="{{ route('pms.milestones.show', [$project->id, $milestone->id]) }}" class="btn btn-secondary w-100">
          <i class="fas fa-arrow-left"></i> Back to Milestone
        </a>
      </div>
    </div>

    <div class="card mt-4">
      <div class="card-header">
        <h5 class="card-title">Milestone Progress</h5>
      </div>
      <div class="card-body">
        <div class="progress" style="height: 20px;">
          <div class="progress-bar" role="progressbar" style="width: {{ $milestone->task_completion_percentage }}%"
            aria-valuenow="{{ $milestone->task_completion_percentage }}" aria-valuemin="0" aria-valuemax="100">
            {{ $milestone->task_completion_percentage }}%
          </div>
        </div>
        <p class="mt-2 mb-0">
          {{ $milestone->tasks->where('status', \App\Models\PMS\Task::STATUS_COMPLETED)->count() }}
          of {{ $milestone->tasks->count() }} tasks completed
        </p>
      </div>
    </div>
  </div>
</div>
@endsection