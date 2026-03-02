@extends('layouts.app')

@section('title', 'PMS Dashboard')
@section('header', 'Project Management System Dashboard')

@section('styles')
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
</style>
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
<div class="container-fluid">
  <!-- Statistics Row -->
  <div class="row mb-4">
    <div class="col-md-3 mb-4">
      <div class="card stat-card stat-card-primary h-100">
        <div class="card-body">
          <h5 class="card-title">Total Projects</h5>
          <h2 class="card-text">{{ $data['total_projects'] }}</h2>
          <p class="text-muted mb-0">All projects in the system</p>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-4">
      <div class="card stat-card stat-card-success h-100">
        <div class="card-body">
          <h5 class="card-title">Active Projects</h5>
          <h2 class="card-text">{{ $data['active_projects'] }}</h2>
          <p class="text-muted mb-0">Currently ongoing</p>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-4">
      <div class="card stat-card stat-card-info h-100">
        <div class="card-body">
          <h5 class="card-title">Completed Projects</h5>
          <h2 class="card-text">{{ $data['completed_projects'] }}</h2>
          <p class="text-muted mb-0">Successfully delivered</p>
        </div>
      </div>
    </div>

    <!-- Role-specific Stat Card -->
    @if($user->hasRole('team_member'))
    <div class="col-md-3 mb-4">
      <div class="card stat-card stat-card-warning h-100">
        <div class="card-body">
          <h5 class="card-title">Your Tasks</h5>
          <h2 class="card-text">{{ $data['assigned_tasks'] }}</h2>
          <p class="text-muted mb-0">Assigned to you</p>
        </div>
      </div>
    </div>
    @endif

    @if($user->hasRole('team_lead'))
    <div class="col-md-3 mb-4">
      <div class="card stat-card stat-card-warning h-100">
        <div class="card-body">
          <h5 class="card-title">Team Projects</h5>
          <h2 class="card-text">{{ $data['team_projects'] }}</h2>
          <p class="text-muted mb-0">You're leading</p>
        </div>
      </div>
    </div>
    @endif

    @if($user->hasRole('director'))
    <div class="col-md-3 mb-4">
      <div class="card stat-card stat-card-danger h-100">
        <div class="card-body">
          <h5 class="card-title">Pending Approvals</h5>
          <h2 class="card-text">{{ $data['pending_approvals'] }}</h2>
          <p class="text-muted mb-0">Waiting for your review</p>
        </div>
      </div>
    </div>
    @endif

    @if($user->hasRole('finance'))
    <div class="col-md-3 mb-4">
      <div class="card stat-card stat-card-danger h-100">
        <div class="card-body">
          <h5 class="card-title">Pending Invoices</h5>
          <h2 class="card-text">{{ $data['pending_invoices'] }}</h2>
          <p class="text-muted mb-0">Awaiting processing</p>
        </div>
      </div>
    </div>
    @endif
  </div>

  <!-- Main Content Row -->
  <div class="row">
    <!-- Left Column -->
    <div class="col-lg-8">
      {{-- @if($user->hasRole('team_member')) --}}
      <!-- Team Member Widgets -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Your Tasks</h5>
        </div>
        <div class="card-body">
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
      {{-- @endif --}}

      {{-- @if($user->hasRole('team_lead')) --}}
      <!-- Team Lead Widgets -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Team Progress</h5>
        </div>
        <div class="card-body">
          @php
          $projects = Project::whereHas('teamMembers', function($q) use ($user) {
          $q->where('user_id', $user->id)->where('role', 'lead');
          })->withCount(['milestones', 'tasks'])->get();
          @endphp

          @foreach($projects as $project)
          <div class="mb-3">
            <h6>{{ $project->title }}</h6>
            <div class="progress mb-1" style="height: 20px;">
              <div class="progress-bar" role="progressbar" style="width: {{ $project->completion_percentage }}%"
                aria-valuenow="{{ $project->completion_percentage }}" aria-valuemin="0" aria-valuemax="100">
                {{ $project->completion_percentage }}%
              </div>
            </div>
            <div class="d-flex justify-content-between small">
              <span>{{ $project->milestones_count }} Milestones</span>
              <span>{{ $project->tasks_count }} Tasks</span>
            </div>
          </div>
          @endforeach
        </div>
      </div>
      {{-- @endif --}}

      @if($user->hasRole('director') || $user->hasRole('pac_member'))
      <!-- Director/PAC Member Widgets -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Approvals Dashboard</h5>
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
      @endif

      @if($user->hasRole('finance'))
      <!-- Finance Department Widgets -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Financial Overview</h5>
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
      @endif

      {{-- @if($user->hasRole('faculty')) --}}
      <!-- Faculty/Project Investigator Widgets -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Your Projects</h5>
        </div>
        <div class="card-body">
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
      {{-- @endif --}}
    </div>

    <!-- Right Column -->
    <div class="col-lg-4">
      <!-- Quick Links Card -->
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

      <!-- Calendar Widget -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Project Calendar</h5>
        </div>
        <div class="card-body">
          <div id="calendar"></div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">Recent Activity</h5>
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
          <div class="timeline">
            @foreach($activities as $activity)
            <div class="timeline-item">
              <div class="timeline-item-marker">
                <div
                  class="timeline-item-marker-indicator bg-{{ $activity->event == 'created' ? 'success' : ($activity->event == 'updated' ? 'info' : 'danger') }}">
                </div>
              </div>
              <div class="timeline-item-content">
                <div class="d-flex justify-content-between">
                  <strong>{{ $activity->description }}</strong>
                  <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                </div>
                <p class="mb-0 small">{{ class_basename($activity->subject_type) }}: {{ $activity->subject->title ??
                  'N/A' }}</p>
              </div>
            </div>
            @endforeach
          </div>
          @else
          <p>No recent activity found.</p>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
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