@extends('layouts/layoutMaster')

@section('title', 'Blank layout - Layouts')
@section('page-script')
<script src="{{asset('assets/js/pages-auth.js')}}"></script>
<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
<script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>
@endsection

@section('content')

{{--  <lottie-player style="align-items: center; width: 800px; height: 400px;"
src='{{config('variables.login3')}}' background="transparent"
speed="1" style="width: 800px; height: 400px;" loop autoplay></lottie-player>  --}}
<div class="row">
  <!-- Website Analytics -->
  <div class="col-lg-12 mb-4">
    <dotlottie-player style=" " src="https://lottie.host/560f4f0c-e80e-4e77-8793-40f896753469/Y14w0uZqv9.json" background="transparent" speed="1" style="width: 300px; height: 300px;" loop autoplay></dotlottie-player>

  </div>
</div>


@endsection
