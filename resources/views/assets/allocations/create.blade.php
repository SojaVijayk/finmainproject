@extends('layouts/layoutMaster')

@section('title', 'Issue Asset')

@section('vendor-style')
@endsection

@section('vendor-script')
@endsection

@section('page-script')
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Issue Asset</h5>
        @if($asset)
        <a href="{{ route('asset.masters.show', $asset->id) }}" class="btn btn-secondary">Back</a>
        @else
        <a href="{{ route('asset.masters.index') }}" class="btn btn-secondary">Back</a>
        @endif
    </div>
    <div class="card-body">
        <form action="{{ route('asset.allocations.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="asset_id">Asset to Issue</label>
                    @if($asset)
                        <input type="text" class="form-control" value="{{ $asset->name }} ({{ $asset->asset_number }})" disabled />
                        <input type="hidden" name="asset_id" value="{{ $asset->id }}">
                    @else
                        <select class="form-select" id="asset_id" name="asset_id" required>
                            <option value="">Select Asset</option>
                            @foreach($availableAssets as $availAsset)
                            <option value="{{ $availAsset->id }}">{{ $availAsset->name }} ({{ $availAsset->asset_number }})</option>
                            @endforeach
                        </select>
                    @endif
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="employee_id">Issue To (Employee)</label>
                    <select class="form-select" id="employee_id" name="employee_id">
                        <option value="">Select Employee (Optional)</option>
                        @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->employee_code ?? 'N/A' }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="location_id">Location</label>
                    <select class="form-select" id="location_id" name="location_id">
                        <option value="">Select Location (Optional)</option>
                        @foreach($locations as $location)
                        <option value="{{ $location->id }}">{{ $location->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="floor_id">Floor</label>
                    <select class="form-select" id="floor_id" name="floor_id">
                        <option value="">Select Floor (Optional)</option>
                        @foreach($floors as $floor)
                        <option value="{{ $floor->id }}" data-location="{{ $floor->location_id }}">{{ $floor->name }} ({{ $floor->location->name ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="room_id">Room</label>
                     <select class="form-select" id="room_id" name="room_id">
                        <option value="">Select Room (Optional)</option>
                        @foreach($rooms as $room)
                        <option value="{{ $room->id }}" data-floor="{{ $room->floor_id }}">{{ $room->room_number }} ({{ $room->name ?? '' }}) - {{ $room->floor->name ?? '' }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="issued_at">Issue Date</label>
                    <input type="date" class="form-control" id="issued_at" name="issued_at" value="{{ date('Y-m-d') }}" required />
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="expected_return_at">Expected Return Date (Optional)</label>
                    <input type="date" class="form-control" id="expected_return_at" name="expected_return_at" />
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Confirm Issue</button>
            </div>
        </form>
    </div>
</div>
@endsection
