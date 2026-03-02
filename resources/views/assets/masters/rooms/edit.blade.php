@extends('layouts.layoutMaster')

@section('title', 'Edit Room')

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Assets / Rooms /</span> Edit
</h4>

<div class="card">
  <div class="card-header">
    <h5 class="mb-0">Edit Room</h5>
  </div>
  <div class="card-body">
    <form action="{{ route('asset.rooms.update', $room->id) }}" method="POST">
      @csrf
      @method('PUT')
      <div class="mb-3">
        <label class="form-label" for="floor_id">Floor (Location)</label>
        <select class="form-select" id="floor_id" name="floor_id" required>
          <option value="">Select Floor</option>
          @foreach($floors as $floor)
          <option value="{{ $floor->id }}" {{ $room->floor_id == $floor->id ? 'selected' : '' }}>{{ $floor->name }} ({{ $floor->location->name ?? 'Unknown Location' }})</option>
          @endforeach
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label" for="room_number">Room Number</label>
        <input type="text" class="form-control" id="room_number" name="room_number" value="{{ $room->room_number }}" required>
      </div>
      <div class="mb-3">
        <label class="form-label" for="name">Room Name</label>
        <input type="text" class="form-control" id="name" name="name" value="{{ $room->name }}" placeholder="Optional">
      </div>
      <button type="submit" class="btn btn-primary">Update</button>
      <a href="{{ route('asset.rooms.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
  </div>
</div>
@endsection
