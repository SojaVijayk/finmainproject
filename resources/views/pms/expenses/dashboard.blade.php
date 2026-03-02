@extends('layouts.app')

@section('content')
<div class="container-fluid">
  <div class="row mb-4">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Finance Dashboard</h5>
          <form method="GET" action="{{ route('pms.expense.dashboard') }}" class="form-inline">
            <select name="date_range" class="form-control mr-2" onchange="this.form.submit()">
              <option value="this_month" {{ $dateRange=='this_month' ? 'selected' : '' }}>This Month</option>
              <option value="last_month" {{ $dateRange=='last_month' ? 'selected' : '' }}>Last Month</option>
              <option value="last_quarter" {{ $dateRange=='last_quarter' ? 'selected' : '' }}>Last Quarter</option>
              <option value="last_year" {{ $dateRange=='last_year' ? 'selected' : '' }}>Last Year</option>
            </select>
            <span class="text-muted">
              {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}
            </span>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="row mb-4">
    <div class="col-md-3">
      <div class="card bg-primary text-white">
        <div class="card-body">
          <h6 class="card-title">Total Expenses</h6>
          <h3 class="card-text">${{ number_format($totalExpenses, 2) }}</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card bg-success text-white">
        <div class="card-body">
          <h6 class="card-title">Total Revenue</h6>
          <h3 class="card-text">${{ number_format($totalRevenue, 2) }}</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card bg-info text-white">
        <div class="card-body">
          <h6 class="card-title">Total Projects</h6>
          <h3 class="card-text">{{ number_format($totalProjects) }}</h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card bg-warning text-white">
        <div class="card-body">
          <h6 class="card-title">Avg Expense/Project</h6>
          <h3 class="card-text">${{ number_format($avgExpensePerProject, 2) }}</h3>
        </div>
      </div>
    </div>
  </div>

  <div class="row mb-4">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title">Budget vs Actual Expense</h5>
        </div>
        <div class="card-body">
          <canvas id="budgetChart" height="300"></canvas>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title">Revenue vs Expense</h5>
        </div>
        <div class="card-body">
          <canvas id="revenueChart" height="300"></canvas>
        </div>
      </div>
    </div>
  </div>

  <div class="row mb-4">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title">Expense Distribution by Category</h5>
        </div>
        <div class="card-body">
          <canvas id="categoryChart" height="300"></canvas>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title">Expense Trend (Last 6 Months)</h5>
        </div>
        <div class="card-body">
          <canvas id="trendChart" height="300"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  // Budget vs Actual Chart
    const budgetCtx = document.getElementById('budgetChart').getContext('2d');
    const budgetChart = new Chart(budgetCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($budgetData->pluck('project')) !!},
            datasets: [
                {
                    label: 'Budget',
                    data: {!! json_encode($budgetData->pluck('budget')) !!},
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Actual',
                    data: {!! json_encode($budgetData->pluck('actual')) !!},
                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': $' + context.raw.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Revenue vs Expense Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(revenueCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($revenueData->pluck('project')) !!},
            datasets: [
                {
                    label: 'Revenue',
                    data: {!! json_encode($revenueData->pluck('revenue')) !!},
                    backgroundColor: 'rgba(75, 192, 192, 0.5)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Expense',
                    data: {!! json_encode($revenueData->pluck('expense')) !!},
                    backgroundColor: 'rgba(255, 159, 64, 0.5)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': $' + context.raw.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Expense distribution by category
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    const categoryChart = new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($categoryData->pluck('category')) !!},
            datasets: [{
                data: {!! json_encode($categoryData->pluck('total')) !!},
                backgroundColor: [
                    'rgba(255, 99, 132, 0.6)',
                    'rgba(54, 162, 235, 0.6)',
                    'rgba(255, 206, 86, 0.6)',
                    'rgba(75, 192, 192, 0.6)',
                    'rgba(153, 102, 255, 0.6)',
                    'rgba(255, 159, 64, 0.6)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
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

    // Expense trend over time
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    const trendChart = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($trendData->pluck('label')) !!},
            datasets: [{
                label: 'Expenses',
                data: {!! json_encode($trendData->pluck('total')) !!},
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2,
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Expenses: $' + context.raw.toLocaleString();
                        }
                    }
                }
            }
        }
    });
</script>
@endsection