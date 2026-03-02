@extends('layouts.layoutMaster')

@section('title', 'Asset Floors')

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Assets / Floors /</span> List
</h4>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Floor List</h5>
    <a href="{{ route('asset.floors.create') }}" class="btn btn-primary">Add Floor</a>
  </div>
  <div class="table-responsive text-nowrap">
    <table class="table">
      <thead>
        <tr>
          <th>Location</th>
          <th>Floor Name</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        @foreach($floors as $floor)
        <tr>
          <td>{{ $floor->location->name }}</td>
          <td>{{ $floor->name }}</td>
          <td>
            <a href="{{ route('asset.floors.edit', $floor->id) }}" class="btn btn-sm btn-icon btn-text-secondary rounded-pill me-2"><i class="ti ti-edit"></i></a>
            <form action="{{ route('asset.floors.destroy', $floor->id) }}" method="POST" style="display:inline-block;">
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
