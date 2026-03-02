@extends('layouts/layoutMaster')

@section('title', 'Process Draft Invoice')

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



@section('content')
<div class="card">
  <div class="card-header">
    <h5 class="mb-0">Process Draft Invoice</h5>
  </div>
  <div class="card-body">
    <form action="{{ route('pms.finance.invoices.update', $invoice->id) }}" method="POST">
      @csrf
      @method('PUT')

      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">Project</label>
          <input type="text" class="form-control" value="{{ $invoice->project->title ?? 'N/A' }}" readonly>
        </div>
        <div class="col-md-6">
          <label class="form-label">Milestone</label>
          <input type="text" class="form-control" value="{{ $invoice->milestone?->name ?? 'N/A' }}" readonly>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-4">
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
        <div class="col-md-4">
          <label for="invoice_number" class="form-label">Invoice Number*</label>
          <input type="text" class="form-control" id="invoice_number" name="invoice_number"
            value="{{ old('invoice_number', $invoice->invoice_number) }}" required>
        </div>
        <div class="col-md-4">
          <label for="due_date" class="form-label">Due Date*</label>
          <input type="date" class="form-control" id="due_date" name="due_date"
            value="{{ old('due_date', $invoice->due_date?->format('Y-m-d')) }}" required>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <label for="invoice_date" class="form-label">Invoice Date*</label>
          <input type="date" class="form-control" id="invoice_date" name="invoice_date"
            value="{{ old('invoice_date', $invoice->invoice_date?->format('Y-m-d')) }}" required>
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
            <input type="number" name="amount" id="subtotal" class="form-control" readonly>
          </div>
          <div class="col-md-4">
            <label class="form-label">Total Tax (₹)</label>
            <input type="number" name="tax_amount" id="total_tax" class="form-control" readonly>
          </div>
          <div class="col-md-4">
            <label class="form-label"><strong>Grand Total (₹)</strong></label>
            <input type="number" id="grand_total" name="total_amount" class="form-control" readonly>
          </div>
        </div>




        {{-- <div class="col-md-6">
          <label for="amount" class="form-label">Amount*</label>
          <input type="number" step="0.01" min="0.01" class="form-control" id="amount" name="amount"
            value="{{ old('amount', $invoice->amount) }}" required>
        </div>
        <div class="col-md-6">
          <label for="tax_amount" class="form-label">GST Amount*</label>
          <input type="number" step="0.01" min="0.01" class="form-control" id="tax_amount" name="tax_amount"
            value="{{ old('amount', $invoice->tax_amount) }}" required>
        </div>
        <div class="col-md-6">
          <label for="total_amount" class="form-label">Total Amount*</label>
          <input type="number" step="0.01" min="0.01" class="form-control" id="total_amount" name="total_amount"
            value="{{ old('amount', $invoice->total_amount) }}" required>
        </div> --}}
      </div>

      <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control" id="description" name="description"
          rows="3">{{ old('description', $invoice->description) }}</textarea>
      </div>

      <div class="d-flex justify-content-between">
        <a href="{{ route('pms.finance.invoices.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>
@endsection