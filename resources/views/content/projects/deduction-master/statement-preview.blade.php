@extends('layouts/layoutMaster')

@section('title', 'Statement Preview - Proceeded Bills')

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">PMS / Salary Deduction Management /</span> Statement Preview
</h4>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Statement Preview</h5>
            <small class="text-muted">Batch: {{ $salary_id }} | Period: {{ $month }} {{ $year }} | Type: {{ $employment_type }}</small>
        </div>
        <div>
            <a href="{{ route('pms.deduction-master.index', session('project_id', 1)) }}" class="btn btn-label-secondary me-2">
                <i class="ti ti-arrow-left me-1"></i> Back
            </a>
            <a href="{{ route('pms.deduction-master.statement-export', ['project_id' => session('project_id', 1), 'salary_id' => $salary_id, 'month' => $month, 'year' => $year, 'employment_type' => $employment_type, 'columns' => implode(',', $columns)]) }}" class="btn btn-success">
                <i class="ti ti-file-spreadsheet me-1"></i> Download Excel
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive text-nowrap mt-3">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        @foreach($columns as $col)
                            <th>{{ $columnLabels[$col] ?? ucwords(str_replace('_', ' ', $col)) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $totals = array_fill_keys($columns, 0); 
                    @endphp
                    @forelse($statementData as $index => $row)
                        <tr>
                            @foreach($columns as $col)
                                @if($col == 'slno')
                                    <td class="text-center">{{ $index + 1 }}</td>
                                @elseif(in_array($col, ['name', 'designation', 'date_of_joining', 'bank_name', 'account_no', 'ifsc_code', 'branch', 'pan_number', 'address', 'email', 'mobile']))
                                    <td>{{ $row[$col] ?? '-' }}</td>
                                @else
                                    <td class="text-end">₹{{ number_format((float)($row[$col] ?? 0), 2) }}</td>
                                    @php $totals[$col] += (float)($row[$col] ?? 0); @endphp
                                @endif
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($columns) }}" class="text-center">No records found for this statement.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($statementData) > 0)
                <tfoot class="table-light fw-bold">
                    <tr>
                        @foreach($columns as $col)
                            @if($col == 'slno' || $col == 'name')
                                <td>{{ $col == 'name' ? 'Total' : '' }}</td>
                            @elseif(in_array($col, ['designation', 'date_of_joining', 'bank_name', 'account_no', 'ifsc_code', 'branch', 'pan_number', 'address', 'email', 'mobile']))
                                <td></td>
                            @else
                                <td class="text-end">₹{{ number_format($totals[$col], 2) }}</td>
                            @endif
                        @endforeach
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
        <div class="mt-3">
            <p class="text-muted small"><i class="ti ti-info-circle me-1"></i> If the preview looks correct, click 'Download Excel' at the top to export this statement.</p>
        </div>
    </div>
</div>
@endsection
