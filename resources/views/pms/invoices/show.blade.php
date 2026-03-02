@extends('layouts/layoutMaster')

@section('title', 'View Invoices')

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

@endsection
@section('header', 'Invoice #' . $invoice->invoice_number)

@section('content')
<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">Invoice Details</h5>
          <span class="badge bg-{{$invoice->invoice_type == 1 ? 'primary' : 'success'}}">
            {{ $invoice->invoice_type == 1 ? 'Proforma Invoice' : 'Tax Invoice' }}
          </span>
          <span class="badge bg-{{ $invoice->status_badge_color }}">
            {{ $invoice->status_name }}
          </span>
        </div>
      </div>
      <div class="card-body">
        <div class="row mb-4">
          <div class="col-md-6">
            <h6>From:</h6>
            <p>
              <strong>Centre for Management Development</strong><br>
              {{ config('app.address') }}<br>
              {{ config('app.city') }}, {{ config('app.state') }}<br>
              {{ config('app.country') }} - 695014<br>
              GSTIN: {{ config('app.gstin') }}
            </p>
          </div>
          <div class="col-md-6 text-end">
            <h6>To:</h6>
            <p>
              <strong>{{ $project->requirement->client->client_name }}</strong><br>
              {{ $project->requirement->client->address }}<br>
              {{ $project->requirement->client->city }}, {{ $project->requirement->client->state }}<br>
              {{ $project->requirement->client->country }} - {{ $project->requirement->client->pincode }}<br>
              GSTIN: {{ $project->requirement->client->gstin ?? 'N/A' }}
            </p>
          </div>
        </div>

        <div class="row mb-4">
          <div class="col-md-4">
            <p><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</p>
          </div>
          <div class="col-md-4">
            <p><strong>Invoice Date:</strong> {{ $invoice->invoice_date->format('d M Y') }}</p>
          </div>
          <div class="col-md-4">
            <p><strong>Due Date:</strong> {{ $invoice->due_date->format('d M Y') }}</p>
          </div>
        </div>

        @if($invoice->milestone)
        <div class="mb-4">
          <h6>Milestone:</h6>
          <p>{{ $invoice->milestone->name }} ({{ $invoice->milestone->weightage }}%)</p>
        </div>
        @endif

        <div class="mb-4">
          <h6>Description:</h6>
          <p>{{ $invoice->description ?? 'No description provided' }}</p>
        </div>

        {{-- <div class="table-responsive mb-4">
          <table class="table">
            <thead>
              <tr>
                <th>Description</th>
                <th class="text-end">Amount (₹)</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Invoice Amount</td>
                <td class="text-end">{{ number_format($invoice->amount, 2) }}</td>
              </tr>
              <tr>
                <td>Tax ({{ config('app.tax_rate') }}%)</td>
                <td class="text-end">{{ number_format($invoice->amount * (config('app.tax_rate', 18) / 100), 2) }}</td>
              </tr>
              <tr class="table-active">
                <th>Total Amount</th>
                <th class="text-end">{{ number_format($invoice->amount * (1 + (config('app.tax_rate', 18) / 100)), 2) }}
                </th>
              </tr>
            </tbody>
          </table>
        </div> --}}

        <div class="table-responsive mb-4">
          <table class="table">
            <thead>
              <tr>
                <th>Description</th>
                <th class="text-end">Amount (₹)</th>
                <th class="text-end">Tax (%)</th>
                <th class="text-end">Tax Amount (₹)</th>
                <th class="text-end">Total (₹)</th>
              </tr>
            </thead>
            <tbody>
              @php
              $subTotal = 0;
              $totalTax = 0;
              $grandTotal = 0;
              @endphp

              @foreach($invoice->items as $item)
              @php
              $subTotal += $item->amount;
              $totalTax += $item->tax_amount;
              $grandTotal += $item->total_with_tax;
              @endphp
              <tr>
                <td>{{ $item->description }}</td>
                <td class="text-end">{{ number_format($item->amount, 2) }}</td>
                <td class="text-end">{{ number_format($item->tax_percentage, 2) }}</td>
                <td class="text-end">{{ number_format($item->tax_amount, 2) }}</td>
                <td class="text-end">{{ number_format($item->total_with_tax, 2) }}</td>
              </tr>
              @endforeach

              <tr>
                <th>Subtotal</th>
                <th class="text-end">{{ number_format($subTotal, 2) }}</th>
                <th></th>
                <th class="text-end">{{ number_format($totalTax, 2) }}</th>
                <th></th>
              </tr>
              <tr class="table-active">
                <th colspan="4">Grand Total</th>
                <th class="text-end">{{ number_format($grandTotal, 2) }}</th>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="mb-4">
          <h6>Payment Status:</h6>
          <div class="progress mb-2" style="height: 25px;">
            <div class="progress-bar" role="progressbar"
              style="width: {{ ($invoice->paid_amount / $invoice->amount) * 100 }}%"
              aria-valuenow="{{ ($invoice->paid_amount / $invoice->amount) * 100 }}" aria-valuemin="0"
              aria-valuemax="100">
              {{ number_format(($invoice->paid_amount / $invoice->amount) * 100, 2) }}%
            </div>
          </div>
          <p>
            <strong>Paid:</strong> ₹{{ number_format($invoice->paid_amount, 2) }} |
            <strong>Balance:</strong> ₹{{ number_format($invoice->balance_amount, 2) }}
          </p>
        </div>

        @if($invoice->payments->count() > 0)
        <div class="mb-4">
          <h6>Payment History:</h6>
          <div class="table-responsive">
            <table class="table table-sm">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Amount</th>
                  <th>Method</th>
                  <th>Reference</th>
                  <th>Recorded By</th>
                </tr>
              </thead>
              <tbody>
                @foreach($invoice->payments as $payment)
                <tr>
                  <td>{{ $payment->payment_date->format('d M Y') }}</td>
                  <td>₹{{ number_format($payment->amount, 2) }}</td>
                  <td>{{ $payment->payment_method }}</td>
                  <td>{{ $payment->transaction_reference ?? 'N/A' }}</td>
                  <td>{{ $payment->recordedBy->name }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
        @endif
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title">Actions</h5>
      </div>
      <div class="card-body">
        {{-- @if($invoice->status == \App\Models\PMS\Invoice::STATUS_DRAFT && auth()->user()->hasRole('finance'))
        <form action="{{ route('pms.invoices.generate', [$project->id, $invoice->id]) }}" method="POST" class="mb-3">
          @csrf
          <button type="submit" class="btn btn-success w-100">
            <i class="fas fa-file-invoice"></i> Generate Invoice
          </button>
        </form>
        @endif --}}

        @if($invoice->status == \App\Models\PMS\Invoice::STATUS_SENT && auth()->user()->hasRole('finance'))
        <form action="{{ route('pms.invoices.pay', [$project->id, $invoice->id]) }}" method="POST" class="mb-3">
          @csrf
          <button type="submit" class="btn btn-primary w-100">
            <i class="fas fa-check-circle"></i> Mark as Paid
          </button>
        </form>
        @endif

        @if($invoice->status != \App\Models\PMS\Invoice::STATUS_PAID && auth()->user()->hasRole('finance'))
        <div class="mb-3">
          <form action="{{ route('pms.invoices.add-payment', [$project->id, $invoice->id]) }}" method="POST">
            @csrf
            <div class="mb-3">
              <label for="amount" class="form-label">Payment Amount (₹)</label>
              <input type="number" step="0.01" min="0.01" max="{{ $invoice->balance_amount }}" name="amount" id="amount"
                class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="payment_date" class="form-label">Payment Date</label>
              <input type="date" name="payment_date" id="payment_date" class="form-control" value="{{ date('Y-m-d') }}"
                required>
            </div>
            <div class="mb-3">
              <label for="payment_method" class="form-label">Payment Method</label>
              <select name="payment_method" id="payment_method" class="form-select" required>
                <option value="">Select Method</option>
                <option value="Bank Transfer">Bank Transfer</option>
                <option value="Cheque">Cheque</option>
                <option value="Cash">Cash</option>
                <option value="Credit Card">Credit Card</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="transaction_reference" class="form-label">Reference No</label>
              <input type="text" name="transaction_reference" id="transaction_reference" class="form-control">
            </div>
            <div class="mb-3">
              <label for="notes" class="form-label">Notes</label>
              <textarea name="notes" id="notes" class="form-control" rows="2"></textarea>
            </div>
            <button type="submit" class="btn btn-info w-100">
              <i class="fas fa-money-bill-wave"></i> Record Payment
            </button>
          </form>
        </div>
        @endif

        <div class="list-group mt-3">
          <div class="list-group-item">
            <strong>Requested By:</strong> {{ $invoice->requestedBy->name }}
          </div>
          @if($invoice->generated_by)
          <div class="list-group-item">
            <strong>Generated By:</strong> {{ $invoice->generatedBy->name }}
          </div>
          @endif
          <div class="list-group-item">
            <strong>Created At:</strong> {{ $invoice->created_at->format('d M Y H:i') }}
          </div>
        </div>
      </div>
    </div>

    <div class="card mt-4">
      <div class="card-header">
        <h5 class="card-title">Project Details</h5>
      </div>
      <div class="card-body">
        <p><strong>Project Code:</strong> {{ $project->project_code }}</p>
        <p><strong>Title:</strong> {{ $project->title }}</p>
        <p><strong>Client:</strong> {{ $project->requirement->client->client_name }}</p>
        <p><strong>Investigator:</strong> {{ $project->investigator->name }}</p>
        <a href="{{ route('pms.projects.show', $project->id) }}" class="btn btn-sm btn-info w-100 mt-2">
          <i class="fas fa-eye"></i> View Project
        </a>
      </div>
    </div>
  </div>
</div>
@endsection