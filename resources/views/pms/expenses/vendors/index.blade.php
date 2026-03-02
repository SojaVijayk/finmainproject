@extends('layouts/layoutMaster')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Vendors</h5>
          <a href="{{ route('pms.vendors.create') }}" class="btn btn-primary btn-sm">Add Vendor</a>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Contact Details</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($vendors as $vendor)
                <tr>
                  <td>{{ $vendor->id }}</td>
                  <td>{{ $vendor->name }}</td>
                  <td>{{ $vendor->contact_details ?? 'N/A' }}</td>
                  <td>
                    <a href="{{ route('pms.vendors.edit', $vendor) }}" class="btn btn-primary btn-sm">Edit</a>
                    <form action="{{ route('pms.vendors.destroy', $vendor) }}" method="POST" class="d-inline">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-danger btn-sm"
                        onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <div class="d-flex justify-content-center">
            {{ $vendors->links() }}
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection