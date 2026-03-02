@extends('layouts/layoutMaster')

@section('title', 'PMS Dashboard')


@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/apex-charts/apex-charts.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/apex-charts/apexcharts.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('assets/js/dashboards-ecommerce.js')}}"></script>
@endsection

@section('content')
@php
use \App\Models\PMS\Project;
use \App\Models\PMS\Task;
use \App\Models\PMS\Invoice;
use \App\Models\PMS\Requirement;
use \App\Models\PMS\ActivityLog;
use \App\Models\PMS\Proposal;


@endphp
<div class="row">




  <!-- Statistics -->
  <div class="col-xl-12 mb-4 col-lg-7 col-12">
    <div class="card h-100">
      <div class="card-header">
        <div class="d-flex justify-content-between mb-3">
          <h5 class="card-title mb-0">Statistics</h5>
          <small class="text-muted">Updated 1 month ago</small>
        </div>
      </div>
      <div class="card-body">
        <div class="row gy-3">
          <div class="col-md-3 col-6">
            <div class="d-flex align-items-center">
              <div class="badge rounded-pill bg-label-primary me-3 p-2"><i class="ti ti-chart-pie-2 ti-sm"></i></div>
              <div class="card-info">
                <h5 class="mb-0">230k</h5>
                <small>Total Projects</small>
              </div>
            </div>
          </div>
          <div class="col-md-3 col-6">
            <div class="d-flex align-items-center">
              <div class="badge rounded-pill bg-label-info me-3 p-2"><i class="ti ti-users ti-sm"></i></div>
              <div class="card-info">
                <h5 class="mb-0">8.549k</h5>
                <small>Active Projects</small>
              </div>
            </div>
          </div>
          <div class="col-md-3 col-6">
            <div class="d-flex align-items-center">
              <div class="badge rounded-pill bg-label-danger me-3 p-2"><i class="ti ti-shopping-cart ti-sm"></i></div>
              <div class="card-info">
                <h5 class="mb-0">1.423k</h5>
                <small>Completed Projects</small>
              </div>
            </div>
          </div>
          <div class="col-md-3 col-6">
            <div class="d-flex align-items-center">
              <div class="badge rounded-pill bg-label-success me-3 p-2"><i class="ti ti-currency-dollar ti-sm"></i>
              </div>
              <div class="card-info">
                <h5 class="mb-0">$9745</h5>
                <small>Pending ACtions</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--/ Statistics -->

  <div class="col-xl-4 col-12">
    <div class="row">

      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Quick Actions</h5>
        </div>
        <div class="card-body quick-links">
          <div class="row">
            {{-- @if($user->can('create_requirements')) --}}
            <div class="col-md-6 mb-3">
              <div class="card h-100">
                <div class="card-body text-center">
                  <i class="fas fa-file-alt fa-3x mb-3 text-primary"></i>
                  <h6>New Requirement</h6>
                  <a href="{{ route('pms.requirements.create') }}" class="stretched-link"></a>
                </div>
              </div>
            </div>
            {{-- @endif --}}

            {{-- @if($user->hasRole('faculty') || $user->can('create_proposals')) --}}
            <div class="col-md-6 mb-3">
              <div class="card h-100">
                <div class="card-body text-center">
                  <i class="fas fa-project-diagram fa-3x mb-3 text-success"></i>
                  <h6>Projects</h6>
                  {{-- <a href="{{ route('pms.projects.list') }}" class="stretched-link"></a> --}}
                </div>
              </div>
            </div>
            {{-- @endif --}}

            {{-- @if($user->hasRole('team_member')) --}}
            <div class="col-md-6 mb-3">
              <div class="card h-100">
                <div class="card-body text-center">
                  <i class="fas fa-clock fa-3x mb-3 text-info"></i>
                  <h6>Timesheet</h6>
                  <a href="{{ route('pms.timesheets.index') }}" class="stretched-link"></a>
                </div>
              </div>
            </div>
            {{-- @endif --}}

            @if($user->hasRole('finance'))
            <div class="col-md-6 mb-3">
              <div class="card h-100">
                <div class="card-body text-center">
                  <i class="fas fa-file-invoice-dollar fa-3x mb-3 text-warning"></i>
                  <h6>Create Invoice</h6>
                  {{-- <a href="{{ route('pms.invoices.create') }}" class="stretched-link"></a> --}}
                </div>
              </div>
            </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Your Projects -->
  <div class="col-12 col-xl-8 mb-4 col-lg-7">
    <div class="card">
      <div class="card-header pb-3 ">
        <h5 class="m-0 me-2 card-title">Your Projects</h5>
      </div>
      <div class="card-body">
        <div class="row row-bordered g-0">

          @php
          $projects = Project::where('project_investigator_id', $user->id)
          ->with(['requirement.client'])
          ->orderBy('status')
          ->limit(5)
          ->get();
          @endphp

          @if($projects->count() > 0)
          <div class="list-group">
            @foreach($projects as $project)
            <div class="list-group-item">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="mb-1">{{ $project->title }}</h6>
                  <small class="text-muted">{{ $project->requirement->client->client_name }}</small>
                </div>
                <div>
                  <span class="badge bg-{{ $project->status_badge_color }}">
                    {{ $project->status_name }}
                  </span>
                </div>
              </div>
              <div class="mt-2">
                <div class="progress" style="height: 10px;">
                  <div class="progress-bar" role="progressbar" style="width: {{ $project->completion_percentage }}%"
                    aria-valuenow="{{ $project->completion_percentage }}" aria-valuemin="0" aria-valuemax="100">
                  </div>
                </div>
                <small class="text-muted">{{ $project->completion_percentage }}% Complete</small>
              </div>
              <div class="mt-2">
                <a href="{{ route('pms.projects.show', $project->id) }}" class="btn btn-sm btn-outline-primary">
                  <i class="fas fa-eye"></i> View
                </a>
                <a href="{{ route('pms.projects.gantt', $project->id) }}" class="btn btn-sm btn-outline-info">
                  <i class="fas fa-project-diagram"></i> Gantt Chart
                </a>
              </div>
            </div>
            @endforeach
          </div>
          @else
          <p>You are not currently assigned as investigator on any projects.</p>
          @endif
        </div>
      </div>
    </div>
  </div>
  <!--/ Revenue Report -->

  <!-- Earning Reports -->
  <div class="col-xl-4 col-lg-5 col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between">
        <div class="card-title mb-0">
          <h5 class="m-0 me-2">Your Tasks</h5>
          {{-- <small class="text-muted">Weekly Earnings Overview</small> --}}
        </div>

      </div>
      <div class="card-body pb-0">

        @php
        $tasks = Task::whereHas('assignments', function($q) use ($user) {
        $q->where('user_id', $user->id);
        })->where('status', '!=', Task::STATUS_COMPLETED)
        ->with('milestone.project')
        ->orderBy('end_date')
        ->limit(5)
        ->get();
        @endphp

        @if($tasks->count() > 0)
        @foreach($tasks as $task)
        <div
          class="task-item task-priority-{{ $task->priority_name == 'High' ? 'high' : ($task->priority_name == 'Medium' ? 'medium' : 'low') }}">
          <div class="d-flex justify-content-between">
            <h6>{{ $task->name }}</h6>
            <span class="badge bg-{{ $task->status == Task::STATUS_IN_PROGRESS ? 'info' : 'secondary' }}">
              {{ $task->status_name }}
            </span>
          </div>
          <p class="mb-1 small">{{ $task->milestone->project->title }}</p>
          <div class="d-flex justify-content-between">
            <small class="text-muted">Due: {{ $task->end_date->format('M d, Y') }}</small>
            <small>Priority: {{ $task->priority_name }}</small>
          </div>
        </div>
        @endforeach
        @else
        <p>No tasks assigned to you currently.</p>
        @endif
        <div class="mt-3">
          <a href="{{ route('pms.timesheets.index') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-clock"></i> Log Today's Time ({{ gmdate("H:i", $data['today_timesheet'] * 60) }})
          </a>
        </div>
      </div>
    </div>
  </div>
  <!--/ Earning Reports -->

  <!-- Popular Product -->
  <div class="col-md-6 col-xl-4 mb-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between">
        <div class="card-title m-0 me-2">
          <h5 class="m-0 me-2">Popular Products</h5>
          <small class="text-muted">Total 10.4k Visitors</small>
        </div>
        <div class="dropdown">
          <button class="btn p-0" type="button" id="popularProduct" data-bs-toggle="dropdown" aria-haspopup="true"
            aria-expanded="false">
            <i class="ti ti-dots-vertical ti-sm text-muted"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end" aria-labelledby="popularProduct">
            <a class="dropdown-item" href="javascript:void(0);">Last 28 Days</a>
            <a class="dropdown-item" href="javascript:void(0);">Last Month</a>
            <a class="dropdown-item" href="javascript:void(0);">Last Year</a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <ul class="p-0 m-0">
          <li class="d-flex mb-4 pb-1">
            <div class="me-3">
              <img src="{{ asset('assets/img/products/iphone.png') }}" alt="User" class="rounded" width="46">
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <h6 class="mb-0">Apple iPhone 13</h6>
                <small class="text-muted d-block">Item: #FXZ-4567</small>
              </div>
              <div class="user-progress d-flex align-items-center gap-1">
                <p class="mb-0 fw-semibold">$999.29</p>
              </div>
            </div>
          </li>
          <li class="d-flex mb-4 pb-1">
            <div class="me-3">
              <img src="{{asset('assets/img/products/nike-air-jordan.png')}}" alt="User" class="rounded" width="46">
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <h6 class="mb-0">Nike Air Jordan</h6>
                <small class="text-muted d-block">Item: #FXZ-3456</small>
              </div>
              <div class="user-progress d-flex align-items-center gap-1">
                <p class="mb-0 fw-semibold">$72.40</p>
              </div>
            </div>
          </li>
          <li class="d-flex mb-4 pb-1">
            <div class="me-3">
              <img src="{{asset('assets/img/products/headphones.png')}}" alt="User" class="rounded" width="46">
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <h6 class="mb-0">Beats Studio 2</h6>
                <small class="text-muted d-block">Item: #FXZ-9485</small>
              </div>
              <div class="user-progress d-flex align-items-center gap-1">
                <p class="mb-0 fw-semibold">$99</p>
              </div>
            </div>
          </li>
          <li class="d-flex mb-4 pb-1">
            <div class="me-3">
              <img src="{{asset('assets/img/products/apple-watch.png')}}" alt="User" class="rounded" width="46">
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <h6 class="mb-0">Apple Watch Series 7</h6>
                <small class="text-muted d-block">Item: #FXZ-2345</small>
              </div>
              <div class="user-progress d-flex align-items-center gap-1">
                <p class="mb-0 fw-semibold">$249.99</p>
              </div>
            </div>
          </li>
          <li class="d-flex mb-4 pb-1">
            <div class="me-3">
              <img src="{{asset('assets/img/products/amazon-echo.png')}}" alt="User" class="rounded" width="46">
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <h6 class="mb-0">Amazon Echo Dot</h6>
                <small class="text-muted d-block">Item: #FXZ-8959</small>
              </div>
              <div class="user-progress d-flex align-items-center gap-1">
                <p class="mb-0 fw-semibold">$79.40</p>
              </div>
            </div>
          </li>
          <li class="d-flex">
            <div class="me-3">
              <img src="{{asset('assets/img/products/play-station.png')}}" alt="User" class="rounded" width="46">
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <h6 class="mb-0">Play Station Console</h6>
                <small class="text-muted d-block">Item: #FXZ-7892</small>
              </div>
              <div class="user-progress d-flex align-items-center gap-1">
                <p class="mb-0 fw-semibold">$129.48</p>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
  <!--/ Popular Product -->

  <!-- Sales by Countries tabs-->
  <div class="col-md-6 col-xl-4 col-xl-4 mb-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between pb-2 mb-1">
        <div class="card-title mb-1">
          <h5 class="m-0 me-2">Sales by Countries</h5>
          <small class="text-muted">62 Deliveries in Progress</small>
        </div>
        <div class="dropdown">
          <button class="btn p-0" type="button" id="salesByCountryTabs" data-bs-toggle="dropdown" aria-haspopup="true"
            aria-expanded="false">
            <i class="ti ti-dots-vertical ti-sm text-muted"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end" aria-labelledby="salesByCountryTabs">
            <a class="dropdown-item" href="javascript:void(0);">Download</a>
            <a class="dropdown-item" href="javascript:void(0);">Refresh</a>
            <a class="dropdown-item" href="javascript:void(0);">Share</a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="nav-align-top">
          <ul class="nav nav-tabs nav-fill" role="tablist">
            <li class="nav-item">
              <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                data-bs-target="#navs-justified-new" aria-controls="navs-justified-new"
                aria-selected="true">New</button>
            </li>
            <li class="nav-item">
              <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                data-bs-target="#navs-justified-link-preparing" aria-controls="navs-justified-link-preparing"
                aria-selected="false">Preparing</button>
            </li>
            <li class="nav-item">
              <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                data-bs-target="#navs-justified-link-shipping" aria-controls="navs-justified-link-shipping"
                aria-selected="false">Shipping</button>
            </li>
          </ul>
          <div class="tab-content pb-0">
            <div class="tab-pane fade show active" id="navs-justified-new" role="tabpanel">
              <ul class="timeline timeline-advance timeline-advance mb-2 pb-1">
                <li class="timeline-item ps-4 border-left-dashed">
                  <span class="timeline-indicator timeline-indicator-success">
                    <i class="ti ti-circle-check"></i>
                  </span>
                  <div class="timeline-event ps-0 pb-0">
                    <div class="timeline-header">
                      <small class="text-success text-uppercase fw-semibold">sender</small>
                    </div>
                    <h6 class="mb-0">Myrtle Ullrich</h6>
                    <p class="text-muted mb-0 text-nowrap">101 Boulder, California(CA), 95959</p>
                  </div>
                </li>
                <li class="timeline-item ps-4 border-0">
                  <span class="timeline-indicator timeline-indicator-primary">
                    <i class="ti ti-map-pin"></i>
                  </span>
                  <div class="timeline-event ps-0 pb-0">
                    <div class="timeline-header">
                      <small class="text-primary text-uppercase fw-semibold">Receiver</small>
                    </div>
                    <h6 class="mb-0">Barry Schowalter</h6>
                    <p class="text-muted mb-0 text-nowrap">939 Orange, California(CA),92118</p>
                  </div>
                </li>
              </ul>
              <div class="border-bottom border-bottom-dashed mt-0 mb-4"></div>
              <ul class="timeline timeline-advance mb-0">
                <li class="timeline-item ps-4 border-left-dashed">
                  <span class="timeline-indicator timeline-indicator-success">
                    <i class="ti ti-circle-check"></i>
                  </span>
                  <div class="timeline-event ps-0 pb-0">
                    <div class="timeline-header">
                      <small class="text-success text-uppercase fw-semibold">sender</small>
                    </div>
                    <h6 class="mb-0">Veronica Herman</h6>
                    <p class="text-muted mb-0 text-nowrap">162 Windsor, California(CA), 95492</p>
                  </div>
                </li>
                <li class="timeline-item ps-4 border-0">
                  <span class="timeline-indicator timeline-indicator-primary">
                    <i class="ti ti-map-pin"></i>
                  </span>
                  <div class="timeline-event ps-0 pb-0">
                    <div class="timeline-header">
                      <small class="text-primary text-uppercase fw-semibold">Receiver</small>
                    </div>
                    <h6 class="mb-0">Helen Jacobs</h6>
                    <p class="text-muted mb-0 text-nowrap">487 Sunset, California(CA), 94043</p>
                  </div>
                </li>
              </ul>
            </div>

            <div class="tab-pane fade" id="navs-justified-link-preparing" role="tabpanel">
              <ul class="timeline timeline-advance mb-2 pb-1">
                <li class="timeline-item ps-4 border-left-dashed">
                  <span class="timeline-indicator timeline-indicator-success">
                    <i class="ti ti-circle-check"></i>
                  </span>
                  <div class="timeline-event ps-0 pb-0">
                    <div class="timeline-header">
                      <small class="text-success text-uppercase fw-semibold">sender</small>
                    </div>
                    <h6 class="mb-0">Barry Schowalter</h6>
                    <p class="text-muted mb-0 text-nowrap">939 Orange, California(CA),92118</p>
                  </div>
                </li>
                <li class="timeline-item ps-4 border-0 border-left-dashed">
                  <span class="timeline-indicator timeline-indicator-primary">
                    <i class="ti ti-map-pin"></i>
                  </span>
                  <div class="timeline-event ps-0 pb-0">
                    <div class="timeline-header">
                      <small class="text-primary text-uppercase fw-semibold">Receiver</small>
                    </div>
                    <h6 class="mb-0">Myrtle Ullrich</h6>
                    <p class="text-muted mb-0 text-nowrap">101 Boulder, California(CA), 95959 </p>
                  </div>
                </li>
              </ul>
              <div class="border-bottom border-bottom-dashed mt-0 mb-4"></div>
              <ul class="timeline timeline-advance mb-0">
                <li class="timeline-item ps-4 border-left-dashed">
                  <span class="timeline-indicator timeline-indicator-success">
                    <i class="ti ti-circle-check"></i>
                  </span>
                  <div class="timeline-event ps-0 pb-0">
                    <div class="timeline-header">
                      <small class="text-success text-uppercase fw-semibold">sender</small>
                    </div>
                    <h6 class="mb-0">Veronica Herman</h6>
                    <p class="text-muted mb-0 text-nowrap">162 Windsor, California(CA), 95492</p>
                  </div>
                </li>
                <li class="timeline-item ps-4 border-0">
                  <span class="timeline-indicator timeline-indicator-primary">
                    <i class="ti ti-map-pin"></i>
                  </span>
                  <div class="timeline-event ps-0 pb-0">
                    <div class="timeline-header">
                      <small class="text-primary text-uppercase fw-semibold">Receiver</small>
                    </div>
                    <h6 class="mb-0">Helen Jacobs</h6>
                    <p class="text-muted mb-0 text-nowrap">487 Sunset, California(CA), 94043</p>
                  </div>
                </li>
              </ul>
            </div>
            <div class="tab-pane fade" id="navs-justified-link-shipping" role="tabpanel">
              <ul class="timeline timeline-advance mb-2 pb-1">
                <li class="timeline-item ps-4 border-left-dashed">
                  <span class="timeline-indicator timeline-indicator-success">
                    <i class="ti ti-circle-check"></i>
                  </span>
                  <div class="timeline-event ps-0 pb-0">
                    <div class="timeline-header">
                      <small class="text-success text-uppercase fw-semibold">sender</small>
                    </div>
                    <h6 class="mb-0">Veronica Herman</h6>
                    <p class="text-muted mb-0 text-nowrap">101 Boulder, California(CA), 95959</p>
                  </div>
                </li>
                <li class="timeline-item ps-4 border-0">
                  <span class="timeline-indicator timeline-indicator-primary">
                    <i class="ti ti-map-pin"></i>
                  </span>
                  <div class="timeline-event ps-0 pb-0">
                    <div class="timeline-header">
                      <small class="text-primary text-uppercase fw-semibold">Receiver</small>
                    </div>
                    <h6 class="mb-0">Barry Schowalter</h6>
                    <p class="text-muted mb-0 text-nowrap">939 Orange, California(CA),92118</p>
                  </div>
                </li>
              </ul>
              <div class="border-bottom border-bottom-dashed mt-0 mb-4"></div>
              <ul class="timeline timeline-advance mb-0">
                <li class="timeline-item ps-4 border-left-dashed">
                  <span class="timeline-indicator timeline-indicator-success">
                    <i class="ti ti-circle-check"></i>
                  </span>
                  <div class="timeline-event ps-0 pb-0">
                    <div class="timeline-header">
                      <small class="text-success text-uppercase fw-semibold">sender</small>
                    </div>
                    <h6 class="mb-0">Myrtle Ullrich</h6>
                    <p class="text-muted mb-0 text-nowrap">162 Windsor, California(CA), 95492 </p>
                  </div>
                </li>
                <li class="timeline-item ps-4 border-0">
                  <span class="timeline-indicator timeline-indicator-primary">
                    <i class="ti ti-map-pin"></i>
                  </span>
                  <div class="timeline-event ps-0 pb-0">
                    <div class="timeline-header">
                      <small class="text-primary text-uppercase fw-semibold">Receiver</small>
                    </div>
                    <h6 class="mb-0">Helen Jacobs</h6>
                    <p class="text-muted mb-0 text-nowrap">487 Sunset, California(CA), 94043</p>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--/ Sales by Countries tabs -->

  <!-- Transactions -->
  <div class="col-md-6 col-lg-4 mb-4 mb-lg-0">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between">
        <div class="card-title m-0 me-2">
          <h5 class="m-0 me-2">Transactions</h5>
          <small class="text-muted">Total 58 Transactions done in this Month</small>
        </div>
        <div class="dropdown">
          <button class="btn p-0" type="button" id="transactionID" data-bs-toggle="dropdown" aria-haspopup="true"
            aria-expanded="false">
            <i class="ti ti-dots-vertical ti-sm text-muted"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end" aria-labelledby="transactionID">
            <a class="dropdown-item" href="javascript:void(0);">Last 28 Days</a>
            <a class="dropdown-item" href="javascript:void(0);">Last Month</a>
            <a class="dropdown-item" href="javascript:void(0);">Last Year</a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <ul class="p-0 m-0">
          <li class="d-flex mb-3 pb-1 align-items-center">
            <div class="badge bg-label-primary me-3 rounded p-2">
              <i class="ti ti-wallet ti-sm"></i>
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <h6 class="mb-0">Wallet</h6>
                <small class="text-muted d-block">Starbucks</small>
              </div>
              <div class="user-progress d-flex align-items-center gap-1">
                <h6 class="mb-0 text-danger">-$75</h6>
              </div>
            </div>
          </li>
          <li class="d-flex mb-3 pb-1 align-items-center">
            <div class="badge bg-label-success rounded me-3 p-2">
              <i class="ti ti-browser-check ti-sm"></i>
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <h6 class="mb-0">Bank Transfer</h6>
                <small class="text-muted d-block">Add Money</small>
              </div>
              <div class="user-progress d-flex align-items-center gap-1">
                <h6 class="mb-0 text-success">+$480</h6>
              </div>
            </div>
          </li>
          <li class="d-flex mb-3 pb-1 align-items-center">
            <div class="badge bg-label-danger rounded me-3 p-2">
              <i class="ti ti-brand-paypal ti-sm"></i>
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <h6 class="mb-0">Paypal</h6>
                <small class="text-muted d-block mb-1">Client Payment</small>
              </div>
              <div class="user-progress d-flex align-items-center gap-1">
                <h6 class="mb-0 text-success">+$268</h6>
              </div>
            </div>
          </li>
          <li class="d-flex mb-3 pb-1 align-items-center">
            <div class="badge bg-label-secondary me-3 rounded p-2">
              <i class="ti ti-credit-card ti-sm"></i>
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <h6 class="mb-0">Master Card</h6>
                <small class="text-muted d-block mb-1">Ordered iPhone 13</small>
              </div>
              <div class="user-progress d-flex align-items-center gap-1">
                <h6 class="mb-0 text-danger">-$699</h6>
              </div>
            </div>
          </li>
          <li class="d-flex mb-3 pb-1 align-items-center">
            <div class="badge bg-label-info me-3 rounded p-2">
              <i class="ti ti-currency-dollar ti-sm"></i>
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <h6 class="mb-0">Bank Transactions</h6>
                <small class="text-muted d-block mb-1">Refund</small>
              </div>
              <div class="user-progress d-flex align-items-center gap-1">
                <h6 class="mb-0 text-success">+$98</h6>
              </div>
            </div>
          </li>
          <li class="d-flex mb-3 pb-1 align-items-center">
            <div class="badge bg-label-danger me-3 rounded p-2">
              <i class="ti ti-brand-paypal ti-sm"></i>
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <h6 class="mb-0">Paypal</h6>
                <small class="text-muted d-block mb-1">Client Payment</small>
              </div>
              <div class="user-progress d-flex align-items-center gap-1">
                <h6 class="mb-0 text-success">+$126</h6>
              </div>
            </div>
          </li>
          <li class="d-flex align-items-center">
            <div class="badge bg-label-success me-3 rounded p-2">
              <i class="ti ti-browser-check ti-sm"></i>
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <h6 class="mb-0">Bank Transfer</h6>
                <small class="text-muted d-block mb-1">Pay Office Rent</small>
              </div>
              <div class="user-progress d-flex align-items-center gap-1">
                <h6 class="mb-0 text-danger">-$1290</h6>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
  <!--/ Transactions -->

  <!-- Invoice table -->
  <div class="col-lg-8">
    <div class="card h-100">
      <div class="table-responsive card-datatable">
        <table class="table datatable-invoice border-top">
          <thead>
            <tr>
              <th></th>
              <th>ID</th>
              <th><i class='ti ti-trending-up'></i></th>
              <th>Total</th>
              <th>Issued Date</th>
              <th>Invoice Status</th>
              <th>Actions</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
  <!-- /Invoice table -->
</div>

@endsection