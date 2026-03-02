@extends('layouts/layoutMaster')

@section('title', 'Project Dashboard - ' . $project->title)

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/spinkit/spinkit.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}">
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
@endsection

@section('page-style')
<style>
  .stat-card {
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
    height: 100%;
  }

  .progress-thin {
    height: 8px;
  }

  .chart-container {
    position: relative;
    height: 300px;
    width: 100%;
  }

  .activity-timeline {
    position: relative;
    padding-left: 30px;
  }

  .activity-timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background-color: #e9ecef;
  }

  .timeline-item {
    position: relative;
    margin-bottom: 20px;
  }

  .timeline-item::before {
    content: '';
    position: absolute;
    left: -30px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background-color: #696cff;
    border: 2px solid #fff;
    box-shadow: 0 0 0 3px #696cff;
  }
</style>
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js')}}"></script>
<script src="{{asset('assets/vendor/libs/block-ui/block-ui.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection

@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Financial Overview Chart
    const financialCtx = document.getElementById('financialOverviewChart').getContext('2d');
    const financialChart = new Chart(financialCtx, {
      type: 'doughnut',
      data: {
        labels: ['Budget', 'Expenses', 'Invoiced', 'Paid'],
        datasets: [{
          data: [{{ $budget }}, {{ $expenses }}, {{ $totalInvoiced }}, {{ $totalPaid }}],
          backgroundColor: [
            'rgba(54, 162, 235, 0.8)',
            'rgba(255, 99, 132, 0.8)',
            'rgba(255, 206, 86, 0.8)',
            'rgba(75, 192, 192, 0.8)'
          ],
          borderColor: [
            'rgba(54, 162, 235, 1)',
            'rgba(255, 99, 132, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)'
          ],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'bottom',
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                return context.label + ': $' + context.raw.toLocaleString();
              }
            }
          }
        }
      }
    });

    // Expense by Category Chart
    const expenseCtx = document.getElementById('expenseByCategoryChart').getContext('2d');
    const expenseChart = new Chart(expenseCtx, {
      type: 'pie',
      data: {
        labels: {!! json_encode($expenseByCategory->keys()) !!},
        datasets: [{
          data: {!! json_encode($expenseByCategory->values()) !!},
          backgroundColor: [
            'rgba(255, 99, 132, 0.8)',
            'rgba(54, 162, 235, 0.8)',
            'rgba(255, 206, 86, 0.8)',
            'rgba(75, 192, 192, 0.8)',
            'rgba(153, 102, 255, 0.8)',
            'rgba(255, 159, 64, 0.8)'
          ],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'bottom',
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                return context.label + ': $' + context.raw.toLocaleString();
              }
            }
          }
        }
      }
    });

    // Timesheet by User Chart
    const timesheetCtx = document.getElementById('timesheetByUserChart').getContext('2d');
    const timesheetChart = new Chart(timesheetCtx, {
      type: 'bar',
      data: {
        labels: {!! json_encode($timesheetByUser->keys()) !!},
        datasets: [{
          label: 'Hours Worked',
          data: {!! json_encode($timesheetByUser->values()) !!},
          backgroundColor: 'rgba(75, 192, 192, 0.8)',
          borderColor: 'rgba(75, 192, 192, 1)',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: 'Hours'
            }
          }
        }
      }
    });

    // Milestone Progress Chart
    const milestoneCtx = document.getElementById('milestoneProgressChart').getContext('2d');
    const milestoneChart = new Chart(milestoneCtx, {
      type: 'bar',
      data: {
        labels: {!! json_encode($milestoneProgress->pluck('name')) !!},
        datasets: [{
          label: 'Completion %',
          data: {!! json_encode($milestoneProgress->pluck('progress')) !!},
          backgroundColor: 'rgba(54, 162, 235, 0.8)',
          borderColor: 'rgba(54, 162, 235, 1)',
          borderWidth: 1
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        scales: {
          x: {
            beginAtZero: true,
            max: 100,
            title: {
              display: true,
              text: 'Completion %'
            }
          }
        }
      }
    });
  });
</script>
@endsection

@section('content')
<div class="container-fluid">
  <!-- Project Header -->
  <div class="row mb-4">
    <div class="col-md-12">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h4 class="card-title mb-1">{{ $project->title }}</h4>
              <p class="text-muted mb-0">{{ $project->project_code }}</p>
            </div>
            <div>
              <span class="badge bg-{{ $project->status_badge_color }}">{{ $project->status_name }}</span>
              <span class="badge bg-info">Completion: {{ $project->completion_percentage }}%</span>
            </div>
          </div>
          <div class="row mt-3">
            <div class="col-md-3">
              <small class="text-muted">Start Date</small>
              <p class="mb-0">{{ $project->start_date->format('M d, Y') }}</p>
            </div>
            <div class="col-md-3">
              <small class="text-muted">End Date</small>
              <p class="mb-0">{{ $project->end_date->format('M d, Y') }}</p>
            </div>
            <div class="col-md-3">
              <small class="text-muted">Project Investigator</small>
              <p class="mb-0">{{ $project->investigator->name ?? 'N/A' }}</p>
            </div>
            <div class="col-md-3">
              <small class="text-muted">Team Members</small>
              <p class="mb-0">{{ $project->teamMembers->count() }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Financial Overview -->
  <div class="row mb-4">
    <div class="col-md-3">
      <div class="card stat-card bg-label-primary text-white">
        <div class="card-body text-center">
          <h6 class="card-title">Total Budget</h6>
          <h3 class="card-text">{{ number_format($budget, 2) }}</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card stat-card bg-label-info text-white">
        <div class="card-body text-center">
          <h6 class="card-title">Estimated Expenses</h6>
          <h3 class="card-text">{{ $project->estimated_expense }}</h3>
          <small class="card-text text-dark">Actual Expenses</small>
          <small class="card-text text-danger">{{ number_format($expenses, 2) }}</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card stat-card bg-label-warning text-white">
        <div class="card-body text-center">
          <h6 class="card-title">Total Invoiced</h6>

          <h3 class="card-text">{{ number_format($totalInvoiced, 2) }}</h3>
          <small class="card-text text-primary">Proforma Invoice - {{ number_format($totalInvoiced_proforma,
            2)}}</small><br>
          <small class="card-text text-success">Tax Invoice - {{ number_format($totalInvoiced_tax, 2) }}</small>

        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card stat-card bg-label-success text-white">
        <div class="card-body text-center">
          <h6 class="card-title">Total Recieved</h6>
          <h3 class="card-text">{{ number_format($totalPaid, 2) }}</h3>
          <small class="card-text text-dark">Balance</small>
          <small class="card-text text-danger">{{ number_format($totalInvoiced - $totalPaid, 2) }}</small>
        </div>
      </div>
    </div>
  </div>

  <!-- Budget Utilization -->
  <div class="row mb-4">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title">Budget Utilization</h5>
        </div>
        <div class="card-body">
          <div class="d-flex justify-content-between mb-2">
            <span>Budget: {{ number_format($budget, 2) }}</span>
            <span>Expenses: {{ number_format($expenses, 2) }}</span>
            <span>Remaining: {{ number_format($budgetRemaining, 2) }}</span>
          </div>
          <div class="progress" style="height: 20px;">
            <div
              class="progress-bar bg-{{ $budgetUtilization > 90 ? 'danger' : ($budgetUtilization > 75 ? 'warning' : 'success') }}"
              role="progressbar" style="width: {{ $budgetUtilization }}%" aria-valuenow="{{ $budgetUtilization }}"
              aria-valuemin="0" aria-valuemax="100">
              {{ round($budgetUtilization, 2) }}%
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="row mb-4">
    <div class="col-md-6">
      @if($project->expenseComponents->count() > 0)
      <div class="card mt-4">
        <div class="card-header">
          <h5 class="card-title mb-0 badge bg-label-warning">Project Current Estimated Expense Breakdown </h5>
        </div>
        <div class="card-body">
          @php
          $grouped = $project->expenseComponents->groupBy('group_name');
          $grandTotal = 0;
          @endphp

          <div class="accordion" id="proposalExpenseAccordion">
            @foreach($grouped as $groupName => $components)
            @php

            $groupId = Str::slug($groupName ?? 'ungrouped', '_');
            $groupTotal = $components->sum('amount');
            $grandTotal += $groupTotal;
            @endphp

            <div class="accordion-item border rounded mb-2 shadow-sm">
              <h2 class="accordion-header" id="heading_{{ $groupId }}">
                <button class="accordion-button collapsed d-flex justify-content-between" type="button"
                  data-bs-toggle="collapse" data-bs-target="#collapse_{{ $groupId }}" aria-expanded="false"
                  aria-controls="collapse_{{ $groupId }}">
                  <div class="d-flex flex-column">
                    <span class="fw-bold text-primary">{{ $groupName ?? 'Ungrouped' }}</span>
                    <small class="text-muted">Subtotal: ₹{{ number_format($groupTotal, 2) }}</small>
                  </div>
                </button>
              </h2>
              <div id="collapse_{{ $groupId }}" class="accordion-collapse collapse"
                aria-labelledby="heading_{{ $groupId }}" data-bs-parent="#proposalExpenseAccordion">
                <div class="accordion-body p-2">
                  <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                      <thead class="table-light">
                        <tr>
                          <th style="width:25%">Category</th>
                          <th style="width:35%">Component</th>
                          @if($groupName === 'HR')
                          <th style="width:10%" class="text-end">Persondays</th>
                          <th style="width:15%" class="text-end">Rate (₹)</th>
                          @endif
                          <th style="width:20%" class="text-end">Amount (₹)</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($components as $component)
                        <tr>
                          <td>{{ $component->category->name ?? 'N/A' }}</td>
                          <td>{{ $component->component }}</td>
                          @if($groupName === 'HR')
                          <td class="text-end">{{ $component->mandays ?? '-' }}</td>
                          <td class="text-end">{{ $component->rate ? number_format($component->rate, 2) : '-' }}
                          </td>
                          @endif
                          <td class="text-end">₹{{ number_format($component->amount, 2) }}</td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
            @endforeach
          </div>

          <div class="border-top pt-3 mt-3 text-end">
            <strong>Grand Total Estimated Expense: ₹{{ number_format($grandTotal, 2) }}</strong>
          </div>
        </div>
      </div>
      @endif
    </div>
    <div class="col-md-6">
      @if($project->proposal && $project->proposal->expenseComponents->count() > 0)
      <div class="card mt-4">
        <div class="card-header">
          <h5 class="card-title mb-0 badge bg-label-success">Project Approved Estimated Expense Breakdown </h5>
        </div>
        <div class="card-body">
          @php
          $grouped = $project->proposal->expenseComponents->groupBy('group_name');
          $grandTotal = 0;
          @endphp

          <div class="accordion" id="proposalExpenseAccordion">
            @foreach($grouped as $groupName => $components)
            @php

            $groupId = Str::slug($groupName ?? 'ungrouped', '_');
            $groupTotal = $components->sum('amount');
            $grandTotal += $groupTotal;
            @endphp

            <div class="accordion-item border rounded mb-2 shadow-sm">
              <h2 class="accordion-header" id="heading_{{ $groupId }}">
                <button class="accordion-button collapsed d-flex justify-content-between" type="button"
                  data-bs-toggle="collapse" data-bs-target="#collapse_{{ $groupId }}" aria-expanded="false"
                  aria-controls="collapse_{{ $groupId }}">
                  <div class="d-flex flex-column">
                    <span class="fw-bold text-primary">{{ $groupName ?? 'Ungrouped' }}</span>
                    <small class="text-muted">Subtotal: ₹{{ number_format($groupTotal, 2) }}</small>
                  </div>
                </button>
              </h2>
              <div id="collapse_{{ $groupId }}" class="accordion-collapse collapse"
                aria-labelledby="heading_{{ $groupId }}" data-bs-parent="#proposalExpenseAccordion">
                <div class="accordion-body p-2">
                  <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                      <thead class="table-light">
                        <tr>
                          <th style="width:25%">Category</th>
                          <th style="width:35%">Component</th>
                          @if($groupName === 'HR')
                          <th style="width:10%" class="text-end">Persondays</th>
                          <th style="width:15%" class="text-end">Rate (₹)</th>
                          @endif
                          <th style="width:20%" class="text-end">Amount (₹)</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($components as $component)
                        <tr>
                          <td>{{ $component->category->name ?? 'N/A' }}</td>
                          <td>{{ $component->component }}</td>
                          @if($groupName === 'HR')
                          <td class="text-end">{{ $component->mandays ?? '-' }}</td>
                          <td class="text-end">{{ $component->rate ? number_format($component->rate, 2) : '-' }}
                          </td>
                          @endif
                          <td class="text-end">₹{{ number_format($component->amount, 2) }}</td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
            @endforeach
          </div>

          <div class="border-top pt-3 mt-3 text-end">
            <strong>Grand Total Estimated Expense: ₹{{ number_format($grandTotal, 2) }}</strong>
          </div>
        </div>
      </div>
      @endif
    </div>
  </div>

  <!-- Charts Row -->
  <div class="row mb-4">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title">Financial Overview</h5>
        </div>
        <div class="card-body">
          <div class="chart-container">
            <canvas id="financialOverviewChart"></canvas>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title">Expense by Category</h5>
        </div>
        <div class="card-body">
          <div class="chart-container">
            <canvas id="expenseByCategoryChart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Second Charts Row -->
  <div class="row mb-4">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title">Timesheet by User (Hours)</h5>
        </div>
        <div class="card-body">
          <div class="chart-container">
            <canvas id="timesheetByUserChart"></canvas>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title">Milestone Progress</h5>
        </div>
        <div class="card-body">
          <div class="chart-container">
            <canvas id="milestoneProgressChart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Activities -->
  <div class="row mb-4">
    <div class="col-md-4">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title">Recent Invoices</h5>
        </div>
        <div class="card-body">
          <div class="activity-timeline">
            @forelse($recentInvoices as $invoice)
            <div class="timeline-item">
              <h6 class="mb-1">{{ $invoice->invoice_number }}</h6>
              <p class="mb-1">Amount: ${{ number_format($invoice->amount, 2) }}</p>
              <p class="mb-1">Status: <span class="badge bg-{{ $invoice->status_badge_color }}">{{ $invoice->status_name
                  }}</span></p>
              <small class="text-muted">{{ $invoice->invoice_date->format('M d, Y') }}</small>
            </div>
            @empty
            <p class="text-muted">No recent invoices</p>
            @endforelse
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title">Recent Expenses</h5>
        </div>
        <div class="card-body">
          <div class="activity-timeline">
            @forelse($recentExpenses as $expense)
            <div class="timeline-item">
              <h6 class="mb-1">{{ $expense->category->name }}</h6>
              <p class="mb-1">Amount: ${{ number_format($expense->total_amount, 2) }}</p>
              <p class="mb-1">Vendor: {{ $expense->vendor->name }}</p>
              <small class="text-muted">{{ $expense->payment_date->format('M d, Y') }}</small>
            </div>
            @empty
            <p class="text-muted">No recent expenses</p>
            @endforelse
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title">Recent Tasks</h5>
        </div>
        <div class="card-body">
          <div class="activity-timeline">
            @forelse($recentTasks as $task)
            <div class="timeline-item">
              <h6 class="mb-1">{{ $task->name }}</h6>
              <p class="mb-1">Priority: <span class="badge bg-{{ $task->priority_badge_color }}">{{ $task->priority_name
                  }}</span></p>
              <p class="mb-1">Status: <span class="badge bg-{{ $task->status_badge_color }}">{{ $task->status_name
                  }}</span></p>
              <small class="text-muted">{{ $task->created_at->format('M d, Y') }}</small>
            </div>
            @empty
            <p class="text-muted">No recent tasks</p>
            @endforelse
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Quick Links -->

</div>
@endsection