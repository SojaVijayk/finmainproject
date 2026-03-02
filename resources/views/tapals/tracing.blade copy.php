@extends('layouts/layoutMaster')

@section('title', 'Leave - Request')

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
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Tapal Tracing</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('tapals.tracing') }}" method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <input type="text" name="search" class="form-control" placeholder="Search by Tapal No, Subject..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <a href="{{ route('tapals.tracing') }}" class="btn btn-secondary">Reset</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>Tapal No.</th>
                        <th>Subject</th>
                        <th>From/To</th>
                        <th>Received Date</th>
                        <th>Current Holder</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tapals as $tapal)
                    @php
                        $lastMovement = $tapal->movements->last();
                        $fromInfo = $tapal->type == 'inward' ?
                            ($tapal->inward_mode == 'By Mail' ? $tapal->mail_id : $tapal->from_name) :
                            $tapal->creator->name;
                    @endphp
                    <tr>
                        <td>{{ $tapal->tapal_number }}</td>
                        <td>{{ $tapal->subject }}</td>
                        <td>{{ $fromInfo }}</td>
                        <td>{{ $tapal->received_date }}</td>
                        <td>{{ $tapal->currentHolder->name ?? 'N/A' }}</td>
                        <td>
                            @if($lastMovement)
                                <span class="badge badge-{{
                                    $lastMovement->status == 'Completed' ? 'success' :
                                    ($lastMovement->status == 'Accepted' ? 'primary' :
                                    ($lastMovement->status == 'Pending' ? 'warning' : 'secondary'))
                                }}">
                                    {{ $lastMovement->status }}
                                    @if($lastMovement->status == 'Completed' && $lastMovement->completed_at)
                                        <br><small>{{ $lastMovement->completed_at }}</small>
                                    @endif
                                </span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('tapals.tracing.show', $tapal->id) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-search"></i> Details
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $tapals->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
