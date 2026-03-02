<div class="success-container">
  <h2> Congratulations, <br> {{ $user->name }}! </h2>
  <p>
    Your participation in the <strong>International Multiplier Training</strong>
    has been successfully verified.
  </p>

  <div class="certificate-buttons mt-4">
    <a href="{{ route('certificate.view', $user->id) }}" target="_blank" class="btn btn-sm btn-primary btn-view">👁️
      View Certificate</a>
    <a href="{{ route('certificate.download', $user->id) }}" class="btn btn-sm btn-success btn-download">⬇️ Download
      Certificate</a>
  </div>
</div>