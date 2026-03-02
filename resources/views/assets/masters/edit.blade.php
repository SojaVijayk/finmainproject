@extends('layouts/layoutMaster')

@section('title', 'Edit Asset')

@section('vendor-style')
@endsection

@section('vendor-script')
@endsection

@section('page-script')
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Edit Asset: {{ $master->asset_number }}</h5>
        <a href="{{ route('asset.masters.index') }}" class="btn btn-secondary">Back</a>
    </div>
    <div class="card-body">
        <form action="{{ route('asset.masters.update', $master->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Category is fixed usually, or difficult to change because of Asset Number logic. Let's make it readonly or disabled -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Category</label>
                    <input type="text" class="form-control" value="{{ $master->category->parent ? $master->category->parent->name . ' > ' : '' }}{{ $master->category->name }}" disabled />
                    <input type="hidden" name="asset_category_id" value="{{ $master->asset_category_id }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="name">Asset Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ $master->name }}" required />
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="asset_brand_id">Brand</label>
                    <select class="form-select" id="asset_brand_id" name="asset_brand_id">
                        <option value="">Select Brand</option>
                        @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ $master->asset_brand_id == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="model">Model</label>
                    <input type="text" class="form-control" id="model" name="model" value="{{ $master->model }}" />
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="serial_number">Serial Number</label>
                    <input type="text" class="form-control" id="serial_number" name="serial_number" value="{{ $master->serial_number }}" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="asset_vendor_id">Vendor</label>
                    <select class="form-select" id="asset_vendor_id" name="asset_vendor_id">
                        <option value="">Select Vendor</option>
                        @foreach($vendors as $vendor)
                        <option value="{{ $vendor->id }}" {{ $master->asset_vendor_id == $vendor->id ? 'selected' : '' }}>{{ $vendor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="make">Legacy/Other Make (Optional)</label>
                    <input type="text" class="form-control" id="make" name="make" value="{{ $master->make }}" />
                </div>
            </div>

            <hr>
            <h6 class="mb-3">Financial Details</h6>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="purchase_date">Purchase Date</label>
                    <input type="date" class="form-control" id="purchase_date" name="purchase_date" value="{{ $master->purchase_date ? $master->purchase_date->format('Y-m-d') : '' }}" />
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="purchase_cost">Purchase Cost</label>
                    <input type="number" step="0.01" class="form-control" id="purchase_cost" name="purchase_cost" value="{{ $master->purchase_cost }}" min="0" />
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="warranty_expiry_date">Warranty Expiry</label>
                    <input type="date" class="form-control" id="warranty_expiry_date" name="warranty_expiry_date" value="{{ $master->warranty_expiry_date ? $master->warranty_expiry_date->format('Y-m-d') : '' }}" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="status">Status</label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="1" {{ $master->status == 1 ? 'selected' : '' }}>Available (In Stock)</option>
                        <option value="2" {{ $master->status == 2 ? 'selected' : '' }}>Allocated (In Use)</option>
                        <option value="3" {{ $master->status == 3 ? 'selected' : '' }}>Maintenance</option>
                        <option value="4" {{ $master->status == 4 ? 'selected' : '' }}>Disposed</option>
                        <option value="5" {{ $master->status == 5 ? 'selected' : '' }}>Scrap</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="condition">Condition</label>
                    <select class="form-select" id="condition" name="condition">
                        <option value="New" {{ $master->condition == 'New' ? 'selected' : '' }}>New</option>
                        <option value="Good" {{ $master->condition == 'Good' ? 'selected' : '' }}>Good</option>
                        <option value="Fair" {{ $master->condition == 'Fair' ? 'selected' : '' }}>Fair</option>
                        <option value="Poor" {{ $master->condition == 'Poor' ? 'selected' : '' }}>Poor</option>
                    </select>
                </div>
            </div>

            <hr>
            <h6 class="mb-3">Specifications</h6>
            <div class="row" id="dynamic-specifications">
                <!-- If category has schema, render existing values -->
                @if($master->category && $master->category->specifications_schema)
                    @foreach($master->category->specifications_schema as $field)
                        @php
                            $val = $master->specifications[$field['label']] ?? '';
                            $label = $field['label'] . ($field['required'] == '1' ? ' *' : '');
                        @endphp
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ $label }}</label>
                            @if($field['type'] == 'boolean')
                            <select name="specifications[{{ $field['label'] }}]" class="form-select" {{ $field['required'] == '1' ? 'required' : '' }}>
                                <option value="Yes" {{ $val == 'Yes' ? 'selected' : '' }}>Yes</option>
                                <option value="No" {{ $val == 'No' ? 'selected' : '' }}>No</option>
                            </select>
                            @else
                            <input type="{{ $field['type'] }}" class="form-control" name="specifications[{{ $field['label'] }}]" value="{{ $val }}" {{ $field['required'] == '1' ? 'required' : '' }} />
                            @endif
                        </div>
                    @endforeach
                @else
                    <p class="text-muted">No custom specifications defined for this category.</p>
                @endif
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Update Asset</button>
            </div>
        </form>
    </div>
</div>
@endsection
