@extends('layouts/layoutMaster')

@section('title', 'PMS Dashboard')

@php
use \App\Models\PMS\Project;
use \App\Models\PMS\Task;
use \App\Models\PMS\Invoice;
use \App\Models\PMS\Requirement;
use \App\Models\PMS\ActivityLog;
use \App\Models\PMS\Proposal;
$user = Auth::user();
// PROJECT LISTING BASED ON ROLE
if ($user->hasRole('director')) {
// Director sees ALL projects
$projects = Project::with(['investigator', 'teamMembers'])->get();
} else {
// Investigator or Team Member
$projects = Project::with(['investigator', 'teamMembers'])
->where(function ($q) use ($user) {
$q->where('project_investigator_id', $user->id)
->orWhereHas('teamMembers', function ($tq) use ($user) {
$tq->where('user_id', $user->id);
});
})
->get();
}


@endphp
@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/apex-charts/apex-charts.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}" />

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
<style>
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

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/apex-charts/apexcharts.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
<script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>


@endsection

@section('page-script')
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
                @foreach($projects as $project)
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

// Bulk Actions Functionality
document.addEventListener('DOMContentLoaded', function() {
    // State management
    let selectedItems = {
        requirements: [],
        proposals: []
    };

    let currentAction = '';
    let currentType = '';

    // Initialize bulk actions
    setupBulkActionEventListeners();

    function setupBulkActionEventListeners() {
        // Select all checkboxes
        const selectAllRequirements = document.getElementById('selectAllRequirements');
        const selectAllProposals = document.getElementById('selectAllProposals');

        if (selectAllRequirements) {
            selectAllRequirements.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.requirement-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                    toggleRequirementSelection(checkbox.value, this.checked);
                });
                updateBulkActionsPanel();
            });
        }

        if (selectAllProposals) {
            selectAllProposals.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.proposal-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                    toggleProposalSelection(checkbox.value, this.checked);
                });
                updateBulkActionsPanel();
            });
        }

        // Individual checkbox changes
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('requirement-checkbox')) {
                toggleRequirementSelection(e.target.value, e.target.checked);
                updateBulkActionsPanel();
                updateSelectAllCheckbox('requirements');
            }

            if (e.target.classList.contains('proposal-checkbox')) {
                toggleProposalSelection(e.target.value, e.target.checked);
                updateBulkActionsPanel();
                updateSelectAllCheckbox('proposals');
            }
        });

        // Bulk action buttons
        document.getElementById('bulkApproveBtn').addEventListener('click', function() {
            showBulkActionModal('approve');
        });

        document.getElementById('bulkSendToPacBtn').addEventListener('click', function() {
            showBulkActionModal('send_to_pac');
        });

        document.getElementById('bulkRejectBtn').addEventListener('click', function() {
            showBulkActionModal('reject');
        });

        document.getElementById('clearSelectionBtn').addEventListener('click', function() {
            clearAllSelections();
        });

        // Confirm bulk action
        document.getElementById('confirmBulkAction').addEventListener('click', function() {
            performBulkAction();
        });

        // Tab change event
        document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', function() {
                updateBulkActionsPanel();
            });
        });
    }

    // Toggle requirement selection
    function toggleRequirementSelection(id, isSelected) {
        id = parseInt(id);
        if (isSelected) {
            if (!selectedItems.requirements.includes(id)) {
                selectedItems.requirements.push(id);
            }
        } else {
            selectedItems.requirements = selectedItems.requirements.filter(item => item !== id);
        }
    }

    // Toggle proposal selection
    function toggleProposalSelection(id, isSelected) {
        id = parseInt(id);
        if (isSelected) {
            if (!selectedItems.proposals.includes(id)) {
                selectedItems.proposals.push(id);
            }
        } else {
            selectedItems.proposals = selectedItems.proposals.filter(item => item !== id);
        }
    }

    // Update select all checkbox state
    function updateSelectAllCheckbox(type) {
        const selectAllCheckbox = document.getElementById(`selectAll${type.charAt(0).toUpperCase() + type.slice(1)}`);
        if (selectAllCheckbox) {
            const checkboxes = document.querySelectorAll(`.${type}-checkbox`);
            const allChecked = checkboxes.length > 0 && Array.from(checkboxes).every(checkbox => checkbox.checked);
            selectAllCheckbox.checked = allChecked;
        }
    }

    // Update bulk actions panel visibility and selected count
    function updateBulkActionsPanel() {
        const container = document.getElementById('bulkActionsContainer');
        const selectedCountElement = document.getElementById('selectedCount');

        // Determine which tab is active and get the count
        const activeTab = document.querySelector('.tab-pane.active');
        let selectedCount = 0;
        let type = '';

        if (activeTab && activeTab.id === 'requirements') {
            selectedCount = selectedItems.requirements.length;
            type = 'requirements';
        } else if (activeTab && activeTab.id === 'proposals') {
            selectedCount = selectedItems.proposals.length;
            type = 'proposals';
        }

        // Update UI
        if (selectedCount > 0) {
            container.style.display = 'block';
            selectedCountElement.textContent = `${selectedCount} ${type} selected`;
        } else {
            container.style.display = 'none';
        }
    }

    // Show bulk action confirmation modal
    function showBulkActionModal(action) {
        const activeTab = document.querySelector('.tab-pane.active');
        let selectedCount = 0;
        let type = '';

        if (activeTab && activeTab.id === 'requirements') {
            selectedCount = selectedItems.requirements.length;
            type = 'requirements';
        } else if (activeTab && activeTab.id === 'proposals') {
            selectedCount = selectedItems.proposals.length;
            type = 'proposals';
        }

        if (selectedCount === 0) {
            alert('Please select at least one item to perform this action.');
            return;
        }

        currentAction = action;
        currentType = type;

        const modal = new bootstrap.Modal(document.getElementById('bulkActionModal'));
        const actionText = getActionText(action);
        document.getElementById('bulkActionMessage').textContent =
            `Are you sure you want to ${actionText} ${selectedCount} ${type}?`;

        modal.show();
    }

    // Get action text for display
    function getActionText(action) {
        switch(action) {
            case 'approve': return 'approve';
            case 'send_to_pac': return 'send to PAC';
            case 'reject': return 'reject';
            default: return 'perform this action on';
        }
    }

    // Perform the bulk action
    function performBulkAction() {
        const actionText = getActionText(currentAction);
        const selectedIds = selectedItems[currentType];

        // Create form data
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('action', currentAction);
        formData.append('type', currentType);
        selectedIds.forEach(id => {
            formData.append('ids[]', id);
        });

        // Show loading state
        const confirmBtn = document.getElementById('confirmBulkAction');
        const originalText = confirmBtn.innerHTML;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        confirmBtn.disabled = true;

        // Make AJAX request
        fetch('{{ route("pms.requirements.bulk-actions.process") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const modal = bootstrap.Modal.getInstance(document.getElementById('bulkActionModal'));
            modal.hide();

            if (data.success) {
                // Show success message
                showAlert('success', data.message || `Successfully ${actionText}d ${selectedIds.length} ${currentType}.`);

                // Reload the page to reflect changes
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showAlert('error', data.message || 'An error occurred while processing your request.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'An error occurred while processing your request.');
        })
        .finally(() => {
            // Reset button state
            confirmBtn.innerHTML = originalText;
            confirmBtn.disabled = false;
        });
    }

    // Clear all selections
    function clearAllSelections() {
        selectedItems.requirements = [];
        selectedItems.proposals = [];

        document.querySelectorAll('.requirement-checkbox, .proposal-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });

        const selectAllRequirements = document.getElementById('selectAllRequirements');
        const selectAllProposals = document.getElementById('selectAllProposals');

        if (selectAllRequirements) selectAllRequirements.checked = false;
        if (selectAllProposals) selectAllProposals.checked = false;

        updateBulkActionsPanel();
    }

    // Show alert message
    function showAlert(type, message) {
        // You can use your existing alert system or create a simple one
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

        // Prepend alert to the card body
        const cardBody = document.querySelector('.card-body');
        if (cardBody) {
            cardBody.insertAdjacentHTML('afterbegin', alertHtml);
        }
    }
});
</script>
@endsection
@section('header', 'Project Management System Dashboard')



@section('content')

<div class="container-fluid">
  <!-- Statistics Row -->
  {{-- <div class="row mb-4">
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
  </div> --}}

  <div class="row">
    <!-- Statistics -->
    <div class="col-xl-12 mb-4 col-lg-7 col-12">
      <div class="card h-100 ">
        <div class="card-header">
          <div class="d-flex justify-content-between mb-3">
            <h5 class="card-title mb-0">Statistics</h5>
            <small class="text-muted">Updated 1 month ago</small>
          </div>
        </div>
        <div class="card-body">
          <div class="row gy-3">
            <div class="col-md-2 col-4">
              <div class="d-flex align-items-center">
                <div class="badge rounded-pill bg-label-primary me-3 p-2"><i class="ti ti-chart-pie-2 ti-sm"></i></div>
                <div class="card-info">
                  <h5 class="mb-0">{{ $data['total_projects'] }}</h5>
                  <small>Live Projects</small>

                </div>
              </div>
            </div>
            <div class="col-md-2 col-4">
              <div class="d-flex align-items-center">
                <div class="badge rounded-pill bg-label-info me-3 p-2"><i class="ti ti-users ti-sm"></i></div>
                <div class="card-info">
                  <h5 class="mb-0">{{ $data['active_projects'] }}</h5>
                  <small>Ongoing Projects</small>
                </div>
              </div>
            </div>
            <div class="col-md-2 col-4">
              <div class="d-flex align-items-center">
                <div class="badge rounded-pill bg-label-danger me-3 p-2"><i class="ti ti-shopping-cart ti-sm"></i></div>
                <div class="card-info">
                  <h5 class="mb-0">{{ $data['completed_projects'] }}</h5>
                  <small>Completed Projects</small>
                </div>
              </div>
            </div>
            <div class="col-md-2 col-4">
              <div class="d-flex align-items-center">
                <div class="badge rounded-pill bg-label-danger me-3 p-2"><i class="ti ti-shopping-cart ti-sm"></i></div>
                <div class="card-info">
                  <h5 class="mb-0">{{ $data['delayed_projects'] }}</h5>
                  <small>Delayed Projects</small>
                </div>
              </div>
            </div>
            <div class="col-md-2 col-4">
              <div class="d-flex align-items-center">
                <div class="badge rounded-pill bg-label-danger me-3 p-2"><i class="ti ti-shopping-cart ti-sm"></i></div>
                <div class="card-info">
                  <h5 class="mb-0">{{ $data['proposal_submitted_projects'] }}</h5>
                  <small>Proposal Submitted</small>
                </div>
              </div>
            </div>
            <div class="col-md-2 col-4">
              <div class="d-flex align-items-center">
                <div class="badge rounded-pill bg-label-danger me-3 p-2"><i class="ti ti-shopping-cart ti-sm"></i></div>
                <div class="card-info">
                  <h5 class="mb-0">{{ $data['planning_projects'] }}</h5>
                  <small>Planning Stage</small>
                </div>
              </div>
            </div>

            <div class="col-md-2 col-4">
              <div class="d-flex align-items-center">
                <div class="badge rounded-pill bg-label-danger me-3 p-2"><i class="ti ti-shopping-cart ti-sm"></i></div>
                <div class="card-info">
                  <h5 class="mb-0">{{ $data['archived'] }}</h5>
                  <small>Archived</small>
                </div>
              </div>
            </div>


            @if($user->hasRole('director'))
            <div class="col-md-2 col-4">
              <div class="d-flex align-items-center">
                <div class="badge rounded-pill bg-label-success me-3 p-2"><i class="ti ti-currency-dollar ti-sm"></i>
                </div>
                <div class="card-info">
                  <h5 class="mb-0">{{ $data['pending_approvals'] }}</h5>
                  <small>Pending Approval</small>
                </div>
              </div>
            </div>
            @endif
            <div class="col-md-2 col-4">
              <div class="d-flex align-items-center">
                <div class="badge rounded-pill bg-label-success me-3 p-2"><i class="ti ti-currency-dollar ti-sm"></i>
                </div>
                <div class="card-info">
                  <h5 class="mb-0">{{ $data['assigned_tasks'] }}</h5>
                  <small>Your Tasks</small>
                </div>
              </div>
            </div>

            @if($user->hasRole('finance'))
            <div class="col-md-2 col-4">
              <div class="d-flex align-items-center">
                <div class="badge rounded-pill bg-label-success me-3 p-2"><i class="ti ti-currency-dollar ti-sm"></i>
                </div>
                <div class="card-info">
                  <h5 class="mb-0">{{ $data['pending_invoices'] }}</h5>
                  <small>Pending Invoices</small>
                </div>
              </div>
            </div>
            @endif
          </div>
        </div>
      </div>
    </div>
    <!--/ Statistics -->

  </div>
  <div class="row">
    <div class="col-md-12 mb-4">
      <div class="card text-center">
        <div class="card-body">

          <a type="button" class="btn btn-label-primary"
            href="{{ route('pms.reports.project-status-detailed') }}">Project Status
            Report</a>
          @if($user->hasRole('director') || $user->hasRole('finance') )
          <a type="button" class="btn btn-label-secondary" href="{{ route('pms.reports.financial') }}">Financial
            Report</a>
          @endif
          {{-- <a type="button" class="btn btn-label-success" href="{{ route('pms.reports.timesheet') }}">Timesheet</a>
          --}}
          <a type="button" class="btn btn-label-danger" href="{{ route('pms.reports.resource-utilization') }}">Resource
            Utilization Chart</a>
          {{-- <a type="button" class="btn btn-label-warning"
            href="{{ route('pms.timesheets.resource-utilization') }}">Resource
            Utilization Timesheet</a> --}}
          <a type="button" class="btn btn-label-info" href="{{ route('pms.timesheets.calendar') }}">Timesheet
            Calendar</a>
          {{-- <a type="button" class="btn btn-label-dark" href="{{ route('pms.timesheets.report') }}">Report</a> --}}

        </div>
      </div>
    </div>
  </div>





  <!-- Main Content Row -->
  <div class=" row">
    <!-- Left Column -->
    <div class="col-lg-8">



      @if($user->hasRole('director') || $user->hasRole('PAC'))
      <!-- Director/PAC Member Widgets -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Approvals Dashboard</h5>
        </div>
        <div class="card-body">

          <!-- Bulk Actions Panel -->
          <div class="bulk-actions-container" id="bulkActionsContainer" style="display: none;">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <span class="selected-count" id="selectedCount">0 items selected</span>
              </div>
              <div class="btn-group">
                <button type="button" class="btn btn-success" id="bulkApproveBtn">
                  <i class="fas fa-check"></i> Approve Selected
                </button>
                <button type="button" class="btn btn-primary" id="bulkSendToPacBtn">
                  <i class="fas fa-paper-plane"></i> Send to PAC
                </button>
                <button type="button" class="btn btn-danger" id="bulkRejectBtn">
                  <i class="fas fa-times"></i> Reject Selected
                </button>
                <button type="button" class="btn btn-outline-secondary" id="clearSelectionBtn">
                  <i class="fas fa-times"></i> Clear Selection
                </button>
              </div>
            </div>
          </div>

          <ul class="nav nav-tabs" id="approvalsTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="requirements-tab" data-bs-toggle="tab" data-bs-target="#requirements"
                type="button" role="tab">Requirements</button>
            </li>
            @if($user->hasRole('director'))
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="proposals-tab" data-bs-toggle="tab" data-bs-target="#proposals" type="button"
                role="tab">Proposals</button>
            </li>
            @endif
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
                      <th width="50">
                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" id="selectAllRequirements">
                        </div>
                      </th>
                      <th>Temp No</th>
                      <th>Client</th>
                      <th>Category</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody id="requirementsTableBody">
                    @foreach($requirements as $requirement)
                    <tr>
                      <td>
                        <div class="form-check">
                          <input class="form-check-input requirement-checkbox" type="checkbox"
                            value="{{ $requirement->id }}" data-type="requirement">
                        </div>
                      </td>
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
                      <th>Category</th>
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
                      <td>{{ $proposal->requirement->category->name }}</td>
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

      <!-- Bulk Action Confirmation Modal -->
      <div class="modal fade" id="bulkActionModal" tabindex="-1" aria-labelledby="bulkActionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="bulkActionModalLabel">Confirm Bulk Action</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <p id="bulkActionMessage">Are you sure you want to perform this action on the selected items?</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-primary" id="confirmBulkAction">Confirm</button>
            </div>
          </div>
        </div>
      </div>


      @endif




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
              <small><span class="badge bg-{{ $task->status == Task::STATUS_IN_PROGRESS ? 'info' : 'secondary' }}">
                  {{ $task->status_name }}
                </span></small>
            </div>
            <p class="mb-1 small">{{ $task->milestone->project->title }}</p>
            <div class="d-flex justify-content-between">
              <small class="text-muted">Due: {{ $task->end_date->format('M d, Y') }}</small>
              <small>Priority: <span class="badge bg-{{ $task->priority_badge_color }}">{{ $task->priority_name
                  }}</span></small>
            </div>
            <a class="btn btn-sm btn-success" href="{{ route('pms.tasks.show', [$task->milestone->project_id,
              $task->milestone_id, $task->id]) }}">view</a>
            <a class="btn btn-sm btn-label-danger "
              href="{{ route('pms.projects.kanban.index',$task->milestone->project_id) }}">View All Task</a>
          </div>
          @endforeach
          @else
          <p>No tasks assigned to you currently.</p>
          @endif



          <div class="card mb-3">
            <div class="row g-0">
              <div class="col-md-8">
                <div class="card-header header-elements">
                  <span class="me-2">Log Today's Time ({{ gmdate("H:i", $data['today_timesheet'] * 60) }})</span>
                  <div class="card-header-elements">
                  </div>
                </div>
                <div class="card-body">
                  <a href="{{ route('pms.timesheets.index') }}" class="btn btn-primary">Update</a>
                </div>
              </div>
              <div class="col-md-4">
                {{-- <dotlottie-player style=" "
                  src="https://lottie.host/cfe10c8b-0c45-4e09-bc58-25917c3521b8/lwtYYeGqnK.lottie"
                  background="transparent" speed="1" style="width: 150px; height: 150px;" loop autoplay>
                </dotlottie-player> --}}
                <dotlottie-player style=" " src="{{asset('assets/json/time-lottie.json')}}" background="transparent"
                  speed="1" style="width: 150px; height: 150px;" loop autoplay>
                </dotlottie-player>

              </div>

            </div>
          </div>



          {{-- <div class="mt-3">
            <a href="{{ route('pms.timesheets.index') }}" class="btn btn-sm btn-primary">
              <i class="fas fa-clock"></i> Log Today's Time ({{ gmdate("H:i", $data['today_timesheet'] * 60) }})
            </a>
          </div> --}}
        </div>
      </div>
      {{-- @endif --}}


      <!-- Team Lead Widgets -->
      {{-- <div class="card mb-4">
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
      </div> --}}




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
          $projects = Project::with(['requirement.client'])
          ->where('status','!=',Project::STATUS_COMPLETED)
          ->where(function ($q) use ($user) {
          $q->where('project_investigator_id', $user->id)
          ->orWhereHas('teamMembers', function ($tq) use ($user) {
          $tq->where('user_id', $user->id);
          });
          })
          ->orderBy('status')

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
          <p>You are not currently assigned as Principal investigator on any projects.</p>
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
            @if($user->hasRole('Project Investigator'))
            <div class="col-md-6 mb-3">
              <div class="card h-100">
                <div class="card-body text-center">
                  <i class="fas fa-file-alt fa-3x mb-3 text-primary"></i>
                  <h6>New Requirement</h6>
                  <a href="{{ route('pms.requirements.index') }}" class="stretched-link"></a>
                </div>
              </div>
            </div>


            <div class="col-md-6 mb-3">
              <div class="card h-100">
                <div class="card-body text-center">
                  <i class="fas fa-file-alt fa-3x mb-3 text-warning"></i>
                  <h6>Create Proposal</h6>
                  <a href="{{ route('pms.requirements.proposals') }}" class="stretched-link"></a>
                </div>
              </div>
            </div>
            @endif

            {{-- @if($user->hasRole('faculty') || $user->can('create_proposals')) --}}
            <div class="col-md-6 mb-3">
              <div class="card h-100">
                <div class="card-body text-center">
                  <i class="fas fa-project-diagram fa-3x mb-3 text-success"></i>
                  <h6>Projects</h6>
                  <a href="{{ route('pms.projects.index') }}" class="stretched-link"></a>
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