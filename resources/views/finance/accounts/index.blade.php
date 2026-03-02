@extends('layouts/layoutMaster')
@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}">

@endsection
@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>

@endsection
@section('page-script')
<script>
  $('.select2').select2({ width: 'resolve' });
</script>
@endsection

@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Bank Accounts</h5>
    <a href="{{ route('pms.finance.accounts.create') }}" class="btn btn-primary">Add New Account</a>
  </div>
  <div class="table-responsive text-nowrap">
    <table class="table">
      <thead>
        <tr>
          <th>Bank Name</th>
          <th>Account Name</th>
          <th>Account Number</th>
          <th>Branch</th>
          <th>Balance</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($accounts as $account)
        <tr>
          <td>{{ $account->bank_name }}</td>
          <td>{{ $account->account_name }}</td>
          <td>{{ $account->account_number }}</td>
          <td>{{ $account->branch }}</td>
          <td>₹{{ number_format($account->current_balance, 2) }}</td>
          <td><span class="badge {{ $account->is_active ? 'bg-label-success' : 'bg-label-secondary' }}">{{
              $account->is_active ? 'Active' : 'Inactive' }}</span></td>
          <td>
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i
                  class="bx bx-dots-vertical-rounded"></i></button>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{ route('pms.finance.accounts.edit', $account->id) }}"><i
                    class="bx bx-edit-alt me-1"></i> Edit</a>
                <form action="{{ route('pms.finance.accounts.destroy', $account->id) }}" method="POST"
                  onsubmit="return confirm('Are you sure?');">
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
</div>
@endsection