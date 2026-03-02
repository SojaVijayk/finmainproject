@extends('layouts/layoutMaster')

@section('title', 'Fullcalendar - Apps')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/fullcalendar/fullcalendar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/quill/editor.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css') }}" />

@endsection

@section('page-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-calendar.css') }}" />
@endsection

@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/fullcalendar/fullcalendar.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js') }}"></script>


@endsection

@section('page-script')
    <script src="{{ asset('assets/js/app-calendar-events.js') }}"></script>


    <script>
        $(".datepicker").datepicker({
            autoclose: true,
            format:'dd/mm/yyyy',
        });
        var select2 = $('.select2');
        if (select2.length) {
            select2.each(function() {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>').select2({
                    placeholder: 'Select value',
                    dropdownParent: $this.parent()
                });
            });
        }

        function calculateDuration(startDate, endDate) {
            // Convert both dates to milliseconds
            var startTime = new Date(startDate).getTime();
            var endTime = new Date(endDate).getTime();

            // Calculate the difference in milliseconds
            var timeDiff = endTime - startTime;

            // Convert milliseconds to seconds, minutes, hours, and days
            var seconds = Math.floor(timeDiff / 1000);
            var minutes = Math.floor(seconds / 60);
            var hours = Math.floor(minutes / 60);
            var days = Math.floor(hours / 24);

            // Calculate remaining hours, minutes, and seconds
            hours %= 24;
            minutes %= 60;
            seconds %= 60;

            // Return an object with the duration components
            return {
                days: days,
                hours: hours,
                minutes: minutes,
                seconds: seconds
            };
        }



        /**
         * App Calendar
         */

        /**
         * ! If both start and end dates are same Full calendar will nullify the end date value.
         * ! Full calendar will end the event on a day before at 12:00:00AM thus, event won't extend to the end date.
         * ! We are getting events from a separate file named app-calendar-events.js. You can add or remove events from there.
         *
         **/

        'use strict';

        let direction = 'ltr';

        if (isRtl) {
            direction = 'rtl';
        }

        document.addEventListener('DOMContentLoaded', function() {
            (function() {
                const calendarEl = document.getElementById('calendar'),
                    appCalendarSidebar = document.querySelector('.app-calendar-sidebar'),
                    addEventSidebar = document.getElementById('addEventSidebar'),
                    appOverlay = document.querySelector('.app-overlay'),
                    calendarsColor = {
                        {{--  Business: 'primary',

        Personal: 'danger',
        Family: 'warning',
        ETC: 'info'  --}}
                        Requested: 'dark',
                        Approved: 'success',
                        Absent: 'danger',
                        Rejected: 'warning',
                        Present: 'primary',
                        Holiday: 'danger',
                    },
                    offcanvasTitle = document.querySelector('.offcanvas-title'),
                    btnToggleSidebar = document.querySelector('.btn-toggle-sidebar'),
                    btnSubmit = document.querySelector('button[type="submit"]'),
                    btnDeleteEvent = document.querySelector('.btn-delete-event'),
                    btnCancel = document.querySelector('.btn-cancel'),
                    eventTitle = document.querySelector('#eventTitle'),
                    eventStartDate = document.querySelector('#eventStartDate'),
                    eventEndDate = document.querySelector('#eventEndDate'),
                    eventUrl = document.querySelector('#eventURL'),
                    eventLabel = $('#eventLabel'), // ! Using jquery vars due to select2 jQuery dependency
                    eventGuests = $('#eventGuests'), // ! Using jquery vars due to select2 jQuery dependency
                    eventLocation = document.querySelector('#eventLocation'),
                    eventDescription = document.querySelector('#eventDescription'),
                    allDaySwitch = document.querySelector('.allDay-switch'),
                    selectAll = document.querySelector('.select-all'),
                    filterInput = [].slice.call(document.querySelectorAll('.input-filter')),
                    inlineCalendar = document.querySelector('.inline-calendar');

                let eventToUpdate,
                    currentEvents =
                    events, // Assign app-calendar-events.js file events (assume events from API) to currentEvents (browser store/object) to manage and update calender events
                    isFormValid = false,
                    inlineCalInstance;

                // Init event Offcanvas
                const bsAddEventSidebar = new bootstrap.Offcanvas(addEventSidebar);

                //! TODO: Update Event label and guest code to JS once select removes jQuery dependency
                // Event Label (select2)
                if (eventLabel.length) {
                    function renderBadges(option) {
                        if (!option.id) {
                            return option.text;
                        }
                        var $badge =
                            "<span class='badge badge-dot bg-" + $(option.element).data('label') + " me-2'> " +
                            '</span>' + option.text;

                        return $badge;
                    }
                    eventLabel.wrap('<div class="position-relative"></div>').select2({
                        placeholder: 'Select value',
                        dropdownParent: eventLabel.parent(),
                        templateResult: renderBadges,
                        templateSelection: renderBadges,
                        minimumResultsForSearch: -1,
                        escapeMarkup: function(es) {
                            return es;
                        }
                    });
                }

                // Event Guests (select2)
                if (eventGuests.length) {
                    function renderGuestAvatar(option) {
                        if (!option.id) {
                            return option.text;
                        }
                        var $avatar =
                            "<div class='d-flex flex-wrap align-items-center'>" +
                            "<div class='avatar avatar-xs me-2'>" +
                            "<img src='" +
                            assetsPath +
                            'img/avatars/' +
                            $(option.element).data('avatar') +
                            "' alt='avatar' class='rounded-circle' />" +
                            '</div>' +
                            option.text +
                            '</div>';

                        return $avatar;
                    }
                    eventGuests.wrap('<div class="position-relative"></div>').select2({
                        placeholder: 'Select value',
                        dropdownParent: eventGuests.parent(),
                        closeOnSelect: false,
                        templateResult: renderGuestAvatar,
                        templateSelection: renderGuestAvatar,
                        escapeMarkup: function(es) {
                            return es;
                        }
                    });
                }

                // Event start (flatpicker)
                if (eventStartDate) {
                    var start = eventStartDate.flatpickr({
                        enableTime: true,
                        altFormat: 'Y-m-dTH:i:S',
                        onReady: function(selectedDates, dateStr, instance) {
                            if (instance.isMobile) {
                                instance.mobileInput.setAttribute('step', null);
                            }
                        }
                    });
                }

                // Event end (flatpicker)
                if (eventEndDate) {
                    var end = eventEndDate.flatpickr({
                        enableTime: true,
                        altFormat: 'Y-m-dTH:i:S',
                        onReady: function(selectedDates, dateStr, instance) {
                            if (instance.isMobile) {
                                instance.mobileInput.setAttribute('step', null);
                            }
                        }
                    });
                }

                // Inline sidebar calendar (flatpicker)
                if (inlineCalendar) {
                    inlineCalInstance = inlineCalendar.flatpickr({
                        monthSelectorType: 'static',
                        inline: true
                    });
                }

                // Event click function
                function eventClick(info) {

                    eventToUpdate = info.event;
                    if (eventToUpdate.url) {
                        info.jsEvent.preventDefault();
                        window.open(eventToUpdate.url, '_blank');
                    }

                }

                // Modify sidebar toggler
                function modifyToggler() {
                    const fcSidebarToggleButton = document.querySelector('.fc-sidebarToggle-button');
                    fcSidebarToggleButton.classList.remove('fc-button-primary');
                    fcSidebarToggleButton.classList.add('d-lg-none', 'd-inline-block', 'ps-0');
                    while (fcSidebarToggleButton.firstChild) {
                        fcSidebarToggleButton.firstChild.remove();
                    }
                    fcSidebarToggleButton.setAttribute('data-bs-toggle', 'sidebar');
                    fcSidebarToggleButton.setAttribute('data-overlay', '');
                    fcSidebarToggleButton.setAttribute('data-target', '#app-calendar-sidebar');
                    fcSidebarToggleButton.insertAdjacentHTML('beforeend', '<i class="ti ti-menu-2 ti-sm"></i>');
                }

                // Filter events by calender
                function selectedCalendars() {
                    let selected = [],
                        filterInputChecked = [].slice.call(document.querySelectorAll('.input-filter:checked'));

                    filterInputChecked.forEach(item => {
                        selected.push(item.getAttribute('data-value'));
                    });

                    return selected;
                }

                // --------------------------------------------------------------------------------------------------
                // AXIOS: fetchEvents
                // * This will be called by fullCalendar to fetch events. Also this can be used to refetch events.
                // --------------------------------------------------------------------------------------------------
                function fetchEvents(info, successCallback) {
                    // Fetch Events from API endpoint reference
                    $.ajax({
                        url: '/fetchEvents',
                        {{--  url: '../../../app-assets/data/app-calendar-events.js',  --}}
                        type: 'GET',
                        {{--  dataType: "script",  --}}
                        success: function(result) {
                            // Get requested calendars as Array
                            var calendars = selectedCalendars();
                            {{--  let selectedEvents = [result.events.filter(event => calendars.includes(event.extendedProps.calendar))];  --}}

                            let selectedEvents = result.events.filter(function(event) {
                                // console.log(event.extendedProps.calendar.toLowerCase());
                                return calendars.includes(event.extendedProps.calendar
                                    .toLowerCase());
                            });

                            {{--  return [result.events.filter(event => calendars.includes(event.extendedProps.calendar))];  --}}
                            successCallback(selectedEvents);

                        },
                        error: function(error) {
                            console.log(error);
                        }
                    });



                    {{--  let calendars = selectedCalendars();
      // We are reading event object from app-calendar-events.js file directly by including that file above app-calendar file.
      // You should make an API call, look into above commented API call for reference
      let selectedEvents = currentEvents.filter(function (event) {
        // console.log(event.extendedProps.calendar.toLowerCase());
        return calendars.includes(event.extendedProps.calendar.toLowerCase());
      });  --}}
                    // if (selectedEvents.length > 0) {
                    {{--  successCallback(selectedEvents);  --}}
                    // }
                }


                // Init FullCalendar
                // ------------------------------------------------
                let calendar = new Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    events: fetchEvents,
                    plugins: [dayGridPlugin, interactionPlugin, listPlugin, timegridPlugin],
                    editable: true,
                    dragScroll: true,
                    dayMaxEvents: 2,
                    eventResizableFromStart: true,
                    customButtons: {
                        sidebarToggle: {
                            text: 'Sidebar'
                        }
                    },
                    headerToolbar: {
                        start: 'sidebarToggle, prev,next, title',
                        end: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
                    },
                    direction: direction,
                    initialDate: new Date(),
                    navLinks: true, // can click day/week names to navigate views
                    eventClassNames: function({
                        event: calendarEvent
                    }) {
                        const colorName = calendarsColor[calendarEvent._def.extendedProps.calendar];
                        // Background Color
                        return ['fc-event-' + colorName];
                    },
                    dateClick: function(info) {
                        let date = moment(info.date).format('YYYY-MM-DD');
                        resetValues();
                        bsAddEventSidebar.show();

                        // For new event set offcanvas title text: Add Event
                        if (offcanvasTitle) {
                            offcanvasTitle.innerHTML = 'Add Event';
                        }
                        btnSubmit.innerHTML = 'Add';
                        btnSubmit.classList.remove('btn-update-event');
                        btnSubmit.classList.add('btn-add-event');
                        btnDeleteEvent.classList.add('d-none');
                        eventStartDate.value = date;
                        eventEndDate.value = date;
                    },
                    eventClick: function(info) {
                        eventClick(info);
                    },
                    datesSet: function() {
                        modifyToggler();
                    },
                    viewDidMount: function() {
                        modifyToggler();
                    }
                });

                // Render calendar
                calendar.render();
                // Modify sidebar toggler
                modifyToggler();

                const eventForm = document.getElementById('eventForm');
                const fv = FormValidation.formValidation(eventForm, {
                        fields: {
                            eventTitle: {
                                validators: {
                                    notEmpty: {
                                        message: 'Please enter event title '
                                    }
                                }
                            },
                            eventStartDate: {
                                validators: {
                                    notEmpty: {
                                        message: 'Please enter start date '
                                    }
                                }
                            },
                            eventEndDate: {
                                validators: {
                                    notEmpty: {
                                        message: 'Please enter end date '
                                    }
                                }
                            }
                        },
                        plugins: {
                            trigger: new FormValidation.plugins.Trigger(),
                            bootstrap5: new FormValidation.plugins.Bootstrap5({
                                // Use this for enabling/changing valid/invalid class
                                eleValidClass: '',
                                rowSelector: function(field, ele) {
                                    // field is the field name & ele is the field element
                                    return '.mb-3';
                                }
                            }),
                            submitButton: new FormValidation.plugins.SubmitButton(),
                            // Submit the form when all fields are valid
                            // defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
                            autoFocus: new FormValidation.plugins.AutoFocus()
                        }
                    })
                    .on('core.form.valid', function() {
                        // Jump to the next step when all fields in the current step are valid
                        isFormValid = true;
                    })
                    .on('core.form.invalid', function() {
                        // if fields are invalid
                        isFormValid = false;
                    });

                // Sidebar Toggle Btn
                if (btnToggleSidebar) {
                    btnToggleSidebar.addEventListener('click', e => {
                        btnCancel.classList.remove('d-none');
                    });
                }

                // Add Event
                // ------------------------------------------------
                function addEvent(eventData) {
                    // ? Add new event data to current events object and refetch it to display on calender
                    // ? You can write below code to AJAX call success response

                    currentEvents.push(eventData);
                    calendar.refetchEvents();

                    // ? To add event directly to calender (won't update currentEvents object)
                    // calendar.addEvent(eventData);
                }

                // Update Event
                // ------------------------------------------------
                function updateEvent(eventData) {
                    // ? Update existing event data to current events object and refetch it to display on calender
                    // ? You can write below code to AJAX call success response
                    eventData.id = parseInt(eventData.id);
                    currentEvents[currentEvents.findIndex(el => el.id === eventData.id)] =
                        eventData; // Update event by id
                    calendar.refetchEvents();

                    // ? To update event directly to calender (won't update currentEvents object)
                    // let propsToUpdate = ['id', 'title', 'url'];
                    // let extendedPropsToUpdate = ['calendar', 'guests', 'location', 'description'];

                    // updateEventInCalendar(eventData, propsToUpdate, extendedPropsToUpdate);
                }

                // Remove Event
                // ------------------------------------------------

                function removeEvent(eventId) {
                    // ? Delete existing event data to current events object and refetch it to display on calender
                    // ? You can write below code to AJAX call success response
                    currentEvents = currentEvents.filter(function(event) {
                        return event.id != eventId;
                    });
                    calendar.refetchEvents();

                    // ? To delete event directly to calender (won't update currentEvents object)
                    // removeEventInCalendar(eventId);
                }

                // (Update Event In Calendar (UI Only)
                // ------------------------------------------------
                const updateEventInCalendar = (updatedEventData, propsToUpdate, extendedPropsToUpdate) => {
                    const existingEvent = calendar.getEventById(updatedEventData.id);

                    // --- Set event properties except date related ----- //
                    // ? Docs: https://fullcalendar.io/docs/Event-setProp
                    // dateRelatedProps => ['start', 'end', 'allDay']
                    // eslint-disable-next-line no-plusplus
                    for (var index = 0; index < propsToUpdate.length; index++) {
                        var propName = propsToUpdate[index];
                        existingEvent.setProp(propName, updatedEventData[propName]);
                    }

                    // --- Set date related props ----- //
                    // ? Docs: https://fullcalendar.io/docs/Event-setDates
                    existingEvent.setDates(updatedEventData.start, updatedEventData.end, {
                        allDay: updatedEventData.allDay
                    });

                    // --- Set event's extendedProps ----- //
                    // ? Docs: https://fullcalendar.io/docs/Event-setExtendedProp
                    // eslint-disable-next-line no-plusplus
                    for (var index = 0; index < extendedPropsToUpdate.length; index++) {
                        var propName = extendedPropsToUpdate[index];
                        existingEvent.setExtendedProp(propName, updatedEventData.extendedProps[propName]);
                    }
                };

                // Remove Event In Calendar (UI Only)
                // ------------------------------------------------
                function removeEventInCalendar(eventId) {
                    calendar.getEventById(eventId).remove();
                }

                // Add new event
                // ------------------------------------------------
                btnSubmit.addEventListener('click', e => {
                    if (btnSubmit.classList.contains('btn-add-event')) {
                        if (isFormValid) {
                            let newEvent = {
                                id: calendar.getEvents().length + 1,
                                title: eventTitle.value,
                                start: eventStartDate.value,
                                end: eventEndDate.value,
                                startStr: eventStartDate.value,
                                endStr: eventEndDate.value,
                                display: 'block',
                                extendedProps: {
                                    location: eventLocation.value,
                                    guests: eventGuests.val(),
                                    calendar: eventLabel.val(),
                                    description: eventDescription.value
                                }
                            };
                            if (eventUrl.value) {
                                newEvent.url = eventUrl.value;
                            }
                            if (allDaySwitch.checked) {
                                newEvent.allDay = true;
                            }
                            addEvent(newEvent);
                            bsAddEventSidebar.hide();
                        }
                    } else {
                        // Update event
                        // ------------------------------------------------
                        if (isFormValid) {
                            let eventData = {
                                id: eventToUpdate.id,
                                title: eventTitle.value,
                                start: eventStartDate.value,
                                end: eventEndDate.value,
                                url: eventUrl.value,
                                extendedProps: {
                                    location: eventLocation.value,
                                    guests: eventGuests.val(),
                                    calendar: eventLabel.val(),
                                    description: eventDescription.value
                                },
                                display: 'block',
                                allDay: allDaySwitch.checked ? true : false
                            };

                            updateEvent(eventData);
                            bsAddEventSidebar.hide();
                        }
                    }
                });

                // Call removeEvent function
                btnDeleteEvent.addEventListener('click', e => {
                    removeEvent(parseInt(eventToUpdate.id));
                    // eventToUpdate.remove();
                    bsAddEventSidebar.hide();
                });

                // Reset event form inputs values
                // ------------------------------------------------
                function resetValues() {
                    eventEndDate.value = '';
                    eventUrl.value = '';
                    eventStartDate.value = '';
                    eventTitle.value = '';
                    eventLocation.value = '';
                    allDaySwitch.checked = false;
                    eventGuests.val('').trigger('change');
                    eventDescription.value = '';
                }

                // When modal hides reset input values
                addEventSidebar.addEventListener('hidden.bs.offcanvas', function() {
                    resetValues();
                });

                // Hide left sidebar if the right sidebar is open
                btnToggleSidebar.addEventListener('click', e => {
                    if (offcanvasTitle) {
                        offcanvasTitle.innerHTML = 'Add Event';
                    }
                    btnSubmit.innerHTML = 'Add';
                    btnSubmit.classList.remove('btn-update-event');
                    btnSubmit.classList.add('btn-add-event');
                    btnDeleteEvent.classList.add('d-none');
                    appCalendarSidebar.classList.remove('show');
                    appOverlay.classList.remove('show');
                });

                // Calender filter functionality
                // ------------------------------------------------
                if (selectAll) {
                    selectAll.addEventListener('click', e => {
                        if (e.currentTarget.checked) {
                            document.querySelectorAll('.input-filter').forEach(c => (c.checked = 1));
                        } else {
                            document.querySelectorAll('.input-filter').forEach(c => (c.checked = 0));
                        }
                        calendar.refetchEvents();
                    });
                }

                if (filterInput) {
                    filterInput.forEach(item => {
                        item.addEventListener('click', () => {
                            document.querySelectorAll('.input-filter:checked').length < document
                                .querySelectorAll('.input-filter').length ?
                                (selectAll.checked = false) :
                                (selectAll.checked = true);
                            calendar.refetchEvents();
                        });
                    });
                }

                // Jump to date on sidebar(inline) calendar change
                inlineCalInstance.config.onChange.push(function(date) {
                    calendar.changeView(calendar.view.type, moment(date[0]).format('YYYY-MM-DD'));
                    modifyToggler();
                    appCalendarSidebar.classList.remove('show');
                    appOverlay.classList.remove('show');
                });
            })();


            $("#reportType").change(function() {
              $('input[name="viewTypeOptinon"]').prop('checked', false);
              $('input[name="viewTypeOptinon"]:checked').val();
              var $radios = $('input:radio[name=viewTypeOptinon]');
              if($('#reportType').val() ==1){
                $('.detailed-radio').show();
                $radios.filter('[value=monitor]').prop('checked', true);
              }
              else{
                $radios.filter('[value=html]').prop('checked', true);
                $('.detailed-radio').hide();
              }
          });


            $("body").on("click", "#report", function(e) {
                e.preventDefault();

                var fromDate = $("#fromDate").val();
                var toDate = $("#toDate").val();

                var view_type = $('input[name="viewTypeOptinon"]:checked').val();
                var reportType = $("#reportType").val();


                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                    }
                })
                if (reportType == 1) {
                    if (view_type == 'excel' || view_type == 'pdf') {

                        $.ajax({
                            data: {
                                fromDate: fromDate,
                                toDate: toDate,
                                type: 1,
                                view_type: view_type,

                                "_token": "{{ csrf_token() }}",
                            },
                            url: `${baseUrl}downloadBulk`,
                            type: 'POST',
                            xhrFields: {
                                responseType: 'blob'
                            },
                            beforeSend: function() {
                                //
                            },
                            success: function(data) {
                                var url = window.URL || window.webkitURL;
                                var objectUrl = url.createObjectURL(data);
                                window.open(objectUrl);
                            },
                            error: function(data) {
                                //
                            }
                        });
                    }
                    else if(view_type == 'monitor'){
                      $.ajax({
                        data:  {
                        fromDate: fromDate,
                                toDate: toDate,
                                type: '1',
                                view_type: view_type,


                           "_token": "{{ csrf_token() }}",
                       },
                         url: '/attendance/monitor-report',
                         type: 'POST',

                       success: function(data) {

                        $('#AttendanceReport').modal('show');
                        $(".datatables-leave-list #dataList").html(data);
                       },
                       error: function(data) {
                           //
                       }
                   });

                    }

                    else if(view_type == 'html'){
                      $.ajax({
                        data:  {
                         fromDate: fromDate,
                                toDate: toDate,
                                type: '1',
                                view_type: view_type,
                                "_token": "{{ csrf_token() }}",
                       },
                       url: `${baseUrl}downloadBulk`,
                         type: 'POST',

                       success: function(data) {

                        $('#AttendanceReport').modal('show');
                        $(".datatables-leave-list #dataList").html(data);
                       },
                       error: function(data) {
                           //
                       }
                   });

                    }


                    else {



                        $.ajax({
                            data: {
                                fromDate: fromDate,
                                toDate: toDate,
                                type: '1',
                                view_type: view_type,
                                "_token": "{{ csrf_token() }}",
                            },
                            url: `${baseUrl}downloadBulk`,
                            type: 'POST',

                            success: function(data) {
                                {{--
            data.list.forEach((item, index) => {
              tbody=tbody+'<tr><td>'+item.name+'</td><td>'+item.date+'</td><td> <span class="text-'+(item.in_time <= '09:30'  ? "success" : 'warning')+'">'+item.in_time+'</span></td><td><span class="text-'+(item.out_time >= '17:30'  ? "success" : 'warning')+'">'+item.out_time+'</span></td>';
              });

             --}}

                                var tbody = '';
                                data.list.forEach((item, index) => {
                                    var totalDuration = 0;
                                    var extraTime = 0;
                                    if ((item.InTime != null && item.InTime != '') && (
                                            item.OutTime != null && item.OutTime != ''
                                        )) {
                                        var startDate = item
                                            .InTime; // Format: YYYY-MM-DDTHH:MM:SS
                                        var endDate = item
                                            .OutTime; // Format: YYYY-MM-DDTHH:MM:SS

                                        var duration = calculateDuration(item.InTime,
                                            item.OutTime);
                                        var hours = duration.hours;
                                        var minutes = duration.minutes;
                                        totalDuration = (hours * 60) + minutes;
                                        if (totalDuration > 480) {
                                            {{--  extraTime = totalDuration-480;  --}}
                                        }


                                    }



                                    tbody = tbody + '<tr><td>' + item.name +
                                        '</td><td>' + item.date + '</td>' +
                                        '<td><span class="text-success"><strong>IN</strong></span> : ' +
                                        item.InTime +
                                        '<br><span class="text-danger"><strong>Out</strong></span> : ' +
                                        (item.OutTime != item.InTime ? item.OutTime :
                                            'No Records') +
                                        {{--  '</td><td>'+(item.OutTime != item.InTime ? item.OutTime : '')+'</td>'+  --}} '<td>' + (item.LateBy >
                                            0 ? item.LateBy : "-") + '</td><td>' + (item
                                            .EarlyBy > 0 ? item.EarlyBy : "-") +
                                        '</td><td>' + (totalDuration > 0 ?
                                            totalDuration : "-") + '</td>' +
                                        {{--  ' <td> <span class="text-'+(item.InTime <= '09:30'  ? "success" : 'warning')+'">'+(item.InTime != null ? item.InTime : '')+'</span></td>'+
               '<td><span class="text-'+(item.OutTime >= '17:30'  ? "success" : 'warning')+'">'+(item.OutTime != null ? item.OutTime : '')+'</span></td>'+  --}} '';
                                    var leave = '';
                                    var leave_status = '';
                                    var leave_day = '';

                                    var mov_title = '';
                                    var mov_loc = '';
                                    var mov_date = '';
                                    var mov_type = '';
                                    var mov_status = '';




                                    var miss_date = '';
                                    var miss_type = '';
                                    var miss_in = '';
                                    var miss_out = '';
                                    var miss_status = '';


                                    if ((item.leave_type != '') && (item.leave_type !=
                                            null)) {
                                        leave = item.leave_type;
                                        leave_status = (item.leave_status == 1 ?
                                            'Approved' : (item.leave_status == 2 ?
                                                'Rejected' : 'Pending'));
                                        leave_day = (item.leave_day_type == 1 ?
                                            'Full Day' : (item.leave_day_type == 2 ?
                                                'AN' : 'FN'));

                                        tbody = tbody +
                                            '<td> Leave Details (Leave Type : ' +
                                            leave + ' - Day Type :' + leave_day +
                                            ' - Status:' + leave_status + ')</td>';
                                    }



                                    if ((item.movement_status != '') && (item
                                            .movement_status != null)) {
                                        mov_type = item.type;
                                        mov_title = item.title;
                                        mov_loc = item.location;
                                        mov_date = item.start_date + ' - ' + item
                                            .start_time + ' to ' + item.end_date +
                                            ' - ' + item.end_time;
                                        mov_status = (item.mov_status == 1 ?
                                            'Approved' : (item.mov_status == 2 ?
                                                'Rejected' : 'Pending'));
                                        tbody = tbody + '<td> Movement Details ( ' +
                                            mov_type + ' -  ' + mov_title +
                                            ' Duraton : ' + mov_date + ' - Status:' +
                                            mov_status + ')</td>';

                                    }

                                    if ((item.miss_status != '') && (item.miss_status !=
                                            null)) {
                                        miss_type = item.miss_type;

                                        miss_date = item.miss_date + ' - In Time ' +
                                            item.checkinTime + ' Out Time ' + item
                                            .checkoutTime;
                                        miss_status = (item.miss_status == 1 ?
                                            'Approved' : (item.miss_status == 2 ?
                                                'Rejected' : 'Pending'));
                                        tbody = tbody + '<td> Miss Punch Details ( ' +
                                            miss_type + ' - Duraton : ' + miss_date +
                                            ' - Status:' + miss_status + ')</td>';

                                    }

                                    if (((item.miss_status == '') || (item
                                            .miss_status == null)) && ((item
                                            .movement_status == '') || (item
                                            .movement_status == null)) && ((item
                                            .leave_type == '') || (item
                                            .leave_type == null))) {
                                        tbody = tbody + '<td></td>';
                                    }

                                    tbody = tbody + '</tr>';

                                });
                                $('#DesignationModal').modal('show');
                                $(".datatables-leave-list #dataList").html(tbody);

                            },
                            error: function(data) {
                                //
                            }
                        });

                    }
                }

                if (reportType == 2) {
                    if (view_type == 'excel' || view_type == 'pdf') {

                        $.ajax({
                            data: {
                                fromDate: fromDate,
                                toDate: toDate,
                                type: 1,
                                view_type: view_type,

                                "_token": "{{ csrf_token() }}",
                            },
                            url: `${baseUrl}movement/downloadBulk`,
                            type: 'POST',
                            xhrFields: {
                                responseType: 'blob'
                            },
                            beforeSend: function() {
                                //
                            },
                            success: function(data) {
                                var url = window.URL || window.webkitURL;
                                var objectUrl = url.createObjectURL(data);
                                window.open(objectUrl);
                            },
                            error: function(data) {
                                //
                            }
                        });
                    } else {



                        $.ajax({
                            data: {
                                fromDate: fromDate,
                                toDate: toDate,
                                type: '1',
                                view_type: view_type,
                                "_token": "{{ csrf_token() }}",
                            },
                            url: `${baseUrl}movement/downloadBulk`,
                            type: 'POST',

                            success: function(data) {
                                var tbody = '';
                                data.list.forEach((item, index) => {
                                    tbody = tbody + '<tr><td>' + item.name +
                                        '</td><td>' + item.start_date + '</td><td>' +
                                        item.start_time + '</td><td>' + item.end_date +
                                        '</td><td>' + item.end_time +
                                        '<td>' + item.title + '</td><td>' + item.type +
                                        '</td><td>' + item.location + '</td><td>' + item
                                        .description + '</td><td>' + item.requested_at +
                                        '</td>' +
                                        '<td>' + (item.status == 0 ?
                                            '<span class="badge bg-secondary">Pending</span>' :
                                            (item.status == 1 ?
                                                '<span class="badge bg-success">Aproved</span>' :
                                                '<span class="badge bg-danger">Rejected</span>'
                                            )) + '</td>' +
                                        '<td>' + item.action_by_name + '</td><td>' +
                                        item.action_at + '</td>';
                                });
                                $('#MovementModal').modal('show');
                                $(".datatables-leave-list #dataList").html(tbody);

                            },
                            error: function(data) {
                                //
                            }
                        });

                    }
                }


                if (reportType == 3) {
                    if (view_type == 'excel' || view_type == 'pdf') {

                        $.ajax({
                            data: {
                                fromDate: fromDate,
                                toDate: toDate,
                                type: 1,
                                view_type: view_type,

                                "_token": "{{ csrf_token() }}",
                            },
                            url: `${baseUrl}leave/downloadBulk`,
                            type: 'POST',
                            xhrFields: {
                                responseType: 'blob'
                            },
                            beforeSend: function() {
                                //
                            },
                            success: function(data) {
                                var url = window.URL || window.webkitURL;
                                var objectUrl = url.createObjectURL(data);
                                window.open(objectUrl);
                            },
                            error: function(data) {
                                //
                            }
                        });
                    } else {



                        $.ajax({
                            data: {
                                fromDate: fromDate,
                                toDate: toDate,
                                type: '1',
                                view_type: view_type,
                                "_token": "{{ csrf_token() }}",
                            },
                            url: `${baseUrl}leave/downloadBulk`,
                            type: 'POST',

                            success: function(data) {
                                var tbody = '';
                                var tbody_sub = '';
                                data.list.forEach((item, index) => {
                                    {{--  tbody=tbody+'<tr><td>'+item.name+'</td><td>'+item.from+'</td><td>'+item.to+'</td><td>'+item.duration+'</td><td>'+item.requested_at+'</td><td>'+item.status+'</td>';  --}}



                                    tbody_sub = tbody_sub + '<tr><td>' + item.name +
                                        '</td><td>' + item.leave_type + '</td><td>' +
                                        item.date + '</td><td>' + (item
                                            .leave_day_type == 1 ? 'Full Day' : item
                                            .leave_day_type == 2 ? 'FN' : 'AN') +
                                        '</td>' +
                                        '<td>' + item.requested_at + '</td><td>' + (item
                                            .status == 0 ?
                                            '<span class="text-nowrap badge bg-label-secondary">Pending</span></td>' :
                                            (item.status == 1 ?
                                                '<span class="badge bg-label-success">Approved</span><br>Remark : ' +
                                                item.remark + ' ' :
                                                '<span class="badge bg-label-danger">Rejected</span> <br>Remark : ' +
                                                item.remark + '</td>')) + '<td>' + item
                                        .action_by_name + '</td><td>' + item.action_at +
                                        '</td></tr>';



                                });
                                $('#LeaveModal').modal('show');
                                $(".datatables-leave-list #dataList").html(tbody_sub);

                            },
                            error: function(data) {
                                //
                            }
                        });

                    }
                } else if (reportType == 4) {
                    if (view_type == 'excel' || view_type == 'pdf') {

                        $.ajax({
                            data: {
                                fromDate: fromDate,
                                toDate: toDate,
                                type: 1,
                                view_type: view_type,

                                "_token": "{{ csrf_token() }}",
                            },
                            url: `${baseUrl}misspunch/downloadBulk`,
                            type: 'POST',
                            xhrFields: {
                                responseType: 'blob'
                            },
                            beforeSend: function() {
                                //
                            },
                            success: function(data) {
                                var url = window.URL || window.webkitURL;
                                var objectUrl = url.createObjectURL(data);
                                window.open(objectUrl);
                            },
                            error: function(data) {
                                //
                            }
                        });
                    } else {



                        $.ajax({
                            data: {
                                fromDate: fromDate,
                                toDate: toDate,
                                type: '1',
                                view_type: view_type,
                                "_token": "{{ csrf_token() }}",
                            },
                            url: `${baseUrl}misspunch/downloadBulk`,
                            type: 'POST',

                            success: function(data) {
                                var tbody = '';
                                data.list.forEach((item, index) => {
                                    tbody = tbody + '<tr><td>' + item.name +
                                        '</td><td>' + item.date + '</td><td>' + (item
                                            .type == 1 ? "Checkin" : item.type == 2 ?
                                            "Checkout" : "Checkin&Checkout") +
                                        '</td><td>' + item.checkinTime + '</td><td>' +
                                        item.checkoutTime +
                                        '<td>' + item.description + '</td><td>' + item
                                        .requested_at + '</td>' +
                                        '<td>' + (item.status == 0 ?
                                            '<span class="badge bg-secondary">Pending</span>' :
                                            (item.status == 1 ?
                                                '<span class="badge bg-success">Aproved</span>' :
                                                '<span class="badge bg-danger">Rejected</span>'
                                            )) + '</td>' +
                                        '<td>' + item.action_by_name + '</td><td>' +
                                        item.action_at + '</td>';
                                });
                                $('#MisspunchModal').modal('show');
                                $(".datatables-leave-list #dataList").html(tbody);

                            },
                            error: function(data) {
                                //
                            }
                        });

                    }
                }





            });




        });
    </script>
@endsection

@section('content')
    <div class="row ">
        <div class="alert alert-warning alert-dismissible d-flex align-items-baseline" role="alert">
            <span class="alert-icon alert-icon-lg text-primary me-2">
                <i class="ti ti-calendar ti-sm"></i>
            </span>
            <div class="d-flex flex-column ps-1">
                <p class="mb-0 text-dark"> Attendance Statistics for the period of {{ $from }} TO
                    {{ $to }}</p>
                <h5 class="alert-heading mb-2"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                </button>
            </div>
        </div>


    </div>

    <div class="row">

        <div class="col-lg-12 col-12 mb-4">

            <div class="card mb-3">
                {{--  <h5 class="card-header">Responsive Table</h5>  --}}
                <div class="table-responsive  table-hover">
                    <table class="table">
                        <thead>
                            <tr class="text-nowrap">
                                <th>Days</th>
                                <th>Status</th>
                                <th>In Time</th>
                                <th>Out Time</th>

                                <th>Duration</th>

                                <th>Late By</th>

                                <th>Early By</th>

                                <th>OT</th>
                                <th>Remark</th>

                            </tr>
                        </thead>
                        <tbody>

                            @php

                                $array_excemption_dates=[];
                                $array_excemption_dates=[];
                                    $numberof_days= count($date_range_array);
                                    $number_of_holdays = count($holidays);
                                    $number_of_leaves = count($leaves);

                                    $total_working_days = $numberof_days-$number_of_holdays;
                                    $total_working_days_by_employee = $numberof_days-($number_of_holdays+$number_of_leaves);

                            @endphp
                            @foreach ($date_range_array as $item)
                                @php
                                    $date_data = explode('-', $item['date']);
                                    $flag = 0;
                                    $late_flag = 0;

                                @endphp


                                <tr class="text-nowrap">
                                    <td> <span
                                            class="badge badge-center rounded-pill bg-dark bg-glow">{{ $date_data[2] }}</span>
                                        <br>
                                        <span class="font-weight-bold text-bold">
                                            <strong>{{ $item['weekday'] }}</strong></span>
                                    </td>


                                    @foreach ($attendance_data as $data)

                                        @if ($item['date'] == $data['date'])
                                            @php
                                                $flag++;
                                                $overTime=0;
                                                    $minutes=0;
                                                    $startTime = new DateTime($data['InTime']);
                                                    $endTime = new DateTime($data['OutTime']);

                                                    // Calculate the difference
                                                    $interval = $startTime->diff($endTime);

                                                    // Convert the difference to minutes
                                                    $minutes = ($interval->h * 60) + $interval->i;
                                                    $minutes += ($interval->d * 24 * 60);

                                                    if($minutes >= 480){
                                                        $overTime = ( $minutes - 480);
                                                    }
                                            @endphp
                                            <td>
                                                <span class="badge bg-label-success bg-glow text-sm">
                                                    {{ $data['StatusCode'] }}
                                                </span>
                                            </td>
                                            <td>

                                                {{ $data['InTime'] }}
                                            </td>
                                            <td>
                                                {{ $data['OutTime'] }}
                                            </td>
                                            <td>
                                                <span class="badge bg-label-primary">{{ $minutes }} </span>

                                            </td>
                                            <td>
                                                {{--  <span class="badge bg-label-danger"> {{ $data['LateBy'] }}</span>  --}}
                                                <span class="badge bg-label-danger"> @if($minutes < 480) @if($data['EarlyBy'] == 0 && $data['LateBy'] > 0) {{480-$minutes}} @else {{  $data['LateBy'] }} @endif @endif </span>


                                            </td>
                                            <td>
                                                {{--  <span class="badge bg-label-danger"> {{ $data['EarlyBy'] }}</span>  --}}
                                                <span class="badge bg-label-danger">@if($minutes < 480) @if($data['LateBy'] == 0 && $data['EarlyBy'] > 0) {{480-$minutes}} @else {{ $data['EarlyBy'] }} @endif @endif</span>


                                            </td>
                                            <td>
                                                <span class="badge bg-label-secondary">{{ $overTime }} </span>

                                            </td>
                                        @endif

                                    @endforeach
                                    @if ($flag == 0)
                                        <td colspan="7" class="text-nowrap text-center">
                                           <span class="text-center badge bg-label-secondary"> No records </span>
                                        </td>
                                    @endif
                                    <td>
                                      @foreach ($holidays as $holiday)
                                          @if ($holiday->date == $item['date'])
                                              @php
                                                  $late_flag++;
                                                  array_push($array_excemption_dates, $item['date']);
                                              @endphp
                                              <span
                                                  class="badge bg-danger p-2 mb-1">{{ $holiday->description }}</span>
                                              <br>
                                          @endif
                                      @endforeach

                                      @foreach ($leaves as $leave)
                                          @if ($leave->leave_date == $item['date'] && $leave->leave_user_id == $data['user_id'])
                                              @php
                                                  $late_flag++;
                                                  array_push($array_excemption_dates, $item['date']);
                                              @endphp
                                              <span class="badge bg-label-warning  p-2 mb-1">
                                                  {{ $leave->leave_type }} -
                                                  {{ $leave->leave_day_type == 1 ? 'Full Day' : ($leave->leave_day_type == 2 ? 'FN' : 'AN') }}
                                                  -

                                                  {{ $leave->leave_status == 0 ? 'Pending' : ($leave->leave_status == 1 ? 'Approved' : 'Rejected') }}
                                                  {{--  {{ $leave->leave_action_by_name }}  --}}
                                              </span><br>
                                          @endif
                                      @endforeach

                                      @foreach ($movements as $movement)
                                          @php
                                              $start = date('Y-m-d', strtotime($movement->start_date));
                                              $end = date('Y-m-d', strtotime($movement->end_date));

                                          @endphp
                                          @if ($item['date'] >= $start && $item['date'] <= $end && $movement->mov_user_id == $data['user_id'])
                                              @php
                                                  $late_flag++;
                                                  array_push($array_excemption_dates, $item['date']);
                                              @endphp
                                              <span class="badge bg-label-dark p-2 mb-1">
                                                  {{ $movement->title }} -
                                                  {{--  {{ $movement->type }} -  --}}

                                                  {{ $movement->movement_status == 0 ? 'Pending' : ($movement->movement_status == 1 ? 'Approved' : 'Rejected') }}
                                              </span><br>
                                          @endif
                                      @endforeach

                                  </td>
                                  @php
                                  $uniqueData = array_unique($array_excemption_dates);
                                      // if ($late_flag == 0) {
                                     //     $lateby = $lateby + $data['LateBy'];
                                     //     $earlyby = $earlyby + $data['EarlyBy'];
                                    //  }
                                  @endphp



                                </tr>
                            @endforeach
                            {{--  <tr>
                                <td>lateby {{ print_r( $uniqueData ) }}</td>
                                <td>lateby {{ $earlyby }}</td>
                            </tr>  --}}
                            @php
                            $total_duration = 0;
                            $lateby = 0;
                            $earlyby = 0;
                            $remaining_grace = 0;
                            $OT=0;
                            $totalDuration=0;
                            @endphp
                            @foreach ($attendance_data as $data)
                              @php
                              //$lateby = $lateby + $data['LateBy'];
                             // $earlyby = $earlyby + $data['EarlyBy'];
                              $startTime = new DateTime($data['InTime']);
                              $endTime = new DateTime($data['OutTime']);

                              // Calculate the difference
                              $interval = $startTime->diff($endTime);

                              // Convert the difference to minutes
                              $minutes = ($interval->h * 60) + $interval->i;
                              $minutes += ($interval->d * 24 * 60);

                              if($minutes >= 480){
                                $OT = $OT+( $minutes - 480);
                            }

                            if($minutes < 480){

                              if($data['EarlyBy'] == 0 && $data['LateBy'] > 0){
                                $actual_lateby = 480-$minutes;
                              }
                              else{
                                $actual_lateby = $data['LateBy'];
                              }
                            //  $lateby = $lateby + $data['LateBy'];
                            $lateby = $lateby + $actual_lateby;

                            }
                            if($minutes < 480){
                              if($data['LateBy'] == 0 && $data['EarlyBy'] > 0){
                                $actual_earlyby = 480-$minutes;
                              }
                              else{
                                $actual_earlyby = $data['EarlyBy'];
                              }

                             // $earlyby = $earlyby + $data['EarlyBy'];
                              $earlyby = $earlyby + $actual_earlyby;
                            }


                            $totalDuration =   $totalDuration + ($minutes)
                              @endphp
                            @foreach ($uniqueData as $item)
                                        @if ($item == $data['date'])

                                        @php
                                        //$lateby = $lateby -$data['LateBy'];
                                        //$earlyby = $earlyby -$data['EarlyBy'];
                                        $startTime = new DateTime($data['InTime']);
                                        $endTime = new DateTime($data['OutTime']);

                                        // Calculate the difference
                                        $interval = $startTime->diff($endTime);

                                        // Convert the difference to minutes
                                        $minutes = ($interval->h * 60) + $interval->i;
                                        $minutes += ($interval->d * 24 * 60);

                                                  if($minutes < 480){

                                                    if($data['EarlyBy'] == 0 && $data['LateBy'] > 0){
                                                      $actual_lateby = 480-$minutes;
                                                    }
                                                    else{
                                                      $actual_lateby = $data['LateBy'];
                                                    }
                                                  //  $lateby = $lateby + $data['LateBy'];
                                                  $lateby = $lateby - $actual_lateby;

                                                  }


                                                 // $lateby = $lateby -$data['LateBy'];
                                                 if($minutes < 480){
                                                  if($data['LateBy'] == 0 && $data['EarlyBy'] > 0){
                                                    $actual_earlyby = 480-$minutes;
                                                  }
                                                  else{
                                                    $actual_earlyby = $data['EarlyBy'];
                                                  }

                                                 // $earlyby = $earlyby + $data['EarlyBy'];
                                                  $earlyby = $earlyby - $actual_earlyby;
                                                }
                                                 // $earlyby = $earlyby -$data['EarlyBy'];
                                        @endphp
                                        @endif
                            @endforeach
                            @endforeach
                            @php
                                $LateByHours = floor($lateby / 60) . ' hours ' . $lateby % 60 . ' minutes';
                                $EarlyByHours = floor($earlyby / 60) . ' hours ' . $earlyby % 60 . ' minutes';
                                $remaining = $grace - ($lateby + $earlyby);
                            @endphp
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Orders -->
        {{--  <div class="col-lg-3 col-6 mb-4">
            <div class="card bg-gradient-primary">
                <div class="card-body text-center">
                    <div class="badge rounded-pill p-2 bg-label-success mb-2"><i class="ti ti-chart-pie-2 ti-sm"></i></div>
                    <h5 class="card-title mb-2"><small>Minutes<br></small>{{ $DurationMinutes }}</h5>
                    <h5 class="card-title mb-2"><small>Hours<br></small>{{ $durationHours }}</h5>
                    <small class="badge rounded-pill p-2 bg-label-danger mb-2">Total Duration</small>
                </div>
            </div>
        </div>

        <!-- Reviews -->
        <div class="col-lg-3 col-6 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="badge rounded-pill p-2 bg-label-danger mb-2"><i class="ti ti ti-clock ti-sm"></i></div>

                    <h5 class="card-title mb-2"><small>Minutes<br></small>{{ $lateby }}</h5>
                    <h5 class="card-title mb-2"><small>Hours<br></small>{{ $LateByHours }}</h5>
                    <small class="badge rounded-pill p-2 bg-label-danger mb-2">Late By</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="badge rounded-pill p-2 bg-label-warning mb-2"><i class="ti ti ti-clock ti-sm"></i></div>

                    <h5 class="card-title mb-2"><small>Minutes<br></small>{{ $earlyby }}</h5>
                    <h5 class="card-title mb-2"><small>Hours<br></small>{{ $EarlyByHours }}</h5>
                    <small class="badge rounded-pill p-2 bg-label-danger mb-2">Early Exit By</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="badge rounded-pill p-2 bg-label-primary mb-2"><i class="ti ti-alert-triangle ti-sm"></i>
                    </div>

                    <h5 class="card-title mb-2"><small>Total Minutes<br></small>{{ $grace }}</h5>
                    <h5 class="card-title mb-2"><small>Remaining Minutes<br></small>{{ $remaining }}</h5>
                    <small class="badge rounded-pill p-2 bg-label-danger mb-2">Grace Period</small>
                </div>
            </div>
        </div>  --}}

        <div class="col-xl-12 col-md-12 order-2 order-lg-1">
          <div class="card">
              <div class="card-header d-flex justify-content-between">
                  <div class="card-title mb-0">
                      <h5 class="mb-0">Consolidation Report</h5>
                      <small class="text-muted">Attendance</small>
                  </div>
                  <div class="dropdown">
                      <button class="btn p-0" type="button" id="sourceVisits" data-bs-toggle="dropdown"
                          aria-haspopup="true" aria-expanded="false">
                          <i class="ti ti-dots-vertical ti-sm text-muted"></i>
                      </button>

                  </div>
              </div>
              <div class="card-body">
                  <ul class="list-unstyled mb-0">
                      <li class="mb-3 pb-1">
                          <div class="d-flex align-items-start">
                              <div class="badge bg-label-primary p-2 me-3 rounded"><i class="ti ti-shadow ti-sm"></i>
                              </div>
                              <div class="d-flex justify-content-between w-100 flex-wrap gap-2">
                                  <div class="me-2">
                                      <h6 class="mb-0">Total Number of Days</h6>
                                      <small class="text-muted">In Days</small>
                                  </div>
                                  <div class="d-flex align-items-center">
                                      <p class="mb-0">{{ $numberof_days}}</p>

                                  </div>
                              </div>
                          </div>
                      </li>
                      <li class="mb-3 pb-1">
                          <div class="d-flex align-items-start">
                              <div class="badge bg-label-secondary p-2 me-3 rounded"><i class="ti ti-globe ti-sm"></i>
                              </div>
                              <div class="d-flex justify-content-between w-100 flex-wrap gap-2">
                                  <div class="me-2">
                                      <h6 class="mb-0">Total Working Days</h6>
                                      <small class="text-muted">In Days</small>
                                  </div>
                                  <div class="d-flex align-items-center">
                                      <p class="mb-0">{{$total_working_days}}</p>

                                  </div>
                              </div>
                          </div>
                      </li>
                      <li class="mb-3 pb-1">
                          <div class="d-flex align-items-start">
                              <div class="badge bg-label-warning p-2 me-3 rounded"><i class="ti ti-mail ti-sm"></i>
                              </div>
                              <div class="d-flex justify-content-between w-100 flex-wrap gap-2">
                                  <div class="me-2">
                                      <h6 class="mb-0">Number of Leaves</h6>
                                      <small class="text-muted">In Days</small>
                                  </div>
                                  <div class="d-flex align-items-center">
                                      <p class="mb-0">{{$number_of_leaves}}</p>

                                  </div>
                              </div>
                          </div>
                      </li>
                      <li class="mb-3 pb-1">
                          <div class="d-flex align-items-start">
                              <div class="badge bg-label-primary p-2 me-3 rounded"><i
                                      class="ti ti-external-link ti-sm"></i></div>
                              <div class="d-flex justify-content-between w-100 flex-wrap gap-2">
                                  <div class="me-2">
                                      <h6 class="mb-0">Net Working Days by the Employee</h6>
                                      <small class="text-muted">In Days</small>
                                  </div>
                                  <div class="d-flex align-items-center">
                                      <p class="mb-0">{{$total_working_days_by_employee}}</p>

                                  </div>
                              </div>
                          </div>
                      </li>
                      <li class="mb-3 pb-1">
                          <div class="d-flex align-items-start">
                              <div class="badge bg-label-success p-2 me-3 rounded"><i class="ti ti ti-clock ti-sm"></i>
                              </div>
                              <div class="d-flex justify-content-between w-100 flex-wrap gap-2">
                                  <div class="me-2">
                                      <h6 class="mb-0">Total Work Hours</h6>
                                      <small class="text-muted">In Minutes</small>
                                  </div>
                                  <div class="d-flex align-items-center">
                                      <p class="mb-0">{{$DurationMinutes}}</p>
                                  </div>
                              </div>
                          </div>
                      </li>
                      <li class="mb-2">
                          <div class="d-flex align-items-start">
                              <div class="badge bg-label-primary p-2 me-3 rounded"><i class="ti ti ti-clock ti-sm"></i>
                              </div>
                              <div class="d-flex justify-content-between w-100 flex-wrap gap-2">
                                  <div class="me-2">
                                      <h6 class="mb-0">Average Work Hours/Day</h6>
                                      <small class="text-muted">In Minutes</small>
                                  </div>
                                  <div class="d-flex align-items-center">
                                      <p class="mb-0">@if($DurationMinutes > 0){{  round($DurationMinutes/$total_working_days_by_employee) }}@endif</p>

                                  </div>
                              </div>
                          </div>
                      </li>

                      <li class="mb-2">
                        <div class="d-flex align-items-start">
                            <div class="badge bg-label-danger p-2 me-3 rounded"><i class="ti ti-run ti-sm"></i>
                            </div>
                            <div class="d-flex justify-content-between w-100 flex-wrap gap-2">
                                <div class="me-2">
                                    <h6 class="mb-0">Total Late Minutes</h6>
                                    <small class="text-muted">In Minutes</small>
                                </div>
                                <div class="d-flex align-items-center">
                                    <p class="mb-0">{{ $lateby }}</p>

                                </div>
                            </div>
                        </div>
                    </li>

                    <li class="mb-2">
                      <div class="d-flex align-items-start">
                          <div class="badge bg-label-danger p-2 me-3 rounded"><i class="ti ti-run ti-sm"></i>
                          </div>
                          <div class="d-flex justify-content-between w-100 flex-wrap gap-2">
                              <div class="me-2">
                                  <h6 class="mb-0">Total Early Exit</h6>
                                  <small class="text-muted">In Minutes</small>
                              </div>
                              <div class="d-flex align-items-center">
                                  <p class="mb-0">{{ $earlyby }}</p>

                              </div>
                          </div>
                      </div>
                  </li>

                  <li class="mb-2">
                    <div class="d-flex align-items-start">
                        <div class="badge bg-label-success p-2 me-3 rounded"><i class="ti ti-star ti-sm"></i>
                        </div>
                        <div class="d-flex justify-content-between w-100 flex-wrap gap-2">
                            <div class="me-2">
                                <h6 class="mb-0">Total Grace Period Avialable</h6>
                                <small class="text-muted">In Minutes</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <p class="mb-0">{{ $grace }}</p>

                            </div>
                        </div>
                    </div>
                </li>

                <li class="mb-2">
                  <div class="d-flex align-items-start">
                      <div class="badge bg-label-warning p-2 me-3 rounded"><i class="ti ti-alert-triangle ti-sm"></i>
                      </div>
                      <div class="d-flex justify-content-between w-100 flex-wrap gap-2">
                          <div class="me-2">
                              <h6 class="mb-0">Availed Grace Period</h6>
                              <small class="text-muted">In Minutes</small>
                          </div>
                          <div class="d-flex align-items-center">
                              <p class="mb-0">{{ $earlyby+$lateby }}</p>

                          </div>
                      </div>
                  </div>
              </li>

              <li class="mb-2">
                  <div class="d-flex align-items-start">
                      <div class="badge bg-label-warning p-2 me-3 rounded"><i class="ti ti-alert-triangle ti-sm"></i>
                      </div>
                      <div class="d-flex justify-content-between w-100 flex-wrap gap-2">
                          <div class="me-2">
                              <h6 class="mb-0">Balance Grace Period</h6>
                              <small class="text-muted">In Minutes</small>
                          </div>
                          <div class="d-flex align-items-center">
                              <p class="mb-0">{{ $remaining }}</p>

                          </div>
                      </div>
                  </div>
              </li>

              <li class="mb-2">
                <div class="d-flex align-items-start">
                    <div class="badge bg-label-warning p-2 me-3 rounded"><i class="ti ti ti-clock ti-sm"></i>
                    </div>
                    <div class="d-flex justify-content-between w-100 flex-wrap gap-2">
                        <div class="me-2">
                            <h6 class="mb-0">Total Over Time</h6>
                            <small class="text-muted">In Minutes</small>
                        </div>
                        <div class="d-flex align-items-center">
                            <p class="mb-0">{{$OT}}</p>

                        </div>
                    </div>
                </div>
            </li>

                  </ul>
              </div>
          </div>
      </div>


    </div>
    <div class="card app-calendar-wrapper">
        <div class="row mb-2 g-0">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 select2-primary">
                            <label class="form-label" for="reportType">Report Type</label>
                            <select id="reportType" class="select2 form-select">
                                <option value="">Select All</option>

                                <option value='1' selected>Attendance</option>
                                <option value='2'>Movement</option>
                                <option value='3'>Leave</option>
                                <option value='4'>Miss Punch</option>

                            </select>
                        </div>
                        <div class="mb-3 col-3">
                            <label for="fromDate" class="form-label">From</label>
                            <input type="text" class="form-control datepicker" id="fromDate" name="fromDate"
                                placeholder="MM/DD/YYYY" class="form-control" />

                        </div>

                        <div class="mb-3 col-3">
                            <label for="toDate" class="form-label">To</label>
                            <input type="text" class="form-control datepicker" id="toDate" name="toDate"
                                placeholder="MM/DD/YYYY" class="form-control" />

                        </div>

                        <div class="col-md">
                            <small class="text-light fw-medium d-block">Report Type</small>
                            <div class="form-check form-check-inline mt-3">
                                <input class="form-check-input" type="radio" checked name="viewTypeOptinon"
                                    id="viewTypeOptinon" value="html" />
                                <label class="form-check-label" for="inlineRadio1"><i class="ti ti-list ti-xs"></i>
                                    Basic</label>
                            </div>

                            <div class="form-check form-check-inline mt-3 detailed-radio">
                              <input class="form-check-input" type="radio"  name="viewTypeOptinon"
                                  id="viewTypeOptinon" value="monitor" />
                              <label class="form-check-label" for="inlineRadio1"><i class="ti ti-list ti-xs"></i>
                                  Detailed</label>
                          </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="viewTypeOptinon"
                                    id="viewTypeOptinon" value="pdf" />
                                <label class="form-check-label" for="inlineRadio2"><i class="ti ti-file-text"></i>
                                    PDF</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="viewTypeOptinon"
                                    id="viewTypeOptinon" value="excel" />
                                <label class="form-check-label" for="inlineRadio3"><i
                                        class="ti ti-file-spreadsheet ti-xs"></i> Excel</label>
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-primary btn-toggle-sidebar" id="report">
                        <i class="ti ti-plus me-1"></i>
                        <span class="align-middle">Get Report</span>
                    </button>
                </div>
            </div>



        </div>
        <div class="row g-0">
            <!-- Calendar Sidebar -->
            <div class="col app-calendar-sidebar" id="app-calendar-sidebar">
                <div class="border-bottom p-4 my-sm-0 mb-3">
                    <div class="d-grid">
                        {{--  <button class="btn btn-primary btn-toggle-sidebar" data-bs-toggle="offcanvas" data-bs-target="#addEventSidebar" aria-controls="addEventSidebar">
            <i class="ti ti-plus me-1"></i>
            <span class="align-middle">Get Report</span>
          </button>  --}}

                    </div>
                </div>
                <div class="p-3">
                    <!-- inline calendar (flatpicker) -->
                    <div class="inline-calendar"></div>

                    <hr class="container-m-nx mb-4 mt-3">

                    <!-- Filter -->
                    <div class="mb-3 ms-3">
                        <small class="text-small text-muted text-uppercase align-middle">Filter</small>
                    </div>

                    <div class="form-check form-check-secondary mb-2 ms-3">
                        <input class="form-check-input select-all" type="checkbox" id="selectAll" data-value="all"
                            checked>
                        <label class="form-check-label" for="selectAll">View All</label>
                    </div>

                    <div class="app-calendar-events-filter ms-3">
                        <div class="form-check form-check-primary mb-2">
                            <input class="form-check-input input-filter" type="checkbox" id="select-present"
                                data-value="present" checked>
                            <label class="form-check-label" for="select-personal">Present</label>
                        </div>
                        <div class="form-check form-check-danger mb-2">
                            <input class="form-check-input input-filter" type="checkbox" id="select-absent"
                                data-value="absent" checked>
                            <label class="form-check-label" for="select-absent">Absent</label>
                        </div>
                        <div class="form-check form-check-dark mb-2">
                            <input class="form-check-input input-filter" type="checkbox" id="select-requested"
                                data-value="requested" checked>
                            <label class="form-check-label" for="select-requested">Requested</label>
                        </div>
                        <div class="form-check form-check-warning mb-2">
                            <input class="form-check-input input-filter" type="checkbox" id="select-rejected"
                                data-value="rejected" checked>
                            <label class="form-check-label" for="select-rejected">Rejected</label>
                        </div>
                        <div class="form-check form-check-success mb-2">
                            <input class="form-check-input input-filter" type="checkbox" id="select-approved"
                                data-value="approved" checked>
                            <label class="form-check-label" for="select-approved">Approved</label>
                        </div>
                        <div class="form-check form-check-danger mb-2">
                            <input class="form-check-input input-filter" type="checkbox" id="select-holiday"
                                data-value="holiday" checked>
                            <label class="form-check-label" for="select-holiday">Holiday</label>
                        </div>

                    </div>
                </div>
            </div>
            <!-- /Calendar Sidebar -->

            <!-- Calendar & Modal -->
            <div class="col app-calendar-content">
                <div class="card shadow-none border-0">
                    <div class="card-body pb-0">
                        <!-- FullCalendar -->
                        <div id="calendar"></div>
                    </div>
                </div>
                <div class="app-overlay"></div>
                <!-- FullCalendar Offcanvas -->
                <div class="offcanvas offcanvas-end event-sidebar" tabindex="-1" id="addEventSidebar"
                    aria-labelledby="addEventSidebarLabel">
                    <div class="offcanvas-header my-1">
                        <h5 class="offcanvas-title" id="addEventSidebarLabel">Attendance Report</h5>
                        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                            aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body pt-0">
                        <form class="event-form pt-0" id="eventForm" onsubmit="return false">

                            <div class="mb-3">
                                <label class="form-label" for="eventStartDate">Start Date</label>
                                <input type="text" class="form-control" id="eventStartDate" name="eventStartDate"
                                    placeholder="Start Date" />
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="eventEndDate">End Date</label>
                                <input type="text" class="form-control" id="eventEndDate" name="eventEndDate"
                                    placeholder="End Date" />
                            </div>



                            <div class="mb-3 d-flex justify-content-sm-between justify-content-start my-4">
                                <div>
                                    <button type="submit" class="btn btn-primary btn-add-event me-sm-3 me-1">Get</button>
                                    <button type="reset" class="btn btn-label-secondary btn-cancel me-sm-0 me-1"
                                        data-bs-dismiss="offcanvas">Cancel</button>
                                    <button type="reset" class="btn btn-label-secondary btn-delete-event me-sm-0 me-1"
                                        data-bs-dismiss="offcanvas">Cancel</button>

                                </div>

                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- /Calendar & Modal -->
        </div>
    </div>
    <!-- Modal -->
    @include('_partials/_modals/modal-attendance')
    @include('_partials/_modals/modal-attendance-report')
    @include('_partials/_modals/modal-movement-report-view')
    @include('_partials/_modals/modal-leave-report-view')
    @include('_partials/_modals/modal-misspunch-report-view')
    <!-- /Modal -->
@endsection
