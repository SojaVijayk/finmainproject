@extends('layouts/layoutMaster')

@section('title', 'Deduction Master - Active Employees')

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">PMS /</span> Deduction Master
</h4>

<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Active Frozen Employees</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('pms.pay-item-master.index', $project_id ?? '') }}" class="btn btn-label-secondary btn-sm" title="Pay Item Master">
                        <i class="ti ti-settings me-1 ti-xs"></i> Pay Item Master
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($frozenPayrolls->isEmpty())
                    <div class="alert alert-warning" role="alert">
                        No frozen employees available for deduction processing. Please freeze employees in Salary Management first.
                    </div>
                @else
                    <form action="{{ route('pms.deduction-master.select-employees', $project_id) }}" method="POST">
                        @csrf
                        <div class="table-responsive text-nowrap mt-3">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Month</th>
                                        <th>Year</th>
                                        <th>Employment Type</th>
                                        <th>Salary ID</th>
                                        <th>Net Salary (Base)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($frozenPayrolls as $record)
                                        <tr>
                                            <td class="fw-semibold text-primary">{{ $record->name }}</td>
                                            <td>{{ $record->paymonth }}</td>
                                            <td>{{ $record->year }}</td>
                                            <td><span class="badge bg-label-info">{{ $record->employment_type }}</span></td>
                                            <td><span class="badge bg-label-secondary">{{ $record->salary_id }}</span></td>
                                            @php
                                                $twd = (float)($record->total_working_days ?? 0);
                                                $dw = (float)($record->days_worked ?? 0);
                                                $gs = (float)($record->gross_salary ?? 0);
                                                $arr = (float)($record->other_allowance ?? 0);
                                                $epf_s = (float)($record->epf_employers_share ?? 0);
                                                $edli_s = (float)($record->edli_charges ?? 0);
                                                $prorated = ($twd > 0) ? ($gs / $twd) * $dw : $gs;
                                                $computedNet = $prorated + $arr + $epf_s + $edli_s;
                                            @endphp
                                            <td class="text-end fw-semibold">₹{{ number_format($computedNet, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Passing the first active combination implicitly so the next step controller continues to work unmodified -->
                        @php
                            $firstRecord = $frozenPayrolls->first();
                        @endphp
                        <input type="hidden" name="month" value="{{ $firstRecord->paymonth }}">
                        <input type="hidden" name="year" value="{{ $firstRecord->year }}">
                        <input type="hidden" name="employment_type" value="{{ $firstRecord->employment_type }}">
                        <input type="hidden" name="default_salary_id" value="{{ $firstRecord->salary_id }}">

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-edit me-1"></i> Proceed to Edit Deductions
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
