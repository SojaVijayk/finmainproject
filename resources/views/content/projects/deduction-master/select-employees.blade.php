@extends('layouts/layoutMaster')

@section('title', 'Deduction Master - Frozen Employees')

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">PMS / Deduction Master /</span> Frozen Employees
</h4>

<div class="card mb-4">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title mb-0">Step 2: Frozen Employee List</h5>
            <small class="text-muted">
                Period: <strong>{{ $month }} {{ $year }}</strong> | 
                Type: <strong>{{ ucfirst($employmentType) }}</strong> | 
                Batch: <strong>{{ $salaryId }}</strong>
            </small>
        </div>
        <div>
             <a href="{{ route('pms.deduction-master.index', $project_id) }}" class="btn btn-label-secondary">
                <i class="ti ti-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>
    
    <div class="card-body mt-4">
        @if($frozenPayrolls->isEmpty())
            <div class="alert alert-warning" role="alert">
                <h6 class="alert-heading fw-bold mb-1"><i class="ti ti-alert-triangle me-1"></i> No Frozen Records Found!</h6>
                <p class="mb-0">There are no salary records that have been "Frozen" for this specific Month, Year, and Employment Type combination. Please ensure the salary cycle has been completed and frozen in Salary Management first.</p>
            </div>
        @else
            <div class="table-responsive text-nowrap">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Sl. No</th>
                            <th>Employee Name</th>
                            <th>Designation</th>
                            <th>LOP Days</th>
                            <th>Gross Salary</th>
                            <th>Net Salary</th>
                            <th>Bank Name</th>
                            <th>Account No.</th>
                            <th>IFSC Code</th>
                            <th>Branch</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($frozenPayrolls as $index => $payroll)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-semibold">{{ $payroll->name }}</td>
                                <td>{{ $payroll->designation ?? 'N/A' }}</td>
                                <td class="text-center"><span class="badge bg-label-danger">{{ $payroll->lop_days ?? 0 }}</span></td>
                                <td class="text-end fw-semibold">₹{{ number_format((float)$payroll->gross_salary, 2) }}</td>
                                <td class="text-end fw-semibold text-success">₹{{ number_format((float)$payroll->net_salary, 2) }}</td>
                                <td>{{ $payroll->bank_name ?? 'N/A' }}</td>
                                <td>{{ $payroll->account_no ?? 'N/A' }}</td>
                                <td>{{ $payroll->ifsc_code ?? 'N/A' }}</td>
                                <td>{{ $payroll->branch ?? 'N/A' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-label-secondary"><i class="ti ti-lock me-1 ti-xs"></i> Frozen</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 text-end">
                <button class="btn btn-primary disabled" title="Further steps coming soon">Proceed to Next Step</button>
            </div>
        @endif
    </div>
</div>
@endsection
