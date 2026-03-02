@extends('layouts/layoutMaster')

@section('title', 'My Assets')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">My Allocated Assets</h5>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Asset #</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Allocated Date</th>
                    <th>Return Due</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assets as $asset)
                <tr>
                    <td>
                        <a href="{{ route('asset.masters.show', $asset->id) }}">
                            <strong>{{ $asset->asset_number }}</strong>
                        </a>
                    </td>
                    <td>{{ $asset->name }}</td>
                    <td>{{ $asset->category->name ?? '-' }}</td>
                    <td>
                        {{ $asset->currentAllocation ? $asset->currentAllocation->created_at->format('Y-m-d') : '-' }}
                    </td>
                    <td>
                         {{ $asset->currentAllocation ? ($asset->currentAllocation->expected_return_at ? $asset->currentAllocation->expected_return_at->format('Y-m-d') : '-') : '-' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">You have no assets currently allocated.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
