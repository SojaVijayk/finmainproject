@extends('layouts/layoutMaster')

@section('title', 'Designation - Master')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/spinkit/spinkit.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>

<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/block-ui/block-ui.js')}}"></script>
@endsection

@section('page-script')
<script>


$(function () {
  var dataTablePermissions = $('.datatables-designation'),
    dt_permission,
    statusObj = {
      1: { title: 'Active', class: 'bg-label-success' },
      2: { title: 'Inactive', class: 'bg-label-secondary' }
    };
    permissionList = baseUrl + 'misspunch/request-list';

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
        { data: 'formatted_date' },
        { data: 'type' },
        { data: 'checkinTime' },
        { data: 'checkoutTime' },
        { data: 'description' },
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
          targets: 3,
          render: function (data, type, full, meta) {
            var $type = full['type'];
            var $type =  ((full['type'] == 1 ) ? 'Checkin' : (full['type'] == 2 ) ? 'Checkout' : 'Checkin & Checkout');
              return '<span class="text-nowrap">' + $type + '</span>';
          }
        },


        {
          // Name
          targets: 6,
          render: function (data, type, full, meta) {
            var $type = full['description'];
            var $formatted_requested_at = full['formatted_requested_at'];

              return '<span class="text-wrap">' + $type + '</span> <br> Requested at : '+$formatted_requested_at;
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
        {{--  {
          // Name
          targets: 6,
          render: function (data, type, full, meta) {
            var $name = (full['action_by_name'] == null ? '' : full['action_by_name']);
            var $action_at = (full['action_at'] == null ? '' :full['action_at']);
            var $remark = (full['remark'] == null ? '' :full['remark']);
            return '<span class="text-nowrap">' + $name + ' <br>'+$action_at+'<br>Remark : '+$remark+'</span>';
          }
        },  --}}

        {
          // Actions
          targets: -1,
          searchable: false,
          title: 'Actions',
          orderable: false,
          render: function (data, type, full, meta) {
            if(full['status'] == 0){
              return (
              '<span class="text-nowrap"><button class="btn btn-sm btn-success  me-2 confirm-approve" data-id="'+full['id']+'" data-status="1" >Approve</button>' +
              '<button class="btn btn-sm btn-danger  confirm-approve" data-id="'+full['id']+'" data-status="2">Reject</button></span>'
            );
            }else{
              var $name = (full['action_by_name'] == null ? '' : full['action_by_name']);
            var $action_at = (full['formatted_action_at'] == null ? '' :full['formatted_action_at']);
            var $remark = (full['remark'] == null ? '' :full['remark']);
            return '<span class="text-nowrap">' + $name + ' <br>'+$action_at+'<br>Remark : '+$remark+'</span>';
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



  // Delete Record
  $('.datatables-designation tbody').on('click', '.delete-record', function () {
    dt_permission.row($(this).parents('tr')).remove().draw();
  });

    // Edit Record
    $('.datatables-designation tbody').on('click', '.confirm-approve', function () {

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
            if(status== 2){

              const { value: text } = Swal.fire({
                input: 'textarea',
                inputLabel: 'Remark',
                inputPlaceholder: 'Type your Remark here...',
                inputAttributes: {
                  'aria-label': 'Type your Remark here'
                },
              }).then((reply) => {

                if(reply.isConfirmed){


                  $(".datatables-designation").block({
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



                  console.log(reply.value);
                  $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                    }
                })
                $.ajax({
                type: "POST",

                url: '/misspunch/action/'+desig_id,
                data:  {
                  status:status,
                  remark:reply.value,
                  "_token": "{{ csrf_token() }}",
              },

                success: function (data) {

                  Swal.fire({
                    icon: 'success',
                    title: 'Updated!',
                    text: 'Request Updated.',
                    customClass: {
                      confirmButton: 'btn btn-success'
                    }
                  }).then((result) => {
                    location.reload();
                  });

                },
                error: function(data){

                }
            });
                }


              });



            }else{


            $.ajaxSetup({
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
              }
          })
          $.ajax({
          type: "POST",

          url: '/misspunch/action/'+desig_id,
          data:  {
            status:status,
            remark:'Nil',
            "_token": "{{ csrf_token() }}",
        },

          success: function (data) {

            Swal.fire({
              icon: 'success',
              title: 'Updated!',
              text: 'Request Updated.',
              customClass: {
                confirmButton: 'btn btn-success'
              }
            }).then((result) => {
              location.reload();
            });

          },
          error: function(data){

          }
      });

    }

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
<h4 class="fw-semibold mb-4">Miss Punch Regularisation</h4>

<div class="row g-4 mb-4">
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>Total Request</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{$totalCount}}</h4>
              <span class="text-success"></span>
            </div>
            <span>Just Updated</span>
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
            <span>Approved Request</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{$approved}}</h4>
              <span class="text-success"></span>
            </div>
            <span>Just Updated </span>
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
            <span>Rejected Request</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{$rejected}}</h4>
              <span class="text-danger"></span>
            </div>
            <span>just Updated</span>
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
              <span class="text-success"></span>
            </div>
            <span>Just Updated</span>
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
          <th>Date</th>
          <th>Type</th>
          <th>In Time</th>
          <th>Out Time</th>
          <th>Description</th>
          <th>Status</th>
          {{--  <th>Action By</th>  --}}
          <th>Actions</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
<!--/ Permission Table -->


@endsection
