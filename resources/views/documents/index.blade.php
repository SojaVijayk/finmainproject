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

@section('page-style')
<style>
  .stat-card {
    padding: 5px;
    text-align: center;
  }

  .stat-title {
    font-size: 0.8rem;
    margin-bottom: 5px;
    color: #6c757d;
  }

  .stat-value {
    font-weight: bold;
    margin: 0;
  }

  #statistics-section .stat-value {
    min-height: 36px;
    /* Prevent layout shift when loading */
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .loading-stat {
    color: transparent;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
    border-radius: 4px;
    min-width: 40px;
    display: inline-block;
  }

  @keyframes loading {
    0% {
      background-position: 200% 0;
    }

    100% {
      background-position: -200% 0;
    }
  }
</style>
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>

<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js')}}"></script>
<script src="{{asset('assets/vendor/libs/block-ui/block-ui.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@6.1.15/index.global.min.js'></script>

@endsection

@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Load statistics initially
    loadStatistics();

    // Refresh every 30 seconds
    setInterval(loadStatistics, 30000);

    function loadStatistics() {
        fetch("{{ route('documents.user.statistics') }}")
            .then(response => response.json())
            .then(data => {
                // Update DS documents
                document.getElementById('total-ds').textContent = data.total_ds;
                document.getElementById('ds-created').textContent = data.ds_created;
                document.getElementById('ds-active').textContent = data.ds_active;
                document.getElementById('ds-cancelled').textContent = data.ds_cancelled;
                document.getElementById('ds-pending').textContent = data.ds_created; // Pending is same as created

                // Update General documents
                document.getElementById('total-general').textContent = data.total_general;
                document.getElementById('general-created').textContent = data.general_created;
                document.getElementById('general-active').textContent = data.general_active;
                document.getElementById('general-cancelled').textContent = data.general_cancelled;
                document.getElementById('general-pending').textContent = data.general_created; // Pending is same as created

                // Update totals
                document.getElementById('total-documents').textContent = data.total_ds + data.total_general;
                document.getElementById('total-created').textContent = data.total_created;
                document.getElementById('total-active').textContent = data.total_active;
                document.getElementById('total-cancelled').textContent = data.total_cancelled;
                document.getElementById('total-pending').textContent = data.total_created; // Pending is same as created

                // Update last updated time
                document.getElementById('last-updated').textContent = 'Last updated: ' + formatDateTime(data.last_updated);
            })
            .catch(error => {
                console.error('Error loading statistics:', error);
            });
    }

    function formatDateTime(datetime) {
        const options = {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        };
        return new Date(datetime).toLocaleString(undefined, options);
    }
});
function showLoading() {
    const statValues = document.querySelectorAll('.stat-value');
    statValues.forEach(el => {
        el.innerHTML = '<span class="loading-stat">00</span>';
    });
}

function loadStatistics() {
    showLoading();
    // Rest of the function remains the same...
}
</script>
@endsection

@section('content')
<div class="container">
  <div class="row mb-4">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">Letter/Document Search</div>
        <div class="card-body">
          {{-- <div class="alert alert-warning m-2" role="alert">
            🚧 This is a <strong>demo version</strong> of the system. The live platform will be launched on <strong>01
              July 2025</strong>.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
            </button>
          </div> --}}
          <form method="GET" action="{{ route('documents.index') }}">
            <div class="row">
              <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search..."
                  value="{{ request('search') }}">
              </div>
              <div class="col-md-2">
                <select name="year" class="form-control">
                  <option value="">All Years</option>
                  @foreach($years as $y)
                  <option value="{{ $y }}" {{ request('year')==$y ? 'selected' : '' }}>{{ $y }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-3">
                <select name="type" class="form-control">
                  <option value="">All Types</option>
                  @foreach($documentTypes as $type)
                  <option value="{{ $type->id }}" {{ request('type')==$type->id ? 'selected' : '' }}>{{ $type->name }}
                  </option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-2">
                <select name="status" class="form-control">
                  <option value="">All Statuses</option>
                  <option value="created" {{ request('status')=='created' ? 'selected' : '' }}>Initiated</option>
                  <option value="active" {{ request('status')=='active' ? 'selected' : '' }}>Created</option>
                  <option value="cancelled" {{ request('status')=='cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
              </div>
              <div class="col-md-2">
                <button type="submit" class="btn btn-primary">Search</button>
                <a href="{{ route('documents.index') }}" class="btn btn-secondary">Reset</a>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span>Documents</span>
          <a href="{{ route('documents.create') }}" class="btn btn-primary">Generate New Number</a>
        </div>
        <div class="card-body">

          <!-- Statistics Section -->
          <div id="statistics-section" class="card-body bg-light">
            <div class="row text-center">
              <div class="col-md-12">
                <h5><i class="fas fa-chart-pie"></i> Document Statistics <small class="text-muted"
                    id="last-updated"></small></h5>
              </div>
            </div>
            <div class="row mt-3">
              <!-- DS Documents -->
              <div class="col-md-4 mb-3">
                <div class="card">
                  <div class="card-header bg-primary text-white">
                    <i class="fas fa-file-alt"></i> DS Documents
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <div class="col-4">
                        <div class="stat-card">
                          <h6 class="stat-title">Total</h6>
                          <h3 class="stat-value" id="total-ds">0</h3>
                        </div>
                      </div>
                      <div class="col-4">
                        <div class="stat-card">
                          <h6 class="stat-title">Initiated</h6>
                          <h3 class="stat-value text-warning" id="ds-created">0</h3>
                        </div>
                      </div>
                      <div class="col-4">
                        <div class="stat-card">
                          <h6 class="stat-title">Created</h6>
                          <h3 class="stat-value text-success" id="ds-active">0</h3>
                        </div>
                      </div>
                    </div>
                    <div class="row mt-2">
                      <div class="col-6">
                        <div class="stat-card">
                          <h6 class="stat-title">Cancelled</h6>
                          <h3 class="stat-value text-danger" id="ds-cancelled">0</h3>
                        </div>
                      </div>
                      <div class="col-6">
                        <div class="stat-card">
                          <h6 class="stat-title">Pending Upload</h6>
                          <h3 class="stat-value text-info" id="ds-pending">0</h3>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- General Documents -->
              <div class="col-md-4 mb-3">
                <div class="card">
                  <div class="card-header bg-secondary text-white">
                    <i class="fas fa-file"></i> General Documents
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <div class="col-4">
                        <div class="stat-card">
                          <h6 class="stat-title">Total</h6>
                          <h3 class="stat-value" id="total-general">0</h3>
                        </div>
                      </div>
                      <div class="col-4">
                        <div class="stat-card">
                          <h6 class="stat-title">Initiated</h6>
                          <h3 class="stat-value text-warning" id="general-created">0</h3>
                        </div>
                      </div>
                      <div class="col-4">
                        <div class="stat-card">
                          <h6 class="stat-title">Created</h6>
                          <h3 class="stat-value text-success" id="general-active">0</h3>
                        </div>
                      </div>
                    </div>
                    <div class="row mt-2">
                      <div class="col-6">
                        <div class="stat-card">
                          <h6 class="stat-title">Cancelled</h6>
                          <h3 class="stat-value text-danger" id="general-cancelled">0</h3>
                        </div>
                      </div>
                      <div class="col-6">
                        <div class="stat-card">
                          <h6 class="stat-title">Pending Upload</h6>
                          <h3 class="stat-value text-info" id="general-pending">0</h3>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Overall Summary -->
              <div class="col-md-4 mb-3">
                <div class="card">
                  <div class="card-header bg-info text-white">
                    <i class="fas fa-chart-bar"></i> Overall Summary
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <div class="col-4">
                        <div class="stat-card">
                          <h6 class="stat-title">Total</h6>
                          <h3 class="stat-value" id="total-documents">0</h3>
                        </div>
                      </div>
                      <div class="col-4">
                        <div class="stat-card">
                          <h6 class="stat-title">Initiated</h6>
                          <h3 class="stat-value text-warning" id="total-created">0</h3>
                        </div>
                      </div>
                      <div class="col-4">
                        <div class="stat-card">
                          <h6 class="stat-title">Created</h6>
                          <h3 class="stat-value text-success" id="total-active">0</h3>
                        </div>
                      </div>
                    </div>
                    <div class="row mt-2">
                      <div class="col-6">
                        <div class="stat-card">
                          <h6 class="stat-title">Cancelled</h6>
                          <h3 class="stat-value text-danger" id="total-cancelled">0</h3>
                        </div>
                      </div>
                      <div class="col-6">
                        <div class="stat-card">
                          <h6 class="stat-title">Pending Activation</h6>
                          <h3 class="stat-value text-info" id="total-pending">0</h3>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>



          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Document Number</th>
                  <th>Subject</th>
                  <th>Type</th>
                  <th>To Address</th>
                  <th>Created By</th>
                  <th>Status</th>
                  <th>Created At</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($documents as $document)
                <tr>
                  <td>{{ $document->document_number }}</td>
                  <td>{{ Str::limit($document->subject, 30) }}</td>
                  <td>{{ $document->documentType->name }}</td>
                  <td>{{ Str::limit($document->to_address_details, 30) }}</td>
                  <td>{{ $document->creator->name }}</td>
                  <td>
                    <span class="badge
                                            @if($document->status == 'created') bg-warning
                                            @elseif($document->status == 'active') bg-success
                                            @else bg-danger @endif">
                      @if($document->status == 'created') Initiated @elseif($document->status == 'active')
                      Created @else {{ ucfirst($document->status) }} @endif
                    </span>
                  </td>
                  <td>{{ $document->created_at->format('d-m-Y') }}</td>
                  <td>
                    <a href="{{ route('documents.show', $document) }}" class="btn btn-sm btn-info">View</a>
                    @if($document->status == 'created' && (auth()->user()->id === $document->user_id ||
                    auth()->user()->id === $document->authorized_person_id ||
                    auth()->user()->id === $document->code->user_id))
                    <a href="{{ route('documents.edit', $document) }}" class="btn btn-sm btn-warning">Edit</a>
                    @endif

                  </td>
                  <td>
                    @if ($document->status == 'active')
                    <a href="{{ route('despatch.create', ['document_id' => $document->id]) }}"
                      class="btn btn-sm btn-success">
                      Add Despatch
                    </a>
                    @endif
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <div class="mt-3">
            {{ $documents->links('pagination::bootstrap-5') }}
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection