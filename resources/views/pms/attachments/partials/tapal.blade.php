@if($attachments->isEmpty())
<p class="text-muted text-center my-3">No Tapal documents available.</p>
@else
<table class="table table-bordered table-sm align-middle">
  <thead>
    <tr>
      <th width="5%">#</th>
      <th>Tapal</th>
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
        <input type="checkbox" class="tapal-checkbox form-check-input" value="{{ $file->id }}"
          data-name="{{ $file->file_name }}" data-file="{{ $file->file_path }}">
      </td>
      <td>{{ $file->tapal->tapal_number }}</td>
      <td>{{ $file->file_name }}</td>
      <td>{{ $file->file_type }}</td>
      <td>{{ number_format($file->file_size / 1024, 2) }} KB</td>
      <td>{{ $file->created_at->format('d-M-Y') }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
@endif