@php
$customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/layoutMaster')

@section('title', 'CMD Certificate - Verify Email')

@section('vendor-style')
<!-- Vendor -->
<link rel="stylesheet" href="{{asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css')}}" />
@endsection

@section('page-style')
<!-- Page -->
<link rel="stylesheet" href="{{asset('assets/vendor/css/pages/page-auth.css')}}">
<style>
  .loading-overlay {
    display: none;
    background: rgba(255, 255, 255, 0.7);
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    top: 0;
    z-index: 9998;
    align-items: center;
    justify-content: center;
  }

  .loading-overlay.is-active {
    display: flex;
  }

  .code {
    font-family: monospace;
    /*   font-size: .9em; */
    color: #dd4a68;
    background-color: rgb(238, 238, 238);
    padding: 0 3px;
  }
</style>
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js')}}"></script>
@endsection

@section('page-script')
{{-- <script src="{{asset('assets/js/pages-auth.js')}}"></script> --}}
<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
<script>
  let overlay = document.getElementsByClassName('loading-overlay')[0]

//overlay.addEventListener('click', e => overlay.classList.toggle('is-active'))

document.getElementById('load-button')
  .addEventListener('click', e => overlay.classList.toggle('is-active'))
</script>
@endsection

@section('content')
<div class="authentication-wrapper authentication-basic px-4">
  <div class="authentication-inner py-4">
    <!-- Verify Email -->
    <div class="card">
      <div class="loading-overlay">
        <span class="fas fa-spinner fa-3x fa-spin"></span>
      </div>
      <div class="card-body">
        <!-- Logo -->
        <div class="app-brand justify-content-center mb-4 mt-2">
          <a href="{{url('/')}}" class="app-brand-link gap-2">
            {{-- <span class="app-brand-logo demo"> --}}
              {{-- @include('_partials.macros',['height'=>20,'withbg' => "fill: #fff;"]) --}}
              <img height="100" width="100" src="{{ asset('assets/img/branding/cmdlogo.png') }}"></img>
              {{-- </span> --}}

          </a>

        </div>
        <p class="app-brand-text demo text-body fw-bold ms-1 text-center">
          Centre for Management Development
        </p>
        <!-- /Logo -->
        {{-- <h4 class="mb-1 pt-2">Verify your email ✉️</h4>
        <p class="text-start mb-4">
          Account activation link sent to your email address: hello@example.com Please follow the link inside to
          continue.
        </p>
        --}}
        <p class="text-start text-center mb-4">
          Please enter your email ID and click the 'Generate OTP' button to receive your one-time password and access
          your certificate.
        </p>
        @if ($errors->has('email'))
        <div class="alert alert-danger flush">
          <strong>{{ $errors->first('email') }}</strong>
        </div>
        @endif
        @if ($errors->has('username'))
        <div class="alert alert-danger flush">
          <strong>{{ $errors->first('username') }}</strong>
        </div>
        @endif
        @include('_partials._error')
        @include('_partials._success')
        <form id="formAuthentication" class="mb-3" action="{{route('request.certificate.otp')}}" method="POST">
          {!! csrf_field() !!}
          <div class="mb-3">
            {{-- <label for="email" class="form-label">Email or Username</label> --}}
            <input type="text" class="form-control" id="email" name="email" placeholder="Enter your email " autofocus>
          </div>
          {{-- <div class="mb-3">
            <label for="email" class="form-label">Email or Username</label>
            <input type="text" class="form-control" id="mobile" name="mobile" placeholder="Enter your email " autofocus>
          </div> --}}
          <button class="btn btn-primary w-100 mb-3 generate-otp " id="load-button">
            Generate OTP
          </button>
        </form>

        <p class="text-center">
          <span>Centre for Management Development</span>

        </p>
      </div>
    </div>
    <!-- /Verify Email -->
  </div>
</div>
@endsection