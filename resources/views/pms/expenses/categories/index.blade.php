@extends('layouts/layoutMaster')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Expense Categories</h5>
          <a href="{{ route('pms.expense-categories.create') }}" class="btn btn-primary btn-sm">Add Category</a>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Description</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($categories as $category)
                <tr>
                  <td>{{ $category->id }}</td>
                  <td>{{ $category->name }}</td>
                  <td>{{ $category->description ?? 'N/A' }}</td>
                  <td>
                    <a href="{{ route('pms.expense-categories.edit', $category) }}"
                      class="btn btn-primary btn-sm">Edit</a>
                    <form action="{{ route('pms.expense-categories.destroy', $category) }}" method="POST"
                      class="d-inline">
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
            {{ $categories->links() }}
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection