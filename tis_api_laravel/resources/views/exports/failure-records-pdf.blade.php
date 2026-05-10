<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Failure Records Report</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        h1 { color: #333; }
    </style>
</head>
<body>
    <h1>Failure Records Report</h1>
    <p>Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>

    <table>
        <thead>
            <tr>
                <th>Timestamp</th>
                <th>Trainset ID</th>
                <th>Equipment Name</th>
                <th>Fault Name</th>
                <th>Classification</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $record)
            <tr>
                <td>{{ $record->timestamp->format('Y-m-d H:i:s') }}</td>
                <td>{{ $record->session->rake_id ?? 'N/A' }}</td>
                <td>{{ $record->equipment_name }}</td>
                <td>{{ $record->fault_name }}</td>
                <td>{{ $record->classification }}</td>
                <td>{{ $record->description }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>