@extends('layouts/layoutMaster')

@section('title', 'Projects - Client')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/animate-css/animate.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    {{--  <link rel="stylesheet" href="{{asset('assets/vendor/libs/tagify/tagify.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-select/bootstrap-select.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/typeahead-js/typeahead.css')}}" />  --}}

@endsection

@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>

    <script src="{{ asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>

    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    {{--  <script src="{{asset('assets/vendor/libs/tagify/tagify.js')}}"></script>  --}}
    <script src="{{ asset('assets/vendor/libs/bootstrap-select/bootstrap-select.js') }}"></script>
    {{--  <script src="{{asset('assets/vendor/libs/typeahead-js/typeahead.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bloodhound/bloodhound.js')}}"></script>  --}}


@endsection

@section('page-script')
    <script src="{{ asset('assets/js/forms-selects.js') }}"></script>
    {{--  <script src="{{asset('assets/js/forms-tagify.js')}}"></script>
<script src="{{asset('assets/js/forms-typeahead.js')}}"></script>  --}}

    <script>
        $(function() {


            var dataTablePermissions = $('.datatables-permissions'),
                dt_project,
                userView = baseUrl + 'user/employee/view/account',
                projectList = baseUrl + 'project/list';

            // ajax setup
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Users List datatable
            if (dataTablePermissions.length) {
                dt_project = dataTablePermissions.DataTable({
                    ajax: {
                        url: projectList
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
                            data: 'project_name'
                        },
                        {
                            data: 'description'
                        },
                        {
                            data: 'typeName'
                        },
                        {
                            data: 'clients'
                        },
                        {
                            data: 'leads'
                        },
                        {
                            data: 'members'
                        },
                        {{--  { data: 'created_at' },  --}} {
                            data: ''
                        }
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
                                var $name = full['project_name'];
                                return '<span class="text-nowrap">' + $name + '</span>';
                            }
                        },
                        {
                            // Description
                            targets: 3,
                            render: function(data, type, full, meta) {
                                var $name = full['description'];
                                return '<span class="">' + $name + '</span>';
                            }
                        },
                        {
                            // type
                            targets: 4,
                            render: function(data, type, full, meta) {
                                var $name = full['typeName'];
                                $name = $name.split(',');
                                {{--  return '<span class="text-nowrap">' + $name + '</span>';  --}}
                                {{--  var $clients = full['clients'],  --}}
                                $output = '';
                                for (var i = 0; i < $name.length; i++) {
                                    var val = $name[i];

                                    $output += '<a><span class="badge bg-label-primary m-1">' +
                                        $name[i] + '</span></a>';
                                }
                                return '<span class="text-nowrap">' + $output + '</span>';
                            }
                        },
                        {
                            // User Role
                            targets: 5,
                            orderable: false,
                            render: function(data, type, full, meta) {
                                var $clients = full['clients'],
                                    $output = '';
                                $1 = 'primary';
                                $2 = 'warning';
                                $3 = 'success';
                                $4 = 'info';
                                for (var i = 0; i < $clients.length; i++) {
                                    var val = $clients[i];

                                    $output += '<a><span class="badge bg-label-success m-1">' +
                                        $clients[i]['client_name'] + '</span></a>';
                                }
                                return '<span class="text-nowrap">' + $output + '</span>';
                            }
                        },
                        {
                            // User Role
                            targets: 6,
                            orderable: false,
                            render: function(data, type, full, meta) {
                                var $leads = full['leads'],
                                    $finaloutput = '';
                                for (var i = 0; i < $leads.length; i++) {
                                    var val = $leads[i];
                                    $image = $leads[i]['profile_pic']
                                    if ($image) {
                                        // For Avatar image
                                        var $output =
                                            '<li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" title="' +
                                            $leads[i]['name'] + '"><img src="' + assetsPath +
                                            'img/avatars/' + $image +
                                            '" alt="Avatar" class="rounded-circle"></li>';
                                    } else {
                                        // For Avatar badge
                                        var stateNum = Math.floor(Math.random() * 6);
                                        var states = ['success', 'danger', 'warning', 'info',
                                            'primary', 'secondary'
                                        ];
                                        var $state = states[stateNum],
                                            $name = $leads[i]['name'],
                                            $initials = $name.match(/\b\w/g) || [];
                                        $initials = (($initials.shift() || '') + ($initials.pop() ||
                                            '')).toUpperCase();
                                        $output =
                                            '<li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" title="' +
                                            $leads[i]['name'] +
                                            '"><span class="avatar-initial rounded-circle bg-label-' +
                                            $state + '">' + $initials + '</span></li>';
                                    }
                                    $finaloutput += $output;
                                }

                                return '<ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">' +
                                    $finaloutput + '</ul>';
                            }
                        },
                        {
                            // User Role
                            targets: 7,
                            orderable: false,
                            render: function(data, type, full, meta) {
                                var $members = full['members'],
                                    $finaloutput = '';
                                for (var i = 0; i < $members.length; i++) {
                                    var val = $members[i];
                                    $image = $members[i]['profile_pic']
                                    if ($image) {
                                        // For Avatar image
                                        var $output =
                                            '<li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" title="' +
                                            $members[i]['name'] + '"><img src="' + assetsPath +
                                            'img/avatars/' + $image +
                                            '" alt="Avatar" class="rounded-circle"></li>';
                                    } else {
                                        // For Avatar badge
                                        var stateNum = Math.floor(Math.random() * 6);
                                        var states = ['success', 'danger', 'warning', 'info',
                                            'primary', 'secondary'
                                        ];
                                        var $state = states[stateNum],
                                            $name = $members[i]['name'],
                                            $initials = $name.match(/\b\w/g) || [];
                                        $initials = (($initials.shift() || '') + ($initials.pop() ||
                                            '')).toUpperCase();
                                        $output =
                                            '<li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-xs pull-up" title="' +
                                            $members[i]['name'] +
                                            '"><span class="avatar-initial rounded-circle bg-label-' +
                                            $state + '">' + $initials + '</span></li>';

                                    }
                                    // Creates full output for row
                                    $finaloutput += $output;
                                }

                                return '<ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">' +
                                    $finaloutput + '</ul>';
                            }
                        },



                        {{--  {
          // remove ordering from Name
          targets: 8,
          orderable: false,
          render: function (data, type, full, meta) {
            var $date = full['created_at'];
            return '<span class="text-nowrap">' + $date + '</span>';
          }
        },  --}} {
                            // Actions
                            targets: -1,
                            searchable: false,
                            title: 'Actions',
                            orderable: false,
                            render: function(data, type, full, meta) {
                                return (
                                    '<span class="text-nowrap"><button class="btn btn-sm btn-icon me-2 edit-record project-edit-modal" data-id=' +
                                    full['id'] +
                                    '  data-bs-target="#addProjectModal" data-bs-toggle="modal"  data-bs-dismiss="modal"><i class="ti ti-edit"></i></button>' +
                                    '<button class="btn btn-sm btn-icon delete-record"><i class="ti ti-trash"></i></button></span>'

                                );
                            }
                        }
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
                        text: 'Add Project',
                        className: 'add-new btn btn-primary mb-3 mb-md-0 add-new-project',
                        attr: {
                            'data-bs-toggle': 'modal',
                            'data-bs-target': '#addProjectModal'
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
                                    return 'Details of ' + data['project_name'];
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




            // Edit permission form validation
            document.addEventListener('DOMContentLoaded', function(e) {
                (function() {
                    FormValidation.formValidation(document.getElementById('editProjectForm'), {
                        fields: {
                            editPermissionName: {
                                validators: {
                                    notEmpty: {
                                        message: 'Please enter project name'
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
            document.addEventListener('DOMContentLoaded', function(e) {
                (function() {
                    // add Project form validation
                    FormValidation.formValidation(document.getElementById('addProjectForm'), {
                        fields: {
                            modalProjectName: {
                                validators: {
                                    notEmpty: {
                                        message: 'Please enter Project name'
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
                                rowSelector: '.col-12'
                            }),
                            submitButton: new FormValidation.plugins.SubmitButton(),
                            // Submit the form when all fields are valid
                            // defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
                            autoFocus: new FormValidation.plugins.AutoFocus()
                        }
                    });

                    // Select All checkbox click
                    const selectAll = document.querySelector('#selectAll'),
                        checkboxList = document.querySelectorAll('[type="checkbox"]');
                    selectAll.addEventListener('change', t => {
                        checkboxList.forEach(e => {
                            e.checked = t.target.checked;
                        });
                    });
                })();
            });

            (function() {
                // On edit Project click, update text
                var projectEditList = document.querySelectorAll('.project-edit-modal'),
                    projectAdd = document.querySelector('.add-new-project'),
                    projectTitle = document.querySelector('.project-title'),
                    projectSubmit = document.querySelector('.submit-project');

                $('#addProjectModal').on('hidden.bs.modal', function(e) {
                    $(this)
                        .find("input,textarea,select")
                        .val('')
                        .end()
                        .find("input[type=checkbox], input[type=radio]")
                        .prop("checked", "")
                        .end();
                });

                $('.client-checkbox').change(function(e){
                  e.preventDefault();
                  var clientsList = [];
                  $('.client-checkbox').each(function() {

                    if ($(this).is(":checked")) {
                      clientsList.push($(this).data('id'));
                    }

                })
                if(clientsList.length >0){

                  $.ajaxSetup({
                      headers: {
                          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                      }
                  })
                  $.ajax({
                      type: "GET",

                      url: '/client/contactPersons',
                      data:{
                        clients:clientsList
                      },
                      success: function(response) {
                          console.log(response.data);
                          var options='';
                          response.data.forEach((item, index) => {
                            options = options+' <optgroup label="'+item.client_name+'">';
                              item.contact_persons.forEach((person, key) => {
                                options = options+' <option value="'+person.id+'" >'+person.name+' -' + person.designation+'</option>';

                              });
                              options = options+' </optgroup>';

                          });
                          $('select#contactperson').html(options);






                      },
                      error: function(data) {


                      }
                  });
                }
                else{
                  $('select#contactperson').html('');
                }
                console.log(clientsList);
              });

                projectAdd.onclick = function() {
                    projectTitle.innerHTML = 'Add New Project'; // reset text
                };
                projectSubmit.onclick = function() {
                    var type = $("#submit_project").data('type');
                    var project_id = $(this).data('id');
                    var modalProjectName = $("#modalProjectName").val();
                    var modalProjectDescription = $("#modalProjectDescription").val();
                    var modalProjectType = $('select#modalProjectType').val();
                    modalProjectType = modalProjectType.join();
                    var projectcost = $("#projectcost").val();

                    var leads = $('select#leads').val();
                    var members = $('select#members').val();

                    var clients = [];
                    $('.client-checkbox').each(function() {
                        if ($(this).is(":checked")) {
                            clients.push($(this).data('id'));
                        }
                    })
                    var initiatedBy = $('select#initiatedBy').val();
                    initiatedBy = initiatedBy.join();
                    var contactperson = $('select#contactperson').val();
                    contactperson = contactperson.join();
                    var contaractStaff = $("#contaractStaff").val();
                    var fieldStaff = $("#fieldStaff").val();
                    var projectStaff = $("#projectStaff").val();
                    var tenure_year = $("#tenure_year").val();
                    var tenure_month = $("#tenure_month").val();
                    var tenure_days = $("#tenure_days").val();
                    var expected_start_date = $("#expected_start_date").val();
                    var expected_end_date = $("#expected_end_date").val();
                    var additional_support = $("#additional_support").val();
                    var remarks = $("#remarks").val();

                    if (type == 'edit') {
                        $.ajax({
                            data: {
                                project_name: modalProjectName,
                                description: modalProjectDescription,
                                type: modalProjectType,
                                clients: clients,
                                leads: leads,
                                members: members,
                                projectcost:projectcost,
                                initiatedBy:initiatedBy,
                                contactperson:contactperson,
                                contaractStaff:contaractStaff,
                                projectStaff:projectStaff,
                                fieldStaff:fieldStaff,
                                tenure_year:tenure_year,
                                tenure_month:tenure_month,
                                tenure_days:tenure_days,
                                expected_start_date:expected_start_date,
                                expected_end_date:expected_end_date,
                                additional_support:additional_support,
                                remarks:remarks,

                                "_token": "{{ csrf_token() }}",

                            },
                            url: `${baseUrl}project/edit/${project_id}`,
                            type: 'POST',

                            success: function(status) {

                                $('#addProjectModal').modal('hide');
                                // sweetalert
                                Swal.fire({
                                    icon: 'success',
                                    title: `Successfully ${status}!`,
                                    text: `Project ${status} Successfully.`,
                                    customClass: {
                                        confirmButton: 'btn btn-success'
                                    }
                                });
                            },
                            error: function(err) {
                                $('#addProjectModal').modal('hide');
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
                    } else if (type == 'new') {
                        $.ajax({
                            data: {
                                project_name: modalProjectName,
                                description: modalProjectDescription,
                                type: modalProjectType,
                                clients: clients,
                                leads: leads,
                                members: members,
                                projectcost:projectcost,
                                initiatedBy:initiatedBy,
                                contactperson:contactperson,
                                projectStaff:projectStaff,
                                contaractStaff:contaractStaff,
                                fieldStaff:fieldStaff,
                                tenure_year:tenure_year,
                                tenure_month:tenure_month,
                                tenure_days:tenure_days,
                                expected_start_date:expected_start_date,
                                expected_end_date:expected_end_date,
                                additional_support:additional_support,
                                remarks:remarks,
                                "_token": "{{ csrf_token() }}",

                            },
                            url: `${baseUrl}project/store`,
                            type: 'POST',

                            success: function(status) {

                                $('#addProjectModal').modal('hide');
                                // sweetalert
                                Swal.fire({
                                    icon: 'success',
                                    title: `Successfully ${status}!`,
                                    text: `Project ${status} Successfully.`,
                                    customClass: {
                                        confirmButton: 'btn btn-success'
                                    }
                                });
                            },
                            error: function(err) {
                                $('#addProjectModal').modal('hide');
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

                };



            })();

            $('.datatables-permissions tbody').on('click', '.edit-record', function() {
                projectTitle = document.querySelector('.project-title'),
                    projectTitle.innerHTML = 'Edit Project'; // reset text
                $("#submit_project").data('type', 'edit');
                $("#submit_project").data('type', 'edit');
                var project_id = $(this).data('id');
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                    }
                })
                $.ajax({
                    type: "GET",

                    url: '/project/edit/' + project_id,
                    success: function(data) {
                        console.log(data);
                        $("#modalProjectName").val(data.project.project_name);
                        $("#modalProjectDescription").val(data.project.description);

                        var options = data.project.type.split(',');

                        $('#modalProjectType').val(options);
                        $('#modalProjectType').trigger('change');



                        {{--  $("#modalProjectType").val(data.project.type);  --}}
                        $("#submit_project").data('id', data.project.id);
                        var projectClients = data['projectClients'];
                        console.log(projectClients);
                        $('.client-checkbox').each(function() {
                            console.log($(this).data('id'));
                            if (projectClients.some(item => item.client_id === $(this)
                                    .data('id'))) {
                                $(this).prop('checked', true);
                            }
                        })
                        $('.client-checkbox').trigger('change');
                        var options_contact = data.project.client_contact_person.split(',');
                        $('#contactperson').val(options_contact);
                        {{--  $('#contactperson').trigger('change');  --}}

                        $('#projectcost').val(data.project.total_cost);


                    },
                    error: function(data) {

                    }
                });
                $('#addProjectModal').modal('show');
            });


            // Delete Record
            $('.datatables-permissions tbody').on('click', '.delete-record', function() {
                dt_project.row($(this).parents('tr')).remove().draw();
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
    <h4 class="fw-semibold mb-4">Project List</h4>

    <p class="mb-4">Each category (Basic, Professional, and Business) includes the four predefined roles shown below.</p>

    <!-- Permission Table -->
    <div class="card">
        <div class="card-datatable table-responsive">
            <table class="datatables-permissions table border-top">
                <thead>
                    <tr>
                        <th></th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Type</th>
                        <th>Clients</th>
                        <th>Leads</th>
                        <th>Teams</th>
                        {{--  <th>Created Date</th>  --}}
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <!--/ Permission Table -->


    <!-- Modal -->
    {{--  @include('_partials/_modals/modal-add-permission')
@include('_partials/_modals/modal-edit-permission')  --}}
    @include('_partials/_modals/modal-add-project', [
        'clients' => $clients,
        'projectTypes' => $projectTypes,
        'leads' => $leads,
        'members' => $members,
    ])
    <!-- /Modal -->
@endsection
