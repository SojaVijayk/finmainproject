@extends('layouts/layoutMaster')

@section('title', 'Asset Master Register')

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
        <div class="d-flex gap-2">
            @if(count($myDepartments) > 1 || (Auth::user()->user_role == 'admin'))
            <form action="{{ route('asset.masters.index') }}" method="GET" id="deptFilterForm">
                 <select name="department_id" class="form-select form-select-sm {{ $mustSelectDepartment ? 'border-danger' : '' }}" onchange="document.getElementById('deptFilterForm').submit()">
                    <option value="">{{ $mustSelectDepartment ? 'Please Select Department' : 'All Departments' }}</option>
                    @foreach($myDepartments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </form>
            @endif
            <a href="{{ route('asset.masters.create') }}" class="btn btn-primary btn-sm">Add New Asset</a>
        </div>
    </div>

    @if($mustSelectDepartment)
    <div class="alert alert-warning mx-4 mt-3 mb-0">
        <i class="ti ti-alert-triangle me-2"></i> Please select a department to view its assets.
    </div>
    @endif
    <div class="table-responsive text-nowrap" style="min-height: 200px;"> <!-- Added min-height to help with dropdown overflow if few rows -->
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Asset #</th>
                    <th>Category</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>User</th>
                    <th>QR</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($assets as $asset)
                <tr>
                    <td>
                        <a href="{{ route('asset.masters.show', $asset->id) }}">
                            <strong>{{ $asset->asset_number }}</strong>
                        </a>
                    </td>
                    <td>{{ $asset->category->name }}</td>
                    <td>{{ $asset->name }}</td>
                    <td>
                        @switch($asset->status)
                            @case(1) <span class="badge bg-label-success">Available</span> @break
                            @case(2) <span class="badge bg-label-warning">Allocated</span> @break
                            @case(3) <span class="badge bg-label-danger">Maintenance</span> @break
                            @case(4) <span class="badge bg-label-dark">Disposed</span> @break
                            @case(5) <span class="badge bg-label-secondary">Scrap</span> @break
                        @endswitch
                    </td>

                     <td>{{ $asset->allocation->employee->name ?? ($asset->latestAllocation->employee->name ?? '-') }}</td>
                    <td>
                        @if($asset->qr_code_path)
                            <a href="{{ asset('storage/' . $asset->qr_code_path) }}" target="_blank" class="btn btn-xs btn-icon btn-outline-secondary">
                                <i class="ti ti-qrcode"></i>
                            </a>
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown" data-bs-boundary="viewport"><i class="ti ti-dots-vertical"></i></button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{ route('asset.masters.show', $asset->id) }}"><i class="ti ti-eye me-1"></i> View</a>
                                <a class="dropdown-item" href="{{ route('asset.masters.edit', $asset->id) }}"><i class="ti ti-pencil me-1"></i> Edit</a>

                                @if($asset->status == 1) <!-- Available -->
                                    <a class="dropdown-item" href="{{ route('asset.allocations.create', ['asset_id' => $asset->id]) }}"><i class="ti ti-arrow-right me-1"></i> Allocate</a>
                                @elseif($asset->status == 2 && $asset->latestAllocation) <!-- Allocated -->
                                    <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#returnAssetModal{{ $asset->id }}">
                                        <i class="ti ti-arrow-left me-1"></i> Return
                                    </button>
                                @endif

                                <a class="dropdown-item" href="{{ route('asset.masters.history', $asset->id) }}"><i class="ti ti-history me-1"></i> History</a>
                                <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#changeStatusModal{{ $asset->id }}">
                                    <i class="ti ti-recycle me-1"></i> Change Status
                                </button>
                                <form action="{{ route('asset.masters.destroy', $asset->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item"><i class="ti ti-trash me-1"></i> Delete</button>
                                </form>
                            </div>
                        </div>

                        <!-- Return Asset Modal -->
                        @if($asset->status == 2 && $asset->latestAllocation)
                        <div class="modal fade" id="returnAssetModal{{ $asset->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <form action="{{ route('asset.allocations.update', $asset->latestAllocation->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="return_asset" value="1">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Return Asset: {{ $asset->asset_number }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Allocated to: <strong>{{ $asset->latestAllocation->employee->name ?? 'Location/Other' }}</strong></p>
                                            <div class="row">
                                                <div class="col mb-3">
                                                    <label for="return_remarks" class="form-label">Return Remarks</label>
                                                    <textarea name="return_remarks" class="form-control" rows="3" required></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Return Asset</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Status Change Modal -->
                        <div class="modal fade" id="changeStatusModal{{ $asset->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <form action="{{ route('asset.masters.change-status', $asset->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Change Status: {{ $asset->asset_number }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col mb-3">
                                                    <label for="status" class="form-label">Status</label>
                                                    <select name="status" class="form-select" required>
                                                        <option value="1" {{ $asset->status == 1 ? 'selected' : '' }}>Available</option>
                                                        <option value="2" {{ $asset->status == 2 ? 'selected' : '' }}>Allocated</option>
                                                        <option value="3" {{ $asset->status == 3 ? 'selected' : '' }}>Maintenance</option>
                                                        <option value="4" {{ $asset->status == 4 ? 'selected' : '' }}>Disposed</option>
                                                        <option value="5" {{ $asset->status == 5 ? 'selected' : '' }}>Scrap</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col mb-3">
                                                    <label for="remarks" class="form-label">Remarks</label>
                                                    <textarea name="remarks" class="form-control" rows="3"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Save changes</button>
                                        </div>
                                    </form>
                                </div>
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
