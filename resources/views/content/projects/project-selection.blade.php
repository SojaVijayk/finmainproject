@extends('layouts/layoutMaster')

@section('title', 'Project Selection')

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">Employee Management /</span> Project Selection
</h4>

<div class="row">
  @forelse($projects as $project)
  <div class="col-md-6 col-lg-4 mb-4">
    <div class="card h-100">
      <div class="card-body text-center">
        <div class="dropdown btn-pinned">
          <button type="button" class="btn dropdown-toggle hide-arrow p-0" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical text-muted"></i></button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="javascript:void(0);">Edit Project</a></li>
          </ul>
        </div>
        <div class="mx-auto mb-3">
          <img src="{{asset('assets/img/icons/unicons/briefcase.png')}}" alt="Project Icon" class="rounded-circle" width="80">
        </div>
        <h5 class="mb-1">{{ $project->name }}</h5>
        <p class="text-muted">Manage employees for this project</p>
        <div class="d-flex align-items-center justify-content-center my-4 py-1">
          <a href="javascript:;" class="me-2"><span class="badge bg-label-primary">Project ID: {{ $project->id }}</span></a>
        </div>
        <div class="d-flex align-items-center justify-content-center">
          <a href="{{ route('pms.employees.project-index', $project->id) }}" class="btn btn-primary d-flex align-items-center me-2"><i class="ti-xs me-1 ti ti-users"></i>Manage</a>
          <a href="{{ route('pms.employees.project-index', $project->id) }}?add=1" class="btn btn-label-success d-flex align-items-center"><i class="ti-xs me-1 ti ti-plus"></i>Add Employee</a>
        </div>
      </div>
    </div>
  </div>
  @empty
  <div class="col-12 text-center">
    <p>No projects found in projectdemo table.</p>
  </div>
  @endforelse
</div>
@endsection
