@extends('layouts/layoutMaster')

@section('title', 'Create Asset')

@section('vendor-style')
@endsection

@section('vendor-script')
@endsection

@section('page-script')
<script>
    document.getElementById('asset_category_id').addEventListener('change', function() {
        let categoryId = this.value;
        let container = document.getElementById('dynamic-specifications');
        container.innerHTML = '<p class="text-muted">Loading...</p>';

        if(categoryId) {
            fetch(`/asset/categories/${categoryId}/schema`)
            .then(response => response.json())
            .then(schema => {
                container.innerHTML = '';
                if(schema && schema.length > 0) {
                    schema.forEach((field, index) => {
                        let html = '';
                        let required = field.required == 1 ? 'required' : '';
                        let label = field.label + (field.required == 1 ? ' *' : '');
                        let fieldName = `specifications[${field.label}]`;

                        html += `<div class="col-md-6 mb-3">`;
                        html += `<label class="form-label">${label}</label>`;

                        if(field.type === 'text' || field.type === 'number' || field.type === 'date') {
                            html += `<input type="${field.type}" name="${fieldName}" class="form-control" ${required}>`;
                        } else if(field.type === 'boolean') {
                            html += `<select name="${fieldName}" class="form-select" ${required}>`;
                            html += `<option value="Yes">Yes</option><option value="No">No</option>`;
                            html += `</select>`;
                        }

                        html += `</div>`;
                        container.insertAdjacentHTML('beforeend', html);
                    });
                } else {
                    container.innerHTML = '<p class="text-muted">No custom specifications defined for this category.</p>';
                }
            });
        } else {
            container.innerHTML = '<p class="text-muted small">Select a category to load specification fields.</p>';
        }
    });
</script>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Create New Asset</h5>
        <a href="{{ route('asset.masters.index') }}" class="btn btn-secondary">Back</a>
    </div>
    <div class="card-body">
        <form action="{{ route('asset.masters.store') }}" method="POST">
            @csrf

            <h6 class="mb-3">Basic Information</h6>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="asset_category_id">Category</label>
                    <select class="form-select" id="asset_category_id" name="asset_category_id" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}
                            @if($category->parent) (Main: {{ $category->parent->name }}) @endif
                            ({{ $category->department->name }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="name">Asset Name</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="e.g. MacBook Pro M3" required />
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="asset_brand_id">Brand</label>
                    <select class="form-select" id="asset_brand_id" name="asset_brand_id">
                        <option value="">Select Brand</option>
                        @foreach($brands as $brand)
                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="model">Model</label>
                    <input type="text" class="form-control" id="model" name="model" placeholder="e.g. A2991" />
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="serial_number">Serial Number</label>
                    <input type="text" class="form-control" id="serial_number" name="serial_number" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="asset_vendor_id">Vendor</label>
                    <select class="form-select" id="asset_vendor_id" name="asset_vendor_id">
                        <option value="">Select Vendor</option>
                        @foreach($vendors as $vendor)
                        <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="make">Legacy/Other Make (Optional)</label>
                    <input type="text" class="form-control" id="make" name="make" placeholder="If brand not listed" />
                </div>
            </div>

            <hr>
            <h6 class="mb-3">Financial Details</h6>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="purchase_date">Purchase Date</label>
                    <input type="date" class="form-control" id="purchase_date" name="purchase_date" />
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="purchase_cost">Purchase Cost</label>
                    <input type="number" step="0.01" class="form-control" id="purchase_cost" name="purchase_cost" min="0" />
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="warranty_expiry_date">Warranty Expiry</label>
                    <input type="date" class="form-control" id="warranty_expiry_date" name="warranty_expiry_date" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="status">Initial Status</label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="1">Available (In Stock)</option>
                        <option value="2">Allocated (In Use)</option>
                        <option value="3">Maintenance</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="condition">Condition</label>
                    <select class="form-select" id="condition" name="condition">
                        <option value="New">New</option>
                        <option value="Good">Good</option>
                        <option value="Fair">Fair</option>
                        <option value="Poor">Poor</option>
                    </select>
                </div>
            </div>

            <hr>
            <h6 class="mb-3">Specifications</h6>
            <div class="row" id="dynamic-specifications">
                <p class="text-muted small">Select a category to load specification fields.</p>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Save Asset</button>
            </div>
        </form>
    </div>
</div>
        } catch (error) {
            console.error(error);
        }
    }
</script>
@endsection
