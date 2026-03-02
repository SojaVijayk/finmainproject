@extends('layouts/layoutMaster')

@section('title', 'Task')

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
@section('header', 'Tasks: ' . $milestone->name)
@php
$userIsInvestigator = auth()->id() === $project->project_investigator_id;
$userIsTeamLead = $project->teamMembers()->where('user_id',
auth()->id())->where('role','lead')->exists();
@endphp
@section('content')
<div class="card">
  <div class="card-header">
    <div class="d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Tasks</h5>
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
    @if($tasks->count() > 0)
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Name</th>
            <th>Priority</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Status</th>
            <th>Assigned To</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($tasks as $task)
          <tr>
            <td>{{ $task->name }}</td>
            <td>
              <span class="badge bg-{{ $task->priority_badge_color }}">
                {{ $task->priority_name }}
              </span>
            </td>
            <td>{{ $task->start_date->format('d M Y') }}</td>
            <td>{{ $task->end_date->format('d M Y') }}</td>
            <td>
              <span class="badge bg-{{ $task->status_badge_color }}">
                {{ $task->status_name }}
              </span>
            </td>
            <td>
              @if($task->assignments->count() > 0)
              {{ $task->assignments->map(function($a) { return $a->user->name; })->implode(', ') }}
              @else
              Not assigned
              @endif
            </td>
            <td>
              <a href="{{ route('pms.tasks.show', [$project->id, $milestone->id, $task->id]) }}"
                class="btn btn-sm btn-info">
                <i class="fas fa-eye"></i>
              </a>
              @if($task->status != \App\Models\PMS\Task::STATUS_COMPLETED &&
              $milestone->status != \App\Models\PMS\Milestone::STATUS_COMPLETED &&
              $project->status != \App\Models\PMS\Project::STATUS_COMPLETED)
              <a href="{{ route('pms.tasks.edit', [$project->id, $milestone->id, $task->id]) }}"
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
    <div class="alert alert-info">No tasks found for this milestone.</div>
    @endif
  </div>
</div>
@endsection