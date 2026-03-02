@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Roles - Apps')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
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
  'use strict';
  {{--  document.querySelector('.edit-record').addEventListener('click', function () {
    new bootstrap.Modal(document.getElementById('donate-modal')).show();
});  --}}
  document.addEventListener('DOMContentLoaded', function (e) {
    (function () {
      // add role form validation
      FormValidation.formValidation(document.getElementById('addRoleForm'), {
        fields: {
          modalRoleName: {
            validators: {
              notEmpty: {
                message: 'Please enter role name'
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

(function () {
  // On edit role click, update text
  var roleEditList = document.querySelectorAll('.role-edit-modal'),
    roleAdd = document.querySelector('.add-new-role'),
    roleTitle = document.querySelector('.role-title'),
    roleSubmit = document.querySelector('.submit-role');

    $('#addRoleModal').on('hidden.bs.modal', function (e) {
      $(this)
        .find("input,textarea,select")
           .val('')
           .end()
        .find("input[type=checkbox], input[type=radio]")
           .prop("checked", "")
           .end();
    })

  roleAdd.onclick = function () {
    roleTitle.innerHTML = 'Add New Role'; // reset text
  };
  roleSubmit.onclick = function () {
    var type =   $("#submit_role").data('type');
    var role_id = $(this).data('id');
   var  modalRoleName =  $("#modalRoleName").val();
    var permissions = [];
    $('.permission-checkbox').each(function() {
      if ($(this).is(":checked")) {
          permissions.push($(this).data('id'));
      }
    })
    if(type=='edit'){
    $.ajax({
      data:  {
        name:modalRoleName,
        permission:permissions,
        "_token": "{{ csrf_token() }}",

    },
      url: `${baseUrl}app/roles/edit/${role_id}`,
      type: 'POST',

      success: function (status) {

          $('#addRoleModal').modal('hide');
        // sweetalert
        Swal.fire({
          icon: 'success',
          title: `Successfully ${status}!`,
          text: `Role ${status} Successfully.`,
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
   else if(type=='new'){
      $.ajax({
        data:  {
          name:modalRoleName,
          permission:permissions,
          "_token": "{{ csrf_token() }}",

      },
        url: `${baseUrl}app/roles/store`,
        type: 'POST',

        success: function (status) {

            $('#addRoleModal').modal('hide');
          // sweetalert
          Swal.fire({
            icon: 'success',
            title: `Successfully ${status}!`,
            text: `Role ${status} Successfully.`,
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
  if (roleEditList) {
    roleEditList.forEach(function (roleEditEl) {
      roleEditEl.onclick = function () {


        roleTitle.innerHTML = 'Edit Role'; // reset text
        {{--  $("#submit_role").data('type','edit');  --}}

        $("#submit_role").attr("data-type", 'edit');

        var role_id = $(this).data('id');
        $("#submit_role").attr('data-id',role_id);
        $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      })
      $.ajax({
      type: "GET",

      url: '/app/roles/edit/'+role_id,
      success: function (data) {
        console.log(data);
          $("#modalRoleName").val(data.role.name);
          $("#submit_role").data('id',data.role.id);
          var dataRolePermissions = data['rolePermissions'];
          console.log(dataRolePermissions);
            $('.permission-checkbox').each(function(){
             console.log($(this).data('id'));
              if(dataRolePermissions.some(item => item.permission_id === $(this).data('id'))){
              $(this).prop('checked', true);
             }
           })

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
<h4 class="fw-semibold mb-4">Roles List</h4>

<p class="mb-4">A role provided access to predefined menus and features so that depending on <br> assigned role an administrator can have access to what user needs.</p>
<!-- Role cards -->
<div class="row g-4">
  @foreach ($roles as $role)


  <div class="col-xl-4 col-lg-6 col-md-6">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between">
          <h6 class="fw-normal mb-2">Total {{$role->users_count}} users</h6>
          <ul class="list-unstyled d-flex align-items-center avatar-group mb-0">
            {{--  @foreach($role->users as $key => $value)

            {{$value->name}}
            <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" title="Vinnie Mostowy" class="avatar avatar-sm pull-up">
              <img class="rounded-circle" src="{{ asset('assets/img/avatars/5.png') }}" alt="Avatar">
            </li>
            @endforeach  --}}
          </ul>
        </div>
        <div class="d-flex justify-content-between align-items-end mt-1">
          <div class="role-heading">
            <h4 class="mb-1">{{$role->name}}</h4>
            <a href="javascript:;" data-bs-toggle="modal"  data-id={{$role->id}} data-bs-target="#addRoleModal" class="role-edit-modal edit-record"><span>Edit Role</span></a>
          </div>
          <a href="javascript:void(0);" class="text-muted"><i class="ti ti-copy ti-md"></i></a>
        </div>
        {{--  {{print_r($roles)}}  --}}
      </div>
    </div>
  </div>
  @endforeach


  <div class="col-xl-4 col-lg-6 col-md-6">
    <div class="card h-100">
      <div class="row h-100">
        <div class="col-sm-5">
          <div class="d-flex align-items-end h-100 justify-content-center mt-sm-0 mt-3">
            <img src="{{ asset('assets/img/illustrations/add-new-roles.png') }}" class="img-fluid mt-sm-4 mt-md-0" alt="add-new-roles" width="83">
          </div>
        </div>
        <div class="col-sm-7">
          <div class="card-body text-sm-end text-center ps-sm-0">
            <button data-bs-target="#addRoleModal" data-bs-toggle="modal" class="btn btn-primary mb-2 text-nowrap add-new-role">Add New Role</button>
            <p class="mb-0 mt-1">Add role, if it does not exist</p>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>
<!--/ Role cards -->

<!-- Add Role Modal -->
@include('_partials/_modals/modal-add-role',['permissions' => $permissions])
<!-- / Add Role Modal -->
@endsection
