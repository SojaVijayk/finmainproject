@extends('layouts/layoutMaster')

@section('title', 'Leave - Assign')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>

<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>

<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bootstrap-select/bootstrap-select.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('assets/js/forms-selects.js')}}"></script>

<script>


$(function () {

  var dataTablePermissions = $('.datatables-leave'),
    dt_permission,
    permissionList = baseUrl + 'leave-assign/list';

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
        { data: 'id' },
        { data: 'leave_type' },
        { data: 'employment_type' },
        { data: 'total_credit' },
        { data: 'status' },
        { data: 'created_at' },
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
          targets: 1,
          searchable: false,
          visible: true
        },
        {
          // Name
          targets: 2,
          render: function (data, type, full, meta) {
            var $name = full['leave_type'];
            return '<span class="text-nowrap">' + $name + '</span>';
          }
        },
        {
          // Name
          targets: 3,
          render: function (data, type, full, meta) {
            var $name = full['employment_type'];
            return '<span class="text-nowrap">' + $name + '</span>';
          }
        },
        {
          // Name
          targets: 4,
          render: function (data, type, full, meta) {
            var $name = full['total_credit'];
            return '<span class="text-nowrap">' + $name + '</span>';
          }
        },
        {
          // User Role
          targets: 5,
          render: function (data, type, full, meta) {
            var $status = full['status'];
            $out = ($status==1 ? '<a><span class="badge bg-label-success m-1">Active</span></a>' : '<a><span class="badge bg-label-warning m-1">Inactive</span></a>' )
            return  $out;
          }
        },

        {
          // remove ordering from Name
          targets: 6,
          orderable: false,
          render: function (data, type, full, meta) {
            var $date = full['created_at'];
            return '<span class="text-nowrap">' + $date + '</span>';
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
              '<span class="text-nowrap"><button class="btn btn-sm btn-icon me-2 edit-leave" data-id="'+full['id']+'" data-bs-target="#LeaveModal" data-bs-toggle="modal" data-bs-dismiss="modal"><i class="ti ti-edit"></i></button>' +
              '<button class="btn btn-sm btn-icon delete-record" disabled><i class="ti ti-trash"></i></button></span>'
            );
          }
        }
      ],
      order: [[1, 'asc']],
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
          text: 'Add Leave',
          className: 'add-new btn btn-primary mb-3 mb-md-0 add-new-leave',
          attr: {
            'data-bs-toggle': 'modal',
            'data-bs-target': '#LeaveModal'
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




// Add/Edit leave form validation
document.addEventListener('DOMContentLoaded', function (e) {

  (function () {
    FormValidation.formValidation(document.getElementById('leaveForm'), {
      fields: {
        leave_type: {
          validators: {
            notEmpty: {
              message: 'Please Select leave '
            }
          }
        },
        total_credit: {
          validators: {
            notEmpty: {
              message: 'Please enter credit'
            }
          }
        },
        employment_type: {
          validators: {
            notEmpty: {
              message: 'Please select employment type'
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
  var leaveEditList = document.querySelectorAll('.datatables-leave .edit-leave'),
    permissionAdd = document.querySelector('.add-new-leave'),
    leaveSubmit = document.querySelector('.submit-leave');
    leaveTitle = document.querySelector('.leave-title'),

    $('#LeaveModal').on('hidden.bs.modal', function (e) {
      $(this)
        .find("input,textarea,select")
           .val('')
           .end()
        .find("input[type=checkbox], input[type=radio]")
           .prop("checked", "")
           .end();
    })

    leaveSubmit.onclick = function () {


   var  leave_type =  $("#leave_type").val();
   var total_credit = $("#total_credit").val();
   var employment_type = $("#employment_type").val();
   var type =   $("#submit_leave").data('type');
   var leave_asign_id =   $("#submit_leave").data('id');
   if(type=='new'){
      $.ajax({
        data:  {
          leave_type:leave_type,
          total_credit:total_credit,
          employment_type:employment_type,
          "_token": "{{ csrf_token() }}",

      },
        url: `${baseUrl}leave-assign/store`,
        type: 'POST',

        success: function (status) {

            $('#LeaveModal').modal('hide');
          // sweetalert
          Swal.fire({
            icon: 'success',
            title: `Successfully ${status}!`,
            text: `Leave ${status} Successfully.`,
            customClass: {
              confirmButton: 'btn btn-success'
            }
          }).then((result) => {
            location.reload();
          });

        },
        error: function (err) {
          $('#LeaveModal').modal('hide');
          Swal.fire({
            title: 'Oh Sorry!',
            text: `${status}`,
            icon: 'error',
            customClass: {
              confirmButton: 'btn btn-success'
            }
          }).then((result) => {
            location.reload();
          });
        }
      });
    }
    else{
      $.ajax({
        data:  {
          leave_type:leave_type,
          total_credit:total_credit,
          employment_type:employment_type,

          "_token": "{{ csrf_token() }}",

      },
        url: `${baseUrl}leave-assign/update/${leave_asign_id}`,
        type: 'POST',

        success: function (status) {

            $('#LeaveModal').modal('hide');
          // sweetalert
          Swal.fire({
            icon: 'success',
            title: `Successfully ${status}!`,
            text: `Leave ${status} Successfully.`,
            customClass: {
              confirmButton: 'btn btn-success'
            }
          }).then((result) => {
            location.reload();
          });

        },
        error: function (err) {
          $('#LeaveModal').modal('hide');
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





})();

  // Delete Record
  $('.datatables-leave tbody').on('click', '.delete-record', function () {
    dt_permission.row($(this).parents('tr')).remove().draw();
  });

    // Edit Record
    $('.datatables-leave tbody').on('click', '.edit-leave', function () {
      leaveTitle.innerHTML = 'Edit Leave'; // reset text
        $("#submit_leave").attr('data-type','edit');

        var desig_id = $(this).data('id');
        $("#submit_leave").attr('data-id',desig_id);
        $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      })
      $.ajax({
      type: "GET",

      url: '/leave-assign/edit/'+desig_id,
      success: function (data) {
        console.log(data);
          $("#leave_type").val(data.leaves.leave_type);
          $("#employment_type").val(data.leaves.employment_type);
          $("#total_credit").val(data.leaves.total_credit);
          $("#submit_leave").data('id',data.leaves.id);
          $(".select2").select2();


      },
      error: function(data){

      }
  });

      {{--  dt_permission.row($(this)).draw();  --}}
    });

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
<h4 class="fw-semibold mb-4">Leave Assign </h4>

<p class="mb-4">Each category (Basic, Professional, and Business) includes the four predefined roles shown below.</p>

<!-- Permission Table -->
<div class="card">
  <div class="card-datatable table-responsive">
    <table class="datatables-leave table border-top">
      <thead>
        <tr>
          <th></th>
          <th>ID</th>
          <th>Leave</th>
          <th>Employment Type</th>
          <th>Total Credit</th>
          <th>Status</th>
          <th>Created Date</th>
          <th>Actions</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
<!--/ Permission Table -->


<!-- Modal -->
@include('_partials/_modals/modal-leave-assign',['leave_types' => $leave_types,'employment_types' => $employment_types])
<!-- /Modal -->
@endsection
