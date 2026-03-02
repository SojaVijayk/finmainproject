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
<div class="col-xl">
  <div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Add Bank Account</h5>
    </div>
    <div class="card-body">
      <form action="{{ route('pms.finance.accounts.store') }}" method="POST">
        @csrf
        <div class="mb-3">
          <label class="form-label" for="bank_name">Bank Name</label>
          <input type="text" class="form-control" id="bank_name" name="bank_name" placeholder="HDFC Bank" required />
        </div>
        <div class="mb-3">
          <label class="form-label" for="account_name">Account Name</label>
          <input type="text" class="form-control" id="account_name" name="account_name" placeholder="Company Name"
            required />
        </div>
        <div class="mb-3">
          <label class="form-label" for="account_number">Account Number</label>
          <input type="text" class="form-control" id="account_number" name="account_number" placeholder="1234567890"
            required />
        </div>
        <div class="mb-3">
          <label class="form-label" for="ifsc_code">IFSC Code</label>
          <input type="text" class="form-control" id="ifsc_code" name="ifsc_code" placeholder="HDFC000123" />
        </div>
        <div class="mb-3">
          <label class="form-label" for="branch">Branch</label>
          <input type="text" class="form-control" id="branch" name="branch" placeholder="Main Branch" />
        </div>
        <div class="mb-3">
          <label class="form-label" for="opening_balance">Opening Balance</label>
          <input type="number" step="0.01" class="form-control" id="opening_balance" name="opening_balance"
            placeholder="0.00" required />
        </div>
        <div class="mb-3">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked />
            <label class="form-check-label" for="is_active"> Active </label>
          </div>
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
        <a href="{{ route('pms.finance.accounts.index') }}" class="btn btn-secondary">Cancel</a>
      </form>
    </div>
  </div>
</div>
@endsection