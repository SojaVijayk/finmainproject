@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Bank Account')

@section('content')
<div class="col-xl">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Edit Bank Account</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('pms.finance.accounts.update', $account->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label" for="bank_name">Bank Name</label>
                    <input type="text" class="form-control" id="bank_name" name="bank_name" value="{{ $account->bank_name }}" required />
                </div>
                <div class="mb-3">
                    <label class="form-label" for="account_name">Account Name</label>
                    <input type="text" class="form-control" id="account_name" name="account_name" value="{{ $account->account_name }}" required />
                </div>
                <div class="mb-3">
                    <label class="form-label" for="account_number">Account Number</label>
                    <input type="text" class="form-control" id="account_number" name="account_number" value="{{ $account->account_number }}" required />
                </div>
                <div class="mb-3">
                    <label class="form-label" for="ifsc_code">IFSC Code</label>
                    <input type="text" class="form-control" id="ifsc_code" name="ifsc_code" value="{{ $account->ifsc_code }}" />
                </div>
                <div class="mb-3">
                    <label class="form-label" for="branch">Branch</label>
                    <input type="text" class="form-control" id="branch" name="branch" value="{{ $account->branch }}" />
                </div>
                <div class="mb-3">
                    <label class="form-label" for="opening_balance">Opening Balance (Read Only)</label>
                    <input type="number" step="0.01" class="form-control" id="opening_balance" name="opening_balance" value="{{ $account->opening_balance }}" readonly />
                    <small class="text-muted">Balance updates should be done via transactions.</small>
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ $account->is_active ? 'checked' : '' }} />
                        <label class="form-check-label" for="is_active"> Active </label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('finance.accounts.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
