@php
$customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/layoutMaster')

@section('title', 'CMD LMS Certificate - Two Steps Verifications')

@section('vendor-style')
<!-- Vendor -->
<link rel="stylesheet" href="{{asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
@endsection

@section('page-style')
<!-- Page -->
<link rel="stylesheet" href="{{asset('assets/vendor/css/pages/page-auth.css')}}">
<style>
  #overlay{
    position: fixed;
    top: 0;
    z-index: 100;
    width: 100%;
    height:100%;
    display: none;
    background: rgba(0,0,0,0.6);
  }
  .cv-spinner {
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
  }
  .spinner {
    width: 40px;
    height: 40px;
    border: 4px #ddd solid;
    border-top: 4px #2e93e6 solid;
    border-radius: 50%;
    animation: sp-anime 0.8s infinite linear;
  }
  @keyframes sp-anime {
    100% {
      transform: rotate(360deg);
    }
  }
  .is-hide{
    display:none;
  }
</style>
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/cleavejs/cleave.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('assets/js/pages-auth.js')}}"></script>
<script src="{{asset('assets/js/pages-auth-two-steps.js')}}"></script>

<script>
  $(document).ready(function() {
    $(".thankyou").hide();
    $('#generate').click(function(e) {
e.preventDefault();
$("#overlay").fadeIn(300);　
  $.ajax({
    data: {
        email: $('#email').val(),
        otp: $('#otp').val(),


        "_token": "{{ csrf_token() }}",
    },
    url: `${baseUrl}otp-verification`,
    type: 'POST',
    xhrFields: {
        responseType: 'blob'
    },
    beforeSend: function() {
        //

    },
    success: function(data) {
        var url = window.URL || window.webkitURL;
        var objectUrl = url.createObjectURL(data);
        var a = document.createElement("a");
        a.href = objectUrl;
        a.download = "certificate-SSK-LTP.pdf";
        document.body.appendChild(a);
        a.click();
        setTimeout(function(){
          $("#overlay").fadeOut(300);
        },500);
        $(".main-block").hide();
        $(".thankyou").show();
        {{--  window.open(objectUrl);  --}}
    },
    error: function(data) {
        {{--  $("#overlay").hide();  --}}
      setTimeout(function(){
        $("#overlay").fadeOut(300);
      },500);
      {{--  console.log(data);  --}}
      Swal.fire({
        title: 'Oh Sorry!',
        text: "Invalid OTP",
        icon: 'error',
        customClass: {
          confirmButton: 'btn btn-success'
        }
      });
        //
    }
});
});
});
</script>
@endsection

@section('content')
<div class="authentication-wrapper authentication-basic px-4">
  <div id="overlay">
    <div class="cv-spinner">
      <span class="spinner"></span>
    </div>
  </div>
  <div class="authentication-inner py-4">
    <!--  Two Steps Verification -->
    <div class="thankyou">
      <div class="card ">
        <div class="card-body">
          Congratulations! Your certificate has been successfully generated.
        </div>
      </div>
    </div>
    <div class="card main-block">

      <div class="card-body">
        <!-- Logo -->
        <div class="app-brand justify-content-center mb-4 mt-2">
          {{--  <a href="{{url('/')}}" class="app-brand-link gap-2">
            <span class="app-brand-logo demo">@include('_partials.macros',['height'=>20,'withbg' => "fill: #fff;"])</span>
            <span class="app-brand-text demo text-body fw-bold ms-1">{{ config('variables.templateName') }}</span>
          </a>  --}}
          <a href="{{url('/')}}" class="app-brand-link gap-2">
              <img height="100" width="100" src="{{ asset('assets/img/branding/moodle-lms-logo.png') }}"></img>
          </a>


        </div>
        <p class="app-brand-text demo text-body fw-bold ms-1 text-center">
          CMD Learning Management System
         </p>
        <!-- /Logo -->
        <h4 class="mb-1 pt-2 text-center">Two Step Verification 💬</h4>
        <p class="text-start mb-4 text-center">
          We sent a verification code to your Email. Enter the code from the mail in the field below.
          @php
          function maskEmail($email)
          {
              $email_parts = explode('@', $email);
              $name_part = $email_parts[0];
              $domain_part = '@' . $email_parts[1];

              return substr($name_part, 0, 1) . str_repeat('*', strlen($name_part) - 1) . $domain_part;
          }
          @endphp
          <span class="fw-bold d-block mt-2">{{maskEmail($user->email)}}</span>
        </p>
        <p class="mb-0 fw-semibold">Type your 6 digit security code</p>
        {{--  <form id="twoStepsForm" action="{{route('lms-otp-verify')}}" method="POST">  --}}
          <form id="twoStepsForm"  method="POST">
          {!! csrf_field() !!}
          <div class="mb-3">
            <div class="auth-input-wrapper d-flex align-items-center justify-content-sm-between numeral-mask-wrapper">
              <input type="text" class="form-control auth-input h-px-50 text-center numeral-mask text-center h-px-50 mx-1 my-2" maxlength="1" autofocus>
              <input type="text" class="form-control auth-input h-px-50 text-center numeral-mask text-center h-px-50 mx-1 my-2" maxlength="1">
              <input type="text" class="form-control auth-input h-px-50 text-center numeral-mask text-center h-px-50 mx-1 my-2" maxlength="1">
              <input type="text" class="form-control auth-input h-px-50 text-center numeral-mask text-center h-px-50 mx-1 my-2" maxlength="1">
              <input type="text" class="form-control auth-input h-px-50 text-center numeral-mask text-center h-px-50 mx-1 my-2" maxlength="1">
              <input type="text" class="form-control auth-input h-px-50 text-center numeral-mask text-center h-px-50 mx-1 my-2" maxlength="1">
            </div>
            <!-- Create a hidden field which is combined by 3 fields above -->
            <input type="hidden" id="otp" name="otp" />
            <input type="hidden" id="email" name="email" value="{{$user->email}}" />
          </div>
          <button class="btn btn-primary d-grid w-100 mb-3" id="generate">
            Generate Certificate
          </button>
          {{--  <div class="text-center">Didn't get the code?
            <a href="javascript:void(0);">
              Resend
            </a>
          </div>  --}}
        </form>
      </div>
    </div>
    <!-- / Two Steps Verification -->
  </div>
</div>
@endsection
