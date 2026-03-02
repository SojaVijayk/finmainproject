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
      <div class="card mt-4 h-100">
        <div class="card-header border-bottom">
          <h5 class="card-title mb-0 text-primary">
            <i class="fas fa-project-diagram me-2"></i> Project Financials
          </h5>
        </div>
        <div class="card-body pt-3">
          <div class="accordion" id="projectFinancialsAccordion">
            {{-- 1. Project Budgeted --}}
            @php
            $projBudgeted = $project->expenseComponents->where('type',
            \App\Models\PMS\ProjectExpenseComponent::TYPE_BUDGETED);
            @endphp
            <div class="accordion-item shadow-sm border mb-2">
              <h2 class="accordion-header" id="headingProjBudgeted">
                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                  data-bs-target="#collapseProjBudgeted" aria-expanded="true" aria-controls="collapseProjBudgeted">
                  <span class="fw-bold"><i class="fas fa-coins me-2 text-primary"></i>Budgeted Expense</span>
                  <span class="ms-auto badge bg-label-primary">₹{{ number_format($projBudgeted->sum('amount'), 2)
                    }}</span>
                </button>
              </h2>
              <div id="collapseProjBudgeted" class="accordion-collapse collapse show"
                aria-labelledby="headingProjBudgeted" data-bs-parent="#projectFinancialsAccordion">
                <div class="accordion-body p-0">
                  @if($projBudgeted->count() > 0)
                  <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                    <table class="table table-sm table-striped mb-0">
                      <thead class="table-light sticky-top">
                        <tr>
                          <th>Component</th>
                          <th class="text-end">Amount (₹)</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($projBudgeted as $comp)
                        <tr>
                          <td>
                            <div class="d-flex flex-column">
                              <span>{{ $comp->component }}</span>
                              <small class="text-muted">{{ $comp->group_name }} | {{ $comp->category->name ?? '-'
                                }}</small>
                            </div>
                          </td>
                          <td class="text-end fw-bold">₹{{ number_format($comp->amount, 2) }}</td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                  @else
                  <div class="text-center p-3 text-muted">No budgeted expenses.</div>
                  @endif
                </div>
              </div>
            </div>

            {{-- 2. Project Estimated (Yearly or Flat) --}}
            @if($project->yearlyBudgets->count() > 0)
            {{-- Yearly Mode --}}
            <div class="d-flex align-items-center mt-3 mb-2">
              <h6 class="mb-0 text-muted w-100 border-bottom pb-2">Yearly Expense Estimates</h6>
            </div>
            @foreach($project->yearlyBudgets as $yearBudget)
            @php
            $yearId = $yearBudget->financial_year_id;
            $yearName = $yearBudget->financialYear->name ?? 'Unknown';
            $yearEstimates = $project->expenseComponents->filter(function($c) use ($yearId) {
            return $c->type == \App\Models\PMS\ProjectExpenseComponent::TYPE_ESTIMATED && $c->financial_year_id ==
            $yearId;
            });
            $totalEst = $yearEstimates->sum('amount');
            @endphp
            <div class="accordion-item shadow-sm border mb-2">
              <h2 class="accordion-header" id="headingYear{{ $yearId }}">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                  data-bs-target="#collapseYear{{ $yearId }}" aria-expanded="false"
                  aria-controls="collapseYear{{ $yearId }}">
                  <div class="d-flex w-100 justify-content-between align-items-center pe-2">
                    <span class="fw-bold">{{ $yearName }}</span>
                    <span class="badge bg-label-warning">Est: ₹{{ number_format($totalEst, 2) }}</span>
                  </div>
                </button>
              </h2>
              <div id="collapseYear{{ $yearId }}" class="accordion-collapse collapse"
                aria-labelledby="headingYear{{ $yearId }}" data-bs-parent="#projectFinancialsAccordion">
                <div class="accordion-body p-0">
                  @if($yearEstimates->count() > 0)
                  <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                      <tbody>
                        @foreach($yearEstimates as $comp)
                        <tr>
                          <td>{{ $comp->component }}</td>
                          <td class="text-end">₹{{ number_format($comp->amount, 2) }}</td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                  @else
                  <div class="p-2 text-center text-muted small">No estimates.</div>
                  @endif
                </div>
              </div>
            </div>
            @endforeach
            @else
            {{-- Flat Mode Fallback --}}
            @php
            $allEstimates = $project->expenseComponents->where('type',
            \App\Models\PMS\ProjectExpenseComponent::TYPE_ESTIMATED);
            @endphp
            @if($allEstimates->count() > 0)
            <div class="accordion-item shadow-sm border mb-2 mt-2">
              <h2 class="accordion-header" id="headingEstFlat">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                  data-bs-target="#collapseEstFlat" aria-expanded="false" aria-controls="collapseEstFlat">
                  <span class="fw-bold"><i class="fas fa-calculator me-2 text-warning"></i> Estimated Expenses
                    (Total)</span>
                  <span class="ms-auto badge bg-label-warning">₹{{ number_format($allEstimates->sum('amount'), 2)
                    }}</span>
                </button>
              </h2>
              <div id="collapseEstFlat" class="accordion-collapse collapse" aria-labelledby="headingEstFlat"
                data-bs-parent="#projectFinancialsAccordion">
                <div class="accordion-body p-0">
                  <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                    <table class="table table-sm table-striped mb-0">
                      <thead class="table-light sticky-top">
                        <tr>
                          <th>Component</th>
                          <th class="text-end">Amount</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($allEstimates as $comp)
                        <tr>
                          <td>{{ $comp->component }}</td>
                          <td class="text-end">₹{{ number_format($comp->amount, 2) }}</td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
            @endif
            @endif
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      @if($project->proposal)
      <div class="card mt-4 h-100">
        <div class="card-header border-bottom">
          <h5 class="card-title mb-0 text-success">
            <i class="fas fa-file-contract me-2"></i> Proposal Estimates
          </h5>
        </div>
        <div class="card-body pt-3">
          <div class="accordion" id="proposalFinancialsAccordion">
            {{-- 1. Proposal Budgeted --}}
            @php
            $propBudgeted = $project->proposal->expenseComponents->where('type',
            \App\Models\PMS\ProposalExpenseComponent::TYPE_BUDGETED);
            @endphp
            <div class="accordion-item shadow-sm border mb-2">
              <h2 class="accordion-header" id="headingPropBudgeted">
                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                  data-bs-target="#collapsePropBudgeted" aria-expanded="true" aria-controls="collapsePropBudgeted">
                  <span class="fw-bold"><i class="fas fa-money-check-alt me-2 text-success"></i> Proposal
                    Budgeted</span>
                  <span class="ms-auto badge bg-label-success">₹{{ number_format($propBudgeted->sum('amount'), 2)
                    }}</span>
                </button>
              </h2>
              <div id="collapsePropBudgeted" class="accordion-collapse collapse show"
                aria-labelledby="headingPropBudgeted" data-bs-parent="#proposalFinancialsAccordion">
                <div class="accordion-body p-0">
                  @if($propBudgeted->count() > 0)
                  <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                    <table class="table table-sm table-striped mb-0">
                      <thead class="table-light sticky-top">
                        <tr>
                          <th>Component</th>
                          <th class="text-end">Amount</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($propBudgeted as $comp)
                        <tr>
                          <td>{{ $comp->component }}</td>
                          <td class="text-end">₹{{ number_format($comp->amount, 2) }}</td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                  @else
                  <div class="text-center p-3 text-muted">No budgeted items.</div>
                  @endif
                </div>
              </div>
            </div>

            {{-- 2. Proposal Estimated --}}
            @php
            $propEstimated = $project->proposal->expenseComponents->where('type',
            \App\Models\PMS\ProposalExpenseComponent::TYPE_ESTIMATED);
            $propGroups = $propEstimated->groupBy('group_name');
            @endphp
            @if($propEstimated->count() > 0)
            <div class="d-flex align-items-center mt-3 mb-2">
              <h6 class="mb-0 text-muted w-100 border-bottom pb-2">Proposal Estimated Breakdown</h6>
            </div>
            @foreach($propGroups as $groupName => $components)
            @php
            $groupId = Str::slug($groupName ?? 'ungrouped', '_') . '_prop';
            $groupTotal = $components->sum('amount');
            @endphp
            <div class="accordion-item shadow-sm border mb-2">
              <h2 class="accordion-header" id="heading{{ $groupId }}">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                  data-bs-target="#collapse{{ $groupId }}" aria-expanded="false" aria-controls="collapse{{ $groupId }}">
                  <div class="d-flex w-100 justify-content-between align-items-center pe-2">
                    <span class="fw-bold">{{ $groupName }}</span>
                    <span class="badge bg-label-info">₹{{ number_format($groupTotal, 2) }}</span>
                  </div>
                </button>
              </h2>
              <div id="collapse{{ $groupId }}" class="accordion-collapse collapse"
                aria-labelledby="heading{{ $groupId }}" data-bs-parent="#proposalFinancialsAccordion">
                <div class="accordion-body p-0">
                  <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                      <tbody>
                        @foreach($components as $comp)
                        <tr>
                          <td>{{ $comp->component }}</td>
                          <td class="text-end">₹{{ number_format($comp->amount, 2) }}</td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
            @endforeach
            <div class="mt-2 text-end">
              <small class="fw-bold text-success">Total Est: ₹{{ number_format($propEstimated->sum('amount'), 2)
                }}</small>
            </div>
            @endif
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