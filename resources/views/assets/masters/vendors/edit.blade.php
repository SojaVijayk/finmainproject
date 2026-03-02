@extends('layouts/layoutMaster')

@section('title', 'Edit Vendor')

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Edit Asset Vendor</h5>
        <a href="{{ route('asset.vendors.index') }}" class="btn btn-secondary">Back</a>
    </div>
    <div class="card-body">
        <form action="{{ route('asset.vendors.update', $vendor->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" class="form-control" name="name" value="{{ $vendor->name }}" required />
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Contact Person</label>
                    <input type="text" class="form-control" name="contact_person" value="{{ $vendor->contact_person }}" />
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" value="{{ $vendor->email }}" />
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" class="form-control" name="phone" value="{{ $vendor->phone }}" />
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Address</label>
                <textarea class="form-control" name="address" rows="3">{{ $vendor->address }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="1" {{ $vendor->status ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ !$vendor->status ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Vendor</button>
        </form>
    </div>
</div>
@endsection
