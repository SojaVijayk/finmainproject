@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Login')

@section('vendor-style')
<!-- Vendor -->
<link rel="stylesheet" href="{{asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css')}}" />
@endsection

@section('page-style')
<!-- Page -->
<link rel="stylesheet" href="{{asset('assets/vendor/css/pages/page-auth.css')}}">
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('assets/js/pages-auth.js')}}"></script>
<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
@endsection

@section('content')
<div class="authentication-wrapper authentication-cover authentication-bg">
    <div class="authentication-inner row">
        <!-- /Left Text -->
        <div class="d-none d-lg-flex col-lg-7 p-0">
            <div class="auth-cover-bg auth-cover-bg-color d-flex justify-content-center align-items-center">

                {{--  <div class="auth-cover-bg  d-flex justify-content-center align-items-center" style="background-image: url(/assets/img/backgrounds/bg2.jpg)">  --}}
                {{--  <img src="{{ asset('assets/img/illustrations/auth-login-illustration-'.$configData['style'].'.png') }}"
                alt="auth-login-cover" class="img-fluid my-5 auth-illustration"
                data-app-light-img="illustrations/auth-login-illustration-light.png"
                data-app-dark-img="illustrations/auth-login-illustration-dark.png"> --}}
                {{--  <img src="{{ asset('assets/img/illustrations/loginbg.jpg') }}" alt="auth-login-cover"
                class="img-fluid my-5 auth-illustration" data-app-light-img="illustrations/loginbg.jpg"
                data-app-dark-img="illustrations/auth-login-illustration-dark.png"> --}}
                <lottie-player style="align-items: center; width: 800px; height: 400px;"
                    src='{{config('variables.login3')}}' background="transparent" speed="1"
                    style="width: 800px; height: 400px;" loop autoplay></lottie-player>
                {{--  <h2>Centre for Management Development</h2>  --}}
                <img src="{{ asset('assets/img/illustrations/bg-shape-image-'.$configData['style'].'.png') }}"
                    alt="auth-login-cover" class="platform-bg"
                    data-app-light-img="illustrations/bg-shape-image-light.png"
                    data-app-dark-img="illustrations/bg-shape-image-dark.png">
            </div>
        </div>
        <!-- /Left Text -->

        <!-- Login -->
        <div class="d-flex col-12 col-lg-5 align-items-center p-sm-5 p-4">
            <div class="w-px-400 mx-auto">
                <!-- Logo -->
                <div class="app-brand mb-4">
                    <a href="{{url('/')}}" class="app-brand-link gap-2">
                        {{--  <span class="app-brand-logo demo">@include('_partials.macros',["height"=>20,"withbg"=>'fill: #fff;'])</span>  --}}
                        {{--  <span class="app-brand-logo demo">
              <svg height="10" >
                <img src="{{ asset('assets/img/branding/logo4.png') }}"></img>

                        </svg>
                        </span> --}}
                        <img height="100" width="100" src="{{ asset('assets/img/branding/logo.png') }}"></img>
                        {{--  <span class="app-brand-logo demo">  <img src="{{ asset('assets/img/illustrations/bg-shape-image-'.$configData['style'].'.png') }}"
                        alt="auth-login-cover" class="platform-bg"
                        data-app-light-img="illustrations/bg-shape-image-light.png"
                        data-app-dark-img="illustrations/bg-shape-image-dark.png">
                        </span> --}}

                    </a>
                    <h3 class=" mb-1 fw-bold">Welcome to {{config('variables.templateName')}}!


                </div>
                <!-- /Logo -->
                </h3>
                <h4 class=" pt-1 fw-bold text-primary">Streamline. Simplify. Succeed:
                    {{--  <span class="text-primary">Simplify,</span>
          <span class="text-primary">Automate, </span>
          <span class="text-primary">and Thrive</span>  --}}
                </h4>

                <p class="mb-4">Please sign-in to your account to start </p>


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

                <form id="formAuthentication" class="mb-3" action="{{route('login.custom')}}" method="POST">
                    {!! csrf_field() !!}
                    <div class="mb-3">
                        <label for="email" class="form-label">Email or Username</label>
                        <input type="text" class="form-control" id="email" name="email"
                            placeholder="Enter your email or username" autofocus>
                    </div>
                    <div class="mb-3 form-password-toggle">
                        <div class="d-flex justify-content-between">
                            <label class="form-label" for="password">Password</label>
                            <a href="{{url('forget-password')}}">
                                <small>Forgot Password?</small>
                            </a>
                        </div>
                        <div class="input-group input-group-merge">
                            <input type="password" id="password" class="form-control" name="password"
                                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                aria-describedby="password" />
                            <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember-me">
                            <label class="form-check-label" for="remember-me">
                                Remember Me
                            </label>
                        </div>
                    </div>
                    <button class="btn btn-primary d-grid w-100">
                        Sign in
                    </button>
                </form>

                <p class="text-center">
                    <span>Centre for Management Development</span>
                    {{--  <a href="{{url('auth/register-cover')}}">
                    <span>Create an account</span>
                    </a> --}}
                </p>

                <div class="divider my-4">
                    <div class="divider-text">Follow us</div>
                </div>

                <div class="d-flex justify-content-center">
                    <a href="https://www.facebook.com/cmdkerala/" class="btn btn-icon btn-label-facebook me-3">
                        <i class="tf-icons fa-brands fa-facebook-f fs-5"></i>
                    </a>

                    <a href="https://www.youtube.com/@cmdkerala/" class="btn btn-icon btn-label-google-plus me-3">
                        <i class="tf-icons fa-brands fa-youtube fs-5"></i>
                    </a>

                    {{--  <a href="javascript:;" class="btn btn-icon btn-label-twitter">
            <i class="tf-icons fa-brands fa-twitter fs-5"></i>
          </a>  --}}
                    <a href="https://www.linkedin.com/company/cmdkerala/" class="btn btn-icon btn-label-linkedin me-3">
                        <i class="tf-icons fa-brands fa-linkedin fs-5"></i>
                    </a>
                    <a href="https://www.instagram.com/cmdkerala/" class="btn btn-icon btn-label-instagram">
                        <i class="tf-icons fa-brands fa-instagram fs-5"></i>
                    </a>
                </div>
            </div>
        </div>
        <!-- /Login -->
    </div>
</div>
@endsection