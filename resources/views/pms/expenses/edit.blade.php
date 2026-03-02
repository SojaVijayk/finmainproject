@extends('layouts/layoutMaster')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title">Edit Expense</h5>
        </div>
        <div class="card-body">
          <form action="{{ route('pms.expenses.update', $expense) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="project_id">Project *</label>
                  <select name="project_id" id="project_id" class="form-control" required>
                    <option value="">Select Project</option>
                    @foreach($projects as $project)
                    <option value="{{ $project->id }}" {{ old('project_id', $expense->project_id) == $project->id ?
                      'selected' : '' }}>{{ $project->title }}</option>
                    @endforeach
                  </select>
                  @error('project_id')
                  <div class="text-danger">{{ $message }}</div>
                  @enderror
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="category_id">Category *</label>
                  <select name="category_id" id="category_id" class="form-control" required>
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id', $expense->category_id) == $category->id ?
                      'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                  </select>
                  @error('category_id')
                  <div class="text-danger">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="vendor_id">Vendor *</label>
                  <select name="vendor_id" id="vendor_id" class="form-control" required>
                    <option value="">Select Vendor</option>
                    @foreach($vendors as $vendor)
                    <option value="{{ $vendor->id }}" {{ old('vendor_id', $expense->vendor_id) == $vendor->id ?
                      'selected' : '' }}>{{ $vendor->name }}</option>
                    @endforeach
                  </select>
                  @error('vendor_id')
                  <div class="text-danger">{{ $message }}</div>
                  @enderror
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="payment_mode">Payment Mode *</label>
                  <select name="payment_mode" id="payment_mode" class="form-control" required>
                    <option value="">Select Payment Mode</option>
                    @foreach($paymentModes as $mode)
                    <option value="{{ $mode }}" {{ old('payment_mode', $expense->payment_mode) == $mode ? 'selected' :
                      '' }}>{{ ucwords(str_replace('_', ' ', $mode)) }}</option>
                    @endforeach
                  </select>
                  @error('payment_mode')
                  <div class="text-danger">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="amount">Amount *</label>
                  <input type="number" step="0.01" name="amount" id="amount" class="form-control"
                    value="{{ old('amount', $expense->amount) }}" required>
                  @error('amount')
                  <div class="text-danger">{{ $message }}</div>
                  @enderror
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="tax">Tax</label>
                  <input type="number" step="0.01" name="tax" id="tax" class="form-control"
                    value="{{ old('tax', $expense->tax) }}">
                  @error('tax')
                  <div class="text-danger">{{ $message }}</div>
                  @enderror
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="payment_date">Payment Date *</label>
                  <input type="date" name="payment_date" id="payment_date" class="form-control"
                    value="{{ old('payment_date', $expense->payment_date->format('Y-m-d')) }}" required>
                  @error('payment_date')
                  <div class="text-danger">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="transaction_reference">Transaction Reference</label>
                  <input type="text" name="transaction_reference" id="transaction_reference" class="form-control"
                    value="{{ old('transaction_reference', $expense->transaction_reference) }}">
                  @error('transaction_reference')
                  <div class="text-danger">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="notes">Notes</label>
                  <textarea name="notes" id="notes" class="form-control"
                    rows="3">{{ old('notes', $expense->notes) }}</textarea>
                  @error('notes')
                  <div class="text-danger">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>

            <div class="form-group">
              <button type="submit" class="btn btn-primary">Update Expense</button>
              <a href="{{ route('pms.expenses.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection