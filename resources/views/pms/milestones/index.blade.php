@extends('layouts/layoutMaster')

@section('title', 'Milestones')

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
@section('header', 'Milestones: ' . $project->title)
@php
$userIsInvestigator = auth()->id() === $project->project_investigator_id;
$userIsTeamLead = $project->teamMembers()->where('user_id',
auth()->id())->where('role','lead')->exists();
@endphp
@section('content')
<div class="card">
  <div class="card-header">
    <div class="d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Milestones</h5>
      <div>
        <a href="{{ route('pms.projects.show', $project->id) }}" class="btn btn-sm btn-secondary me-2">
          <i class="fas fa-arrow-left"></i> Back to Project
        </a>
        @if($project->status != \App\Models\PMS\Project::STATUS_COMPLETED && ($userIsInvestigator ||
        $userIsTeamLead))
        <a href="{{ route('pms.milestones.create', $project->id) }}" class="btn btn-sm btn-primary">
          <i class="fas fa-plus"></i> Add Milestone
        </a>
        @endif
      </div>
    </div>
  </div>
  <div class="card-body">
    @if($milestones->count() > 0)
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Name</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Weightage</th>
            <th>Status</th>
            <th>Completion</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($milestones as $milestone)
          <tr>
            <td>{{ $milestone->name }}</td>
            <td>{{ $milestone->start_date->format('d M Y') }}</td>
            <td>{{ $milestone->end_date->format('d M Y') }}</td>
            <td>{{ $milestone->weightage }}%</td>
            <td>
              <span class="badge bg-{{ $milestone->status_badge_color }}">
                {{ $milestone->status_name }}
              </span>
            </td>
            <td>
              <div class="progress" style="height: 20px;">
                <div class="progress-bar" role="progressbar"
                  style="width: {{ $milestone->task_completion_percentage }}%"
                  aria-valuenow="{{ $milestone->task_completion_percentage }}" aria-valuemin="0" aria-valuemax="100">
                  {{ $milestone->task_completion_percentage }}%
                </div>
              </div>
            </td>
            <td>
              <a href="{{ route('pms.milestones.show', [$project->id, $milestone->id]) }}" class="btn btn-sm btn-info">
                <i class="fas fa-eye"></i>
              </a>
              @if($milestone->status != \App\Models\PMS\Milestone::STATUS_COMPLETED &&
              $project->status != \App\Models\PMS\Project::STATUS_COMPLETED && ($userIsInvestigator ||
              $userIsTeamLead))
              <a href="{{ route('pms.milestones.edit', [$project->id, $milestone->id]) }}"
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
    <div class="alert alert-info">No milestones found for this project.</div>
    @endif
  </div>
</div>
@endsection