@extends('layouts.audit')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Documents Audit</h2>
    <div>
        <a href="{{ route('audit.documents.export', request()->all()) }}" class="btn btn-outline-success">Export CSV</a>
    </div>
</div>

<div class="audit-card">
    <form action="{{ route('audit.documents.index') }}" method="GET" class="mb-4">
        <div class="row g-3">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="Search Document No, Subject..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="year" class="form-select">
                    <option value="">All Years</option>
                    @for($i = date('Y'); $i >= 2020; $i--)
                        <option value="{{ $i }}" {{ request('year') == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
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
                    <th>Document No</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Subject</th>
                    <th>Code</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($documents as $doc)
                <tr>
                    <td>{{ $doc->document_number }}</td>
                     <td>{{ $doc->created_at->format('d-m-Y') }}</td>
                    <td>{{ $doc->documentType->name ?? '-' }}</td>
                    <td>{{ Str::limit($doc->subject, 50) }}</td>
                    <td><span class="badge bg-label-info">{{ $doc->code->code ?? '-' }}</span></td>
                    <td>
                        <span class="badge {{ $doc->status == 'active' ? 'bg-label-success' : 'bg-label-secondary' }}">
                            {{ ucfirst($doc->status) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('audit.documents.show', $doc->id) }}" class="btn btn-sm btn-info">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4">No documents found matching criteria.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $documents->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
