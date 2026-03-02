@extends('layouts.layoutMaster')

@section('title', 'Edit Floor')

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Assets / Floors /</span> Edit
</h4>

<div class="card">
  <div class="card-header">
    <h5 class="mb-0">Edit Floor</h5>
  </div>
  <div class="card-body">
    <form action="{{ route('asset.floors.update', $floor->id) }}" method="POST">
      @csrf
      @method('PUT')
      <div class="mb-3">
        <label class="form-label" for="location_id">Location</label>
        <select class="form-select" id="location_id" name="location_id" required>
          <option value="">Select Location</option>
          @foreach($locations as $location)
          <option value="{{ $location->id }}" {{ $floor->location_id == $location->id ? 'selected' : '' }}>{{ $location->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label" for="name">Floor Name</label>
        <input type="text" class="form-control" id="name" name="name" value="{{ $floor->name }}" required>
      </div>
      <button type="submit" class="btn btn-primary">Update</button>
      <a href="{{ route('asset.floors.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
  </div>
</div>
@endsection
