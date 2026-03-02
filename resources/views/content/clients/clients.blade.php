@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Clients')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/animate-css/animate.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
@endsection

@section('vendor-script')
<script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>

<script src="{{ asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
@endsection

@section('page-script')
<script>
  'use strict';
        document.addEventListener('DOMContentLoaded', function(e) {
            (function() {
                // add role form validation
                FormValidation.formValidation(document.getElementById('addClientForm'), {
                    fields: {
                        modalClientName: {
                            validators: {
                                notEmpty: {
                                    message: 'Please enter client name'
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


            })();
        });

        (function() {

            // On edit role click, update text
            var clientEditList = document.querySelectorAll('.client-edit-modal'),
                clientAdd = document.querySelector('.add-new-client'),
                clientTitle = document.querySelector('.client-title'),
                clientSubmit = document.querySelector('.submit-client');

            $('#addClientModal').on('hidden.bs.modal', function(e) {
                $("#submit_client").data('type', 'new');
                $(this)
                    .find("input,textarea,select")
                    .val('')
                    .end()
                    .find("input[type=checkbox], input[type=radio]")
                    .prop("checked", "")
                    .end();
            })

            $('#addClientModal').on('shown.bs.modal', function(e) {
                var type = $("#submit_client").data('type');



            })

            clientAdd.onclick = function() {
                clientTitle.innerHTML = 'Add New Client'; // reset text
            };
            clientSubmit.onclick = function() {
                var type = $("#submit_client").data('type');
                var client_id = $("#submit_client").data('id');

                var modalClientName = $("#modalClientName").val();
                var modalClientCode = $('#modalClientCode').val();
                var modalClientEmail = $("#modalClientEmail").val();
                var modalClientAddress = $("#modalClientAddress").val();
                var modalClientPhone = $("#modalClientPhone").val();
                var permissions = [];
                if (type == 'edit') {
                    $.ajax({
                        data: {
                            client_name: modalClientName,
                            client_code: modalClientCode,
                            email: modalClientEmail,
                            address: modalClientAddress,
                            phone: modalClientPhone,
                            "_token": "{{ csrf_token() }}",

                        },
                        url: `${baseUrl}client/edit/${client_id}`,
                        type: 'POST',

                        success: function(status) {

                            $('#addClientModal').modal('hide');
                            // sweetalert
                            Swal.fire({
                                icon: 'success',
                                title: `Successfully ${status}!`,
                                text: `Client ${status} Successfully.`,
                                customClass: {
                                    confirmButton: 'btn btn-success'
                                }
                            });
                        },
                        error: function(err) {

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
                            client_name: modalClientName,
                            client_code: modalClientCode,
                            email: modalClientEmail,
                            address: modalClientAddress,
                            phone: modalClientPhone,
                            "_token": "{{ csrf_token() }}",

                        },
                        url: `${baseUrl}client/store`,
                        type: 'POST',

                        success: function(status) {


                            $('#addClientModal').modal('hide');
                            // sweetalert
                            Swal.fire({
                                icon: 'success',
                                title: `Successfully ${status}!`,
                                text: `Client ${status} Successfully.`,
                                customClass: {
                                    confirmButton: 'btn btn-success'
                                }
                            }).then((result) => {
                              window.location.reload();
                          });

                        },
                        error: function(err) {
                            offCanvasForm.offcanvas('hide');
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
                } else {


                    $.ajax({
                        data: {
                            client_name: modalClientName,
                            client_code: modalClientCode,
                            email: modalClientEmail,
                            address: modalClientAddress,
                            phone: modalClientPhone,
                            "_token": "{{ csrf_token() }}",

                        },
                        url: `${baseUrl}client/update${cliengt_id}`,
                        type: 'POST',

                        success: function(status) {


                            $('#addClientModal').modal('hide');
                            // sweetalert
                            Swal.fire({
                                icon: 'success',
                                title: `Successfully ${status}!`,
                                text: `Client ${status} Successfully.`,
                                customClass: {
                                    confirmButton: 'btn btn-success'
                                }
                            }).then((result) => {
                              window.location.reload();
                          });

                        },
                        error: function(err) {
                            offCanvasForm.offcanvas('hide');
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
            if (clientEditList) {
                clientEditList.forEach(function(clientEditEl) {
                    clientEditEl.onclick = function() {

                        clientTitle.innerHTML = 'Edit Client'; // reset text
                        $("#submit_client").data('type', 'edit');
                        var client_id = $(this).data('id');
                        $("#submit_client").attr('data-id', client_id);


                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                            }
                        })
                        $.ajax({
                            type: "GET",

                            url: '/client/edit/' + client_id,
                            success: function(data) {
                                console.log(data);
                                $("#modalClientName").val(data.client.client_name);
                                $("#modalClientCode").val(data.client.code);
                                $("#modalClientEmail").val(data.client.email);
                                $("#modalClientAddress").val(data.client.address);
                                $("#modalClientPhone").val(data.client.phone);
                                $("#submit_client").data('id', data.client.id);



                            },
                            error: function(data) {

                            }
                        });
                    };
                });
            }


            $(document).on('click', '.create-contact', function(e) {

              var client_id = $(this).data('id');
              $("#submit_contact_person").attr('data-client', client_id);
              $("#submit_contact_person").attr('data-type', "new");
              $('#addContactPersonModal').modal('show');
              e.preventDefault();

          });

          $(document).on('click', '#submit_contact_person', function(e) {

            var client_id = $(this).data('client');
            var contact_id = $(this).data('id');
            var type = $(this).data('type');
            e.preventDefault();

            var name=$('#contactName').val();
            var designation=$('#contactDesignation').val();
            var email=$('#contactEmail').val();
            var address= $('#contactAddress').val();
            var mobile=$('#contactMobile').val();

          if(type=='new'){
            $.ajax({
              url: `${baseUrl}client/contactPerson/store/${client_id}`,
              type: 'POST',
              data: {

                   name: name,
                  designation: designation,
                  email: email,
                  address: address,
                  mobile:mobile,
                  "_token": "{{ csrf_token() }}",

              },
              success: function(status) {

                  Swal.fire({
                      icon: 'success',
                      title: `Successfully ${status}!`,
                      text: `Contact Person ${status} Successfully.`,
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
          }
          else if(type=='edit'){
            $.ajax({
              url: `${baseUrl}client/contactPerson/update/${contact_id}`,
              type: 'POST',
              data: {

                   name: name,
                  designation: designation,
                  email: email,
                  address: address,
                  mobile:mobile,
                  "_token": "{{ csrf_token() }}",

              },
              success: function(status) {

                  Swal.fire({
                      icon: 'success',
                      title: `Successfully ${status}!`,
                      text: `Contact Person ${status} Successfully.`,
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
          }

        });


      $(document).on('click', '.edit-contact', function(e) {


        var contact_id = $(this).data('id');
        $("#submit_contact_person").attr('data-id', contact_id);
        $("#submit_contact_person").attr('data-type', "edit");

        e.preventDefault();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        })
        $.ajax({
            type: "GET",

            url: '/client/contactPerson/edit/' + contact_id,
            success: function(response) {

                console.log(response);
                var data= response.data;

                $('#contactName').val(data.name);
                $('#contactDesignation').val(data.designation);
                $('#contactEmail').val(data.email);
                $('#contactAddress').text(data.address);
                $('#contactMobile').val(data.mobile);
                $('#addContactPersonModal').modal('show');



            },
            error: function(data) {

            }
        });

    });



    // Real-time duplicate check
$('#modalClientCode').on('input', function() {
    let code = $(this).val();
    let clientId = $("#submit_client").data('id');

    if(code.length === 4){
        $.ajax({
            url: `${baseUrl}client/check-code`,
            type: 'POST',
            data: {
                code: code,
                id: clientId,
                "_token": "{{ csrf_token() }}"
            },
            success: function(res) {
                if(res.exists){
                    $('#codeError').removeClass('d-none');
                    $('#submit_client').prop('disabled', true);
                } else {
                    $('#codeError').addClass('d-none');
                    $('#submit_client').prop('disabled', false);
                }
            }
        });
    } else {
        $('#codeError').addClass('d-none');
        $('#submit_client').prop('disabled', true);
    }
});



        })();
</script>
@endsection

@section('content')
<h4 class="fw-semibold mb-4">Client List</h4>

{{-- <p class="mb-4">A role provided access to predefined menus and features so that depending on <br> assigned role an
  administrator can have access to what user needs.</p> --}}
<div class="  text-center mb-2">
  <button data-bs-target="#addClientModal" data-bs-toggle="modal"
    class="btn btn-primary mb-2 text-nowrap add-new-client">Add New Client</button>
  <p class="mb-0 mt-1">Add Client, if it does not exist</p>
</div>
<!-- Role cards -->
<div class="row g-4">
  @foreach ($clients as $client)
  <!-- Orders tabs-->
  <div class="col-md-6 col-lg-4 col-xl-4 mb-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between pb-2 mb-1">
        <div class="card-title mb-1">
          <h5 class="m-0 me-2">{{ $client->client_name }}</h5>
          {{-- <small class="text-muted">Total {{ $client->projects_count }} Projects</small> --}}
        </div>
        <div class="dropdown">
          <button class="btn p-0" type="button" id="salesByCountryTabs" data-bs-toggle="dropdown" aria-haspopup="true"
            aria-expanded="false">
            <i class="ti ti-dots-vertical ti-sm text-muted"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end" aria-labelledby="salesByCountryTabs">

            <a href="javascript:;" data-bs-toggle="modal" data-id={{ $client->id }}
              data-bs-target="#addClientModal"
              class=" dropdown-item client-edit-modal edit-record"><span>Edit Client</span></a>
            <a data-id={{ $client->id }} class=" dropdown-item client-contact-modal create-contact"><span>Add Contact
                Person</span></a>

          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="nav-align-top">
          <ul class="nav nav-tabs nav-fill" role="tablist">
            <li class="nav-item">
              <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                data-bs-target="#navs-justified-link-about{{ $client->id }}" aria-controls="navs-justified-link-about"
                aria-selected="false">About</button>
            </li>
            {{-- <li class="nav-item">
              <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                data-bs-target="#navs-justified-link-project{{ $client->id }}"
                aria-controls="navs-justified-link-project" aria-selected="false">Projects</button>
            </li> --}}
            <li class="nav-item">
              <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                data-bs-target="#navs-justified-link-contact{{ $client->id }}"
                aria-controls="navs-justified-link-contact" aria-selected="false">Contacts</button>
            </li>
          </ul>
          <div class="tab-content pb-0">

            <div class="tab-pane fade active show" id="navs-justified-link-about{{ $client->id }}" role="tabpanel">
              {{ $client->client_name }}

              <div class="border-bottom border-bottom-dashed mt-0 mb-4"></div>
              <span class="p-2 bg bg-label-primary fw-bold">{{ $client->code }}</span>
            </div>

            {{-- <div class="tab-pane fade" id="navs-justified-link-project{{ $client->id }}" role="tabpanel">
              @foreach ($client->projects as $key => $value)
              <ul class="timeline timeline-advance mb-2 pb-1">
                <li class="timeline-item ps-4 border-left-dashed">
                  <span class="timeline-indicator timeline-indicator-success">
                    <i class="ti ti-circle-check"></i>
                  </span>
                  <div class="timeline-event ps-0 pb-0">
                    <div class="timeline-header">
                      <small class="text-success text-uppercase fw-semibold">{{ $value->project_name }}</small>
                    </div>
                    <h6 class="mb-0">{{ $value->type }}</h6>
                    <p class="text-muted mb-0">{{ $value->description }}</p>
                  </div>
                </li>

              </ul>
              <div class="border-bottom border-bottom-dashed mt-0 mb-4"></div>
              @endforeach



            </div> --}}
            <div class="tab-pane fade " id="navs-justified-link-contact{{ $client->id }}" role="tabpanel">
              <ul class="timeline timeline-advance mb-2 pb-1">
                <li class="timeline-item ps-4 border-left-dashed">
                  <span class="timeline-indicator timeline-indicator-success">
                    <i class="ti ti-circle-check"></i>
                  </span>
                  <div class="timeline-event ps-0 pb-0">
                    <div class="timeline-header">
                      <small class="text-primary text-uppercase fw-semibold">{{ $client->address }}</small>
                    </div>
                    <h6 class="mb-0">{{ $client->email }}</h6>
                    <p class="text-muted mb-0">{{ $client->phone }}</p>
                  </div>
                </li>
                <div class="divider">
                  <div class="divider-text">
                    Contact Persons
                  </div>
                </div>
                @foreach ($client->contactPersons as $person)
                <li class="timeline-item ps-4 border-left-dashed">
                  <span class="timeline-indicator timeline-indicator-success">
                    <i class="ti ti-circle-check"></i>
                  </span>
                  <div class="timeline-event ps-0 pb-0">
                    <div class="timeline-header">
                      <small class="text-primary text-uppercase fw-semibold">{{ $person->name }}
                        - ({{ $person->designation }})</small>
                      <span class="float-end">
                        <button data-id="{{ $person->id }}"
                          class="btn btn-outline-warning btn-sm my-sm-0 my-3 edit-contact">
                          Edit
                        </button>
                      </span>
                    </div>
                    <h6 class="mb-0">{{ $person->email }}</h6>
                    <p class="text-muted mb-0">{{ $person->phone }}</p>

                  </div>
                </li>
                @endforeach

              </ul>


            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--/ Orders tabs -->
  @endforeach

  {{--
  <div class="col-xl-4 col-lg-4 col-md-4">
    <div class="card h-100">
      <div class="row h-100">

        <div class="col-sm-7">

        </div>
      </div>
    </div>
  </div> --}}

</div>
<!--/ Client cards -->

<!-- Add Client Modal -->
@include('_partials/_modals/modal-add-client')
@include('_partials/_modals/modal-add-client-contactPerson')
<!-- / Add Client Modal -->
@endsection