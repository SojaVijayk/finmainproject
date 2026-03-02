@if($attachments->isEmpty())
<p class="text-muted text-center my-3">No document attachments found.</p>
@else
<table class="table table-bordered table-sm align-middle">
  <thead>
    <tr>
      <th width="5%">#</th>
      <th>Document Number</th>
      <th>File Name</th>
      <th>Type</th>
      <th>Size</th>
      <th>Created Date</th>
    </tr>
  </thead>
  <tbody>
    @foreach($attachments as $index => $file)
    <tr>
      <td>
        <input type="checkbox" class="document-checkbox form-check-input" value="{{ $file->id }}"
          data-name="{{ $file->original_name }}" data-file="{{ $file->file_path }}">
      </td>
      <td>{{ $file->document->document_number }}</td>

      <td>{{ $file->original_name }}</td>
      <td>{{ $file->mime_type }}</td>
      <td>{{ number_format($file->file_size / 1024, 2) }} KB</td>
      <td>{{ $file->created_at->format('d-M-Y') }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
@endif