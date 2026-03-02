@extends('layouts/layoutMaster')

@section('title', 'Edit Task')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/spinkit/spinkit.css')}}" />

@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js')}}"></script>
<script src="{{asset('assets/vendor/libs/block-ui/block-ui.js')}}"></script>

@endsection

@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Set end date minimum based on start date
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');

    startDateInput.addEventListener('change', function() {
        endDateInput.min = this.value;

        // If end date is before start date, reset it
        if (endDateInput.value && endDateInput.value < this.value) {
            endDateInput.value = this.value;
        }
    });
});
</script>
@endsection
@section('header', 'Edit Task: ' . $task->name)

@section('content')
<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-body">
        <form action="{{ route('pms.tasks.update', [$project->id, $milestone->id, $task->id]) }}" method="POST">
          @csrf
          @method('PUT')

          <div class="row mb-3">
            <div class="col-md-12">
              <label for="name" class="form-label">Task Name</label>
              <input type="text" name="name" id="name" class="form-control" value="{{ $task->name }}" required>
              @error('name')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label for="start_date" class="form-label">Start Date</label>
              <input type="date" name="start_date" id="start_date" class="form-control"
                value="{{ $task->start_date->format('Y-m-d') }}" required>
              @error('start_date')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label for="end_date" class="form-label">End Date</label>
              <input type="date" name="end_date" id="end_date" class="form-control"
                value="{{ $task->end_date->format('Y-m-d') }}" required>
              @error('end_date')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label for="priority" class="form-label">Priority</label>
              <select name="priority" id="priority" class="form-select" required>
                <option value="1" {{ $task->priority == 1 ? 'selected' : '' }}>Low</option>
                <option value="2" {{ $task->priority == 2 ? 'selected' : '' }}>Medium</option>
                <option value="3" {{ $task->priority == 3 ? 'selected' : '' }}>High</option>
              </select>
              @error('priority')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label for="status" class="form-label">Status</label>
              <select name="status" id="status" class="form-select" required>
                <option value="0" {{ $task->status == 0 ? 'selected' : '' }}>Not Started</option>
                <option value="1" {{ $task->status == 1 ? 'selected' : '' }}>In Progress</option>
                <option value="2" {{ $task->status == 2 ? 'selected' : '' }}>Completed</option>
              </select>
              @error('status')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-12">
              <label for="description" class="form-label">Description</label>
              <textarea name="description" id="description" class="form-control"
                rows="3">{{ $task->description }}</textarea>
              @error('description')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-12">
              <label class="form-label">Assign To</label>
              <div class="row">
                @foreach($teamMembers as $member)
                <div class="col-md-4">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="assigned_to[]"
                      id="member_{{ $member->user_id }}" value="{{ $member->user_id }}" {{
                      $task->assignments->contains('user_id', $member->user_id) ? 'checked' : '' }}>
                    <label class="form-check-label" for="member_{{ $member->user_id }}">
                      {{ $member->user->name }}
                    </label>
                  </div>
                </div>
                @endforeach
              </div>
              @error('assigned_to')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="mt-4">
            <button type="submit" class="btn btn-primary me-2">Update Task</button>
            <a href="{{ route('pms.tasks.show', [$project->id, $milestone->id, $task->id]) }}"
              class="btn btn-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title">Task Details</h5>
      </div>
      <div class="card-body">
        <p><strong>Created At:</strong> {{ $task->created_at->format('d M Y H:i') }}</p>
        <p><strong>Last Updated:</strong> {{ $task->updated_at->format('d M Y H:i') }}</p>
      </div>
    </div>
  </div>
</div>

@endsection