@extends('layouts/layoutMaster')

@section('title', 'Asset Reports')

@section('vendor-style')
@endsection

@section('vendor-script')
@endsection

@section('page-script')
@endsection

@section('content')
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="ti ti-file-analytics ti-xl mb-3 text-primary"></i>
                <h5 class="card-title">Asset Register</h5>
                <p class="card-text">Comprehensive list of all assets with their current status, location, and custodian.</p>
                <a href="{{ route('asset.reports.register') }}" class="btn btn-primary">View Report</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
         <div class="card h-100">
            <div class="card-body text-center">
                <i class="ti ti-chart-pie ti-xl mb-3 text-danger"></i>
                <h5 class="card-title">Depreciation Report</h5>
                <p class="card-text">Calculate depreciation for assets based on their Category useful life.</p>
                <a href="{{ route('asset.reports.depreciation') }}" class="btn btn-danger">View Report</a>
            </div>
        </div>
    </div>
</div>
@endsection
