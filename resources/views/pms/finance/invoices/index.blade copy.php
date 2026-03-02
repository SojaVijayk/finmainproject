@extends('layouts/layoutMaster')

@section('title', 'Invoice Management')

@section('content')
@php
use \App\Models\PMS\Project;
use \App\Models\PMS\Invoice;

@endphp
<div class="card">
  <div class="card-header">
    <h5 class="mb-0">Invoices by Project</h5>
  </div>
  <div class="card-body">
    <div class="accordion" id="invoiceAccordion">
      @foreach($projects as $project)
      <div class="accordion-item">
        <h2 class="accordion-header" id="heading{{ $project->id }}">
          <button class="accordion-button" type="button" data-bs-toggle="collapse"
            data-bs-target="#collapse{{ $project->id }}" aria-expanded="true"
            aria-controls="collapse{{ $project->id }}">
            <div class="d-flex justify-content-between w-100">
              <span>{{ $project->title }}</span>
              <span class="badge bg-primary rounded-pill ms-2">
                {{ $project->invoices->count() }} invoices
              </span>
            </div>
          </button>
        </h2>
        <div id="collapse{{ $project->id }}" class="accordion-collapse collapse show"
          aria-labelledby="heading{{ $project->id }}" data-bs-parent="#invoiceAccordion">
          <div class="accordion-body p-0">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Invoice #</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Balance</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($project->invoices as $invoice)
                  <tr>
                    <td>
                      @if($invoice->invoice_number)
                      {{ $invoice->invoice_number }}
                      @else
                      <span class="text-muted">Draft</span>
                      @endif
                    </td>
                    <td>
                      <span class="badge bg-{{$invoice->invoice_type == 1 ? 'primary' : 'success'}}">
                        {{ $invoice->invoice_type == 1 ? 'Proforma Invoice' : 'Tax Invoice' }}
                      </span>
                    </td>
                    <td>
                      <span class="badge bg-{{ $invoice->status_badge_color }}">
                        {{ $invoice->status_name }}
                      </span>
                    </td>
                    <td>{{ $invoice->invoice_date?->format('M d, Y') ?? '-' }}</td>
                    <td>{{ number_format($invoice->amount, 2) }}</td>
                    <td>
                      @if($invoice->status == Invoice::STATUS_PAID)
                      <span class="text-success">Paid</span>
                      @else
                      {{ number_format($invoice->balance_amount, 2) }}
                      @endif
                    </td>
                    <td>
                      <a href="{{ route('pms.finance.invoices.show', $invoice->id) }}" class="btn btn-sm btn-icon">
                        <i class="fas fa-eye"></i>
                      </a>
                      @if($invoice->status == Invoice::STATUS_DRAFT)
                      <a href="{{ route('pms.finance.invoices.process', $invoice->id) }}" class="btn btn-sm btn-icon">
                        <i class="fas fa-edit"></i>
                      </a>
                      @endif
                    </td>
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
  </div>
</div>
@endsection