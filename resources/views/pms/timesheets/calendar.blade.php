@extends('layouts/layoutMaster')

@section('title', 'Task')

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
<!-- FullCalendar JS -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var timesheetModal = new bootstrap.Modal(document.getElementById('timesheetModal'));
        var timesheetForm = document.getElementById('timesheetForm');

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            initialDate: '{{ $currentMonth->format("Y-m-d") }}',
            headerToolbar: false,
            height: 'auto',
            dayMaxEvents: true,
            events: [
                @foreach($timesheets as $date => $entries)
                @foreach($entries as $entry)
                {
                    title: '{{ $entry->category->name }}: {{ $entry->formatted_time }}',
                    start: '{{ $date }}',
                    extendedProps: {
                        entryId: '{{ $entry->id }}',
                        category: '{{ $entry->category->name }}',
                        project: '{{ $entry->project ? $entry->project->title : "N/A" }}',
                        hours: '{{ $entry->hours }}',
                        description: '{{ $entry->description }}'
                    },
                    backgroundColor: getCategoryColor('{{ $entry->category->name }}'),
                    borderColor: getCategoryColor('{{ $entry->category->name }}'),
                },
                @endforeach
                @endforeach
            ],
            dateClick: function(info) {
                // Handle date click (for adding new entries)
                // You can implement this if needed
            },
            eventClick: function(info) {
                // Populate modal with event data
                document.getElementById('timesheetModalLabel').textContent = 'Edit Timesheet Entry';
                document.getElementById('displayDate').value = info.event.startStr;
                document.getElementById('displayCategory').value = info.event.extendedProps.category;
                document.getElementById('displayProject').value = info.event.extendedProps.project;
                document.getElementById('hours').value = info.event.extendedProps.hours;
                document.getElementById('description').value = info.event.extendedProps.description;

                // Set form action
                timesheetForm.action = '/pms/timesheets/' + info.event.extendedProps.entryId;
                timesheetForm.querySelector('input[name="_method"]')?.remove();
                timesheetForm.insertAdjacentHTML('beforeend', '<input type="hidden" name="_method" value="PUT">');

                // Show modal
                {{--  timesheetModal.show();  --}}
            }
        });

        calendar.render();

        function getCategoryColor(category) {
            const colors = {
                'Capacity Building': '#3b82f6',
                'Consulting': '#10b981',
                'Manpower Services': '#f59e0b',
                'Recruitment': '#ef4444',
                'Research & Studies': '#8b5cf6',
                'Business Development': '#ec4899',
                'General CMD/Administration': '#64748b'
            };

            return colors[category] || '#6b7280';
        }
    });
</script>
@endsection
@section('header', 'Timesheet Calendar')

@section('page-style')
<!-- FullCalendar CSS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<style>
  .fc-daygrid-day-frame {
    min-height: 100px;
  }

  .fc-event {
    cursor: pointer;
  }

  .timesheet-entry {
    font-size: 0.8rem;
    margin-bottom: 2px;
  }
</style>
@endsection

@section('content')
<div class="card">
  <div class="card-header">
    <div class="d-flex justify-content-between align-items-center">
      <h5 class="mb-0">
        {{ $currentMonth->format('F Y') }}
      </h5>
      <div>
        <a href="{{ route('pms.timesheets.index') }}" class="btn btn-sm btn-secondary me-2">
          <i class="fas fa-list"></i> Daily View
        </a>
        <a href="{{ route('pms.timesheets.report') }}" class="btn btn-sm btn-info me-2">
          <i class="fas fa-chart-bar"></i> Reports
        </a>
        <a href="{{ route('pms.timesheets.calendar', ['month' => $currentMonth->copy()->subMonth()->format('Y-m')]) }}"
          class="btn btn-sm btn-outline-primary me-2">
          <i class="fas fa-chevron-left"></i>
        </a>
        <a href="{{ route('pms.timesheets.calendar', ['month' => $currentMonth->copy()->addMonth()->format('Y-m')]) }}"
          class="btn btn-sm btn-outline-primary">
          <i class="fas fa-chevron-right"></i>
        </a>
      </div>
    </div>
  </div>
  <div class="card-body">
    <div id="calendar"></div>
  </div>
</div>

<!-- Add/Edit Timesheet Modal -->
<div class="modal fade" id="timesheetModal" tabindex="-1" aria-labelledby="timesheetModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="timesheetForm" method="POST">
        @csrf
        <input type="hidden" name="date" id="modalDate">
        <input type="hidden" name="category_id" id="modalCategoryId">
        <input type="hidden" name="project_id" id="modalProjectId">

        <div class="modal-header">
          <h5 class="modal-title" id="timesheetModalLabel">Add Timesheet Entry</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Date</label>
            <input type="text" class="form-control" id="displayDate" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label">Category</label>
            <input type="text" class="form-control" id="displayCategory" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label">Project</label>
            <input type="text" class="form-control" id="displayProject" readonly>
          </div>
          <div class="mb-3">
            <label for="hours" class="form-label">Hours</label>
            <input type="number" step="0.1" min="0.1" max="24" class="form-control" name="hours" id="hours" required>
          </div>
          <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" name="description" id="description" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection