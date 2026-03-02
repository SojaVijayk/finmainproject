@extends('layouts.app')

@section('title', 'Requirements')
@section('header', 'Requirements')
@section('actions')
    <a href="{{ route('pms.requirements.create') }}" class="btn btn-sm btn-primary">
        <i class="fas fa-plus"></i> Add New
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Temp No</th>
                        <th>Type</th>
                        <th>Client</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requirements as $requirement)
                    <tr>
                        <td>{{ $requirement->temp_no }}</td>
                        <td>{{ $requirement->type_name }}</td>
                        <td>{{ $requirement->client->client_name }}</td>
                        <td>{{ $requirement->category->name }}</td>
                        <td>
                            <span class="badge bg-{{ $requirement->status_badge_color }}">
                                {{ $requirement->status_name }}
                            </span>
                        </td>
                        <td>{{ $requirement->created_at->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('pms.requirements.show', $requirement->id) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($requirement->status == \App\Models\PMS\Requirement::STATUS_INITIATED)
                            <a href="{{ route('pms.requirements.edit', $requirement->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No requirements found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $requirements->links() }}
    </div>
</div>
@endsection
