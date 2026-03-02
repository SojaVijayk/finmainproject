@extends('layouts/layoutMaster')

@section('title', 'Venue Details')
@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}" />

<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/spinkit/spinkit.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />

<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/spinkit/spinkit.css')}}" />



@endsection

@section('page-style')

<style>
  .fc-event.readonly {
    background-color: #e9ecef;
    border-color: #dee2e6;
    color: #6c757d;
    cursor: default;
  }

  #eventForm.view-mode input,
  #eventForm.view-mode select,
  #eventForm.view-mode textarea {
    background-color: #f8f9fa;
    cursor: not-allowed;
  }

  /* Add to your styles */
  #viewEventModal .card {
    height: 100%;
  }

  #viewEventModal .card-header {
    font-weight: bold;
    background-color: #f8f9fa;
  }

  #viewEventModal .badge {
    font-size: 0.9rem;
    margin-left: 5px;
  }

  #viewEventDescription {
    white-space: pre-line;
  }



  .venue-card {
    transition: all 0.3s ease;
  }

  .venue-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  }

  .venue-checkbox:checked+.card {
    border: 2px solid #28a745;
    background-color: #f8fff8;
  }

  /* Fix fade look or semi-transparent toasts */
  .toast {
    opacity: 1 !important;
    z-index: 1060 !important;
    /* Ensure it's above modals */
    background-color: rgba(32, 31, 31, 0.85) !important;
    /* darker background for visibility */
    color: white;
  }

  /* Optional: ensure toast container is positioned above everything */
  #toast-container {
    z-index: 1061 !important;
  }

  .loader-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 0.7);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1050;
  }

  .spinner-border {
    width: 3rem;
    height: 3rem;
  }

  .fc-event-cancelled {
    background-color: #f8d7da !important;
    border-color: #f5c6cb !important;
    color: #e50116 !important;
    text-decoration: line-through;
    opacity: 0.7;
  }

  .fc .fc-day-today {
    background-color: #fff6e5 !important;
    /* soft yellow highlight */
    border: 2px solid #f39c12 !important;
    /* orange border */
  }

  /* Optional: make header date bold */
  .fc .fc-col-header-cell.fc-day-today {
    background: #f39c12 !important;
    color: #fff !important;
    font-weight: bold;
  }
</style>

@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>

<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js')}}"></script>
<script src="{{asset('assets/vendor/libs/block-ui/block-ui.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@6.1.15/index.global.min.js'></script>

@endsection

@section('page-script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
  document.addEventListener('DOMContentLoaded', function() {

var calendarEl1 = document.getElementById('calendarHall');



    var calendar1 = new FullCalendar.Calendar(calendarEl1, {
          schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
        initialView: 'resourceTimelineMonth',
         initialDate: new Date(),  // focus today
  nowIndicator: true,  // load current week by default
        aspectRatio: 2,
        resourceAreaHeaderContent: 'Venues',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'resourceTimelineWeek,resourceTimelineMonth'
        },

        // Load Venues dynamically
        resources: function(fetchInfo, successCallback, failureCallback) {
    fetch("{{ route('venues.list') }}")
        .then(res => res.json())
        .then(data => {
            // Backend returns: [{ id: "1", title: "Hall A" }, …]
            // But if your backend still sends {id, name}, map it:
            let resources = data.map(v => ({
                id: String(v.id),      // cast to string (important)
                title: v.name ?? v.title
            }));
            successCallback(resources);
        })
        .catch(err => failureCallback(err));
},

      events: {
      url: '{{ route('bookings.list') }}',
      method: 'GET'
    },

        eventDidMount: function(info) {
  if (!window.bootstrap) return;

  // Event color
  info.el.style.backgroundColor = '#e74c3c';
  info.el.style.borderColor = '#e74c3c';

  const p = info.event.extendedProps || {};

  // Nicely formatted tooltip content
  const html = `
    <div style="font-size: 13px; line-height: 1.5;">
      <div style="font-weight:600; font-size:14px; margin-bottom:4px; color:#2c3e50;">
        ${info.event.title}
      </div>
      <div><span style="color:#7f8c8d;">📍 Venue:</span> <strong>${p.venue_name ?? '-'}</strong></div>
      <div><span style="color:#7f8c8d;">🕒 Start:</span> <strong>${p.start_hm ?? '-'}</strong></div>
      <div><span style="color:#7f8c8d;">⏰ End:</span> <strong>${p.end_hm ?? '-'}</strong></div>
      <div><span style="color:#7f8c8d;">👥 Participants:</span> <strong>${p.participants ?? 0}</strong></div>
      <div><span style="color:#7f8c8d;">📝 Booked by:</span> <strong>${p.booked_by ?? 'N/A'}</strong></div>
    </div>
  `;

  new bootstrap.Tooltip(info.el, {
    title: html,
    html: true,
    placement: 'top',
    trigger: 'hover',
    container: 'body',
    customClass: 'event-tooltip' // optional custom class for extra CSS
  });
}
    });

    calendar1.render();



    });
</script>
@endsection

@section('content')

<div class="container">
  <div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h2>Venue Booking Availability Calendar</h2>
      <div class="card-header-elements ms-auto">
        <a class="btn btn-primary" href="/calendar">New Booking</a>
      </div>
    </div>
    <div class="card-body">


      <div id="calendarHall"></div>
    </div>
  </div>
</div>





@endsection