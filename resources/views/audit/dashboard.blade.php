@extends('layouts.audit')

@section('content')
<div class="row">
    <div class="col-12 mb-4">
        <h2>Dashboard</h2>
        <p class="text-muted">Welcome to the Audit View. Select a module below to inspect records.</p>
    </div>
</div>

<div class="row">
    <!-- Tapals Card -->
    <div class="col-md-4">
        <a href="{{ route('audit.tapals.index') }}" class="text-decoration-none">
            <div class="audit-card text-center hover-shadow">
                <div class="mb-3">
                    <i class="menu-icon tf-icons ti ti-mail fs-1 text-primary"></i>
                </div>
                <h4>Tapals</h4>
                <p class="text-muted">View Inward/Outward correspondence and movement history.</p>
                <button class="btn btn-outline-primary btn-sm">View Tapals</button>
            </div>
        </a>
    </div>

    <!-- Documents Card -->
    <div class="col-md-4">
        <a href="{{ route('audit.documents.index') }}" class="text-decoration-none">
            <div class="audit-card text-center hover-shadow">
                 <div class="mb-3">
                    <i class="menu-icon tf-icons ti ti-file-text fs-1 text-success"></i>
                </div>
                <h4>Documents</h4>
                <p class="text-muted">Inspect generated document numbers and details.</p>
                <button class="btn btn-outline-success btn-sm">View Documents</button>
            </div>
        </a>
    </div>

    <!-- Projects Card -->
    <div class="col-md-4">
        <a href="{{ route('audit.projects.index') }}" class="text-decoration-none">
            <div class="audit-card text-center hover-shadow">
                 <div class="mb-3">
                    <i class="menu-icon tf-icons ti ti-briefcase fs-1 text-warning"></i>
                </div>
                <h4>Projects</h4>
                <p class="text-muted">Review project details, financials, and milestones.</p>
                <button class="btn btn-outline-warning btn-sm">View Projects</button>
            </div>
        </a>
    </div>
</div>
@endsection
