@extends('layouts/layoutMaster')

@section('title', 'Employment Types - Master')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/animate-css/animate.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
    <link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}" />
@endsection

@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>

    <script src="{{ asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
    <script src="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js')}}" ></script>
<script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
@endsection

@section('page-script')
    <script>
        $(function() {
          $(".datepicker").datepicker({
            autoclose: true ,
            });

            var dataTablePermissions = $('.datatables-employment'),
                dt_permission,
                permissionList = baseUrl + 'master/holiday/list';

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
                        {
                            data: ''
                        },
                        {
                            data: 'id'
                        },
                        {
                            data: 'date'
                        },
                        {
                            data: 'description'
                        },
                        {
                            data: 'created_at'
                        },
                        {{--  {
                            data: ''
                        }  --}}
                    ],
                    columnDefs: [{
                            // For Responsive
                            className: 'control',
                            orderable: false,
                            searchable: false,
                            responsivePriority: 2,
                            targets: 0,
                            render: function(data, type, full, meta) {
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
                            render: function(data, type, full, meta) {
                                var $name = full['date'];
                                return '<span class="text-nowrap">' + $name + '</span>';
                            }
                        },
                        {
                            // User Role
                            targets: 3,
                            render: function(data, type, full, meta) {
                                var $description = full['description'];

                                return $description;
                            }
                        },

                        {
                            // remove ordering from Name
                            targets: 4,
                            orderable: false,
                            render: function(data, type, full, meta) {
                                var $date = full['created_at'];
                                return '<span class="text-nowrap">' + $date + '</span>';
                            }
                        },
                        {{--  {
                            // Actions
                            targets: -1,
                            searchable: false,
                            title: 'Actions',
                            orderable: false,
                            render: function(data, type, full, meta) {
                                return (
                                    '<span class="text-nowrap">' +
                                    '<button class="btn btn-sm btn-icon delete-record"><i class="ti ti-trash"></i></button></span>'
                                );
                            }
                        }  --}}
                    ],
                    order: [
                        [1, 'asc']
                    ],
                    dom: '<"row mx-1"' +
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
                    buttons: [{
                        text: 'Add Holiday',
                        className: 'add-new btn btn-primary mb-3 mb-md-0 add-new-employment-type',
                        attr: {
                            'data-bs-toggle': 'modal',
                            'data-bs-target': '#EmploymentTypeModal'
                        },
                        init: function(api, node, config) {
                            $(node).removeClass('btn-secondary');
                        }
                    }],
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
                    }

                });
            }




            // Add/Edit designation form validation
            document.addEventListener('DOMContentLoaded', function(e) {
                (function() {
                    FormValidation.formValidation(document.getElementById('employmentTypeForm'), {
                        fields: {
                            date: {
                                validators: {
                                    notEmpty: {
                                        message: 'Please enter date'
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

            (function() {
                // On edit permission click, update text
                var designationEditList = document.querySelectorAll(
                        '.datatables-employment .edit-employment-type'),
                    permissionAdd = document.querySelector('.add-new-employment-type'),
                    designationSubmit = document.querySelector('.submit-employment');
                designationTitle = document.querySelector('.employment-title'),

                    $('#EmploymentTypeModal').on('hidden.bs.modal', function(e) {
                        $(this)
                            .find("input,textarea,select")
                            .val('')
                            .end()
                            .find("input[type=checkbox], input[type=radio]")
                            .prop("checked", "")
                            .end();
                    })

                designationSubmit.onclick = function() {

                    var date = $("#date").val();
                    var description = $("#description").val();
                    var type = $("#submit_employment").data('type');
                    var desig_id = $("#submit_employment").data('id');
                    if (type == 'new') {
                        $.ajax({
                            data: {
                                date: date,
                                description: description,

                                "_token": "{{ csrf_token() }}",

                            },
                            url: `${baseUrl}master/holiday/store`,
                            type: 'POST',

                            success: function(status) {

                                $('#EmploymentTypeModal').modal('hide');
                                // sweetalert
                                Swal.fire({
                                    icon: 'success',
                                    title: `Successfully ${status}!`,
                                    text: `Designation ${status} Successfully.`,
                                    customClass: {
                                        confirmButton: 'btn btn-success'
                                    }
                                });

                            },
                            error: function(err) {
                                $('#EmploymentTypeModal').modal('hide');
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
                    {{--  else {
                        $.ajax({
                            data: {
                                designation: modalDesignationName,

                                "_token": "{{ csrf_token() }}",

                            },
                            url: `${baseUrl}master/holiday/update/${desig_id}`,
                            type: 'POST',

                            success: function(status) {

                                $('#EmploymentTypeModal').modal('hide');
                                // sweetalert
                                Swal.fire({
                                    icon: 'success',
                                    title: `Successfully ${status}!`,
                                    text: `Designation ${status} Successfully.`,
                                    customClass: {
                                        confirmButton: 'btn btn-success'
                                    }
                                });

                            },
                            error: function(err) {
                                $('#EmploymentTypeModal').modal('hide');
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
                    }  --}}
                }





            })();

            // Delete Record
            $('.datatables-designation tbody').on('click', '.delete-record', function() {
                dt_permission.row($(this).parents('tr')).remove().draw();
            });

            // Edit Record
            $('.datatables-designation tbody').on('click', '.edit-designation', function() {
                designationTitle.innerHTML = 'Edit Designation'; // reset text
                $("#submit_designation").attr('data-type', 'edit');

                var desig_id = $(this).data('id');
                $("#submit_designation").attr('data-id', desig_id);
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                    }
                })
                $.ajax({
                    type: "GET",

                    url: '/master/designation/edit/' + desig_id,
                    success: function(data) {
                        console.log(data);
                        $("#employment_type").val(data.designation.designation);
                        $("#submit_employment").data('id', data.designation.id);


                    },
                    error: function(data) {

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
    <h4 class="fw-semibold mb-4">HolidayList</h4>

    {{--  <p class="mb-4">Each category (Basic, Professional, and Business) includes the four predefined roles shown below.</p>  --}}

    <!-- Permission Table -->
    <div class="card">
        <div class="card-datatable table-responsive">
            <table class="datatables-employment table border-top">
                <thead>
                    <tr>
                        <th></th>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Created Date</th>
                        {{--  <th>Actions</th>  --}}
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <!--/ Permission Table -->


    <!-- Modal -->
    @include('_partials/_modals/modal-holiday')
    <!-- /Modal -->
@endsection
