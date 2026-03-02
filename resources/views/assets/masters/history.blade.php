@extends('layouts/layoutMaster')

@section('title', 'Asset History - ' . $asset->asset_number)

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Asset History: {{ $asset->name }} ({{ $asset->asset_number }})</h5>
        <a href="{{ route('asset.masters.index') }}" class="btn btn-secondary">Back to List</a>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Action</th>
                    <th>Performed By</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @forelse($histories as $history)
                <tr>
                    <td>{{ $history->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        <span class="badge bg-label-info">{{ $history->action }}</span>
                    </td>
                    <td>{{ $history->performer->name ?? '-' }}</td>
                    <td>{{ $history->description }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center">No history found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
