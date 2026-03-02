@extends('layouts/layoutMaster')

@section('title', 'Edit Milestone')

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
});
</script>
@endsection
@section('header', 'Edit Milestone: ' . $milestone->name)

@section('content')
<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-body">
        <form action="{{ route('pms.milestones.update', [$project->id, $milestone->id]) }}" method="POST">
          @csrf
          @method('PUT')

          <div class="row mb-3">
            <div class="col-md-6">
              <label for="name" class="form-label">Milestone Name</label>
              <input type="text" name="name" id="name" class="form-control" value="{{ $milestone->name }}" required>
              @error('name')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label for="weightage" class="form-label">Weightage (%)</label>
              <input type="number" name="weightage" id="weightage" class="form-control" min="1" max="100"
                value="{{ $milestone->weightage }}" required>
              @error('weightage')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label for="start_date" class="form-label">Start Date</label>
              <input type="date" name="start_date" id="start_date" class="form-control"
                value="{{ $milestone->start_date->format('Y-m-d') }}" min="{{ $project->start_date->format('Y-m-d') }}"
                max="{{ $project->end_date->format('Y-m-d') }}" required>
              @error('start_date')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label for="end_date" class="form-label">End Date</label>
              <input type="date" name="end_date" id="end_date" class="form-control"
                value="{{ $milestone->end_date->format('Y-m-d') }}" min="{{ $project->start_date->format('Y-m-d') }}"
                max="{{ $project->end_date->format('Y-m-d') }}" required>
              @error('end_date')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-12">
              <label for="description" class="form-label">Description (Optional)</label>
              <textarea name="description" id="description" class="form-control"
                rows="3">{{ $milestone->description }}</textarea>
              @error('description')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-12">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="invoice_trigger" id="invoice_trigger" {{
                  $milestone->invoice_trigger ? 'checked' : '' }}>
                <label class="form-check-label" for="invoice_trigger">
                  Invoice Trigger (Check if this milestone should trigger an invoice)
                </label>
              </div>
            </div>
          </div>

          <div class="mt-4">
            <button type="submit" class="btn btn-primary me-2">Update Milestone</button>
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
        <h5 class="card-title">Project Details</h5>
      </div>
      <div class="card-body">
        <p><strong>Project Code:</strong> {{ $project->project_code }}</p>
        <p><strong>Start Date:</strong> {{ $project->start_date->format('d M Y') }}</p>
        <p><strong>End Date:</strong> {{ $project->end_date->format('d M Y') }}</p>
        <p><strong>Status:</strong>
          <span class="badge bg-{{ $project->status_badge_color }}">
            {{ $project->status_name }}
          </span>
        </p>
      </div>
    </div>

    <div class="card mt-4">
      <div class="card-header">
        <h5 class="card-title">Current Status</h5>
      </div>
      <div class="card-body">
        <p><strong>Milestone Status:</strong>
          <span class="badge bg-{{ $milestone->status_badge_color }}">
            {{ $milestone->status_name }}
          </span>
        </p>
        <p><strong>Tasks Completed:</strong>
          {{ $milestone->tasks->where('status', \App\Models\PMS\Task::STATUS_COMPLETED)->count() }} /
          {{ $milestone->tasks->count() }}
        </p>
      </div>
    </div>
  </div>
</div>


@endsection