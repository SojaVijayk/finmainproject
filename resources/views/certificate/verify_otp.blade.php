@extends('layouts/layoutMaster')

@section('title', 'CMD Certificate - Verify OTP')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
@endsection

@section('page-style')
<link rel="stylesheet" href="{{asset('assets/vendor/css/pages/page-auth.css')}}">
<style>
  #overlay {
    position: fixed;
    top: 0;
    z-index: 100;
    width: 100%;
    height: 100%;
    display: none;
    background: rgba(0, 0, 0, 0.6);
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

  .success-container {
    text-align: center;
    margin-top: 60px;
    font-family: "Arial", sans-serif;
    opacity: 0;
    transform: translateY(40px);
    animation: fadeInUp 1s ease-out forwards;
  }

  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(40px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .certificate-buttons {
    opacity: 0;
    transform: translateY(40px);
    animation: slideUp 1s ease-out forwards;
    animation-delay: 0.8s;
  }

  @keyframes slideUp {
    from {
      opacity: 0;
      transform: translateY(40px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .certificate-buttons a {
    display: inline-block;
    padding: 10px 20px;
    border-radius: 6px;
    color: #fff;
    text-decoration: none;
    margin: 10px;
    transition: 0.3s;
  }

  .btn-view {
    background: #28a745;
  }

  .btn-download {
    background: #007bff;
  }
</style>
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
@endsection

@section('page-script')
<script>
  $(document).ready(function() {

  $(".thankyou").hide();

  // ✅ Auto-move focus between OTP inputs
  $('.auth-input').on('keyup', function(e) {
    let key = e.which || e.keyCode;
    if (this.value.length === this.maxLength && key !== 8) {
      $(this).next('.auth-input').focus();
    } else if (key === 8 && !this.value) {
      $(this).prev('.auth-input').focus();
    }

    // Update hidden OTP field
    let otp = '';
    $('.auth-input').each(function() {
      otp += $(this).val();
    });
    $('#otp').val(otp);
  });

  $('#generate').click(function(e) {
    e.preventDefault();
    $("#overlay").fadeIn(300);

    $.ajax({
      url: "{{ url('certificate-otp-verification') }}",
      type: 'POST',
      data: {
        email: $('#email').val(),
        otp: $('#otp').val(),
        _token: "{{ csrf_token() }}"
      },
      success: function(response) {
        $("#overlay").fadeOut(300);
        $(".main-block").fadeOut(400, function() {
          $(".thankyou").html(response.html).fadeIn(600);
        });

        // 🎉 Confetti
        const duration = 3000, end = Date.now() + duration;
        (function frame() {
          confetti({ particleCount: 6, spread: 70, origin: { y: 0.6 } });
          if (Date.now() < end) requestAnimationFrame(frame);
        })();

        Swal.fire({
          title: 'Verified',
          text: 'Your certificate is ready!',
          icon: 'success',
          timer: 2000,
          showConfirmButton: false
        });
      },
      error: function() {
        $("#overlay").fadeOut(300);
        Swal.fire({
          title: 'Invalid OTP',
          text: 'Please try again.',
          icon: 'error',
          customClass: { confirmButton: 'btn btn-success' }
        });
      }
    });
  });
});
</script>
@endsection

@section('content')
<div class="authentication-wrapper authentication-basic px-4">
  <div id="overlay">
    <div class="cv-spinner"><span class="spinner"></span></div>
  </div>

  <div class="authentication-inner py-4">
    <div class="thankyou"></div>

    <div class="card main-block">
      <div class="card-body text-center">
        <div class="app-brand justify-content-center mb-4 mt-">
          <img height="90" src="{{ asset('assets/img/branding/cmdlogo.png') }}">
        </div>

        <h4 class="mb-1">Two Step Verification 💬</h4>
        <p class="text-muted mb-4">
          We sent a 6-digit verification code to your email.<br>
          <strong>{{ substr($user->email,0,1) . str_repeat('*', strpos($user->email,'@')-1) .
            substr($user->email,strpos($user->email,'@')) }}</strong>
        </p>

        <form id="twoStepsForm">
          @csrf
          <div class="mb-3">
            <div class="d-flex justify-content-center">
              @for($i = 0; $i < 6; $i++) <input type="text" maxlength="1"
                class="form-control auth-input text-center mx-1" style="width:50px;">
                @endfor
            </div>
            <input type="hidden" id="otp" name="otp" />
            <input type="hidden" id="email" name="email" value="{{ $user->email }}" />
          </div>
          <button class="btn btn-primary w-100" id="generate">Verify & Continue</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection