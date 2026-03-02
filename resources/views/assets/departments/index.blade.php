@extends('layouts/layoutMaster')

@section('title', 'Asset Departments')

@section('vendor-style')
@endsection

@section('vendor-script')
@endsection

@section('page-script')
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Asset Departments</h5>
        <a href="{{ route('asset.departments.create') }}" class="btn btn-primary">Add Department</a>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Custodian</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($departments as $department)
                <tr>
                    <td>{{ $department->name }}</td>
                    <td>{{ $department->custodian->name ?? '-' }}</td>
                    <td>
                        <span class="badge {{ $department->status ? 'bg-label-success' : 'bg-label-secondary' }}">
                            {{ $department->status ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{ route('asset.departments.edit', $department->id) }}"><i class="ti ti-pencil me-1"></i> Edit</a>
                                <form action="{{ route('asset.departments.destroy', $department->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item"><i class="ti ti-trash me-1"></i> Delete</button>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
