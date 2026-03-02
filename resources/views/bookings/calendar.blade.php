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
        var calendarEl = document.getElementById('calendar');
        var eventModal = $('#eventModal');
        var eventForm = $('#eventForm');
        var eventId = $('#eventId');
        var title = $('#title');
        var description = $('#description');
        var startDate = $('#start_date');
        var endDate = $('#end_date');
        var eventTypeId = $('#event_type_id');
        var eventModeId = $('#event_mode_id');
        var coordinatorId = $('#coordinator_id');
        var facultyId = $('#faculty_id');
        var participantsCount = $('#participants_count');
        var eventCategory = $('input[name="event_category"]');
        var externalEntity = $('#external_entity');
        var externalEntityGroup = $('#externalEntityGroup');
        var venueTypeId = $('#venue_type_id');
        var venueId = $('#venue_id');
        var venueGroup = $('#venueGroup');
        var externalVenue = $('#external_venue');
        var externalVenueGroup = $('#externalVenueGroup');


                // Add these variables at the top with your other variables
        var viewEventModal = $('#viewEventModal');
        var viewEventTitle = $('#viewEventTitle');
        var viewEventType = $('#viewEventType');
          var viewEventStatus = $('#viewEventStatus');
        var viewEventMode = $('#viewEventMode');
        var viewEventStart = $('#viewEventStart');
        var viewEventEnd = $('#viewEventEnd');
        var viewEventDuration = $('#viewEventDuration');
        var viewEventCoordinators = $('#viewEventCoordinators');
        var viewEventCoordinatorsContainer = $('#viewEventCoordinatorsContainer');
        var viewEventFaculties = $('#viewEventFaculties');
        var viewEventFacultiesContainer = $('#viewEventFacultiesContainer');
        var viewEventParticipants = $('#viewEventParticipants');
        var viewEventVenueType = $('#viewEventVenueType');
        var viewEventVenues = $('#viewEventVenues');
          var viewEventExternalVenues = $('#viewEventExternalVenues');

        var viewEventExternalEntity = $('#viewEventExternalEntity');
        var viewEventExternalEntityContainer = $('#viewEventExternalEntityContainer');
        var viewEventCreator = $('#viewEventCreator');
        var viewEventCreated = $('#viewEventCreated');
        var viewEventDescription = $('#viewEventDescription');
        var viewEventCustomAmenities = $('#viewEventCustomAmenities');

        var availableVenuesContainer = $('#availableVenuesContainer');
var availableVenuesList = $('#availableVenuesList');
var customAmenitiesRequest = $('#custom_amenities_request');
var coordinatorsSelect = $('#coordinators');
var facultiesSelect = $('#faculties');
var venuesSelect = $('#venues');

const $allDaySwitch = $('.switch-input');
  const $startDateInput = $('#start_date');
  const $endDateInput = $('#end_date');


// Update date change handlers
startDate.change(function() {
  const startDateTime = new Date($(this).val());
    const now = new Date();

    if (startDateTime < now) {
        toastr.error('Start time cannot be in the past');
        const currentDateTime = formatDateLocal(now);
        $(this).val(currentDateTime);
    }
    updateAvailableVenues();
});

endDate.change(function() {
   const startDateTime = new Date(startDate.val());
    const endDateTime = new Date($(this).val());
    const now = new Date();

    if (endDateTime <= startDateTime) {
        toastr.error('End time must be after start time');
        const minEndTime = new Date(startDateTime.getTime() + 5 * 60000);
        const minEndDateTime = formatDateLocal(minEndTime);
        $(this).val(minEndDateTime);
    }

    if (endDateTime < now) {
        toastr.error('End time cannot be in the past');
        const minEndTime = new Date(now.getTime() + 5 * 60000);
        const minEndDateTime = formatDateLocal(minEndTime);
        $(this).val(minEndDateTime);
    }
    updateAvailableVenues();
});

function updateAvailableVenuesOLD(callback) {
    if (startDate.val() && endDate.val()) {
        $.ajax({
            url: '/venues/available',
            method: 'GET',
            data: {
                start_date: startDate.val(),
                end_date: endDate.val(),
                exclude_event: eventId.val() // Pass current event ID to exclude it from conflict check
            },
            success: function(venues) {
                venuesSelect.empty();
                availableVenuesList.empty();

                if (venues.length > 0) {
                    venuesSelect.append($('<option>', {
                        value: '',
                        text: 'Select Venue(s)',
                        disabled: true,
                        selected: true
                    }));

                    $.each(venues, function(index, venue) {
                        venuesSelect.append($('<option>', {
                            value: venue.id,
                            text: venue.name
                        }));

                        var amenities = venue.amenities ? venue.amenities.split(',').map(a => a.trim()).join(', ') : 'No amenities specified';

                        availableVenuesList.append(`
                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">${venue.name}</h5>
                                        <p class="card-text">
                                            <strong>Capacity:</strong> ${venue.seating_capacity}<br>
                                            <strong>Amenities:</strong> ${amenities}<br>
                                            <strong>Status:</strong> <span class="badge badge-success">${venue.status}</span>
                                        </p>
                                        <div class="form-check">
                                            <input class="form-check-input venue-checkbox" type="checkbox" value="${venue.id}" id="venue-${venue.id}">
                                            <label class="form-check-label" for="venue-${venue.id}">
                                                Select this venue
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `);
                    });

                    availableVenuesContainer.show();
                } else {
                    availableVenuesContainer.hide();
                    toastr.warning('No venues available for the selected time slot');
                }

                // Execute callback if provided
                if (typeof callback === 'function') {
                    callback();
                }
            },
            error: function(xhr) {
                {{--  if (xhr.responseJSON && xhr.responseJSON.message) {
                   message = xhr.responseJSON.message;
                }
                toastr.error(message);  --}}

                // Optionally, include specific field errors:
                {{--  if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    for (const field in errors) {
                        if (errors[field].length) {
                            message += `<br>${errors[field][0]}`; // show only the first error
                        }
                    }
                }  --}}




                toastr.error('Failed to load available venues End time must be a time after Start time');
                console.error('Error loading venues:', xhr.responseText);

                // Execute callback even if there's an error
                if (typeof callback === 'function') {
                    callback();
                }
            }
        });
    } else if (typeof callback === 'function') {
        callback();
    }
}

function updateAvailableVenues(callback) {
    if (!startDate.val() || !endDate.val()) {
        if (typeof callback === 'function') callback();
        return;
    }

    // Validate time range first
    const start = new Date(startDate.val());
    const end = new Date(endDate.val());

    if (end <= start) {
        availableVenuesContainer.hide();
        toastr.error('End time must be after start time');
        if (typeof callback === 'function') callback();
        return;
    }

    // Show loading state
    availableVenuesList.html(`
        <div class="col-12 text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Checking venue availability...</p>
        </div>
    `);
    availableVenuesContainer.show();

    $.ajax({
        url: '/venues/available',
        method: 'GET',
        data: {
            start_date: startDate.val(),
            end_date: endDate.val(),
            exclude_event: eventId.val() || null
        },
        success: function(venues) {
            venuesSelect.empty();
            availableVenuesList.empty();

            if (venues.length > 0) {
                // Add default option
                venuesSelect.append($('<option>', {
                    value: '',
                    text: 'Select Venue(s)',
                    disabled: true,
                    selected: true
                }));

                // Populate both select dropdown and cards
                $.each(venues, function(index, venue) {
                    // Add to select dropdown
                    venuesSelect.append($('<option>', {
                        value: venue.id,
                        text: venue.name,
                        'data-capacity': venue.seating_capacity
                    }));

                    // Create venue card
                    const amenities = venue.amenities
                        ? venue.amenities.split(',').map(a => a.trim()).join(', ')
                        : 'No amenities specified';

                    const cardHtml = `
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 venue-card" data-venue-id="${venue.id}">
                                <div class="card-body">
                                    <h5 class="card-title">${venue.name}</h5>
                                    <p class="card-text">
                                        <strong>Capacity:</strong> ${venue.seating_capacity}<br>
                                        <strong>Amenities:</strong> ${amenities}<br>
                                        <strong>Status:</strong> <span class="badge bg-success">${venue.status}</span>
                                    </p>
                                    <div class="form-check">
                                        <input class="form-check-input venue-checkbox"
                                               type="checkbox"
                                               value="${venue.id}"
                                               id="venue-${venue.id}"
                                               ${venuesSelect.val()?.includes(venue.id) ? 'checked' : ''}>
                                        <label class="form-check-label" for="venue-${venue.id}">
                                            Select this venue
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                    availableVenuesList.append(cardHtml);
                });

                // Highlight previously selected venues
                const selectedVenues = venuesSelect.val() || [];
                selectedVenues.forEach(venueId => {
                    $(`#venue-${venueId}`).prop('checked', true);
                    $(`.venue-card[data-venue-id="${venueId}"]`).addClass('border-primary');
                });

                availableVenuesContainer.show();
            } else {
                availableVenuesContainer.hide();
                toastr.warning('No venues available for the selected time slot');
            }
        },
        error: function(xhr) {
            let errorMsg = 'Failed to load available venues';

            if (xhr.responseJSON?.message) {
                errorMsg = xhr.responseJSON.message;
            } else if (xhr.status === 422 && xhr.responseJSON?.errors) {
                errorMsg = Object.values(xhr.responseJSON.errors).join('<br>');
            }

            toastr.error(errorMsg);
            availableVenuesContainer.hide();
            console.error('Venue availability error:', xhr.responseText);
        },
        complete: function() {
            if (typeof callback === 'function') callback();
        }
    });
}

// Add event listeners for real-time checking
startDate.on('change.datetimepicker', debounce(updateAvailableVenues, 300));
endDate.on('change.datetimepicker', debounce(updateAvailableVenues, 300));

// Debounce function to prevent excessive API calls
function debounce(func, wait) {
    let timeout;
    return function() {
        const context = this, args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(context, args), wait);
    };
}

// Handle venue selection from cards
$(document).on('change', '.venue-checkbox', function() {
    var selectedVenues = [];
    $('.venue-checkbox:checked').each(function() {
        selectedVenues.push($(this).val());
    });
    venuesSelect.val(selectedVenues).trigger('change');
});

// Initialize select2 for multiple selection
$(document).ready(function() {
  {{--  $('#cancelEventBtn').hide();  --}}


    console.log('Coordinators options:', $('#coordinators option'));
console.log('Faculties options:', $('#faculties option'));
    coordinatorsSelect.select2({
        placeholder: 'Select Coordinator(s)',
        multiple: true,
        dropdownParent: $('#eventModal')
    });

    facultiesSelect.select2({
        placeholder: 'Select Faculty (if applicable)',
        multiple: true,
         dropdownParent: $('#eventModal')
    });

    venuesSelect.select2({
        placeholder: 'Select Venue(s)',
        multiple: true
    });


    loadFormData();

    // Load dashboard sections after page loads
    loadDashboardSections();

    // Hall availability filter buttons
     $('[data-my-events-filter]').click(function() {
        $('[data-my-events-filter]').removeClass('active');
        $(this).addClass('active');

        const filter = $(this).data('my-events-filter');
        if (filter === 'custom') {
            $('#myEventsDatePicker').show();
            const selectedDate = $('#myEventsDatePickerInput').val();
            if (selectedDate) {
                loadMyEvents(filter, selectedDate);
            }
        } else {
            $('#myEventsDatePicker').hide();
            loadMyEvents(filter);
        }
    });

    // My Events date picker change
    $('#myEventsDatePickerInput').change(function() {
        if ($('[data-my-events-filter="custom"]').hasClass('active')) {
            loadMyEvents('custom', $(this).val());
        }
    });

    // Upcoming Events filter buttons
    $('[data-upcoming-filter]').click(function() {
        $('[data-upcoming-filter]').removeClass('active');
        $(this).addClass('active');

        const filter = $(this).data('upcoming-filter');
        if (filter === 'custom') {
            $('#upcomingEventsDatePicker').show();
            const selectedDate = $('#upcomingEventsDatePickerInput').val();
            if (selectedDate) {
                loadUpcomingEvents(filter, selectedDate);
            }
        } else {
            $('#upcomingEventsDatePicker').hide();
            loadUpcomingEvents(filter);
        }
    });

    // Upcoming Events date picker change
    $('#upcomingEventsDatePickerInput').change(function() {
        if ($('[data-upcoming-filter="custom"]').hasClass('active')) {
            loadUpcomingEvents('custom', $(this).val());
        }
    });

    $(document).on('click', '[data-time-filter]', function() {
    $('[data-time-filter]').removeClass('active');
    $(this).addClass('active');

    const filter = $(this).data('time-filter');
    if (filter === 'custom') {
        $('#customDatePicker').show();
        const selectedDate = $('#availabilityDatePicker').val();
        if (selectedDate) {
            loadHallAvailability(filter, selectedDate);
        }
    } else {
        $('#customDatePicker').hide();
        loadHallAvailability(filter);
    }
});

// Handle date picker change for hall availability
$('#availabilityDatePicker').change(function() {
    if ($('[data-time-filter="custom"]').hasClass('active')) {
        loadHallAvailability('custom', $(this).val());
    }
});




    $allDaySwitch.on('change', function() {
    if ($(this).is(':checked')) {
      // When "All Day" is checked - use existing dates but set to midnight/23:59
      const startDate = new Date($startDateInput.val());
      const endDate = new Date($endDateInput.val());

      // If no date selected yet, use current date
      if (isNaN(startDate.getTime())) {
        startDate = new Date();
      }
      if (isNaN(endDate.getTime())) {
        endDate = new Date();
      }

      // Set start date to 00:00 of selected date
      startDate.setHours(0, 0, 0, 0);
      $startDateInput.val(formatDateTimeLocal(startDate));

      // Set end date to 23:59 of selected date
      endDate.setHours(23, 59, 0, 0);
      $endDateInput.val(formatDateTimeLocal(endDate));

      // Disable the inputs
      {{--  $startDateInput.prop('disabled', true);
      $endDateInput.prop('disabled', true);  --}}
    } else {
      // When "All Day" is unchecked
      {{--  $startDateInput.prop('disabled', false);
      $endDateInput.prop('disabled', false);  --}}
    }
  });

  // Initialize if switch is checked by default
  if ($allDaySwitch.is(':checked')) {
    $allDaySwitch.trigger('change');
  }


// Helper function to format date as datetime-local input value
function formatDateTimeLocal(date) {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');

  return `${year}-${month}-${day}T${hours}:${minutes}`;
}



});

// Update event form in modal
function populateEventForm(event) {
    resetForm();
    eventModal.modal('show');
    eventId.val(event.id);
    title.val(event.title);
    description.val(event.description);
    startDate.val(event.start_date);
    endDate.val(event.end_date);

    //set all day flag

    if (isFullDay(new Date(event.start_date), new Date(event.end_date))) {
        $allDaySwitch.prop('checked', true);
        {{--  $startDateInput.prop('disabled', true);
        $endDateInput.prop('disabled', true);  --}}
    }
    else{
        $allDaySwitch.prop('checked', false);
        {{--  $startDateInput.prop('disabled', false);
        $endDateInput.prop('disabled', false);  --}}
    }

    // Set other fields
    eventTypeId.val(event.event_type_id).trigger('change');
    eventModeId.val(event.event_mode_id);

    // Set minimum time for start and end dates
    const now = new Date();
    const currentDateTime = formatDateLocal(now);
    const minEndTime = new Date(now.getTime() + 5 * 60000); // 30 minutes from now
    const minEndDateTime = formatDateLocal(minEndTime);



    // Set min attribute for start and end datetime inputs
    startDate.attr('min', currentDateTime);
    endDate.attr('min', minEndDateTime);

      // Set multiple coordinators after a small delay to ensure Select2 is ready
        setTimeout(function() {
            if (event.coordinators && event.coordinators.length > 0) {
                var coordinatorIds = event.coordinators.map(c => c.id);
                coordinatorsSelect.val(coordinatorIds).trigger('change');
            }
        }, 100);

        // Set multiple faculties after a small delay
        setTimeout(function() {
            if (event.faculties && event.faculties.length > 0) {
                var facultyIds = event.faculties.map(f => f.id);
                facultiesSelect.val(facultyIds).trigger('change');
            }
        }, 100);

    participantsCount.val(event.participants_count);
    $('input[name="event_category"][value="' + event.event_category + '"]').prop('checked', true).trigger('change');
    externalEntity.val(event.external_entity);
    venueTypeId.val(event.venue_type_id).trigger('change');


      // Set multiple venues - this is the key change
    if (event.venues && event.venues.length > 0) {
        // First load all available venues
        updateAvailableVenues(function() {
            // After venues are loaded, select the ones for this event
            var venueIds = event.venues.map(v => v.id);
            venuesSelect.val(venueIds).trigger('change');

            // Check the checkboxes for the selected venues
            venueIds.forEach(function(venueId) {
                $('#venue-' + venueId).prop('checked', true);
            });
        });
    }

    customAmenitiesRequest.val(event.custom_amenities_request);
    externalVenue.val(event.external_venue);
    if(event.status == 1){
        $('#cancelEventBtn').show();
    }
    else{
      $('#cancelEventBtn').hide();
    }
    // Disable form if user can't edit
    if (!event.can_edit) {
        eventForm.addClass('view-mode');
        $('#eventModalLabel').text('Event Details - View Only');
        $('.modal-footer .btn-primary').hide();
        $('#cancelEventBtn').hide();
    } else {
        eventForm.removeClass('view-mode');
        $('#eventModalLabel').text('Edit Event');
        $('.modal-footer .btn-primary').show();
        $('#cancelEventBtn').show().off('click').on('click', function() {
            cancelEvent(event.id);
        });

    }
}

function formatDateLocal(date) {
    const pad = (n) => n.toString().padStart(2, '0');

    const year = date.getFullYear();
    const month = pad(date.getMonth() + 1); // getMonth is 0-indexed
    const day = pad(date.getDate());
    const hours = pad(date.getHours());
    const minutes = pad(date.getMinutes());

    return `${year}-${month}-${day}T${hours}:${minutes}`;
}

        // Initialize calendar
        var calendar = new FullCalendar.Calendar(calendarEl, {
            {{--  plugins: [ 'dayGrid', 'timeGrid', 'interaction' ],  --}}
            {{--  plugins: [ FullCalendar.DayGrid, FullCalendar.TimeGrid, FullCalendar.Interaction ],  --}}
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
            defaultView: 'dayGridMonth',
            editable: true,
            selectable: true,
            eventLimit: true,
            selectAllow: function(selectInfo) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            return selectInfo.start >= today;
          },
            {{--  events: '/events',  --}}
            events: {
                url: '/events',
                extraParams: {
                    canEdit: true
                },
                failure: function(error) {
                toastr.error('Failed to load events');
                console.error('Error loading events:', error);
            }
            },


            select: function(info) {
                resetForm();
                eventModal.modal('show');
                {{--  startDate.val(info.startStr.substring(0, 16));
                endDate.val(info.endStr.substring(0, 16));  --}}

                //old code
                {{--  const start = info.start;
                const end = info.end || info.start;
                const now = new Date();
                start.setHours(now.getHours(), now.getMinutes(), 0, 0);
                const startLocal = formatDateLocal(new Date(start));
                const endLocal = formatDateLocal(new Date(end));

                // Create end date/time 30 minutes later
                const endDateObj = new Date(start.getTime() + 30 * 60000); // 30 mins = 1800000 ms
                const endLocalTime = formatDateLocal(endDateObj);
                endDate.val(endLocal);

                    startDate.val(startLocal);
                    endDate.val(endLocalTime);
                   startDate.trigger('change');  --}}

                   const now = new Date();
            const currentDateTime = formatDateLocal(now);
            const minEndTime = new Date(now.getTime() + 5 * 60000); // 30 minutes from now
            const minEndDateTime = formatDateLocal(minEndTime);

            // Set min attribute for start and end datetime inputs
            startDate.attr('min', currentDateTime);
            endDate.attr('min', minEndDateTime);

            const start = info.start;
            const end = info.end || info.start;
            start.setHours(now.getHours(), now.getMinutes(), 0, 0);
            const startLocal = formatDateLocal(new Date(start));
            const endLocal = formatDateLocal(new Date(end));

            // Create end date/time 30 minutes later
            const endDateObj = new Date(start.getTime() + 5 * 60000);
            const endLocalTime = formatDateLocal(endDateObj);

            // Ensure selected time is not in the past
            if (new Date(startLocal) < now) {
                startDate.val(currentDateTime);
            } else {
                startDate.val(startLocal);
            }

            if (new Date(endLocalTime) < minEndTime) {
                endDate.val(minEndDateTime);
            } else {
                endDate.val(endLocalTime);
            }

            startDate.trigger('change');
                    },


          eventClick: function(info) {
          $.get('/events/' + info.event.id, function(event) {
            console.log(event.can_edit);
            if (event.can_edit && event.status == '1') {
                populateEventForm(event);
            } else {
                // Populate view modal
                viewEventTitle.text(event.title);
                viewEventType.text(event.event_type);
                viewEventStatus.text((event.status == '1' ? "Booked" : "Cancelled"));

                viewEventMode.text(event.event_mode);
                viewEventStart.text(new Date(event.start_date).toLocaleString());
                viewEventEnd.text(new Date(event.end_date).toLocaleString());

                // Calculate duration
                var start = new Date(event.start_date);
                var end = new Date(event.end_date);
                var diff = end - start;
                var hours = Math.floor(diff / (1000 * 60 * 60));
                var minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                viewEventDuration.text(hours + " hours " + minutes + " minutes");
                if (event.coordinators && event.coordinators.length > 0) {
                viewEventCoordinators.text(event.coordinators.map(c => c.name).join(', '));
                viewEventCoordinatorsContainer.show();
                }else {
                    {{--  viewEventCoordinatorsContainer.hide();  --}}
                }

                if (event.faculties && event.faculties.length > 0) {
                    viewEventFaculties.text(event.faculties.map(f => f.name).join(', '));
                    viewEventFacultiesContainer.show();
                } else {
                    {{--  viewEventFacultiesContainer.hide();  --}}
                }

                viewEventParticipants.text(event.participants_count);
                viewEventVenueType.text(event.venue_type);

                viewEventVenues.text(event.venues.map(v => v.name).join(', '));
                viewEventCustomAmenities.text(event.custom_amenities_request || 'None');
                if(event.external_venue){
                  viewEventExternalVenues.text(event.external_venue+' -(External)');
                }
                if (event.external_entity) {
                    viewEventExternalEntity.text(event.external_entity);
                    viewEventExternalEntityContainer.show();
                } else {
                    viewEventExternalEntityContainer.hide();
                }

                viewEventCreator.text(event.creator.name);
                viewEventCreated.text(event.created_at);
                viewEventDescription.text(event.description || 'No description provided');

                viewEventModal.modal('show');
            }
        }).fail(function() {
            toastr.error('Failed to load event details');
        });
      },
  eventDidMount: function(info) {
    {{--  alert(info.event.extendedProps.status);  --}}
    var startDateTime = info.event.start.toLocaleString();
    var endDateTime = info.event.end ? info.event.end.toLocaleString() : '';
     var status = info.event.extendedProps.status == '1' ? 'Booked' : 'Cancelled';

    // Calculate duration for tooltip
    var duration = '';
    if (info.event.end) {
        var diff = info.event.end - info.event.start;
        var hours = Math.floor(diff / (1000 * 60 * 60));
        var minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        duration = `<strong>Duration:</strong> ${hours}h ${minutes}m<br>`;
    }
    if (info.event.extendedProps.status == '2') {
        $(info.el).addClass('fc-event-cancelled');
        {{--  $(info.el).find('.fc-event-title').prepend('[CANCELLED] ');  --}}
    }
    else  {
        $(info.el).removeClass('fc-event-cancelled');
        {{--  $(info.el).find('.fc-event-title').prepend('[CANCELLED] ');  --}}
    }
  $(info.el).tooltip({
        title: `
        <strong>Status : ${status}</strong><br>
            <strong>${info.event.title}</strong><br>
            <strong>Venues:</strong> ${info.event.extendedProps.venues.join(', ')}<br>
            <strong>Type:</strong> ${info.event.extendedProps.eventType}<br>
            <strong>Mode:</strong> ${info.event.extendedProps.eventMode}<br>
            <strong>Start:</strong> ${startDateTime}<br>
            <strong>End:</strong> ${endDateTime}<br>
            ${duration}
            <strong>Coordinators:</strong> ${info.event.extendedProps.coordinators.join(', ')}<br>
            ${info.event.extendedProps.faculties.length ? `<strong>Faculties:</strong> ${info.event.extendedProps.faculties.join(', ')}<br>` : ''}

            ${info.event.extendedProps.custom_amenities_request ? `<strong>Additional Amenities:</strong> ${info.event.extendedProps.custom_amenities_request}<br>` : ''}
            <strong>Participants:</strong> ${info.event.extendedProps.participants_count}
        `,
        html: true,
        placement: 'top',
        container: 'body'
    });
    if (!info.event.extendedProps.canEdit) {
        $(info.el).addClass('fc-event-readonly');
    }
},
       eventRender: function(info) {
    // Format dates for tooltip

    var startDateTime = info.event.start.toLocaleString();
    var endDateTime = info.event.end ? info.event.end.toLocaleString() : '';
     var status = info.event.extendedProps.status =='1' ? 'Booked' : 'Cancelled';

    // Calculate duration for tooltip
    var duration = '';
    if (info.event.end) {
        var diff = info.event.end - info.event.start;
        var hours = Math.floor(diff / (1000 * 60 * 60));
        var minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        duration = `<strong>Duration:</strong> ${hours}h ${minutes}m<br>`;
    }
    if (info.event.extendedProps.status == '2') {
        $(info.el).addClass('fc-event-cancelled');
        {{--  $(info.el).find('.fc-event-title').prepend('[CANCELLED] ');  --}}
    }
    else  {
        $(info.el).removeClass('fc-event-cancelled');
        {{--  $(info.el).find('.fc-event-title').prepend('[CANCELLED] ');  --}}
    }

    $(info.el).tooltip({
        title: `
         <strong>Status : ${status}</strong><br>
            <strong>${info.event.title}</strong><br>
            <strong>Type:</strong> ${info.event.extendedProps.eventType}<br>
            <strong>Mode:</strong> ${info.event.extendedProps.eventMode}<br>
            <strong>Start:</strong> ${startDateTime}<br>
            <strong>End:</strong> ${endDateTime}<br>
            ${duration}
            <strong>Coordinators:</strong> ${info.event.extendedProps.coordinators.join(', ')}<br>
            ${info.event.extendedProps.faculties.length ? `<strong>Faculties:</strong> ${info.event.extendedProps.faculties.join(', ')}<br>` : ''}
            <strong>Venues:</strong> ${info.event.extendedProps.venues.join(', ')}<br>
            ${info.event.extendedProps.custom_amenities_request ? `<strong>Additional Amenities:</strong> ${info.event.extendedProps.custom_amenities_request}<br>` : ''}
            <strong>Participants:</strong> ${info.event.extendedProps.participants_count}
        `,
        html: true,
        placement: 'top',
        container: 'body'
    });

    // Style based on edit permissions
    if (!info.event.extendedProps.canEdit) {
        $(info.el).addClass('fc-event-readonly');
    }
}






        });

        calendar.render();

        // Load form data
      function loadFormData() {
    $.ajax({
        url: '/events/form-data',
        method: 'GET',
        success: function(data) {
            // Clear existing options
            eventTypeId.empty().append('<option value="">Select Event Type</option>');
            eventModeId.empty().append('<option value="">Select Event Mode</option>');
            venueTypeId.empty().append('<option value="">Select Venue Type</option>');
            coordinatorsSelect.empty();
            facultiesSelect.empty();
            venuesSelect.empty().append('<option value="">Select Venue(s)</option>');

            // Populate event types
            data.eventTypes.forEach(function(type) {
                eventTypeId.append($('<option>', {
                    value: type.id,
                    text: type.name
                }));
            });

            // Populate event modes
            data.eventModes.forEach(function(mode) {
                eventModeId.append($('<option>', {
                    value: mode.id,
                    text: mode.name
                }));
            });

            // Populate venue types
            data.venueTypes.forEach(function(type) {
                venueTypeId.append($('<option>', {
                    value: type.id,
                    text: type.name
                }));
            });

            // Populate venues
            data.venues.forEach(function(venue) {
                venuesSelect.append($('<option>', {
                    value: venue.id,
                    text: venue.name
                }));
            });
            // Clear and populate coordinators
            coordinatorsSelect.empty();
            data.users.forEach(function(user) {
                coordinatorsSelect.append(new Option(user.name, user.id));
            });
            coordinatorsSelect.trigger('change');

            // Clear and populate faculties
            facultiesSelect.empty();
            data.faculties.forEach(function(user) {
                facultiesSelect.append(new Option(user.name, user.id));
            });
            facultiesSelect.trigger('change');
            {{--  // Populate users (coordinators and faculty)
            data.users.forEach(function(user) {
                coordinatorsSelect.append($('<option>', {
                    value: user.id,
                    text: user.name
                }));

                facultiesSelect.append($('<option>', {
                    value: user.id,
                    text: user.name
                }));
            });  --}}

            // Initialize Select2 after options are loaded
            coordinatorsSelect.select2({
                placeholder: 'Select Coordinator(s)',
                multiple: true,
                 dropdownParent: $('#eventModal')
            });

             facultiesSelect.select2({
                placeholder: 'Select Faculty (if applicable)',
                multiple: true,
                 dropdownParent: $('#eventModal')
            });

            venuesSelect.select2({
                placeholder: 'Select Venue(s)',
                multiple: true
            });
        },
        error: function(xhr) {
            toastr.error('Failed to load form data');
            console.error('Error loading form data:', xhr.responseText);
        }
    });
}

        // Event category toggle
        eventCategory.change(function() {
            if ($(this).val() === 'External') {
                externalEntityGroup.show();
            } else {
                externalEntityGroup.hide();
                externalEntity.val('');
            }
        });

        // Venue type toggle
        venueTypeId.change(function() {
            var selectedValue = $(this).val();

            venueGroup.hide();
            externalVenueGroup.hide();
            venueId.val('');
            externalVenue.val('');

            if (selectedValue == 1) { // Inhouse
                venueGroup.show();
            } else if (selectedValue == 2) { // External
                externalVenueGroup.show();
            } else if (selectedValue == 3) { // Both
                venueGroup.show();
                externalVenueGroup.show();
            }
        });

        // Form submission
        eventForm.submit(function(e) {
            e.preventDefault();
            $('.loader-overlay').show();
            var formData = $(this).serialize();
            var url = eventId.val() ? '/events/' + eventId.val() : '/events';
            var method = eventId.val() ? 'PUT' : 'POST';

            $.ajax({
                url: url,
                type: method,
                data: formData,
                success: function(response) {
                    calendar.refetchEvents();
                    eventModal.modal('hide');
                    toastr.success('Event saved successfully');
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors;
                       $.each(errors, function(key, value) {
                        Swal.fire({
                    title: value[0],
                    customClass: {
                      confirmButton: 'btn btn-warning'
                    },
                    buttonsStyling: false
                  });

                    });
                     updateAvailableVenues();
                     {{--  if (xhr.responseJSON && xhr.responseJSON.message) {
                   message = xhr.responseJSON.message;
                }
                     if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    for (const field in errors) {
                        if (errors[field].length) {
                            message += `<br>${errors[field][0]}`; // show only the first error
                        }
                    }
                }
                    Swal.fire({
                    title: message,
                    customClass: {
                      confirmButton: 'btn btn-warning'
                    },
                    buttonsStyling: false
                  });  --}}
                    {{--  $.each(errors, function(key, value) {
                        toastr.error(value[0]);
                    });  --}}
                },
                complete: function() {
            // Hide loader when request is complete
            $('.loader-overlay').hide();
        }
            });
        });

        // Reset form
  function resetForm() {
    eventForm.trigger('reset');
    eventId.val('');
    externalEntityGroup.hide();
    venueGroup.hide();
    externalVenueGroup.hide();
    availableVenuesContainer.hide();

    // Reset Select2 dropdowns
    coordinatorsSelect.val(null).trigger('change');
    facultiesSelect.val(null).trigger('change');
    venuesSelect.val(null).trigger('change');

    // Clear available venues list
    availableVenuesList.empty();
}

function isFullDay(startDate, endDate) {
    if (!startDate || !endDate) return false;

    return (
      startDate.getHours() === 0 &&
      startDate.getMinutes() === 0 &&
      endDate.getHours() === 23 &&
      endDate.getMinutes() === 59 &&
      startDate.toDateString() === endDate.toDateString()
    );
  }

function loadDashboardSections() {
    loadHallAvailability('now');
    loadMyEvents('upcoming');
    loadUpcomingEvents('upcoming');
}

// Hall Availability
function loadHallAvailability(timeFilter, customDate = null) {
    let url = '/hall-availability?filter=' + timeFilter;
    if (customDate) {
        url += '&date=' + customDate;
    }

    $('#hallAvailabilityContent').html(loadingSpinner());

    $.get(url, function(data) {
      $('#hallAvailabilityContent').empty();
        let html = '';
      console.log('Avialable'+data.availableVenues);
        // Available venues section
        if (data.availableVenues && data.availableVenues.length > 0) {
            html += '<h6 class="mt-0">Available Venues</h6>';
            html += '<div class="list-group mb-3">';
            data.availableVenues.forEach(venue => {
                html += `
                <div class="list-group-item">
                    <div class="d-flex justify-content-between">
                        <h6 class="mb-1">${venue.name}</h6>
                        <span class="badge bg-success">Available</span>
                    </div>
                    <small>Capacity: ${venue.seating_capacity}</small>
                    <div class="mt-1">
                        <small class="text-muted">Amenities: ${venue.amenities || 'None'}</small>
                    </div>
                </div>
                `;
            });
            html += '</div>';
        } else {
            html += '<p class="text-muted">No venues available for the selected time.</p>';
        }

        // Booked venues section
         console.log('booked'+data.bookedVenues.length);
        if (data.bookedVenues && data.bookedVenues.length > 0) {
            html += '<h6 class="mt-3">Booked Venues</h6>';
            html += '<div class="list-group">';
            data.bookedVenues.forEach(event => {
                html += `
                <div class="list-group-item">
                    <div class="d-flex justify-content-between">
                        <h6 class="mb-1">${event.venue_name}</h6>
                        <span class="badge bg-danger">Booked</span>
                    </div>
                    <small>Event: ${event.title}</small>
                    <div class="mt-1">
                        <small class="text-muted">
                            ${new Date(event.start_date).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})} -
                            ${new Date(event.end_date).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                        </small>
                    </div>
                </div>
                `;
            });
            html += '</div>';
        }

        $('#hallAvailabilityContent').html(html);
    }).fail(function() {
        $('#hallAvailabilityContent').html('<p class="text-danger">Error loading hall availability data</p>');
    });
}
// My Events
function loadMyEvents(timeFilter, customDate = null) {
    let url = '/my-events?filter=' + timeFilter;
    if (customDate) {
        url += '&date=' + customDate;
    }

    $('#myEventsContent').html(loadingSpinner());

    $.get(url, function(data) {
        let html = '';
        if (data.length > 0) {
            html += '<div class="list-group">';
            data.forEach(event => {
                html += eventListItem(event);
            });
            html += '</div>';
        } else {
            html = '<p class="text-muted">No events found.</p>';
        }
        $('#myEventsContent').html(html);
    });
}

// Upcoming Events
function loadUpcomingEvents(timeFilter, customDate = null) {
    let url = '/upcoming-events?filter=' + timeFilter;
    if (customDate) {
        url += '&date=' + customDate;
    }

    $('#upcomingEventsContent').html(loadingSpinner());

    $.get(url, function(data) {
        let html = '';
        if (data.length > 0) {
            html += '<div class="list-group">';
            data.forEach(event => {
                html += eventListItem(event, true);
            });
            html += '</div>';
        } else {
            html = '<p class="text-muted">No events found.</p>';
        }
        $('#upcomingEventsContent').html(html);
    });
}

// Helper functions
function loadingSpinner() {
    return `
    <div class="text-center py-4">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    `;
}

function venueListItem(venue, status, badgeClass) {
    return `
    <div class="list-group-item">
        <div class="d-flex justify-content-between">
            <h6 class="mb-1">${venue.name}</h6>
            <span class="badge bg-${badgeClass}">${status}</span>
        </div>
        <small>Capacity: ${venue.seating_capacity}</small>
        <div class="mt-1">
            <small class="text-muted">Amenities: ${venue.amenities || 'None'}</small>
        </div>
    </div>
    `;
}

function eventListItem(event, showCoordinators = false) {
    const startTime = new Date(event.start_date).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    const endTime = new Date(event.end_date).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});

    let html = `
    <a href="#" class="list-group-item list-group-item-action view-event" data-id="${event.id}">
        <div class="d-flex justify-content-between">
            <h6 class="mb-1">${event.title}</h6>
            <span class="badge" style="background-color: ${event.color}">${event.event_type}</span>
        </div>
        <small>${event.venue_names.join(', ')}</small>
        <div class="mt-1">
            <small class="text-muted">
                ${new Date(event.start_date).toLocaleDateString()} •
                ${startTime} - ${endTime}
            </small>
        </div>
    `;

    if (showCoordinators && event.coordinator_names && event.coordinator_names.length > 0) {
        html += `
        <div class="mt-1">
            <small>Coordinator: ${event.coordinator_names.join(', ')}</small>
        </div>
        `;
    }

    html += `</a>`;
    return html;
}
// Add cancelEvent function
function cancelEvent(eventId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This will cancel the event and notify all participants",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, cancel it!'
    }).then((result) => {
        if (result.isConfirmed) {
           $('.loader-overlay').show();
            $.ajax({
                url: '/events/' + eventId + '/cancel',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'DELETE'
                },
                success: function() {
                    calendar.refetchEvents();
                    eventModal.modal('hide');
                    toastr.success('Event cancelled successfully');
                     {{--  $('.loader-overlay').hide();  --}}
                },
                error: function(xhr) {
                   {{--  $('.loader-overlay').hide();  --}}
                    toastr.error(xhr.responseJSON?.error || 'Failed to cancel event');
                },
                 complete: function() {
            // Hide loader when request is complete
            $('.loader-overlay').hide();
        }
            });
        }
    });
}



var calendarEl1 = document.getElementById('calendarHall');



    var calendar1 = new FullCalendar.Calendar(calendarEl1, {
          schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
        initialView: 'resourceTimelineMonth',   // load current week by default
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
      info.el.style.backgroundColor = '#e74c3c';
    info.el.style.borderColor = '#e74c3c';

      const p = info.event.extendedProps || {};
      const html = `
        <div>
          <div><strong>${info.event.title}</strong></div>
          <div>Venue: ${p.venue_name ?? ''}</div>
          <div>Start: ${p.start_hm ?? ''}</div>
          <div>End: ${p.end_hm ?? ''}</div>
          <div>Participants: ${p.participants ?? 0}</div>
          <div>Booked by: ${p.booked_by ?? 'N/A'}</div>
        </div>
      `;
      new bootstrap.Tooltip(info.el, {
        title: html,
        html: true,
        placement: 'top',
        trigger: 'hover',
        container: 'body'
      });
    }
    });

    calendar1.render();



    });
</script>
@endsection

@section('content')

<div class="container">
  <div class="row">
    <!-- Left Column - Dashboard Sections -->
    <div class="col-md-4">


      <!-- Hall Availability Card -->
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5>Hall Availability</h5>
          <div class="btn-group">
            <button class="btn btn-sm btn-outline-secondary active" data-time-filter="now">Now</button>
            <button class="btn btn-sm btn-outline-secondary" data-time-filter="today">Today</button>
            <button class="btn btn-sm btn-outline-secondary" data-time-filter="custom">Custom Date</button>
          </div>
        </div>


        <div class="card-body">
          <div id="customDatePicker" class="mb-3" style="display: none;">
            <input type="date" class="form-control" id="availabilityDatePicker">
          </div>
          <div id="hallAvailabilityContent">
            <!-- Content will be loaded via AJAX -->
            <div class="text-center py-4">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>
          </div>
        </div>
      </div>



      <!-- My Events Card -->
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5>My Events</h5>
          <div class="btn-group">
            <button class="btn btn-sm btn-outline-secondary active" data-my-events-filter="upcoming">Upcoming</button>
            {{-- <button class="btn btn-sm btn-outline-secondary" data-my-events-filter="today">Today</button>
            <button class="btn btn-sm btn-outline-secondary" data-my-events-filter="custom">Custom Date</button> --}}
          </div>
        </div>
        <div class="card-body">
          <div id="myEventsDatePicker" class="mb-3" style="display: none;">
            <input type="date" class="form-control" id="myEventsDatePickerInput">
          </div>
          <div id="myEventsContent">
            <!-- Content will be loaded via AJAX -->
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          Event Type Legend
        </div>
        <div class="card-body mt-2">
          <div class="d-flex flex-wrap gap-2">
            <div><span class="badge mt-2" style="background-color: #007bff;">Workshop</span></div>
            <div><span class="badge mt-2" style="background-color: #28a745;">Seminar</span></div>
            <div><span class="badge mt-2" style="background-color: #ffc107; color: #000;">Meeting</span></div>
            <div><span class="badge mt-2" style="background-color: #17a2b8;">Training</span></div>
            <div><span class="badge mt-2" style="background-color: #6f42c1;">Webinar</span></div>
            <div><span class="badge mt-2" style="background-color: #fd7e14;">Conference</span></div>
            <div><span class="badge mt-2" style="background-color: #dc3545;">Recruitment</span></div>
            <div><span class="badge mt-2" style="background-color: #6610f2;">Discussion</span></div>
            {{-- <div><span class="badge mt-2" style="background-color: #6c757d;">Read-only</span></div> --}}

          </div>
        </div>
      </div>

    </div>

    <!-- Right Column - Calendar -->
    <div class="col-md-8">
      <div class="card">
        <div class="card-header">{{ __('Event Calendar') }}</div>

        <div class="card-body">
          {{-- <div class="alert alert-warning m-2" role="alert">
            🚧 This is a <strong>demo version</strong> of the system. The live platform will be launched on <strong>01
              July 2025</strong>.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
            </button>
          </div> --}}
          <div id="calendar"></div>
        </div>
      </div>


      <!-- Upcoming Events Card -->
      <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5>Upcoming Events</h5>
          <div class="btn-group">
            <button class="btn btn-sm btn-outline-secondary active" data-upcoming-filter="upcoming">Upcoming</button>
            <button class="btn btn-sm btn-outline-secondary" data-upcoming-filter="today">Today</button>
            <button class="btn btn-sm btn-outline-secondary" data-upcoming-filter="custom">Custom Date</button>
          </div>
        </div>
        <div class="card-body">
          <div id="upcomingEventsDatePicker" class="mb-3" style="display: none;">
            <input type="date" class="form-control" id="upcomingEventsDatePickerInput">
          </div>
          <div id="upcomingEventsContent">
            <!-- Content will be loaded via AJAX -->
          </div>
        </div>
      </div>

    </div>
  </div>

  <div class="container">
    <div class="card mt-4">
      <div class="card-body">
        <h2>Venue Booking Avialability Calendar</h2>

        <div id="calendarHall"></div>
      </div>
    </div>
  </div>

</div>

<!-- Event Modal -->
<div class="modal fade" id="eventModal" tabindex="-1" role="dialog" aria-labelledby="eventModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="loader-overlay" style="display: none;">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>
      <div class="modal-header">
        <h5 class="modal-title" id="eventModalLabel">Hall Booking</h5>
        {{-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button> --}}
      </div>
      <div class="modal-body">
        <form id="eventForm">
          @csrf
          <input type="hidden" id="eventId">
          <div class="form-group mt-2">
            <label for="title">Event Title *</label>
            <input type="text" class="form-control" id="title" name="title" required>
          </div>
          <div class="form-group mt-2">
            <label for="description">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
          </div>
          <div class="row">
            <div class="col-md-4">

              <div class="form-group mt-2">
                <label for="start_date">Start Date & Time *</label>
                <input type="datetime-local" class="form-control" id="start_date" name="start_date" required>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group mt-2">
                <label for="end_date">End Date & Time *</label>
                <input type="datetime-local" class="form-control" id="end_date" name="end_date" required>
              </div>
            </div>
            <div class="col-md-3">
              <label class="switch switch-primary mt-4">
                <input type="checkbox" class="switch-input" />
                <span class="switch-toggle-slider">
                  <span class="switch-on">
                    <i class="icon-base ti tabler-check"></i>
                  </span>
                  <span class="switch-off">
                    <i class="icon-base ti tabler-x"></i>
                  </span>
                </span>
                <span class="switch-label">All Day</span>
              </label>
            </div>



          </div>
          <div class="row">
            <div class="col-md-3">
              <div class="form-group mt-2">
                <label for="event_type_id">Event Type *</label>
                <select class="form-control" id="event_type_id" name="event_type_id" required>
                  <option value="">Select Event Type</option>
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group mt-2">
                <label for="event_mode_id">Event Mode *</label>
                <select class="form-control" id="event_mode_id" name="event_mode_id" required>
                  <option value="">Select Event Mode</option>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group mt-2">
                {{-- <label>Hosted By *</label> --}}
                <small class="fw-medium d-block">Hosted By *</small>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="event_category" id="cmd" value="CMD" checked>
                  <label class="form-check-label" for="cmd">
                    CMD
                  </label>
                </div>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="event_category" id="external" value="External">
                  <label class="form-check-label" for="external">
                    External
                  </label>
                </div>
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group mt-2">
                {{-- <label for="participants_count">Participants Count *</label> --}}
                <small class="fw-medium d-block">Participants *</small>
                <input type="number" class="form-control" id="participants_count" name="participants_count" min="0"
                  required>
              </div>
            </div>


          </div>
          <div class="row">
            <div class="col-md-4">
              <div class="form-group mt-2">
                <label for="coordinators">Coordinators *</label>
                <select class="form-control select2" id="coordinators" name="coordinators[]" multiple="multiple"
                  required>
                  <!-- Options will be loaded dynamically -->
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <!-- Faculty Field -->
              <div class="form-group mt-2">
                <label for="faculties">Faculty (if applicable)</label>
                <select class="form-control select2" id="faculties" name="faculties[]" multiple="multiple">
                  <!-- Options will be loaded dynamically -->
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group mt-2">
                <label for="venue_type_id">Venue Type *</label>
                <select class="form-control" id="venue_type_id" name="venue_type_id" required>
                  <option value="">Select Venue Type</option>
                </select>
              </div>
            </div>
          </div>


          <div class="form-group mt-2" id="externalEntityGroup" style="display: none;">
            <label for="external_entity">External Department/Organization/Person *</label>
            <input type="text" class="form-control" id="external_entity" name="external_entity">
          </div>

          {{-- <div class="form-group" id="venueGroup" style="display: none;">
            <label for="venue_id">Venue</label>
            <select class="form-control" id="venue_id" name="venue_id">
              <option value="">Select Venue</option>
            </select>
          </div> --}}
          <!-- Venue Selection -->
          {{-- <div id="floatingInputHelp" class="form-text  text-warning">Note: Changing the date or time will
            clear selected
            venues.
            Please confirm them before choosing a venue.</div> --}}

          <div class="form-group mt-2" id="venueGroup" style="display: none;">
            <label for="venues">Venues *</label>
            <select class="form-control select2" id="venues" name="venues[]" multiple="multiple">
              <option value="">Select Venue(s)</option>
            </select>

            <div class="mt-3" id="availableVenuesContainer" style="display: none;">
              <h5>Available Venues</h5>
              <div class="row" id="availableVenuesList">
                <!-- Venue cards will be inserted here -->
              </div>
            </div>
          </div>
          <div class="form-group mt-2" id="externalVenueGroup" style="display: none;">
            <label for="external_venue">External Venue</label>
            <input type="text" class="form-control" id="external_venue" name="external_venue">
          </div>

          <div class="form-group mt-2">
            <label for="custom_amenities_request">Additional Amenities Request</label>
            <textarea class="form-control" id="custom_amenities_request" name="custom_amenities_request" rows="2"
              placeholder="Specify any additional amenities you require"></textarea>
          </div>
          <div class="modal-footer">
            <div id="floatingInputHelp" class="form-text  text-warning">Note: The venues chosen for booking will be
              cleared if the date or time is changed. Before selecting a Venue, kindly confirm them.</div>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save Event</button>
            <button type="button" class="btn btn-danger mr-auto" id="cancelEventBtn" style="display: none;">
              Cancel Event
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="viewEventModal" tabindex="-1" role="dialog" aria-labelledby="viewEventModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <!-- Modal Header -->
      <div class="modal-header bg-primary">
        <h5 class="modal-title text-white fw-semibold" id="viewEventModalLabel">
          <i class="far fa-calendar-alt me-2"></i>Event Details
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Modal Body -->
      <div class="modal-body p-4">
        <!-- Title and Badges -->
        <div class="d-flex justify-content-between align-items-start mb-4">
          <h4 class="text-dark fw-semibold mb-0"><span id="viewEventTitle"></span></h4>
          <div>
            <span class="badge bg-secondary bg-opacity-10 text-white me-2" id="viewEventStatus"></span>
            <span class="badge bg-success bg-opacity-10 text-white me-2" id="viewEventType"></span>
            <span class="badge bg-dark bg-opacity-10 text-white" id="viewEventMode"></span>
          </div>
        </div>

        <!-- Two Column Layout -->
        <div class="row">
          <!-- Left Column -->
          <div class="col-md-6">
            <!-- Timing Section -->
            <div class="mb-4">
              <h6 class=" fw-semibold mb-3 border-bottom pb-2">
                <i class="far text-success fa-clock me-2"></i>Timing
              </h6>
              <div class="row mb-2">
                <div class="col-4 text-muted">Start:</div>
                <div class="col-8"><span id="viewEventStart"></span></div>
              </div>
              <div class="row mb-2">
                <div class="col-4 text-muted">End:</div>
                <div class="col-8"><span id="viewEventEnd"></span></div>
              </div>
              <div class="row">
                <div class="col-4 text-muted">Duration:</div>
                <div class="col-8"><span id="viewEventDuration"></span></div>
              </div>
            </div>

            <!-- Participants Section -->
            <div class="mb-4">
              <h6 class=" fw-semibold mb-3 border-bottom pb-2">
                <i class="text-success fa-solid fa-users me-2"></i>Participants
              </h6>
              <div class="row mb-2">
                <div class="col-4 text-muted">Coordinators:</div>
                <div class="col-8"><span id="viewEventCoordinators"></span></div>
              </div>
              <div class="row mb-2">
                <div class="col-4 text-muted">Faculties:</div>
                <div class="col-8"><span id="viewEventFaculties"></span></div>
              </div>
              <div class="row">
                <div class="col-4 text-muted">Count:</div>
                <div class="col-8"><span id="viewEventParticipants"></span></div>
              </div>
            </div>
          </div>

          <!-- Right Column -->
          <div class="col-md-6">
            <!-- Venue Details -->
            <div class="mb-4">
              <h6 class=" fw-semibold mb-3 border-bottom pb-2">
                <i class="text-success fa-solid fa-house me-2"></i>Venue Details
              </h6>
              <div class="row mb-2">
                <div class="col-4 text-muted">Venue Type:</div>
                <div class="col-8"><span id="viewEventVenueType"></span></div>
              </div>
              <div class="row mb-2">
                <div class="col-4 text-muted">Venues:</div>
                <div class="col-8"><span id="viewEventVenues"></span></div>
                <div class="col-8"><span id="viewEventExternalVenues"></span></div>
              </div>
              <div class="row mb-2">
                <div class="col-4 text-muted">Amenities:</div>
                <div class="col-8"><span id="viewEventCustomAmenities"></span></div>
              </div>
              <div class="row">
                <div class="col-4 text-muted">External Entity:</div>
                <div class="col-8"><span id="viewEventExternalEntity"></span></div>
              </div>
            </div>

            <!-- Other Details -->
            <div class="mb-4">
              <h6 class="d fw-semibold mb-3 border-bottom pb-2">

                <i class="text-success fa-solid fa-square-info me-2"></i>Other Details
              </h6>
              <div class="row mb-2">
                <div class="col-4 text-muted">Created By:</div>
                <div class="col-8"><span id="viewEventCreator"></span></div>
              </div>
              <div class="row">
                <div class="col-4 text-muted">Created On:</div>
                <div class="col-8"><span id="viewEventCreated"></span></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Description -->
        <div class="mt-4">
          <h6 class=" fw-semibold mb-3 border-bottom pb-2">
            <i class="far text-success fa-file-alt me-2"></i>Description
          </h6>
          <div class="p-3 bg-light rounded">
            <span id="viewEventDescription"></span>
          </div>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
          <i class="fas fa-times me-1"></i> Close
        </button>
      </div>
    </div>
  </div>
</div>

@endsection