@extends('layouts/layoutMaster')

@section('title', 'Project Detailed Report')

{{-- Vendor Styles --}}
@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">
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

{{-- Page Scripts --}}
@section('page-script')
<script>
  $(document).ready(function() {
    $('#projectReportTable').DataTable({
      responsive: true,
      pageLength: 25
    });
  });
</script>
@endsection

{{-- Main Content --}}
@section('content')
<div class="card shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h5 class="mb-0">📊 Project Detailed Report</h5>
    <a href="{{ route('pms.reports.export', ['type'=>'project-detailed']) }}" class="btn btn-success btn-sm">
      <i class="ti ti-file-export me-1"></i> Export
    </a>
  </div>

  <div class="card-body border-bottom pb-4">
    <form method="GET" class="row g-3 align-items-end">
      <div class="col-md-2">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
          @foreach($statuses as $key => $val)
          <option value="{{ $key }}" {{ $status==$key ? 'selected' : '' }}>{{ $val }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-md-3">
        <label class="form-label">Client</label>
        <select name="client" class="form-select">
          <option value="all">All Clients</option>
          @foreach($clients as $c)
          <option value="{{ $c->id }}" {{ $client==$c->id ? 'selected' : '' }}>{{ $c->client_name }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-md-3">
        <label class="form-label">Category</label>
        <select name="category" class="form-select">
          <option value="all">All Categories</option>
          @foreach($categories as $cat)
          <option value="{{ $cat->id }}" {{ $category==$cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-md-3">
        <label class="form-label">Investigator</label>
        <select name="investigator" class="form-select">
          <option value="all">All Investigators</option>
          @foreach($investigators as $inv)
          <option value="{{ $inv->id }}" {{ $investigator==$inv->id ? 'selected' : '' }}>{{ $inv->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-md-1 text-end">
        <button type="submit" class="btn btn-primary w-100">
          <i class="ti ti-filter"></i>
        </button>
      </div>
    </form>
  </div>

  <div class="card-body pt-3">
    @if($projects->count())
    <div class="table-responsive">
      <table class="table table-hover table-bordered align-middle" id="projectReportTable">
        <thead class="table-light">
          <tr>
            <th>Code</th>
            <th>Title</th>
            <th>Client</th>
            <th>Category</th>
            <th>Investigator</th>
            <th>Status</th>
            <th class="text-end">Budget</th>
            <th class="text-end">Est. Exp.</th>
            <th class="text-end">Actual Exp.</th>
            <th class="text-end">Invoiced</th>
            <th class="text-end">Paid</th>
            <th class="text-end">Balance</th>
            <th class="text-center">Details</th>
          </tr>
        </thead>
        <tbody>
          @foreach($projects as $p)
          @php
          $actualExpense = $p->expenses->sum('total_amount');
          $invoiced = $p->invoices->sum('total_amount');
          $paid = $p->invoices->sum(fn($i) => $i->payments->sum('amount'));
          @endphp
          <tr>
            <td>{{ $p->project_code }}</td>
            <td>{{ Str::limit($p->title, 40) }}</td>
            <td>{{ $p->requirement->client->client_name ?? '-' }}</td>
            <td>{{ $p->requirement->category->name ?? '-' }}</td>
            <td>{{ $p->investigator->name ?? '-' }}</td>
            <td><span class="badge bg-{{ $p->status_badge_color }}">{{ $p->status_name }}</span></td>
            <td class="text-end">{{ number_format($p->budget, 2) }}</td>
            <td class="text-end">{{ number_format($p->estimated_expense, 2) }}</td>
            <td class="text-end text-danger">{{ number_format($actualExpense, 2) }}</td>
            <td class="text-end">{{ number_format($invoiced, 2) }}</td>
            <td class="text-end text-success">{{ number_format($paid, 2) }}</td>
            <td class="text-end text-warning">{{ number_format($invoiced - $paid, 2) }}</td>
            <td class="text-center">
              <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                data-bs-target="#projectModal{{ $p->id }}">
                <i class="ti ti-eye"></i>
              </button>
            </td>
          </tr>

          {{-- Modal --}}
          <div class="modal fade" id="projectModal{{ $p->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
              <div class="modal-content">
                <div class="modal-header bg-light">
                  <h5 class="modal-title">
                    {{ $p->title }} <small class="text-muted">({{ $p->project_code }})</small>
                  </h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <div class="row mb-3">
                    <div class="col-md-6"><strong>Investigator:</strong> {{ $p->investigator->name ?? '-' }}</div>
                    <div class="col-md-6"><strong>Duration:</strong> {{ $p->start_date->format('d M Y') }} → {{
                      $p->end_date->format('d M Y') }}</div>
                  </div>

                  <h6 class="border-bottom pb-1">Expense Breakdown</h6>
                  <ul>
                    @foreach($p->expenses as $ex)
                    <li>{{ $ex->category->name ?? '-' }} - ₹{{ number_format($ex->total_amount, 2) }}</li>
                    @endforeach
                  </ul>

                  <h6 class="border-bottom pb-1 mt-3">Invoice Details</h6>
                  <table class="table table-sm table-striped">
                    <thead>
                      <tr>
                        <th>Type</th>
                        <th>Status</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Paid</th>
                        <th class="text-end">Balance</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($p->invoices as $inv)
                      @php $paidAmt = $inv->payments->sum('amount'); @endphp
                      <tr>
                        <td>{{ $inv->invoice_type == 1 ? 'Proforma' : 'Tax' }}</td>
                        <td><span class="badge bg-{{ $inv->status_badge_color }}">{{ $inv->status_name }}</span></td>
                        <td class="text-end">{{ number_format($inv->total_amount, 2) }}</td>
                        <td class="text-end">{{ number_format($paidAmt, 2) }}</td>
                        <td class="text-end">{{ number_format($inv->total_amount - $paidAmt, 2) }}</td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
          @endforeach
        </tbody>
        <tfoot class="table-secondary fw-semibold">
          <tr>
            <td colspan="6" class="text-end">TOTAL:</td>
            <td class="text-end">{{ number_format($totals['budget'], 2) }}</td>
            <td class="text-end">{{ number_format($totals['estimated_expense'], 2) }}</td>
            <td class="text-end">{{ number_format($totals['actual_expense'], 2) }}</td>
            <td class="text-end">{{ number_format($totals['invoiced'], 2) }}</td>
            <td class="text-end">{{ number_format($totals['paid'], 2) }}</td>
            <td class="text-end">{{ number_format($totals['balance'], 2) }}</td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </div>
    @else
    <div class="alert alert-info mb-0">No projects found for the selected filters.</div>
    @endif
  </div>
</div>
@endsection