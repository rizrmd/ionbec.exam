<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Candidate Token List</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            margin: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18px;
            margin: 5px 0;
        }
        .header h2 {
            font-size: 14px;
            margin: 5px 0;
            color: #555;
        }
        .header p {
            font-size: 10px;
            margin: 3px 0;
            color: #777;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table th {
            background-color: #f0f0f0;
            border: 1px solid #999;
            padding: 8px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
        }
        table td {
            border: 1px solid #ccc;
            padding: 6px;
            font-size: 10px;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #777;
        }
        .token {
            font-weight: bold;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Candidate Token List</h1>
        <h2>{{ $delivery->name }}</h2>
        <p>Group: {{ $group->name }}</p>
        <p>Scheduled at: {{ \Carbon\Carbon::parse($delivery->scheduled_at)->format('d F Y, H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 40px;">NO</th>
                <th style="width: 120px;">CODE</th>
                <th>NAME</th>
                <th>EMAIL</th>
                <th style="width: 100px;">TOKEN</th>
            </tr>
        </thead>
        <tbody>
            @forelse($takers as $index => $taker)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $taker->taker_code }}</td>
                <td>{{ $taker->name }}</td>
                <td>{{ $taker->email }}</td>
                <td class="token">{{ $taker->token ?: '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: 20px;">No candidates found</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Total Candidates: {{ count($takers) }}</p>
        <p>Generated on: {{ \Carbon\Carbon::now()->format('d F Y, H:i:s') }}</p>
    </div>
</body>
</html>
