@extends('layouts.layoutMaster')

@section('title', 'Create Location')

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Assets / Locations /</span> Create
</h4>

<div class="card">
  <div class="card-header">
    <h5 class="mb-0">Add New Location</h5>
  </div>
  <div class="card-body">
    <form action="{{ route('asset.locations.store') }}" method="POST">
      @csrf
      <div class="mb-3">
        <label class="form-label" for="name">Location Name</label>
        <input type="text" class="form-control" id="name" name="name" required>
      </div>
      <div class="mb-3">
        <label class="form-label" for="address">Address</label>
        <textarea class="form-control" id="address" name="address" rows="3"></textarea>
      </div>
      <button type="submit" class="btn btn-primary">Submit</button>
      <a href="{{ route('asset.locations.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
  </div>
</div>
@endsection
