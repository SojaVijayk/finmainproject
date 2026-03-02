@extends('layouts/layoutMaster')

@section('title', 'View Asset - ' . $asset->asset_number)

@section('content')
<div class="row">
    <!-- Asset Details -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Asset Details</h5>
                <div>
                     @if($asset->status == 1)
                        <a href="{{ route('asset.allocations.create', ['asset_id' => $asset->id]) }}" class="btn btn-sm btn-primary">Allocate</a>
                     @endif
                     <a href="{{ route('asset.masters.edit', $asset->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tbody>
                        <tr><th>Asset Number</th><td>{{ $asset->asset_number }}</td></tr>
                        <tr><th>Name</th><td>{{ $asset->name }}</td></tr>
                        <tr><th>Category</th><td>{{ $asset->category->name ?? '-' }}</td></tr>
                        <tr><th>Department</th><td>{{ $asset->category->department->name ?? '-' }}</td></tr>
                        <tr><th>Brand</th><td>{{ $asset->brand->name ?? $asset->make ?? '-' }}</td></tr>
                        <tr><th>Vendor</th><td>{{ $asset->vendor->name ?? '-' }}</td></tr>
                        <tr><th>Model</th><td>{{ $asset->model ?? '-' }}</td></tr>
                        <tr><th>Serial No</th><td>{{ $asset->serial_number ?? '-' }}</td></tr>
                        <tr><th>Purchase Date</th><td>{{ $asset->purchase_date ? $asset->purchase_date->format('Y-m-d') : '-' }}</td></tr>
                        <tr><th>Cost</th><td>{{ $asset->purchase_cost }}</td></tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @switch($asset->status)
                                    @case(1) <span class="badge bg-label-success">Available</span> @break
                                    @case(2) <span class="badge bg-label-warning">Allocated</span> @break
                                    @case(3) <span class="badge bg-label-danger">Maintenance</span> @break
                                    @case(4) <span class="badge bg-label-dark">Disposed</span> @break
                                    @case(5) <span class="badge bg-label-secondary">Scrap</span> @break
                                @endswitch
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- QR Code & Specifications -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">QR Code</h5>
            </div>
            <div class="card-body text-center">
                 @if($asset->qr_code_path)
                    <img src="{{ asset('storage/' . $asset->qr_code_path) }}" alt="QR Code" class="img-fluid" style="max-height: 200px;">
                    <div class="mt-2">
                        <a href="{{ asset('storage/' . $asset->qr_code_path) }}" download="QR_{{ $asset->asset_number }}.svg" class="btn btn-sm btn-outline-primary">Download QR</a>
                    </div>
                @else
                    <p>No QR Code generated.</p>
                @endif
            </div>
        </div>

        @if($asset->specifications)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Specifications</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    @foreach($asset->specifications as $key => $value)
                    <tr>
                        <th>{{ ucfirst($key) }}</th>
                        <td>{{ $value }}</td>
                    </tr>
                    @endforeach
                </table>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- History & Allocations -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">History</h5>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Action</th>
                    <th>By</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @foreach($asset->history as $history)
                <tr>
                    <td>{{ $history->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $history->action }}</td>
                    <td>{{ $history->performer->name ?? '-' }}</td>
                    <td>{{ $history->description }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
