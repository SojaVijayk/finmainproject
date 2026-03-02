@extends('layouts/layoutMaster')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title">Create Vendor</h5>
        </div>
        <div class="card-body">
          <form action="{{ route('pms.vendors.store') }}" method="POST">
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
                  <label for="contact_details">Contact Details</label>
                  <textarea name="contact_details" id="contact_details" class="form-control"
                    rows="3">{{ old('contact_details') }}</textarea>
                  @error('contact_details')
                  <div class="text-danger">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>

            <div class="form-group">
              <button type="submit" class="btn btn-primary">Save Vendor</button>
              <a href="{{ route('pms.vendors.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
          </form>
        </div>
      </div>
    </div>
    @endsection