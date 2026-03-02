@extends('layouts/layoutMaster')

@section('title', 'Edit Brand')

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Edit Asset Brand</h5>
        <a href="{{ route('asset.brands.index') }}" class="btn btn-secondary">Back</a>
    </div>
    <div class="card-body">
        <form action="{{ route('asset.brands.update', $brand->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" class="form-control" name="name" value="{{ $brand->name }}" required />
            </div>
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="1" {{ $brand->status ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ !$brand->status ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Brand</button>
        </form>
    </div>
</div>
@endsection
