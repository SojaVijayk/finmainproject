@php
    // Earnings mapping based on Reference Image and employee_payroll table
    $basicPay = $payroll->basic_pay > 0 ? $payroll->basic_pay : ($payroll->service_basic ?? 0);
    $da = $payroll->da > 0 ? $payroll->da : ($payroll->service_da ?? 0);
    $hra = $payroll->hra > 0 ? $payroll->hra : ($payroll->service_hra ?? 0);
    // Controller maps 'Arrear' to 'other_allowance'
    $arrear = $payroll->other_allowance ?? 0;
    
    // Fixed Allowance sum
    $fixedAllowance = ($payroll->conveyance_allowance ?? 0) + ($payroll->medical_allowance ?? 0) + ($payroll->special_allowance ?? 0);
    
    // Other Allowance sum
    $otherAllowance = ($payroll->festival_allowance ?? 0) + ($payroll->bonus ?? 0) + ($payroll->overtime_pay ?? 0) + ($payroll->attendance_bonus ?? 0);
    
    $totalEarnings = $basicPay + $da + $hra + $arrear + $fixedAllowance + $otherAllowance;
    
    // Deductions mapping
    $tds = $payroll->tds + $payroll->tds_192_b + $payroll->tds_194_j;
    $profTax = $payroll->professional_tax;
    $lop = (float)($payroll->lop_days ?? 0); // Display as '-' in table if 0
    
    $medisep = $payroll->medisep ?? ($dynamicDeductions['MEDISEP'] ?? $dynamicDeductions['Medisep'] ?? 0);
    $gpf = $payroll->gpf ?? ($payroll->pf ?? 0); 
    $lic = $payroll->lic + $payroll->lic_others;
    $sli1 = $payroll->sli1 ?? ($dynamicDeductions['SLI 1'] ?? 0);
    $sli2 = $payroll->sli2 ?? ($dynamicDeductions['SLI 2'] ?? 0);
    $sli3 = $payroll->sli3 ?? ($dynamicDeductions['SLI 3'] ?? 0);
    $gis = $payroll->gis ?? ($dynamicDeductions['GIS'] ?? $dynamicDeductions['Gis'] ?? 0);
    $gpais = $payroll->gpais ?? ($dynamicDeductions['GPAIS'] ?? 0);
    $others = $payroll->others ?? 0;

    $totalDeductions = $tds + $profTax + $medisep + $gpf + $lic + $sli1 + $sli2 + $sli3 + $gis + $gpais + $others;
    // Note: LOP amount is usually already subtracted from Gross or handled, so we match image's totals logic.
    $netSalary = $totalEarnings - $totalDeductions;
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Slip - {{ $payroll->name }}</title>
    <style>
        @page { margin: 10mm; }
        body { font-family: 'Times New Roman', serif; color: #000; margin: 0; padding: 0; background-color: #fff; line-height: 1.4; font-size: 14px; }
        .slip-container { width: 100%; max-width: 800px; margin: 0 auto; padding: 20px; position: relative; }
        
        /* Header Styling matching image */
        .header-section { text-align: center; margin-bottom: 30px; position: relative; }
        .logo-container { position: absolute; left: 10px; top: 0; }
        .logo { width: 85px; }
        .header-content { padding-top: 0; }
        .header-content h1 { margin: 0; font-size: 26px; color: #003366; font-weight: bold; }
        .header-content .subtitle { margin: 2px 0; font-size: 13px; color: #cc0000; font-weight: bold; }
        .header-content .address { margin: 0; font-size: 13px; font-weight: bold; }
        
        /* Employee Details Styling */
        .details-table { width: 100%; margin-bottom: 25px; border-collapse: collapse; margin-top: 50px; }
        .details-table td { padding: 5px 0; border: none; vertical-align: top; font-size: 15px; }
        .label-cell { width: 160px; font-weight: normal; }
        .value-cell { padding-left: 20px; font-weight: normal; }
        
        /* Month Banner */
        .month-banner { background-color: #eeeeee; text-align: center; padding: 8px; font-weight: bold; font-size: 16px; margin-bottom: 20px; border-top: 1px solid #ddd; border-bottom: 1px solid #ddd; }
        
        /* Main Earnings/Deductions Table */
        .main-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; border: 1px solid #ccc; }
        .main-table th { border: 1px solid #ccc; padding: 8px; font-weight: bold; text-align: center; font-size: 15px; text-decoration: underline; }
        .main-table td { border: 1px solid #ccc; padding: 6px 15px; font-size: 14px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        /* Net Salary Section */
        .net-section { text-align: right; margin-top: 10px; }
        .net-table { float: right; border-collapse: collapse; min-width: 250px; }
        .net-table td { padding: 5px 10px; border: none; font-weight: bold; font-size: 16px; text-align: right; }
        .net-label { width: 120px; padding-right: 20px; }
        .net-value { width: 130px; border-top: 1px solid #000; border-bottom: 4px double #000; padding: 6px 0; }
        
        /* Footer Sections */
        .in-words { font-weight: bold; margin-top: 30px; clear: both; text-align: left; padding-left: 10px; font-size: 14px; }
        .issued-to { margin-top: 80px; text-align: left; font-size: 14px; padding-left: 10px; }
        
        .footer-meta { width: 100%; margin-top: 100px; border-collapse: collapse; }
        .footer-meta td { border: none; font-size: 14px; font-weight: bold; }
        
        /* Control Buttons (Screen only) */
        .controls { position: fixed; top: 20px; right: 20px; z-index: 1000; }
        .btn { padding: 10px 20px; border-radius: 4px; border: none; color: #fff; cursor: pointer; text-decoration: none; font-size: 14px; margin-left: 10px; display: inline-block; }
        .btn-print { background-color: #003366; }
        .btn-back { background-color: #666; }
        
        @media print {
            .controls { display: none; }
            body { background-color: #fff; padding: 0; }
            .slip-container { border: none; padding: 0; }
        }
    </style>
</head>
<body>

@if(!isset($isExport))
    <div class="controls">
        <a href="{{ route('pms.deduction-master.select-employees', $payroll->project_id) }}" class="btn btn-back">← Back</a>
        <button onclick="window.print()" class="btn btn-print">Print Slip</button>
    </div>
@endif

<div class="slip-container">
    <div class="header-section">
        <div class="logo-container">
            <img src="{{ public_path('assets/img/branding/CMD-logo.png') }}" alt="CMD Logo" class="logo">
        </div>
        <div class="header-content">
            <h1>Centre for Management Development</h1>
            <p class="subtitle">(An Autonomous Institution under the Government of Kerala)</p>
            <p class="address">CV Raman Pillai Road, Thycaud P.O, Thiruvananthapuram 695014</p>
        </div>
    </div>

    <table class="details-table">
        <tr>
            <td class="label-cell">Name</td>
            <td class="value-cell">{{ $payroll->name }}</td>
        </tr>
        <tr>
            <td class="label-cell">Designation</td>
            <td class="value-cell">{{ $payroll->role }}</td>
        </tr>
        <tr>
            <td colspan="2" style="height: 15px;"></td>
        </tr>
        <tr>
            <td class="label-cell">Name of Project</td>
            <td class="value-cell">{{ $payroll->project_name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td colspan="2" style="height: 15px;"></td>
        </tr>
        <tr>
            <td class="label-cell">PAN</td>
            <td class="value-cell">{{ $payroll->pan_number ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label-cell">Bank Account No.</td>
            <td class="value-cell">{{ $payroll->account_no ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label-cell">Bank Name</td>
            <td class="value-cell">{{ $payroll->bank_name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label-cell">Bank IFSC</td>
            <td class="value-cell">{{ $payroll->ifsc_code ?? 'N/A' }}</td>
        </tr>
    </table>

    <div class="month-banner">
        {{ $payroll->paymonth }}, {{ $payroll->year }}
    </div>

    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 32%;">Earnings</th>
                <th style="width: 18%;"></th>
                <th style="width: 32%;">Deductions</th>
                <th style="width: 18%;"></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Basic Pay</td>
                <td class="text-right">{{ number_format($basicPay, 2) }}</td>
                <td>Tax Deducted at Source</td>
                <td class="text-right">{{ $tds > 0 ? number_format($tds, 2) : '-' }}</td>
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
                <td class="text-right">-</td>
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
                <td rowspan="7" style="vertical-align: top;">Other Allowance</td>
                <td rowspan="7" style="vertical-align: top;" class="text-right">{{ $otherAllowance > 0 ? number_format($otherAllowance, 2) : '-' }}</td>
                <td>LIC Premium</td>
                <td class="text-right">{{ $lic > 0 ? number_format($lic, 2) : '-' }}</td>
            </tr>
            <tr>
                <td>SLI 1</td>
                <td class="text-right">{{ $sli1 > 0 ? number_format($sli1, 2) : '-' }}</td>
            </tr>
            <tr>
                <td>SLI 2</td>
                <td class="text-right">{{ $sli2 > 0 ? number_format($sli2, 2) : '-' }}</td>
            </tr>
            <tr>
                <td>SLI 3</td>
                <td class="text-right">{{ $sli3 > 0 ? number_format($sli3, 2) : '-' }}</td>
            </tr>
            <tr>
                <td>GIS</td>
                <td class="text-right">{{ $gis > 0 ? number_format($gis, 2) : '-' }}</td>
            </tr>
            <tr>
                <td>GPAIS</td>
                <td class="text-right">{{ $gpais > 0 ? number_format($gpais, 2) : '-' }}</td>
            </tr>
            <tr>
                <td>Others</td>
                <td class="text-right">{{ $others > 0 ? number_format($others, 2) : '-' }}</td>
            </tr>
            <tr style="font-weight: bold;">
                <td></td>
                <td class="text-right">{{ number_format($totalEarnings, 2) }}</td>
                <td></td>
                <td class="text-right">{{ number_format($totalDeductions, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="net-section">
        <table class="net-table">
            <tr>
                <td class="net-label">Net</td>
                <td class="net-value">{{ number_format($netSalary, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="in-words">
        ({{ $netInWords }})
    </div>

    <div class="issued-to">
        This is issued for the information of : {{ $payroll->name }}
    </div>

    <table class="footer-meta">
        <tr>
            <td style="width: 50%;">Document No: <span style="font-weight: normal;">{{ $payroll->name }} {{ $payroll->paymonth }}, {{ $payroll->year }}</span></td>
            <td style="width: 50%; text-align: right;">Date: <span style="font-weight: normal;">{{ date('d-m-Y') }}</span></td>
        </tr>
    </table>
</div>

</body>
</html>
