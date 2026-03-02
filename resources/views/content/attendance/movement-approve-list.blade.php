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
    permissionList = baseUrl + 'movement/request-list';

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
        { data: 'title' },
        { data: 'title' },

        { data: 'start_date' },
        {{--  { data: 'end_date' },  --}}
        { data: 'status' },
         { data: 'report' },
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
            var $name = full['title'];
            var $location = full['location'];
            var $description = full['description'];
            var $type = full['type'];
            var $requested_at = full['formatted_requested_at'];
            return '<span class="fw-semibold text-gray"> Title :</span> <span class="text-wrap">' + $name + '</span><br><span class="fw-semibold text-gray"> Type :</span><span class=""> ' + $type + '</span><br><span class=" text-gray fw-semibold"> Location :</span><span class="text-nowrap"> ' + $location + '</span><br><span class="fw-semibold text-gray"> Des :</span><span class=""> ' + $description + '</span>'+
            '<br><span class="fw-semibold text-gray"> requested_at :</span><span class=""> ' + $requested_at + '</span>';
          }
        },

        {
          // Reference
          targets: 3,
          title: 'Reference',
          render: function (data, type, full, meta) {
             var $refTitle = full['ref_title'];
            var $refCode = full['ref_code'];
            var $refType = full['ref_type_label'];
            if($refTitle) {
                 return '<span class="text-nowrap">' + $refType + ': ' + $refCode + '</span><br><span class="text-muted" style="font-size:0.85em;">' + $refTitle + '</span>';
            }
            return '<span class="text-nowrap">-</span>';
          }
        },
        {
          // Name
          targets: 4,
          render: function (data, type, full, meta) {
            var $name = full['formatted_start_date'];
            var $time = full['start_time'];
            var $name2 = full['formatted_end_date'];
            var $time2 = full['end_time'];
            return 'From <span class="text-nowrap">' + $name + '-'+$time+'</span><br> To <span class="text-nowrap">' + $name2 + '-'+$time2+'</span>';
          }
        },
        {{--  {
          // Name
          targets: 4,
          render: function (data, type, full, meta) {
            var $name = full['end_date'];
            var $time = full['end_time'];
            return '<span class="text-nowrap">' + $name + '-'+$time+'</span>';
          }
        },  --}}



        {
          // User Role
          targets: 5,
          render: function (data, type, full, meta) {
            var $status = full['status'];
            $out = ($status==1 ? '<a><span class="badge bg-label-success m-1">Approved</span></a>' : ($status==2 ? '<a><span class="badge bg-label-danger m-1">Rejected</span></a>' : '<a><span class="badge bg-label-warning m-1">Pending</span></a>')  )
            return  $out;
          }
        },

         {
          // report
          targets: 6,
          render: function (data, type, full, meta) {
            if(full['type'] == 'Official' && new Date(full['start_date']) >= new Date('2025-06-01')){
                  if(full['report'] == '' || full['report'] == null){
                   return (
                    '<span class="text-nowrap badge text-bg-danger"> Not Updated' +
                    '</span>'
                  );
                  }
                  else{
                    return ('<span class="text-nowrap"><button class="btn btn-primary btn-sm  me-2 view-report" data-id="'+full['id']+'" data-bs-target="#viewReportModal" data-bs-toggle="modal" data-bs-dismiss="modal">View Report</button> </span>');

                  }
               }
               else{
                        return (
                    '<span class="text-nowrap">N/A' +
                    '</span>'
                  );
               }

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
            var remark_placeholder;
            {{--  if(status== 2){  --}}

              const { value: text } = Swal.fire({
                input: 'textarea',
                inputLabel: 'Remark',
                inputPlaceholder: 'Type your Remark here...',
                 inputValue: 'Nil',
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

                url: '/movement/action/'+desig_id,
                data:  {
                  status:status,
                  remark:reply.value,
                  "_token": "{{ csrf_token() }}",
              },

                success: function (data) {

                  Swal.fire({
                    icon: 'success',
                    title: 'Updated!',
                    text: 'Movement Request Updated.',
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



            {{--  }
            else{


            $.ajaxSetup({
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
              }
          })
          $.ajax({
          type: "POST",

          url: '/movement/action/'+desig_id,
          data:  {
            status:status,
            remark:'Nil',
            "_token": "{{ csrf_token() }}",
        },

          success: function (data) {

            Swal.fire({
              icon: 'success',
              title: 'Updated!',
              text: 'Movement Request Updated.',
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

    }  --}}

          }
        });



      {{--  dt_permission.row($(this)).draw();  --}}
    });

     $('.datatables-designation tbody').on('click', '.view-report', function () {

        var desig_id = $(this).data('id');
        $("#submit_designation").attr('data-id',desig_id);
        $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      })
      $.ajax({
      type: "GET",
      url: '/movement/edit/'+desig_id,
      success: function (data) {
        console.log(data);

          $("#eventReportData").html(data.designation.report);
          $("#eventReport_updated_at").html(data.designation.report_updated_at);

      },
      error: function(data){

      }
  });


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
<h4 class="fw-semibold mb-4">Movement Approval List</h4>

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
          <th>Movement Details</th>
          <th>Reference</th>
          <th>TIME</th>
          {{-- <th>To</th> --}}
          <th>Status</th>
          <th>Report</th>
          {{-- <th>Action By</th> --}}
          <th>Actions</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
<!--/ Permission Table -->

@include('_partials/_modals/modal-movement-status-report-view')
@endsection