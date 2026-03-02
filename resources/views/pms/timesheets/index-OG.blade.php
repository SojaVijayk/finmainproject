@extends('layouts/layoutMaster')

@section('title', 'Timesheet - Quick Entry')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css') }}" />
<style>
  /* Container Layout */
  .timesheet-container {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  .scroll-x {
    display: flex;
    flex-wrap: nowrap;
    overflow-x: auto;
    gap: 1rem;
    padding-bottom: 0.5rem;
    scrollbar-width: thin;
    scrollbar-color: #bbb transparent;
  }

  .scroll-x::-webkit-scrollbar {
    height: 8px;
  }

  .scroll-x::-webkit-scrollbar-thumb {
    background-color: #bbb;
    border-radius: 4px;
  }

  .project-card {
    flex: 0 0 auto;
    min-width: 250px;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 1rem;
    background: #fff;
  }

  .project-header {
    font-weight: bold;
    margin-bottom: 0.5rem;
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
  }

  .project-category {
    font-weight: normal;
    color: #666;
    font-size: 0.9rem;
  }

  .category-row,
  .time-input-container {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  .category-name {
    flex: 1;
    font-weight: 500;
  }

  .today-header {
    background-color: #f8f9fa;
    padding: 10px;
    margin-bottom: 15px;
    text-align: center;
    font-weight: bold;
    border-radius: 4px;
    font-size: 1.2rem;
  }

  .save-btn-container {
    padding: 15px 0;
    text-align: center;
  }

  @media (max-width: 768px) {
    .category-row {
      flex-direction: column;
      align-items: flex-start;
    }

    .project-header {
      flex-direction: column;
      align-items: flex-start;
    }
  }
</style>
@endsection

@section('vendor-script')
<script src="{{ asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/block-ui/block-ui.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    // Initialize datepicker
    $('#date').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true
    }).on('changeDate', function () {
        window.location.href = "{{ route('pms.timesheets.index') }}?date=" + $(this).val();

    });

    // Save all timesheet entries
    $('#saveAll').click(function () {
        const entries = [];
        let hasEntries = false;

        $('.project-time-input').each(function () {
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

        $('.category-time-input').each(function () {
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

        blockUI();
        $.ajax({
            url: "{{ route('pms.timesheets.bulk') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                entries: entries
            },
            success: function (response) {
                unblockUI();
                if (response.success) {
                    window.location.reload();
                } else {
                    alert(response.message || 'Error saving timesheets');
                }
            },
            error: function () {
                unblockUI();
                alert('Error saving timesheets');
            }
        });
    });

    // Render chart if data exists
    @if($timesheets->count() > 0)
    const ctx = document.getElementById('timeChart').getContext('2d');
    const chartData = {
        labels: {!! json_encode($timesheets->map(function($t) {
            return $t->project ? $t->project->title : $t->category->name;
        })) !!},
        datasets: [{
            label: 'Hours',
            data: {!! json_encode($timesheets->pluck('hours')) !!},
            backgroundColor: [
                '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e',
                '#e74a3b', '#858796', '#5a5c69'
            ],
        }]
    };
    new Chart(ctx, {
        type: 'pie',
        data: chartData,
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' },
                title: { display: true, text: 'Today\'s Time Distribution' }
            }
        }
    });
    @endif
});

function blockUI() {
    $('body').block({
        message: '<div class="spinner-border text-primary" role="status"></div>',
        css: { backgroundColor: 'transparent', border: 'none' },
        overlayCSS: { backgroundColor: '#fff', opacity: 0.8 }
    });
}

function unblockUI() {
    $('body').unblock();
}
</script>
@endsection

@section('header', 'Timesheet - Quick Entry')

@section('content')

<div class="today-header">
  {{ $selectedDate->format('l, F j, Y') }}
</div>



<div class="timesheet-container">
  <!-- Projects Horizontal Scroll old working -->
  {{-- <div class="projects-section scroll-x">

    @foreach($projects as $project)
    @php
    $projectCategory = $project->requirement->category ?? null;
    $timesheetCategory = $categories->firstWhere('name', $projectCategory->name ?? '');
    @endphp

    @if($timesheetCategory)
    <div class="project-card">
      <div class="project-header">
        <span>{{ $project->title }}</span>
        <span class="project-category">Category: {{ $timesheetCategory->name }}</span>
      </div>
      <div class="category-row">
        <div class="category-name">Project Time</div>
        <div class="time-input-container">
          @php
          $entry = $timesheets->firstWhere(fn($item) =>
          $item->project_id == $project->id &&
          $item->category_id == $timesheetCategory->id
          );
          @endphp
          <input type="number" step="0.1" min="0" max="24" class="form-control project-time-input"
            value="{{ $entry ? $entry->hours : '' }}" data-date="{{ $selectedDate->format('Y-m-d') }}"
            data-category-id="{{ $timesheetCategory->id }}" data-project-id="{{ $project->id }}" placeholder="0.0">
          <span>hours</span>
        </div>
      </div>
    </div>
    @endif
    @endforeach
  </div> --}}

  <div class="row alert alert-primary">
    <h4>Project wise Time Entry</h4>
  </div>
  <div class="projects-section scroll-x">
    @php
    // Group projects by their requirement category
    $groupedProjects = $projects->groupBy(function($project) {
    return $project->requirement->category->name ?? 'Uncategorized';
    });
    @endphp


    <div class="accordion" id="projectAccordion">
      @foreach($groupedProjects as $categoryName => $categoryProjects)
      @php
      $timesheetCategory = $categories->firstWhere('name', $categoryName);
      @endphp

      @if($timesheetCategory)
      <div class="accordion-item">
        <h2 class="accordion-header" id="heading-{{ Str::slug($categoryName) }}">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
            data-bs-target="#collapse-{{ Str::slug($categoryName) }}" aria-expanded="false"
            aria-controls="collapse-{{ Str::slug($categoryName) }}">
            {{ $categoryName }}
          </button>
        </h2>

        <div id="collapse-{{ Str::slug($categoryName) }}" class="accordion-collapse collapse"
          aria-labelledby="heading-{{ Str::slug($categoryName) }}" data-bs-parent="#projectAccordion">
          <div class="accordion-body">
            @foreach($categoryProjects as $project)
            @php
            $entry = $timesheets->firstWhere(fn($item) =>
            $item->project_id == $project->id &&
            $item->category_id == $timesheetCategory->id
            );
            @endphp
            <div class="project-card mb-3">
              <div class="project-header">
                <span>{{ $project->title }}</span>
              </div>
              <div class="category-row">
                <div class="category-name">Project Time</div>
                <div class="time-input-container">
                  <input type="number" step="0.1" min="0" max="24" class="form-control project-time-input"
                    value="{{ $entry ? $entry->hours : '' }}" data-date="{{ $selectedDate->format('Y-m-d') }}"
                    data-category-id="{{ $timesheetCategory->id }}" data-project-id="{{ $project->id }}"
                    placeholder="0.0">
                  <span>hours</span>
                </div>
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>
      @endif
      @endforeach
    </div>
  </div>



  <!-- General Time Horizontal Scroll -->
  <div class="row alert alert-primary">
    <h4>General Time Horizontal Scroll</h4>
  </div>
  <div class="general-time-section scroll-x">
    @foreach($categories as $category)
    <div class="project-card">
      <div class="project-header">{{ $category->name }}</div>
      <div class="time-input-container">
        @php
        $entry = $timesheets->firstWhere(fn($item) =>
        $item->project_id === null &&
        $item->category_id == $category->id
        );
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
          <input type="text" name="date" id="date" class="form-control" value="{{  $selectedDate->format('Y-m-d')  }}">
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
    <div class="col-md-4">
      @if($timesheets->count() > 0)
      <div class="card">
        <div class=" mb-4">
          <canvas id="timeChart"></canvas>
        </div>
      </div>
      @endif
    </div>
    <div class="col-md-12">
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
    </div>


    <div class="mt-4" style="display: none;">
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