@extends('layouts/layoutMaster')

@section('title', 'Timesheet - Daily Entry')

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
    // Edit Timesheet Modal
    const editModal = new bootstrap.Modal(document.getElementById('editTimesheetModal'));
    const editForm = document.getElementById('editTimesheetForm');

    document.querySelectorAll('.edit-timesheet').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const date = this.getAttribute('data-date');
            const categoryId = this.getAttribute('data-category-id');
            const projectId = this.getAttribute('data-project-id');
            const hours = this.getAttribute('data-hours');
            const description = this.getAttribute('data-description');

            editForm.action = `/pms/timesheets/${id}`;
            document.getElementById('edit_date').value = date;
            document.getElementById('edit_category_id').value = categoryId;
            document.getElementById('edit_project_id').value = projectId || '';
            document.getElementById('edit_hours').value = hours;
            document.getElementById('edit_description').value = description || '';

            editModal.show();
        });
    });
});
</script>
@endsection
@section('header', 'Timesheet - Daily Entry')

@section('content')
<div class="card">
  <div class="card-header">
    <div class="d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Daily Timesheet</h5>
      <div>
        <a href="{{ route('pms.timesheets.calendar') }}" class="btn btn-sm btn-info me-2">
          <i class="fas fa-calendar-alt"></i> Calendar View
        </a>
        <a href="{{ route('pms.timesheets.report') }}" class="btn btn-sm btn-primary">
          <i class="fas fa-chart-bar"></i> Reports
        </a>
      </div>
    </div>
  </div>
  <div class="card-body">
    <form method="GET" class="mb-4">
      <div class="row">
        <div class="col-md-4">
          <label for="date" class="form-label">Date</label>
          <input type="date" name="date" id="date" class="form-control" value="{{ $selectedDate->format('Y-m-d') }}">
        </div>
        <div class="col-md-4 d-flex align-items-end">
          <button type="submit" class="btn btn-primary me-2">Load</button>
          <a href="{{ route('pms.timesheets.index') }}" class="btn btn-secondary">Today</a>
        </div>
      </div>
    </form>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Category</th>
            <th>Project</th>
            <th>Time</th>
            <th>Description</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($timesheets as $timesheet)
          <tr>
            <td>{{ $timesheet->category->name }}</td>
            <td>{{ $timesheet->project ? $timesheet->project->title : 'N/A' }}</td>
            <td>{{ $timesheet->formatted_time }}</td>
            <td>{{ Str::limit($timesheet->description, 50) }}</td>
            <td>
              <button class="btn btn-sm btn-primary edit-timesheet" data-id="{{ $timesheet->id }}"
                data-date="{{ $timesheet->date->format('Y-m-d') }}" data-category-id="{{ $timesheet->category_id }}"
                data-project-id="{{ $timesheet->project_id }}" data-hours="{{ $timesheet->hours }}"
                data-description="{{ $timesheet->description }}">
                <i class="fas fa-edit"></i>
              </button>
              <form action="{{ route('pms.timesheets.destroy', $timesheet->id) }}" method="POST" class="d-inline"
                onsubmit="return confirm('Are you sure you want to delete this entry?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger">
                  <i class="fas fa-trash"></i>
                </button>
              </form>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="text-center">No timesheet entries for this date</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-4">
      <h5>Add New Entry</h5>
      <form action="{{ route('pms.timesheets.store') }}" method="POST">
        @csrf
        <div class="row">
          <div class="col-md-3">
            <label for="new_date" class="form-label">Date</label>
            <input type="date" name="date" id="new_date" class="form-control"
              value="{{ $selectedDate->format('Y-m-d') }}" required>
          </div>
          <div class="col-md-3">
            <label for="category_id" class="form-label">Category</label>
            <select name="category_id" id="category_id" class="form-select" required>
              <option value="">Select Category</option>
              @foreach($categories as $category)
              <option value="{{ $category->id }}">{{ $category->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label for="project_id" class="form-label">Project (Optional)</label>
            <select name="project_id" id="project_id" class="form-select">
              <option value="">Select Project</option>
              @foreach($projects as $project)
              <option value="{{ $project->id }}">{{ $project->title }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <label for="hours" class="form-label">Hours</label>
            <input type="number" step="0.1" min="0.1" max="24" name="hours" id="hours" class="form-control" required>
          </div>
        </div>
        <div class="row mt-3">
          <div class="col-md-9">
            <label for="description" class="form-label">Description (Optional)</label>
            <textarea name="description" id="description" class="form-control" rows="2"></textarea>
          </div>
          <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">
              <i class="fas fa-plus"></i> Add Entry
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editTimesheetModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="editTimesheetForm" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title">Edit Timesheet Entry</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="edit_date" class="form-label">Date</label>
            <input type="date" name="date" id="edit_date" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="edit_category_id" class="form-label">Category</label>
            <select name="category_id" id="edit_category_id" class="form-select" required>
              @foreach($categories as $category)
              <option value="{{ $category->id }}">{{ $category->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label for="edit_project_id" class="form-label">Project (Optional)</label>
            <select name="project_id" id="edit_project_id" class="form-select">
              <option value="">Select Project</option>
              @foreach($projects as $project)
              <option value="{{ $project->id }}">{{ $project->title }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label for="edit_hours" class="form-label">Hours</label>
            <input type="number" step="0.1" min="0.1" max="24" name="hours" id="edit_hours" class="form-control"
              required>
          </div>
          <div class="mb-3">
            <label for="edit_description" class="form-label">Description (Optional)</label>
            <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection