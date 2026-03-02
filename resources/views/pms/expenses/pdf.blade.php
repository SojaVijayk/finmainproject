<!DOCTYPE html>
<html>
<head>
    <title>Expenses Report</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; text-align: left; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h1>Expenses Report</h1>
    <p>Generated on: {{ now()->format('M d, Y h:i A') }}</p>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Project</th>
                <th>Category</th>
                <th>Vendor</th>
                <th>Amount</th>
                <th>Tax</th>
                <th>Total</th>
                <th>Payment Mode</th>
                <th>Payment Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenses as $expense)
            <tr>
                <td>{{ $expense->id }}</td>
                <td>{{ $expense->project->name }}</td>
                <td>{{ $expense->category->name }}</td>
                <td>{{ $expense->vendor->name }}</td>
                <td class="text-right">${{ number_format($expense->amount, 2) }}</td>
                <td class="text-right">${{ number_format($expense->tax, 2) }}</td>
                <td class="text-right">${{ number_format($expense->total_amount, 2) }}</td>
                <td>{{ ucwords(str_replace('_', ' ', $expense->payment_mode)) }}</td>
                <td>{{ $expense->payment_date->format('M d, Y') }}</td>
            </tr>
            @endforeach
            @if($expenses->count() > 0)
            <tr>
                <td colspan="4" class="text-right"><strong>Total:</strong></td>
                <td class="text-right"><strong>${{ number_format($expenses->sum('amount'), 2) }}</strong></td>
                <td class="text-right"><strong>${{ number_format($expenses->sum('tax'), 2) }}</strong></td>
                <td class="text-right"><strong>${{ number_format($expenses->sum('total_amount'), 2) }}</strong></td>
                <td colspan="2"></td>
            </tr>
            @endif
        </tbody>
    </table>
</body>
</html>