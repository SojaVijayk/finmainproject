@extends('layouts/layoutMaster')

@section('title', 'Invoice Management')
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
  document.getElementById('conversion_type').addEventListener('change', function () {
        const type = this.value;
        document.getElementById('cancel_remark').classList.add('d-none');
        document.getElementById('tax_invoice_details').classList.add('d-none');

        if (type === 'cancel') {
            document.getElementById('cancel_remark').classList.remove('d-none');
        } else if (['full', 'partial', 'partial_no_proforma'].includes(type)){
            document.getElementById('tax_invoice_details').classList.remove('d-none');
        }
    });
</script>
@endsection

@section('content')
<div class="container">
  <h4>Convert Proforma Invoice #{{ $invoice->invoice_number }}</h4>
  @if ($errors->any())
  <div class="alert alert-danger">
    <ul>
      @foreach ($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
  @endif
  <form action="{{ route('pms.finance.invoices.convert', $invoice->id) }}" method="POST">
    @csrf

    <div class="form-group mt-3">
      <label for="conversion_type">Conversion Type</label>
      <select class="form-control" name="conversion_type" id="conversion_type" required>
        <option value="">-- Select Type --</option>
        <option value="cancel">Cancel Proforma</option>
        <option value="full">Fully Convert to Tax Invoice</option>
        <option value="partial">Partially Convert (With Proforma)</option>
        <option value="partial_no_proforma">Partially Convert (No Proforma)</option>
      </select>
    </div>

    <div id="cancel_remark" class="form-group mt-3 d-none">
      <label for="remark">Cancellation Remark</label>
      <textarea name="remark" class="form-control" rows="3"></textarea>
    </div>

    <div id="tax_invoice_details" class="d-none mt-3">
      <h5>New Tax Invoice Details</h5>
      <div class="row">
        <div class="col-md-4">
          <label>Invoice Number</label>
          <input type="text" class="form-control" name="invoice_number">
        </div>
        <div class="col-md-4">
          <label>Invoice Date</label>
          <input type="date" class="form-control" name="invoice_date">
        </div>
        <div class="col-md-4">
          <label>Due Date</label>
          <input type="date" class="form-control" name="due_date">
        </div>
      </div>
    </div>

    <div class="mt-4">
      <button type="submit" class="btn btn-primary">Convert</button>
      <a href="{{ route('pms.finance.invoices.show', $invoice->id) }}" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>

@endsection