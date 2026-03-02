@extends('layouts/layoutMaster')

@section('title', 'User View - Pages')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/animate-css/animate.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/bs-stepper/bs-stepper.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-select/bootstrap-select.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/dropzone/dropzone.css') }}" />

@endsection
@section('page-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-user-view.css') }}" />
@endsection

@section('vendor-script')
<script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/cleavejs/cleave.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/cleavejs/cleave-phone.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/bs-stepper/bs-stepper.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/bootstrap-select/bootstrap-select.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/dropzone/dropzone.js') }}"></script>
@endsection

@section('page-script')
{{-- <script src="{{asset('assets/js/modal-edit-user.js')}}"></script>
<script src="{{asset('assets/js/app-user-view.js')}}"></script>
<script src="{{asset('assets/js/app-user-view-account.js')}}"></script>
<script src="{{asset('assets/js/pages-account-settings-account.js')}}"></script> --}}
<script src="{{ asset('assets/js/form-wizard-icons.js') }}"></script>
{{-- <script src="{{ asset('assets/js/forms-file-upload.js') }}"></script> --}}
{{-- <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script> --}}
<script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>

<script>
  const formAuthentication = document.querySelector('#formAuthentication');
        $(".datepicker").datepicker({
            autoclose: true,
        });

        document.addEventListener('DOMContentLoaded', function(e) {

          $('#uploadImage').prop("disabled", true);
            (function() {
                // Form validation for Add new record
                if (formAuthentication) {
                    const fv = FormValidation.formValidation(formAuthentication, {
                        fields: {
                            password: {
                                validators: {
                                    notEmpty: {
                                        message: 'Please enter your password'
                                    },
                                    stringLength: {
                                        min: 6,
                                        message: 'Password must be more than 6 characters'
                                    }
                                }
                            },
                            'password_confirmation': {
                                validators: {
                                    notEmpty: {
                                        message: 'Please confirm password'
                                    },
                                    identical: {
                                        compare: function() {
                                            return formAuthentication.querySelector(
                                                '[name="password"]').value;
                                        },
                                        message: 'The password and its confirm are not the same'
                                    },
                                    stringLength: {
                                        min: 6,
                                        message: 'Password must be more than 6 characters'
                                    }
                                }
                            },

                        },
                        plugins: {
                            trigger: new FormValidation.plugins.Trigger(),
                            bootstrap5: new FormValidation.plugins.Bootstrap5({
                                eleValidClass: '',
                                rowSelector: '.mb-3'
                            }),
                            submitButton: new FormValidation.plugins.SubmitButton(),

                            defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
                            autoFocus: new FormValidation.plugins.AutoFocus()
                        },
                        init: instance => {
                            instance.on('plugins.message.placed', function(e) {
                                if (e.element.parentElement.classList.contains(
                                        'input-group')) {
                                    e.element.parentElement.insertAdjacentElement(
                                        'afterend', e.messageElement);
                                }
                            });
                        }
                    });
                }

                //  Two Steps Verification
                const numeralMask = document.querySelectorAll('.numeral-mask');

                // Verification masking
                if (numeralMask.length) {
                    numeralMask.forEach(e => {
                        new Cleave(e, {
                            numeral: true
                        });
                    });
                }
            })();
        });

        $(document).on('click', '#passwordChange', function(e) {
            e.preventDefault();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                type: "POST",
                data: {
                    password: $('#password').val(),
                    password_confirmation: $('#password_confirmation').val(),

                },

                url: '/user/employee/resetPasssword',
                success: function(data) {
                    Swal.fire({
                        icon: 'success',
                        title: `Successfully ${data}!`,
                        text: `Your password has been changed.`,
                        customClass: {
                            confirmButton: 'btn btn-success'
                        }
                    });

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

            // delete $.ajaxSettings.headers["X-CSRF-TOKEN"];
            $.ajax({
                url: "https://ifsc.razorpay.com/" + $("#ifsc").val(),
                type: "GET",
                success: function(data) {
                    $('#bank_name').val(data.BANK);
                    $('#branch').val(data.BRANCH);
                    $('#bank_address').html(data.ADDRESS + ' District -' + data.DISTRICT + ' City -' +
                        data.CITY + ' State -' + data.STATE);
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
                    account_number: $('#account_number').val(),
                    account_holder_name: $('#account_holder_name').val(),
                    ifsc: $('#ifsc').val(),
                    bank_name: $('#bank_name').val(),
                    bank_address: $('#bank_address').val(),
                    branch: $('#branch').val(),
                    languages: $('#languages').val(),
                    state: $('#state').val(),




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

        $(document).on('click', '.add-account', function(e) {

          var user_id = $(this).data('id');
          $("#submit-bank-account").attr('data-id', user_id);
          $('#addNewBankAccount').modal('show');
          e.preventDefault();

      });

      $(document).on('click', '#submit-bank-account', function(e) {

        var user_id = $(this).data('id');
        e.preventDefault();

        $.ajax({
            url: `${baseUrl}user/employee/addBankAccount/${user_id}`,
            type: 'POST',
            data: {

                account_number: $('#account_number').val(),
                account_holder_name: $('#account_holder_name').val(),
                ifsc: $('#ifsc').val(),
                bank_name: $('#bank_name').val(),
                bank_address: $('#bank_address').val(),
                branch: $('#branch').val(),
                "_token": "{{ csrf_token() }}",

            },
            success: function(status) {

                Swal.fire({
                    icon: 'success',
                    title: `Successfully ${status}!`,
                    text: `User Account ${status} Successfully.`,
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

    $(document).on('click', '.updateCard', function(e) {

      var user_id = $(this).data('id');
      var account_id = $(this).data('account');

      e.preventDefault();
      Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes,Update it!',
        customClass: {
          confirmButton: 'btn btn-primary me-1',
          cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
      }).then(function(result) {
        if (result.value) {
          $.ajax({
            url: `${baseUrl}user/employee/updateBankAccount`,
            type: 'POST',
            data: {
              user_id:user_id,
              account_id:account_id,
                "_token": "{{ csrf_token() }}",

            },
            success: function(status) {

                Swal.fire({
                    icon: 'success',
                    title: `Successfully ${status}!`,
                    text: `User Account ${status} Successfully.`,
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

  });

  $(document).on('click', '#uploadImage', function(e) {

    e.preventDefault();
    var fd = new FormData();
    var files = $('input[type=file]')[0].files[0];
    fd.append('image',files);
    fd.append('_token',"{{ csrf_token() }}");



    $.ajax({
        url: `${baseUrl}user/employee/uploadImage`,
        type: 'POST',
        data: fd,
        contentType: false,
        processData: false,

        success: function(status) {
          $('#uploadImage').prop("disabled", true);

            Swal.fire({
                icon: 'success',
                title: `Successfully ${status}!`,
                text: `User Account ${status} Successfully.`,
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

upload.onchange = evt => {
  $('#uploadImage').prop("disabled", false);
  preview = document.getElementById('uploadedAvatar');
  preview.style.display = 'block';
  const [file] = upload.files
  if (file) {
      preview.src = URL.createObjectURL(file)
  }
}

</script>
@endsection

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">User / View /</span> Account
</h4>
<div class="row">
  <!-- User Sidebar -->
  <div class="col-xl-4 col-lg-5 col-md-5 order-1 order-md-0">
    <!-- User Card -->
    <div class="card mb-4">
      <div class="card-body">
        <form id="attendanceImport" enctype="multipart/form-data">
          <div class="d-flex align-items-start align-items-sm-center gap-4">
            <img src="{{ asset('assets/img/avatars/' . $employee->profile_pic) }}" alt="user-avatar"
              class="d-block w-px-100 h-px-100 rounded" id="uploadedAvatar" />
            <div class="button-wrapper">
              <label for="upload" class="btn btn-primary me-2 mb-3" tabindex="0">
                <span class="d-none d-sm-block">Change</span>
                <i class="ti ti-upload d-block d-sm-none"></i>
                <input type="file" id="upload" class="account-file-input" hidden accept="image/png, image/jpeg" />
              </label>
              <button type="button" class="btn btn-label-success account-image-reset mb-3" id="uploadImage">
                <i class="ti ti-refresh-dot d-block d-sm-none"></i>
                <span class="d-none d-sm-block">Save</span>
              </button>


              <div class="text-muted"><small>Allowed JPG, GIF or PNG. Max size of 800K</small></div>
            </div>

          </div>
        </form>
        <div class="user-avatar-section">
          <div class=" d-flex align-items-center flex-column">
            {{-- <img class="img-fluid rounded mb-3 pt-1 mt-4" src="{{ asset('assets/img/avatars/15.png') }}"
              height="100" width="100" alt="User avatar" /> --}}
            <div class="user-info text-center">
              <h4 class="mb-2">{{ $employee->name }}</h4>
              <span class="badge bg-label-secondary mt-1">{{ $employee->designation }}</span><br>
              <span class="badge bg-label-success mt-1">{{ $employee->empId }}</span>
            </div>
          </div>
        </div>

        <div class="d-flex justify-content-around flex-wrap mt-3 pt-3 pb-4 border-bottom">
          {{-- <div class="d-flex align-items-start me-4 mt-3 gap-2">
            <span class="badge bg-label-primary p-2 rounded"><i class='ti ti-checkbox ti-sm'></i></span>
            <div>
              <p class="mb-0 fw-semibold">{{ $employee_projects->lead_projects_count }}</p>
              <small>Lead Project</small>
            </div>
          </div> --}}
          {{-- <div class="d-flex align-items-start mt-3 gap-2">
            <span class="badge bg-label-primary p-2 rounded"><i class='ti ti-briefcase ti-sm'></i></span>
            <div>
              <p class="mb-0 fw-semibold">{{ $employee_projects->member_projects_count }}</p>
              <small>Member Projects</small>
            </div>
          </div> --}}
        </div>
        <p class="mt-4 small text-uppercase text-muted">Details</p>
        <div class="info-container">
          <ul class="list-unstyled">
            <li class="mb-2">
              <span class="fw-semibold me-1">Username:</span>
              <span>{{ $employee->email }}</span>
            </li>
            <li class="mb-2 pt-1">
              <span class="fw-semibold me-1">Email:</span>
              <span>{{ $employee->name }}</span>
            </li>
            <li class="mb-2 pt-1">
              <span class="fw-semibold me-1">Status:</span>
              <span class="badge bg-label-success">{{ $employee->status == 1 ? 'Active' : 'Inactive' }}</span>
            </li>
            <li class="mb-2 pt-1">
              <span class="fw-semibold me-1">Role:</span>

              @foreach ($employeeRoles['roles'] as $role)
              <span class="badge bg-label-primary ">{{ $role->name }}</span>
              @endforeach

            </li>
            <li class="mb-2 pt-1">
              <span class="fw-semibold me-1">PAN:</span>
              <span>{{ $employee->pan }}</span>
            </li>
            <li class="mb-2 pt-1">
              <span class="fw-semibold me-1">Contact:</span>
              <span>{{ $employee->mobile }}</span>
            </li>
            <li class="mb-2 pt-1">
              <span class="fw-semibold me-1">Languages:</span>
              <span>English</span>
            </li>
            <li class="pt-1">
              <span class="fw-semibold me-1">Country:</span>
              <span>India</span>
            </li>
          </ul>
          <div class="d-flex justify-content-center">
            <button class="btn btn-primary me-3 edit-user" id="edit-user"
              data-id="{{ $employee->employee_id }}">Edit</button>
            {{-- <a href="javascript:;" class="btn btn-label-danger suspend-user">Suspended</a> --}}
          </div>
        </div>
      </div>
    </div>
    <!-- /User Card -->

  </div>
  <!--/ User Sidebar -->


  <!-- User Content -->
  <div class="col-xl-8 col-lg-7 col-md-7 order-0 order-md-1">


    <div class="nav-align-top">
      <ul class="nav nav-pills flex-column flex-md-row mb-4" role="tablist">
        <li class="nav-item"><a class="nav-link active" role="tab" data-bs-toggle="tab"
            data-bs-target="#navs-pills-top-security" aria-controls="navs-pills-top-security" aria-selected="true"><i
              class="ti ti-lock ti-xs me-1"></i>Security</a></li>

        <li class="nav-item"><a class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-top-bank"
            aria-controls="navs-pills-top-bank" aria-selected="false"><i
              class="ti ti-currency-dollar ti-xs me-1"></i>Bank</a></li>
        <li class="nav-item"><a class="nav-link" role="tab" data-bs-toggle="tab"
            data-bs-target="#navs-pills-top-notification" aria-controls="navs-pills-top-notification"
            aria-selected="false"><i class="ti ti-bell ti-xs me-1"></i>Notifications</a></li>
        <li class="nav-item"><a class="nav-link" role="tab" data-bs-toggle="tab"
            data-bs-target="#navs-pills-top-documents" aria-controls="navs-pills-top-documents" aria-selected="false"><i
              class="ti ti-link ti-xs me-1"></i>Documents</a></li>
      </ul>

      <div class="tab-content">
        <div class="tab-pane fade show active" id="navs-pills-top-security" role="tabpanel">

          <!-- Change Password -->
          <div class="card mb-4">
            <h5 class="card-header">Change Password</h5>
            <div class="card-body">
              <form id="formAuthentication" method="POST">

                <div class="alert alert-warning" role="alert">
                  <h5 class="alert-heading mb-2">Ensure that these requirements are met</h5>
                  <span>Minimum 8 characters long, uppercase & symbol</span>
                </div>
                <div class="row">
                  <div class="mb-3 col-12 col-sm-6 form-password-toggle">
                    <label class="form-label" for="password">New Password</label>
                    <div class="input-group input-group-merge">
                      <input class="form-control" type="password" id="password" name="password"
                        placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                      <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                    </div>
                  </div>

                  <div class="mb-3 col-12 col-sm-6 form-password-toggle">
                    <label class="form-label" for="password_confirmation">Confirm New Password</label>
                    <div class="input-group input-group-merge">
                      <input class="form-control" type="password" name="password_confirmation"
                        id="password_confirmation"
                        placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                      <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                    </div>
                  </div>
                  <div>
                    <button id="passwordChange" class="btn btn-primary me-2">Change Password</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
          <!--/ Change Password -->

        </div>
        <div class="tab-pane fade  " id="navs-pills-top-bank" role="tabpanel">


          <!-- Payment Methods -->
          <div class="card card-action mb-4">
            <div class="card-header align-items-center">
              <h5 class="card-action-title mb-0">Bank Account</h5>
              <div class="card-action-element">
                <button class="btn btn-primary btn-sm add-account" type="button" data-id="{{$employee->id}}">
                  <i class="ti ti-plus ti-xs me-1"></i>Add Account</button>
              </div>
            </div>



            <div class="card-body">
              @foreach ($employeeAccounts as $item)
              <div class="added-cards">
                <div class="cardMaster border p-3 rounded mb-3">
                  <div class="d-flex justify-content-between flex-sm-row flex-column">
                    <div class="card-information">
                      <img class="mb-3 img-fluid" src="{{ asset('assets/img/icons/payments/bank.png') }}"
                        alt="Master Card">
                      <h6 class="mb-2 pt-1">{{ $item->account_holder_name }}</h6>
                      <span class="card-number">&#8727;&#8727;&#8727;&#8727; &#8727;&#8727;&#8727;&#8727;
                        &#8727;&#8727;&#8727;&#8727;{{ $item->account_number }} </span><br>
                      <small class="text-sm">{{ $item->bank_name }}</small><br>
                      <small>{{$item->ifsc}}</small><br>
                      <small>{{$item->branch}}</small>
                    </div>
                    <div class="d-flex flex-column text-start text-lg-end">
                      <div class="d-flex order-sm-0 order-1 mt-3">
                        {{-- <button disabled class="btn btn-label-dark me-3">{{$item->ifsc}}</button> --}}
                        @if($item->status == 1 && $item->status == 1 )
                        <button disabled class="btn btn-label-success  me-3">Active</button>
                        @else
                        <button data-id="{{$employee->id}}" data-account="{{$item->id}}"
                          class="btn btn-label-primary me-3 updateCard">Set As Primary</button>
                        @endif

                      </div>
                      <small class="mt-sm-auto mt-2 order-sm-1 order-0"></small>
                    </div>
                  </div>
                </div>
              </div>
              @endforeach
            </div>

          </div>
          <!--/ Payment Methods -->

        </div>
        <div class="tab-pane fade  " id="navs-pills-top-notification" role="tabpanel">

          <dotlottie-player style="align-items: center; height: 200px;" src='{{config(' variables.coming-soon')}}'
            background="transparent" speed="1" loop autoplay></dotlottie-player>

        </div>
        <div class="tab-pane fade  " id="navs-pills-top-documents" role="tabpanel">
          <dotlottie-player style="align-items: center;  height: 200px;" src='{{config(' variables.coming-soon')}}'
            background="transparent" speed="1" loop autoplay></dotlottie-player>

        </div>

      </div>
    </div>











  </div>
  <!--/ User Content -->
</div>
<div id="userInfoModal"></div>

<!-- Modal -->
@include('_partials/_modals/modal-edit-user')
@include('_partials/_modals/modal-add-new-bankaccount')
<!-- /Modal -->
@endsection