@extends('layouts.app')

@section('title', 'Timesheet Calendar')
@section('header', 'Timesheet Calendar')

@section('styles')
<!-- FullCalendar CSS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<style>
    .fc-daygrid-day-frame {
        min-height: 100px;
    }
    .fc-event {
        cursor: pointer;
        font-size: 0.8rem;
        padding: 2px;
    }
    .timesheet-total {
        font-weight: bold;
        margin-top: 5px;
    }
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ $currentMonth->format('F Y') }}</h5>
            <div>
                <a href="{{ route('pms.timesheets.index') }}" class="btn btn-sm btn-secondary me-2">
                    <i class="fas fa-list"></i> Daily View
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

<!-- Entry Details Modal -->
<div class="modal fade" id="entriesModal" tabindex="-1" aria-labelledby="entriesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="entriesModalLabel">Timesheet Entries</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="entriesModalBody">
                Loading...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="viewDayLink" class="btn btn-primary">
                    <i class="fas fa-eye"></i> View Day
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- FullCalendar JS -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var entriesModal = new bootstrap.Modal(document.getElementById('entriesModal'));

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        initialDate: '{{ $currentMonth->format("Y-m-d") }}',
        headerToolbar: false,
        height: 'auto',
        dayMaxEvents: true,
        events: [
            @foreach($timesheets as $date => $entries)
            {
                title: '{{ $entries->sum("hours") }}h',
                start: '{{ $date }}',
                extendedProps: {
                    entries: {!! $entries->map(function($entry) {
                        return [
                            'category' => $entry->category->name,
                            'project' => $entry->project ? $entry->project->title : 'N/A',
                            'hours' => $entry->hours,
                            'description' => $entry->description
                        ];
                    })->toJson() !!}
                },
                backgroundColor: '#3b82f6',
                borderColor: '#3b82f6',
            },
            @endforeach
        ],
        eventClick: function(info) {
            const date = info.event.startStr;
            const entries = info.event.extendedProps.entries;
            
            let html = `<p><strong>Date:</strong> ${new Date(date).toLocaleDateString()}</p>`;
            html += '<table class="table table-sm">';
            html += '<thead><tr><th>Category</th><th>Project</th><th>Hours</th></tr></thead>';
            html += '<tbody>';
            
            entries.forEach(entry => {
                html += `<tr>
                    <td>${entry.category}</td>
                    <td>${entry.project}</td>
                    <td>${entry.hours}h</td>
                </tr>`;
                if (entry.description) {
                    html += `<tr><td colspan="3"><small>${entry.description}</small></td></tr>`;
                }
            });
            
            html += '</tbody></table>';
            
            document.getElementById('entriesModalBody').innerHTML = html;
            document.getElementById('viewDayLink').href = `/pms/timesheets?date=${date}`;
            
            entriesModal.show();
        },
        dayCellContent: function(arg) {
            // Add total hours to each day cell if available
            const dateStr = arg.date.toISOString().split('T')[0];
            const dayEvents = calendar.getEvents().filter(event => 
                event.start.toISOString().split('T')[0] === dateStr
            );
            
            let totalHours = 0;
            dayEvents.forEach(event => {
                totalHours += parseFloat(event.title.replace('h', ''));
            });
            
            const dayNumber = arg.dayNumberText.replace('a', '').replace('A', '');
            
            if (totalHours > 0) {
                return {
                    html: `${dayNumber}<div class="timesheet-total">${totalHours}h</div>`
                };
            }
            
            return dayNumber;
        }
    });

    calendar.render();
});
</script>
@endsection