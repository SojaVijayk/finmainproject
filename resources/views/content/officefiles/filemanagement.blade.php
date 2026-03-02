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


@endsection

@section('page-script')
<script>


$(function () {
  var dataTablePermissions = $('.datatables-designation'),
    dt_permission,
    permissionList = baseUrl + 'officefiles/list';


    $(".datepicker").datepicker({
      autoclose: true ,
      format:"dd-mm-yyyy"
      });
      $(".timepicker").timepicker({
        step: 15 ,
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
        { data: 'designation' },
        { data: 'filename' },
        { data: 'description' },
        { data: 'date' },
        { data: 'year' },
        { data: 'numbers' },
        { data: 'status' },
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
          // User full name and email
          targets: 1,
          responsivePriority: 4,
          render: function(data, type, full, meta) {
          var $name = full['name'],
              $email = full['email'],
              $image = full['profile_pic'];
          if ($image) {
              // For Avatar image
              var $output =
                  '<img src="' + assetsPath + 'img/avatars/' + $image +
                  '" alt="Avatar" class="rounded-circle">';
          } else {
              // For Avatar badge
              var stateNum = Math.floor(Math.random() * 6);
              var states = ['success', 'danger', 'warning', 'info', 'primary',
                  'secondary'
              ];
              var $state = states[stateNum],
                  $name = full['name'],
                  $initials = $name.match(/\b\w/g) || [];
              $initials = (($initials.shift() || '') + ($initials.pop() ||
                  '')).toUpperCase();
              $output =
                  '<span class="avatar-initial rounded-circle bg-label-' +
                  $state + '">' + $initials + '</span>';
          }
          // Creates full output for row
          var $row_output =
              '<div class="d-flex justify-content-start align-items-center user-name">' +
              '<div class="avatar-wrapper">' +
              '<div class="avatar avatar-sm me-3">' +
              $output +
              '</div>' +
              '</div>' +
              '<div class="d-flex flex-column">' +
              '<a href="#" class="text-body text-truncate"><span class="fw-semibold">' +
              $name +
              '</span></a>' +
              '<small class="text-muted">' +
              $email +
              '</small>' +
              '</div>' +
              '</div>';
          return $row_output;
      }
    },
        {
          // Name
          targets: 2,
          render: function (data, type, full, meta) {
            var $name = full['filename'];

            return '<span class= "text-primary p-2"><i class="ti ti-folder"></i></span> <span class="text-nowrap">' + $name + '</span><br>';
          }
        },
        {
          // Name
          targets: 3,
          render: function (data, type, full, meta) {
            var $name = full['description'];
            return '<span class="text-nowrap">' + $name + '</span>';
          }
        },


        {
          // Name
          targets: 4,
          render: function (data, type, full, meta) {
            var $name = full['date'];
            return '<span class="text-nowrap">' + $name + '</span>';
          }
        },
        {
          // Name
          targets: 5,
          render: function (data, type, full, meta) {
            var $name = full['year'];
            return '<span class="text-nowrap">' + $name + '</span>';
          }
        },
        {
          // Name
          targets: 6,
          render: function (data, type, full, meta) {
            var $name = full['numbers'];
            return '<span class="text-nowrap">' + $name + '</span>';
          }
        },
        {
          // User Role
          targets: 7,
          render: function (data, type, full, meta) {
            var $status = full['status'];
            $out = ($status==1 ? '<a><span class="badge bg-label-success m-1">Active</span></a>' : ($status==2 ? '<a><span class="badge bg-label-danger m-1">Closed</span></a>' : '<a><span class="badge bg-label-warning m-1">Pending</span></a>')  )
            return  $out;
          }
        },


        {
          // Actions
          targets: -1,
          searchable: false,
          title: 'Actions',
          orderable: false,
          render: function (data, type, full, meta) {

              return (
                '<span class="text-nowrap"><button class="btn btn-sm btn-icon me-2 edit-designation" data-id="'+full['id']+'" data-bs-target="#DesignationModal" data-bs-toggle="modal" data-bs-dismiss="modal"><i class="ti ti-edit"></i></button>' +
                '<button class="btn btn-sm btn-icon delete-record" data-id="'+full['id']+'"><i class="ti ti-trash"></i></button></span>'
              );


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
          text: 'Create New File',
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
    designationSubmit = document.querySelector('.submit-file'),
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



      var  name =  $("#name").val();
      var  numbers =  $("#numbers").val();
      var  date =  $("#date").val();
      var  year =  $("#year").val();
      var  description =  $("#description").val();
      var  status =  $("#status").val();
      var request_type =   $("#submit_file").data('type');
      var desig_id =   $("#submit_file").data('id');



      if(request_type=='new'){

          $.ajax({
            data:  {
              filename:name,
              numbers:numbers,
              date:date,
              year:year,
              description:description,
              status:status,
              "_token": "{{ csrf_token() }}",
          },
            url: `${baseUrl}officefiles/store`,
            type: 'POST',

            success: function (status) {

                $('#DesignationModal').modal('hide');

              // sweetalert
              Swal.fire({
                icon: 'success',
                title: `Successfully ${status}!`,
                text: `File ${status} Successfully.`,
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

              filename:name,
              numbers:numbers,
              date:date,
              year:year,
              description:description,
              status:status,

              "_token": "{{ csrf_token() }}",

          },
            url: `${baseUrl}officefiles/update/${desig_id}`,
            type: 'POST',

            success: function (status) {

                $('#DesignationModal').modal('hide');
              // sweetalert
              Swal.fire({
                icon: 'success',
                title: `Successfully ${status}!`,
                text: `File ${status} Successfully.`,
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

    url: '/officefiles/delete/'+desig_id,
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

      designationTitle.innerHTML = 'Edit Movement'; // reset text
        $("#submit_file").attr('data-type','edit');

        var desig_id = $(this).data('id');
        $("#submit_file").attr('data-id',desig_id);
        $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      })

      $.ajax({
      type: "GET",

      url: '/officefiles/edit/'+desig_id,
      success: function (data) {
        console.log(data);
          $("#name").val(data.designation.filename);
          $("#date").val(data.designation.date);
          $("#year").val(data.designation.year);
          $("#status").val(data.designation.status);
          $("#numbers").val(data.designation.numbers);

          $("#description").val(data.designation.description);

          $("#submit_file").data('id',data.designation.id);


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
<h4 class="fw-semibold mb-4">Office Files List</h4>



<!-- Permission Table -->
<div class="card">
  <div class="card-datatable table-responsive">
    <table class="datatables-designation table border-top">
      <thead>
        <tr>
          <th></th>
          <th>User</th>

          <th>File Name</th>
          <th>Description</th>
          <th>Date</th>
          <th>Year</th>
          <th>Numbers</th>
          <th>AStatus</th>
          <th>Actions</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
<!--/ Permission Table -->


<!-- Modal -->
@include('_partials/_modals/modal-office-files')
<!-- /Modal -->
@endsection
