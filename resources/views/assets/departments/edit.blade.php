@extends('layouts/layoutMaster')

@section('title', 'Edit Department')

@section('vendor-style')
@endsection

@section('vendor-script')
@endsection

@section('page-script')
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Edit Asset Department</h5>
        <a href="{{ route('asset.departments.index') }}" class="btn btn-secondary">Back</a>
    </div>
    <div class="card-body">
        <form action="{{ route('asset.departments.update', $department->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label" for="name">Department Name</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ $department->name }}" required />
            </div>

            <div class="mb-3">
                <label class="form-label" for="custodian_id">Custodian (Department Head)</label>
                <select class="form-select" id="custodian_id" name="custodian_id" required>
                    <option value="">Select Custodian</option>
                    @foreach($employees as $employee)
                    <option value="{{ $employee->id }}" {{ $department->custodian_id == $employee->id ? 'selected' : '' }}>
                        {{ $employee->name }} ({{ $employee->empId ?? $employee->id }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label" for="status">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="1" {{ $department->status ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ !$department->status ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Update Department</button>
        </form>
    </div>
</div>
@endsection
