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
<div class="row">
  <!-- Manual Entry -->
  <div class="col-xl-6">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Manual Transaction</h5>
      </div>
      <div class="card-body">
        <form action="{{ route('pms.finance.transactions.store') }}" method="POST">
          @csrf
          <div class="mb-3">
            <label class="form-label" for="finance_bank_account_id">Bank Account</label>
            <select class="form-select" id="finance_bank_account_id" name="finance_bank_account_id" required>
              <option value="">Select Account</option>
              @foreach($accounts as $account)
              <option value="{{ $account->id }}">{{ $account->bank_name }} - {{ $account->account_number }} (Current:
                ₹{{ number_format($account->current_balance, 2) }})</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label" for="type">Type</label>
            <select class="form-select" id="type" name="type" required>
              <option value="credit">Credit (Inflow)</option>
              <option value="debit">Debit (Outflow)</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label" for="amount">Amount</label>
            <input type="number" step="0.01" class="form-control" id="amount" name="amount" placeholder="0.00"
              required />
          </div>
          <div class="mb-3">
            <label class="form-label" for="transaction_date">Date</label>
            <input type="date" class="form-control" id="transaction_date" name="transaction_date"
              value="{{ date('Y-m-d') }}" required />
          </div>
          <div class="mb-3">
            <label class="form-label" for="category">Category</label>
            <select class="form-select" id="category" name="category" required>
              <option value="Manual Entry">Manual Entry</option>
              <option value="Capital Injection">Capital Injection</option>
              <option value="Other Income">Other Income</option>
              <option value="Office Expense">Office Expense</option>
              <option value="Salary Payment">Salary Payment</option>
              <option value="Misc">Misc</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label" for="description">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
          </div>
          <button type="submit" class="btn btn-primary">Save Transaction</button>
          <a href="{{ route('pms.finance.transactions.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
      </div>
    </div>
  </div>

  <!-- Bulk Import -->
  <div class="col-xl-6">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Bulk Import (Excel/CSV)</h5>
      </div>
      <div class="card-body">
        <div class="alert alert-info">
          <h6 class="alert-heading fw-bold mb-1">Instructions:</h6>
          <p class="mb-0">Upload an Excel/CSV file with columns: <code>amount</code> (positive for number),
            <code>type</code> (credit/debit), <code>transaction_date</code>, <code>category</code> (optional),
            <code>description</code> (optional).
          </p>
        </div>
        <form action="{{ route('pms.finance.transactions.import') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="mb-3">
            <label class="form-label" for="import_bank_account_id">Target Bank Account</label>
            <select class="form-select" id="import_bank_account_id" name="finance_bank_account_id" required>
              <option value="">Select Account</option>
              @foreach($accounts as $account)
              <option value="{{ $account->id }}">{{ $account->bank_name }} - {{ $account->account_number }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label" for="file">Upload File</label>
            <input type="file" class="form-control" id="file" name="file" accept=".xlsx,.csv" required />
          </div>
          <button type="submit" class="btn btn-primary">Import Transactions</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection