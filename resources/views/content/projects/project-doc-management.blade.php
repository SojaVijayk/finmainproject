@extends('layouts/layoutMaster')

@section('title', 'User List - Pages')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bs-stepper/bs-stepper.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-select/bootstrap-select.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/dropzone/dropzone.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/jstree/jstree.css')}}" />


@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/cleavejs/cleave.js')}}"></script>
<script src="{{asset('assets/vendor/libs/cleavejs/cleave-phone.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bs-stepper/bs-stepper.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bootstrap-select/bootstrap-select.js')}}"></script>
<script src="{{asset('assets/vendor/libs/dropzone/dropzone.js')}}" ></script>
<script src="{{asset('assets/vendor/libs/jstree/jstree.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('assets/js/form-wizard-icons.js')}}"></script>3
<script src="{{asset('assets/js/forms-file-upload.js')}}"></script>
<script src="{{asset('assets/js/extended-ui-treeview.js')}}"></script>
<script>


  /**
 * Page User List
 */

'use strict';

// Datatable (jquery)
$(function () {
  let borderColor, bodyBg, headingColor;

  if (isDarkStyle) {
    borderColor = config.colors_dark.borderColor;
    bodyBg = config.colors_dark.bodyBg;
    headingColor = config.colors_dark.headingColor;
  } else {
    borderColor = config.colors.borderColor;
    bodyBg = config.colors.bodyBg;
    headingColor = config.colors.headingColor;
  }

  // Variable declaration for table
  var dt_user_table = $('.datatables-users'),userView,select2,statusObj, offCanvasForm = $('#offcanvasAddUser'),
      usersList = baseUrl + 'project/employees/detail/list',

    select2 = $('.select2'),
    userView = baseUrl + 'project/employee/view/account',

    statusObj = {
      1: { title: 'Active', class: 'bg-label-success' },
      2: { title: 'Inactive', class: 'bg-label-secondary' }
    };





  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });



  if (select2.length) {
    var $this = select2;
    $this.wrap('<div class="position-relative"></div>').select2({
      placeholder: 'Select Privilege',
      dropdownParent: $this.parent()
    });
  }

  // Users datatable
  if (dt_user_table.length) {
   var dt_user = dt_user_table.DataTable({

      ajax: {
        url: usersList
       },
      columns: [
        // columns according to JSON
        { data: '' },
        { data: 'name' },
        { data: 'gender_name' },
        { data: 'user_type' },
        { data: 'designation' },
        { data: 'status' },
        { data: 'empId' },
        { data: 'action' }
      ],
      columnDefs: [
        {
          // For Responsive
          className: 'control',
          searchable: false,
          orderable: false,
          responsivePriority: 2,
          targets: 0,
          render: function (data, type, full, meta) {
            return '';
          }
        },
        {
          // User full name and email
          targets: 1,
          responsivePriority: 4,
          render: function (data, type, full, meta) {
            var $name = full['name'],
              $email = full['email_pri'],
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
            // Creates full output for row
            var $row_output =
              '<div class="d-flex justify-content-start align-items-center user-name">' +
              '<div class="avatar-wrapper">' +
              '<div class="avatar avatar-sm me-3">' +
              $output +
              '</div>' +
              '</div>' +
              '<div class="d-flex flex-column">' +
              '<a href="' +
              userView +'/'+full['user_id']+
              '" class="text-body text-truncate"><span class="fw-semibold">' +
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
          // User Role
          targets: 2,
          render: function (data, type, full, meta) {
            var $role = full['user_type'];
            var roleBadgeObj = {
              Contract:
                '<span class="badge badge-center rounded-pill bg-label-warning w-px-30 h-px-30 me-2"><i class="ti ti-user ti-sm"></i></span>',
              Consultant:
                '<span class="badge badge-center rounded-pill bg-label-success w-px-30 h-px-30 me-2"><i class="ti ti-circle-check ti-sm"></i></span>',
              DailyWage:
                '<span class="badge badge-center rounded-pill bg-label-primary w-px-30 h-px-30 me-2"><i class="ti ti-chart-pie-2 ti-sm"></i></span>',
              Editor:
                '<span class="badge badge-center rounded-pill bg-label-info w-px-30 h-px-30 me-2"><i class="ti ti-edit ti-sm"></i></span>',
              Admin:
                '<span class="badge badge-center rounded-pill bg-label-secondary w-px-30 h-px-30 me-2"><i class="ti ti-device-laptop ti-sm"></i></span>'
            };
            return "<span class='text-truncate d-flex align-items-center'>" + roleBadgeObj[$role] + $role + '</span>';
          }
        },
        {
          // Designation
          targets: 3,
          render: function (data, type, full, meta) {
            var $plan = full['designation'];

            return '<span class="fw-semibold">' + $plan + '</span>';
          }
        },
        {
          // Designation
          targets: 4,
          render: function (data, type, full, meta) {
            var $plan = full['gender_name'];

            return '<span class="fw-semibold">' + $plan + '</span>';
          }
        },

        {
          // User Status
          targets: 5,
          render: function (data, type, full, meta) {
            var $status = full['status'];

            return (
              '<span class="badge ' +
              statusObj[$status].class +
              '" text-capitalized>' +
              statusObj[$status].title +
              '</span>'
            );
          }
        },
        {
          // Designation
          targets: 6,
          render: function (data, type, full, meta) {
            var $plan = full['empId'];

            return '<span class="fw-semibold">' + $plan + '</span>';
          }
        },
        {
          // Actions
          targets: -1,
          title: 'Actions',
          searchable: false,
          orderable: false,
          render: function (data, type, full, meta) {
            return (
              '<div class="d-flex align-items-center">' +
              '<a href="javascript:;" class="text-body"><i class="ti ti-edit ti-sm me-2"></i></a>' +
              '<a href="javascript:;" class="text-body delete-record"><i class="ti ti-trash ti-sm mx-2"></i></a>' +
              '<a href="javascript:;" class="text-body dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical ti-sm mx-1"></i></a>' +
              '<div class="dropdown-menu dropdown-menu-end m-0">' +
              '<a href="' +
              userView +
              '" class="dropdown-item">View</a>' +
              '<a href="javascript:;" class="dropdown-item">Suspend</a>' +
              '</div>' +
              '</div>'
            );
          }
        }
      ],
      order: [[1, 'desc']],
      dom:
        '<"row me-2"' +
        '<"col-md-2"<"me-3"l>>' +
        '<"col-md-10"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-3 mb-md-0"fB>>' +
        '>t' +
        '<"row mx-2"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6"p>' +
        '>',
      language: {
        sLengthMenu: '_MENU_',
        search: '',
        searchPlaceholder: 'Search..'
      },
      // Buttons with Dropdown
      buttons: [
        {
          extend: 'collection',
          className: 'btn btn-label-secondary dropdown-toggle mx-3',
          text: '<i class="ti ti-screen-share me-1 ti-xs"></i>Export',
          buttons: [
            {
              extend: 'print',
              text: '<i class="ti ti-printer me-2" ></i>Print',
              className: 'dropdown-item',
              exportOptions: {
                columns: [1, 2, 3, 4, 5],
                // prevent avatar to be print
                format: {
                  body: function (inner, coldex, rowdex) {
                    if (inner.length <= 0) return inner;
                    var el = $.parseHTML(inner);
                    var result = '';
                    $.each(el, function (index, item) {
                      if (item.classList !== undefined && item.classList.contains('user-name')) {
                        result = result + item.lastChild.firstChild.textContent;
                      } else if (item.innerText === undefined) {
                        result = result + item.textContent;
                      } else result = result + item.innerText;
                    });
                    return result;
                  }
                }
              },
              customize: function (win) {
                //customize print view for dark
                $(win.document.body)
                  .css('color', headingColor)
                  .css('border-color', borderColor)
                  .css('background-color', bodyBg);
                $(win.document.body)
                  .find('table')
                  .addClass('compact')
                  .css('color', 'inherit')
                  .css('border-color', 'inherit')
                  .css('background-color', 'inherit');
              }
            },
            {
              extend: 'csv',
              text: '<i class="ti ti-file-text me-2" ></i>Csv',
              className: 'dropdown-item',
              exportOptions: {
                columns: [1, 2, 3, 4, 5],
                // prevent avatar to be display
                format: {
                  body: function (inner, coldex, rowdex) {
                    if (inner.length <= 0) return inner;
                    var el = $.parseHTML(inner);
                    var result = '';
                    $.each(el, function (index, item) {
                      if (item.classList !== undefined && item.classList.contains('user-name')) {
                        result = result + item.lastChild.firstChild.textContent;
                      } else if (item.innerText === undefined) {
                        result = result + item.textContent;
                      } else result = result + item.innerText;
                    });
                    return result;
                  }
                }
              }
            },
            {
              extend: 'excel',
              text: '<i class="ti ti-file-spreadsheet me-2"></i>Excel',
              className: 'dropdown-item',
              exportOptions: {
                columns: [1, 2, 3, 4, 5],
                // prevent avatar to be display
                format: {
                  body: function (inner, coldex, rowdex) {
                    if (inner.length <= 0) return inner;
                    var el = $.parseHTML(inner);
                    var result = '';
                    $.each(el, function (index, item) {
                      if (item.classList !== undefined && item.classList.contains('user-name')) {
                        result = result + item.lastChild.firstChild.textContent;
                      } else if (item.innerText === undefined) {
                        result = result + item.textContent;
                      } else result = result + item.innerText;
                    });
                    return result;
                  }
                }
              }
            },
            {
              extend: 'pdf',
              text: '<i class="ti ti-file-code-2 me-2"></i>Pdf',
              className: 'dropdown-item',
              exportOptions: {
                columns: [1, 2, 3, 4, 5],
                // prevent avatar to be display
                format: {
                  body: function (inner, coldex, rowdex) {
                    if (inner.length <= 0) return inner;
                    var el = $.parseHTML(inner);
                    var result = '';
                    $.each(el, function (index, item) {
                      if (item.classList !== undefined && item.classList.contains('user-name')) {
                        result = result + item.lastChild.firstChild.textContent;
                      } else if (item.innerText === undefined) {
                        result = result + item.textContent;
                      } else result = result + item.innerText;
                    });
                    return result;
                  }
                }
              }
            },
            {
              extend: 'copy',
              text: '<i class="ti ti-copy me-2" ></i>Copy',
              className: 'dropdown-item',
              exportOptions: {
                columns: [1, 2, 3, 4, 5],
                // prevent avatar to be display
                format: {
                  body: function (inner, coldex, rowdex) {
                    if (inner.length <= 0) return inner;
                    var el = $.parseHTML(inner);
                    var result = '';
                    $.each(el, function (index, item) {
                      if (item.classList !== undefined && item.classList.contains('user-name')) {
                        result = result + item.lastChild.firstChild.textContent;
                      } else if (item.innerText === undefined) {
                        result = result + item.textContent;
                      } else result = result + item.innerText;
                    });
                    return result;
                  }
                }
              }
            }
          ]
        },
        {{--  {
          text: '<i class="ti ti-plus me-0 me-sm-1 ti-xs"></i><span class="d-none d-sm-inline-block">Add New User</span>',
          className: 'add-new btn btn-primary',
          attr: {
            'data-bs-toggle': 'offcanvas',
            'data-bs-target': '#offcanvasAddUser'
          }
        },  --}}
        {
          text: '<i class="ti ti-plus me-0 me-sm-1 ti-xs"></i><span class="d-none d-sm-inline-block">Add New User</span>',
          className: 'add-new btn btn-primary',
          attr: {
            'data-bs-toggle': 'modal',
            'data-bs-target': '#fullscreenModal'
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
      },
      initComplete: function () {
        // Adding role filter once table initialized
        this.api()
          .columns(3)
          .every(function () {
            var column = this;
            var select = $(
              '<select id="UserRole" class="form-select text-capitalize"><option value=""> Select User Type </option></select>'
            )
              .appendTo('.user_role')
              .on('change', function () {
                var val = $.fn.dataTable.util.escapeRegex($(this).val());
                column.search(val ? '^' + val + '$' : '', true, false).draw();
              });

            column
              .data()
              .unique()
              .sort()
              .each(function (d, j) {
                select.append('<option value="' + d + '">' + d + '</option>');
              });
          });
        // Adding plan filter once table initialized
        this.api()
          .columns(4)
          .every(function () {
            var column = this;
            var select = $(
              '<select id="UserPlan" class="form-select text-capitalize"><option value=""> Select Designation </option></select>'
            )
              .appendTo('.user_plan')
              .on('change', function () {
                var val = $.fn.dataTable.util.escapeRegex($(this).val());
                column.search(val ? '^' + val + '$' : '', true, false).draw();
              });

            column
              .data()
              .unique()
              .sort()
              .each(function (d, j) {
                select.append('<option value="' + d + '">' + d + '</option>');
              });
          });
        // Adding status filter once table initialized
        this.api()
          .columns(5)
          .every(function () {
            var column = this;
            var select = $(
              '<select id="FilterTransaction" class="form-select text-capitalize"><option value=""> Select Status </option></select>'
            )
              .appendTo('.user_status')
              .on('change', function () {
                var val = $.fn.dataTable.util.escapeRegex($(this).val());
                column.search(val ? '^' + val + '$' : '', true, false).draw();
              });

            column
              .data()
              .unique()
              .sort()
              .each(function (d, j) {
                select.append(
                  '<option value="' +
                    statusObj[d].title +
                    '" class="text-capitalize">' +
                    statusObj[d].title +
                    '</option>'
                );
              });
          });
      }
    });
  }

  // Delete Record
  $('.datatables-users tbody').on('click', '.delete-record', function () {
    dt_user.row($(this).parents('tr')).remove().draw();
  });

  // Filter form control to default size
  // ? setTimeout used for multilingual table initialization
  setTimeout(() => {
    $('.dataTables_filter .form-control').removeClass('form-control-sm');
    $('.dataTables_length .form-select').removeClass('form-select-sm');
  }, 300);
});

// Validation & Phone mask
(function () {

  $("#jstree-basic").jstree()


  const phoneMaskList = document.querySelectorAll('.phone-mask'),
    addNewUserForm = document.getElementById('addNewUserForm');

  // Phone Number
  {{--  if (phoneMaskList) {
    phoneMaskList.forEach(function (phoneMask) {
      new Cleave(phoneMask, {
        phone: true,
        phoneRegionCode: 'US'
      });
    });
  }  --}}
  // Add New User Form Validation
  const fv = FormValidation.formValidation(addNewUserForm, {
    fields: {
      name: {
        validators: {
          notEmpty: {
            message: 'Please enter fullname '
          }
        }
      },
      email: {
        validators: {
          notEmpty: {
            message: 'Please enter your email'
          },
          emailAddress: {
            message: 'The value is not a valid email address'
          }
        }
      },
      mobile: {
        validators: {
          notEmpty: {
            message: 'Please enter your contact number'
          },
          number: {
            message: 'The value is not a valid number'
          }
        }
      },
      {{--  designation: {
        validators: {
          notEmpty: {
            message: 'Please select designation '
          }
        }
      },
      usertype_role: {
        validators: {
          notEmpty: {
            message: 'Please select user type '
          }
        }
      },
      roles: {
        validators: {
          notEmpty: {
            message: 'Please select Roles '
          }
        }
      },  --}}
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        // Use this for enabling/changing valid/invalid class
        eleValidClass: '',
        rowSelector: function (field, ele) {
          // field is the field name & ele is the field element
          return '.mb-3';
        }
      }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      // Submit the form when all fields are valid
      // defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
      autoFocus: new FormValidation.plugins.AutoFocus()
    }
  }).on('core.form.valid', function () {
    // adding or updating user when form successfully validate
    $.ajax({
      data: $('#addNewUserForm').serialize(),
      url: `${baseUrl}user/employee/store`,
      type: 'POST',
      success: function (status) {
        {{--  dt_user.draw();  --}}
        offCanvasForm.offcanvas('hide');

        // sweetalert
        Swal.fire({
          icon: 'success',
          title: `Successfully ${status}!`,
          text: `User ${status} Successfully.`,
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      },
      error: function (err) {
        offCanvasForm.offcanvas('hide');
        Swal.fire({
          title: 'Duplicate Entry!',
          text: 'Your email should be unique.',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });
  });


})();

</script>
@endsection

@section('content')
<div class="col-md-12 mb-4">
  <div class="card">
    <div class="card-body">
      <div class="divider">
        <div class="divider-text text-primary"> {{$project_details->project_name}} Project Documents</div>
        <input type="hidden" name="project_id" value={{$project_details->id}} />
      </div>
    </div>
  </div>
</div>

<div class="card">

  <div class="card-body">
    <form action="/upload" class="dropzone needsclick" id="dropzone-basic">
      <div class="row mb-2">
        <div class="col-md-6 mb-4">

          <label for="defaultFormControlInput" class="form-label">Name</label>
          <input type="text" class="form-control" id="defaultFormControlInput" placeholder="John Doe" aria-describedby="defaultFormControlHelp" />

      </div>
      <div class="col-md-6 mb-4">
        <label for="select2Multiple" class="form-label">Doc Type</label>
        <select id="select2Multiple" class="select2 form-select" multiple>

            <option value="AK">Workorder</option>
            <option value="HI">Agreement</option>

            <option value="CA">Certificate</option>
            <option value="NV">Report</option>
            <option value="OR">Proposal</option>
        </select>
      </div>

    </div>
      <div class="row">
        <div class="dz-message needsclick">
          Drop files here or click to upload
          <span class="note needsclick">(This is just a demo dropzone. Selected files are <strong>not</strong> actually uploaded.)</span>
        </div>
        <div class="fallback">
          <input name="file" type="file" />
        </div>
      </div>

    </form>
  </div>
</div>

<!-- Users List Table -->
<div class="card">
  <div class="card-header border-bottom">
    <h5 class="card-title mb-3">Documents</h5>
    <div id="jstree-basic">
      <ul>
        <li data-jstree='{"icon" : "ti ti-folder"}'>
          Qoutations
          <ul>
            <li data-jstree='{"icon" : "ti ti-folder"}'>
              app.css
            </li>
            <li data-jstree='{"icon" : "ti ti-folder"}'>
              style.css
            </li>
          </ul>
        </li>
        <li class="jstree-open" data-jstree='{"icon" : "ti ti-folder"}'>
          Proposals
          <ul data-jstree='{"icon" : "ti ti-folder"}'>
            <li data-jstree='{"icon" : "ti ti-folder"}'>
              bg.jpg
            </li>
            <li data-jstree='{"icon" : "ti ti-folder"}'>
              logo.png
            </li>
            <li data-jstree='{"icon" : "ti ti-folder"}'>
              avatar.png
            </li>
          </ul>
        </li>
        <li class="jstree-open" data-jstree='{"icon" : "ti ti-folder"}'>
          Workorder
          <ul>
            <li data-jstree='{"icon" : "ti ti-folder"}'>jquery.js</li>
            <li data-jstree='{"icon" : "ti ti-folder"}'>app.js</li>
          </ul>
        </li>
        <li class="jstree-open" data-jstree='{"icon" : "ti ti-folder"}'>
          Agrement
          <ul>
            <li data-jstree='{"icon" : "ti ti-folder"}'>jquery.js</li>
            <li data-jstree='{"icon" : "ti ti-folder"}'>app.js</li>
          </ul>
        </li>
        <li class="jstree-open" data-jstree='{"icon" : "ti ti-folder"}'>
          Completion Certificate
          <ul>
            <li data-jstree='{"icon" : "ti ti-folder"}'>jquery.js</li>
            <li data-jstree='{"icon" : "ti ti-folder"}'>app.js</li>
          </ul>
        </li>
        <li class="jstree-open" data-jstree='{"icon" : "ti ti-folder"}'>
        Reports
          <ul>
            <li data-jstree='{"icon" : "ti ti-folder"}'>jquery.js</li>
            <li data-jstree='{"icon" : "ti ti-folder"}'>app.js</li>
          </ul>
        </li>
        <li data-jstree='{"icon" : "ti ti-file-text"}'>
          index.html
        </li>
        <li data-jstree='{"icon" : "ti ti-file-text"}'>
          page-one.html
        </li>
        <li data-jstree='{"icon" : "ti ti-file-text"}'>
          page-two.html
        </li>
      </ul>
    </div>
  </div>
</div>
<div class="card" style="display: none;">
  <div class="card-header border-bottom">
    <h5 class="card-title mb-3">Search Filter</h5>
    <div class="d-flex justify-content-between align-items-center row pb-2 gap-3 gap-md-0">
      <div class="col-md-4 user_role"></div>
      <div class="col-md-4 user_plan"></div>
      <div class="col-md-4 user_status"></div>
    </div>
  </div>
  <div class="card-datatable table-responsive">


    <table class="datatables-users table border-top">
      <thead>
        <tr>
          <th></th>
          <th>User</th>
          <th>User Type</th>
          <th>Designation</th>
          <th>Gender</th>
          <th>Status</th>
          <th>Employee ID</th>
          <th>Actions</th>
        </tr>
      </thead>
    </table>
  </div>
  <!-- Offcanvas to add new user -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddUser" aria-labelledby="offcanvasAddUserLabel">
    <div class="offcanvas-header">
      <h5 id="offcanvasAddUserLabel" class="offcanvas-title">Add User</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 pt-0 h-100">
      <form class="add-new-user pt-0" id="addNewUserForm" onsubmit="return false">
        <div class="mb-3">
          <label class="form-label" for="name">Full Name</label>
          <input type="text" class="form-control" id="name" placeholder="John Doe" name="name" aria-label="John Doe" />
        </div>
        <div class="mb-3">
          <label class="form-label" for="email">Email</label>
          <input type="text" id="email" class="form-control" placeholder="john.doe@example.com" aria-label="john.doe@example.com" name="email" />
        </div>
        <div class="mb-3">
          <label class="form-label" for="mobile">Contact</label>
          <input type="text" id="mobile" class="form-control phone-mask" placeholder="609 988 44 11" aria-label="john.doe@example.com" name="mobile" />
        </div>
        <div class="mb-3">
          <label class="form-label" for="empId">Employee ID</label>
          <input type="text" id="empId" class="form-control " placeholder="XXX" aria-label="john.doe@example.com" name="empId" />
        </div>
        <div class="mb-3">
          <label class="form-label" for="designation">Designation</label>
          <select id="designation" name="designation" class=" form-select">
            <option disabled value="">Select</option>
            @foreach ($designations as $designation)
            <option value={{$designation->id}}> {{$designation->designation}}</option>
            @endforeach


          </select>
        </div>
        <div class="mb-3">
          <label class="form-label" for="usertype_role">User Role</label>
          <select id="usertype_role" name="usertype_role" class="form-select">
            @foreach ($user_types as $usertype)
            <option value={{$usertype->id}}> {{$usertype->user_type}}</option>
            @endforeach
          </select>
        </div>

        <button type="submit" class="btn btn-primary me-sm-3 me-1 data-submit">Submit</button>
        <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Cancel</button>
      </form>



    </div>
  </div>


  <div class="modal fade" id="fullscreenModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalFullTitle">Add New User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- Vertical Icons Wizard -->
          <div class="col-12 mb-4">
            <small class="text-light fw-semibold">Vertical Icons</small>
            <div class="bs-stepper vertical wizard-vertical-icons-example mt-2">
              <div class="bs-stepper-header">
                <div class="step" data-target="#personal-info-vertical">
                  <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">
                      <i class="ti ti-file-description"></i>
                    </span>
                    <span class="bs-stepper-label">
                      <span class="bs-stepper-title">Personal Info</span>
                      <span class="bs-stepper-subtitle">Add personal info</span>
                    </span>
                  </button>
                </div>
                <div class="line"></div>
                <div class="step" data-target="#contact-details-vertical">
                  <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">
                      <i class="ti ti-user"></i>
                    </span>
                    <span class="bs-stepper-label">
                      <span class="bs-stepper-title">Contact Details</span>
                      <span class="bs-stepper-subtitle">Add Contact Details</span>
                    </span>
                  </button>
                </div>
                <div class="line"></div>
                <div class="step" data-target="#account-details-vertical">
                  <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">
                      <i class="ti ti-user"></i>
                    </span>
                    <span class="bs-stepper-label">
                      <span class="bs-stepper-title">Employment Details</span>
                      <span class="bs-stepper-subtitle">Add Employment Details</span>
                    </span>
                  </button>
                </div>
                <div class="line"></div>
                <div class="step" data-target="#social-links-vertical">
                  <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle"><i class="ti ti-brand-instagram"></i>
                    </span>
                    <span class="bs-stepper-label">
                      <span class="bs-stepper-title">Account Details</span>
                      <span class="bs-stepper-subtitle">Add Account Details</span>
                    </span>
                  </button>
                </div>
                <div class="line"></div>
                <div class="step" data-target="#documents">
                  <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle"><i class="ti ti-brand-instagram"></i>
                    </span>
                    <span class="bs-stepper-label">
                      <span class="bs-stepper-title">Documents</span>
                      <span class="bs-stepper-subtitle">Add Documents</span>
                    </span>
                  </button>
                </div>
              </div>
              <div class="bs-stepper-content">
                <form onSubmit="return false">

                  <!-- Personal Info -->
                  <div id="personal-info-vertical" class="content">
                    <div class="content-header mb-3">
                      <h6 class="mb-0">Personal Info</h6>
                      <small>Enter Your Personal Info.</small>
                    </div>
                    <div class="row g-3">
                      <div class="col-sm-6">
                        <label class="form-label" for="country1">Prefix</label>
                        <select class="select2" id="prefix">
                          <option label=" "></option>
                          <option>UK</option>
                          <option>USA</option>
                          <option>Spain</option>
                          <option>France</option>
                          <option>Italy</option>
                          <option>Australia</option>
                        </select>
                      </div>
                      <div class="col-sm-6">
                        <label class="form-label" for="first-name1">First Name</label>
                        <input type="text" id="first-name1" class="form-control" placeholder="John" />
                      </div>
                      <div class="col-sm-6">
                        <label class="form-label" for="last-name1">Last Name</label>
                        <input type="text" id="last-name1" class="form-control" placeholder="Doe" />
                      </div>
                      <div class="col-sm-6">
                        <label class="form-label" for="country1">Gender</label>
                        <select class="select2" id="gender">
                          <option label=" "></option>
                          <option>UK</option>
                          <option>USA</option>
                          <option>Spain</option>
                          <option>France</option>
                          <option>Italy</option>
                          <option>Australia</option>
                        </select>
                      </div>
                      <div class="col-sm-6">
                        <label class="form-label" for="first-name1">DOB</label>
                        <input type="text" id="first-name1" class="form-control" placeholder="John" />
                      </div>

                      <div class="col-sm-6">
                        <label class="form-label" for="country1">Country</label>
                        <select class="select2" id="country1">
                          <option label=" "></option>
                          <option>UK</option>
                          <option>USA</option>
                          <option>Spain</option>
                          <option>France</option>
                          <option>Italy</option>
                          <option>Australia</option>
                        </select>
                      </div>
                      <div class="col-sm-6">
                        <label class="form-label" for="country1">State</label>
                        <select class="select2" id="state">
                          <option label=" "></option>
                          <option>UK</option>
                          <option>USA</option>
                          <option>Spain</option>
                          <option>France</option>
                          <option>Italy</option>
                          <option>Australia</option>
                        </select>
                      </div>
                      <div class="col-sm-6">
                        <label class="form-label" for="country1">District</label>
                        <select class="select2" id="District">
                          <option label=" "></option>
                          <option>UK</option>
                          <option>USA</option>
                          <option>Spain</option>
                          <option>France</option>
                          <option>Italy</option>
                          <option>Australia</option>
                        </select>
                      </div>
                      <div class="col-sm-6">
                        <label class="form-label" for="first-name1">Address</label>
                        <input type="text" id="first-name1" class="form-control" placeholder="John" />
                      </div>
                      <div class="col-sm-6">
                        <label class="form-label" for="first-name1">Pincode</label>
                        <input type="text" id="first-name1" class="form-control" placeholder="John" />
                      </div>
                      <div class="col-sm-6">
                        <label class="form-label" for="language1">Language</label>
                        <select class="selectpicker w-auto" id="language1" data-style="btn-default" data-icon-base="ti" data-tick-icon="ti-check text-white" multiple>
                          <option>English</option>
                          <option>French</option>
                          <option>Spanish</option>
                        </select>
                      </div>
                      <div class="col-12 d-flex justify-content-between">
                        {{--  <button class="btn btn-label-secondary btn-prev"> <i class="ti ti-arrow-left me-sm-1"></i>
                          <span class="align-middle d-sm-inline-block d-none">Previous</span>
                        </button>  --}}
                        <button class="btn btn-primary btn-next"> <span class="align-middle d-sm-inline-block d-none me-sm-1">Next</span> <i class="ti ti-arrow-right"></i></button>
                      </div>
                    </div>
                  </div>
                  <div id="contact-details-vertical" class="content">
                    <div class="content-header mb-3">
                      <h6 class="mb-0">Contact Details</h6>
                      <small>Enter Your Contact Details.</small>
                    </div>
                    <div class="row g-3">
                      <div class="col-sm-6">
                        <label class="form-label" for="username1">Primary Mobile</label>
                        <input type="text" id="username1" class="form-control" placeholder="john.doe" />
                      </div>
                      <div class="col-sm-6">
                        <label class="form-label" for="username1">Secondary Mobile</label>
                        <input type="text" id="username1" class="form-control" placeholder="john.doe" />
                      </div>
                      <div class="col-sm-6">
                        <label class="form-label" for="email1">Primary Email</label>
                        <input type="text" id="email1" class="form-control" placeholder="john.doe" aria-label="john.doe" />
                      </div>
                      <div class="col-sm-6">
                        <label class="form-label" for="email1">Secondary Email</label>
                        <input type="text" id="email1" class="form-control" placeholder="john.doe" aria-label="john.doe" />
                      </div>

                      <div class="col-12 d-flex justify-content-between">
                        <button class="btn btn-label-secondary btn-prev"> <i class="ti ti-arrow-left me-sm-1"></i>
                          <span class="align-middle d-sm-inline-block d-none">Previous</span>
                        </button>
                        <button class="btn btn-primary btn-next"> <span class="align-middle d-sm-inline-block d-none me-sm-1">Next</span> <i class="ti ti-arrow-right"></i></button>
                      </div>
                    </div>
                  </div>
                  <!-- Account Details -->
                  <div id="account-details-vertical" class="content">
                    <div class="content-header mb-3">
                      <h6 class="mb-0">Employment Details</h6>
                      <small>Enter Your Employment Details.</small>
                    </div>
                    <div class="row g-3">
                      <div class="col-sm-6">
                        <label class="form-label" for="username1">Employee ID</label>
                        <input type="text" id="username1" class="form-control" placeholder="john.doe" />
                      </div>
                      <div class="col-sm-6">
                        <label class="form-label" for="email1">Date of join</label>
                        <input type="text" id="email1" class="form-control" placeholder="john.doe" aria-label="john.doe" />
                      </div>
                      <div class="col-sm-6">
                        <label class="form-label" for="language1">Designation</label>
                        <select class="selectpicker w-auto" id="language1" data-style="btn-default" data-icon-base="ti" data-tick-icon="ti-check text-white" multiple>
                          <option>English</option>
                          <option>French</option>
                          <option>Spanish</option>
                        </select>
                      </div>
                      <div class="col-sm-6">
                        <label class="form-label" for="language1">Highest Educational Qualification</label>
                        <select class="selectpicker w-auto" id="language1" data-style="btn-default" data-icon-base="ti" data-tick-icon="ti-check text-white" multiple>
                          <option>English</option>
                          <option>French</option>
                          <option>Spanish</option>
                        </select>
                      </div>
                      <div class="col-sm-6">
                        <label class="form-label" for="language1">Leave Type</label>
                        <select class="selectpicker w-auto" id="language1" data-style="btn-default" data-icon-base="ti" data-tick-icon="ti-check text-white" multiple>
                          <option>English</option>
                          <option>French</option>
                          <option>Spanish</option>
                        </select>
                      </div>
                      <div class="col-sm-6">
                        <label class="form-label" for="email1">Exit Date</label>
                        <input type="text" id="email1" class="form-control" placeholder="john.doe" aria-label="john.doe" />
                      </div>
                      <div class="col-12 d-flex justify-content-between">
                        <button class="btn btn-label-secondary btn-prev"> <i class="ti ti-arrow-left me-sm-1"></i>
                          <span class="align-middle d-sm-inline-block d-none">Previous</span>
                        </button>
                        <button class="btn btn-primary btn-next"> <span class="align-middle d-sm-inline-block d-none me-sm-1">Next</span> <i class="ti ti-arrow-right"></i></button>
                      </div>
                    </div>
                  </div>
                  <!-- Social Links -->
                  <div id="social-links-vertical" class="content">
                    <div class="content-header mb-3">
                      <h6 class="mb-0">Bank Account Details</h6>
                      <small>Enter Your Bank Account Details.</small>
                    </div>
                    <div class="row g-3">
                      <div class="col-sm-6 form-password-toggle">
                        <label class="form-label" for="password">Account Number</label>
                        <div class="input-group input-group-merge">
                          <input type="password" id="password" class="form-control" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password2" />
                          <span class="input-group-text cursor-pointer" id="password2"><i class="ti ti-eye-off"></i></span>
                        </div>
                      </div>
                      <div class="col-sm-6 form-password-toggle">
                        <label class="form-label" for="confirm-password">Confirm Account Number</label>
                        <div class="input-group input-group-merge">
                          <input type="password" id="confirm-password" class="form-control" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="confirm-password2" />
                          <span class="input-group-text cursor-pointer" id="confirm-password2"><i class="ti ti-eye-off"></i></span>
                        </div>
                      </div>
                      <div class="col-sm-6">
                        <label class="form-label" for="facebook1">IFSC</label>
                        <input type="text" id="facebook1" class="form-control" placeholder="https://facebook.com/abc" />
                      </div>
                      <div class="col-sm-6">
                        <label class="form-label" for="google1">Bank Name</label>
                        <input type="text" id="google1" class="form-control" placeholder="https://plus.google.com/abc" />
                      </div>
                      <div class="col-sm-6">
                        <label class="form-label" for="linkedin1">Branch</label>
                        <input type="text" id="linkedin1" class="form-control" placeholder="https://linkedin.com/abc" />
                      </div>
                      <div class="col-12 d-flex justify-content-between">
                        <button class="btn btn-label-secondary btn-prev"> <i class="ti ti-arrow-left me-sm-1"></i>
                          <span class="align-middle d-sm-inline-block d-none">Previous</span>
                        </button>
                        <button class="btn btn-primary btn-next"> <span class="align-middle d-sm-inline-block d-none me-sm-1">Next</span> <i class="ti ti-arrow-right"></i></button>
                      </div>
                    </div>
                  </div>
                  <div id="documents" class="content">
                    <div class="content-header mb-3">
                      <h6 class="mb-0">Social Links</h6>
                      <small>Enter Your Social Links.</small>
                    </div>
                    <div class="row g-3">
                      <div class="col-sm-6">
                        <label class="form-label" for="twitter1">Document Name</label>
                        <input type="text" id="twitter1" class="form-control" placeholder="https://twitter.com/abc" />
                      </div>
                      <div class="col-sm-6">
                        <label class="form-label" for="facebook1">Document Number</label>
                        <input type="text" id="facebook1" class="form-control" placeholder="https://facebook.com/abc" />
                      </div>
                      <div class="card-body">
                        <form action="/upload" class="dropzone needsclick" id="dropzone-basic">
                          <div class="dz-message needsclick">
                            Drop files here or click to upload
                            <span class="note needsclick">(This is just a demo dropzone. Selected files are <strong>not</strong> actually uploaded.)</span>
                          </div>
                          <div class="fallback">
                            <input name="file" type="file" />
                          </div>
                        </form>
                      </div>
                      <div class="col-12 d-flex justify-content-between">
                        <button class="btn btn-label-secondary btn-prev"> <i class="ti ti-arrow-left me-sm-1"></i>
                          <span class="align-middle d-sm-inline-block d-none">Previous</span>
                        </button>
                        <button class="btn btn-success btn-submit">Submit</button>
                      </div>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
          <!-- /Vertical Icons Wizard -->

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
          {{--  <button type="button" class="btn btn-primary">Save changes</button>  --}}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
