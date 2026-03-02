@extends('layouts/layoutMaster')

@section('title', 'Create Task')

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
            endDateInput.value = '';
        }
    });

    // Initialize select2 for multiple assignment
    $('#assigned_to').select2({
        placeholder: "Select team members",
        width: '100%'
    });
});
</script>
@endsection
@section('header', 'Create Task: ' . $milestone->name)

@section('content')
<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-body">
        <form action="{{ route('pms.tasks.store', [$project->id, $milestone->id]) }}" method="POST">
          @csrf

          <div class="row mb-3">
            <div class="col-md-6">
              <label for="name" class="form-label">Task Name</label>
              <input type="text" name="name" id="name" class="form-control" required>
              @error('name')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label for="priority" class="form-label">Priority</label>
              <select name="priority" id="priority" class="form-select" required>
                <option value="">Select Priority</option>
                <option value="1">Low</option>
                <option value="2">Medium</option>
                <option value="3">High</option>
                <option value="4">Critical</option>

              </select>
              @error('priority')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label for="start_date" class="form-label">Start Date</label>
              <input type="date" name="start_date" id="start_date" class="form-control"
                min="{{ $milestone->start_date->format('Y-m-d') }}" max="{{ $milestone->end_date->format('Y-m-d') }}"
                required>
              @error('start_date')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label for="end_date" class="form-label">End Date</label>
              <input type="date" name="end_date" id="end_date" class="form-control"
                min="{{ $milestone->start_date->format('Y-m-d') }}" max="{{ $milestone->end_date->format('Y-m-d') }}"
                required>
              @error('end_date')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-12">
              <label for="description" class="form-label">Description (Optional)</label>
              <textarea name="description" id="description" class="form-control" rows="3"></textarea>
              @error('description')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-12">
              <label for="assigned_to" class="form-label">Assign To (Select multiple if needed)</label>
              <select name="assigned_to[]" id="assigned_to" class="form-select" multiple required>
                @foreach($teamMembers as $member)
                <option value="{{ $member->user->id }}">{{ $member->user->name }}</option>
                @endforeach
              </select>
              @error('assigned_to')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="mt-4">
            <button type="submit" class="btn btn-primary me-2">Create Task</button>
            <a href="{{ route('pms.milestones.show', [$project->id, $milestone->id]) }}"
              class="btn btn-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title">Milestone Details</h5>
      </div>
      <div class="card-body">
        <p><strong>Name:</strong> {{ $milestone->name }}</p>
        <p><strong>Start Date:</strong> {{ $milestone->start_date->format('d M Y') }}</p>
        <p><strong>End Date:</strong> {{ $milestone->end_date->format('d M Y') }}</p>
        <p><strong>Status:</strong>
          <span class="badge bg-{{ $milestone->status_badge_color }}">
            {{ $milestone->status_name }}
          </span>
        </p>
      </div>
    </div>

    <div class="card mt-4">
      <div class="card-header">
        <h5 class="card-title">Project Details</h5>
      </div>
      <div class="card-body">
        <p><strong>Title:</strong> {{ $project->title }}</p>
        <p><strong>Project Code:</strong> {{ $project->project_code }}</p>
        <p><strong>Status:</strong>
          <span class="badge bg-{{ $project->status_badge_color }}">
            {{ $project->status_name }}
          </span>
        </p>
      </div>
    </div>
  </div>
</div>


@endsection