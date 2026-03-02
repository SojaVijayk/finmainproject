@extends('layouts/layoutMaster')
@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Expense Details</h5>
          <div>
            <a href="{{ route('pms.expenses.edit', $expense) }}" class="btn btn-primary btn-sm">Edit</a>
            <form action="{{ route('pms.expenses.destroy', $expense) }}" method="POST" class="d-inline">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-danger btn-sm"
                onclick="return confirm('Are you sure?')">Delete</button>
            </form>
          </div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <table class="table table-borderless">
                <tr>
                  <th width="30%">Project</th>
                  <td>{{ $expense->project->title }}</td>
                </tr>
                <tr>
                  <th>Category</th>
                  <td>{{ $expense->category->name }}</td>
                </tr>
                <tr>
                  <th>Vendor</th>
                  <td>{{ $expense->vendor->name }}</td>
                </tr>
                <tr>
                  <th>Amount</th>
                  <td>{{ number_format($expense->amount, 2) }}</td>
                </tr>
                <tr>
                  <th>Tax</th>
                  <td>{{ number_format($expense->tax, 2) }}</td>
                </tr>
              </table>
            </div>
            <div class="col-md-6">
              <table class="table table-borderless">
                <tr>
                  <th width="30%">Total Amount</th>
                  <td>{{ number_format($expense->total_amount, 2) }}</td>
                </tr>
                <tr>
                  <th>Payment Mode</th>
                  <td>{{ ucwords(str_replace('_', ' ', $expense->payment_mode)) }}</td>
                </tr>
                <tr>
                  <th>Payment Date</th>
                  <td>{{ $expense->payment_date->format('M d, Y') }}</td>
                </tr>
                <tr>
                  <th>Transaction Reference</th>
                  <td>{{ $expense->transaction_reference ?? 'N/A' }}</td>
                </tr>
                <tr>
                  <th>Recorded By</th>
                  <td>{{ $expense->creator->name }}</td>
                </tr>
              </table>
            </div>
          </div>

          <div class="row mt-3">
            <div class="col-md-12">
              <h6>Notes</h6>
              <p>{{ $expense->notes ?? 'No notes provided.' }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection