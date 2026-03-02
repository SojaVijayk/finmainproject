@extends('layouts/layoutMaster')

@section('title', 'Asset Categories')

@section('vendor-style')
@endsection

@section('vendor-script')
@endsection

@section('page-script')
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Asset Categories</h5>
        <div class="d-flex gap-2">
            @if(count($myDepartments) > 1 || (Auth::user()->user_role == 'admin'))
            <form action="{{ route('asset.categories.index') }}" method="GET" id="deptFilterForm">
                <select name="department_id" class="form-select form-select-sm {{ $mustSelectDepartment ? 'border-danger' : '' }}" onchange="document.getElementById('deptFilterForm').submit()">
                     <option value="">{{ $mustSelectDepartment ? 'Please Select Department' : 'All Departments' }}</option>
                    @foreach($myDepartments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </form>
            @endif
            <a href="{{ route('asset.categories.create') }}" class="btn btn-primary btn-sm">Add New Category</a>
        </div>
    </div>

    @if($mustSelectDepartment)
    <div class="alert alert-warning mx-4 mt-3 mb-0">
        <i class="ti ti-alert-triangle me-2"></i> Please select a department to view its categories.
    </div>
    @endif
    <div class="table-responsive text-nowrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Dept</th>
                    <th>Name</th>
                    <th>Parent Category</th>
                    <th>Prefix</th>
                    <th>Depreciable?</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $category)
                <tr>
                    <td>{{ $category->department->name ?? '-' }}</td>
                    <td>{{ $category->name }}</td>
                    <td><span class="badge bg-label-secondary">{{ $category->parent->name ?? 'None' }}</span></td>
                    <td><span class="badge bg-label-info">{{ $category->prefix }}</span></td>
                    <td>
                        @if($category->is_depreciable)
                        <span class="badge bg-label-warning">Yes ({{ $category->useful_life_years }} yr)</span>
                        @else
                        <span class="badge bg-label-secondary">No</span>
                        @endif
                    </td>
                    <td>
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{ route('asset.categories.edit', $category->id) }}"><i class="ti ti-pencil me-1"></i> Edit</a>
                                <form action="{{ route('asset.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('All assets in this category will be deleted. Are you sure?');">
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
