@extends('layouts/contentNavbarLayout')

@section('title', 'Upcoming Fund Requirements')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Upcoming Fund Requirements</h5>
        <a href="{{ route('finance.requirements.create') }}" class="btn btn-primary">Add New Requirement</a>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Due Date</th>
                    <th>Amount</th>
                    <th>Recurring</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($requirements as $requirement)
                <tr>
                    <td>{{ $requirement->title }}</td>
                    <td>{{ $requirement->due_date->format('d M Y') }}</td>
                    <td>₹{{ number_format($requirement->amount, 2) }}</td>
                    <td><span class="badge {{ $requirement->is_recurring ? 'bg-label-info' : 'bg-label-secondary' }}">{{ $requirement->is_recurring ? 'Yes (' . ucfirst($requirement->frequency) . ')' : 'No' }}</span></td>
                    <td>
                        <span class="badge @if($requirement->status == 'pending') bg-label-warning @elseif($requirement->status == 'paid') bg-label-success @else bg-label-primary @endif">
                            {{ ucfirst($requirement->status) }}
                        </span>
                    </td>
                    <td>
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{ route('finance.requirements.edit', $requirement->id) }}"><i class="bx bx-edit-alt me-1"></i> Edit</a>
                                <form action="{{ route('finance.requirements.destroy', $requirement->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item"><i class="bx bx-trash me-1"></i> Delete</button>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $requirements->links() }}
    </div>
</div>
@endsection
