@extends('layouts/layoutMaster')

@section('title', 'Depreciation Report')

@section('vendor-style')
@endsection

@section('vendor-script')
@endsection

@section('page-script')
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Depreciation Report</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive text-nowrap">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Asset #</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Purchase Cost</th>
                        <th>Purchase Date</th>
                        <th>Useful Life (Yrs)</th>
                        <th>Current Value (Approx)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assets as $asset)
                    @php
                        // Straight Line Depreciation Calculation
                        $purchaseDate = \Carbon\Carbon::parse($asset->purchase_date);
                        $now = \Carbon\Carbon::now();
                        $ageYears = $purchaseDate->diffInYears($now);
                        $usefulLife = $asset->category->useful_life_years ?? 5; // Default 5 years
                        $salvageValue = 0; // Assuming 0 for now as it wasn't prominent in Category

                        $depreciationPerYear = ($asset->purchase_cost - $salvageValue) / $usefulLife;
                        $accumulatedDepreciation = $depreciationPerYear * $ageYears;
                        $currentValue = max(0, $asset->purchase_cost - $accumulatedDepreciation);
                    @endphp
                    <tr>
                        <td>{{ $asset->asset_number }}</td>
                        <td>{{ $asset->name }}</td>
                        <td>{{ $asset->category->name }}</td>
                        <td>{{ number_format($asset->purchase_cost, 2) }}</td>
                        <td>{{ $asset->purchase_date ? $asset->purchase_date->format('d M Y') : '-' }}</td>
                        <td>{{ $usefulLife }}</td>
                        <td>{{ number_format($currentValue, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No depreciable assets found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
