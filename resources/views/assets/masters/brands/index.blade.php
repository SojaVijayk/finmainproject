@extends('layouts/layoutMaster')

@section('title', 'Asset Brands')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Asset Brands</h5>
        <a href="{{ route('asset.brands.create') }}" class="btn btn-primary">Add New Brand</a>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($brands as $brand)
                <tr>
                    <td>{{ $brand->name }}</td>
                    <td>
                        @if($brand->status)
                        <span class="badge bg-label-success">Active</span>
                        @else
                        <span class="badge bg-label-secondary">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{ route('asset.brands.edit', $brand->id) }}"><i class="ti ti-pencil me-1"></i> Edit</a>
                                <form action="{{ route('asset.brands.destroy', $brand->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
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
