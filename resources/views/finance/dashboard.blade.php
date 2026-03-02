@extends('layouts/layoutMaster')

@section('title', 'Finance Dashboard')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />
@endsection

@section('vendor-script')
<script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
@endsection

@section('content')
@php
$user = Auth::user();

@endphp
<div class="row mb-4">
  <div class="col-md-6">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Finance /</span> Bank Dashboard</h4>
  </div>

  @if ($errors->any())
  <div class="alert alert-danger">
    <ul>
      @foreach ($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
  @endif
  <div class="col-md-6 text-end">
    <!-- Date Filter Form -->
    <form action="{{ route('pms.finance.bank-dashboard') }}" method="GET" class="d-inline-flex align-items-center">
      <input type="date" name="date" class="form-control me-2" value="{{ $date }}" onchange="this.form.submit()">
    </form>
    @if($user->hasRole('finance'))
    <a href="{{ route('pms.finance.accounts.index') }}" class="btn btn-primary">Bank Account</a>
    <a href="{{ route('pms.finance.transactions.index') }}" class="btn btn-dark">Bank Transaction</a>

    <!-- Import Button -->
    <button type="button" class="btn btn-success ms-2" data-bs-toggle="modal" data-bs-target="#importModal">
      Import Excel
    </button>
    @endif
  </div>

</div>

<!-- Summary Cards -->
<div class="row">
  <div class="col-md-3 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <h6 class="card-title text-muted">Opening Balance</h6>
        <h3 class="mb-0">₹{{ number_format($totalOpening, 2) }}</h3>
      </div>
    </div>
  </div>
  <div class="col-md-3 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <h6 class="card-title text-muted">Receipts</h6>
        <h3 class="text-success mb-0">₹{{ number_format($totalReceipts, 2) }}</h3>
      </div>
    </div>
  </div>
  <div class="col-md-3 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <h6 class="card-title text-muted">Payments</h6>
        <h3 class="text-danger mb-0">₹{{ number_format($totalPayments, 2) }}</h3>
      </div>
    </div>
  </div>
  <div class="col-md-3 mb-4">
    <div class="card h-100">
      <div class="card-body">
        <h6 class="card-title text-muted">Closing Balance</h6>
        <h3 class="text-primary mb-0">₹{{ number_format($totalClosing, 2) }}</h3>
      </div>
    </div>
  </div>
</div>



<!-- Detailed Table -->
<div class="card">
  <h5 class="card-header">Daily Account Details - {{ \Carbon\Carbon::parse($date)->format('d M Y') }}</h5>
  <div class="table-responsive text-nowrap">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>Account Name</th>
          <th>Account Number</th>
          <th>Bank Name</th>
          <th class="text-end">Opening Balance</th>
          <th class="text-end">Receipts</th>
          <th class="text-end">Payments</th>
          <th class="text-end">Closing Balance</th>
        </tr>
      </thead>
      <tbody>
        @forelse($balances as $balance)
        <tr
          onclick="window.location='{{ route('pms.finance.transactions.index', ['account_id' => $balance->finance_bank_account_id]) }}'"
          style="cursor: pointer;">
          <td>{{ $balance->bankAccount->account_name }}</td>
          <td>{{ $balance->bankAccount->account_number }}</td>
          <td>{{ $balance->bankAccount->bank_name }}</td>
          <td class="text-end">{{ number_format($balance->opening_balance, 2) }}</td>
          <td class="text-end text-success">{{ number_format($balance->receipts, 2) }}</td>
          <td class="text-end text-danger">{{ number_format($balance->payments, 2) }}</td>
          <td class="text-end fw-bold">{{ number_format($balance->closing_balance, 2) }}</td>
        </tr>
        @empty
        <tr>
          <td colspan="7" class="text-center py-4">No data found for this date.</td>
        </tr>
        @endforelse
      </tbody>
      <tfoot>
        <tr class="table-light fw-bold">
          <td colspan="3">Total</td>
          <td class="text-end">{{ number_format($totalOpening, 2) }}</td>
          <td class="text-end text-success">{{ number_format($totalReceipts, 2) }}</td>
          <td class="text-end text-danger">{{ number_format($totalPayments, 2) }}</td>
          <td class="text-end">{{ number_format($totalClosing, 2) }}</td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>
<!-- Charts Section -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header header-elements">
        <h5 class="card-title mb-0">Receipts vs Payments (Last 7 Days)</h5>
      </div>
      <div class="card-body">
        <div id="receiptsPaymentsChart"></div>
      </div>
    </div>
  </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form action="{{ route('pms.finance.transactions.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Import Daily Bank Balances</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col mb-3">
              <label for="file" class="form-label">Excel File</label>
              <input type="file" id="file" name="file" class="form-control" accept=".xlsx,.csv" required>
              <div class="form-text">
                Format: Date | Account | Opening | Receipts | Payments | Closing
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Import</button>
        </div>
      </form>
    </div>
  </div>
</div>

@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    var options = {
      series: [{
        name: 'Receipts',
        data: @json($chartReceipts)
      }, {
        name: 'Payments',
        data: @json($chartPayments)
      }],
      chart: {
        type: 'bar',
        height: 350,
        toolbar: { show: false }
      },
      plotOptions: {
        bar: {
          horizontal: false,
          columnWidth: '55%',
          endingShape: 'rounded'
        },
      },
      dataLabels: { enabled: false },
      stroke: {
        show: true,
        width: 2,
        colors: ['transparent']
      },
      xaxis: {
        categories: @json($chartDates),
      },
      yaxis: {
        title: { text: 'Amount (₹)' }
      },
      fill: { opacity: 1 },
      colors: ['#28c76f', '#ea5455'],
      tooltip: {
        y: {
          formatter: function (val) {
            return "₹ " + val.toLocaleString()
          }
        }
      }
    };

    var chart = new ApexCharts(document.querySelector("#receiptsPaymentsChart"), options);
    chart.render();
  });
</script>
@php echo "fjd";
print_r($chartReceipts);
@endphp
@endsection
@endsection