@php
$types = \App\Models\DespatchType::all();
@endphp

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
          data-ack="{{ $type->requires_ack }}" data-mail="{{ $type->requires_mail_id }}">
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
      <label>Mail ID</label>
      <input type="email" name="mail_id" class="form-control" placeholder="Enter email address">
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

<script>
  function toggleDespatchFields() {
    const typeSelect = document.getElementById('despatchType');
    const selected = typeSelect.options[typeSelect.selectedIndex];
    document.getElementById('trackingField').style.display = selected.dataset.tracking === '1' ? 'block' : 'none';
    document.getElementById('ackField').style.display = selected.dataset.ack === '1' ? 'block' : 'none';
    document.getElementById('mailField').style.display = selected.dataset.mail === '1' ? 'block' : 'none';
}
</script>