@extends('layouts.audit')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Tapals Audit</h2>
    <div>
        <a href="{{ route('audit.tapals.export', request()->all()) }}" class="btn btn-outline-success">Export CSV</a>
    </div>
</div>

<div class="audit-card">
    <form action="{{ route('audit.tapals.index') }}" method="GET" class="mb-4">
        <div class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search Number, Subject, From..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" placeholder="From Date">
            </div>
            <div class="col-md-3">
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" placeholder="To Date">
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
                    <th>Tapal No</th>
                    <th>Inward Date</th>
                    <th>Received Date</th>
                    <th>From</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tapals as $tapal)
                <tr>
                    <td>{{ $tapal->tapal_number ?? $tapal->id }}</td>
                     <td>{{ $tapal->inward_date ? \Carbon\Carbon::parse($tapal->inward_date)->format('d-m-Y') : '-' }}</td>
                    <td>{{ $tapal->received_date ? \Carbon\Carbon::parse($tapal->received_date)->format('d-m-Y') : '-' }}</td>
                    <td>
                        <div class="fw-bold">{{ $tapal->from_name }}</div>
                        <small class="text-muted">{{ $tapal->from_department }}</small>
                    </td>
                    <td>{{ Str::limit($tapal->subject, 50) }}</td>
                    <td>
                        @php
                            $statusClass = 'bg-label-primary';
                            if(str_contains(strtolower($tapal->status), 'completed')) $statusClass = 'bg-label-success';
                            elseif(str_contains(strtolower($tapal->status), 'pending')) $statusClass = 'bg-label-warning';
                        @endphp
                        <span class="badge {{ $statusClass }}">{{ $tapal->status ?? 'Active' }}</span>
                    </td>
                    <td>
                        <a href="{{ route('audit.tapals.show', $tapal->id) }}" class="btn btn-sm btn-info">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4">No tapals found matching criteria.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $tapals->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
