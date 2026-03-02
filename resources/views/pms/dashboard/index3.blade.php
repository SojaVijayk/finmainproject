@extends('layouts/layoutMaster')

@section('title', 'PMS Dashboard')

@php
use \App\Models\PMS\Project;
use \App\Models\PMS\Task;
use \App\Models\PMS\Invoice;
use \App\Models\PMS\Requirement;
use \App\Models\PMS\ActivityLog;
use \App\Models\PMS\Proposal;
@endphp

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/apex-charts/apex-charts.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}" />
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
<style>
  .stat-card {
    border-left: 4px solid;
    transition: all 0.3s ease;
  }

  .stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
  }

  .stat-card-primary {
    border-left-color: #3b82f6;
  }

  .stat-card-success {
    border-left-color: #10b981;
  }

  .stat-card-danger {
    border-left-color: #ef4444;
  }

  .stat-card-warning {
    border-left-color: #f59e0b;
  }

  .stat-card-info {
    border-left-color: #0ea5e9;
  }

  .quick-links .card {
    transition: all 0.3s ease;
  }

  .quick-links .card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
  }

  .task-item {
    border-left: 3px solid;
    padding-left: 10px;
    margin-bottom: 10px;
  }

  .task-priority-high {
    border-left-color: #ef4444;
  }

  .task-priority-medium {
    border-left-color: #f59e0b;
  }

  .task-priority-low {
    border-left-color: #10b981;
  }

  #calendar {
    height: 400px;
  }

  .progress-thin {
    height: 6px;
  }

  .activity-timeline {
    position: relative;
    padding-left: 24px;
  }

  .activity-timeline::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 0;
    bottom: 0;
    width: 2px;
    background-color: #e9ecef;
  }

  .activity-item {
    position: relative;
    padding-bottom: 16px;
  }

  .activity-item::before {
    content: '';
    position: absolute;
    left: -24px;
    top: 4px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background-color: #0ea5e9;
    border: 2px solid #fff;
  }

  .activity-item-success::before {
    background-color: #10b981;
  }

  .activity-item-danger::before {
    background-color: #ef4444;
  }

  .activity-item-warning::before {
    background-color: #f59e0b;
  }
</style>
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/apex-charts/apexcharts.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
@endsection

@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Initialize Calendar
    const calendarEl = document.getElementById('calendar');
    if (calendarEl) {
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: [
                @foreach(Project::all() as $project)
                {
                    title: '{{ $project->title }}',
                    start: '{{ $project->start_date->format("Y-m-d") }}',
                    end: '{{ $project->end_date->format("Y-m-d") }}',
                    url: '{{ route("pms.projects.show", $project->id) }}',
                    backgroundColor: '#3b82f6',
                    borderColor: '#3b82f6'
                },
                @endforeach

                @foreach(Task::whereHas('assignments', function($q) { $q->where('user_id', auth()->id()); })->get() as $task)
                {
                    title: '{{ $task->name }}',
                    start: '{{ $task->start_date->format("Y-m-d") }}',
                    end: '{{ $task->end_date->format("Y-m-d") }}',
                    url: '{{ route("pms.tasks.show", [$task->milestone->project_id, $task->milestone_id, $task->id]) }}',
                    backgroundColor: '#f59e0b',
                    borderColor: '#f59e0b'
                },
                @endforeach
            ]
        });
        calendar.render();
    }

    // Initialize Charts for Finance Dashboard
    @if($user->hasRole('finance'))
    // Invoice Status Chart
    const invoiceCtx = document.getElementById('invoiceStatusChart').getContext('2d');
    const invoiceChart = new Chart(invoiceCtx, {
        type: 'doughnut',
        data: {
            labels: ['Paid', 'Pending', 'Overdue'],
            datasets: [{
                data: [
                    {{ Invoice::where('status', Invoice::STATUS_PAID)->count() }},
                    {{ Invoice::where('status', Invoice::STATUS_SENT)->count() }},
                    {{ Invoice::where('status', Invoice::STATUS_OVERDUE)->count() }}
                ],
                backgroundColor: [
                    '#10b981',
                    '#0ea5e9',
                    '#ef4444'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(revenueCtx, {
        type: 'bar',
        data: {
            labels: @json(Project::orderBy('revenue', 'desc')->limit(5)->pluck('title')),
            datasets: [{
                label: 'Revenue (₹)',
                data: @json(Project::orderBy('revenue', 'desc')->limit(5)->pluck('revenue')),
                backgroundColor: '#3b82f6',
                borderColor: '#3b82f6',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₹' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '₹' + context.raw.toLocaleString();
                        }
                    }
                }
            }
        }
    });
    @endif
});
</script>
@endsection

@section('content')
<div class="row">
  <!-- Congratulations Card -->
  <div class="col-xl-4 mb-4 col-lg-5 col-12">
    <div class="card">
      <div class="d-flex align-items-end row">
        <div class="col-7">
          <div class="card-body text-nowrap">
            <h5 class="card-title mb-0">Welcome {{ Auth::user()->name }}! 👋</h5>
            <p class="mb-2">Project Management System Dashboard</p>
            <h4 class="text-primary mb-1">{{ $data['total_projects'] }} Projects</h4>
            {{-- <a href="{{ route('pms.projects.list') }}" class="btn btn-primary">View Projects</a> --}}
          </div>
        </div>
        <div class="col-5 text-center text-sm-left">
          <div class="card-body pb-0 px-0 px-md-4">
            <img src="{{asset('assets/img/illustrations/card-advance-sale.png')}}" height="140" alt="view sales">
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--/ Congratulations Card -->

  <!-- Statistics -->
  <div class="col-xl-8 mb-4 col-lg-7 col-12">
    <div class="card h-100">
      <div class="card-header">
        <div class="d-flex justify-content-between mb-3">
          <h5 class="card-title mb-0">Project Statistics</h5>
          <small class="text-muted">Updated {{ now()->diffForHumans() }}</small>
        </div>
      </div>
      <div class="card-body">
        <div class="row gy-3">
          <div class="col-md-3 col-6">
            <div class="d-flex align-items-center">
              <div class="badge rounded-pill bg-label-primary me-3 p-2"><i class="ti ti-layout-kanban ti-sm"></i></div>
              <div class="card-info">
                <h5 class="mb-0">{{ $data['total_projects'] }}</h5>
                <small>Total Projects</small>
              </div>
            </div>
          </div>
          <div class="col-md-3 col-6">
            <div class="d-flex align-items-center">
              <div class="badge rounded-pill bg-label-success me-3 p-2"><i class="ti ti-rocket ti-sm"></i></div>
              <div class="card-info">
                <h5 class="mb-0">{{ $data['active_projects'] }}</h5>
                <small>Active Projects</small>
              </div>
            </div>
          </div>
          <div class="col-md-3 col-6">
            <div class="d-flex align-items-center">
              <div class="badge rounded-pill bg-label-info me-3 p-2"><i class="ti ti-check ti-sm"></i></div>
              <div class="card-info">
                <h5 class="mb-0">{{ $data['completed_projects'] }}</h5>
                <small>Completed</small>
              </div>
            </div>
          </div>
          <div class="col-md-3 col-6">
            <div class="d-flex align-items-center">
              <div class="badge rounded-pill bg-label-warning me-3 p-2"><i class="ti ti-list-check ti-sm"></i></div>
              <div class="card-info">
                <h5 class="mb-0">{{ $data['assigned_tasks'] ?? 0 }}</h5>
                <small>Your Tasks</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--/ Statistics -->

  <!-- Quick Stats Row -->
  <div class="col-xl-4 col-12">
    <div class="row">
      <!-- Active Projects -->
      <div class="col-xl-6 mb-4 col-md-3 col-6">
        <div class="card">
          <div class="card-header pb-0">
            <h5 class="card-title mb-0">{{ $data['active_projects'] }}</h5>
            <small class="text-muted">Active Projects</small>
          </div>
          <div class="card-body">
            <div id="activeProjectsChart"></div>
            <div class="mt-md-2 text-center mt-lg-3 mt-3">
              <small class="text-muted mt-3">{{ $data['completed_projects'] }} completed</small>
            </div>
          </div>
        </div>
      </div>
      <!--/ Active Projects -->

      <!-- Your Tasks -->
      <div class="col-xl-6 mb-4 col-md-3 col-6">
        <div class="card">
          <div class="card-header pb-0">
            <h5 class="card-title mb-0">Tasks</h5>
            <small class="text-muted">Assigned to You</small>
          </div>
          <div class="card-body">
            <div id="yourTasksChart"></div>
            <div class="d-flex justify-content-between align-items-center mt-3 gap-3">
              <h4 class="mb-0">{{ $data['assigned_tasks'] ?? 0 }}</h4>
              {{-- <a href="{{ route('pms.tasks.my-tasks') }}" class="btn btn-sm btn-outline-primary">View</a> --}}
            </div>
          </div>
        </div>
      </div>
      <!--/ Your Tasks -->

      <!-- Timesheet Summary -->
      <div class="col-xl-12 mb-4 col-md-6">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div class="d-flex flex-column">
                <div class="card-title mb-auto">
                  <h5 class="mb-1 text-nowrap">Today's Timesheet</h5>
                  <small>Hours Logged</small>
                </div>
                <div class="chart-statistics">
                  <h3 class="card-title mb-1">{{ gmdate("H:i", $data['today_timesheet'] * 60) }}</h3>
                  <a href="{{ route('pms.timesheets.index') }}" class="btn btn-sm btn-primary">Log Time</a>
                </div>
              </div>
              <div id="timesheetChart"></div>
            </div>
          </div>
        </div>
      </div>
      <!--/ Timesheet Summary -->
    </div>
  </div>

  <!-- Revenue Report -->
  <div class="col-12 col-xl-8 mb-4 col-lg-7">
    <div class="card">
      <div class="card-header pb-3">
        <h5 class="m-0 me-2 card-title">Project Progress</h5>
      </div>
      <div class="card-body">
        <div class="row row-bordered g-0">
          <div class="col-md-8">
            <div id="projectProgressChart"></div>
          </div>
          <div class="col-md-4">
            <div class="text-center mt-4">
              <div class="dropdown">
                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="progressFilter"
                  data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  All Projects
                </button>
                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="progressFilter">
                  <a class="dropdown-item" href="javascript:void(0);">Active</a>
                  <a class="dropdown-item" href="javascript:void(0);">Completed</a>
                  <a class="dropdown-item" href="javascript:void(0);">Your Projects</a>
                </div>
              </div>
            </div>
            <h3 class="text-center pt-4 mb-0">{{ round(Project::avg('completion_percentage'), 1) }}%</h3>
            <p class="mb-4 text-center"><span class="fw-semibold">Average Completion</span></p>
            <div class="px-3">
              <div id="completionChart"></div>
            </div>
            <div class="text-center mt-4">
              <button type="button" class="btn btn-primary">View Details</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--/ Revenue Report -->

  <!-- Your Tasks -->
  <div class="col-xl-4 col-lg-5 col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between">
        <div class="card-title mb-0">
          <h5 class="m-0 me-2">Your Tasks</h5>
          <small class="text-muted">Recent Assigned Tasks</small>
        </div>
        <div class="dropdown">
          <button class="btn p-0" type="button" id="taskReports" data-bs-toggle="dropdown" aria-haspopup="true"
            aria-expanded="false">
            <i class="ti ti-dots-vertical ti-sm text-muted"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end" aria-labelledby="taskReports">
            {{-- <a class="dropdown-item" href="{{ route('pms.tasks.my-tasks') }}">View All</a> --}}
            <a class="dropdown-item" href="javascript:void(0);">Filter</a>
          </div>
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
        <ul class="p-0 m-0">
          @foreach($tasks as $task)
          <li class="d-flex mb-3">
            <div class="avatar flex-shrink-0 me-3">
              <span
                class="avatar-initial rounded bg-label-{{ $task->priority_name == 'High' ? 'danger' : ($task->priority_name == 'Medium' ? 'warning' : 'success') }}">
                <i class='ti ti-{{ $task->priority_name == ' High' ? 'alert-triangle' : ($task->priority_name ==
                  'Medium' ? 'alert-circle' : 'check') }} ti-sm'></i>
              </span>
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <h6 class="mb-0">{{ $task->name }}</h6>
                <small class="text-muted">{{ $task->milestone->project->title }}</small>
              </div>
              <div class="user-progress">
                <small class="text-muted">{{ $task->end_date->format('M d') }}</small>
              </div>
            </div>
          </li>
          @endforeach
        </ul>
        @else
        <div class="alert alert-info">No tasks assigned to you currently.</div>
        @endif
      </div>
    </div>
  </div>
  <!--/ Your Tasks -->

  <!-- Quick Actions -->
  <div class="col-md-6 col-xl-4 mb-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between">
        <div class="card-title m-0 me-2">
          <h5 class="m-0 me-2">Quick Actions</h5>
          <small class="text-muted">Frequently used actions</small>
        </div>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-6">
            <div class="card cursor-pointer">
              <div class="card-body text-center p-3">
                <div class="avatar avatar-md mb-3">
                  <span class="avatar-initial rounded bg-label-primary">
                    <i class="ti ti-file-text ti-md"></i>
                  </span>
                </div>
                <h6>New Requirement</h6>
                <a href="{{ route('pms.requirements.create') }}" class="stretched-link"></a>
              </div>
            </div>
          </div>
          <div class="col-6">
            <div class="card cursor-pointer">
              <div class="card-body text-center p-3">
                <div class="avatar avatar-md mb-3">
                  <span class="avatar-initial rounded bg-label-success">
                    <i class="ti ti-clock ti-md"></i>
                  </span>
                </div>
                <h6>Timesheet</h6>
                <a href="{{ route('pms.timesheets.index') }}" class="stretched-link"></a>
              </div>
            </div>
          </div>
          <div class="col-6">
            <div class="card cursor-pointer">
              <div class="card-body text-center p-3">
                <div class="avatar avatar-md mb-3">
                  <span class="avatar-initial rounded bg-label-info">
                    <i class="ti ti-list ti-md"></i>
                  </span>
                </div>
                <h6>My Tasks</h6>
                {{-- <a href="{{ route('pms.tasks.my-tasks') }}" class="stretched-link"></a> --}}
              </div>
            </div>
          </div>
          <div class="col-6">
            <div class="card cursor-pointer">
              <div class="card-body text-center p-3">
                <div class="avatar avatar-md mb-3">
                  <span class="avatar-initial rounded bg-label-warning">
                    <i class="ti ti-report-analytics ti-md"></i>
                  </span>
                </div>
                <h6>Reports</h6>
                <a href="{{ route('pms.reports.project-status') }}" class="stretched-link"></a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--/ Quick Actions -->

  <!-- Recent Activity -->
  <div class="col-md-6 col-xl-4 mb-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between pb-2 mb-1">
        <div class="card-title mb-1">
          <h5 class="m-0 me-2">Recent Activity</h5>
          <small class="text-muted">Your latest actions</small>
        </div>
      </div>
      <div class="card-body">
        @php
        $activities = \App\Models\ActivityLog::where('user_id', $user->id)
        ->orWhere(function($q) use ($user) {
        if ($user->hasRole('team_lead')) {
        $projectIds = Project::whereHas('teamMembers', function($q) use ($user) {
        $q->where('user_id', $user->id)->where('role', 'lead');
        })->pluck('id');
        $q->whereIn('subject_id', $projectIds)
        ->where('subject_type', Project::class);
        }
        })
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();
        @endphp

        @if($activities->count() > 0)
        <div class="activity-timeline">
          @foreach($activities as $activity)
          <div
            class="activity-item {{ $activity->event == 'created' ? 'activity-item-success' : ($activity->event == 'deleted' ? 'activity-item-danger' : 'activity-item-warning') }}">
            <div class="d-flex justify-content-between">
              <p class="mb-1">{{ $activity->description }}</p>
              <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
            </div>
            <p class="mb-2 small text-muted">{{ class_basename($activity->subject_type) }}: {{ $activity->subject->title
              ?? 'N/A' }}</p>
          </div>
          @endforeach
        </div>
        @else
        <div class="alert alert-info">No recent activity found.</div>
        @endif
      </div>
    </div>
  </div>
  <!--/ Recent Activity -->

  <!-- Project Calendar -->
  <div class="col-md-6 col-lg-4 mb-4 mb-lg-0">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between">
        <div class="card-title m-0 me-2">
          <h5 class="m-0 me-2">Project Calendar</h5>
          <small class="text-muted">Upcoming milestones and tasks</small>
        </div>
      </div>
      <div class="card-body">
        <div id="calendar"></div>
      </div>
    </div>
  </div>
  <!--/ Project Calendar -->

  <!-- Team Progress -->
  @if($user->hasRole('team_lead'))
  <div class="col-lg-8">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title mb-0">Team Progress</h5>
      </div>
      <div class="card-body">
        @php
        $projects = Project::whereHas('teamMembers', function($q) use ($user) {
        $q->where('user_id', $user->id)->where('role', 'lead');
        })->withCount(['milestones', 'tasks'])->get();
        @endphp

        @foreach($projects as $project)
        <div class="mb-4">
          <div class="d-flex justify-content-between mb-2">
            <h6 class="mb-0">{{ $project->title }}</h6>
            <span class="text-muted">{{ $project->completion_percentage }}%</span>
          </div>
          <div class="progress progress-thin mb-2">
            <div class="progress-bar" role="progressbar" style="width: {{ $project->completion_percentage }}%"
              aria-valuenow="{{ $project->completion_percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
          </div>
          <div class="d-flex justify-content-between small text-muted">
            <span>{{ $project->milestones_count }} Milestones</span>
            <span>{{ $project->tasks_count }} Tasks</span>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
  @endif

  <!-- Pending Approvals -->
  @if($user->hasRole('director') || $user->hasRole('pac_member'))
  <div class="col-lg-8">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title mb-0">Pending Approvals</h5>
      </div>
      <div class="card-body">
        <ul class="nav nav-tabs" id="approvalsTab" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="requirements-tab" data-bs-toggle="tab" data-bs-target="#requirements"
              type="button" role="tab">Requirements</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="proposals-tab" data-bs-toggle="tab" data-bs-target="#proposals" type="button"
              role="tab">Proposals</button>
          </li>
        </ul>
        <div class="tab-content" id="approvalsTabContent">
          <div class="tab-pane fade show active" id="requirements" role="tabpanel">
            @php
            $requirements = Requirement::whereIn('status', [
            Requirement::STATUS_SENT_TO_DIRECTOR,
            Requirement::STATUS_SENT_TO_PAC
            ])->with(['category', 'client'])->get();
            @endphp

            @if($requirements->count() > 0)
            <div class="table-responsive mt-3">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Temp No</th>
                    <th>Client</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($requirements as $requirement)
                  <tr>
                    <td>{{ $requirement->temp_no }}</td>
                    <td>{{ $requirement->client->client_name }}</td>
                    <td>{{ $requirement->category->name }}</td>
                    <td>
                      <span class="badge bg-{{ $requirement->status_badge_color }}">
                        {{ $requirement->status_name }}
                      </span>
                    </td>
                    <td>
                      <a href="{{ route('pms.requirements.show', $requirement->id) }}" class="btn btn-sm btn-info">
                        <i class="fas fa-eye"></i> Review
                      </a>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            @else
            <div class="alert alert-info mt-3">No requirements pending approval</div>
            @endif
          </div>
          <div class="tab-pane fade" id="proposals" role="tabpanel">
            @php
            $proposals = Proposal::where('status', Proposal::STATUS_SENT_TO_DIRECTOR)
            ->with(['requirement.client'])->get();
            @endphp

            @if($proposals->count() > 0)
            <div class="table-responsive mt-3">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Requirement</th>
                    <th>Client</th>
                    <th>Budget</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($proposals as $proposal)
                  <tr>
                    <td>{{ $proposal->requirement->temp_no }}</td>
                    <td>{{ $proposal->requirement->client->client_name }}</td>
                    <td>₹{{ number_format($proposal->budget, 2) }}</td>
                    <td>
                      <span class="badge bg-{{ $proposal->status_badge_color }}">
                        {{ $proposal->status_name }}
                      </span>
                    </td>
                    <td>
                      <a href="{{ route('pms.proposals.show', $proposal->id) }}" class="btn btn-sm btn-info">
                        <i class="fas fa-eye"></i> Review
                      </a>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            @else
            <div class="alert alert-info mt-3">No proposals pending approval</div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
  @endif

  <!-- Financial Overview -->
  @if($user->hasRole('finance'))
  <div class="col-lg-8">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title mb-0">Financial Overview</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <h6>Invoice Status</h6>
            <canvas id="invoiceStatusChart" height="200"></canvas>
          </div>
          <div class="col-md-6">
            <h6>Revenue by Project</h6>
            <canvas id="revenueChart" height="200"></canvas>
          </div>
        </div>
        <div class="mt-3">
          <a href="{{ route('pms.reports.financial') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-chart-bar"></i> View Financial Reports
          </a>
        </div>
      </div>
    </div>
  </div>
  @endif
</div>
@endsection