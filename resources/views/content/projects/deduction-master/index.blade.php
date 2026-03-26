@extends('layouts/layoutMaster')

@section('title', 'Salary Deduction Management - Active Employees')

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">PMS /</span> Salary Deduction Management
</h4>

<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Active Frozen Employees</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('pms.employees.project-index', ['id' => $project_id]) }}" class="btn btn-label-secondary btn-sm">
                        <i class="ti ti-arrow-left me-1 ti-xs"></i> Back
                    </a>
                    <a href="{{ route('pms.pay-item-master.index', $project_id ?? '') }}" class="btn btn-label-secondary btn-sm" title="Pay Item Master">
                        <i class="ti ti-settings me-1 ti-xs"></i> Pay Item Master
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($frozenBatches->isEmpty())
                    <div class="alert alert-warning" role="alert">
                        No frozen salary bills available for deduction processing. Please freeze employees in Salary Management first.
                    </div>
                @else
                    <div class="table-responsive text-nowrap mt-3">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Batch / Salary ID</th>
                                    <th>Period</th>
                                    <th>Employment Type</th>
                                    <th class="text-center">Total Employees</th>
                                    <th class="text-end">Total Net (Base)</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($frozenBatches as $batch)
                                    <tr>
                                        <td>
                                            <span class="fw-semibold text-primary">
                                                <i class="ti ti-file-invoice me-1"></i> {{ $batch->salary_id }}
                                            </span>
                                        </td>
                                        <td>{{ $batch->paymonth }} {{ $batch->year }}</td>
                                        <td><span class="badge bg-label-info">{{ $batch->employment_type }}</span></td>
                                        <td class="text-center">
                                            <span class="badge rounded-pill bg-label-secondary">{{ $batch->employee_count }}</span>
                                        </td>
                                        <td class="text-end fw-semibold">₹{{ number_format($batch->total_net, 2) }}</td>
                                        <td class="text-center">
                                            <form action="{{ route('pms.deduction-master.select-employees', $project_id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="salary_id" value="{{ $batch->salary_id }}">
                                                <input type="hidden" name="month" value="{{ $batch->paymonth }}">
                                                <input type="hidden" name="year" value="{{ $batch->year }}">
                                                <input type="hidden" name="employment_type" value="{{ $batch->employment_type }}">
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    <i class="ti ti-edit me-1 ti-xs"></i> Process Deductions
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info py-2 mb-0 mt-3" role="alert">
                        <i class="ti ti-info-circle me-1 ti-xs"></i> Only frozen salary bills are listed. Select a bill to view and edit deductions for the employees within that batch.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
