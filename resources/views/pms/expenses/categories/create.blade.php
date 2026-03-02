@extends('layouts/layoutMaster')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title">Create Expense Category</h5>
        </div>
        <div class="card-body">
          <form action="{{ route('pms.expense-categories.store') }}" method="POST">
            @csrf
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="name">Name *</label>
                  <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
                  @error('name')
                  <div class="text-danger">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="description">Description</label>
                  <textarea name="description" id="description" class="form-control"
                    rows="3">{{ old('description') }}</textarea>
                  @error('description')
                  <div class="text-danger">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>

            <div class="form-group">
              <button type="submit" class="btn btn-primary">Save Category</button>
              <a href="{{ route('pms.expense-categories.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection