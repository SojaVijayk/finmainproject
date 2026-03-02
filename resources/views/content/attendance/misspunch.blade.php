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
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />



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
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>


@endsection

@section('page-script')
<script>


$(function () {
  var dataTablePermissions = $('.datatables-designation'),
    dt_permission,
    permissionList = baseUrl + 'misspunch/list';


    $(".datepicker").datepicker({
      autoclose: true ,
      format:'dd-mm-yyyy',
      {{--  maxDate: new Date(new Date()),  --}}
      });
      $(".timepicker").timepicker({
        step: 5 ,
        {{--  disableTimeRanges: [
          ["12am", "9:30am"],
          ["5:30pm", "12am"]
        ]  --}}

      });
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
        { data: 'formatted_date' },

        { data: 'type' },
        { data: 'checkinTime' },
        { data: 'checkoutTime' },
        { data: 'description' },
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

        {{--  {
          // Name
          targets: 1,
          render: function (data, type, full, meta) {
            var $name = full['name'];
            var $designation = full['designation'];
            return '<span class="text-nowrap">' + $name + '<br>' + $designation + '</span>';
          }
        },  --}}
        {
          // Name
          targets: 2,
          render: function (data, type, full, meta) {
            var $type = full['type'];
            var $type =  ((full['type'] == 1 ) ? 'Checkin' : (full['type'] == 2 ) ? 'Checkout' : 'Checkin & Checkout');
              return '<span class="text-nowrap">' + $type + '</span>';
          }
        },


        {{--  {
          // Name
          targets: 4,
          render: function (data, type, full, meta) {
            var $name = full['start_date'];
            var $time = full['start_time'];
            return '<span class="text-nowrap">' + $name + '-'+$time+'</span>';
          }
        },
        {
          // Name
          targets: 5,
          render: function (data, type, full, meta) {
            var $name = full['end_date'];
            var $time = full['end_time'];
            return '<span class="text-nowrap">' + $name + '-'+$time+'</span>';
          }
        },  --}}


        {
          // Name
          targets: 6,
          render: function (data, type, full, meta) {
            var $name = full['formatted_requested_at'];
            return '<span class="text-nowrap">' + $name + '</span>';
          }
        },
        {
          // User Role
          targets: 7,
          render: function (data, type, full, meta) {
            var $status = full['status'];
            $out = ($status==1 ? '<a><span class="badge bg-label-success m-1">Approved</span></a>' : ($status==2 ? '<a><span class="badge bg-label-danger m-1">Rejected</span></a>' : '<a><span class="badge bg-label-warning m-1">Pending</span></a>')  )
            return  $out;
          }
        },
        {
          // Name
          targets: 8,
          render: function (data, type, full, meta) {
            var $name = (full['action_by_name'] == null ? '' : full['action_by_name']);
            var $action_at = (full['formatted_action_at'] == null ? '' :full['formatted_action_at']);
            var $remark = (full['remark'] == null ? '' :full['remark']);
            return '<span class="text-nowrap">' + $name + ' <br>'+$action_at+'<br>'+$remark+'</span>';

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
                '<span class="text-nowrap"><button class="btn btn-sm btn-icon me-2 edit-designation" data-id="'+full['id']+'" data-bs-target="#DesignationModal" data-bs-toggle="modal" data-bs-dismiss="modal"><i class="ti ti-edit"></i></button>' +
                '<button class="btn btn-sm btn-icon delete-record" data-id="'+full['id']+'"><i class="ti ti-trash"></i></button></span>'
              );
            }
            else{
              return (
              '<span class="text-nowrap">' +
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
          text: 'Create Request',
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
      responsive: {
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
      }

    });
  }


  $('#type').change(function(){
    var id= $(this).val();

    if(id == 1){
      $('#checkoutTime').prop('disabled',true);
      $('#checkinTime').prop('disabled',false);

    }
    else if(id == 2){

      $('#checkinTime').prop('disabled',true);
      $('#checkoutTime').prop('disabled',false);

    }
    else{
      $('#checkinTime').prop('disabled',false);
      $('#checkoutTime').prop('disabled',false);
    }
  })

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




      var  type =  $("#type").val();
      var  date =  $("#date").val();
      var  checkinTime =  $("#checkinTime").val();
      var  checkoutTime =  $("#checkoutTime").val();
      var  description =  $("#description").val();
      var request_type =   $("#submit_designation").data('type');
      var desig_id =   $("#submit_designation").data('id');



      if(request_type=='new'){

          $.ajax({
            data:  {
              type:type,
              date:date,
              checkinTime:checkinTime,
              checkoutTime:checkoutTime,
              description:description,
              "_token": "{{ csrf_token() }}",
          },
            url: `${baseUrl}misspunch/store`,
            type: 'POST',

            success: function (status) {

                $('#DesignationModal').modal('hide');

              // sweetalert
              Swal.fire({
                icon: 'success',
                title: `Successfully ${status}!`,
                text: `Miss punch request ${status} Successfully.`,
                customClass: {
                  confirmButton: 'btn btn-success'
                }
              }).then((result) => {
                window.location.reload();
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
        else{
          $.ajax({
            data:  {
              type:type,
              date:date,
              checkinTime:checkinTime,
              checkoutTime:checkoutTime,
              description:description,

              "_token": "{{ csrf_token() }}",

          },
            url: `${baseUrl}misspunch/update/${desig_id}`,
            type: 'POST',

            success: function (status) {

                $('#DesignationModal').modal('hide');
              // sweetalert
              Swal.fire({
                icon: 'success',
                title: `Successfully ${status}!`,
                text: `Miss punch request ${status} Successfully.`,
                customClass: {
                  confirmButton: 'btn btn-success'
                }
              }).then((result) => {
               window.location.reload();
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

    url: '/misspunch/delete/'+desig_id,
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

    // Edit Record
    $('.datatables-designation tbody').on('click', '.edit-designation', function () {

      designationTitle.innerHTML = 'Edit Miss Punch Request'; // reset text
        $("#submit_designation").attr('data-type','edit');

        var desig_id = $(this).data('id');
        $("#submit_designation").attr('data-id',desig_id);
        $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      })

      $.ajax({
      type: "GET",

      url: '/misspunch/edit/'+desig_id,
      success: function (data) {

          $("#type").val(data.designation.type);
          $("#date").val(data.designation.date);
          $("#checkinTime").val(data.designation.checkinTime);
          $("#checkoutTime").val(data.designation.checkoutTime);
          $("#description").val(data.designation.description);

          $("#submit_designation").data('id',data.designation.id);
          {{--  $("#type").select2();  --}}
          {{--  $('#type').select2();  --}}

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
});
</script>



@endsection

@section('content')
<h4 class="fw-semibold mb-4">Miss Punch Regularisation</h4>



<!-- Permission Table -->
<div class="card">
  <div class="card-datatable table-responsive">
    <table class="datatables-designation table border-top">
      <thead>
        <tr>
          <th></th>
          <th>Date</th>
          <th>Type</th>
          <th>In Time</th>
          <th>Out Time</th>
          <th>Description</th>
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


<!-- Modal -->
@include('_partials/_modals/modal-misspunch')
<!-- /Modal -->
@endsection
