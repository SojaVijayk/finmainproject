@extends('layouts.layoutMaster')

@section('title', 'Create Floor')

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Assets / Floors /</span> Create
</h4>

<div class="card">
  <div class="card-header">
    <h5 class="mb-0">Add New Floor</h5>
  </div>
  <div class="card-body">
    <form action="{{ route('asset.floors.store') }}" method="POST">
      @csrf
      <div class="mb-3">
        <label class="form-label" for="location_id">Location</label>
        <select class="form-select" id="location_id" name="location_id" required>
          <option value="">Select Location</option>
          @foreach($locations as $location)
          <option value="{{ $location->id }}">{{ $location->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label" for="name">Floor Name</label>
        <input type="text" class="form-control" id="name" name="name" required>
      </div>
      <button type="submit" class="btn btn-primary">Submit</button>
      <a href="{{ route('asset.floors.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
  </div>
</div>
@endsection
