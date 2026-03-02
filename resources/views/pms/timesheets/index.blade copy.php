@extends('layouts/layoutMaster')

@section('title', 'Timesheet - Quick Entry')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}" />
<style>
/* Improved Timesheet Grid Styles */
.timesheet-container {
    width: 100%;
    overflow-x: auto;
    margin-bottom: 20px;
}

.project-card {
    border: 1px solid #eee;
    border-radius: 4px;
    margin-bottom: 20px;
    background: white;
}

.project-header {
    background-color: #f1f1f1;
    padding: 12px 15px;
    font-weight: bold;
    border-bottom: 1px solid #eee;
}

.category-row {
    display: flex;
    align-items: center;
    padding: 10px 15px;
    border-bottom: 1px solid #eee;
}

.category-name {
    flex: 0 0 200px;
    font-weight: 500;
    padding-right: 15px;
}

.time-input-container {
    display: flex;
    align-items: center;
}

.time-input {
    width: 80px;
    text-align: center;
    padding: 8px;
    margin-right: 10px;
}

.save-btn-container {
    padding: 15px 0;
    text-align: center;
}

.today-header {
    background-color: #f8f9fa;
    padding: 10px;
    margin-bottom: 20px;
    text-align: center;
    font-weight: bold;
    border-radius: 4px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .category-row {
        flex-direction: column;
        align-items: flex-start;
    }

    .category-name {
        flex: 1;
        margin-bottom: 10px;
    }

    .time-input {
        width: 100%;
        margin-bottom: 10px;
    }
}
</style>
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js')}}"></script>
<script src="{{asset('assets/vendor/libs/block-ui/block-ui.js')}}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize datepicker
    $('#date').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true
    }).on('changeDate', function() {
        window.location.href = "{{ route('pms.timesheets.index') }}?date=" + $(this).val();
    });

    // Save all timesheet entries
    $('#saveAll').click(function() {
        const entries = [];
        let hasEntries = false;

        // Project time entries
        $('.project-time-input').each(function() {
            const hours = parseFloat($(this).val());
            if (hours > 0) {
                hasEntries = true;
                entries.push({
                    date: $(this).data('date'),
                    category_id: $(this).data('category-id'),
                    project_id: $(this).data('project-id'),
                    hours: hours
                });
            }
        });

        // Category-only time entries
        $('.category-time-input').each(function() {
            const hours = parseFloat($(this).val());
            if (hours > 0) {
                hasEntries = true;
                entries.push({
                    date: $(this).data('date'),
                    category_id: $(this).data('category-id'),
                    project_id: null,
                    hours: hours
                });
            }
        });

        if (!hasEntries) {
            alert('Please enter at least one time entry');
            return;
        }

        blockUI(); // Show loading indicator

        $.ajax({
            url: "{{ route('pms.timesheets.bulk') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                entries: entries
            },
            success: function(response) {
                unblockUI();
                if (response.success) {
                    window.location.reload();
                } else {
                    alert(response.message || 'Error saving timesheets');
                }
            },
            error: function() {
                unblockUI();
                alert('Error saving timesheets');
            }
        });
    });

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

function blockUI() {
    $('body').block({
        message: '<div class="spinner-border text-primary" role="status"></div>',
        css: {
            backgroundColor: 'transparent',
            border: 'none'
        },
        overlayCSS: {
            backgroundColor: '#fff',
            opacity: 0.8
        }
    });
}

function unblockUI() {
    $('body').unblock();
}
</script>
@endsection

@section('header', 'Timesheet - Quick Entry')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Quick Timesheet Entry</h5>
            <div>
                <input type="text" name="date" id="date" class="form-control"
                    value="{{ $selectedDate->format('Y-m-d') }}" style="width: 150px;">
            </div>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="timesheet-container">
            <div class="today-header">
                {{ $selectedDate->format('D, M j, Y') }}
            </div>

            <!-- Projects List -->
            <!-- @foreach($projects as $project) -->
            <div class="project-card">
                <div class="project-header">

                </div>

                <!-- Categories for this project -->
                @foreach($projects as $project)
                <div class="category-row">
                    <div class="category-name">
                        {{ $category->name }}
                    </div>
                    <div class="time-input-container">
                        @php
                        $entry = $timesheets->firstWhere(fn($item) =>
                        $item->project_id == $project->id &&
                        $item->category_id == $category->id);
                        @endphp
                        <input type="number" step="0.1" min="0" max="24" class="form-control project-time-input"
                            value="{{ $entry ? $entry->hours : '' }}" data-date="{{ $selectedDate->format('Y-m-d') }}"
                            data-category-id="{{ $category->id }}" data-project-id="{{ $project->id }}"
                            placeholder="0.0">
                        <span>hours</span>
                    </div>
                </div>
                @endforeach
            </div>


            <!-- Category-only entries -->
            <div class="project-card">
                <div class="project-header">
                    General Time (No Project)
                </div>

                @foreach($categories as $category)
                <div class="category-row">
                    <div class="category-name">
                        {{ $category->name }}
                    </div>
                    <div class="time-input-container">
                        @php
                        $entry = $timesheets->firstWhere(fn($item) =>
                        $item->project_id === null &&
                        $item->category_id == $category->id);
                        @endphp
                        <input type="number" step="0.1" min="0" max="24" class="form-control category-time-input"
                            value="{{ $entry ? $entry->hours : '' }}" data-date="{{ $selectedDate->format('Y-m-d') }}"
                            data-category-id="{{ $category->id }}" placeholder="0.0">
                        <span>hours</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="save-btn-container">
            <button id="saveAll" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>Save All Entries
            </button>
        </div>
    </div>
</div>


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
                    <input type="date" name="date" id="date" class="form-control"
                        value="{{ $selectedDate->format('Y-m-d') }}">
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
                                data-date="{{ $timesheet->date->format('Y-m-d') }}"
                                data-category-id="{{ $timesheet->category_id }}"
                                data-project-id="{{ $timesheet->project_id }}" data-hours="{{ $timesheet->hours }}"
                                data-description="{{ $timesheet->description }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('pms.timesheets.destroy', $timesheet->id) }}" method="POST"
                                class="d-inline"
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
                        <input type="number" step="0.1" min="0.1" max="24" name="hours" id="hours" class="form-control"
                            required>
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
                        <input type="number" step="0.1" min="0.1" max="24" name="hours" id="edit_hours"
                            class="form-control" required>
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