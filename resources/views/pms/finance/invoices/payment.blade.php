@extends('layouts/layoutMaster')

@section('title', 'Record Payment')

@section('content')
<div class="card">
  <div class="card-header">
    <h5 class="mb-0">Record Payment for Invoice #{{ $invoice->invoice_number }}</h5>
  </div>
  <div class="card-body">
    <form action="{{ route('pms.finance.invoices.store-payment', $invoice->id) }}" method="POST">
      @csrf

      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">Invoice Amount</label>
          <input type="text" class="form-control" value="{{ number_format($invoice->total_amount, 2) }}" readonly>
        </div>
        <div class="col-md-6">
          <label class="form-label">Balance Due</label>
          <input type="text" class="form-control" value="{{ number_format($invoice->balance_amount, 2) }}" readonly>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <label for="amount" class="form-label">Payment Amount*</label>
          <input type="number" step="0.01" min="0.01" max="{{ $invoice->balance_amount }}" class="form-control"
            id="amount" name="amount" required>
        </div>
        <div class="col-md-6">
          <label for="payment_date" class="form-label">Payment Date*</label>
          <input type="date" class="form-control" id="payment_date" name="payment_date"
            value="{{ old('payment_date', today()->format('Y-m-d')) }}" required>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <label for="payment_method" class="form-label">Payment Method*</label>
          <select class="form-select" id="payment_method" name="payment_method" required>
            <option value="">Select Method</option>
            <option value="Bank Transfer">Bank Transfer</option>
            <option value="Credit Card">Credit Card</option>
            <option value="Check">Check</option>
            <option value="Cash">Cash</option>
            <option value="Online Payment">Online Payment</option>
            <option value="Other">Other</option>
          </select>
        </div>
        <div class="col-md-6">
          <label for="transaction_reference" class="form-label">Transaction Reference</label>
          <input type="text" class="form-control" id="transaction_reference" name="transaction_reference">
        </div>
      </div>

      <div class="mb-3">
        <label for="notes" class="form-label">Notes</label>
        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
      </div>

      <div class="d-flex justify-content-between">
        <a href="{{ route('pms.finance.invoices.show', $invoice->id) }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">Record Payment</button>
      </div>
    </form>
  </div>
</div>
@endsection