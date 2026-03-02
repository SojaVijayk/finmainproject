@extends('layouts/layoutMaster')

@section('title', 'Resource Utilization Report')

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
@section('page-style')
<style>
  .progress {
    min-width: 100px;
  }

  .collapse-row td {
    padding: 0;
    border-top: none;
  }

  .collapse-row .card-body {
    padding: 1rem;
  }

  .sub-items {
    margin-left: 1rem;
    font-size: 0.9rem;
  }
</style>
@endsection
@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Team Utilization Summary Chart
    const teamCtx = document.getElementById('teamUtilizationChart').getContext('2d');
    const teamChart = new Chart(teamCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(collect($utilizationData)->pluck('user.name')->toArray()) !!},
            datasets: [{
                label: 'Utilization %',
                data: {!! json_encode(collect($utilizationData)->pluck('utilization_percentage')->toArray()) !!},
                backgroundColor: {!! json_encode(collect($utilizationData)->map(function($item) {
                    if ($item['utilization_percentage'] > 100) return 'rgba(220, 53, 69, 0.7)';
                    if ($item['utilization_percentage'] > 80) return 'rgba(25, 135, 84, 0.7)';
                    if ($item['utilization_percentage'] > 50) return 'rgba(13, 110, 253, 0.7)';
                    return 'rgba(255, 193, 7, 0.7)';
                })->toArray()) !!},
                borderColor: {!! json_encode(collect($utilizationData)->map(function($item) {
                    if ($item['utilization_percentage'] > 100) return 'rgba(220, 53, 69, 1)';
                    if ($item['utilization_percentage'] > 80) return 'rgba(25, 135, 84, 1)';
                    if ($item['utilization_percentage'] > 50) return 'rgba(13, 110, 253, 1)';
                    return 'rgba(255, 193, 7, 1)';
                })->toArray()) !!},
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: Math.max(100, Math.ceil(Math.max(...{!! json_encode(collect($utilizationData)->pluck('utilization_percentage')->toArray()) !!}) / 10) * 10),
                    title: {
                        display: true,
                        text: 'Utilization Percentage'
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y.toFixed(1) + '%';
                        }
                    }
                },
                legend: {
                    display: false
                }
            }
        }
    });

    // Individual user charts
    @foreach($utilizationData as $data)
    @if(count($data['projects']) > 0)
    const ctx{{ $data['user']->id }} = document.getElementById('chart-{{ $data['user']->id }}').getContext('2d');
    const chart{{ $data['user']->id }} = new Chart(ctx{{ $data['user']->id }}, {
        type: 'doughnut',
        data: {
            {{--  labels: {!! json_encode(collect($data['projects'])->map(function($project) {
                return $project['project'] ? $project['project']->title : 'Non-Project';
            })->toArray()) !!},  --}}
            labels: {!! json_encode(collect($data['projects'])->map(function($project) {
    return $project['project']->title
        ?? ($project['category']->name ?? 'Non-Project');
})->toArray()) !!},
            datasets: [{
                data: {!! json_encode(collect($data['projects'])->pluck('hours')->toArray()) !!},
                backgroundColor: [
                    '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
                    '#ec4899', '#14b8a6', '#f97316', '#64748b', '#84cc16'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: ${value.toFixed(1)} hours (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
    @endif
    @endforeach
});
</script>
@endsection
@section('header', 'Resource Utilization Report')

@section('content')
<div class="card">
  <div class="card-header">
    <div class="d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Resource Utilization Report</h5>
      <div>
        <a href="{{ route('pms.reports.export', ['type' => 'resource-utilization']) }}?date_range={{ $dateRange }}"
          class="btn btn-sm btn-success me-2">
          <i class="fas fa-file-export"></i> Export
        </a>
      </div>
    </div>
  </div>

  <div class="card-body">
    <form method="GET" class="mb-4">
      <div class="row">
        <div class="col-md-8">
          <label for="date_range" class="form-label">Date Range</label>
          <select name="date_range" id="date_range" class="form-select">
            @foreach($dateRanges as $key => $value)
            <option value="{{ $key }}" {{ $dateRange==$key ? 'selected' : '' }}>{{ $value }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-4 d-flex align-items-end">
          <button type="submit" class="btn btn-primary">Apply Filters</button>
        </div>
      </div>
    </form>

    <div class="alert alert-info mb-4">
      <i class="fas fa-info-circle"></i>
      Reporting period: {{ $workingDays }} working days ({{ $workingDays * 8 }} available hours per resource)
    </div>

    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Resource</th>
            <th>Total Hours</th>
            <th>Utilization %</th>
            <th>Projects</th>
            <th>Details</th>
          </tr>
        </thead>
        <tbody>
          @foreach($utilizationData as $data)
          <tr>
            <td>
              <strong>{{ $data['user']->name }}</strong><br>
              <small class="text-muted">{{ $data['user']->email }}</small>
            </td>
            <td>{{ number_format($data['total_hours'], 1) }}</td>
            <td>
              <div class="progress" style="height: 20px;">
                <div class="progress-bar
                  @if($data['utilization_percentage'] > 100) bg-danger
                  @elseif($data['utilization_percentage'] > 80) bg-success
                  @elseif($data['utilization_percentage'] > 50) bg-primary
                  @else bg-warning text-dark
                  @endif" style="width: {{ min($data['utilization_percentage'], 100) }}%">
                  {{ number_format($data['utilization_percentage'], 1) }}%
                </div>
              </div>
            </td>
            <td>
              @if(count($data['projects']) > 0)
              <ul class="list-unstyled mb-0">
                @foreach($data['projects'] as $project)
                <li>
                  <small>
                    <strong>
                      @if($project['project'])
                      {{ $project['project']->title }}
                      @elseif($project['category'])
                      {{ $project['category']->name }}
                      @else
                      Non-Project
                      @endif
                    </strong>:
                    {{ number_format($project['hours'], 1) }} hrs
                  </small>

                  {{-- Show "Others" sub-items --}}
                  {{-- @if(isset($project['category']) && strtolower($project['category']->name) === 'others' &&
                  !empty($project['items']))
                  <ul class="sub-items">
                    @foreach($project['items'] as $item)
                    <li>{{ $item->item_name }} – {{ number_format($item->hours, 1) }} hrs</li>
                    @endforeach
                  </ul>
                  @endif --}}
                </li>
                @endforeach
              </ul>
              @else
              <span class="text-muted">No project time</span>
              @endif
            </td>
            <td>
              <button class="btn btn-sm btn-info" data-bs-toggle="collapse"
                data-bs-target="#details-{{ $data['user']->id }}">
                <i class="fas fa-chart-pie"></i> View Breakdown
              </button>
            </td>
          </tr>

          <tr class="collapse" id="details-{{ $data['user']->id }}">
            <td colspan="5">
              <div class="row">
                <div class="col-md-6">
                  <h6>Time Distribution by Project</h6>
                  <canvas id="chart-{{ $data['user']->id }}" height="200"></canvas>
                </div>
                <div class="col-md-6">
                  <h6>Time Allocation</h6>
                  <div class="table-responsive">
                    <table class="table table-sm">
                      <thead>
                        <tr>
                          <th>Project</th>
                          <th>Hours</th>
                          <th>%</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($data['projects'] as $project)
                        <tr>
                          <td>
                            @if($project['project'])
                            {{ $project['project']->title }}
                            @elseif($project['category'])
                            {{ $project['category']->name }}
                            @else
                            Non-Project
                            @endif
                          </td>
                          <td>{{ number_format($project['hours'], 1) }}</td>
                          <td>
                            @if($data['total_hours'] > 0)
                            {{ number_format(($project['hours'] / $data['total_hours']) * 100, 1) }}%
                            @else
                            0%
                            @endif
                          </td>
                        </tr>

                        {{-- Include sub-items for "Others" basic view --}}
                        {{-- @if(isset($project['category']) && strtolower($project['category']->name) === 'others' &&
                        !empty($project['items']))
                        @foreach($project['items'] as $item)
                        <tr class="table-light">
                          <td class="ps-4">↳ {{ $item->item_name }}</td>
                          <td>{{ number_format($item->hours, 1) }}</td>
                          <td>-</td>
                        </tr>
                        @endforeach
                        @endif --}}
                        @if(isset($project['category']) && strtolower($project['category']->name) === 'others' &&
                        !empty($project['items']))

                        @php
                        // Group items by name and sum hours
                        $groupedItems = collect($project['items'])
                        ->groupBy(fn($i) => trim(strtolower($i->item_name)))
                        ->map(function($group) {
                        return [
                        'item_name' => $group->first()->item_name,
                        'hours' => $group->sum('hours')
                        ];
                        })
                        ->values();
                        @endphp

                        @foreach($groupedItems as $item)
                        <tr class="table-light">
                          <td class="ps-4">↳ {{ $item['item_name'] }}</td>
                          <td>{{ number_format($item['hours'], 1) }}</td>
                          <td>-</td>
                        </tr>
                        @endforeach
                        @endif
                        @endforeach

                        <tr class="table-light">
                          <td><strong>Total</strong></td>
                          <td><strong>{{ number_format($data['total_hours'], 1) }}</strong></td>
                          <td><strong>100%</strong></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="card mt-4">
  <div class="card-header">
    <h5 class="card-title">Team Utilization Summary</h5>
  </div>
  <div class="card-body">
    <canvas id="teamUtilizationChart" height="100"></canvas>
  </div>
</div>
@endsection