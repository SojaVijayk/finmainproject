@extends('layouts/layoutMaster')

@section('title', 'Designation - Master')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/jquery-timepicker/jquery-timepicker.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/dropzone/dropzone.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />

<link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-select/bootstrap-select.css')}}" />

<style>
  #loading-overlay {
    position: absolute;
    width: 100%;
    height:100%;
    left: 0;
    top: 0;
    display: none;
    align-items: center;
    background-color: #000;
    z-index: 999;
    opacity: 0.5;
}
.loading-icon{ position:absolute;border-top:2px solid #fff;border-right:2px solid #fff;border-bottom:2px solid #fff;border-left:2px solid #767676;border-radius:25px;width:25px;height:25px;margin:0 auto;position:absolute;left:50%;margin-left:-20px;top:50%;margin-top:-20px;z-index:4;-webkit-animation:spin 1s linear infinite;-moz-animation:spin 1s linear infinite;animation:spin 1s linear infinite;}
@-moz-keyframes spin { 100% { -moz-transform: rotate(360deg); } }
@-webkit-keyframes spin { 100% { -webkit-transform: rotate(360deg); } }
@keyframes spin { 100% { -webkit-transform: rotate(360deg); transform:rotate(360deg); } }
</style>
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>

<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js')}}" ></script>
<script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
<script src="{{asset('assets/vendor/libs/jquery-timepicker/jquery-timepicker.js')}}"></script>
<script src="{{asset('assets/vendor/libs/dropzone/dropzone.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bootstrap-select/bootstrap-select.js')}}"></script>

@endsection

@section('page-script')
<script src="{{asset('assets/js/forms-file-upload.js')}}"></script>
<script>


$(function () {
  $(".selectpicker").selectpicker();
  var dataTablePermissions = $('.datatables-designation'),
    dt_permission,
    permissionList = baseUrl + 'movement/list';


    $(".datepicker").datepicker({
      autoclose: true ,
      format:'dd/mm/yyyy',
      });
       // Select2 Country
  var select2 = $('.select2');
  if (select2.length) {
    select2.each(function () {
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

  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  // Users List datatable





// Add/Edit designation form validation
document.addEventListener('DOMContentLoaded', function (e) {
  (function () {
    FormValidation.formValidation(document.getElementById('designationForm'), {
      fields: {
        employment_type: {
          validators: {
            notEmpty: {
              message: 'Please select Employment Type'
            }
          }
        },
        modalDesignationName: {
          validators: {
            notEmpty: {
              message: 'Please enter designation name'
            }
          }
        }
      },
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({
          // Use this for enabling/changing valid/invalid class
          // eleInvalidClass: '',
          eleValidClass: '',
          rowSelector: '.col-sm-9'
        }),
        submitButton: new FormValidation.plugins.SubmitButton(),
        // Submit the form when all fields are valid
        // defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
        autoFocus: new FormValidation.plugins.AutoFocus()
      }
    });
  })();
});

(function () {
  // On edit permission click, update text

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



    $("body").on("click","#import", function (e) {
      e.preventDefault();
      $("#loading-overlay").show();

      var fd = new FormData();
      var files = $('input[type=file]')[0].files[0];
      fd.append('file',files);
      $.ajaxSetup({
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
              }
          })
          $.ajax({
              type: "POST",
              url: '/attendance/import',
              data: fd,
              contentType: false,
              processData: false,
              // dataType: 'json',
              success: function (data) {
                $("#loading-overlay").hide();
                Swal.fire({
                  icon: 'success',
                  title: `Successfully Imported!`,
                  {{--  text: `Designation ${status} Successfully.`,  --}}
                  customClass: {
                    confirmButton: 'btn btn-success'
                  }
                });

              },
              error: function(data){
                $("#loading-overlay").hide();
                Swal.fire({
                  title: 'Oh Sorry!',
                  text: 'Something went wrong',
                  icon: 'error',
                  customClass: {
                    confirmButton: 'btn btn-success'
                  }
                });
              }
          });
  });
  $("body").on("click","#leave_report", function (e) {
    e.preventDefault();
    var  employeeList =  $("#user_list").val();
    $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
  })
  $.ajax({
    type: "GET",

    url: '/attendance/monitor/'+employeeList,
    success: function (data) {

      $('#ajaxResponse').html(data)
    },
    error: function(data){

      Swal.fire({
        icon: 'warning',
        title: `Something Went wrong!`,
        {{--  text: `Designation ${status} Successfully.`,  --}}
        customClass: {
          confirmButton: 'btn btn-warning'
        }
      });

    }
});

  });

  $("body").on("click","#report", function (e) {
    e.preventDefault();

      var  fromDate =  $("#fromDate").val();
      var  toDate =  $("#toDate").val();
      var  employeeList =  $("#employeeList").val();
      {{--  var  view_type =  $("#viewTypeOptinon").val();  --}}
      var  view_type = $('input[name="viewTypeOptinon"]:checked').val();
      var  reportType =  $("#reportType").val();



      $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    })

    if(reportType ==1){

      if(view_type == 'excel' || view_type == 'pdf'){

        $.ajax({
             data:  {
              fromDate:fromDate,
                toDate:toDate,
                type:'2',
                view_type:view_type,
                employeeList:employeeList,
                "_token": "{{ csrf_token() }}",
            },
              url: `${baseUrl}downloadBulk`,
              type: 'POST',
              xhrFields:{
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
             fromDate:fromDate,
               toDate:toDate,
               type:'2',
               view_type:view_type,
               employeeList:employeeList,
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
             fromDate:fromDate,
               toDate:toDate,
               type:'2',
               view_type:view_type,
               employeeList:employeeList,
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

        else{
          $.ajax({
            data:  {
             fromDate:fromDate,
               toDate:toDate,
               type:'2',
               view_type:view_type,
               employeeList:employeeList,
               "_token": "{{ csrf_token() }}",
           },
             url: `${baseUrl}downloadBulk`,
             type: 'POST',

           success: function(data) {
            var tbody='';
            data.list.forEach((item, index) => {
              var totalDuration = 0;
              var extraTime = 0;
              if((item.InTime != null && item.InTime!='') && (item.OutTime != null && item.OutTime != '')){
                var startDate = item.InTime; // Format: YYYY-MM-DDTHH:MM:SS
              var endDate = item.OutTime; // Format: YYYY-MM-DDTHH:MM:SS

              var duration = calculateDuration(item.InTime, item.OutTime);
              var hours= duration.hours;
             var  minutes= duration.minutes;
              totalDuration = (hours*60)+minutes;
              if(totalDuration > 480){
                {{--  extraTime = totalDuration-480;  --}}
              }

              }



              tbody=tbody+'<tr><td>'+item.name+'</td><td>'+item.date+'</td>'+
               {{--  ' <td> <span class="text-'+(item.in_time <= '09:30'  ? "success" : 'warning')+'">'+(item.in_time != null ? item.in_time : '')+'</span></td>'+
               '<td><span class="text-'+(item.out_time >= '17:30'  ? "success" : 'warning')+'">'+(item.out_time != null ? item.out_time : '')+'</span></td>'+  --}}
               '<td><span class="text-success"><strong>IN</strong></span> : '+item.InTime+'<br><span class="text-danger"><strong>Out</strong></span> : '+(item.OutTime != item.InTime ? item.OutTime : 'No Records')+
              '<td>'+(item.LateBy >0 ? item.LateBy : "-")+'</td><td>'+(item.EarlyBy >0 ? item.EarlyBy : "-")+'</td><td>'+(totalDuration >0 ? totalDuration : "-")+'</td>'+

               '';
                var leave= '';
                var leave_status= '';
                var leave_day= '';

                var mov_title= '';
                var mov_loc= '';
                var mov_date= '';
                var mov_type= '';
                var mov_status= '';


                var miss_date= '';
                var miss_type= '';
                var miss_in= '';
                var miss_out= '';
                var miss_status= '';


                if((item.leave_type != '') && (item.leave_type != null)){
                  leave = item.leave_type;
                  leave_status=(item.leave_status == 1 ? 'Approved' : (item.leave_status == 2 ? 'Rejected' : 'Pending'));
                  leave_day= (item.leave_day_type == 1 ? 'Full Day' : (item.leave_day_type == 2 ? 'AN' : 'FN'));

                  tbody=tbody+'<td> Leave Details (Leave Type : '+leave+' - Day Type :'+leave_day+ ' - Status:' + leave_status+ ')</td>';
                }



                if((item.movement_status != '') && (item.movement_status != null)){
                  mov_type = item.type;
                  mov_title= item.title;
                  mov_loc= item.location;
                  mov_date = item.start_date+' - '+item.start_time+' to '+item.end_date+' - '+item.end_time;
                  mov_status=(item.mov_status == 1 ? 'Approved' : (item.mov_status == 2 ? 'Rejected' : 'Pending'));
                  tbody=tbody+'<td> Movement Details ( '+mov_type+' -  '+mov_title+ ' Duraton : '+mov_date+' - Status:' + mov_status+ ')</td>';

                }

                if((item.miss_status != '') && (item.miss_status != null)){
                  miss_type = item.miss_type;

                  miss_date = item.miss_date+' - In Time '+item.checkinTime+' Out Time '+item.checkoutTime;
                  miss_status=(item.miss_status == 1 ? 'Approved' : (item.miss_status == 2 ? 'Rejected' : 'Pending'));
                  tbody=tbody+'<td> Miss Punch Details ( '+miss_type+' - Duraton : '+miss_date+' - Status:' + miss_status+ ')</td>';

                }

                if(((item.miss_status == '') || (item.miss_status == null)) && ((item.movement_status == '') || (item.movement_status == null)) && ((item.leave_type == '') || (item.leave_type == null)) ){
                  tbody= tbody+'<td></td>';
                }

                tbody=tbody+'</tr>';

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
    else if(reportType ==2){
      if(view_type == 'excel' || view_type == 'pdf'){

        $.ajax({
             data:  {
              fromDate:fromDate,
                toDate:toDate,
                type:'2',
                view_type:view_type,
                employeeList:employeeList,
                "_token": "{{ csrf_token() }}",
            },
              url: `${baseUrl}movement/downloadBulk`,
              type: 'POST',
              xhrFields:{
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
        else{
          $.ajax({
            data:  {
             fromDate:fromDate,
               toDate:toDate,
               type:'2',
               view_type:view_type,
               employeeList:employeeList,
               "_token": "{{ csrf_token() }}",
           },
             url: `${baseUrl}movement/downloadBulk`,
             type: 'POST',

           success: function(data) {
            var tbody='';
            data.list.forEach((item, index) => {
              tbody=tbody+'<tr><td>'+item.name+'</td><td>'+item.start_date+'</td><td>'+item.start_time+'</td><td>'+item.end_date+'</td><td>'+item.end_time+
                '<td>'+item.title+'</td><td>'+item.type+'</td><td>'+item.location+'</td><td>'+item.description+'</td><td>'+item.requested_at+'</td>'+
                '<td>'+(item.status == 0 ? '<span class="badge bg-secondary">Pending</span>' : (item.status == 1 ? '<span class="badge bg-success">Aproved</span>' : '<span class="badge bg-danger">Rejected</span>' ))+'</td>'+
                '<td>'+item.action_by_name+'</td><td>'+item.action_at+'</td>';
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
   else if(reportType ==3){
      if(view_type == 'excel' || view_type == 'pdf'){

        $.ajax({
             data:  {
              fromDate:fromDate,
                toDate:toDate,
                type:'2',
                view_type:view_type,
                employeeList:employeeList,
                "_token": "{{ csrf_token() }}",
            },
              url: `${baseUrl}leave/downloadBulk`,
              type: 'POST',
              xhrFields:{
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
        else{




          $.ajax({
            data:  {
             fromDate:fromDate,
               toDate:toDate,
               type:'2',
               view_type:view_type,
               employeeList:employeeList,
               "_token": "{{ csrf_token() }}",
           },
             url: `${baseUrl}leave/downloadBulk`,
             type: 'POST',

           success: function(data) {
            var tbody='';
            var tbody_sub='';
            data.list.forEach((item, index) => {
              {{--  tbody=tbody+'<tr><td>'+item.name+'</td><td>'+item.from+'</td><td>'+item.to+'</td><td>'+item.duration+'</td><td>'+item.requested_at+'</td><td>'+item.status+'</td>';  --}}



                  tbody_sub=tbody_sub+'<tr><td>'+item.name+'</td><td>'+item.leave_type+'</td><td>'+item.date+'</td><td>'+(item.leave_day_type == 1 ? 'Full Day' : item.leave_day_type == 2 ? 'FN' : 'AN')+'</td>'+
                    '<td>'+item.requested_at+'</td><td>'+(item.status == 0 ? '<span class="text-nowrap badge bg-label-secondary">Pending</span></td>' : (item.status == 1 ? '<span class="badge bg-label-success">Approved</span><br>Remark : '+item.remark+' ': '<span class="badge bg-label-danger">Rejected</span> <br>Remark : '+item.remark+ '</td>'))+'<td>'+item.action_by_name+'</td><td>'+item.action_at+'</td></tr>';



              });
              $('#LeaveModal').modal('show');
            $(".datatables-leave-list #dataList").html(tbody_sub);

           },
           error: function(data) {
               //
           }
       });

        }
    }
    else if(reportType ==4){
      if(view_type == 'excel' || view_type == 'pdf'){

        $.ajax({
             data:  {
              fromDate:fromDate,
                toDate:toDate,
                type:'2',
                view_type:view_type,
                employeeList:employeeList,
                "_token": "{{ csrf_token() }}",
            },
              url: `${baseUrl}misspunch/downloadBulk`,
              type: 'POST',
              xhrFields:{
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
        else{



          $.ajax({
            data:  {
             fromDate:fromDate,
               toDate:toDate,
               type:'2',
               view_type:view_type,
               employeeList:employeeList,
               "_token": "{{ csrf_token() }}",
           },
             url: `${baseUrl}misspunch/downloadBulk`,
             type: 'POST',

           success: function(data) {
            var tbody='';
            data.list.forEach((item, index) => {
              tbody=tbody+'<tr><td>'+item.name+'</td><td>'+item.date+'</td><td>'+(item.type == 1 ? "Checkin" : item.type == 2 ? "Checkout" : "Checkin&Checkout")+'</td><td>'+item.checkinTime+'</td><td>'+item.checkoutTime+
                '<td>'+item.description+'</td><td>'+item.requested_at+'</td>'+
                '<td>'+(item.status == 0 ? '<span class="badge bg-secondary">Pending</span>' : (item.status == 1 ? '<span class="badge bg-success">Aproved</span>' : '<span class="badge bg-danger">Rejected</span>' ))+'</td>'+
                '<td>'+item.action_by_name+'</td><td>'+item.action_at+'</td>';
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











})();


  // Filter form control to default size
  // ? setTimeout used for multilingual table initialization
  setTimeout(() => {
    $('.dataTables_filter .form-control').removeClass('form-control-sm');
    $('.dataTables_length .form-select').removeClass('form-select-sm');
  }, 300);
});
</script>



@endsection

@section('content')
<h4 class="fw-semibold mb-4">Attendance Management</h4>


<div class="row">
  <div id="loading-overlay">
    <div class="loading-icon"></div>
</div>
  <div class="col">
    @can('attendance-management')
    <h6 class="mt-4">Import Attendance & Generate Report </h6>
    @endcan
    @can('team-attendance-management')
    <h6 class="mt-4"> Generate Report </h6>
    @endcan
    <div class="card mb-3">
      <div class="card-header">
        <ul class="nav nav-pills nav-fill  card-header-tabs" role="tablist">
          <li class="nav-item  ">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#form-tabs-personal" role="tab" aria-selected="true"><i class="tf-icons ti ti-home ti-xs me-1"></i>Generate Attendance / Leave / Movement Report</button>
          </li>
          {{--  <li class="nav-item">
            <button class="nav-link " data-bs-toggle="tab" data-bs-target="#form-tabs-leave" role="tab" aria-selected="false"><i class="tf-icons ti ti-user ti-xs me-1"></i>Employee Leave Monitor</button>
          </li>  --}}
          @can('attendance-management')
          <li class="nav-item">
            <button class="nav-link " data-bs-toggle="tab" data-bs-target="#form-tabs-account" role="tab" aria-selected="false"><i class="tf-icons ti ti-calendar ti-xs me-1"></i>Attendance Import / Update</button>
          </li>

          @endcan

        </ul>
      </div>

      <div class="tab-content">
        <div class="tab-pane fade active show" id="form-tabs-personal" role="tabpanel">
          {{--  <form>  --}}
            <div class="row g-3">
              <div class="col-md-4 select2-primary">
                <label class="form-label" for="reportType">Report Type</label>
                <select id="reportType" class="select2 form-select" >
                  {{--  <option value="">Select All</option>  --}}

                  <option value='1' selected>Attendance</option>
                  <option value='2' >Movement</option>
                  <option value='3' >Leave</option>
                  <option value='4' >Miss punch</option>

                </select>
              </div>
              <div class="col-md-4">
                <label for="fromDate" class="form-label">From</label>
            <input type="text" class="form-control datepicker" id="fromDate" name="fromDate" placeholder="MM/DD/YYYY" class="form-control" />

              </div>
              <div class="col-md-4">
                <label for="toDate" class="form-label">To</label>
            <input type="text" class="form-control datepicker" id="toDate" name="toDate" placeholder="MM/DD/YYYY" class="form-control" />

              </div>
              {{--  <div class="col-md-4">
                <label class="form-label" for="formtabs-country">Employment Type</label>
                <select id="formtabs-country" class="select2 form-select" multiple data-allow-clear="true">
                  <option value="">Select All</option>
                  @foreach ($employment_types as $item)
                  <option value={{$item->id}}>{{$item->employment_type}}</option>
                  @endforeach


                </select>
              </div>  --}}
              <div class="col-md-4 select2-primary">
                <label class="form-label" for="employeeList">Employee</label>
                {{--  <select id="employeeList" class="select2 form-select" multiple>

                  @foreach ($employees as $item)
                  <option value={{$item->user_id}}>{{$item->name}}</option>
                  @endforeach
                </select>  --}}

                <select id="employeeList" class="selectpicker w-100" data-live-search="true" data-style="btn-default" multiple data-actions-box="true">
                  @foreach ($employees as $item)
                  <option value={{$item->user_id}}>{{$item->name}}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-md">
                <small class="text-light fw-medium d-block">View Type</small>
                <div class="form-check form-check-inline mt-3">
                  <input class="form-check-input" type="radio" checked name="viewTypeOptinon" id="viewTypeOptinon" value="html" />
                  <label class="form-check-label" for="inlineRadio1"><i class="ti ti-list ti-xs"></i> Basic</label>
                </div>

                <div class="form-check form-check-inline mt-3 detailed-radio">
                  <input class="form-check-input" type="radio" name="viewTypeOptinon" id="viewTypeOptinon" value="monitor" />
                  <label class="form-check-label" for="inlineRadio1"><i class="ti ti-list ti-xs"></i> Detailed</label>
                </div>


                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="viewTypeOptinon" id="viewTypeOptinon" value="pdf" />
                  <label class="form-check-label" for="inlineRadio2"><i class="ti ti-file-text"></i> PDF</label>
                </div>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="viewTypeOptinon" id="viewTypeOptinon" value="excel"  />
                  <label class="form-check-label" for="inlineRadio3"><i class="ti ti-file-spreadsheet ti-xs"></i> Excel</label>
                </div>
              </div>

            </div>
            <div class="pt-4">
              <button type="submit" id="report"   class="btn btn-success me-sm-3 me-1">Generate</button>
              <button type="reset" class="btn btn-label-secondary">Cancel</button>
            </div>
          {{--  </form>  --}}
        </div>
        <div class="tab-pane fade" id="form-tabs-account" role="tabpanel">

            <div class="row">
              <!-- Basic  -->
              <div class="col-12">
                <div class="card mb-4">
                  <h5 class="card-header">Attendance Import</h5>
                  <div class="card-body">
                    {{--  <form action="upload"   enctype="multipart/form-data" class="dropzone needsclick" id="dropzone-basic">
                    @csrf
                      <div class="dz-message needsclick">
                        Drop files here or click to upload
                        <span class="note needsclick">(This is just a demo dropzone. Selected files are <span class="fw-medium">not</span> actually uploaded.)</span>
                      </div>
                      <div class="fallback">
                        <input name="file" id="file" type="file" />
                      </div>

                    </form>--}}
                      <form id="attendanceImport" enctype="multipart/form-data">
                    <input type="file" name="file"
                    class="form-control">
                      </form>
                  </div>
                </div>
              </div>
            <div class="pt-4">
              <button type="submit" id="import" class="btn btn-success me-sm-3 me-1">Import</button>
              <button type="reset" class="btn btn-label-secondary">Cancel</button>

            </div>

        </div>

      </div>


      <div class="tab-pane fade  show" id="form-tabs-leave" role="tabpanel">

          <div class="row g-3">

            <div class="col-md-4 select2-primary">
              <label class="form-label" for="employeeList">Employee</label>


              <select id="user_list" class="selectpicker w-100" data-live-search="true" data-style="btn-default"  data-actions-box="true">
                @foreach ($employees as $item)
                <option value={{$item->user_id}}>{{$item->name}}</option>
                @endforeach
              </select>
            </div>

            <div class="col-md-6">
              <div class="pt-4">
                <button type="submit" id="leave_report"   class="btn btn-success me-sm-3 me-1">View</button>
                <button type="reset" class="btn btn-label-secondary">Cancel</button>
              </div>
            </div>

          </div>
          {{--  <div class="pt-4">
            <button type="submit" id="leave_report"   class="btn btn-primary me-sm-3 me-1">Generate</button>
            <button type="reset" class="btn btn-label-secondary">Cancel</button>
          </div>  --}}
          <br>
          <div id="ajaxResponse" class="m-2">
            <p class="font-weight-bold text-dark">Please select an employee and click the "View" button to access detailed statistics, including current punching details, remaining grace period, and leave balance</p>
          </div>
      </div>


    </div>
  </div>
</div>


<!-- Modal -->
@include('_partials/_modals/modal-attendance')
@include('_partials/_modals/modal-attendance-report')
@include('_partials/_modals/modal-movement-report-view')
@include('_partials/_modals/modal-misspunch-report-view')
@include('_partials/_modals/modal-leave-report-view')
<!-- /Modal -->
@endsection
