@extends('layouts/layoutMaster')

@section('title', 'Fullcalendar - Apps')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/fullcalendar/fullcalendar.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/quill/editor.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css')}}" />
@endsection

@section('page-style')
<link rel="stylesheet" href="{{asset('assets/vendor/css/pages/app-calendar.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/fullcalendar/fullcalendar.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>
<script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('assets/js/app-calendar-events.js')}}"></script>
<script src="{{asset('assets/js/app-calendar.js')}}"></script>
<script>
  $(document).ready(function () {

    function padTo2Digits(num) {
      return num.toString().padStart(2, '0');
    }

    function formatDate(date) {
      return [
        date.getFullYear(),
        padTo2Digits(date.getMonth() + 1),
        padTo2Digits(date.getDate()),
      ].join('-');
    }

      $("#eventEndDate").change(function () {

          var startDate = new Date($("#eventStartDate").val());
          var endDate = new Date($("#eventEndDate").val());
          var dateList = [];
          var dateArray = [];
          if (startDate <= endDate) {

              var currentDate = new Date(startDate);
              while (currentDate <= endDate) {
                  dateList.push(new Date(currentDate));
                  currentDate.setDate(currentDate.getDate() + 1);
              }
              console.log(dateList);
              // Display the dates
              $("#dateList").empty();
              dateList.forEach(function (date) {
                var formated_date=formatDate(date);
                dateArray.push(formated_date);
                  var date_set = '<div class="row row-bordered g-0"><div class="col-sm-12 p-4"><div class="text-light small fw-medium mb-3">Type</div><div class="switches-stacked"><label class="switch"><input type="radio" class="switch-input" name="'+formated_date+'-radio" checked /><span class="switch-toggle-slider"><span class="switch-on"></span><span class="switch-off"></span> </span><span class="switch-label">Full Day</span></label><label class="switch"><input type="radio" class="switch-input" name="'+formated_date+'-radio"  /><span class="switch-toggle-slider"><span class="switch-on"></span><span class="switch-off"></span> </span><span class="switch-label">FN</span></label><label class="switch"><input type="radio" class="switch-input" name="'+formated_date+'-radio"  /><span class="switch-toggle-slider"><span class="switch-on"></span><span class="switch-off"></span> </span><span class="switch-label">AN</span></label></div></div>';
                    $("#dateList").append("<li class='text-primary'>" + date.toDateString() + date_set+"</li>");


              });
          } else {
              alert("End Date should be greater than or equal to Start Date.");
          }
      });
  });
</script>
@endsection

@section('content')
<div class="card app-calendar-wrapper">
  <div class="row g-0">
    <!-- Calendar Sidebar -->
    <div class="col app-calendar-sidebar" id="app-calendar-sidebar">
      <div class="border-bottom p-4 my-sm-0 mb-3">
        <div class="d-grid">
          <button class="btn btn-primary btn-toggle-sidebar" data-bs-toggle="offcanvas" data-bs-target="#addEventSidebar" aria-controls="addEventSidebar">
            <i class="ti ti-plus me-1"></i>
            <span class="align-middle">Add Request</span>
          </button>
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



        <div class="app-calendar-events-filter ms-3">
          <div class="form-check form-check-success mb-2">
            <input class="form-check-input input-filter" type="checkbox" id="select-personal" data-value="personal" checked>
            <label class="form-check-label" for="select-personal">Approved Leave</label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input input-filter" type="checkbox" id="select-business" data-value="business" checked>
            <label class="form-check-label" for="select-business">Requested Leave</label>
          </div>
          <div class="form-check form-check-danger mb-2">
            <input class="form-check-input input-filter" type="checkbox" id="select-family" data-value="family" checked>
            <label class="form-check-label" for="select-family">Rejected Leave</label>
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
      <div class="offcanvas offcanvas-end event-sidebar" tabindex="-1" id="addEventSidebar" aria-labelledby="addEventSidebarLabel">
        <div class="offcanvas-header my-1">
          <h5 class="offcanvas-title" id="addEventSidebarLabel">Add Leave Request</h5>
          <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body pt-0">
          <form class="event-form pt-0" id="eventForm" onsubmit="return false">
            <div class="mb-3">
              <label class="form-label" for="eventLabel">Label</label>
              <select class="select2 select-event-label form-select" id="eventLabel" name="eventLabel">
                @foreach ($leave_types as $leave)
                <option value={{$leave->id}} >{{$leave->leave_type}}</option>
                @endforeach

              </select>
            </div>


            <div class="mb-3">
              <label class="form-label" for="eventStartDate">Start Date</label>
              <input type="text" class="form-control" id="eventStartDate" name="eventStartDate" placeholder="Start Date" />
            </div>
            <div class="mb-3">
              <label class="form-label" for="eventEndDate">End Date</label>
              <input type="text" class="form-control" id="eventEndDate" name="eventEndDate" placeholder="End Date" />
            </div>
            <div class="mb-3">
              <ul id="dateList"></ul>
            </div>



            <div class="mb-3">
              <label class="form-label" for="eventDescription">Remark</label>
              <textarea class="form-control" name="eventDescription" id="eventDescription"></textarea>
            </div>
            <div class="mb-3 d-flex justify-content-sm-between justify-content-start my-4">
              <div>
                <button type="submit" class="btn btn-primary btn-add-event me-sm-3 me-1">Add</button>
                <button type="reset" class="btn btn-label-secondary btn-cancel me-sm-0 me-1" data-bs-dismiss="offcanvas">Cancel</button>
              </div>
              <div><button class="btn btn-label-danger btn-delete-event d-none">Delete</button></div>
            </div>
          </form>
        </div>
      </div>
    </div>
    <!-- /Calendar & Modal -->
  </div>
</div>
@endsection
