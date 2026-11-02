<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Weekly Schedule - {{ $planningWeek->label }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11pt;
            color: #1f2937;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #1e40af;
            padding-bottom: 20px;
        }
        .logos {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-bottom: 15px;
        }
        .logo {
            height: 50px;
        }
        h1 {
            color: #1e40af;
            font-size: 20pt;
            margin: 10px 0;
        }
        .meta {
            color: #6b7280;
            font-size: 10pt;
            margin-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #1e40af;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        td {
            padding: 8px 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .event-name {
            font-weight: 600;
            color: #111827;
        }
        .event-type {
            display: inline-block;
            padding: 3px 8px;
            background-color: #dbeafe;
            color: #1e40af;
            border-radius: 4px;
            font-size: 9pt;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 9pt;
        }
        .summary {
            background-color: #f0f9ff;
            padding: 15px;
            border-left: 4px solid #1e40af;
            margin: 20px 0;
        }
        .summary-item {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Weekly Event Schedule</h1>
        <div class="meta">
            {{ $planningWeek->label }}<br>
            {{ $planningWeek->week_start->format('F j, Y') }} - {{ $planningWeek->week_end->format('F j, Y') }}
        </div>
    </div>

    <div class="summary">
        <div class="summary-item"><strong>Total Events:</strong> {{ $events->count() }}</div>
        <div class="summary-item"><strong>Department:</strong> {{ $departmentName }}</div>
        <div class="summary-item"><strong>Generated:</strong> {{ now()->format('F j, Y \a\t H:i') }}</div>
    </div>

    @if($events->count() > 0)
    <table>
        <thead>
            <tr>
                <th style="width: 40%;">Event Name</th>
                <th style="width: 25%;">Date & Time</th>
                <th style="width: 20%;">Type</th>
                <th style="width: 15%;">Venue</th>
            </tr>
        </thead>
        <tbody>
            @foreach($events as $event)
            <tr>
                <td class="event-name">{{ $event->name }}</td>
                <td>
                    {{ $event->event_date->format('D, M j, Y') }}
                    @if($event->event_time)
                        <br><small style="color: #6b7280;">{{ \Carbon\Carbon::parse($event->event_time)->format('H:i') }}</small>
                    @endif
                </td>
                <td>
                    <span class="event-type">{{ $event->type }}</span>
                </td>
                <td>{{ $event->venue ?? 'TBA' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div style="text-align: center; padding: 40px; color: #6b7280;">
        No events scheduled for this week.
    </div>
    @endif

    <div class="footer">
        <strong>Kenya School of Government</strong><br>
        Event Readiness Management System<br>
        Generated on {{ now()->format('F j, Y \a\t H:i') }}
    </div>
</body>
</html>
