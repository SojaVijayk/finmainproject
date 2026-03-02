@extends('layouts/layoutMaster')

@section('title', 'Tapal Tracing')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/spinkit/spinkit.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/chartjs/chartjs.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js')}}"></script>
<script src="{{asset('assets/vendor/libs/block-ui/block-ui.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/chartjs/chartjs.js')}}"></script>
@endsection

@section('page-script')
<script>
    $(document).ready(function() {
        // Initialize date pickers
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });

        // Initialize select2
        $('.select2').select2();

        // Initialize charts
        if (document.getElementById('statusChart')) {
            new Chart(document.getElementById('statusChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Pending', 'Completed', 'Overdue', 'In Progress'],
                    datasets: [{
                        data: [
                            {{ $stats['total_pending'] }},
                            {{ $stats['total_completed'] }},
                            {{ $stats['total_overdue'] }},
                            {{ $stats['total_in_progress'] }}
                        ],
                        backgroundColor: [
                            '#FFC107',
                            '#28A745',
                            '#DC3545',
                            '#17A2B8'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    }
                }
            });
        }

        {{--  if (document.getElementById('userPerformanceChart')) {
            new Chart(document.getElementById('userPerformanceChart'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($stats['top_users']->pluck('name')) !!},
                    datasets: [{
                        label: 'Completed',
                        data: {!! json_encode($stats['top_users']->pluck('completed_count')) !!},
                        backgroundColor: '#28A745'
                    }, {
                        label: 'Pending',
                        data: {!! json_encode($stats['top_users']->pluck('pending_count')) !!},
                        backgroundColor: '#FFC107'
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            stacked: true
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true
                        }
                    }
                }
            });
        }  --}}
    });
</script>
@endsection

@section('content')




<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Tapal Tracing</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('tapals.tracing') }}" method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <input type="text" name="search" class="form-control" placeholder="Search by Tapal No, Subject..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <select name="user_id" class="form-control select2" data-placeholder="Filter by User">
                            <option value=""></option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <select name="status" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="Completed" {{ request('status') == 'Accepted' ? 'selected' : '' }}>Accepted</option>
                            <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Action Takened</option>
                            <option value="Overdue" {{ request('status') == 'Overdue' ? 'selected' : '' }}>Overdue</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <input type="text" name="from_date" class="form-control datepicker" placeholder="From Date" value="{{ request('from_date') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <input type="text" name="to_date" class="form-control datepicker" placeholder="To Date" value="{{ request('to_date') }}">
                    </div>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary btn-block">Filter</button>
                </div>
            </div>
        </form>


        <div class="row mb-4">
    <!-- Summary Statistics -->
    <div class="col-md-3 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <h5 class="card-title">Total Tapals</h5>
                <h2 class="mb-1">{{ $stats['total_tapals'] }}</h2>
                <small class="text-muted">All time</small>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <h5 class="card-title">Pending</h5>
                <h2 class="mb-1">{{ $stats['total_pending'] }}</h2>
                <small class="text-muted">{{ $stats['pending_percentage'] }}% of total</small>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <h5 class="card-title">Completed</h5>
                <h2 class="mb-1">{{ $stats['total_completed'] }}</h2>
                <small class="text-muted">{{ $stats['completed_percentage'] }}% of total</small>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <h5 class="card-title">Overdue</h5>
                <h2 class="mb-1">{{ $stats['total_overdue'] }}</h2>
                <small class="text-muted">{{ $stats['overdue_percentage'] }}% of pending</small>
            </div>
        </div>
    </div>
</div>

    <!-- Charts Section -->
    <div class="col-md-3 mb-2">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Tapal Status Distribution</h5>
            </div>
            <div class="card-body">
                <canvas id="statusChart" height="250"></canvas>
            </div>
        </div>
    </div>

    {{--  <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Top Users Performance</h5>
            </div>
            <div class="card-body">
                <canvas id="userPerformanceChart" height="250"></canvas>
            </div>
        </div>
    </div>


    --}}

<div class="row mb-4">
       <div class="col-md-12 mb-4">


        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>Tapal No.</th>
                        <th>Subject</th>
                        <th>Type</th>
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

                        $lastMovement = $tapal->movements->where('is_assignment',1)->last();
                        $fromInfo = $tapal->type == 'inward' ?
                            ($tapal->inward_mode == 'By Mail' ? $tapal->mail_id : $tapal->from_name) :
                            $tapal->creator->name;
                    @endphp


                    <tr>
                        <td>{{ $tapal->tapal_number }}</td>
                        <td>{{ $tapal->subject }}</td>
                        <td>{{ ucfirst($tapal->type) }}</td>
                        <td>{{ $fromInfo }}</td>
                        <td>{{ $tapal->received_date }}</td>
                        <td>
                          {{ $tapal->currentHolder->name ?? 'N/A' }}</td>
                        <td>
                             @if($lastMovement)
                                <span class="badge text-bg-{{
                                    $lastMovement->status == 'Completed' ? 'success' :
                                    ($lastMovement->status == 'Accepted' ? 'primary' :
                                    ($lastMovement->status == 'Pending' ? 'warning' : 'secondary'))
                                }}">
                                    {{ $lastMovement->status }}
                                    @if($lastMovement->status == 'Completed' && $lastMovement->completed_at)
                                        <br><small>{{ $lastMovement->completed_at }}</small>
                                    @endif
                                     @if($lastMovement->status == 'Accepted' && $lastMovement->accepted_at)
                                        <br><small>{{ $lastMovement->accepted_at }}</small>
                                    @endif
                                </span>
                            @endif
                        </td>

                        <td>
                            <a href="{{ route('tapals.tracing.show', $tapal->id) }}" class="btn btn-sm btn-info" title="View Details">
                                <i class="fas fa-search"></i>
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



    </div>
</div>
@endsection
