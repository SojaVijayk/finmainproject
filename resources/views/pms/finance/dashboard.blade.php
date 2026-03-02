@extends('layouts/layoutMaster')

@section('title', 'Finance Dashboard')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/apex-charts/apex-charts.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/apex-charts/apexcharts.js')}}"></script>
@endsection

@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function() {
        // Revenue Chart
           const revenueData = JSON.parse('{!! json_encode($chartData["revenue"]->map(function($item) {
        return [
            "x" => \Carbon\Carbon::create($item->year, $item->month)->format("M Y"),
            "y" => $item->total
        ];
    })) !!}');

        const revenueChart = new ApexCharts(document.getElementById('revenueChart'), {
            series: [{
                name: 'Revenue',
                data: revenueData
            }],
            chart: {
                type: 'area',
                height: 350,
                toolbar: { show: false }
            },
            colors: ['#7367F0'],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth' },
            xaxis: { type: 'category' },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return '$' + val.toLocaleString();
                    }
                }
            }
        });
        revenueChart.render();

        // Invoice Status Chart
        const statusData = @json($chartData['status']);
        const statusChart = new ApexCharts(document.getElementById('statusChart'), {
            series: statusData.map(item => item.count),
            chart: {
                type: 'donut',
                height: 350
            },
            labels: statusData.map(item => {
                const statusNames = {
                    0: 'Draft',
                    1: 'Sent',
                    2: 'Paid',
                    3: 'Overdue',
                    4: 'Cancelled'
                };
                return statusNames[item.status];
            }),
            colors: ['#B8C2CC', '#FFC107', '#28C76F', '#EA5455', '#82868B'],
            legend: { position: 'bottom' }
        });
        statusChart.render();
    });
</script>
@endsection

@section('content')
<div class="row">
  <!-- Stats Cards -->
  <div class="col-xl-3 col-md-4 col-sm-6 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <span class="fw-semibold d-block mb-1">Total Revenue</span>
            <h3 class="card-title mb-2">{{ number_format($stats['total_revenue'], 2) }}</h3>
          </div>
          <div class="avatar">
            <div class="avatar-initial bg-label-success rounded">
              <i class="fas fa-dollar-sign"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-3 col-md-4 col-sm-6 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <span class="fw-semibold d-block mb-1">Outstanding</span>
            <h3 class="card-title mb-2">{{ number_format($stats['outstanding'], 2) }}</h3>
          </div>
          <div class="avatar">
            <div class="avatar-initial bg-label-warning rounded">
              <i class="fas fa-exclamation-circle"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-3 col-md-4 col-sm-6 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <span class="fw-semibold d-block mb-1">Overdue</span>
            <h3 class="card-title mb-2">{{ number_format($stats['overdue'], 2) }}</h3>
          </div>
          <div class="avatar">
            <div class="avatar-initial bg-label-danger rounded">
              <i class="fas fa-clock"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-3 col-md-4 col-sm-6 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <span class="fw-semibold d-block mb-1">Draft Invoices</span>
            <h3 class="card-title mb-2">{{ $stats['draft_invoices'] }}</h3>
          </div>
          <div class="avatar">
            <div class="avatar-initial bg-label-secondary rounded">
              <i class="fas fa-file-invoice"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row mb-4">
  <!-- Revenue Chart -->
  <div class="col-md-8 mb-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Monthly Revenue</h5>
        <div class="dropdown">
          <button class="btn p-0" type="button" data-bs-toggle="dropdown">
            <i class="fas fa-ellipsis-v"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end">
            <a class="dropdown-item" href="javascript:void(0);">Last 12 Months</a>
            <a class="dropdown-item" href="javascript:void(0);">This Year</a>
            <a class="dropdown-item" href="javascript:void(0);">Custom Range</a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div id="revenueChart"></div>
      </div>
    </div>
  </div>

  <!-- Invoice Status Chart -->
  <div class="col-md-4 mb-4">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="mb-0">Invoice Status</h5>
      </div>
      <div class="card-body">
        <div id="statusChart"></div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <!-- Overdue Invoices -->
  <div class="col-md-6 col-lg-4 mb-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Overdue Invoices</h5>
        <a href="{{ route('pms.finance.invoices.index') }}" class="btn btn-sm btn-outline-primary">View All Invoices</a>
        <span class="badge bg-danger">{{ $overdueInvoices->count() }}</span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover table-borderless">
            <tbody>
              @forelse($overdueInvoices as $invoice)
              <tr>
                <td>
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <h6 class="mb-1">{{ $invoice->invoice_number }}</h6>
                      <small class="text-muted">{{ $invoice->project->title ?? 'N/A' }}</small>
                    </div>
                    <div class="text-end">
                      <span class="d-block fw-semibold">{{ number_format($invoice->balance_amount, 2) }}</span>
                      <small class="text-danger">{{ $invoice->due_date->diffForHumans() }}</small>
                    </div>
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td class="text-center py-4">No overdue invoices</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
      <div class="card-footer text-center">
        <a href="{{ route('pms.finance.invoices.index') }}" class="btn btn-sm btn-outline-primary">View All Invoices</a>
      </div>
    </div>
  </div>

  <!-- Draft Invoices -->
  <div class="col-md-6 col-lg-4 mb-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Draft Invoices</h5>
        <a href="{{ route('pms.finance.invoices.index') }}" class="btn btn-sm btn-outline-secondary">Process Drafts</a>
        <span class="badge bg-secondary">{{ $invoices->get(0, collect())->count() }}</span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover table-borderless">
            <tbody>
              @forelse($invoices->get(0, []) as $invoice)
              <tr>
                <td>
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <h6 class="mb-1">{{ $invoice->project->title ?? 'N/A'}}</h6>
                      <small class="text-muted">Requested by {{ $invoice->requestedBy->name }}</small>
                      <a href="{{ route('pms.finance.invoices.process', $invoice->id) }}"
                        class="btn btn-sm btn-primary p-2" title="Edit">
                        Process
                      </a>
                    </div>
                    <div class="text-end">
                      <span class="d-block fw-semibold">{{ number_format($invoice->amount, 2) }}</span>
                      <small class="text-muted">{{ $invoice->created_at->diffForHumans() }}</small>
                    </div>
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td class="text-center py-4">No draft invoices</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
      <div class="card-footer text-center">
        <a href="{{ route('pms.finance.invoices.index') }}" class="btn btn-sm btn-outline-secondary">Process Drafts</a>
      </div>
    </div>
  </div>

  <!-- Recent Payments -->
  <div class="col-md-6 col-lg-4 mb-4">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="mb-0">Recent Payments</h5>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover table-borderless">
            <tbody>
              @forelse($recentPayments as $payment)
              <tr>
                <td>
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <h6 class="mb-1">{{ $payment->invoice->invoice_number }}</h6>
                      <small class="text-muted">{{ $payment->invoice->project->title ?? 'N/A'}}</small>
                    </div>
                    <div class="text-end">
                      <span class="d-block fw-semibold">{{ number_format($payment->amount, 2) }}</span>
                      <small class="text-muted">{{ $payment->payment_date->format('M d, Y') }}</small>
                    </div>
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td class="text-center py-4">No recent payments</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
      <div class="card-footer text-center">
        <a href="{{ route('pms.finance.invoices.index') }}" class="btn btn-sm btn-outline-success">View All Payments</a>
      </div>
    </div>
  </div>
</div>
@endsection