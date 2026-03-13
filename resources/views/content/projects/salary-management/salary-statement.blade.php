<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Salary Statement</title>
    <style>
        @page {
            size: landscape;
            margin: 1cm;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #000;
            background: #fff;
            margin: 0;
            font-size: 12px;
        }
        .invoice-container {
            padding: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .company-name {
            font-weight: bold;
            font-size: 20px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .statement-title {
            font-weight: bold;
            font-size: 16px;
        }
        .table-custom {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .table-custom th, .table-custom td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }
        .table-custom th {
            text-align: center;
            font-weight: bold;
            background-color: #f2f2f2;
        }
        .text-end {
            text-align: right !important;
        }
        .text-center {
            text-align: center !important;
        }
        .summary-block {
            width: 350px;
            margin-left: auto;
            margin-top: 15px;
        }
        .summary-row {
            display: block;
            margin-bottom: 4px;
            font-weight: bold;
        }
        .summary-label {
            display: inline-block;
            width: 200px;
        }
        .summary-value {
            display: inline-block;
            width: 130px;
            text-align: right;
        }
        .double-underline {
            border-bottom: 3px double #000;
        }
        .no-print {
            display: none;
        }
    </style>
</head>
<body>
<div class="invoice-container">
    <div class="no-print" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
        <a href="{{ route('pms.salary-management.summary', ['project_id' => $project->id]) }}" onclick="window.location.href=this.href; return false;" style="background: #ea5455; color: white; text-decoration: none; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; font-family: sans-serif; display: inline-block;">
            ← Back
        </a>
        <a href="#" onclick="let url = new URL(window.location.href); url.searchParams.set('format', 'pdf'); window.location.href = url.toString(); return false;" style="background: #7367f0; color: white; text-decoration: none; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; font-family: sans-serif; display: inline-block;">
            Print
        </a>
    </div>

    @php
        $showInvoiceStyle = in_array('epf_employers_share', $columns) || in_array('edli_charges', $columns) || in_array('pf', $columns);
        
        // Calculate total employer contribution upfront
        $upfrontTotalEmployerContribution = 0;
        foreach($statementData as $row) {
            $upfrontTotalEmployerContribution += $row->employer_contribution ?? 0;
        }

        // Auto-hide Employer Contribution column if there is no data
        if ($upfrontTotalEmployerContribution == 0 && ($key = array_search('employer_contribution', $columns)) !== false) {
            unset($columns[$key]);
        }
    @endphp

    @if(!$showInvoiceStyle)
    <div class="header">
        <div class="company-name">{{ $project->name ?? 'KTIL' }}</div>
        <div class="statement-title">Salary for the Month of {{ $month }}, {{ $year }}</div>
    </div>
    @endif

    <table class="table-custom">
        <thead>
            @if($showInvoiceStyle)
            <tr>
                <th colspan="{{ count($columns) }}" style="font-size: 14px; padding: 10px;">
                    Attendance and Salary details of the persons engaged through CMD from {{ $startDateStr }} to {{ $endDateStr }}
                </th>
            </tr>
            <tr>
                <th colspan="{{ count($columns) }}" style="font-size: 14px; padding: 10px;">
                    {{ $project->name ?? 'K.C.M.M.F. Ltd., Head Office, Thiruvananthapuram' }}
                </th>
            </tr>
            @endif
            <tr>
                @if(in_array('slno', $columns))<th style="width: 40px;">Sl. No</th>@endif
                @if(in_array('name', $columns))<th>Names</th>@endif
                @if(in_array('designation', $columns))<th>Designation</th>@endif
                @if(in_array('doj', $columns))<th>Date of Joining</th>@endif
                @if(in_array('remuneration', $columns))<th>Remuneration</th>@endif
                @if(in_array('arrear', $columns))<th>Arrears</th>@endif
                @if(in_array('epf', $columns))<th>EPF</th>@endif
                @if(in_array('employer_contribution', $columns))<th>Employer Contribution</th>@endif
                @if(in_array('epf_employers_share', $columns))<th>EPF Employers share</th>@endif
                @if(in_array('edli_charges', $columns))<th>EDLI & EPF Admin</th>@endif
                @if(in_array('pf', $columns))<th>PF</th>@endif
                @if(in_array('deduction', $columns))<th>Deductions</th>@endif
                @if(in_array('payable', $columns))<th>Payable Remuneration</th>@endif
            </tr>
        </thead>
        <tbody>
            @php
                $totalArrears = 0;
                $totalRemuneration = 0;
                $totalDeductions = 0;
                $totalPayable = 0;
                $totalEmployerContribution = 0;
                $totalEpf = 0;
                $totalEpfEmployersShare = 0;
                $totalEdliCharges = 0;
                $totalPf = 0;
            @endphp
            @foreach($statementData as $index => $row)
            <tr>
                @if(in_array('slno', $columns))<td class="text-center">{{ $index + 1 }}</td>@endif
                @if(in_array('name', $columns))<td>{{ $row->name }}</td>@endif
                @if(in_array('designation', $columns))<td>{{ $row->designation }}</td>@endif
                @if(in_array('doj', $columns))<td class="text-center">{{ $row->doj }}</td>@endif
                @if(in_array('remuneration', $columns))<td class="text-end">{{ number_format((float)$row->remuneration, 2) }}</td>@endif
                @if(in_array('arrear', $columns))<td class="text-end">{{ $row->arrears > 0 ? number_format((float)$row->arrears, 2) : '-' }}</td>@endif
                @if(in_array('epf', $columns))<td class="text-end">{{ $row->epf > 0 ? number_format((float)$row->epf, 2) : '-' }}</td>@endif
                @if(in_array('employer_contribution', $columns))<td class="text-end">{{ $row->employer_contribution > 0 ? number_format((float)$row->employer_contribution, 2) : '-' }}</td>@endif
                @if(in_array('epf_employers_share', $columns))<td class="text-end">{{ $row->epf_employers_share > 0 ? number_format((float)$row->epf_employers_share, 2) : '-' }}</td>@endif
                @if(in_array('edli_charges', $columns))<td class="text-end">{{ $row->edli_charges > 0 ? number_format((float)$row->edli_charges, 2) : '-' }}</td>@endif
                @if(in_array('pf', $columns))<td class="text-end">{{ $row->pf > 0 ? number_format((float)$row->pf, 2) : '-' }}</td>@endif
                @if(in_array('deduction', $columns))<td class="text-end">{{ $row->deductions > 0 ? number_format((float)$row->deductions, 2) : '-' }}</td>@endif
                @if(in_array('payable', $columns))<td class="text-end">{{ number_format((float)$row->payable, 2) }}</td>@endif
            </tr>
            @php
                $totalArrears += $row->arrears;
                $totalRemuneration += $row->remuneration;
                $totalEpf += $row->epf ?? 0;
                $totalDeductions += $row->deductions;
                $totalPayable += $row->payable;
                $totalEmployerContribution += $row->employer_contribution ?? 0;
                $totalEpfEmployersShare += $row->epf_employers_share ?? 0;
                $totalEdliCharges += $row->edli_charges ?? 0;
                $totalPf += $row->pf ?? 0;
            @endphp
            @endforeach
            <tr style="font-weight: bold; background-color: #f2f2f2;">
                @php
                    $prefixCols = 0;
                    if(in_array('slno', $columns)) $prefixCols++;
                    if(in_array('name', $columns)) $prefixCols++;
                    if(in_array('designation', $columns)) $prefixCols++;
                    if(in_array('doj', $columns)) $prefixCols++;
                @endphp
                @if($prefixCols > 0)
                    <td colspan="{{ $prefixCols }}" class="text-center">Sub Total</td>
                @endif
                @if(in_array('remuneration', $columns))<td class="text-end">{{ number_format((float)$totalRemuneration, 2) }}</td>@endif
                @if(in_array('arrear', $columns))<td class="text-end">{{ $totalArrears > 0 ? number_format((float)$totalArrears, 2) : '-' }}</td>@endif
                @if(in_array('epf', $columns))<td class="text-end">{{ $totalEpf > 0 ? number_format((float)$totalEpf, 2) : '-' }}</td>@endif
                @if(in_array('employer_contribution', $columns))<td class="text-end">{{ $totalEmployerContribution > 0 ? number_format((float)$totalEmployerContribution, 2) : '-' }}</td>@endif
                @if(in_array('epf_employers_share', $columns))<td class="text-end">{{ $totalEpfEmployersShare > 0 ? number_format((float)$totalEpfEmployersShare, 2) : '-' }}</td>@endif
                @if(in_array('edli_charges', $columns))<td class="text-end">{{ $totalEdliCharges > 0 ? number_format((float)$totalEdliCharges, 2) : '-' }}</td>@endif
                @if(in_array('pf', $columns))<td class="text-end">{{ $totalPf > 0 ? number_format((float)$totalPf, 2) : '-' }}</td>@endif
                @if(in_array('deduction', $columns))<td class="text-end">{{ $totalDeductions > 0 ? number_format((float)$totalDeductions, 2) : '-' }}</td>@endif
                @if(in_array('payable', $columns))<td class="text-end">{{ number_format((float)$totalPayable, 2) }}</td>@endif
            </tr>
            
            @if($showInvoiceStyle)
            @php
                // MATH BASED ON INVOICE IMAGE
                $totalEligibleSalary = $totalRemuneration + $totalArrears;
                $serviceChargePercent = request('admin_charge', 7.5);
                $serviceCharge = round($totalEligibleSalary * ($serviceChargePercent / 100));
                $invoiceSubTotal = $totalEligibleSalary + $totalEpfEmployersShare + $totalEdliCharges + $serviceCharge;
                $cgst = round($invoiceSubTotal * 0.09, 2);
                $sgst = round($invoiceSubTotal * 0.09, 2);
                $totalGst = $cgst + $sgst;
                $finalPayable = $invoiceSubTotal + $totalGst;
                $colspanLabels = count($columns) - 1;
            @endphp
            <tr style="font-weight: bold;">
                <td colspan="{{ $colspanLabels }}" class="text-center">Total</td>
                <td class="text-end">{{ number_format((float)$totalEligibleSalary, 2) }}</td>
            </tr>
            <tr style="font-weight: bold;">
                <td colspan="{{ $colspanLabels }}" class="text-center">Add: EPF Employer's Share @ 12%</td>
                <td class="text-end">{{ number_format((float)$totalEpfEmployersShare, 2) }}</td>
            </tr>
            <tr style="font-weight: bold;">
                <td colspan="{{ $colspanLabels }}" class="text-center">EDLI AND EPF contribution</td>
                <td class="text-end">{{ number_format((float)$totalEdliCharges, 2) }}</td>
            </tr>
            <tr style="font-weight: bold;">
                <td colspan="{{ $colspanLabels }}" class="text-center">Add: Service Charges {{ $serviceChargePercent }}% of total eligible salary</td>
                <td class="text-end">{{ number_format((float)$serviceCharge, 2) }}</td>
            </tr>
            <tr style="font-weight: bold; background-color: #f2f2f2;">
                <td colspan="{{ $colspanLabels }}" class="text-center">Total</td>
                <td class="text-end">{{ number_format((float)$invoiceSubTotal, 2) }}</td>
            </tr>
            <tr style="font-weight: bold;">
                <td colspan="{{ $colspanLabels }}" class="text-center">Add: CGST @ 9%</td>
                <td class="text-end">{{ number_format((float)$cgst, 2) }}</td>
            </tr>
            <tr style="font-weight: bold;">
                <td colspan="{{ $colspanLabels }}" class="text-center">Add: SGST @ 9%</td>
                <td class="text-end">{{ number_format((float)$sgst, 2) }}</td>
            </tr>
            <tr style="font-weight: bold;">
                <td colspan="{{ $colspanLabels }}" class="text-center">Total GST</td>
                <td class="text-end">{{ number_format((float)$totalGst, 2) }}</td>
            </tr>
            <tr style="font-weight: bold; background-color: #f2f2f2;">
                <td colspan="{{ $colspanLabels }}" class="text-center">Total payable</td>
                <td class="text-end">{{ number_format((float)$finalPayable, 2) }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    @if(!$showInvoiceStyle)
    <div class="summary-block">
        @php
            $remunerationPayable = $totalPayable;
            $employerContributionValue = $totalEmployerContribution ?? 0;
            $subTotalBeforeServiceCharge = $remunerationPayable + $employerContributionValue;
            $adminChargePercent = request('admin_charge', 7.5);
            $serviceChargeValue = round($subTotalBeforeServiceCharge * ($adminChargePercent / 100));
            $subTotalValue = $subTotalBeforeServiceCharge + $serviceChargeValue;
            $gstValue = round($subTotalValue * 0.18);
            $invoiceTotal = $subTotalValue + $gstValue;
        @endphp

        <div class="summary-row">
            <span class="summary-label">Remuneration Payable</span>
            <span class="summary-value">{{ number_format((float)$remunerationPayable, 2) }}</span>
        </div>
        
        @if($employerContributionValue > 0)
        <div class="summary-row">
            <span class="summary-label">Employer Contribution</span>
            <span class="summary-value">{{ number_format((float)$employerContributionValue, 2) }}</span>
        </div>
        @endif
        
        <div class="summary-row">
            <span class="summary-label">Administrative Charge ({{ $adminChargePercent }}%)</span>
            <span class="summary-value">{{ number_format((float)$serviceChargeValue, 2) }}</span>
        </div>
        <div class="summary-row" style="border-top: 1px solid #000; padding-top: 5px;">
            <span class="summary-label">Total</span>
            <span class="summary-value">{{ number_format((float)$subTotalValue, 2) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">GST</span>
            <span class="summary-value" style="border-bottom: 1px solid #000;">{{ number_format((float)$gstValue, 2) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Invoice Total</span>
            <span class="summary-value double-underline">{{ number_format((float)$invoiceTotal, 2) }}</span>
        </div>
    </div>
    @endif

    @if(!empty($note))
    <div style="margin-top: 20px; font-size: 11px; white-space: pre-wrap;">
        <strong style="display: block; margin-bottom: 5px; font-size: 13px;">Note:</strong>
        {{ $note }}
    </div>
    @endif
</div>
</body>
</html>
