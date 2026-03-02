<!DOCTYPE html>
<html>
<head>
    <title>Asset Register Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        h2 { text-align: center; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: right; font-size: 10px; }
    </style>
</head>
<body>
    <h2>Asset Register Report</h2>
    <p>Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>

    <table>
        <thead>
            <tr>
                <th>Asset #</th>
                <th>Name</th>
                <th>Category</th>
                <th>Department</th>
                <th>Cost</th>
                <th>Status</th>
                <th>Condition</th>
            </tr>
        </thead>
        <tbody>
            @foreach($assets as $asset)
            <tr>
                <td>{{ $asset->asset_number }}</td>
                <td>{{ $asset->name }}</td>
                <td>{{ $asset->category->name }}</td>
                <td>{{ $asset->category->department->name }}</td>
                <td>{{ number_format($asset->purchase_cost, 2) }}</td>
                <td>
                    @switch($asset->status)
                        @case(1) Available @break
                        @case(2) Allocated @break
                        @case(3) Maintenance @break
                        @case(4) Disposed @break
                        @case(5) Scrap @break
                    @endswitch
                </td>
                <td>{{ $asset->condition }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Page {PAGE_NUM}
    </div>
</body>
</html>
