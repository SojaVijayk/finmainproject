@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Fund Requirement')

@section('content')
<div class="col-xl">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Edit Fund Requirement</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('finance.requirements.update', $requirement->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label" for="title">Title</label>
                    <input type="text" class="form-control" id="title" name="title" value="{{ $requirement->title }}" required />
                </div>
                <div class="mb-3">
                    <label class="form-label" for="amount">Amount</label>
                    <input type="number" step="0.01" class="form-control" id="amount" name="amount" value="{{ $requirement->amount }}" required />
                </div>
                <div class="mb-3">
                    <label class="form-label" for="due_date">Due Date</label>
                    <input type="date" class="form-control" id="due_date" name="due_date" value="{{ $requirement->due_date->format('Y-m-d') }}" required />
                </div>
                 <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_recurring" name="is_recurring" value="1" {{ $requirement->is_recurring ? 'checked' : '' }} />
                        <label class="form-check-label" for="is_recurring"> Recurring Payment </label>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="frequency">Frequency (if recurring)</label>
                    <select class="form-select" id="frequency" name="frequency">
                        <option value="">Select Frequency</option>
                        <option value="weekly" {{ $requirement->frequency == 'weekly' ? 'selected' : '' }}>Weekly</option>
                        <option value="monthly" {{ $requirement->frequency == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="yearly" {{ $requirement->frequency == 'yearly' ? 'selected' : '' }}>Yearly</option>
                    </select>
                </div>
                 <div class="mb-3">
                    <label class="form-label" for="status">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="pending" {{ $requirement->status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ $requirement->status == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="paid" {{ $requirement->status == 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3">{{ $requirement->description }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('finance.requirements.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
