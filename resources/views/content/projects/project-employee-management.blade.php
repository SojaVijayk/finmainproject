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
@endsection

@section('page-script')
<script src="{{asset('assets/js/form-wizard-icons.js')}}"></script>3
<script src="{{asset('assets/js/forms-file-upload.js')}}"></script>
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
      usersList = @if(isset($is_global) && $is_global) baseUrl + 'pms/employees/list' @else baseUrl + 'project/employees/detail/list' @endif,

    select2 = $('.select2'),
    userView = baseUrl + 'project/employee/view/account',
    userDetails = baseUrl + 'pms/employees/details',

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
        url: "{{ route('pms.employees.list') }}",
        data: function(d) {
          @if(isset($project_details))
            d.project_id = "{{ $project_details->id }}";
          @endif
        }
       },
      columns: [
        // columns according to JSON
        { data: '' },
        { data: 'name' },
        { data: 'email' },
        { data: 'age' },
        { data: 'mobile' },
        { data: 'dob' },
        { data: 'date_of_joining' },
        { data: 'address' },
        { data: 'status' },
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
              $email = full['email'],
              $image = ''; // Removed profile_pic support as column is gone
            
            // For Avatar badge
            var stateNum = Math.floor(Math.random() * 6);
            var states = ['success', 'danger', 'warning', 'info', 'primary', 'secondary'];
            var $state = states[stateNum],
              $initials = $name.match(/\b\w/g) || [];
            $initials = (($initials.shift() || '') + ($initials.pop() || '')).toUpperCase();
            var $output = '<span class="avatar-initial rounded-circle bg-label-' + $state + '">' + $initials + '</span>';

            // Creates full output for row
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
              '</span>' +
              '<small class="text-muted">' +
              $email +
              '</small>' +
              '</div>' +
              '</div>';
            return $row_output;
          }
        },
        {
          // Status
          targets: 8,
          responsivePriority: 1,
          render: function (data, type, full, meta) {
            var $status = full['status'];
            if (!statusObj[$status]) return $status || 'N/A';
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
          // Actions
          // Actions
          targets: -1,
          title: 'Actions',
          searchable: false,
          orderable: false,
          responsivePriority: 1,
          render: function (data, type, full, meta) {
            var detailUrl = "{{ route('pms.employees.details', ':id') }}";
            detailUrl = detailUrl.replace(':id', full['id']);
            
            return (
              '<div class="d-flex align-items-center">' +
              '<a href="' + detailUrl + '" class="btn btn-icon btn-label-success me-2" title="View Details"><i class="ti ti-eye"></i></a>' +
              '<a href="javascript:;" class="btn btn-icon btn-label-info edit-record me-2" data-id="' + full['id'] + '" title="Edit"><i class="ti ti-edit"></i></a>' +
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
        // Adding status filter once table initialized
        this.api()
          .columns(4)
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
document.addEventListener('DOMContentLoaded', function () {
// Validation & Phone mask

  const addGlobalEmployeeForm = document.getElementById('addGlobalEmployeeForm');

  // Add Global Employee Form Validation
  const fv = FormValidation.formValidation(addGlobalEmployeeForm, {
    fields: {
      name: {
        validators: {
          notEmpty: {
            message: 'Please enter fullname'
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
          regexp: {
            regexp: /^[0-9]+$/,
            message: 'The value is not a valid number'
          }
        }
      },
      age: {
        validators: {
          notEmpty: {
            message: 'Please enter age'
          }
        }
      },
      dob: {
        validators: {
          notEmpty: {
            message: 'Please select Date of Birth'
          }
        }
      },
      joining_date: {
        validators: {
          notEmpty: {
            message: 'Please select Date of Joining'
          }
        }
      },
      address: {
        validators: {
          notEmpty: {
            message: 'Please enter address'
          }
        }
      }
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        // Use this for enabling/changing valid/invalid class
        eleValidClass: '',
        rowSelector: function (field, ele) {
          // field is the field name & ele is the field element
          return '.col-sm-6, .col-sm-12';
        }
      }),
      autoFocus: new FormValidation.plugins.AutoFocus(),
      submitButton: new FormValidation.plugins.SubmitButton()
    }
  });

  // Revalidate Select2 on change
  $('.select2').on('change', function() {
      fv.revalidateField($(this).attr('name'));
  });

  // Manual Submit Handler
  const submitBtn = document.getElementById('btn-submit-employee');
  if(submitBtn){
    submitBtn.addEventListener('click', function (e) {
      e.preventDefault();
      fv.validate().then(function(status) {
        if (status === 'Valid') {
          var formData = new FormData(addGlobalEmployeeForm);
          // Debug URL
          var actionUrl = $('#addGlobalEmployeeForm').attr('action');
          console.log('Submitting to:', actionUrl);

          $.ajax({
            data: formData,
            url: actionUrl,
            type: 'POST',
            contentType: false,
            processData: false,
            success: function (response) {
              $('#fullscreenModal').modal('hide');
              Swal.fire({
                title: 'Success!',
                text: response.message ? response.message : 'Employee details submitted successfully',
                icon: 'success',
                customClass: {
                  confirmButton: 'btn btn-primary'
                },
                buttonsStyling: false
              }).then(function (result) {
                   window.location.reload();
              });
            },
            error: function (err) {
              $('#fullscreenModal').modal('hide'); // Optional: keep open to show errors?
              Swal.fire({
                title: 'Error!',
                text: err.responseJSON && err.responseJSON.message ? err.responseJSON.message : 'Something went wrong.',
                icon: 'error',
                customClass: {
                  confirmButton: 'btn btn-primary'
                },
                buttonsStyling: false
              });
            }
          });
        }
      });
    });
  }

  // Edit Record
  $(document).on('click', '.edit-record', function () {
    var id = $(this).data('id');
    var editUrl = "{{ route('pms.employees.edit', ':id') }}";
    editUrl = editUrl.replace(':id', id);
    var updateUrl = "{{ route('pms.employees.update-master', ':id') }}";
    updateUrl = updateUrl.replace(':id', id);

    // Fetch data
    $.get(editUrl, function (data) {
      if (data) {
        // Ensure data is an object (in case of string response)
        if (typeof data === 'string') {
             try { data = JSON.parse(data); } catch (e) { console.error("Parsed error", e); return; }
        }

        $('#wizard_name').val(data.name);
        $('#wizard_email').val(data.email);
        $('#wizard_mobile').val(data.mobile);
        $('#wizard_pan_number').val(data.pan_number);
        $('#wizard_age').val(data.age);
        $('#wizard_dob').val(data.dob);
        $('#wizard_joining_date').val(data.date_of_joining);
        $('#wizard_address').val(data.address);
        
        // Populate Bank Details
        $('#wizard_bank_name').val(data.bank_name);
        $('#wizard_account_no').val(data.account_no);
        $('#wizard_account_name').val(data.account_name);
        $('#wizard_branch').val(data.branch);
        $('#wizard_ifsc_code').val(data.ifsc_code);


        if (data.documents) {
            var docUrl = "{{ asset('uploads/employee_documents') }}/" + data.documents;
             $('#existing_document_link').html('<a href="' + docUrl + '" target="_blank" class="btn btn-sm btn-label-primary"><i class="ti ti-file-description me-1"></i> View Document</a>');
        } else {
             $('#existing_document_link').html('');
        }

        // Populate service details if available

        // Update form action
        $('#addGlobalEmployeeForm').attr('action', updateUrl);
        $('#modalFullTitle').text('Edit User');
        $('#btn-submit-employee').text('Update');

        // Show modal
        var myModal = new bootstrap.Modal(document.getElementById('fullscreenModal'));
        myModal.show();
      }
    });
  });

  // Reset modal on close
  $('#fullscreenModal').on('hidden.bs.modal', function () {
      $('#addGlobalEmployeeForm')[0].reset();
      $('#existing_document_link').html('');
      $('#addGlobalEmployeeForm').attr('action', "{{ route('pms.employees.store') }}");
      $('#modalFullTitle').text('Add New User');
      $('#btn-submit-employee').text('Submit');
  });

  // Pay Type Label Sync
  function updateWizardPayLabel(payType) {
    var label = payType ? payType : 'Consolidated Pay';
    $('#wizard_pay_label').text(label);
    $('#wizard_consolidated_pay').attr('placeholder', label);
  }

  $(document).on('change', '#wizard_pay_type', function() {
    updateWizardPayLabel($(this).val());
  });

  $('#fullscreenModal').on('shown.bs.modal', function () {
    updateWizardPayLabel($('#wizard_pay_type').val());
  });

  // Auto-open modal if ?add=1 is present
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has('add')) {
    var myModal = new bootstrap.Modal(document.getElementById('fullscreenModal'));
    myModal.show();
  }

});

</script>
@endsection

@section('content')
<div class="col-md-12 mb-4">
  <div class="card">
    <div class="card-body">
      <div class="divider">
        <div class="divider-text text-primary">
          @if(isset($project_details))
            Employee Details of {{$project_details->name}} Project
            <input type="hidden" name="project_id" value={{$project_details->id}} />
          @else
            Employee Management
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header border-bottom d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">Employee Management</h5>
    <div class="d-flex gap-2">
      <a href="{{ route('pms.pay-item-master.index', $project_details->id ?? '') }}" class="btn btn-label-secondary" title="Pay Item Master">
        <i class="ti ti-settings me-1 ti-xs"></i> Pay Item Master
      </a>
      <a href="{{ route('pms.deduction-master.index', $project_details->id ?? '') }}" class="btn btn-label-secondary" title="Deduction Master">
        <i class="ti ti-receipt-2 me-1 ti-xs"></i> Deduction Master
      </a>
      <a href="{{ route('pms.salary-management.index', $project_details->id ?? '') }}" class="btn btn-label-primary">
        <i class="ti ti-currency-dollar me-1 ti-xs"></i> Salary Management
      </a>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#fullscreenModal">
        <i class="ti ti-plus me-0 me-sm-1 ti-xs"></i> Add Employee
      </button>
    </div>
  </div>
  <div class="card-datatable table-responsive">


    <table class="datatables-users table border-top">
      <thead>
        <tr>
          <th></th>
          <th>Name</th>
          <th>Email</th>
          <th>Age</th>
          <th>Mobile</th>
          <th>DOB</th>
          <th>DOJ</th>
          <th>Address</th>
          <th>Status</th>
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
          <label class="form-label" for="pan_number">PAN Number</label>
          <input type="text" id="pan_number" class="form-control" placeholder="ABCDE1234F" name="pan_number" style="text-transform: uppercase;" />
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
              <form id="addGlobalEmployeeForm" method="POST" action="{{ route('pms.employees.store') }}" onSubmit="return false" novalidate>
                @csrf
                @if(isset($project_details))
                  <input type="hidden" name="project_id" value="{{ $project_details->id }}">
                @endif
                <div class="row g-3">
                  <div class="col-sm-6">
                    <label class="form-label" for="name">Full Name</label>
                    <input type="text" id="wizard_name" name="name" class="form-control" placeholder="John" required />
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="wizard_email" name="email" class="form-control" placeholder="john@example.com" required />
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label" for="age">Age</label>
                    <input type="number" id="wizard_age" name="age" class="form-control" placeholder="25" required />
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label" for="mobile">Mobile</label>
                    <input type="text" id="wizard_mobile" name="mobile" class="form-control phone-mask" placeholder="1234567890" required />
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label" for="wizard_pan_number">PAN Number</label>
                    <input type="text" id="wizard_pan_number" name="pan_number" class="form-control" placeholder="ABCDE1234F" style="text-transform: uppercase;" />
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label" for="dob">Date of Birth</label>
                    <input type="date" id="wizard_dob" name="dob" class="form-control" required />
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label" for="joining_date">Date of Joining</label>
                    <input type="date" id="wizard_joining_date" name="joining_date" class="form-control" required />
                  </div>
                  <div class="col-sm-12">
                    <label class="form-label" for="wizard_address">Address</label>
                    <textarea id="wizard_address" name="address" class="form-control" rows="2" placeholder="123 Main St..." required></textarea>
                  </div>
                   <div class="col-sm-12">
                    <label class="form-label" for="wizard_documents">Documents (Optional)</label>
                    <input type="file" id="wizard_documents" name="documents" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" />
                    <div id="existing_document_link" class="mt-2"></div>
                  </div>

                  <!-- Bank Account Details -->
                  <div class="col-12 mt-4">
                    <h6 class="border-bottom pb-2">Bank Account Details</h6>
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label" for="wizard_bank_name">Bank Name</label>
                    <input type="text" id="wizard_bank_name" name="bank_name" class="form-control" placeholder="e.g. State Bank of India" />
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label" for="wizard_account_no">Account Number</label>
                    <input type="text" id="wizard_account_no" name="account_no" class="form-control" placeholder="1234567890" />
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label" for="wizard_account_name">Account Holder Name</label>
                    <input type="text" id="wizard_account_name" name="account_name" class="form-control" placeholder="John Doe" />
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label" for="wizard_branch">Branch</label>
                    <input type="text" id="wizard_branch" name="branch" class="form-control" placeholder="e.g. Main Branch" />
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label" for="wizard_ifsc_code">IFSC Code</label>
                    <input type="text" id="wizard_ifsc_code" name="ifsc_code" class="form-control" placeholder="SBIN0001234" />
                  </div>
                  <div class="col-12 text-center mt-4">
                    <button type="submit" id="btn-submit-employee" class="btn btn-primary me-sm-3 me-1 btn-submit">Submit</button>
                    <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                  </div>
                </div>
            </form>
          </div>

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
