@extends('layouts/layoutMaster')

@section('title', 'Designation - Master')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>

<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
@endsection

@section('page-script')
<script>
  $(function () {

  $.fn.modal.Constructor.prototype.enforceFocus = function () {};
  $('#DesignationModal').on('hidden.bs.modal', function (e) {
    $(this)
      .find("input,textarea,select")
         .val('')
         .end()
      .find("input[type=checkbox], input[type=radio]")
         .prop("checked", "")
         .end();


  })


  var dataTablePermissions = $('.datatables-designation'),
    dt_permission,
    statusObj = {
      1: { title: 'Active', class: 'bg-label-success' },
      2: { title: 'Inactive', class: 'bg-label-secondary' }
    };
    permissionList = baseUrl + 'leave/request-list';

    function convertDateFormat(dateString) {
      // Split the input date string by the hyphen
      const [year, month, day] = dateString.split('-');

      // Rearrange into the desired format
      const formattedDate = `${day}-${month}-${year}`;

      return formattedDate;
  }

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
        { data: 'leave_type' },
        { data: 'leave_request_details' },
         { data: 'duty_assignments' },
        { data: 'requested_at' },
        { data: 'status' },
        {{--  { data: 'action_by' },  --}}
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
            var $name = full['name'];
            var $designation = full['designation'];
            $image = full['profile_pic'];
            if ($image) {
              // For Avatar image
              var $output =
                '<img src="' + assetsPath + 'img/avatars/' + $image + '" alt="Avatar" class="rounded-circle">';
            } else {
              // For Avatar badge
              var stateNum = Math.floor(Math.random() * 6);
              var states = ['success', 'danger', 'warning', 'info', 'primary', 'secondary'];
              var $state = states[stateNum],
                $name = full['name'],
                $initials = $name.match(/\b\w/g) || [];
              $initials = (($initials.shift() || '') + ($initials.pop() || '')).toUpperCase();
              $output = '<span class="avatar-initial rounded-circle bg-label-' + $state + '">' + $initials + '</span>';
            }
            var $row_output =
            '<div class="d-flex justify-content-start align-items-center user-name">' +
            '<div class="avatar-wrapper">' +
            '<div class="avatar avatar-sm me-3">' +
            $output +
            '</div>' +
            '</div>' +
            '<div class="d-flex flex-column">' +
            '<span class="fw-semibold">' +
            $name +
            '</span></a>' +
            '<small class="text-muted">' +
            $designation +
            '</small>' +
            '</div>' +
            '</div>';
          return $row_output;
            {{--  return '<span class="text-nowrap">' + $name + '<br>' + $designation + '</span>';  --}}
          }
        },
        {
          // Name
          targets: 2,
          render: function (data, type, full, meta) {
            var $leave_type = full['leave_type'];
            var $duration = full['duration'];
            return '<span class="text-nowrap">' + $leave_type + '</span><br><span class="text-nowrap"> Duration : ' + $duration + '</span>';
          }
        },
        {
          targets: 3,
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
          // Actions
          targets: -1,
          searchable: false,
          title: 'Actions',
          orderable: false,
          render: function (data, type, full, meta) {
            if(full['status'] == 0){
              return (
              '<span class="text-nowrap"><button class="btn btn-sm btn-success  me-2 edit-designation" data-id="'+full['id']+'" data-bs-target="#leaveActionModal" data-bs-toggle="modal" data-bs-dismiss="modal">Take Action</button>' +
              '</span>'
            );
            }
            else if(full['status'] == 1){
              return (
              '<span class="text-nowrap"><button class="btn btn-sm btn-dark  me-2 edit-designation" data-id="'+full['id']+'" data-bs-target="#leaveActionModal" data-bs-toggle="modal" data-bs-dismiss="modal">Complete Action</button>' +
              '</span>'
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




// Add/Edit designation form validation
document.addEventListener('DOMContentLoaded', function (e) {
  (function () {
    FormValidation.formValidation(document.getElementById('designationForm'), {
      fields: {
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

$('.datatables-leave-list tbody').on('click', '.confirm-action', function () {

  var desig_id = $(this).data('id');
  var status = $(this).data('status');
  var status_text= ($(this).data('status') == 1 ? 'Approve' : 'Reject')
  Swal.fire({
    title: 'Are you sure?',
    text: "You won't be able to revert this!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, '+status_text+' it!',
    customClass: {
      confirmButton: 'btn btn-primary me-1',
      cancelButton: 'btn btn-label-secondary'
    },
    buttonsStyling: false
  }).then(function(result) {
    if (result.value) {

      if(status==1){
        $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      })
      $.ajax({
      type: "POST",

      url: '/leave/request/action/'+desig_id,
      data:  {
        status:status,
        remark:'Nil',
        "_token": "{{ csrf_token() }}",
    },

      success: function (data) {
        var tbody='';
        data.leave_list.leave_request_details.forEach((item, index) => {
          tbody=tbody+'<tr><td>'+data.leave_list.leave_type+'</td><td>'+item.date+'</td><td>'+(item.leave_day_type == 1 ? 'Full Day' : item.leave_day_type == 2 ? 'FN' : 'AN')+'</td>'+
            '<td>'+(item.status == 0 ? '<span class="text-nowrap"><button class="btn btn-sm btn-success  me-2 confirm-action" data-status="1" data-id="'+item['id']+'"  >Approve</button><button class="btn btn-sm btn-danger  me-2 confirm-action" data-status="2" data-id="'+item['id']+'"  >Reject</button></span></td></tr>' : (item.status == 1 ? '<span class="badge bg-label-success">Approved</span><br>Remark : '+item.remark+' ': '<span class="badge bg-label-danger">Rejected</span> <br>Remark : '+item.remark+ ''));
          })
        $(".datatables-leave-list #dataList").html(tbody);
        $(".leave-type-name").html(data.leave_list.leave_type);
        $(".leave-total-credit").html(data.leave_balance.total_leaves_credit);
        $(".leave-total-availed").html(data.leave_balance.availed_leave);
        $(".leave-total-requested").html(data.leave_balance.pending_leave);
        $(".leave-total-balance").html(data.leave_balance.balance_credit);
        Swal.fire({
          icon: 'success',
          title: 'Updated!',
          text: 'Leave Request Action Updated.',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });

      },
      error: function(data){

      }
  });
}
else if(status== 2){

  $.fn.modal.Constructor.prototype.enforceFocus = function () {};
  const { value: text } = Swal.fire({
    input: 'textarea',
    inputLabel: 'Remark',
    inputPlaceholder: 'Type your Remark here...',
    {{--  inputAttributes: {
      'aria-label': 'Type your Remark here'
    },  --}}
  }).then((reply) => {
    if(reply.isConfirmed){
      console.log(reply.value);

      $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    })
    $.ajax({
    type: "POST",

    url: '/leave/request/action/'+desig_id,
    data:  {
      status:status,
      remark:reply.value,
      "_token": "{{ csrf_token() }}",
  },

    success: function (data) {
      var tbody='';
      data.leave_list.leave_request_details.forEach((item, index) => {

        const converted_date = convertDateFormat(item.date);
        console.log(converted_date);
        tbody=tbody+'<tr><td>'+data.leave_list.leave_type+'</td><td>'+converted_date+'</td><td>'+(item.leave_day_type == 1 ? 'Full Day' : item.leave_day_type == 2 ? 'FN' : 'AN')+'</td>'+
          '<td>'+(item.status == 0 ? '<span class="text-nowrap"><button class="btn btn-sm btn-success  me-2 confirm-action" data-status="1" data-id="'+item['id']+'"  >Approve</button><button class="btn btn-sm btn-danger  me-2 confirm-action" data-status="2" data-id="'+item['id']+'"  >Reject</button></span></td></tr>' : (item.status == 1 ? '<span class="badge bg-label-success">Approved</span><br>Remark : '+item.remark+' ': '<span class="badge bg-label-danger">Rejected</span> <br>Remark : '+item.remark+ ''));
       })
      $(".datatables-leave-list #dataList").html(tbody);
      $(".leave-type-name").html(data.leave_list.leave_type);
      $(".leave-total-credit").html(data.leave_balance.total_leaves_credit);
      $(".leave-total-availed").html(data.leave_balance.availed_leave);
      $(".leave-total-requested").html(data.leave_balance.pending_leave);
      $(".leave-total-balance").html(data.leave_balance.balance_credit);
      Swal.fire({
        icon: 'success',
        title: 'Updated!',
        text: 'Leave Request Action Updated.',
        customClass: {
          confirmButton: 'btn btn-success'
        }
      });

    },
    error: function(data){

    }
});
    }


  });

}


    }
  });







{{--  dt_permission.row($(this)).draw();  --}}
});


  // Delete Record
  $('.datatables-designation tbody').on('click', '.delete-record', function () {
    dt_permission.row($(this).parents('tr')).remove().draw();
  });

    // Edit Record
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
            '<td>'+(item.status == 0 ? '<span class="text-nowrap"><button class="btn btn-sm btn-success  me-2 confirm-action" data-status="1" data-id="'+item['id']+'"  >Approve</button><button class="btn btn-sm btn-danger  me-2 confirm-action" data-status="2" data-id="'+item['id']+'"  >Reject</button></span></td></tr>' : (item.status == 1 ? '<span class="badge bg-label-success">Approved</span><br>Remark : '+item.remark+' ': '<span class="badge bg-label-danger">Rejected</span> <br>Remark : '+item.remark+ '</span>'));
           })
        $(".datatables-leave-list #dataList").html(tbody);
        $(".leave-type-name").html(data.leave_list.leave_type);
        $(".leave-total-credit").html(data.leave_balance.total_leaves_credit);
        $(".leave-total-availed").html(data.leave_balance.availed_leave);
        $(".leave-total-requested").html(data.leave_balance.pending_leave);
        $(".leave-total-balance").html(data.leave_balance.balance_credit);

        $(".leave-start").html(data.date_start);
        $(".leave-end").html(data.date_end);




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



  $('#leaveActionModal').on('shown.bs.modal', function (e) {
    $(document).off('focusin.modal');


    })
    $('#leaveActionModal').on('hidden.bs.modal', function () {
      location.reload();
  });



});
</script>



@endsection

@section('content')
<h4 class="fw-semibold mb-4">Leave Approval List</h4>

<div class="row g-4 mb-4">
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>Total Request</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{$totalCount}}</h4>

            </div>

          </div>
          <span class="badge bg-label-primary rounded p-2">
            <i class="ti ti-user ti-sm"></i>
          </span>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>Action Started</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{$action_started}}</h4>

            </div>

          </div>
          <span class="badge bg-label-danger rounded p-2">
            <i class="ti ti-user-plus ti-sm"></i>
          </span>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>Completed Request</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{$completed}}</h4>

            </div>

          </div>
          <span class="badge bg-label-success rounded p-2">
            <i class="ti ti-user-check ti-sm"></i>
          </span>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>Pending Request</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{$pending}}</h4>
            </div>

          </div>
          <span class="badge bg-label-warning rounded p-2">
            <i class="ti ti-user-exclamation ti-sm"></i>
          </span>
        </div>
      </div>
    </div>
  </div>
</div>


<!-- Permission Table -->
<div class="card">
  <div class="card-datatable table-responsive">
    <table class="datatables-designation table border-top">
      <thead>
        <tr>
          <th></th>
          <th>User</th>
          <th>Leave Type</th>
          <th>Leave Days</th>
          <th>Duty Assigned</th>
          <th>Requested_at</th>
          <th>Status</th>
          {{-- <th>Action By</th> --}}
          <th>Actions</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
<!--/ Permission Table -->


<!-- Modal -->
@include('_partials/_modals/modal-leave-action')
<!-- /Modal -->
@endsection