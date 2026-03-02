@extends('layouts/layoutMaster')

@section('title', 'Document Number')
@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}" />

<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/spinkit/spinkit.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />

<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/spinkit/spinkit.css')}}" />



@endsection

@section('page-script')
<script>
  function toggleDespatchFields() {
    const typeSelect = document.getElementById('despatchType');
    const selected = typeSelect.options[typeSelect.selectedIndex];
    document.getElementById('trackingField').style.display = selected.dataset.tracking === '1' ? 'block' : 'none';
    document.getElementById('ackField').style.display = selected.dataset.ack === '1' ? 'block' : 'none';
    document.getElementById('mailField').style.display = selected.dataset.mail === '1' ? 'block' : 'none';
     document.getElementById('courierField').style.display = selected.dataset.courier === '1' ? 'block' : 'none';
}
</script>
@endsection

@section('content')
<div class="card">
  <div class="card-header">Add Despatch for {{ $document->document_number }}
    @if ($errors->any())
    <div class="alert alert-danger">
      <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
    @endif
  </div>
  <div class="card-body">
    <form action="{{ route('despatch.store', $document->id) }}" method="POST" enctype="multipart/form-data">
      @csrf

      <div class="row mb-3">
        <div class="col-md-4">
          <label>Date of Despatch *</label>
          <input type="date" name="despatch_date" class="form-control" required>
        </div>

        <div class="col-md-4">
          <label>Type *</label>
          <select name="type_id" class="form-control" id="despatchType" onchange="toggleDespatchFields()" required>
            <option value="">-- Select Type --</option>
            @foreach($types as $type)
            <option value="{{ $type->id }}" data-tracking="{{ $type->requires_tracking }}"
              data-ack="{{ $type->requires_ack }}" data-mail="{{ $type->requires_mail_id }}"
              data-courier="{{ $type->requires_courier_name }}">
              {{ $type->name }}
            </option>
            @endforeach
          </select>
        </div>

        <div class="col-md-4">
          <label>Send By</label>
          <input type="text" name="send_by" class="form-control">
        </div>
      </div>

      <div class="row mb-3" id="mailField" style="display:none;">
        <div class="col-md-6">
          <label>Recipient Mail ID</label>
          <input type="email" name="mail_id" class="form-control" placeholder="Enter email address">
        </div>
      </div>
      <div class="row mb-3" id="courierField" style="display:none;">
        <div class="col-md-6">
          <label>Courier Name</label>
          <input type="text" name="courier_name" class="form-control" placeholder="Enter courier name">
        </div>
      </div>

      <div class="row mb-3" id="trackingField" style="display:none;">
        <div class="col-md-6">
          <label>Tracking Number</label>
          <input type="text" name="tracking_number" class="form-control" placeholder="Enter tracking number">
        </div>
      </div>

      <div class="row mb-3" id="ackField" style="display:none;">
        <div class="col-md-6">
          <label>Upload Acknowledgement (optional)</label>
          <input type="file" name="acknowledgement_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <label>Upload Despatch Receipt (optional)</label>
          <input type="file" name="despatch_receipt" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
        </div>
      </div>

      <button type="submit" class="btn btn-primary">Save Despatch</button>
    </form>

    @if(session('success'))
    <div class="alert alert-success mt-3">
      {{ session('success') }}
    </div>
    @endif

    @if($document->despatches->count())
    <hr>
    <h5 class="text-primary mt-4">Despatch Records</h5>

    <div class="row g-3">
      @foreach($document->despatches->sortByDesc('id') as $d)
      <div class="col-12 col-md-6 col-lg-4">
        <div class="card border-primary shadow-sm h-100">
          <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <strong>{{ $d->type->name }}</strong>
            <span class="badge bg-primary">{{ \Carbon\Carbon::parse($d->despatch_date)->format('d-M-Y') }}</span>
          </div>
          <div class="card-body">
            <p class="mb-1"><strong>Send By:</strong> {{ $d->send_by ?? '-' }}</p>
            @if($d->mail_id)
            <p class="mb-1"><strong>Mail ID:</strong> {{ $d->mail_id }}</p>
            @endif
            @if($d->courier_name)
            <p class="mb-1"><strong>Courier:</strong> {{ $d->courier_name }}</p>
            @endif
            @if($d->tracking_number)
            <p class="mb-1"><strong>Tracking No:</strong> {{ $d->tracking_number }}</p>
            @endif

            <div class="mt-2 d-flex flex-wrap gap-2">
              @if($d->acknowledgement_file)
              <a href="{{ Storage::url($d->acknowledgement_file) }}" target="_blank" class="btn btn-sm btn-success">
                <i class="fas fa-file"></i> Ack
              </a>
              @endif
              @if($d->despatch_receipt)
              <a href="{{ Storage::url($d->despatch_receipt) }}" target="_blank" class="btn btn-sm btn-info">
                <i class="fas fa-file"></i> Receipt
              </a>
              @endif
            </div>
          </div>
          <div class="card-footer text-muted small">
            Added on {{ $d->created_at->format('d-M-Y h:i A') }}
          </div>
        </div>
      </div>
      @endforeach
    </div>
    @endif


  </div>
</div>


@endsection