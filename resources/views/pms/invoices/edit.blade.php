@extends('layouts/layoutMaster')

@section('title', 'Edit Invoices')

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
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Set due date minimum based on invoice date
    const invoiceDateInput = document.getElementById('invoice_date');
    const dueDateInput = document.getElementById('due_date');

    invoiceDateInput.addEventListener('change', function() {
        dueDateInput.min = this.value;

        // If due date is before invoice date, reset it
        if (dueDateInput.value && dueDateInput.value < this.value) {
            dueDateInput.value = '';
        }
    });

    // Auto-calculate amount if milestone is selected
    const milestoneSelect = document.getElementById('milestone_id');
    const amountInput = document.getElementById('amount');

    {{--  milestoneSelect.addEventListener('change', function() {
        if (this.value) {
            const milestone = @json($milestones->keyBy('id'));
            const milestoneData = milestone[this.value];
            const amount = (milestoneData.weightage / 100) * {{ $project->budget }};
            amountInput.value = amount.toFixed(2);
        }
    });  --}}

    function recalculateTotals() {
    {{--  let grandTotal = 0;
    let total_amount_without_tax = 0;  --}}
     let subtotal = 0, totalTax = 0, grandTotal = 0;
    document.querySelectorAll('#items-table tbody tr').forEach(row => {
      const amount = parseFloat(row.querySelector('.amount')?.value || 0);
      const taxPercentage = parseFloat(row.querySelector('.tax-percentage')?.value || 0);
      const taxAmount = amount * taxPercentage / 100;
      const total = amount + taxAmount;


      row.querySelector('.tax-amount').value = taxAmount.toFixed(2);
      row.querySelector('.total-with-tax').value = total.toFixed(2);
      {{--  grandTotal += total;
      total_amount_without_tax +=amount;  --}}

      subtotal += amount;
      totalTax += taxAmount;
      grandTotal += total;
    });
    {{--  document.getElementById('grand-total').textContent = grandTotal.toFixed(2);
    document.getElementById('amount').value = total_amount_without_tax.toFixed(2);  --}}
    document.getElementById('subtotal').value = subtotal.toFixed(2);
    document.getElementById('total_tax').value = totalTax.toFixed(2);
    document.getElementById('grand_total').value = grandTotal.toFixed(2);
    {{--  document.getElementById('amount').value = subtotal.toFixed(2);  --}}
  }

  document.getElementById('items-table').addEventListener('input', recalculateTotals);

  document.getElementById('add-item').addEventListener('click', function() {
    const tbody = document.querySelector('#items-table tbody');
    const index = tbody.rows.length;
    const newRow = document.createElement('tr');
    newRow.innerHTML = `
      <td><input type="text" name="items[${index}][description]" class="form-control" required></td>
      <td><input type="number" step="0.01" min="0" name="items[${index}][amount]" class="form-control amount" required></td>
      <td><input type="number" step="0.01" min="0" max="100" name="items[${index}][tax_percentage]" class="form-control tax-percentage" required></td>
      <td><input type="number" step="0.01" name="items[${index}][tax_amount]" class="form-control tax-amount" readonly></td>
      <td><input type="number" step="0.01" name="items[${index}][total_with_tax]" class="form-control total-with-tax" readonly></td>
      <td><button type="button" class="btn btn-sm btn-danger remove-item">X</button></td>
    `;
    tbody.appendChild(newRow);
  });

  document.querySelector('#items-table').addEventListener('click', e => {
    if (e.target.classList.contains('remove-item')) {
      e.target.closest('tr').remove();
      recalculateTotals();
    }
  });

  recalculateTotals();


});
</script>
@endsection
@section('header', 'Edit Invoice #' . $invoice->invoice_number)

@section('content')
<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-body">
        <form action="{{ route('pms.invoices.update', [$project->id, $invoice->id]) }}" method="POST">
          @csrf
          @method('PUT')

          <div class="row mb-3">
            <div class="col-md-6">
              <label for="milestone_id" class="form-label">Milestone (Optional)</label>
              <select name="milestone_id" id="milestone_id" class="form-select">
                <option value="">Select Milestone</option>
                @foreach($milestones as $milestone)
                <option value="{{ $milestone->id }}" {{ $invoice->milestone_id == $milestone->id ? 'selected' : '' }}>
                  {{ $milestone->name }} ({{ $milestone->weightage }}%)
                </option>
                @endforeach
              </select>
              @error('milestone_id')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label for="invoice_type" class="form-label">Invoice Type</label>
              <select name="invoice_type" id="invoice_type" class="form-select">
                <option value="">Select Invoice Type</option>

                <option value="1" {{ $invoice->invoice_type == 1 ? 'selected' : ''
                  }}>Proforma Invoice</option>
                <option value="2" {{ $invoice->invoice_type == 2 ? 'selected' : '' }}>Tax Invoice</option>

              </select>
              @error('milestone_id')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label for="invoice_date" class="form-label">Invoice Date</label>
              <input type="date" name="invoice_date" id="invoice_date" class="form-control"
                value="{{ old('invoice_date', $invoice->invoice_date->format('Y-m-d')) }}" required>
              @error('invoice_date')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label for="due_date" class="form-label">Due Date *</label>
              <input type="date" name="due_date" id="due_date" class="form-control"
                value="{{ old('due_date', $invoice->due_date->format('Y-m-d')) }}">
              @error('due_date')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>
          <h6 class="mb-3">Invoice Items</h6>
          <table class="table table-bordered" id="items-table">
            <thead>
              <tr>
                <th>Description</th>
                <th>Amount (₹)</th>
                <th>Tax (%)</th>
                <th>Tax Amount (₹)</th>
                <th>Total (₹)</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($invoice->items as $index => $item)
              <tr>
                <td><input type="text" name="items[{{ $index }}][description]" class="form-control"
                    value="{{ $item->description }}" required></td>
                <td><input type="number" step="0.01" min="0" name="items[{{ $index }}][amount]"
                    class="form-control amount" value="{{ $item->amount }}" required></td>
                <td><input type="number" step="0.01" min="0" max="100" name="items[{{ $index }}][tax_percentage]"
                    class="form-control tax-percentage" value="{{ $item->tax_percentage }}" required></td>
                <td><input type="number" step="0.01" name="items[{{ $index }}][tax_amount]"
                    class="form-control tax-amount" value="{{ $item->tax_amount }}" readonly></td>
                <td><input type="number" step="0.01" name="items[{{ $index }}][total_with_tax]"
                    class="form-control total-with-tax" value="{{ $item->total_with_tax }}" readonly></td>
                <td><button type="button" class="btn btn-sm btn-danger remove-item">X</button></td>
              </tr>
              @endforeach
            </tbody>
          </table>

          <button type="button" class="btn btn-sm btn-secondary mb-3" id="add-item">+ Add Item</button>


          <div class="row mt-3">
            <div class="col-md-4">
              <label class="form-label">Subtotal (₹)</label>
              <input type="number" id="subtotal" class="form-control" readonly>
            </div>
            <div class="col-md-4">
              <label class="form-label">Total Tax (₹)</label>
              <input type="number" id="total_tax" class="form-control" readonly>
            </div>
            <div class="col-md-4">
              <label class="form-label"><strong>Grand Total (₹)</strong></label>
              <input type="number" id="grand_total" name="amount" class="form-control" readonly>
            </div>
          </div>


          {{-- <div class="text-end mt-3">
            <strong>Total (₹): </strong> <span id="grand-total">0.00</span>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="amount" class="form-label">Amount (₹ without tax)</label>
              <input type="hidden" step="0.01" min="0" name="amount" id="amount" class="form-control"
                value="{{ old('amount', $invoice->amount) }}" required readonly>
              @error('amount')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>


          </div> --}}



          <div class="row mb-3">
            <div class="col-md-12">
              <label for="description" class="form-label">Description</label>
              <textarea name="description" id="description" class="form-control"
                rows="3">{{ old('description', $invoice->description) }}</textarea>
              @error('description')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="mt-4">
            <button type="submit" class="btn btn-primary me-2">Update Invoice</button>
            <a href="{{ route('pms.invoices.show', [$project->id, $invoice->id]) }}"
              class="btn btn-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title">Invoice Status</h5>
      </div>
      <div class="card-body">
        <div class="alert alert-{{ $invoice->status_badge_color }}">
          <strong>Status:</strong> {{ $invoice->status_name }}
        </div>

        @if($invoice->invoice_number)
        <p><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</p>
        @endif

        <p><strong>Created At:</strong> {{ $invoice->created_at->format('d M Y H:i') }}</p>
        <p><strong>Updated At:</strong> {{ $invoice->updated_at->format('d M Y H:i') }}</p>
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
        <p><strong>Budget:</strong> ₹{{ number_format($project->budget, 2) }}</p>
      </div>
    </div>
  </div>
</div>
@endsection