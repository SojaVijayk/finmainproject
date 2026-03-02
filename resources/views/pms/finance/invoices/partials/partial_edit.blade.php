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

@endsection

@section('content')
<div class="container-fluid">
  <h4>Partial Conversion — Edit Invoices</h4>

  <div class="row mt-4">
    <div class="col-md-12">
      <div class="card border-secondary">
        <div class="card-header bg-light">
          <strong>Edit New Proforma Invoice</strong>
        </div>
        <div class="card-body">
          {{-- <a href="pms/finanace/invoices/{{$proformaInvoice->id}}/process">Edit Proforma Invoice -
            {{ $proformaInvoice->invoice_number }} </a> --}}
          <iframe src="{{ route('pms.finance.invoices.process', $proformaInvoice->id) }}"
            style="width:100%; height:100%; border:0;" title="Proforma Invoice Edit">
          </iframe>
          {{-- @include('pms.finance.invoices.partials.edit_form', ['invoice' => $proformaInvoice]) --}}
        </div>
      </div>
    </div>

    <div class="col-md-12">
      <div class="card border-success">
        <div class="card-header bg-light">
          <strong>Edit New Tax Invoice </strong>
        </div>
        <div class="card-body">
          {{-- @include('pms.invoices.partials.edit_form', ['invoice' => $]) --}}
          {{-- <a href="pms/finanace/invoices/{{$taxInvoice->id}}/process">Edit Tax Invoice - {{
            $taxInvoice->invoice_number
            }}</a> --}}
          <iframe src="{{ route('pms.finance.invoices.process', $taxInvoice->id) }}"
            style="width:100%; height:100%; border:0;" title="Tax Invoice Edit">
          </iframe>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection