@extends('layouts/layoutMaster')

@section('title', 'Projects Overview')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}">
@endsection

@section('page-style')
<style>
  .status-badge {
    font-size: 0.85rem;
    padding: 0.4em 0.6em;
    border-radius: 5px;
  }
</style>
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
@endsection

@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    $('#projectsTable').DataTable({
      responsive: true,
      pageLength: 25,
      order: [[0, 'asc']],
    });
    $('.select2').select2({ width: 'resolve' });
  });
</script>
@endsection

@section('content')
<div class="container-fluid">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
      <h4 class="mb-2 mb-sm-0">Projects Overview</h4>
      {{-- <a href="{{ route('projects.create') }}" class="btn btn-primary btn-sm">Add New Project</a> --}}
      <a href="{{ route('pms.reports.projects.export', request()->all()) }}" class="btn btn-success btn-sm">
        Export to Excel
      </a>
    </div>


    <!-- 🔍 FILTER FORM -->
    <div class="card-body border-bottom">
      <form method="GET" action="{{ route('pms.reports.project-status-report') }}" class="row g-3 align-items-end">
        <div class="col-md-3">
          <label for="status" class="form-label">Project Status</label>
          <select name="status" id="status" class="form-select select2">
            <option value="">All</option>
            <option value="0" {{ request('status')=='0' ? 'selected' : '' }}>Initiated</option>
            <option value="1" {{ request('status')=='1' ? 'selected' : '' }}>Ongoing</option>
            <option value="2" {{ request('status')=='2' ? 'selected' : '' }}>Completed</option>
            <option value="3" {{ request('status')=='3' ? 'selected' : '' }}>Archived</option>
          </select>
        </div>
        @if(Auth::user()->hasRole('director') || Auth::user()->hasRole('Project Report') ||
        Auth::user()->hasRole('finance'))
        <div class="col-md-3">
          <label for="investigator_id" class="form-label">Project Investigator</label>
          <select name="investigator_id" id="investigator_id" class="form-select select2">
            <option value="">All</option>
            @foreach($investigators as $investigator)
            <option value="{{ $investigator->id }}" {{ request('investigator_id')==$investigator->id ? 'selected' : ''
              }}>
              {{ $investigator->name }}
            </option>
            @endforeach
          </select>
        </div>
        @endif

        <div class="col-md-3">
          <label for="client_id" class="form-label">Client</label>
          <select name="client_id" id="client_id" class="form-select select2">
            <option value="">All</option>
            @foreach($clients as $client)
            <option value="{{ $client->id }}" {{ request('client_id')==$client->id ? 'selected' : '' }}>
              {{ $client->client_name }} - CODE- {{ $client->client_code }}
            </option>
            @endforeach
          </select>
        </div>

        <div class="col-md-3 d-flex gap-2">
          <button type="submit" class="btn btn-primary">Filter</button>
          <a href="{{ route('pms.reports.project-status-report') }}" class="btn btn-secondary">Reset</a>
        </div>
      </form>
    </div>

    <!-- 🔢 PROJECTS TABLE -->
    <div class="card-body">
      <div class="table-responsive">
        <table id="projectsTable" class="table table-bordered table-striped align-middle">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Project Title</th>
              <th>Category</th>
              <th>Client</th>


              <th>Budget</th>
              <th>Estimated Expenses</th>
              <th>Actual Expenses</th>
              <th>Invoiced (Proforma & Tax)</th>
              <th>Invoiced (Proforma)</th>
              <th>Invoiced (Tax)</th>
              <th>Paid</th>
              <th>Outstanding</th>
              <th>Completion</th>
              <th>Investigator</th>
              <th>Start Date</th>
              <th>End Date</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($projects as $index => $project)
            <tr>
              <td>{{ $index + 1 }}</td>
              <td>{{ $project->title }} <br> <strong>{{ $project->project_code }}</strong> </td>
              <td> {{ $project->requirement->category->name }}</td>
              <td>{{ $project->requirement->client->client_name ?? 'N/A' }}</td>


              <td>{{ number_format($project->calculated['budget'], 2) }}</td>
              <td>{{ number_format($project->calculated['estimated_expense'], 2) }}</td>
              <td>{{ number_format($project->calculated['expenses'], 2) }}</td>
              <td>{{ number_format($project->calculated['total_invoiced'], 2) }}</td>
              <td>{{ number_format($project->calculated['total_invoiced_proforma'], 2) }}</td>
              <td>{{ number_format($project->calculated['total_invoiced_tax'], 2) }}</td>
              <td>{{ number_format($project->calculated['total_paid'], 2) }}</td>
              <td>{{ number_format($project->calculated['outstanding'], 2) }}</td>
              <td>
                <div class="progress" style="height:8px;">
                  <div
                    class="progress-bar bg-{{ $project->completion_percentage > 90 ? 'success' : ($project->completion_percentage > 50 ? 'warning' : 'danger') }}"
                    role="progressbar" style="width: {{ $project->completion_percentage }}%">
                  </div>
                </div>
                <small>{{ $project->completion_percentage }}%</small>
              </td>
              <td>{{ $project->investigator->name ?? 'N/A' }}</td>
              <td>{{ $project->start_date?->format('M d, Y') }}</td>
              <td>{{ $project->end_date?->format('M d, Y') }}</td>
              <td>
                <span class="badge bg-{{ $project->status_badge_color }}">{{ $project->status_name }}</span>
              </td>
              <td>
                {{-- <a href="{{ route('projects.dashboard', $project->id) }}" class="btn btn-sm btn-primary">View</a>
                <a href="{{ route('projects.edit', $project->id) }}" class="btn btn-sm btn-warning">Edit</a> --}}
                <a href="{{ route('pms.projects.dashboard', $project->id) }}" class="btn btn-sm btn-info">
                  <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="{{ route('pms.invoices.index', $project->id) }}" class="btn btn-sm btn-primary">
                  <i class="fas fa-plus"></i> Add Invoice
                </a>

              </td>
            </tr>
            @empty
            <tr>
              <td colspan="14" class="text-center text-muted">No projects found.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection