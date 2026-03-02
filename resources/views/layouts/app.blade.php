<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>PMS - @yield('title')</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- Custom CSS -->
  <link href="{{ asset('css/pms.css') }}" rel="stylesheet">

  @yield('styles')
</head>

<body>
  {{-- @include('partials.navbar') --}}

  <div class="container-fluid">
    <div class="row">
      {{-- @include('partials.sidebar') --}}

      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div
          class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
          <h1 class="h2">@yield('header')</h1>
          <div class="btn-toolbar mb-2 mb-md-0">
            @yield('actions')
          </div>
        </div>

        {{-- @include('partials.alerts') --}}

        @yield('content')
      </main>
    </div>
  </div>

  <!-- Bootstrap Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Custom JS -->
  <script src="{{ asset('js/pms.js') }}"></script>

  @yield('scripts')
</body>

</html>