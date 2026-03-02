@extends('layouts/layoutMaster')

@section('title', 'Leave - Request')

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
<script>
  $(function () {
  var dataTablePermissions = $('.datatables-designation'),
    dt_permission,
    dateArray = [],
    dateList = [];
    date_leave_type=[],
    permissionList = baseUrl + 'leave/request/list';

    function convertDateFormat(dateString) {
      // Split the input date string by the hyphen
      const [year, month, day] = dateString.split('-');

      // Rearrange into the desired format
      const formattedDate = `${day}-${month}-${year}`;

      return formattedDate;
  }


    $(".datepicker").datepicker({
      autoclose: true ,
      calendarWeeks: true,
      clearBtn: true,
      todayHighlight: true,
      orientation: "auto right",
      format:'dd/mm/yyyy',
      {{--  minDate: new Date('2024-01-05')  --}}
      {{--  minDate: new Date($('#date_start').val()),
      maxDate: new Date($('#date_end').val()),  --}}
      });
      $('.availability').hide();
      $('.date-group').hide();
      $('.remark-input').hide();
      $('.submit-designation').prop("disabled", true);
      $('.message').hide();
      var start = new Date($('#date_start').val());
      var end = new Date($('#date_end').val());

      $('#fromDate').datepicker('setStartDate', new Date(start.getFullYear(), (start.getMonth()), start.getDate(), start.getHours(), start.getMinutes()));
      $('#fromDate').datepicker('setEndDate', new Date(end.getFullYear(), (end.getMonth()), end.getDate(), end.getHours(), end.getMinutes()));

      $('#toDate').datepicker('setStartDate', new Date(start.getFullYear(), (start.getMonth()), start.getDate(), start.getHours(), start.getMinutes()));
      $('#toDate').datepicker('setEndDate', new Date(end.getFullYear(), (end.getMonth()), end.getDate(), end.getHours(), end.getMinutes()));

      {{--  $("#bs-datepicker-multidate").datepicker({ multidate: true,
        calendarWeeks: true,
        clearBtn: true,
        todayHighlight: true,
        orientation: "auto right" });  --}}


        $('#fromDate').change(function(){
          console.log(start);
          $('.remark-input').show();
          $("#dateList").empty();

          {{--  $("#toDate").val($(this).val());  --}}
          {{--  $("#toDate").trigger("keyup");  --}}

          var leavetype_val =  $('#leaveType').val();
          if(leavetype_val == 3){
            var minDate = $(this).datepicker("getDate");// Example: Today's date
          minDate.setDate(minDate.getDate() + 4 ); // Example: Set minimum date 7 days from now
          $('#toDate').datepicker('setStartDate', minDate);
          }
          else{
            $("#toDate").val($(this).val()).change();
            {{--  var minDate = new Date(); // Example: Today's date  --}}
            var minDate = $(this).datepicker("getDate"); // Example: Today's date
            minDate.setDate(minDate.getDate());
            $('#toDate').datepicker('setStartDate', minDate);
          }



          {{--  $("#toDate").focus();  --}}
            {{--  var sDate = $(this).datepicker("getDate");
            var minDate = $(this).datepicker("getDate");
            sDate.setDate(sDate.getDate()+7);  --}}

            {{--  $('#toDate').datepicker({
                maxDate : sDate,
                minDate : $(this).val(),
            });  --}}
        })




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


      $("#toDate").change(function () {
        if (Array.isArray(dateList) && dateList.length) {
          console.log("Array exists and is not empty");
          dateList = [];
          dateArray = [];
      }

        {{--  var startDate = new Date($("#fromDate").val());  --}}
        {{--  var endDate = new Date($("#toDate").val());  --}}
        {{--  var startDate = start;
        var endDate = end;  --}}
        $('.submit-designation').prop("disabled", false);
        let dateString = $("#fromDate").val();
      let [day, month, year] = dateString.split('/');
      const startDate = new Date(+year, +month - 1, +day)
      let dateString1 = $("#toDate").val();
      let [day1, month1, year1] = dateString1.split('/');
      const endDate = new Date(+year1, +month1 - 1, +day1)

        {{--  alert(startDate);  --}}


        if (startDate <= endDate) {

            var currentDate = new Date(startDate);
            while (currentDate <= endDate) {
                dateList.push(new Date(currentDate));
                currentDate.setDate(currentDate.getDate() + 1);
            }
            console.log(dateList);

            var requested = dateList.length;

            $("#dateListCount").val(requested);

            // Display the dates
            $("#dateList").empty();
             var Leave_selecetd = $('#leaveType').val();

            dateList.forEach(function (date) {
              var formated_date=formatDate(date);
              dateArray.push(formated_date);
              if(Leave_selecetd != '2'){
                  var date_switch = '<label class="switch switch-danger"> <input value="1" type="radio" name="'+formated_date+'-radio"  class="switch-input '+formated_date+'-radio" checked /><span class="switch-toggle-slider"><span class="switch-on"><i class="ti ti-check"></i> </span><span class="switch-off"><i class="ti ti-x"></i></span></span><span class="switch-label">Full Day</span></label><label class="switch switch-warning"><input type="radio" value="2"  name="'+formated_date+'-radio" class="switch-input '+formated_date+'-radio"  /><span class="switch-toggle-slider"><span class="switch-on"> <i class="ti ti-check"></i> </span><span class="switch-off"><i class="ti ti-x"></i></span></span><span class="switch-label">FN</span></label><label class="switch switch-warning"><input value="3" type="radio" name="'+formated_date+'-radio" class="switch-input '+formated_date+'-radio"  /><span class="switch-toggle-slider"><span class="switch-on"><i class="ti ti-check"></i></span><span class="switch-off"><i class="ti ti-x"></i></span></span><span class="switch-label">AN</span></label>';
                var date_set = '<div class="row row-bordered g-0"><div class="col-sm-12 p-4"><div class="text-light small fw-medium mb-3">Type</div><div class="switches-stacked"><label class="switch"><input type="radio" class="switch-input" name="'+formated_date+'-radio" checked /><span class="switch-toggle-slider"><span class="switch-on"></span><span class="switch-off"></span> </span><span class="switch-label">Full Day</span></label><label class="switch"><input type="radio" class="switch-input" name="'+formated_date+'-radio"  /><span class="switch-toggle-slider"><span class="switch-on"></span><span class="switch-off"></span> </span><span class="switch-label">FN</span></label><label class="switch"><input type="radio" class="switch-input" name="'+formated_date+'-radio"  /><span class="switch-toggle-slider"><span class="switch-on"></span><span class="switch-off"></span> </span><span class="switch-label">AN</span></label></div></div>';

              }
              else{
                  var date_switch = '<label class="switch switch-danger"> <input value="1" type="radio" name="'+formated_date+'-radio"  class="switch-input '+formated_date+'-radio" checked /><span class="switch-toggle-slider"><span class="switch-on"><i class="ti ti-check"></i> </span><span class="switch-off"><i class="ti ti-x"></i></span></span><span class="switch-label">Full Day</span></label>';
                var date_set = '<div class="row row-bordered g-0"><div class="col-sm-12 p-4"><div class="text-light small fw-medium mb-3">Type</div><div class="switches-stacked"><label class="switch"><input type="radio" class="switch-input" name="'+formated_date+'-radio" checked /><span class="switch-toggle-slider"><span class="switch-on"></span><span class="switch-off"></span> </span><span class="switch-label">Full Day</span></label></div></div>';

              }

                $("#dateList").append("<li class='text-primary'>" + date.toDateString() +'<br><br>'+ date_switch+"</li><br>");


            });
        } else {
          $("#toDate").val('');
          Swal.fire({
            title: 'End Date should be greater than or equal to Start Date.',
            customClass: {
              confirmButton: 'btn btn-warning'
            },
            buttonsStyling: false
          });
            {{--  alert("End Date should be greater than or equal to Start Date.");  --}}
        }
    });



    $('#leaveType').change(function(){
      var id= $(this).val();
       $("#dateList").empty();
       $("#fromDate").val('');
        $("#toDate").val('');
         $('.availability').hide();

      if(id > 0){
        if(id <4){
 $('.availability').show();
        }

        $('.date-group').show();



        $('.total-leave').html($('#typeTotal'+id).val());
        $('.balance-leave').html($('#typeBalance'+id).val());
        $('.requested-leave').html($('#typeRequested'+id).val());
        $('.availed-leave').html($('#typeAvailed'+id).val());
        if($('#typeBalance'+id).val() == 0 && id<=3){
          $('.message').show();



          $('.leaveTypeName').html($( "#leaveType option:selected" ).text());
          $("#leaveType").val("");
        }
        else{
          $('.message').hide();
        }
      }
      else{
        {{--  $('.availability').hide();  --}}

      }
    })



  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  // Users List datatable
  if (dataTablePermissions.length) {
    dt_permission = dataTablePermissions.DataTable({
       ajax: {
        url: permissionList
       }, // JSON file to add data
      //ajax: assetsPath + 'json/permissions-list1.json', // JSON file to add data
      columns: [
        // columns according to JSON
        { data: '' },

        { data: 'leave_type' },
        { data: 'leave_request_details' },
        { data: 'duration' },
         {{--  { data: 'duty_assigned' },  --}}
          { data: 'duty_assignments' },
        { data: 'requested_at' },
        { data: 'status' },
        { data: 'action_by' },
        { data: '' }
      ],
      columnDefs: [
        {
          // For Responsive
          className: 'control',
          orderable: false,
          searchable: false,
          responsivePriority: 2,
          targets: 0,
          render: function (data, type, full, meta) {
            return '';
          }
        },


        {
          // Name
          targets: 1,
          render: function (data, type, full, meta) {
            var $leave_type = full['leave_type'];
            return '<span class="text-nowrap">' + $leave_type + '</span>';
          }
        },

        {
          targets: 2,
          render: function (data, type, full, meta) {
          var $leave_request_details = full['leave_request_details'],
            $output = '';

          for (var i = 0; i < $leave_request_details.length; i++) {
            var val = $leave_request_details[i];


            const convertedDate = convertDateFormat($leave_request_details[i]['date']);
            console.log(convertedDate);


            $output +=  '<span class="badge bg-label-dark m-1">'+convertedDate+'</span> <span class="badge  m-1 '+($leave_request_details[i]['leave_day_type'] == 1 ? 'bg-label-primary' : $leave_request_details[i]['leave_day_type'] == 2 ? 'bg-label-secondary' :  'bg-label-info')+'">'+($leave_request_details[i]['leave_day_type'] == 1 ? 'Full Day' : $leave_request_details[i]['leave_day_type'] == 2 ? 'FN' :  'AN')+'</span><br>';
          }
          return '<span class="text-nowrap">' + $output + '</span>';
        }

        },

        {
          // Name
          targets: 3,
          render: function (data, type, full, meta) {
            var $duration = full['duration'];
            return '<span class="text-nowrap">' + $duration + '</span>';
          }
        },

         {{--  {
          // Name
          targets: 4,
          render: function (data, type, full, meta) {
            var $duration = full['duty_assigned_name'] == null ? 'Nil' : full['duty_assigned_name'];
            return '<span class="text-nowrap">' + $duration + '</span>';
          }
        },  --}}

         {
  targets: 4, // Change according to your table column index
  render: function (data, type, full, meta) {
    let output = '';
    const dutyAssignments = full.duty_assignments; // make sure this matches your JSON structure

    if (Array.isArray(dutyAssignments) && dutyAssignments.length > 0) {
      for (let i = 0; i < dutyAssignments.length; i++) {
        const assignment = dutyAssignments[i];
        const userName = assignment.user?.name || 'Unknown';
        const description = assignment.description || 'No Description';

        output += `
          <span class="badge bg-label-dark m-1">${userName}</span>
          <span class="badge bg-label-primary m-1 text-wrap">${description}</span><br>
        `;
      }
    } else {
      output = '<span class="badge bg-label-warning">No Assignment</span>';
    }

    return output;
  }
},



        {
          // Name
          targets: 5,
          render: function (data, type, full, meta) {
            var $requested_at = full['formatted_requested_at'];
            return '<span class="text-nowrap">' + $requested_at + '</span>';
          }
        },
        {
          // User Role
          targets: 6,
          render: function (data, type, full, meta) {
            var $status = full['status'];
            $out = ($status==1 ? '<a><span class="badge bg-label-dark m-1">Partially Completed</span></a>' : ($status==0 ? '<a><span class="badge bg-label-warning m-1">Pending</span></a>' :  '<a><span class="badge bg-label-success m-1">Completed</span></a>') )
            return  $out;
          }
        },
        {
          // Name
          targets: 7,
          render: function (data, type, full, meta) {
            var $name = (full['action_by_name'] == null ? '' : full['action_by_name']);
            var $action_at = (full['formatted_action_at'] == null ? '' :full['formatted_action_at']);
            return '<span class="text-nowrap">' + $name + ' <br>'+$action_at+'</span>';
          }
        },


        {
          // Actions
          targets: -1,
          searchable: false,
          title: 'Actions',
          orderable: false,
          render: function (data, type, full, meta) {
            if(full['status'] == 0){

              return (
                '<span class="text-nowrap">' +
                '<button class="btn btn-sm btn-icon delete-record" data-id="'+full['id']+'"><i class="ti ti-trash"></i></button></span>'
              );
            }
                if(full['status'] == 2){
                   return (
              '<span class="text-nowrap"><button class="btn btn-sm btn-primary  me-2 edit-designation" data-id="'+full['id']+'" data-bs-target="#leaveActionModal" data-bs-toggle="modal" data-bs-dismiss="modal">View</button>' +
              '<button class="btn btn-sm btn-danger  me-2 " data-id="'+full['id']+'" data-bs-toggle="modal"  data-bs-target="#cancelModal">Cancel</button></span>'
            );
                }
            else{
              return (
              '<span class="text-nowrap"><button class="btn btn-sm btn-primary  me-2 edit-designation" data-id="'+full['id']+'" data-bs-target="#leaveActionModal" data-bs-toggle="modal" data-bs-dismiss="modal">View</button>' +
              '</span>'
            );
            }

          }
        }
      ],
      {{--  order: [[1, 'asc']],  --}}
      dom:
        '<"row mx-1"' +
        '<"col-sm-12 col-md-3" l>' +
        '<"col-sm-12 col-md-9"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-md-end justify-content-center flex-wrap me-1"<"me-3"f>B>>' +
        '>t' +
        '<"row mx-2"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6"p>' +
        '>',
      language: {
        sLengthMenu: 'Show _MENU_',
        search: 'Search',
        searchPlaceholder: 'Search..'
      },
      // Buttons with Dropdown
      buttons: [
        {
          text: 'Create Leave Request',
          className: 'add-new btn btn-primary mb-3 mb-md-0 add-new-designation',
          attr: {
            'data-bs-toggle': 'modal',
            'data-bs-target': '#DesignationModal'
          },
          init: function (api, node, config) {
            $(node).removeClass('btn-secondary');
          }
        }
      ],
      // For responsive popup
      {{--  responsive: {
        details: {
          display: $.fn.dataTable.Responsive.display.modal({
            header: function (row) {
              var data = row.data();
              return 'Details of ' + data['name'];
            }
          }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            var data = $.map(columns, function (col, i) {
              return col.title !== '' // ? Do not show row in modal popup if title is blank (for check box)
                ? '<tr data-dt-row="' +
                    col.rowIndex +
                    '" data-dt-column="' +
                    col.columnIndex +
                    '">' +
                    '<td>' +
                    col.title +
                    ':' +
                    '</td> ' +
                    '<td>' +
                    col.data +
                    '</td>' +
                    '</tr>'
                : '';
            }).join('');

            return data ? $('<table class="table"/><tbody />').append(data) : false;
          }
        }
      }  --}}

    });
  }




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
  var designationEditList = document.querySelectorAll('.datatables-designation .edit-designation'),
    permissionAdd = document.querySelector('.add-new-designation'),
    designationSubmit = document.querySelector('.submit-designation'),
    designationTitle = document.querySelector('.designation-title');

    $('#DesignationModal').on('hidden.bs.modal', function (e) {
      $(this)
        .find("input,textarea,select")
           .val('')
           .end()
        .find("input[type=checkbox], input[type=radio]")
           .prop("checked", "")
           .end();
    })

    designationSubmit.onclick = function () {

      $("#DesignationModal .modal-body").block({
        message:
          '<div class="sk-wave sk-primary mx-auto"><div class="sk-rect sk-wave-rect"></div> <div class="sk-rect sk-wave-rect"></div> <div class="sk-rect sk-wave-rect"></div> <div class="sk-rect sk-wave-rect"></div> <div class="sk-rect sk-wave-rect"></div></div>',
        timeout: 8000,
        css: {
          backgroundColor: "transparent",
          border: "0"
        },
        overlayCSS: {
          backgroundColor: "#fff",
          opacity: 0.8
        }
      })



      var  leaveType =  $("#leaveType").val();
      var  start_date =  $("#fromDate").val();
      var  end_date =  $("#toDate").val();
      var  description =  $("#eventDescription").val();
      var  leaveType =  $("#leaveType").val();
      var request_type =   $("#submit_designation").data('type');

      var duration = 0;
      var  leave_period_start =  $("#date_start").val();
      var  leave_period_end =  $("#date_end").val();
      dateArray.forEach((item, index) => {
        var date_leave_type_val = $('.'+item+'-radio:checked').val();
        if(date_leave_type_val == 1 ){
          duration++;
        }
        else{
          duration=duration+0.5;
        }
        date_leave_type.push({'date':item,'leave_day_type':date_leave_type_val})

     })


     var duty_assigned = [];
$('.duty-assignment-entry').each(function () {
  var userId = $(this).find('select').val();
  var desc = $(this).find('input').val();

  if (userId && desc) {
    duty_assigned.push({
      user_id: userId,
      description: desc
    });
  }
});


     console.log(date_leave_type);
      if(request_type=='new'){



          $.ajax({
            data:  {
              leave_type_id:leaveType,
              from:start_date,
              to:end_date,
              duration:duration,
              date_list:date_leave_type,
              description:description,

                duty_assignments: duty_assigned,
              leave_period_start:leave_period_start,
              leave_period_end:leave_period_end,
              "_token": "{{ csrf_token() }}",
          },
            url: `${baseUrl}leave/request/store`,
            type: 'POST',

            success: function (response) {
              dateArray = [],
              $("#dateList").empty();
              $("#toDate").val('');
              $("#fromDate").val('');
              $("#leaveType").val('');
              $("#availability").hide();
              $('#DesignationModal').modal('hide');
              if(response.status == true){

                Swal.fire({
                  icon: 'success',
                  title: `Successfully Created!`,
                  text: `Leave Requested Successfully.`,
                  customClass: {
                    confirmButton: 'btn btn-success'
                  }
                }).then((result) => {
                  location.reload();
                });
              }
              else{
                Swal.fire({
                  icon: 'warning',
                  title: `Can't Save Request!`,
                  text: response.message,
                  customClass: {
                    confirmButton: 'btn btn-success'
                  }
                }).then((result) => {
                  location.reload();
                });

              }


              // sweetalert


            },
            error: function (err) {
              $('#DesignationModal').modal('hide');
              dateArray = [],
              $("#dateList").empty();
              $("#toDate").val('');
              $("#fromDate").val('');
              $("#leaveType").val('');
              $("#availability").hide();
              {{--  Swal.fire({
                title: 'Oh Sorry ddd!',
                text: `${status}`,
                icon: 'error',
                customClass: {
                  confirmButton: 'btn btn-success'
                }
              });  --}}

               var errors = err.responseJSON.errors;
                       $.each(errors, function(key, value) {
                        Swal.fire({
                    title: value[0],
                    customClass: {
                      confirmButton: 'btn btn-warning'
                    },
                    buttonsStyling: false
                  });

                    });
            }
          });
        }
        else{
          $.ajax({
            data:  {
              title:title,
              type:type,
              start_date:start_date,
              start_time:start_time,
              end_date:end_date,
              end_time:end_time,
              location:location,
              description:description,

               duty_assignments: duty_assigned,

              "_token": "{{ csrf_token() }}",

          },
            url: `${baseUrl}movement/update/${desig_id}`,
            type: 'POST',

            success: function (status) {

                $('#DesignationModal').modal('hide');
              // sweetalert
              Swal.fire({
                icon: 'success',
                title: `Successfully Updated!`,
                text: `Leave Updated Successfully.`,
                customClass: {
                  confirmButton: 'btn btn-success'
                }
              }).then((result) => {
                location.reload();
              });

            },
            error: function (err) {
              $('#DesignationModal').modal('hide');
              Swal.fire({
                title: 'Oh Sorry!',
                text: `${status}`,
                icon: 'error',
                customClass: {
                  confirmButton: 'btn btn-success'
                }
              });
            }
          });
        }
    }


  // Delete Record
  $('.datatables-designation tbody').on('click', '.delete-record', function () {
    var desig_id = $(this).data('id');
    $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
  })
  $.ajax({
    type: "GET",

    url: '/leave/request/delete/'+desig_id,
    success: function (data) {
      Swal.fire({
        icon: 'success',
        title: `Successfully deleted!`,
        {{--  text: `Designation ${status} Successfully.`,  --}}
        customClass: {
          confirmButton: 'btn btn-success'
        }
      });

    },
    error: function(data){

    }
});

    dt_permission.row($(this).parents('tr')).remove().draw();
  });

  $('.datatables-designation tbody').on('click', '.edit-designation', function () {


    var desig_id = $(this).data('id');
    $("#submit_designation").attr('data-id',desig_id);
    $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
  })
  $.ajax({
  type: "GET",

  url: '/leave/request/edit/'+desig_id,
  success: function (data) {
    console.log(data);
      {{--  $("#modalDesignationName").val(data.designation.designation);
      $("#submit_designation").data('id',data.designation.id);  --}}
    var tbody='';
    data.leave_list.leave_request_details.forEach((item, index) => {
      const converted_date = convertDateFormat(item.date);
      console.log(converted_date);
      tbody=tbody+'<tr><td>'+data.leave_list.leave_type+'</td><td>'+converted_date+'</td><td>'+(item.leave_day_type == 1 ? 'Full Day' : item.leave_day_type == 2 ? 'FN' : 'AN')+'</td>'+
        '<td>'+(item.status == 0 ? '<span class="text-nowrap badge bg-label-secondary">Pending</span></td></tr>' : (item.status == 1 ? '<span class="badge bg-label-success">Approved</span><br>Remark : '+item.remark+' ': item.status == 5 ? '<span class="badge bg-label-danger">Cancelled</span> <br>Remark : '+item.remark+ '': '<span class="badge bg-label-danger">Rejected</span> <br>Remark : '+item.remark+ ''));
     })
    $(".datatables-leave-list #dataList").html(tbody);
    $(".leave-type-name").html(data.leave_list.leave_type);
    $(".leave-total-credit").html(data.leave_balance.total_leaves_credit);
    $(".leave-total-availed").html(data.leave_balance.availed_leave);
    $(".leave-total-requested").html(data.leave_balance.pending_leave);
    $(".leave-total-balance").html(data.leave_balance.balance_credit);



  },
  error: function(data){

  }
});

  {{--  dt_permission.row($(this)).draw();  --}}
});



})();


  // Filter form control to default size
  // ? setTimeout used for multilingual table initialization
  setTimeout(() => {
    $('.dataTables_filter .form-control').removeClass('form-control-sm');
    $('.dataTables_length .form-select').removeClass('form-select-sm');
  }, 300);

   let assignmentIndex = 1;

  document.getElementById('addDutyAssignment').addEventListener('click', function () {
    const container = document.getElementById('dutyAssignmentContainer');

    const newEntry = document.createElement('div');
    newEntry.className = 'duty-assignment-entry mb-2 row';
    newEntry.innerHTML = `
      <div class="col-md-5">
        <select name="duty_assignments[${assignmentIndex}][user_id]" class="form-select" >
          <option value="">Select User</option>
           @php
              $users = DB::table('users')->where('active',1)->orderBy('name')->get();
              @endphp
          @foreach($users as $user)
            @if($user->id != Auth::id())
            <option value="{{ $user->id }}">{{ $user->name }}</option>
            @endif
          @endforeach
        </select>
      </div>
      <div class="col-md-5">
        <input type="text" name="duty_assignments[${assignmentIndex}][description]" class="form-control" placeholder="Description" >
      </div>
      <div class="col-md-2">
        <button type="button" class="btn btn-danger remove-assignment">Remove</button>
      </div>
    `;
    container.appendChild(newEntry);
    assignmentIndex++;
  });

  document.addEventListener('click', function (e) {
    if (e.target.classList.contains('remove-assignment')) {
      e.target.closest('.duty-assignment-entry').remove();
    }
  });



    var cancelModal = document.getElementById('cancelModal');
    cancelModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        var leaveId = button.getAttribute('data-id');
        document.getElementById('leave_request_id').value = leaveId;

        // fetch dates via AJAX
        fetch("/leave/" + leaveId + "/dates")
            .then(res => res.json())
            .then(data => {
                let html = "";
                data.forEach(date => {
                    html += `<div class="form-check">
                                <input class="form-check-input" type="checkbox" name="dates[]" value="${date}">
                                <label class="form-check-label">${date}</label>
                             </div>`;
                });
                document.getElementById('datesContainer').innerHTML = html;
            });
    });
    {{--  var cancelModal = document.getElementById('cancelModal');
cancelModal.addEventListener('show.bs.modal', function(event) {
    var button = event.relatedTarget;
    var leaveId = button.getAttribute('data-id');
    document.getElementById('leave_request_id').value = leaveId;

    // fetch dates via AJAX
    fetch("/leave/" + leaveId + "/dates")
        .then(res => res.json())
        .then(data => {
            let html = "";
            data.forEach(item => {
                let dayTypeLabel = '';
                switch (item.leave_day_type) {
                    case 1: dayTypeLabel = 'Full Day'; break;
                    case 2: dayTypeLabel = 'FN'; break;
                    case 3: dayTypeLabel = 'AN'; break;
                }

                html += `
                    <div class="border rounded p-2 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="dates[]" value="${item.date}">
                            <label class="form-check-label fw-bold">${item.date} (${dayTypeLabel})</label>
                        </div>`;

                if (item.leave_day_type == 1) {
                    html += `
                        <div class="ms-4 mt-1">
                            <label class="form-label small">Cancel partially as:</label><br>
                            <input type="radio" name="partial[${item.date}]" value="3"> FN
                            <input type="radio" name="partial[${item.date}]" value="2" class="ms-2"> AN
                        </div>`;
                }

                html += `</div>`;
            });

            document.getElementById('datesContainer').innerHTML = html;
        });
});  --}}


});
</script>



@endsection

@section('content')
<h4 class="fw-semibold mb-4">Leave Request List</h4>

<div class="row g-4 mb-4">


  <div class="alert alert-warning alert-dismissible d-flex align-items-baseline" role="alert">
    <span class="alert-icon alert-icon-lg text-primary me-2">
      <i class="ti ti-calendar ti-sm"></i>
    </span>
    <div class="d-flex flex-column ps-1">
      <input type="hidden" id="date_start" value="{{$date_start}}" />
      <input type="hidden" id="date_end" value="{{$date_end}}" />
      <p class="mb-0"> Leave Statistics for the period of {{$date_start}} TO {{$date_end}}
        @if($employment_type !=1)
        <small> - (Casual Leave Statistics for the period of {{$cl_date_start}} TO {{$cl_date_end}})</small>
        @endif
      </p>
      <h5 class="alert-heading mb-2"></h5>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
      </button>
    </div>
  </div>

  @foreach ($leaves_total_credit_details as $leave)
  @if($leave['leave_type_id'] == 2 && $employment_type !=1)
  @if($durationMonths < 11) @php $leave['balance_credit']=0; $leave['total_leaves_credit']=0; @endphp @endif @endif <div
    class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>{{$leave['leave_type']}}</span>
            <div class="d-flex align-items-center my-1">
              <small>Available - </small>
              <h4 class="mb-0 me-2"> @if($leave['leave_type_id'] <= 3 ) {{$leave['balance_credit']}} @endif</h4>
                  <input type="hidden" id="typeBalance{{$leave['leave_type_id']}}" value={{$leave['balance_credit']}} />
                  <input type="hidden" id="typeTotal{{$leave['leave_type_id']}}"
                    value={{$leave['total_leaves_credit']}} />
                  <input type="hidden" id="typeRequested{{$leave['leave_type_id']}}"
                    value={{$leave['pending_leave']}} />
                  <input type="hidden" id="typeAvailed{{$leave['leave_type_id']}}" value={{$leave['pending_leave']}} />
            </div>
            <span>Total - @if($leave['leave_type_id'] <= 3 ) {{$leave['total_leaves_credit']}} @endif</span>
          </div>
          <span class="badge bg-label-primary rounded p-2">
            <i class="ti ti-user ti-sm"></i>
          </span>
        </div>
      </div>
    </div>
</div>
@endforeach

</div>

<!-- Permission Table -->
<div class="card">
  <div class="card-datatable table-responsive">
    <table class="datatables-designation table border-top">
      <thead>
        <tr>
          <th></th>
          {{-- <th>User</th> --}}
          <th>Leave Type</th>
          <th>Leave Days</th>
          <th>Duration</th>
          <th>Duty Assigned</th>
          <th>Requested_at</th>
          <th>Status</th>
          <th>Action By</th>
          <th>Actions</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
<!--/ Permission Table -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('leave.cancel.request') }}">
      @csrf
      <input type="hidden" name="leave_request_id" id="leave_request_id">

      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Cancel Leave Dates</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div id="datesContainer">
            <!-- checkboxes will be injected with JS -->
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-danger">Submit Cancel Request</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal -->
@include('_partials/_modals/modal-leave-request')
@include('_partials/_modals/modal-leave-action')
<!-- /Modal -->
@endsection