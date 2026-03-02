@extends('layouts.layoutMaster')

@section('title', 'Asset Locations')

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Assets /</span> Locations
</h4>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Location List</h5>
    <a href="{{ route('asset.locations.create') }}" class="btn btn-primary">Add Location</a>
  </div>
  <div class="table-responsive text-nowrap">
    <table class="table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Address</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        @foreach($locations as $location)
        <tr>
          <td>{{ $location->name }}</td>
          <td>{{ $location->address }}</td>
          <td>
            <a href="{{ route('asset.locations.edit', $location->id) }}" class="btn btn-sm btn-icon btn-text-secondary rounded-pill me-2"><i class="ti ti-edit"></i></a>
            <form action="{{ route('asset.locations.destroy', $location->id) }}" method="POST" style="display:inline-block;">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-sm btn-icon btn-text-danger rounded-pill"><i class="ti ti-trash"></i></button>
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
