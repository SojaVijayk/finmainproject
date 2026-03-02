@extends('layouts/layoutMaster')

@section('title', 'Edit Despatch')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
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

  document.addEventListener('DOMContentLoaded', toggleDespatchFields);
</script>
@endsection

@section('content')
<div class="card">
  <div class="card-header bg-primary text-white">
    {{-- <h5 class="mb-0">Edit Despatch for {{ $$despatch->document->document_number }}</h5> --}}
  </div>

  <div class="card-body">
    <form action="{{ route('despatch.update', $despatch->id) }}" method="POST" enctype="multipart/form-data">
      @csrf
      @method('PUT')

      <div class="row mb-3">
        <div class="col-md-4">
          <label>Date of Despatch *</label>
          <input type="date" name="despatch_date" class="form-control"
            value="{{ old('despatch_date', $despatch->despatch_date) }}" required>
        </div>

        <div class="col-md-4">
          <label>Type *</label>
          <select name="type_id" id="despatchType" class="form-control" onchange="toggleDespatchFields()" required>
            <option value="">-- Select Type --</option>
            @foreach($types as $type)
            <option value="{{ $type->id }}" data-tracking="{{ $type->requires_tracking }}"
              data-ack="{{ $type->requires_ack }}" data-mail="{{ $type->requires_mail_id }}"
              data-courier="{{ $type->requires_courier }}" {{ $despatch->type_id == $type->id ? 'selected' : '' }}>
              {{ $type->name }}
            </option>
            @endforeach
          </select>
        </div>

        <div class="col-md-4">
          <label>Send By</label>
          <input type="text" name="send_by" class="form-control" value="{{ old('send_by', $despatch->send_by) }}">
        </div>
      </div>

      <div class="row mb-3" id="mailField" style="display:none;">
        <div class="col-md-6">
          <label>Mail ID</label>
          <input type="email" name="mail_id" class="form-control" value="{{ old('mail_id', $despatch->mail_id) }}"
            placeholder="Enter email address">
        </div>
      </div>

      <div class="row mb-3" id="courierField" style="display:none;">
        <div class="col-md-6">
          <label>Courier Name</label>
          <input type="text" name="courier_name" class="form-control"
            value="{{ old('courier_name', $despatch->courier_name) }}" placeholder="Enter courier name">
        </div>
      </div>

      <div class="row mb-3" id="trackingField" style="display:none;">
        <div class="col-md-6">
          <label>Tracking Number</label>
          <input type="text" name="tracking_number" class="form-control"
            value="{{ old('tracking_number', $despatch->tracking_number) }}" placeholder="Enter tracking number">
        </div>
      </div>

      <div class="row mb-3" id="ackField" style="display:none;">
        <div class="col-md-6">
          <label>Upload Acknowledgement (optional)</label>
          <input type="file" name="acknowledgement_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
          @if($despatch->acknowledgement_file)
          <small class="text-success d-block mt-1">
            Current: <a href="{{ Storage::url($despatch->acknowledgement_file) }}" target="_blank">View File</a>
          </small>
          @endif
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <label>Upload Despatch Receipt (optional)</label>
          <input type="file" name="despatch_receipt" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
          @if($despatch->despatch_receipt)
          <small class="text-success d-block mt-1">
            Current: <a href="{{ Storage::url($despatch->despatch_receipt) }}" target="_blank">View File</a>
          </small>
          @endif
        </div>
      </div>

      <div class="mt-4">
        <button type="submit" class="btn btn-primary">Update Despatch</button>
        <a href="{{ route('document.show', $document->id) }}" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
@endsection