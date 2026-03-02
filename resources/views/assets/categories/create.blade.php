@extends('layouts/layoutMaster')

@section('title', 'Create Asset Category')

@section('vendor-style')
@endsection

@section('vendor-script')
@endsection

@section('page-script')
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Create Asset Category</h5>
        <a href="{{ route('asset.categories.index') }}" class="btn btn-secondary">Back</a>
    </div>
    <div class="card-body">
        <form action="{{ route('asset.categories.store') }}" method="POST">
            @csrf

            <h6 class="mb-3">Category Details</h6>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="asset_department_id">Department</label>
                    <select class="form-select" id="asset_department_id" name="asset_department_id" required>
                        <option value="">Select Department</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="parent_id">Parent Category (Optional)</label>
                    <select class="form-select" id="parent_id" name="parent_id">
                        <option value="">None (Top Level)</option>
                        @foreach($parents as $parent)
                        <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="name">Category Name</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="e.g. Laptops" required />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="prefix">Asset Prefix</label>
                    <input type="text" class="form-control" id="prefix" name="prefix" placeholder="e.g. LAP" required />
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="is_depreciable">Is Depreciable?</label>
                    <select class="form-select" id="is_depreciable" name="is_depreciable" onchange="toggleDepreciation()">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select>
                </div>
            </div>

            <div class="row" id="depreciation-fields" style="display: none;">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="useful_life_years">Useful Life (Years)</label>
                    <input type="number" class="form-control" id="useful_life_years" name="useful_life_years" />
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="salvage_value">Salvage Value</label>
                    <input type="number" class="form-control" id="salvage_value" name="salvage_value" value="0" />
                </div>
            </div>

            <hr>
            <h6 class="mb-3">Specifications Schema</h6>
            <p class="small text-muted">Define custom fields for assets in this category.</p>

            <div id="spec-container"></div>
            <button type="button" class="btn btn-sm btn-outline-primary mb-3" onclick="addSpecField()">+ Add Field</button>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Create Category</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleDepreciation() {
        const val = document.getElementById('is_depreciable').value;
        const fields = document.getElementById('depreciation-fields');
        fields.style.display = val === '1' ? 'flex' : 'none';

        // Toggle required
        document.getElementById('useful_life_years').required = val === '1';
    }

    let specCount = 0;
    function addSpecField() {
        const container = document.getElementById('spec-container');
        const div = document.createElement('div');
        div.className = 'row mb-2 align-items-center';
        div.innerHTML = `
            <div class="col-md-4">
                <input type="text" name="specifications_schema[${specCount}][label]" class="form-control" placeholder="Label (e.g. Create RAM)" required>
            </div>
            <div class="col-md-3">
                <select name="specifications_schema[${specCount}][type]" class="form-select">
                    <option value="text">Text</option>
                    <option value="number">Number</option>
                    <option value="date">Date</option>
                    <option value="boolean">Yes/No</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="specifications_schema[${specCount}][required]" class="form-select">
                    <option value="0">Optional</option>
                    <option value="1">Required</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-sm btn-label-danger" onclick="this.parentElement.parentElement.remove()">X</button>
            </div>
        `;
        container.appendChild(div);
        specCount++;
    }
</script>
@endsection
