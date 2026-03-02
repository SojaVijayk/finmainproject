@extends('layouts/layoutMaster')

@section('title', 'User List - Pages')
@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/animate-css/animate.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bs-stepper/bs-stepper.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-select/bootstrap-select.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/dropzone/dropzone.css') }}" />

@endsection

@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/cleavejs/cleave.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/cleavejs/cleave-phone.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/bs-stepper/bs-stepper.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/bootstrap-select/bootstrap-select.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/dropzone/dropzone.js') }}"></script>

@endsection

@section('page-script')
    <script src="{{ asset('assets/js/form-wizard-icons.js') }}"></script>
    <script src="{{ asset('assets/js/forms-file-upload.js') }}"></script>
    <script>
        /**
         * Page User List
         */

        'use strict';

        // Datatable (jquery)
        $(function() {
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
            var dt_user_table = $('.datatables-users'),
                userView, select2, statusObj, offCanvasForm = $('#offcanvasAddUser'),
                dt_user,
                usersList = baseUrl + 'user/employee/list';

            select2 = $('.select2'),
                {{--  userView = baseUrl + 'user/employee/view/account',  --}}
                 userView = '#',

                statusObj = {
                    1: {
                        title: 'Active',
                        class: 'bg-label-success'
                    },
                    0: {
                        title: 'Inactive',
                        class: 'bg-label-secondary'
                    }
                };

            $(".datepicker").datepicker({
                autoclose: true,
                format: "yyyy/mm/dd"
            });



            // ajax setup
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });



            {{--  if (select2.length) {
    var $this = select2;
    $this.wrap('<div class="position-relative"></div>').select2({
      placeholder: 'Select Privilege',
      dropdownParent: $this.parent()
    });
  }  --}}

            // Users datatable
            if (dt_user_table.length) {
                dt_user = dt_user_table.DataTable({

                    ajax: {
                        url: usersList
                    },
                    columns: [
                        // columns according to JSON
                        {
                            data: ''
                        },
                        {
                            data: 'name'
                        },
                        {{--  {
                            data: 'usertype_role'
                        },  --}}

                        {
                          data:'employment_type'},

                        {
                            data: 'designation'
                        },
                        {
                            data: 'roles'
                        },
                        {
                            data: 'status'
                        },
                        {
                            data: 'empId'
                        },
                        {
                            data: 'action'
                        }
                    ],
                    columnDefs: [{
                            // For Responsive
                            className: 'control',
                            searchable: false,
                            orderable: false,
                            responsivePriority: 2,
                            targets: 0,
                            render: function(data, type, full, meta) {
                                return '';
                            }
                        },
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
                                    '<a href="' +
                                    userView + '/' + full['id'] +
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
                        {{--  {
                            // User Role
                            targets: 2,
                            render: function(data, type, full, meta) {
                                var $role = full['usertype_role'];
                                var roleBadgeObj = {
                                    SuperAdmin: '<span class="badge badge-center rounded-pill bg-label-warning w-px-30 h-px-30 me-2"><i class="ti ti-user ti-sm"></i></span>',
                                    Employee: '<span class="badge badge-center rounded-pill bg-label-success w-px-30 h-px-30 me-2"><i class="ti ti-circle-check ti-sm"></i></span>',
                                    Maintainer: '<span class="badge badge-center rounded-pill bg-label-primary w-px-30 h-px-30 me-2"><i class="ti ti-chart-pie-2 ti-sm"></i></span>',
                                    Editor: '<span class="badge badge-center rounded-pill bg-label-info w-px-30 h-px-30 me-2"><i class="ti ti-edit ti-sm"></i></span>',
                                    Admin: '<span class="badge badge-center rounded-pill bg-label-secondary w-px-30 h-px-30 me-2"><i class="ti ti-device-laptop ti-sm"></i></span>'
                                };
                                return "<span class='text-truncate d-flex align-items-center'>" +
                                    roleBadgeObj[$role] + $role + '</span>';
                            }
                        },  --}}
                        {
                          // Designation
                          targets: 2,
                          render: function(data, type, full, meta) {
                              var $emp_type = full['employment_type_name'];
                              var $expire =  full['contract_end_date'];
                              var $badge;
                              var $contract;
                               var $today = new Date().toISOString().slice(0,10);



                              if(full['employment_type'] > 1){

                                if(full['contract_end_date'] !=null){
                                  // Compare dates
                                  if ($expire < $today) {
                                    console.log($expire + " is greater than today's date.");
                                    $badge = '<span class="badge  bg-label-danger">Expired</span>';
                                    $contract = '<span class="badge  bg-label-warning">Expired on '+$expire+'</span>';
                                } else {
                                    console.log($expire + " is not greater than today's date.");
                                    $badge = '<span class="badge  bg-label-success">Active</span>';
                                    $contract = '<span class="badge  bg-label-warning">Expire on '+$expire+'</span>';
                                }

                              return '<span class="fw-semibold">' + $emp_type + '</span><br>'+$badge+'<br>'+$contract;

                                }else{
                                  $badge = '<span class="badge  bg-label-secondary">Contract period not updated</span>';
                                  return '<span class="fw-semibold">' + $emp_type + '</span><br>'+$badge;

                                }

                              }else{


                                return '<span class="fw-semibold">' + $emp_type + '</span>';
                              }




                          }
                      },
                        {
                            // Designation
                            targets: 3,
                            render: function(data, type, full, meta) {
                                var $plan = full['designation'];

                                return '<span class="fw-semibold">' + $plan + '</span>';
                            }
                        },
                        {
                            // Roles
                            targets: 4,
                            render: function(data, type, full, meta) {
                                var $assignedTo = full['roles'],
                                    $output = '';

                                for (var i = 0; i < $assignedTo.length; i++) {
                                    var val = $assignedTo[i];

                                    $output += '<a><span class="badge bg-label-primary m-1">' +
                                        $assignedTo[i]['name'] + '</span></a>';
                                }
                                return '<span class="">' + $output + '</span>';
                            }
                        },
                        {
                            // User Status
                            targets: 5,
                            render: function(data, type, full, meta) {
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
                            render: function(data, type, full, meta) {
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
                            render: function(data, type, full, meta) {
                                return (
                                    '<div class="d-flex align-items-center">' +
                                    '<a href="javascript:;" data-id="' + full['id'] +
                                    '" class="text-body edit-record"  data-bs-toggle="offcanvas"  data-bs-target= "#offcanvasAddUser" ><i class="ti ti-edit ti-sm me-2"></i></a>' +
                                    '<a href="javascript:;" class="text-body delete-record"><i class="ti ti-trash ti-sm mx-2"></i></a>' +
                                    '<a href="javascript:;" class="text-body dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical ti-sm mx-1"></i></a>' +
                                    '<div class="dropdown-menu dropdown-menu-end m-0">' +
                                    '<a href="' +
                                    userView +
                                    '" class="dropdown-item">View</a>' +
                                    '<a  data-id="' + full['employee_id'] +
                                    '" class="dropdown-item edit-user" id="edit-user">More Data</a>' +
                                    '</div>' +
                                    '</div>'
                                );
                            }
                        }
                    ],
                    {{--  order: [
                        [1, 'desc']
                    ],  --}}
                    dom: '<"row me-2"' +
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
                    buttons: [{
                            extend: 'collection',
                            className: 'btn btn-label-secondary dropdown-toggle mx-3',
                            text: '<i class="ti ti-screen-share me-1 ti-xs"></i>Export',
                            buttons: [{
                                    extend: 'print',
                                    text: '<i class="ti ti-printer me-2" ></i>Print',
                                    className: 'dropdown-item',
                                    exportOptions: {
                                        columns: [1, 2, 3, 4, 5],
                                        // prevent avatar to be print
                                        format: {
                                            body: function(inner, coldex, rowdex) {
                                                if (inner.length <= 0) return inner;
                                                var el = $.parseHTML(inner);
                                                var result = '';
                                                $.each(el, function(index, item) {
                                                    if (item.classList !== undefined &&
                                                        item.classList.contains(
                                                            'user-name')) {
                                                        result = result + item.lastChild
                                                            .firstChild.textContent;
                                                    } else if (item.innerText ===
                                                        undefined) {
                                                        result = result + item
                                                            .textContent;
                                                    } else result = result + item
                                                        .innerText;
                                                });
                                                return result;
                                            }
                                        }
                                    },
                                    customize: function(win) {
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
                                            body: function(inner, coldex, rowdex) {
                                                if (inner.length <= 0) return inner;
                                                var el = $.parseHTML(inner);
                                                var result = '';
                                                $.each(el, function(index, item) {
                                                    if (item.classList !== undefined &&
                                                        item.classList.contains(
                                                            'user-name')) {
                                                        result = result + item.lastChild
                                                            .firstChild.textContent;
                                                    } else if (item.innerText ===
                                                        undefined) {
                                                        result = result + item
                                                            .textContent;
                                                    } else result = result + item
                                                        .innerText;
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
                                            body: function(inner, coldex, rowdex) {
                                                if (inner.length <= 0) return inner;
                                                var el = $.parseHTML(inner);
                                                var result = '';
                                                $.each(el, function(index, item) {
                                                    if (item.classList !== undefined &&
                                                        item.classList.contains(
                                                            'user-name')) {
                                                        result = result + item.lastChild
                                                            .firstChild.textContent;
                                                    } else if (item.innerText ===
                                                        undefined) {
                                                        result = result + item
                                                            .textContent;
                                                    } else result = result + item
                                                        .innerText;
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
                                            body: function(inner, coldex, rowdex) {
                                                if (inner.length <= 0) return inner;
                                                var el = $.parseHTML(inner);
                                                var result = '';
                                                $.each(el, function(index, item) {
                                                    if (item.classList !== undefined &&
                                                        item.classList.contains(
                                                            'user-name')) {
                                                        result = result + item.lastChild
                                                            .firstChild.textContent;
                                                    } else if (item.innerText ===
                                                        undefined) {
                                                        result = result + item
                                                            .textContent;
                                                    } else result = result + item
                                                        .innerText;
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
                                            body: function(inner, coldex, rowdex) {
                                                if (inner.length <= 0) return inner;
                                                var el = $.parseHTML(inner);
                                                var result = '';
                                                $.each(el, function(index, item) {
                                                    if (item.classList !== undefined &&
                                                        item.classList.contains(
                                                            'user-name')) {
                                                        result = result + item.lastChild
                                                            .firstChild.textContent;
                                                    } else if (item.innerText ===
                                                        undefined) {
                                                        result = result + item
                                                            .textContent;
                                                    } else result = result + item
                                                        .innerText;
                                                });
                                                return result;
                                            }
                                        }
                                    }
                                }
                            ]
                        },
                        {
                            text: '<i class="ti ti-plus me-0 me-sm-1 ti-xs"></i><span class="d-none d-sm-inline-block">Add New User</span>',
                            className: 'add-new btn btn-primary',
                            attr: {
                                'data-bs-toggle': 'offcanvas',
                                'data-bs-target': '#offcanvasAddUser'
                            }
                        }
                    ],
                    // For responsive popup
                    responsive: {
                        details: {
                            display: $.fn.dataTable.Responsive.display.modal({
                                header: function(row) {
                                    var data = row.data();
                                    return 'Details of ' + data['name'];
                                }
                            }),
                            type: 'column',
                            renderer: function(api, rowIdx, columns) {
                                var data = $.map(columns, function(col, i) {
                                    return col.title !==
                                        '' // ? Do not show row in modal popup if title is blank (for check box)
                                        ?
                                        '<tr data-dt-row="' +
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
                                        '</tr>' :
                                        '';
                                }).join('');

                                return data ? $('<table class="table"/><tbody />').append(data) : false;
                            }
                        }
                    },
                    initComplete: function() {
                        // Adding role filter once table initialized
                        this.api()
                            .columns(2)
                            .every(function() {
                                var column = this;
                                var select = $(
                                        '<select id="UserRole" class="form-select text-capitalize"><option value=""> Select Role </option></select>'
                                    )
                                    .appendTo('.user_role')
                                    .on('change', function() {
                                        var val = $.fn.dataTable.util.escapeRegex($(this)
                                    .val());
                                        column.search(val ? '^' + val + '$' : '', true, false)
                                            .draw();
                                    });

                                column
                                    .data()
                                    .unique()
                                    .sort()
                                    .each(function(d, j) {
                                        select.append('<option value="' + d + '">' + d +
                                            '</option>');
                                    });
                            });
                        // Adding plan filter once table initialized
                        this.api()
                            .columns(3)
                            .every(function() {
                                var column = this;
                                var select = $(
                                        '<select id="UserPlan" class="form-select text-capitalize"><option value=""> Select Designation </option></select>'
                                    )
                                    .appendTo('.user_plan')
                                    .on('change', function() {
                                        var val = $.fn.dataTable.util.escapeRegex($(this)
                                    .val());
                                        column.search(val ? '^' + val + '$' : '', true, false)
                                            .draw();
                                    });

                                column
                                    .data()
                                    .unique()
                                    .sort()
                                    .each(function(d, j) {
                                        select.append('<option value="' + d + '">' + d +
                                            '</option>');
                                    });
                            });
                        // Adding status filter once table initialized
                        this.api()
                            .columns(5)
                            .every(function() {
                                var column = this;
                                var select = $(
                                        '<select id="FilterTransaction" class="form-select text-capitalize"><option value=""> Select Status </option></select>'
                                    )
                                    .appendTo('.user_status')
                                    .on('change', function() {
                                        var val = $.fn.dataTable.util.escapeRegex($(this)
                                    .val());
                                        column.search(val ? '^' + val + '$' : '', true, false)
                                            .draw();
                                    });

                                column
                                    .data()
                                    .unique()
                                    .sort()
                                    .each(function(d, j) {
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
            $('.datatables-users tbody').on('click', '.delete-record', function() {
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
        (function() {
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
                        rowSelector: function(field, ele) {
                            // field is the field name & ele is the field element
                            return '.mb-3';
                        }
                    }),
                    submitButton: new FormValidation.plugins.SubmitButton(),
                    // Submit the form when all fields are valid
                    // defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
                    autoFocus: new FormValidation.plugins.AutoFocus()
                }
            }).on('core.form.valid', function() {
                var name = $('#name').val();
                var email = $('#email').val();
                var mobile = $('#mobile').val();
                var empId = $('#empId').val();
                var employment_type = $('#employment_type').val();
                var doj = $('#doj').val();
                var designation = $('#designation').val();
                var usertype_role = $('#usertype_role').val();
                var reporting_officer = $('#reporting_officer').val();
                var roles = $('#roles').val();
               var data_type =$(".data-submit").data('type');
               var data_id =$(".data-submit").data('id');
               var contract_start_date = $('#contract_start_date').val();
               var contract_end_date = $('#contract_end_date').val();
            if(data_type == 'new'){
              $.ajax({

                data: {
                    name: name,
                    email: email,
                    mobile: mobile,
                    empId: empId,
                    doj: doj,
                    employment_type: employment_type,
                    designation: designation,
                    usertype_role: usertype_role,
                    reporting_officer: reporting_officer,
                    roles: roles,
                    contract_start_date:contract_start_date,
                    contract_end_date:contract_end_date

                },
                url: `${baseUrl}user/employee/store`,
                type: 'POST',
                success: function(status) {


                    // sweetalert
                    Swal.fire({
                        icon: 'success',
                        title: `Successfully ${status}!`,
                        text: `User ${status} Successfully.`,
                        customClass: {
                            confirmButton: 'btn btn-success'
                        }
                    }).then((result) => {
                        window.location.reload();
                    });
                },
                error: function(err) {
                    {{--  offCanvasForm.offcanvas('hide');  --}}
                    Swal.fire({
                        title: 'Duplicate Entry!',
                        text: 'Your email should be unique.',
                        icon: 'error',
                        customClass: {
                            confirmButton: 'btn btn-success'
                        }
                    }).then((result) => {
                        window.location.reload();
                    });
                }
            });
            }
            else if(data_type == 'edit'){
              $.ajax({

                data: {
                    name: name,
                    email: email,
                    mobile: mobile,
                    empId: empId,
                    doj: doj,
                    employment_type: employment_type,
                    designation: designation,
                    usertype_role: usertype_role,
                    reporting_officer: reporting_officer,
                    roles: roles,
                    contract_start_date:contract_start_date,
                    contract_end_date:contract_end_date

                },
                url: `${baseUrl}user/employee/update/`+data_id,
                type: 'POST',
                success: function(status) {


                    // sweetalert
                    Swal.fire({
                        icon: 'success',
                        title: `Successfully ${status}!`,
                        text: `User ${status} Successfully.`,
                        customClass: {
                            confirmButton: 'btn btn-success'
                        }
                    }).then((result) => {
                        window.location.reload();
                    });
                },
                error: function(err) {
                    {{--  offCanvasForm.offcanvas('hide');  --}}
                    Swal.fire({
                        title: 'Duplicate Entry!',
                        text: 'Your email should be unique.',
                        icon: 'error',
                        customClass: {
                            confirmButton: 'btn btn-success'
                        }
                    }).then((result) => {
                        window.location.reload();
                    });
                }
            });
            }
                // adding or updating user when form successfully validate

            });


            $('#employment_type').change(function(){
            var employment_type = $(this).val();
              $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            })

            if($(this).val() != 1){
              $('.contract').show();

            }
            else{
              $('.contract').hide();
            }
            $.ajax({
              type: "GET",

              url: '/helpers/emplymenttypeDesignation/' + employment_type,
              success: function(response) {
                  {{--  console.log(response);  --}}
                  $('#designation').empty();
                  var select = $('#designation');
                  $.each(response.data, function(index,value) {
                    {{--  console.log(value.designation);  --}}
                    $('<option>').val(value.id).text(value.designation).appendTo(select);

                 });
                  $('#designation').trigger('change');


              },
              error: function(data) {

              }
          });

           });

            $(document).on('click', '.edit-record', function() {
                var user_id = $(this).data('id'),
                    dtrModal = $('.dtr-bs-modal.show');

                // hide responsive modal in small screen
                if (dtrModal.length) {
                    dtrModal.modal('hide');
                }

                // changing the title of offcanvas
                $('#offcanvasAddUserLabel').html('Edit User');



                $(".data-submit").attr('data-type', 'edit');
                $(".data-submit").attr('data-id', user_id);


                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                    }
                })
                $.ajax({
                    type: "GET",

                    url: '/user/employee/edit/' + user_id,
                    success: function(data) {
                        console.log(data);
                        $('#user_id').val(data.id);
                        $('#name').val(data.name);
                        $('#email').val(data.email);
                        $('#mobile').val(data.mobile);
                        $('#empId').val(data.empId);
                        $('#employment_type').val(data.employment_type);
                        $('#designation').val(data.desig_id);
                        $('#doj').val(data.doj);
                        $('#usertype_role').val(data.user_role);
                        $('#reporting_officer').val(data.reporting_officer);
                        $('#contract_start_date').val(data.contract_start_date);
                        $('#contract_end_date').val(data.contract_end_date);

                        let roles = data.roles.map(a => a.id);
                        console.log(roles);
                        $('#roles').val(roles);

                        $('.select2').trigger('change');


                    },
                    error: function(data) {

                    }
                });

            });


            $(document).on('click', '.edit-user', function(e) {

                var user_id = $(this).data('id');
                e.preventDefault();
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                    }
                })
                $.ajax({
                    type: "GET",

                    url: '/user/employee/editInfo/' + user_id,
                    success: function(data) {
                        console.log(data);
                        $("#updateInfo").attr('data-id', user_id);
                        $('#prefix').val(data.prefix);
                        {{--  $('#prefix').trigger('change');  --}}
                        $('#gender').val(data.gender);
                        $('#pincode').val(data.pincode);
                        $('#pan').val(data.pan);
                        $('#address').text(data.address);
                        $('#email_sec').val(data.email);
                        $('#dob').val(data.dob);
                        $('#country').val(data.country);
                        $('#state').val(data.state);
                        $('#district').val(data.district);
                        $('#mobile_sec').val(data.mobile_sec);
                        $('#email_sec').val(data.email_sec);
                        $('#twitter').val(data.twitter);
                        $('#facebook').val(data.facebook);
                        $('#linkedin').val(data.linkedin);
                        $('#instagram').val(data.instagram);
                        $('#whatsapp').val(data.whatsapp);
                        $('#contract_start_date').val( data.contract_start_date),
                        $('#contract_end_date').val(data.contract_end_date),
                        $('#contract_duration').val(data.contract_duration),

                        $('#account_number').val(data.account_number);
                        $('#account_holder_name').val(data.account_holder_name);
                        $('#ifsc').val(data.ifsc);
                        $('#bank_name').val(data.bank_name);
                        $('#bank_address').text(data.bank_address);
                        $('#branch').val(data.branch);
                        if (data.languages != null) {
                            $('#languages').val(data.languages.split(','));
                        }
                        $('.select2').trigger('change');
                        $('#editUser').modal('show');

                    },
                    error: function(data) {

                    }
                });

            });

            $('#ifsc').keyup(function() {
                this.value = this.value.toLocaleUpperCase();
            });

            $("#ifsc").blur(function() {

                 delete $.ajaxSettings.headers["X-CSRF-TOKEN"];
                $.ajax({
                    url: "https://ifsc.razorpay.com/" + $("#ifsc").val(),
                    type: "GET",
                    success: function(data) {
                        $('#bank_name').val(data.BANK);
                        $('#branch').val(data.BRANCH);
                        $('#bank_address').html(data.ADDRESS + ' District -' + data.DISTRICT +
                            ' City -' + data.CITY + ' State -' + data.STATE);
                    }
                })
            })


            $(document).on('click', '#updateInfo', function(e) {

                var user_id = $(this).data('id');
                e.preventDefault();
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                    }
                })
                $.ajax({
                    url: `${baseUrl}user/employee/editInfo/${user_id}`,
                    type: 'POST',
                    data: {
                        prefix: $('#prefix').val(),
                        gender: $('#gender').val(),
                        pincode: $('#pincode').val(),
                        address: $('#address').val(),
                        pan: $('#pan').val(),
                        dob: $('#dob').val(),
                        country: $('#country').val(),
                        district: $('#district').val(),
                        mobile_sec: $('#mobile_sec').val(),
                        email_sec: $('#email_sec').val(),
                        twitter: $('#twitter').val(),
                        facebook: $('#facebook').val(),
                        linkedin: $('#linkedin').val(),
                        instagram: $('#instagram').val(),
                        whatsapp: $('#whatsapp').val(),
                        contract_start_date: $('#contract_start_date').val(),
                        contract_end_date: $('#contract_end_date').val(),
                        contract_duration: $('#contract_duration').val(),
                        account_number: $('#account_number').val(),
                        account_holder_name: $('#account_holder_name').val(),
                        ifsc: $('#ifsc').val(),
                        bank_name: $('#bank_name').val(),
                        bank_address: $('#bank_address').val(),
                        branch: $('#branch').val(),
                        languages: $('#languages').val(),
                        state: $('#state').val(),
                        type:'HR',




                        "_token": "{{ csrf_token() }}",

                    },
                    success: function(status) {

                        Swal.fire({
                            icon: 'success',
                            title: `Successfully ${status}!`,
                            text: `User ${status} Successfully.`,
                            customClass: {
                                confirmButton: 'btn btn-success'
                            }
                        }).then((result) => {
                            window.location.reload();
                        });

                    },
                    error: function() {
                        Swal.fire({
                            title: 'Ohh!',
                            text: 'Error.',
                            icon: 'error',
                            customClass: {
                                confirmButton: 'btn btn-success'
                            }
                        }).then((result) => {
                            window.location.reload();
                        });

                    }
                });
            });



        })();
    </script>
@endsection

@section('content')

    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span>Users</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{$totalUser}}</h4>
                                {{--  <span class="text-success">(+29%)</span>  --}}
                            </div>
                            <span>Total Users</span>
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
                            <span>Inactive Users</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{$nonactive}}</h4>
                                {{--  <span class="text-success">(+18%)</span>  --}}
                            </div>
                            <span>Last week analytics </span>
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
                            <span>Active Users</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{$active}}</h4>
                                 {{--  <span class="text-danger">(-14%)</span>  --}}
                            </div>
                            <span>Last week analytics</span>
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
                            <span>Employees</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{$totalUser}}</h4>
                                {{--  <span class="text-success">(+42%)</span>  --}}
                            </div>
                            <span>Last week analytics</span>
                        </div>
                        <span class="badge bg-label-warning rounded p-2">
                            <i class="ti ti-user-exclamation ti-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Users List Table -->
    <div class="card">
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
                        <th>Roles</th>
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
                        <input type="text" class="form-control" id="name" placeholder="" name="name"
                            aria-label="" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input type="text" id="email" class="form-control" placeholder="" aria-label=""
                            name="email" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="mobile">Contact</label>
                        <input type="text" id="mobile" class="form-control phone-mask" placeholder="609 988 44 11"
                            aria-label="" name="mobile" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="empId">Employee ID</label>
                        <input type="text" id="empId" class="form-control " placeholder="XXX" aria-label=""
                            name="empId" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="employment_type">Employment Type</label>
                        <select id="employment_type" name="employment_type" class=" form-select select2">
                            <option disabled value="" selected>Select</option>
                            @foreach ($employment_types as $employment_type)
                                <option value={{ $employment_type->id }}> {{ $employment_type->employment_type }}</option>
                            @endforeach


                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="designation">Designation</label>
                        <select id="designation" name="designation" class=" form-select select2">
                          <option disabled value="" selected>Select</option>
                            @foreach ($designations as $designation)
                                <option value={{ $designation->id }}> {{ $designation->designation }}</option>
                            @endforeach


                        </select>
                    </div>
                    <div class="mb-3 ">
                        <label for="fromDate" class="form-label">Date of Joining</label>
                        <input type="text" class="form-control datepicker" id="doj" name="doj"
                            placeholder="MM/DD/YYYY" class="form-control" />

                    </div>
                    <div class="mb-3 contract">
                      <label for="fromDate" class="form-label">Contract start</label>
                      <input type="text" class="form-control datepicker" id="contract_start_date" name="contract_start_date"
                          placeholder="MM/DD/YYYY" class="form-control" />

                    </div>
                    <div class="mb-3 contract">
                      <label for="fromDate" class="form-label">Contract End</label>
                      <input type="text" class="form-control datepicker" id="contract_end_date" name="contract_end_date"
                          placeholder="MM/DD/YYYY" class="form-control" />
                  </div>
                    <div class="mb-3">
                        <label class="form-label" for="usertype_role">User Role</label>
                        <select id="usertype_role" name="usertype_role" class="form-select select2">
                          <option disabled value="" selected>Select</option>
                            @foreach ($usertype_roles as $usertype_role)
                                <option value={{ $usertype_role->id }}> {{ $usertype_role->usertype_role }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="reporting_officer">Reporting Officer</label>
                        <select id="reporting_officer" name="reporting_officer" class="form-select select2">
                          <option disabled value="" selected>Select</option>
                            @foreach ($reporting_officers as $reporting_officer)
                                <option value={{ $reporting_officer->user_id }}> {{ $reporting_officer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="roles">Select Privileges</label>
                        <select id="roles" name="roles" class="form-select select2" multiple>
                            @foreach ($roles as $role)
                                <option @if ($role->id == 2 || $role->id == 7) selected @endif value={{ $role->id }}>
                                    {{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" data-type="new"
                        class="btn btn-primary me-sm-3 me-1 data-submit">Submit</button>
                    <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Cancel</button>
                </form>
            </div>
        </div>





    </div>
    <!-- Modal -->
    @include('_partials/_modals/modal-edit-user')
    <!-- /Modal -->
@endsection
