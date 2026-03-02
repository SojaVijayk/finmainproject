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


    // 🧮 Function to calculate and update total hours inside each project-card
    function updateCategoryTotal(card) {
        let total = 0;
        card.find('.item-hours').each(function () {
            const val = parseFloat($(this).val());
            if (!isNaN(val) && val > 0) total += val;
        });
        card.find('.category-time-input').val(total.toFixed(2));
    }

    // ➕ Add new item for "Others"
    $(document).on('click', '.add-item-btn', function () {
        const list = $(this).closest('.project-card').find('.custom-items-list');
        const newItem = `
            <div class="input-group mb-2">
                <input type="text" class="form-control item-name" placeholder="Item name" />
                <input type="number" step="0.1" min="0" max="24" class="form-control item-hours" placeholder="Hours" />
                <input type="text" class="form-control item-desc" placeholder="Description (optional)" />
                <button class="btn btn-danger remove-item" type="button">&times;</button>
            </div>`;
        list.append(newItem);
    });

    // 🧮 Recalculate total whenever an item-hours input changes
    $(document).on('input', '.item-hours', function () {
        const card = $(this).closest('.project-card');
        updateCategoryTotal(card);
    });

    // 🗑️ Remove item + recalc total
    $(document).on('click', '.remove-item', function () {
        const card = $(this).closest('.project-card');
        $(this).closest('.input-group').remove();
        updateCategoryTotal(card);
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


    // Category inputs (including "Others" with dynamic items)
    $('.category-time-input').each(function () {
        const $input = $(this);
        const card = $input.closest('.project-card');
        const catId = $input.data('category-id');
        const date = $input.data('date');

        // Gather any dynamic items inside this card
        const items = [];
        card.find('.custom-items-list .input-group').each(function () {
            const name = $(this).find('.item-name').val()?.trim();
            const hrs = parseFloat($(this).find('.item-hours').val());
            const desc = $(this).find('.item-desc').val() ?? null; // optional desc field if you added it
            if (name && !isNaN(hrs) && hrs > 0) {
                items.push({
                    item_name: name,
                    hours: hrs,
                    description: desc
                });
            }
        });

        // If items exist — build entry from items (sum hours)
        if (items.length > 0) {
            hasEntries = true;
            const totalHours = items.reduce((s, it) => s + parseFloat(it.hours), 0);
            entries.push({
                date: date,
                category_id: catId,
                project_id: null,
                hours: parseFloat(totalHours.toFixed(2)),
                items: items
            });
        } else {
            // No items → fallback to single category input value
            const hours = parseFloat($input.val());
            if (!isNaN(hours) && hours > 0) {
                hasEntries = true;
                entries.push({
                    date: date,
                    category_id: catId,
                    project_id: null,
                    hours: hours
                });
            }
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
        error: function (xhr) {
            unblockUI();
            // Optionally show validation errors returned from server
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                const errs = Object.values(xhr.responseJSON.errors).flat().join('\n');
                alert('Validation errors:\n' + errs);
            } else {
                alert('Error saving timesheets');
            }
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




    function updateAllTotals() {
    let grand = 0;
    let categoryTotals = {};

    // 1️⃣ CATEGORY TIME INPUTS (GENERAL + OTHERS)
    $(".category-time-input").each(function () {
        const hours = parseFloat($(this).val());
        if (isNaN(hours) || hours <= 0) return;

        const card = $(this).closest(".project-card");
        const catName = card.find(".project-header").text().trim();

        categoryTotals[catName] = (categoryTotals[catName] || 0) + hours;
        grand += hours;
    });


    // 2️⃣ PROJECT TIME INPUTS → GROUP BY CATEGORY, NOT BY PROJECT
    $(".project-time-input").each(function () {
        const hours = parseFloat($(this).val());
        if (isNaN(hours) || hours <= 0) return;

        const projectCard = $(this).closest(".project-card");

        // Inside the accordion, category name is the accordion button
        const catHeader = projectCard.closest(".accordion-item")
            .find(".accordion-button")
            .first()
            .text()
            .trim();

        const categoryName = catHeader || "Uncategorized";

        categoryTotals[categoryName] = (categoryTotals[categoryName] || 0) + hours;
        grand += hours;
    });


    // 3️⃣ Render category totals
    let html = "";
    Object.entries(categoryTotals).forEach(([name, total]) => {
        html += `<div><strong>${name}</strong>: ${total.toFixed(2)} hrs</div>`;
    });

    $("#categoryTotalsList").html(html);
    $("#grandTotal").text(grand.toFixed(2));
}

// 🔄 Trigger updates when typing
$(document).on("input", ".category-time-input, .project-time-input, .item-hours", function () {
    updateAllTotals();
});

// Initialize
updateAllTotals();


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

{{-- <div id="floatingCategoryTotals" style="position:fixed; top:150px; left:20px; background:#ffffff; color:#000;
     padding:12px 18px; border-radius:8px; min-width:200px;
     box-shadow:0 4px 10px rgba(0,0,0,0.25); font-size:14px; z-index:9999;">
  <strong>Category Totals</strong>
  <div id="categoryTotalsList" style="margin-top:8px;"></div>
  <hr>
  <strong>Total Today: <span id="grandTotal">0.0</span> hrs</strong>
</div> --}}
<div id="floatingCategoryTotals" style="
        position:fixed;
        bottom:10px;
        left:20px;
        padding:14px 16px;
        min-width:220px;
        background: rgba(255, 255, 255, 0.75);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.45);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        font-size: 13px;
        color:#333;
        z-index:9999;
     ">

  <div style="font-weight:600; font-size:14px; margin-bottom:6px; color:#1d3557;">
    Category Totals
  </div>

  <div id="categoryTotalsList" style="margin-bottom:10px; line-height:1.5;"></div>

  <div style="
        height:1px;
        background: rgba(0,0,0,0.1);
        margin:6px 0 10px 0;">
  </div>

  <div style="
        font-weight:600;
        font-size:14px;
        display:flex;
        justify-content:space-between;
        align-items:center;
        color:#1d3557;">
    Total Today:
    <span id="grandTotal" style="
                padding:3px 10px;
                background:#1d3557;
                color:white;
                border-radius:20px;
                font-size:13px;">
      0.0
    </span>
  </div>

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
    @foreach($categories->whereIn('id',[6,7,8]) as $category)
    <div class="project-card">
      <div class="project-header">{{ $category->name }}</div>

      @if(strtolower($category->name) === 'others')
      @php
      // find existing "Others" entry (no project_id)
      $entry = $timesheets->firstWhere(fn($item) =>
      $item->project_id === null &&
      $item->category_id == $category->id
      );
      $items = $entry && $entry->items ? $entry->items : collect();
      @endphp

      <div class="category-items">
        <div class="mb-2">
          <button type="button" class="btn btn-sm btn-secondary add-item-btn">+ Add Item</button>
        </div>

        {{-- List existing items dynamically --}}
        <div class="custom-items-list">
          @forelse($items as $item)
          <div class="input-group mb-2">
            <input type="text" class="form-control item-name" placeholder="Item name" value="{{ $item->item_name }}" />
            <input type="number" step="0.1" min="0" max="24" class="form-control item-hours" placeholder="Hours"
              value="{{ $item->hours }}" />
            <input type="text" class="form-control item-desc" placeholder="Description (optional)"
              value="{{ $item->description }}" />
            <button class="btn btn-danger remove-item" type="button">&times;</button>
          </div>
          @empty
          {{-- No existing items --}}
          @endforelse
        </div>
      </div>
      @endif

      <div class="time-input-container mt-2">
        @php
        //if (!isset($entry)) {
        $entryCat = $timesheets->firstWhere(fn($item) =>
        $item->project_id === null &&
        $item->category_id == $category->id
        );
        // }
        @endphp
        <input type="number" step="0.1" min="0" max="24" class="form-control category-time-input"
          value="{{ $entryCat ? $entryCat->hours : '' }}" data-date="{{ $selectedDate->format('Y-m-d') }}"
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
        <a type="button" class="btn btn-label-danger" href="{{ route('pms.reports.resource-utilization') }}">Team
          Resource
          Utilization Chart</a>
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
            {{-- Check if this timesheet belongs to "Others" and has items --}}
            @if (strtolower($timesheet->category->name) === 'others' && $timesheet->items->count() > 0)
            @foreach ($timesheet->items as $item)
            <tr>
              <td>{{ $timesheet->category->name }}</td>
              <td>N/A</td>
              <td>{{ number_format($item->hours, 2) }} hrs</td>
              <td>
                <strong>{{ $item->item_name }}</strong>
                @if (!empty($item->description))
                <div class="text-muted small">{{ Str::limit($item->description, 80) }}</div>
                @endif
              </td>
              <td>
                {{-- If you want to allow deleting individual items later, you can add a button here --}}
                {{-- <button class="btn btn-sm btn-primary edit-item" data-timesheet-id="{{ $timesheet->id }}"
                  data-item-id="{{ $item->id }}" data-item-name="{{ $item->item_name }}"
                  data-item-hours="{{ $item->hours }}" data-item-desc="{{ $item->description }}">
                  <i class="fas fa-edit"></i>
                </button> --}}
                {{-- 🗑️ Delete individual item --}}
                <form
                  action="{{ route('pms.timesheets.destroyItem', ['timesheet' => $timesheet->id, 'item' => $item->id]) }}"
                  method="POST" class="d-inline" onsubmit="return confirm('Delete only this item from Others?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Item">
                    <i class="fas fa-trash"></i>
                  </button>
                </form>
              </td>
            </tr>
            @endforeach
            @else
            {{-- Normal timesheet row --}}
            <tr>
              <td>{{ $timesheet->category->name }}</td>
              <td>{{ $timesheet->project ? $timesheet->project->title : 'N/A' }}</td>
              <td>{{ $timesheet->formatted_time }}</td>
              <td>{{ Str::limit($timesheet->description, 50) }}</td>
              <td>
                {{-- <button class="btn btn-sm btn-primary edit-timesheet" data-id="{{ $timesheet->id }}"
                  data-date="{{ $timesheet->date->format('Y-m-d') }}" data-category-id="{{ $timesheet->category_id }}"
                  data-project-id="{{ $timesheet->project_id }}" data-hours="{{ $timesheet->hours }}"
                  data-description="{{ $timesheet->description }}">
                  <i class="fas fa-edit"></i>
                </button> --}}

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
            @endif
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