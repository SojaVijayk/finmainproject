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
    <h5 class="mb-0">Bank Transactions</h5>
    <a href="{{ route('pms.finance.transactions.create') }}" class="btn btn-primary">Add New / Import</a>
  </div>
  <div class="table-responsive text-nowrap">
    <table class="table">
      <thead>
        <tr>
          <th>Date</th>
          <th>Account</th>
          <th>Type</th>
          <th>Category</th>
          <th>Description</th>
          <th>Amount</th>
          <th>Balance After</th>
          <th>Reference</th>
        </tr>
      </thead>
      <tbody>
        @foreach($transactions as $transaction)
        <tr>
          <td>{{ $transaction->transaction_date->format('d M Y') }}</td>
          <td>{{ $transaction->bankAccount->bank_name }} - {{ $transaction->bankAccount->account_number }}</td>
          <td><span class="badge {{ $transaction->type == 'credit' ? 'bg-label-success' : 'bg-label-danger' }}">{{
              ucfirst($transaction->type) }}</span></td>
          <td>{{ $transaction->category }}</td>
          <td>{{ $transaction->description }}</td>
          <td class="{{ $transaction->type == 'credit' ? 'text-success' : 'text-danger' }}">
            {{ $transaction->type == 'credit' ? '+' : '-' }}₹{{ number_format($transaction->amount, 2) }}
          </td>
          <td>₹{{ number_format($transaction->balance_after, 2) }}</td>
          <td>
            @if($transaction->reference_type)
            <small>{{ class_basename($transaction->reference_type) }} #{{ $transaction->reference_id }}</small>
            @else
            -
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="card-footer">
    {{ $transactions->links() }}
  </div>
</div>
@endsection