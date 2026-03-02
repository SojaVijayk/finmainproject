@extends('layouts.audit')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Projects Audit</h2>
    <div>
        <a href="{{ route('audit.projects.export', request()->all()) }}" class="btn btn-outline-success">Export CSV</a>
    </div>
</div>

<div class="audit-card">
    <form action="{{ route('audit.projects.index') }}" method="GET" class="mb-4">
        <div class="row g-3">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="Search Title, Project Code..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Initiated</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Ongoing</option>
                    <option value="2" {{ request('status') === '2' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            <div class="col-md-2">
                 <select name="investigator_id" class="form-select">
                    <option value="">All Investigators</option>
                    @foreach($investigators as $inv)
                        <option value="{{ $inv->id }}" {{ request('investigator_id') == $inv->id ? 'selected' : '' }}>{{ $inv->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Project Code</th>
                    <th>Title</th>
                    <th>Investigator</th>
                    <th>Total Budget</th>
                    <th>Start Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $project)
                <tr>
                    <td><span class="fw-bold text-primary">{{ $project->project_code }}</span></td>
                    <td>{{ Str::limit($project->title, 40) }}</td>
                    <td>{{ $project->investigator->name ?? 'N/A' }}</td>
                    <td>₹{{ number_format($project->budget, 2) }}</td>
                    <td>{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d-m-Y') : '-' }}</td>
                    <td>
                        @if($project->status == 0) <span class="badge bg-label-info">Initiated</span>
                        @elseif($project->status == 1) <span class="badge bg-label-primary">Ongoing</span>
                        @elseif($project->status == 2) <span class="badge bg-label-success">Completed</span>
                        @else <span class="badge bg-label-secondary">Unknown</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('audit.projects.show', $project->id) }}" class="btn btn-sm btn-info">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4">No projects found matching criteria.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $projects->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
