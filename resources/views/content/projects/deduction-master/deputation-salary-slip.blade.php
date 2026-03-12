@php
    $basicPay = $payroll->basic_pay > 0 ? $payroll->basic_pay : ($payroll->service_basic ?? 0);
    $da = $payroll->da > 0 ? $payroll->da : ($payroll->service_da ?? 0);
    $hra = $payroll->hra > 0 ? $payroll->hra : ($payroll->service_hra ?? 0);
    $arrear = $payroll->other_allowance;
    
    // Identified Fixed and Other Allowances in Reference Image
    $fixedAllowance = 0; // If available in schema later, map here.
    $otherAllowance = 0; // If available in schema later, map here.
    
    $totalEarnings = $basicPay + $da + $hra + $arrear + $fixedAllowance + $otherAllowance;
    
    // Deductions from Refernce Image
    $tds = $payroll->tds + $payroll->tds_192_b + $payroll->tds_194_j;
    $profTax = $payroll->professional_tax;
    $lop = 0; // Hidden or already deducted
    $medisep = $dynamicDeductions['MEDISEP'] ?? 0;
    $gpf = $payroll->gpf;
    $lic = $payroll->lic + $payroll->lic_others;
    $sli1 = $dynamicDeductions['SLI 1'] ?? 0;
    $sli2 = $dynamicDeductions['SLI 2'] ?? 0;
    $sli3 = $dynamicDeductions['SLI 3'] ?? 0;
    $gis = $dynamicDeductions['GIS'] ?? 0;
    $gpais = $dynamicDeductions['GPAIS'] ?? 0;
    $others = $payroll->others;

    // Check for other common names if exact keys don't exist
    if($medisep == 0) $medisep = $dynamicDeductions['Medisep'] ?? 0;
    if($gis == 0) $gis = $dynamicDeductions['Gis'] ?? 0;

    $totalDeductions = $tds + $profTax + $lop + $medisep + $gpf + $lic + $sli1 + $sli2 + $sli3 + $gis + $gpais + $others;
    $netSalary = $totalEarnings - $totalDeductions;
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deputation Salary Slip - {{ $payroll->name }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; margin: 0; padding: 20px; background-color: #fff; line-height: 1.4; }
        .slip-container { width: 100%; max-width: 850px; margin: 0 auto; border: 1px solid #eee; padding: 30px; position: relative; }
        .header-table { width: 100%; border-bottom: 2px solid #eee; margin-bottom: 25px; }
        .logo { width: 85px; }
        .header-text { text-align: center; }
        .header-text h1 { margin: 0; font-size: 22px; color: #004a99; text-transform: uppercase; }
        .header-text p { margin: 4px 0; font-size: 13px; color: #666; }
        .header-text .subtitle { color: #e31e24; font-weight: bold; }
        
        .details-table { width: 100%; margin-bottom: 25px; }
        .details-table td { padding: 6px; font-size: 14px; border: none; vertical-align: top; }
        .label { font-weight: bold; width: 180px; }
        
        .month-banner { background-color: #e9ecef; text-align: center; padding: 10px; font-weight: bold; font-size: 16px; margin-bottom: 20px; border-radius: 2px; }
        
        .main-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .main-table th, .main-table td { border: 1px solid #ccc; padding: 12px 10px; font-size: 14px; }
        .main-table th { background-color: #f8f9fa; text-decoration: underline; text-align: center; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .net-salary-block { text-align: right; margin-top: 20px; }
        .net-salary-box { display: inline-block; border-top: 2px solid #333; border-bottom: 4px double #333; padding: 10px 20px; font-weight: bold; font-size: 19px; }
        
        .in-words { font-weight: bold; margin-top: 20px; text-align: center; font-size: 15px; font-style: italic; }
        .footer-info { margin-top: 40px; font-size: 13px; color: #444; }
        
        .print-btn, .back-btn { position: fixed; bottom: 20px; background: #004a99; color: #fff; border: none; padding: 12px 24px; border-radius: 6px; cursor: pointer; text-decoration: none; font-size: 14px; z-index: 1000; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .print-btn { right: 20px; }
        .back-btn { right: 170px; background: #6c757d; }
        
        @media print {
            .print-btn, .back-btn { display: none; }
            body { padding: 0; }
            .slip-container { border: none; padding: 10px; }
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
        <tr><td colspan="2" style="height: 10px;"></td></tr>
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
    </table>

    <div class="month-banner">
        {{ $payroll->paymonth }}, {{ $payroll->year }}
    </div>

    <table class="main-table">
        <thead>
            <tr>
                <th colspan="2">Earnings</th>
                <th colspan="2">Deductions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="width: 30%;">Basic Pay</td>
                <td style="width: 20%;" class="text-right">{{ number_format($basicPay, 2) }}</td>
                <td style="width: 30%;">Tax Deducted at Source</td>
                <td style="width: 20%;" class="text-right">{{ $tds > 0 ? number_format($tds, 2) : '-' }}</td>
            </tr>
            <tr>
                <td>DA</td>
                <td class="text-right">{{ number_format($da, 2) }}</td>
                <td>Professional Tax</td>
                <td class="text-right">{{ $profTax > 0 ? number_format($profTax, 2) : '-' }}</td>
            </tr>
            <tr>
                <td>HRA</td>
                <td class="text-right">{{ number_format($hra, 2) }}</td>
                <td>Loss of pay</td>
                <td class="text-right">{{ $lop > 0 ? number_format($lop, 2) : '-' }}</td>
            </tr>
            <tr>
                <td>Arrear</td>
                <td class="text-right">{{ $arrear > 0 ? number_format($arrear, 2) : '-' }}</td>
                <td>MEDISEP</td>
                <td class="text-right">{{ $medisep > 0 ? number_format($medisep, 2) : '-' }}</td>
            </tr>
            <tr>
                <td>Fixed Allowance</td>
                <td class="text-right">{{ $fixedAllowance > 0 ? number_format($fixedAllowance, 2) : '-' }}</td>
                <td>GPF</td>
                <td class="text-right">{{ $gpf > 0 ? number_format($gpf, 2) : '-' }}</td>
            </tr>
            <tr>
                <td rowspan="2">Other Allowance</td>
                <td rowspan="2" class="text-right">{{ $otherAllowance > 0 ? number_format($otherAllowance, 2) : '-' }}</td>
                <td>LIC Premium</td>
                <td class="text-right">{{ $lic > 0 ? number_format($lic, 2) : '-' }}</td>
            </tr>
            <tr>
                <td>SLI 1</td>
                <td class="text-right">{{ $sli1 > 0 ? number_format($sli1, 2) : '-' }}</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td>SLI 2</td>
                <td class="text-right">{{ $sli2 > 0 ? number_format($sli2, 2) : '-' }}</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td>SLI 3</td>
                <td class="text-right">{{ $sli3 > 0 ? number_format($sli3, 2) : '-' }}</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td>GIS</td>
                <td class="text-right">{{ $gis > 0 ? number_format($gis, 2) : '-' }}</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td>GPAIS</td>
                <td class="text-right">{{ $gpais > 0 ? number_format($gpais, 2) : '-' }}</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td>Others</td>
                <td class="text-right">{{ $others > 0 ? number_format($others, 2) : '-' }}</td>
            </tr>
            <tr style="background-color: #f8f9fa; font-weight: bold;">
                <td class="text-center">Total</td>
                <td class="text-right">{{ number_format($totalEarnings, 2) }}</td>
                <td class="text-center">Total</td>
                <td class="text-right">{{ number_format($totalDeductions, 2) }}</td>
            </tr>
        </tbody>
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
        <table style="width: 100%; border: none; margin-top: 30px;">
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
