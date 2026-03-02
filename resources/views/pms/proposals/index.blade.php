@extends('layouts/layoutMaster')

@section('title', 'Proposals')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/spinkit/spinkit.css')}}" />

@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js')}}"></script>
<script src="{{asset('assets/vendor/libs/block-ui/block-ui.js')}}"></script>

@endsection

@section('page-script')
<script>
  $(document).ready(function() {
    $('.table').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        language: {
            paginate: {
                previous: 'Prev',
                next: 'Next'
            }
        }
    });
});
</script>
@endsection


@section('content')
<div class="card">
  <div class="card-body">
    <div class="table-responsive card-datatable">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Requirement</th>
            <th>Title</th>
            <th>Budget</th>
            <th>Tenure</th>
            <th>Status</th>
            <th>Created By</th>
            <th>Created At</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($proposals as $proposal)
          <tr>
            <td>{{ $proposal->requirement->temp_no }}</td>
            <td>{{ $proposal->requirement->project_title }}</td>
            <td>₹{{ number_format($proposal->budget, 2) }}</td>
            <td>{{ $proposal->tenure }}</td>
            <td>
              <span class="badge bg-{{ $proposal->status_badge_color }}">
                {{ $proposal->status_name }}
              </span>
            </td>
            <td>{{ $proposal->creator->name }}</td>
            <td>{{ $proposal->created_at->format('d M Y') }}</td>
            <td>
              <a href="{{ route('pms.proposals.show', $proposal->id) }}" class="btn btn-sm btn-info">
                <i class="fas fa-eye"></i>
              </a>
              @if(in_array($proposal->status, [
              \App\Models\PMS\Proposal::STATUS_CREATED,
              \App\Models\PMS\Proposal::STATUS_RETURNED_FOR_CLARIFICATION
              ]) && $proposal->created_by == auth()->id())
              <a href="{{ route('pms.proposals.edit', $proposal->id) }}" class="btn btn-sm btn-primary">
                <i class="fas fa-edit"></i>
              </a>
              @endif
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7" class="text-center">No proposals found</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- {{ $proposals->links() }} --}}
  </div>
</div>
@endsection