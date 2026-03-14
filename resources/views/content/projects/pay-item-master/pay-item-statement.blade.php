@extends('layouts/layoutMaster')

@section('title', 'Pay Item Bill Statement')

@section('page-style')
<style>
    .statement-header {
        border-bottom: 2px solid #696cff;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
    }
    .company-logo {
        max-height: 60px;
    }
    .table th {
        background-color: #f8f9fa !important;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 1px;
    }
    .amount-column {
        font-family: 'Courier New', Courier, monospace;
        font-weight: 600;
    }
    @media print {
        .btn-print { display: none; }
        .layout-navbar, .layout-footer { display: none !important; }
        .content-wrapper { padding: 0 !important; }
    }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card p-5">
        <div class="d-flex justify-content-between align-items-center mb-4 btn-print">
            <h4 class="mb-0">Pay Item Statement</h4>
            <div>
                <button onclick="window.print()" class="btn btn-primary me-2">
                    <i class="ti ti-printer me-1"></i> Print
                </button>
                <a href="{{ route('pms.pay-item-master.statement', array_merge(request()->all(), ['format' => 'pdf'])) }}" class="btn btn-danger">
                    <i class="ti ti-file-type-pdf me-1"></i> Download PDF
                </a>
            </div>
        </div>

        <div id="printable-area">
            <div class="statement-header d-flex justify-content-between align-items-start">
                <div>
                    <h3 class="fw-bold mb-1 text-primary">{{ $project->title ?? 'Main Project' }}</h3>
                    <p class="text-muted mb-0">Project Location: {{ $project->location ?? 'Site Office' }}</p>
                </div>
                <div class="text-end">
                    <h5 class="mb-1">BATCH STATEMENT</h5>
                    <p class="mb-0 text-muted">Generated: {{ date('d-M-Y H:i') }}</p>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <p class="mb-1"><span class="fw-bold">Pay Item:</span> {{ $payItem->name }}</p>
                    <p class="mb-1"><span class="fw-bold">Type:</span> {{ $payItem->type }}</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-1"><span class="fw-bold">Period:</span> {{ $periodLabel }}</p>
                    <p class="mb-1"><span class="fw-bold">Total Recipients:</span> {{ count($statementData) }}</p>
                </div>
            </div>

            <div class="table-responsive border rounded">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
                            <th>Employee Name</th>
                            <th>P_ID</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $grandTotal = 0; @endphp
                        @foreach($statementData as $index => $row)
                            @php $grandTotal += $row->amount; @endphp
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>
                                    <span class="fw-bold">{{ $row->name }}</span>
                                </td>
                                <td><span class="badge bg-label-secondary small">{{ $row->p_id }}</span></td>
                                <td class="text-end amount-column">
                                    ₹{{ number_format($row->amount, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light border-top">
                        <tr>
                            <th colspan="3" class="text-end py-3">Grand Total:</th>
                            <th class="text-end py-3 text-primary h5 mb-0 amount-column">
                                ₹{{ number_format($grandTotal, 2) }}
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-5 pt-4 d-flex justify-content-between border-top">
                <div class="text-center" style="width: 200px;">
                    <div style="height: 50px;"></div>
                    <div class="border-top pt-2 small text-muted">Prepared By</div>
                </div>
                <div class="text-center" style="width: 200px;">
                    <div style="height: 50px;"></div>
                    <div class="border-top pt-2 small text-muted">Checked By</div>
                </div>
                <div class="text-center" style="width: 200px;">
                    <div style="height: 50px;"></div>
                    <div class="border-top pt-2 small text-muted">Authorized Signatory</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
