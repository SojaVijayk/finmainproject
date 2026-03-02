@extends('layouts/layoutMaster')

@section('title', 'Projects')

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
<div class="card">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Project Code</th>
            <th>Title</th>
            <th>Client</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Status</th>
            <th>Completion</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($projects as $project)
          <tr>
            <td>{{ $project->project_code }}</td>
            <td>{{ $project->title }}</td>
            <td>{{ $project->requirement->client->client_name }}</td>
            <td>{{ $project->start_date->format('d M Y') }}</td>
            <td>{{ $project->end_date->format('d M Y') }}</td>
            <td>
              <span class="badge bg-{{ $project->status_badge_color }}">
                {{ $project->status_name }}
              </span>
            </td>
            <td>
              <div class="progress" style="height: 20px;">
                <div class="progress-bar" role="progressbar" style="width: {{ $project->completion_percentage }}%"
                  aria-valuenow="{{ $project->completion_percentage }}" aria-valuemin="0" aria-valuemax="100">
                  {{ $project->completion_percentage }}%
                </div>
              </div>
            </td>
            <td>
              <a href="{{ route('pms.projects.show', $project->id) }}" class="btn btn-sm btn-info">
                <i class="fas fa-eye"></i>
              </a>
              @if($project->status == \App\Models\PMS\Project::STATUS_INITIATED)
              <a href="{{ route('pms.projects.edit', $project->id) }}" class="btn btn-sm btn-primary">
                <i class="fas fa-edit"></i>
              </a>
              @endif
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="8" class="text-center">No projects found</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{ $projects->links() }}
  </div>
</div>
@endsection