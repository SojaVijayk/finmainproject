@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Reset Password Cover - Pages')

@section('vendor-style')
<!-- Vendor -->
<link rel="stylesheet" href="{{asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
@endsection

@section('page-style')
<!-- Page -->
<link rel="stylesheet" href="{{asset('assets/vendor/css/pages/page-auth.css')}}">
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('assets/js/pages-auth.js')}}"></script>
<script>
  $(document).on('click', '#passwordChange', function (e) {
    e.preventDefault();
    $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });
    $.ajax({
    type: "POST",
    data:{
      email : $('#email').val(),
      token:$('#token').val(),
      password : $('#password').val(),
      password_confirmation : $('#password_confirmation').val(),

    },

    url: '/reset-password',
    success: function (data) {
      console.log(data);
      if(data.status === 'true'){
        Swal.fire({
          icon: 'success',
          title: `Successfully Updated.!`,
          text: data.message,
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
      else{
        Swal.fire({
          icon: 'info',
          title: `error!`,
          text: data.message,
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }


    },
    error: function(data){
      Swal.fire({
        icon: 'info',
        title: `error!`,
        text: data.message,
        customClass: {
          confirmButton: 'btn btn-success'
        }
      });

    }
    });
  });
</script>
@endsection

@section('content')
<div class="authentication-wrapper authentication-cover authentication-bg ">
  <div class="authentication-inner row">

    <!-- /Left Text -->
    <div class="d-none d-lg-flex col-lg-7 p-0">
      <div class="auth-cover-bg auth-cover-bg-color d-flex justify-content-center align-items-center">
        <img src="{{ asset('assets/img/illustrations/auth-reset-password-illustration-'.$configData['style'].'.png') }}" alt="auth-reset-password-cover" class="img-fluid my-5 auth-illustration" data-app-light-img="illustrations/auth-reset-password-illustration-light.png" data-app-dark-img="illustrations/auth-reset-password-illustration-dark.png">

        <img src="{{ asset('assets/img/illustrations/bg-shape-image-'.$configData['style'].'.png') }}" alt="auth-reset-password-cover" class="platform-bg" data-app-light-img="illustrations/bg-shape-image-light.png" data-app-dark-img="illustrations/bg-shape-image-dark.png">
      </div>
    </div>
    <!-- /Left Text -->

    <!-- Reset Password -->
    <div class="d-flex col-12 col-lg-5 align-items-center p-4 p-sm-5">
      <div class="w-px-400 mx-auto">
        <!-- Logo -->
        <div class="app-brand mb-4">
          <a href="{{url('/')}}" class="app-brand-link gap-2">
            <span class="app-brand-logo demo">@include('_partials.macros',['height'=>20,'withbg' => "fill: #fff;"])</span>
          </a>
        </div>
        <!-- /Logo -->
        <h3 class="mb-1 fw-bold">Reset Password 🔒</h3>
        {{--  <p class="mb-4">for <span class="fw-bold">john.doe@email.com</span></p>  --}}
        <form id="formAuthentication" class="mb-3" action="{{ route('reset.password.post') }}" method="POST">

         <input type="hidden" id="token" name="token" value="{{ $token }}">


         <div class="mb-3 form-email-toggle">
          <label class="form-label" for="email">Email</label>
          <div class="input-group input-group-merge">
            <input id="email" class="form-control" name="email" placeholder="" aria-describedby="email" />


          </div>
        </div>

          <div class="mb-3 form-password-toggle">
            <label class="form-label" for="password">New Password</label>
            <div class="input-group input-group-merge">
              <input type="password" id="password" class="form-control" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password" />
              <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>


            </div>
          </div>
          <div class="mb-3 form-password-toggle">
            <label class="form-label" for="password_confirmation">Confirm Password</label>
            <div class="input-group input-group-merge">
              <input type="password" id="password_confirmation" class="form-control" name="password_confirmation" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password" />
              <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>

            </div>
          </div>
          <button id="passwordChange" class="btn btn-primary d-grid w-100 mb-3">
            Set new password
          </button>
          <div class="text-center">
            <a href="{{url('auth/login-cover')}}">
              <i class="ti ti-chevron-left scaleX-n1-rtl"></i>
              Back to login
            </a>
          </div>
        </form>
      </div>
    </div>
    <!-- /Reset Password -->
  </div>
</div>
@endsection
