@php
    $remuneration = $payroll->gross_salary;
    $arrears = $payroll->other_allowance;
    $totalEarnings = $payroll->gross_salary + $payroll->other_allowance + $payroll->festival_allowance + $payroll->bonus;
    
    // Summing deductions as per the image categories
    $tds = $payroll->tds + $payroll->tds_192_b + $payroll->tds_194_j;
    $profTax = $payroll->professional_tax;
    $lop = 0; // If LOP amount is available separately, use it. Usually it's already deducted from gross.
    $pf = $payroll->pf;
    $esi = $payroll->esi_employer;
    $lic = $payroll->lic_others;
    $others = $payroll->others;
    
    $totalDeductions = $tds + $profTax + $lop + $pf + $esi + $lic + $others;
    $netSalary = $payroll->net_salary;
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Slip - {{ $payroll->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 20px;
            background-color: #fff;
        }
        .slip-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #eee;
            padding: 20px;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #eee;
            margin-bottom: 20px;
        }
        .logo {
            width: 80px;
        }
        .header-text {
            text-align: center;
        }
        .header-text h1 {
            margin: 0;
            font-size: 20px;
            color: #004a99;
            text-transform: uppercase;
        }
        .header-text p {
            margin: 3px 0;
            font-size: 12px;
            color: #666;
        }
        .header-text .subtitle {
            color: #e31e24;
            font-weight: bold;
        }
        .details-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .details-table td {
            padding: 5px;
            font-size: 13px;
            border: none;
        }
        .label {
            font-weight: bold;
            width: 160px;
        }
        .month-banner {
            background-color: #e9ecef;
            text-align: center;
            padding: 8px;
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 15px;
        }
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .main-table th, .main-table td {
            border: 1px solid #ccc;
            padding: 10px;
            font-size: 13px;
        }
        .main-table th {
            background-color: #f8f9fa;
            text-decoration: underline;
        }
        .text-right {
            text-align: right;
        }
        .net-salary-block {
            text-align: right;
            margin-top: 15px;
        }
        .net-salary-box {
            display: inline-block;
            border-top: 2px solid #333;
            border-bottom: 4px double #333;
            padding: 8px 15px;
            font-weight: bold;
            font-size: 18px;
        }
        .in-words {
            font-weight: bold;
            margin-top: 15px;
            text-align: center;
            font-size: 14px;
        }
        .footer-info {
            margin-top: 30px;
            font-size: 12px;
            color: #555;
        }
        .print-btn, .back-btn {
            position: fixed;
            bottom: 20px;
            background: #004a99;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            z-index: 1000;
        }
        .print-btn {
            right: 20px;
        }
        .back-btn {
            right: 160px;
            background: #6c757d;
        }
        @media print {
            .print-btn, .back-btn { display: none; }
            body { padding: 0; }
            .slip-container { border: none; }
        }
    </style>
</head>
<body>

<div class="slip-container">
    <table class="header-table">
        <tr>
            <td style="width: 100px;">
                <img src="{{ public_path('assets/img/branding/CMD-logo.png') }}" alt="Logo" class="logo">
            </td>
            <td class="header-text">
                <h1>Centre for Management Development</h1>
                <p class="subtitle">(An Autonomous Institution under the Government of Kerala)</p>
                <p>CV Raman Pillai Road, Thycaud P.O, Thiruvananthapuram 695014</p>
            </td>
        </tr>
    </table>

    <table class="details-table">
        <tr>
            <td class="label">Name</td>
            <td>{{ $payroll->name }}</td>
        </tr>
        <tr>
            <td class="label">Designation</td>
            <td>{{ $payroll->designation_name ?? $payroll->role }}</td>
        </tr>
        <tr>
            <td class="label">Name of Project</td>
            <td>{{ $payroll->project_name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">PAN</td>
            <td>{{ $payroll->pan_number ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Bank Account No.</td>
            <td>{{ $payroll->account_no ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Bank Name</td>
            <td>{{ $payroll->bank_name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Bank IFSC</td>
            <td>{{ $payroll->ifsc_code ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Pay Scale</td>
            <td>{{ $payroll->consolidated_pay ? 'Rs. ' . number_format($payroll->consolidated_pay, 2) . '/- p.m' : 'Consolidated Pay Rs.' . number_format($payroll->gross_salary, 2) . '/-p.m' }}</td>
        </tr>
    </table>

    <div class="month-banner">
        {{ $payroll->paymonth }}, {{ $payroll->year }}
    </div>

    <table class="main-table">
        <tr>
            <th colspan="2" style="text-align: center;">Earnings</th>
            <th colspan="2" style="text-align: center;">Deductions</th>
        </tr>
        <tr>
            <td style="width: 30%;">Remuneration</td>
            <td style="width: 20%;" class="text-right">{{ number_format($remuneration, 2) }}</td>
            <td style="width: 30%;">Tax Deducted at Source</td>
            <td style="width: 20%;" class="text-right">{{ $tds > 0 ? number_format($tds, 2) : '-' }}</td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td>Professional Tax</td>
            <td class="text-right">{{ $profTax > 0 ? number_format($profTax, 2) : '-' }}</td>
        </tr>
        <tr>
            <td>Arrears</td>
            <td class="text-right">{{ $arrears > 0 ? number_format($arrears, 2) : '-' }}</td>
            <td>Loss of pay</td>
            <td class="text-right">{{ $lop > 0 ? number_format($lop, 2) : '-' }}</td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td>PF Employee</td>
            <td class="text-right">{{ $pf > 0 ? number_format($pf, 2) : '-' }}</td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td>ESI Employee</td>
            <td class="text-right">{{ $esi > 0 ? number_format($esi, 2) : '-' }}</td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td>LIC Premium</td>
            <td class="text-right">{{ $lic > 0 ? number_format($lic, 2) : '-' }}</td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td>Others</td>
            <td class="text-right">{{ $others > 0 ? number_format($others, 2) : '-' }}</td>
        </tr>
        <tr style="background-color: #f8f9fa; font-weight: bold;">
            <td></td>
            <td class="text-right">{{ number_format($totalEarnings, 2) }}</td>
            <td></td>
            <td class="text-right">{{ $totalDeductions > 0 ? number_format($totalDeductions, 2) : '-' }}</td>
        </tr>
    </table>

    <div class="net-salary-block">
        <div class="net-salary-box">
            Net &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{ number_format($netSalary, 2) }}
        </div>
    </div>

    <div class="in-words">
        ({{ $netInWords }})
    </div>

    <div class="footer-info">
        <p>This is issued for the information of : {{ $payroll->name }}</p>
        <table style="width: 100%; border: none; margin-top: 20px;">
            <tr>
                <td style="border: none;">Document No: {{ $payroll->name }}{{ $payroll->paymonth }}{{ $payroll->year }}</td>
                <td style="border: none; text-align: right;">Date: {{ date('d-m-Y') }}</td>
            </tr>
        </table>
    </div>
</div>

@if(!isset($isExport))
<a href="{{ route('pms.deduction-master.select-employees', $payroll->project_id) }}" class="back-btn">← Back to List</a>
<button class="print-btn" onclick="window.print()">Print Salary Slip</button>
@endif

</body>
</html>
