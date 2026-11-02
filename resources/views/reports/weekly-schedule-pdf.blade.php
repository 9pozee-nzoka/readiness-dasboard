<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Schedule of Events — {{ $week->label }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 10pt; color: #000; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 3px solid #000; padding-bottom: 10px; }
        .header h1 { font-size: 20pt; font-weight: bold; margin-bottom: 5px; }
        .header .campus { font-size: 11pt; font-weight: bold; margin: 5px 0; }
        .header h2 { font-size: 14pt; color: #dc2626; font-weight: bold; margin-top: 5px; }
        .week-label { font-size: 11pt; font-weight: bold; margin: 15px 0; padding: 5px 0; border-top: 2px solid #000; border-bottom: 2px solid #000; }
        .category { margin-bottom: 10px; page-break-inside: avoid; }
        .category-header { background: #90EE90; color: #000; padding: 6px 8px; font-weight: bold; font-size: 10pt; border: 1px solid #000; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        th { background: #90EE90; border: 1px solid #000; padding: 6px 4px; text-align: center; font-size: 9pt; font-weight: bold; color: #000; }
        td { border: 1px solid #000; padding: 5px 4px; font-size: 9pt; vertical-align: top; }
        .no-events { text-align: center; padding: 15px; font-style: italic; color: #666; border: 1px solid #000; }
        .analysis-section { margin-top: 20px; page-break-inside: avoid; }
        .analysis-title { font-weight: bold; font-size: 11pt; margin-bottom: 10px; text-decoration: underline; }
        .stats-container { display: table; width: 100%; }
        .stats-left { display: table-cell; width: 45%; vertical-align: top; }
        .stats-right { display: table-cell; width: 55%; vertical-align: top; padding-left: 20px; }
        .stats-table { width: 100%; border-collapse: collapse; }
        .stats-table td { border: 1px solid #000; padding: 6px 8px; font-size: 9pt; }
        .stats-table td:first-child { font-weight: normal; background: #fff; }
        .stats-table td:last-child { text-align: center; font-weight: bold; }
        .stats-table tr.total { background: #90EE90; font-weight: bold; }
        .accommodation-table th { background: #90EE90; }
        .accommodation-note { font-style: italic; margin-top: 10px; }
        .ref-code { position: fixed; top: 20px; right: 20px; font-size: 9pt; font-weight: bold; }
        .footer { position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); font-size: 8pt; color: #666; text-align: center; }
        .highlight-yellow { background: #ffff99; }
    </style>
</head>
<body>
    <div class="ref-code">KSG/12/WS/{{ str_pad($week->id, 2, '0', STR_PAD_LEFT) }}</div>

    <div class="header">
        <h1>KENYA SCHOOL OF GOVERNMENT</h1>
        <div class="campus">CAMPUS: {{ strtoupper($week->campus ?? 'NAIROBI') }}</div>
        <h2>SCHEDULE OF EVENTS</h2>
    </div>

    <div class="week-label">WEEK: {{ $week->label }}</div>

    @foreach($grouped as $category => $events)
        <div class="category">
            <table>
                <thead>
                    <tr class="category-header">
                        <th colspan="10">{{ strtoupper($category) }}</th>
                    </tr>
                    <tr>
                        <th style="width: 3%;">#</th>
                        <th style="width: 18%;">Programme</th>
                        <th style="width: 13%;">Client</th>
                        <th style="width: 12%;">Date</th>
                        <th style="width: 4%;">Res</th>
                        <th style="width: 5%;">Non-Res</th>
                        <th style="width: 12%;">Course Coordinator</th>
                        <th style="width: 12%;">Programme Assistant</th>
                        <th style="width: 11%;">Venue</th>
                        <th style="width: 10%;">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @if($events->isEmpty())
                        <tr>
                            <td colspan="10" class="no-events">No events in this category</td>
                        </tr>
                    @else
                        @foreach($events as $index => $event)
                            <tr>
                                <td style="text-align: center;">{{ $loop->iteration }}.</td>
                                <td>{{ $event->name }}</td>
                                <td>{{ $event->client ?? '—' }}</td>
                                <td>{{ $event->dateRangeLabel() }}</td>
                                <td style="text-align: center;">{{ $event->participants_residential ?: '-' }}</td>
                                <td style="text-align: center;">{{ $event->participants_non_residential ?: '-' }}</td>
                                <td>{{ $event->coordinator ?? '—' }}</td>
                                <td>{{ $event->programme_assistant ?? '—' }}</td>
                                <td>{{ $event->venue ?? '—' }}</td>
                                <td>{{ $event->package_remarks ?? '' }}</td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    @endforeach

    <div class="analysis-section">
        <div class="stats-container">
            <div class="stats-left">
                <div class="analysis-title">Analysis</div>
                <table class="stats-table">
                    <tr>
                        <td>Online</td>
                        <td>{{ $stats['online'] }}</td>
                    </tr>
                    <tr>
                        <td>Non-residents</td>
                        <td>{{ $stats['non_residents'] }}</td>
                    </tr>
                    <tr>
                        <td>Residents</td>
                        <td>{{ $stats['residents'] }}</td>
                    </tr>
                    <tr>
                        <td>Offsite</td>
                        <td>{{ $stats['offsite'] }}</td>
                    </tr>
                    <tr>
                        <td>KSG Activities</td>
                        <td>{{ $stats['ksg_activities'] }}</td>
                    </tr>
                    <tr>
                        <td>Consultancies and Research</td>
                        <td>{{ $stats['consultancies'] }}</td>
                    </tr>
                    <tr class="total">
                        <td>Total participants this week</td>
                        <td>{{ $stats['non_residents'] + $stats['residents'] + $stats['consultancies'] + $stats['ksg_activities'] }}</td>
                    </tr>
                    <tr class="total">
                        <td>Total participants in Campus</td>
                        <td>{{ $stats['residents'] }}</td>
                    </tr>
                </table>
            </div>

            <div class="stats-right">
                <div class="analysis-title">Accommodation Analysis</div>
                <table class="accommodation-table">
                    <thead>
                        <tr>
                            <th>HOSTEL NAME</th>
                            <th>RESIDENCY</th>
                            <th>HOSTEL CAPACITY</th>
                            <th>HOSTEL OCCUPANCY</th>
                            <th>ROOMS OUT OF ORDER</th>
                            <th>AVAILABLE ROOM</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="6" style="text-align: center; font-style: italic;">Fill in accommodation data manually</td>
                        </tr>
                        <tr class="total">
                            <td colspan="2">TOTAL</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
                <div class="accommodation-note">➤ Outsourced Accommodation = ___</div>
            </div>
        </div>
    </div>

    <div class="footer">
        Revised on {{ now()->format('n/j/Y g:i A') }}
    </div>
</body>
</html>
