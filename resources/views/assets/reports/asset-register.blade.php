@extends('layouts/layoutMaster')

@section('title', 'Asset Register Report')

@section('vendor-style')
@endsection

@section('vendor-script')
@endsection

@section('page-script')
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Asset Register</h5>
        <div>
             <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="btn btn-danger btn-sm"><i class="ti ti-file-type-pdf me-1"></i> PDF</a>
             <a href="{{ request()->fullUrlWithQuery(['action' => 'export_excel']) }}" class="btn btn-success btn-sm"><i class="ti ti-file-spreadsheet me-1"></i> Excel</a>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('asset.reports.register') }}" method="GET" class="row g-3 mb-4">
             <div class="col-md-4">
                <label class="form-label">Department</label>
                <select name="department_id" class="form-select {{ $mustSelectDepartment ? 'border-danger' : '' }}">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="1" {{ request('status') == 1 ? 'selected' : '' }}>Available</option>
                    <option value="2" {{ request('status') == 2 ? 'selected' : '' }}>Allocated</option>
                    <option value="3" {{ request('status') == 3 ? 'selected' : '' }}>Maintenance</option>
                    <option value="4" {{ request('status') == 4 ? 'selected' : '' }}>Disposed</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Filter</button>
                <a href="{{ route('asset.reports.register') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>

        @if($mustSelectDepartment)
        <div class="alert alert-warning mb-4">
            <i class="ti ti-alert-triangle me-2"></i> Please select a department to generate the register report.
        </div>
        @endif

        <div class="table-responsive text-nowrap">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Asset #</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Department</th>
                        <th>Cost</th>
                        <th>Status</th>
                        <th>Current User</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assets as $asset)
                    <tr>
                        <td>{{ $asset->asset_number }}</td>
                        <td>{{ $asset->name }}</td>
                        <td>{{ $asset->category->name }}</td>
                        <td>{{ $asset->category->department->name }}</td>
                        <td>{{ number_format($asset->purchase_cost, 2) }}</td>
                        <td>
                             @switch($asset->status)
                                @case(1) Available @break
                                @case(2) Allocated @break
                                @case(3) Maintenance @break
                                @case(4) Disposed @break
                            @endswitch
                        </td>
                        <td>{{ $asset->currentAllocation->employee->name ?? ($asset->currentAllocation->location ?? '-') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No assets found matching criteria.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
