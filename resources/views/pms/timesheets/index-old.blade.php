@extends('layouts.app')

@section('title', 'Timesheet')
@section('header', 'Daily Timesheet Entry')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Timesheet for {{ $selectedDate->format('l, F j, Y') }}</h5>
            <div>
                <a href="{{ route('pms.timesheets.calendar') }}" class="btn btn-sm btn-secondary me-2">
                    <i class="fas fa-calendar"></i> Calendar View
                </a>
                <a href="{{ route('pms.timesheets.report') }}" class="btn btn-sm btn-info">
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
                    <input type="date" name="date" id="date" class="form-control" 
                           value="{{ $selectedDate->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Go</button>
                    <a href="{{ route('pms.timesheets.index') }}" class="btn btn-secondary">Today</a>
                </div>
            </div>
        </form>

        @if($timesheets->count() > 0)
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
                    @foreach($timesheets as $timesheet)
                    <tr>
                        <td>{{ $timesheet->category->name }}</td>
                        <td>{{ $timesheet->project ? $timesheet->project->title : 'N/A' }}</td>
                        <td>{{ $timesheet->formatted_time }}</td>
                        <td>{{ Str::limit($timesheet->description, 50) }}</td>
                        <td>
                            <button class="btn btn-sm btn-primary edit-timesheet" 
                                    data-id="{{ $timesheet->id }}"
                                    data-category-id="{{ $timesheet->category_id }}"
                                    data-project-id="{{ $timesheet->project_id }}"
                                    data-hours="{{ $timesheet->hours }}"
                                    data-description="{{ $timesheet->description }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('pms.timesheets.destroy', $timesheet->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="alert alert-info">No time entries for this date.</div>
        @endif

        <hr>

        <h5 class="mb-3">Add New Entry</h5>
        <form action="{{ route('pms.timesheets.store') }}" method="POST">
            @csrf
            <input type="hidden" name="date" value="{{ $selectedDate->format('Y-m-d') }}">
            
            <div class="row">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select name="category_id" id="category_id" class="form-select" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="project_id" class="form-label">Project</label>
                        <select name="project_id" id="project_id" class="form-select">
                            <option value="">Select Project (Optional)</option>
                            @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="mb-3">
                        <label for="hours" class="form-label">Hours</label>
                        <input type="number" step="0.1" min="0.1" max="24" name="hours" id="hours" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <input type="text" name="description" id="description" class="form-control">
                    </div>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Add</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Timesheet Modal -->
<div class="modal fade" id="editTimesheetModal" tabindex="-1" aria-labelledby="editTimesheetModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editTimesheetForm" method="POST">
                @csrf
                @method('PUT')
                
                <div class="modal-header">
                    <h5 class="modal-title" id="editTimesheetModalLabel">Edit Timesheet Entry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_category_id" class="form-label">Category</label>
                        <select name="category_id" id="edit_category_id" class="form-select" required>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_project_id" class="form-label">Project</label>
                        <select name="project_id" id="edit_project_id" class="form-select">
                            <option value="">Select Project (Optional)</option>
                            @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_hours" class="form-label">Hours</label>
                        <input type="number" step="0.1" min="0.1" max="24" name="hours" id="edit_hours" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
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

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle edit button clicks
    document.querySelectorAll('.edit-timesheet').forEach(button => {
        button.addEventListener('click', function() {
            const form = document.getElementById('editTimesheetForm');
            form.action = `/pms/timesheets/${this.dataset.id}`;
            
            document.getElementById('edit_category_id').value = this.dataset.categoryId;
            document.getElementById('edit_project_id').value = this.dataset.projectId || '';
            document.getElementById('edit_hours').value = this.dataset.hours;
            document.getElementById('edit_description').value = this.dataset.description || '';
            
            const modal = new bootstrap.Modal(document.getElementById('editTimesheetModal'));
            modal.show();
        });
    });

    // Dynamic project loading based on category
    document.getElementById('category_id').addEventListener('change', function() {
        const categoryId = this.value;
        const projectSelect = document.getElementById('project_id');
        
        // Clear existing options except the first one
        while (projectSelect.options.length > 1) {
            projectSelect.remove(1);
        }
        
        if (categoryId) {
            // Fetch projects for this category
            fetch(`/pms/timesheets/projects?category_id=${categoryId}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(project => {
                        const option = document.createElement('option');
                        option.value = project.id;
                        option.textContent = project.title;
                        projectSelect.appendChild(option);
                    });
                });
        }
    });
});
</script>
@endsection