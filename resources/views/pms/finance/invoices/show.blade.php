@extends('layouts/layoutMaster')

@section('title', 'Invoice Details')

@section('content')
@php
use \App\Models\PMS\Project;
use \App\Models\PMS\Invoice;

@endphp
<div class="row">
  <div class="col-md-8">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Invoice #{{ $invoice->invoice_number ?? 'Draft' }}</h5>
        <span class="badge bg-{{$invoice->invoice_type == 1 ? 'primary' : 'success'}}">
          {{ $invoice->invoice_type == 1 ? 'Proforma Invoice' : 'Tax Invoice' }}
        </span>
        <span class="badge bg-{{ $invoice->status_badge_color }}">
          {{ $invoice->status_name }}
        </span>
      </div>
      <div class="card-body">
        <div class="row mb-4">
          <div class="col-md-6">
            <h6>Project Details</h6>
            {{-- <p class="mb-1"><strong>{{ $invoice->project->title }}</strong></p> --}}
            {{-- <p class="mb-1">Code: {{ $invoice->project->project_code }}</p> --}}
            @if($invoice->milestone)
            <p class="mb-0">Milestone: {{ $invoice->milestone->name }}</p>
            @endif
          </div>
          <div class="col-md-6 text-md-end">
            <h6>Invoice Details</h6>
            <p class="mb-1"><strong>Date:</strong> {{ $invoice->invoice_date?->format('M d, Y') ?? '-' }}</p>
            <p class="mb-1"><strong>Due Date:</strong> {{ $invoice->due_date?->format('M d, Y') ?? '-' }}</p>
            <p class="mb-0"><strong>Requested By:</strong> {{ $invoice->requestedBy->name }}</p>
          </div>
        </div>

        {{-- <div class="table-responsive mb-4">
          <table class="table">
            <thead>
              <tr>
                <th>Description</th>
                <th class="text-end">Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>{{ $invoice->description ?? 'No description provided' }}</td>
                <td class="text-end">{{ number_format($invoice->amount, 2) }}</td>
              </tr>
            </tbody>
            <tfoot>
              <tr>
                <th class="text-end">Total:</th>
                <th class="text-end">{{ number_format($invoice->amount, 2) }}</th>
              </tr>
              @if($invoice->paid_amount > 0)
              <tr>
                <th class="text-end">Paid:</th>
                <th class="text-end text-success">{{ number_format($invoice->paid_amount, 2) }}</th>
              </tr>
              <tr>
                <th class="text-end">Balance:</th>
                <th class="text-end">
                  @if($invoice->balance_amount > 0)
                  <span class="text-danger">{{ number_format($invoice->balance_amount, 2) }}</span>
                  @else
                  <span class="text-success">Paid</span>
                  @endif
                </th>
              </tr>
              @endif
            </tfoot>
          </table>
        </div> --}}

        {{-- ✅ Items Table with GST --}}
        <div class="table-responsive mb-4">
          <table class="table table-striped align-middle">
            <thead class="table-light">
              <tr>
                <th>Description</th>
                <th class="text-end">Amount (₹)</th>
                <th class="text-end">GST (%)</th>
                <th class="text-end">Tax (₹)</th>
                <th class="text-end">Total (₹)</th>
              </tr>
            </thead>
            <tbody>
              @php
              $subTotal = 0;
              $totalTax = 0;
              $grandTotal = 0;
              @endphp

              @forelse($invoice->items as $item)
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
              @empty
              <tr>
                <td colspan="5" class="text-center text-muted">No items added to this invoice</td>
              </tr>
              @endforelse
            </tbody>
            @if($invoice->items->count() > 0)
            <tfoot>
              <tr>
                <th colspan="4" class="text-end">Subtotal</th>
                <th class="text-end">{{ number_format($subTotal, 2) }}</th>
              </tr>
              <tr>
                <th colspan="4" class="text-end">Total GST</th>
                <th class="text-end">{{ number_format($totalTax, 2) }}</th>
              </tr>
              <tr class="table-active">
                <th colspan="4" class="text-end">Grand Total</th>
                <th class="text-end">{{ number_format($grandTotal, 2) }}</th>
              </tr>
              @if($invoice->paid_amount > 0)
              <tr>
                <th colspan="4" class="text-end text-success">Paid</th>
                <th class="text-end text-success">{{ number_format($invoice->paid_amount, 2) }}</th>
              </tr>
              <tr>
                <th colspan="4" class="text-end">Balance</th>
                <th class="text-end">
                  @if($invoice->balance_amount > 0)
                  <span class="text-danger">{{ number_format($invoice->balance_amount, 2) }}</span>
                  @else
                  <span class="text-success">Paid</span>
                  @endif
                </th>
              </tr>
              @endif
            </tfoot>
            @endif
          </table>
        </div>

        @if($invoice->status == Invoice::STATUS_DRAFT)
        <div class="d-flex justify-content-end">
          <a href="{{ route('pms.finance.invoices.process', $invoice->id) }}" class="btn btn-primary me-2">
            <i class="fas fa-edit me-2"></i>Edit Draft
          </a>
          <form action="{{ route('pms.finance.invoices.generate', $invoice->id) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success">
              <i class="fas fa-paper-plane me-2"></i>Generate Invoice
            </button>
          </form>
        </div>
        @endif
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <!-- Payment Actions -->
    @if(in_array($invoice->status, [Invoice::STATUS_SENT, Invoice::STATUS_PAID, Invoice::STATUS_OVERDUE]))
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Record Payment</h5>
      </div>
      <div class="card-body">
        <div class="d-grid">
          @if($invoice->balance_amount > 0 && $invoice->invoice_type == 2)
          <a href="{{ route('pms.finance.invoices.payment', $invoice->id) }}" class="btn btn-primary mb-3">
            <i class="fas fa-money-bill-wave me-2"></i>Record Payment
          </a>
          @endif

          {{-- @if($invoice->balance_amount == $invoice->amount)
          <form action="{{ route('pms.finance.invoices.pay', $invoice->id) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success w-100">
              <i class="fas fa-check-circle me-2"></i>Mark as Paid
            </button>
          </form>
          @endif --}}
        </div>
      </div>
    </div>
    @endif

    <!-- Payment History -->
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Payment History</h5>
        <span class="badge bg-primary rounded-pill">
          {{ $invoice->payments->count() }}
        </span>
      </div>
      <div class="card-body">
        @forelse($invoice->payments as $payment)
        <div class="border-bottom pb-3 mb-3">
          <div class="d-flex justify-content-between mb-1">
            <span class="fw-semibold">{{ number_format($payment->amount, 2) }}</span>
            <span class="text-muted">{{ $payment->payment_date->format('M d, Y') }}</span>
          </div>
          <div class="d-flex justify-content-between">
            <small class="text-muted">{{ $payment->payment_method }}</small>
            <small>Recorded by {{ $payment->recordedBy->name }}</small>
          </div>
          @if($payment->transaction_reference)
          <div class="mt-1">
            <small class="text-muted">Reference: {{ $payment->transaction_reference }}</small>
          </div>
          @endif
          @if($payment->notes)
          <div class="mt-1">
            <small class="text-muted">Notes: {{ $payment->notes }}</small>
          </div>
          @endif
        </div>
        @empty
        <p class="text-muted text-center">No payments recorded</p>
        @endforelse
      </div>
    </div>
  </div>
</div>
@endsection