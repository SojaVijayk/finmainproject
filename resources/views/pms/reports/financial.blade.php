@extends('layouts/layoutMaster')

@section('title', 'Financial Report')

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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Budget vs Expense Chart
    const budgetCtx = document.getElementById('budgetChart').getContext('2d');
    const budgetChart = new Chart(budgetCtx, {
        type: 'bar',
        data: {
            labels: ['Budget', 'Expense'],
            datasets: [{
                label: 'Amount (₹)',
                data: [
                    {{ $financialData['total_budget'] }},
                    {{ $financialData['total_expense'] }}
                ],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 99, 132, 0.7)'
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 99, 132, 1)'
                ],
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

    // Invoiced vs Paid Chart
    const invoiceCtx = document.getElementById('invoiceChart').getContext('2d');
    const invoiceChart = new Chart(invoiceCtx, {
        type: 'doughnut',
        data: {
            labels: ['Paid', 'Pending'],
            datasets: [{
                data: [
                    {{ $financialData['total_paid'] }},
                    {{ $financialData['total_pending'] }}
                ],
                backgroundColor: [
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(255, 206, 86, 0.7)'
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 206, 86, 1)'
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
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: ₹${value.toLocaleString()} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
});
</script>
@endsection
@section('header', 'Financial Report')

@section('content')
<div class="card">
  <div class="card-header">
    <div class="d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Financial Report</h5>
      <div>
        <a href="{{ route('pms.reports.export', ['type' => 'financial']) }}?date_range={{ $dateRange }}"
          class="btn btn-sm btn-success me-2">
          <i class="fas fa-file-export"></i> Export
        </a>
      </div>
    </div>
  </div>
  <div class="card-body">
    <form method="GET" class="mb-4">
      <div class="row">
        <div class="col-md-8">
          <label for="date_range" class="form-label">Date Range</label>
          <select name="date_range" id="date_range" class="form-select">
            @foreach($dateRanges as $key => $value)
            <option value="{{ $key }}" {{ $dateRange==$key ? 'selected' : '' }}>{{ $value }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-4 d-flex align-items-end">
          <button type="submit" class="btn btn-primary">Apply Filters</button>
        </div>
      </div>
    </form>

    <div class="row mb-4">
      <div class="col-md-3">
        <div class="card bg-label-primary text-white">
          <div class="card-body">
            <h5 class="card-title">Total Budget</h5>
            <h3 class="card-text">₹{{ number_format($financialData['total_budget'], 2) }}</h3>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card bg-label-success text-white">
          <div class="card-body">
            <h5 class="card-title">Total Revenue</h5>
            <h3 class="card-text">₹{{ number_format($financialData['total_revenue'], 2) }}</h3>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card bg-label-info text-white">
          <div class="card-body">
            <h5 class="card-title">Total Invoiced</h5>
            <h3 class="card-text">₹{{ number_format($financialData['total_invoiced'], 2) }}</h3>
            <small class="card-text text-primary">Proforma Invoice -
              ₹{{number_format($financialData['total_proforma_invoiced'], 2) }}</small><br>
            <small class="card-text text-success">Tax Invoice - ₹{{ number_format($financialData['total_tax_invoiced'],
              2) }}</small>

          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card bg-label-warning text-dark">
          <div class="card-body">
            <h5 class="card-title">Total Pending</h5>
            <h3 class="card-text">₹{{ number_format($financialData['total_pending'], 2) }}</h3>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title">Budget vs Expense</h5>
          </div>
          <div class="card-body">
            <canvas id="budgetChart" height="250"></canvas>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title">Invoiced vs Paid</h5>
          </div>
          <div class="card-body">
            <canvas id="invoiceChart" height="250"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection