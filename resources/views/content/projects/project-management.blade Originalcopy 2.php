@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Clients')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
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
  'use strict';
  document.addEventListener('DOMContentLoaded', function (e) {
    (function () {
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

(function () {
  // On edit role click, update text
  var clientEditList = document.querySelectorAll('.client-edit-modal'),
    clientAdd = document.querySelector('.add-new-client'),
    clientTitle = document.querySelector('.client-title'),
    clientSubmit = document.querySelector('.submit-client');

    $('#addClientModal').on('hidden.bs.modal', function (e) {
      $(this)
        .find("input,textarea,select")
           .val('')
           .end()
        .find("input[type=checkbox], input[type=radio]")
           .prop("checked", "")
           .end();
    })

    clientAdd.onclick = function () {
      clientTitle.innerHTML = 'Add New Client'; // reset text
  };
  clientSubmit.onclick = function () {
    var type =   $("#submit_client").data('type');
    var cliengt_id = $(this).data('id');
   var  modalClientName =  $("#modalClientName").val();
   var  modalClientEmail =  $("#modalClientEmail").val();
   var  modalClientAddress =  $("#modalClientAddress").val();
   var  modalClientPhone =  $("#modalClientPhone").val();
    var permissions = [];
    if(type=='edit'){
    $.ajax({
      data:  {
        client_name:modalClientName,
        email:modalClientEmail,
        address:modalClientAddress,
        phone:modalClientPhone,
        "_token": "{{ csrf_token() }}",

    },
      url: `${baseUrl}client/edit/${client_id}`,
      type: 'POST',

      success: function (status) {

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
      error: function (err) {

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
   else if(type=='new'){
      $.ajax({
        data:  {
          client_name:modalClientName,
          email:modalClientEmail,
          address:modalClientAddress,
          phone:modalClientPhone,
          "_token": "{{ csrf_token() }}",

      },
        url: `${baseUrl}client/store`,
        type: 'POST',

        success: function (status) {

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
        error: function (err) {
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
    clientEditList.forEach(function (clientEditEl) {
      clientEditEl.onclick = function () {

        clientTitle.innerHTML = 'Edit Client'; // reset text
        $("#submit_client").data('type','edit');


        var client_id = $(this).data('id');
        $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      })
      $.ajax({
      type: "GET",

      url: '/client/edit/'+client_id,
      success: function (data) {
        console.log(data);
          $("#modalClientName").val(data.client.client_name);
          $("#submit_client").data('id',data.client.id);


      },
      error: function(data){

      }
  });
      };
    });
  }


})();



</script>
@endsection

@section('content')
<h4 class="fw-semibold mb-4">Project List</h4>
<div class="row g-4">
<p class="mb-4 float-end"><button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProjectModal">Add Project</button></p>
</div>
<!-- Role cards -->
<div class="row g-4">
  @foreach ($projects as $project)

  <div class="col-xl-4 col-lg-6 col-md-6">
    <div class="card">
      <div class="card-header">
        <div class="d-flex align-items-start">
          <div class="d-flex align-items-start">
            <div class="avatar me-2">
              <img src="{{ asset('assets/img/icons/brands/social-label.png') }}" alt="Avatar" class="rounded-circle" />
            </div>
            <div class="me-2 ms-1">
              <h5 class="mb-0"><a href="javascript:;" class="stretched-link text-body">{{$project->project_name}}</a></h5>
              <div class="client-info"><strong>Clients: </strong><br>
                @foreach($project->clients as $key => $value)
                <span class="text-muted  ">
                  <div class="badge bg-label-primary me-3 rounded p-2">
                    <i class="ti ti-user ti-sm"></i>   {{$value->client_name}}
                  </div>

                </span>
                @endforeach
              </div>
            </div>
          </div>
          <div class="ms-auto">
            <div class="dropdown zindex-2">
              <button type="button" class="btn dropdown-toggle hide-arrow p-0" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical text-muted"></i></button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="javascript:void(0);">Rename project</a></li>
                <li><a class="dropdown-item" href="javascript:void(0);">View details</a></li>
                <li><a class="dropdown-item" href="javascript:void(0);">Add to favorites</a></li>
                <li>
                  <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item text-danger" href="javascript:void(0);">Leave Project</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="d-flex align-items-center flex-wrap">
          <div class="bg-lighter px-3 py-2 rounded me-auto mb-3">
            <h6 class="mb-0">{{$project->total_cost}} <span class="text-body fw-normal">/ $18.2k</span></h6>
            <span>Total Budget</span>
          </div>
          <div class="text-end mb-3">
            <h6 class="mb-0">Start Date: <span class="text-body fw-normal">{{$project->expected_start_date}}</span></h6>
            <h6 class="mb-1">Deadline: <span class="text-body fw-normal">{{$project->expected_end_date}}</span></h6>
          </div>
        </div>
        <p class="mb-0"> {{$project->description}}</p>
      </div>
      <div class="card-body border-top">
        <div class="d-flex align-items-center mb-3">
          <h6 class="mb-1">All Hours: <span class="text-body fw-normal">380/244</span></h6>
          <span class="badge bg-label-success ms-auto">28 Days left</span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-2 pb-1">
          <small>Task: 290/344</small>
          <small>95% Completed</small>
        </div>
        <div class="progress mb-2" style="height: 8px;">
          <div class="progress-bar" role="progressbar" style="width: 95%;" aria-valuenow="95" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <div class="d-flex align-items-center pt-1">
          <div class="d-flex align-items-center">
            <ul class="list-unstyled d-flex align-items-center avatar-group mb-0 zindex-2 mt-1">
              @foreach($project->members as $key => $value)
              <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" title="{{$value->name}}" class="avatar avatar-sm pull-up">

                <img src="{{ asset('assets/img/avatars/'.$value->profile_pic) }}" alt="Avatar" class="rounded-circle" />
              </li>
              @endforeach

              <li><small class="text-muted p-1">{{$project->members_count}} Members</small></li>
            </ul>
          </div>
          <div class="ms-auto">
            <a href="javascript:void(0);" class="text-body"><i class="ti ti-message-dots ti-sm"></i> 15</a>
          </div>
        </div>
      </div>
    </div>
  </div>



  <div class="card h-100 col-md-12 col-lg-12 col-xl-12 mb-4">
    <div class="card-header d-flex justify-content-between pb-2 mb-1">
      <div class="card-title mb-1">
        <h5 class="m-0 me-2">{{$project->project_name}}</h5>
        <small class="text-muted">Total {{$project->clients_count}} Employees</small>
      </div>
      <div class="dropdown">
        <button class="btn p-0" type="button" id="salesByCountryTabs" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <i class="ti ti-dots-vertical ti-sm text-muted"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="salesByCountryTabs">

          <a href="javascript:;" data-bs-toggle="modal"  data-id={{$project->id}} data-bs-target="#addClientModal" class=" dropdown-item client-edit-modal edit-record"><span>Edit Client</span></a>

        </div>
      </div>
    </div>
    <div class="card-body">


        <div class="nav-align-top pt-2">
          <ul class="nav nav-pills mb-3" role="tablist">
            {{--  <li class="nav-item">
              <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-top-home{{$project->id}}" aria-controls="navs-pills-top-home" aria-selected="true">About</button>
            </li>  --}}
            <li class="nav-item">
              <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-top-client{{$project->id}}" aria-controls="navs-pills-top-home" aria-selected="true">Client</button>
            </li>
            <li class="nav-item">
              <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-top-profile{{$project->id}}" aria-controls="navs-pills-top-profile" aria-selected="false">Team</button>
            </li>
            <li class="nav-item">
              <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-top-manage{{$project->id}}" aria-controls="navs-pills-top-messages" aria-selected="false">HR Manage</button>
            </li>
            <li class="nav-item">
              <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-top-manage{{$project->id}}" aria-controls="navs-pills-top-messages" aria-selected="false">Finance</button>
            </li>
            <li class="nav-item">
              <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-top-manage{{$project->id}}" aria-controls="navs-pills-top-messages" aria-selected="false">Milestone</button>
            </li>
            <li class="nav-item">
              <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-top-manage{{$project->id}}" aria-controls="navs-pills-top-messages" aria-selected="false">Documents</button>
            </li>
            <li class="nav-item">
              <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-top-manage{{$project->id}}" aria-controls="navs-pills-top-messages" aria-selected="false">Tasks</button>
            </li>
            <li class="nav-item">
              <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-top-manage{{$project->id}}" aria-controls="navs-pills-top-messages" aria-selected="false">Dashboard</button>
            </li>
          </ul>
          <div class="tab-content">
            <div class="tab-pane fade show " id="navs-pills-top-home{{$project->id}}" role="tabpanel">
              <p>
              {{$project->description}}
              </p>

            </div>
            <div class="tab-pane fade show active" id="navs-pills-top-client{{$project->id}}" role="tabpanel">

              <ul class="t mb-2 pb-1">
                @foreach($project->clients as $key => $value)
                <li class="d-flex mb-3 pb-1 align-items-center">
                  <div class="badge bg-label-primary me-3 rounded p-2">
                    <i class="ti ti-wallet ti-sm"></i>
                  </div>
                  <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                    <div class="me-2">
                      <h6 class="mb-0">{{$value->client_name}}</h6>
                      <small class="text-muted d-block">{{$value->email}}</small>
                      <small class="text-muted d-block">{{$value->address}}</small>
                    </div>
                    <div class="user-progress d-flex align-items-center gap-1">
                      <h6 class="mb-0 text-danger">{{$value->phone}}</h6>
                    </div>
                  </div>
                </li>

              @endforeach
              </ul>
            </div>
            <div class="tab-pane fade" id="navs-pills-top-profile{{$project->id}}" role="tabpanel">
              <div class="card-header d-flex justify-content-between">
                <h5 class="card-title m-0 me-2 pt-1 mb-2">Project Leads</h5>

              </div>

              <ul class="timeline ms-1 mb-0">
                <li class="timeline-item timeline-item-transparent ps-4">
                  <span class="timeline-point timeline-point-primary"></span>
                  <div class="timeline-event">
                    <div class="timeline-header">
                      <h6 class="mb-0">Project Leads</h6>
                      <small class="text-muted">Leads</small>
                    </div>
                    <p class="mb-2">Project Lead Members</p>
                    @foreach($project->leads as $key => $value)
                    <div class="d-flex flex-wrap">
                      <div class="avatar me-2">
                        @if($value->profile_pic)
                        <img src="{{ asset('assets/img/avatars/'.$value->profile_pic) }}" alt="Avatar" class="rounded-circle" />
                        @else
                        @php

                      $initial = Helper::generateInitial($value->name);
                        @endphp
                        <span titile={{$value->name}} class="avatar-initial rounded-circle bg-label-success">{{$initial}}</span>
                        @endif
                      </div>
                      <div class="ms-1">
                        <h6 class="mb-0">{{$value->name}}</h6>
                        <span>{{$value->email}}</span>
                      </div>
                    </div>
                    @endforeach
                  </div>
                </li>
                <li class="timeline-item timeline-item-transparent ps-4">
                  <span class="timeline-point timeline-point-success"></span>
                  <div class="timeline-event">
                    <div class="timeline-header">
                      <h6 class="mb-0">Team Members</h6>
                      <small class="text-muted">Members</small>
                    </div>
                    <p class="mb-2">Project Team Members</p>
                    @foreach($project->members as $key => $value)
                    <div class="d-flex flex-wrap">
                      <div class="avatar me-2">
                        @if($value->profile_pic)
                        <img src="{{ asset('assets/img/avatars/'.$value->profile_pic) }}" alt="Avatar" class="rounded-circle" />
                        @else
                        @php

                      $initial = Helper::generateInitial($value->name);
                        @endphp
                        <span titile={{$value->name}} class="avatar-initial rounded-circle bg-label-success">{{$initial}}</span>
                        @endif
                      </div>
                      <div class="ms-1">
                        <h6 class="mb-0">{{$value->name}}</h6>
                        <span>{{$value->email}}</span>
                      </div>
                    </div>
                    @endforeach
                  </div>
                </li>
              </ul>




            </div>
            <div class="tab-pane fade" id="navs-pills-top-manage{{$project->id}}" role="tabpanel">

              <div class="col-lg-12 p-1">
                <small class="text-light fw-semibold">Project Employee Manage</small>
                <div class="demo-inline-spacing">
                  <a href="/project/employee/{{$project->id}}" class="btn btn-label-primary">
                    <span class="ti-xs ti ti-star me-1"></span>Employee
                  </a>
                  <button type="button" class="btn btn-label-danger">
                    <span class="ti-xs ti ti-bell me-1"></span>Attendance
                  </button>
                  <button type="button" class="btn btn-label-success">
                    <span class="ti-xs ti ti-bell me-1"></span>Payroll
                  </button>
                </div>
              </div>
              <div class="col-lg-12 p-1">
                <small class="text-light fw-semibold">Project Manage</small>
                <div class="demo-inline-spacing">
                  <button type="button" class="btn btn-label-dark">
                    <span class="ti-xs ti ti-star me-1"></span>Finance
                  </button>
                  <button type="button" class="btn btn-label-info">
                    <span class="ti-xs ti ti-star me-1"></span>Configurations
                  </button>

                    <a href="/project/docs/{{$project->id}}" class="btn btn-label-primary">
                    <span class="ti-xs ti ti-bell me-1"></span>Documentation</a>

                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md mb-4 pb-2 mb-md-2">
          {{--  <small class="text-light fw-semibold">About {{$project->project_name}} Project</small>  --}}
          <div class="accordion mt-3" id="accordionWithIcon">
            <div class="card accordion-item active">
              <h2 class="accordion-header d-flex align-items-center">
                <button type="button" class="accordion-button" data-bs-toggle="collapse" data-bs-target="#accordionWithIcon-{{$project->id}}" aria-expanded="true">
                  <i class="ti ti-star ti-xs me-2"></i>
                  About {{$project->project_name}} Project
                </button>
              </h2>

              <div id="accordionWithIcon-{{$project->id}}" class="accordion-collapse collapse ">
                <div class="accordion-body">
                  {{$project->description}}
                </div>
              </div>
            </div>

          </div>
        </div>
    </div>
</div>

  @endforeach



</div>
<!--/ Client cards -->

<!-- Add Client Modal -->
{{--  @include('_partials/_modals/modal-add-client')  --}}
@include('_partials/_modals/modal-add-project',['clients' => $clients,'projectTypes' => $projectTypes,'leads' => $leads,'members' => $members,])
<!-- / Add Client Modal -->
@endsection
