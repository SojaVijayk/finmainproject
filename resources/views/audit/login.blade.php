@extends('layouts.audit')

@section('content')
<div class="d-flex justify-content-center align-items-center" style="height: 100vh; background-color: #f8f9fa;">
    <div class="audit-card" style="width: 400px; padding: 2rem;">
        <h3 class="text-center mb-4">Audit Login</h3>

        @if($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('audit.login.post') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Enter username" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter password" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>
        </form>

        <div class="text-center mt-3">
            <small class="text-muted">Restricted Access Area</small>
        </div>
    </div>
</div>
@endsection
