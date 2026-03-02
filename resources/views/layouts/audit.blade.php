<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit View - CMD</title>

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <!-- Page CSS -->
    @yield('page-style')

    <style>
        .audit-layout-wrapper {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }
        .audit-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e1e4e8;
        }
        .audit-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: #5d596c;
        }
        .audit-nav a {
            margin-right: 1.5rem;
            color: #6f6b7d;
            text-decoration: none;
            font-weight: 500;
        }
        .audit-nav a.active {
            color: #7367f0;
            border-bottom: 2px solid #7367f0;
        }
        .audit-nav a:hover {
            color: #7367f0;
        }
        .audit-card {
            background: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 0.25rem 1rem rgba(161, 172, 184, 0.45);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin-bottom: 1rem;
        }
        .status-badge {
            padding: 0.25em 0.6em;
            border-radius: 0.25rem;
            font-size: 85%;
            font-weight: 600;
        }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-completed { background-color: #d4edda; color: #155724; }
        .status-rejected { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body class="bg-light">

    @if(session('audit_logged_in'))
    <div class="audit-layout-wrapper">
        <header class="audit-header">
            <div class="audit-brand">
                CMD Audit View
            </div>
            <nav class="audit-nav">
                <a href="{{ route('audit.dashboard') }}" class="{{ request()->routeIs('audit.dashboard') ? 'active' : '' }}">Dashboard</a>
                <a href="{{ route('audit.tapals.index') }}" class="{{ request()->routeIs('audit.tapals*') ? 'active' : '' }}">Tapals</a>
                <a href="{{ route('audit.documents.index') }}" class="{{ request()->routeIs('audit.documents*') ? 'active' : '' }}">Documents</a>
                <a href="{{ route('audit.projects.index') }}" class="{{ request()->routeIs('audit.projects*') ? 'active' : '' }}">Projects</a>

                <form action="{{ route('audit.logout') }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger ms-3">Logout</button>
                </form>
            </nav>
        </header>

        <main>
            @yield('content')
        </main>

        <footer class="mt-5 text-center text-muted">
            <small>&copy; {{ date('Y') }} CMD. All rights reserved. Audit Access Only.</small>
        </footer>
    </div>
    @else
        @yield('content')
    @endif

    <!-- Core JSP -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    @yield('page-script')
</body>
</html>
