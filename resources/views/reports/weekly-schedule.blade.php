<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Schedule of Events — {{ $week->label }}</title>
    @vite(['resources/css/app.css'])
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #000; background: #fff; }

        /* ── Print styles ── */
        @media print {
            @page { size: A4 landscape; margin: 10mm 8mm; }
            body { font-size: 9px; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; }
        }

        /* ── Header ── */
        .ksg-header { text-align: center; margin-bottom: 8px; }
        .ksg-header .logos { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; }
        .ksg-header .logos img { height: 60px; object-fit: contain; }
        .ksg-header h1 { font-size: 20px; font-weight: 900; letter-spacing: 1px; margin: 6px 0 2px; text-transform: uppercase; }
        .ksg-header h2 { font-size: 13px; font-weight: 700; margin: 0 0 2px; }
        /* "SCHEDULE OF EVENTS" in bold red */
        .ksg-header h3 { font-size: 16px; font-weight: 900; color: #cc0000; margin: 2px 0 0; text-transform: uppercase; letter-spacing: 0.5px; }
        /* Week label with thick black border */
        .ksg-header .week-label {
            font-size: 13px; font-weight: 800; margin: 5px auto 0;
            border-top: 2.5px solid #000; border-bottom: 2.5px solid #000;
            padding: 4px 0; display: inline-block; width: 100%;
        }
        .ref { text-align: right; font-size: 10px; font-weight: 700; margin-bottom: 4px; font-style: italic; }

        /* ── Table ── */
        .schedule-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; border: 1.5px solid #888; }
        .schedule-table th,
        .schedule-table td { border: 1px solid #aaa; padding: 3px 5px; vertical-align: top; }

        /* Column header row — light cream/yellow like the PDF */
        .schedule-table thead tr th {
            background: #fff9db;
            font-weight: 700;
            text-align: left;
            font-size: 10px;
            border-bottom: 1.5px solid #888;
        }

        /* Section header rows — medium gray, uppercase, centered, bold */
        .section-header td {
            background: #bfbfbf;
            font-weight: 800;
            text-align: center;
            text-transform: uppercase;
            font-size: 10.5px;
            letter-spacing: 0.4px;
            border-top: 1.5px solid #888;
            border-bottom: 1px solid #888;
            padding: 4px 5px;
        }

        /* Sub-header rows for hosted/consultancy */
        .sub-header th {
            background: #fff9db;
            font-weight: 700;
            font-size: 10px;
            text-align: left;
        }

        /* Data rows */
        .schedule-table tbody tr td { font-size: 10px; background: #fff; }
        .schedule-table tbody tr:nth-child(even) td { background: #f5f5f5; }

        /* Keep section headers always white bg override */
        .section-header td { background: #bfbfbf !important; }

        /* Row number */
        .row-num { width: 22px; text-align: center; font-weight: 600; }

        /* Column widths */
        .col-prog   { min-width: 150px; }
        .col-client { min-width: 90px; }
        .col-date   { min-width: 88px; white-space: nowrap; }
        .col-res    { width: 32px; text-align: center; }
        .col-nonres { width: 40px; text-align: center; }
        .col-coord  { min-width: 75px; }
        .col-asst   { min-width: 75px; }
        .col-venue  { min-width: 75px; }
        /* Remarks: bold italic, right-ish */
        .col-rem    { min-width: 65px; font-weight: 700; font-style: italic; }

        .dash { color: #888; }
        .prog-name { font-weight: 700; }
        .prog-ref  { color: #555; font-size: 9.5px; }

        /* ── Analysis section ── */
        .analysis-wrap { display: flex; gap: 20px; margin-top: 6px; }
        .analysis-box { flex: 1; }
        .analysis-box h4 { font-size: 11px; font-style: italic; font-weight: 600; margin: 0 0 3px; text-decoration: underline; }
        .analysis-table { width: 100%; border-collapse: collapse; font-size: 10px; }
        .analysis-table th,
        .analysis-table td { border: 1px solid #aaa; padding: 2px 6px; }
        .analysis-table thead th { background: #fff9db; font-weight: 700; }
        .analysis-table tbody td { background: #fff; }
        .analysis-table tbody tr:nth-child(even) td { background: #f5f5f5; }
        /* Highlighted totals — orange-yellow like the PDF */
        .highlight-row td { background: #ffd966 !important; font-weight: 800; }

        /* ── Controls (no-print) ── */
        .controls {
            background: #1e293b; color: #fff;
            padding: 10px 16px;
            display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
        }
        .controls select { border-radius: 6px; border: none; padding: 6px 10px; font-size: 13px; }
        .controls button, .controls a {
            padding: 7px 16px; border-radius: 6px; font-size: 13px;
            font-weight: 600; cursor: pointer; border: none;
            text-decoration: none; display: inline-block;
        }
        .btn-print { background: #4f46e5; color: #fff; }
        .btn-csv   { background: #16a34a; color: #fff; }
        .btn-back  { background: #475569; color: #fff; }
    </style>
</head>
<body>

    {{-- ── Controls (hidden when printing) ── --}}
    <div class="controls no-print">
        <form method="GET" action="{{ route('reports.weekly-schedule') }}" style="display:flex;gap:8px;align-items:center">
            <label style="color:#94a3b8;font-size:12px">Week:</label>
            <select name="week" onchange="this.form.submit()">
                @foreach ($weeks as $w)
                    <option value="{{ $w->id }}" {{ $w->id === $week->id ? 'selected' : '' }}>
                        {{ $w->label }}{{ $w->is_current ? ' ✦' : '' }}
                    </option>
                @endforeach
            </select>
        </form>
        <button class="btn-print" onclick="window.print()">🖨 Print / Save PDF</button>
        <a class="btn-csv" href="{{ route('reports.weekly-csv', ['week' => $week->id]) }}">⬇ Export CSV</a>
        <a class="btn-back" href="{{ route('admin.planning-weeks.index') }}">← Back</a>
    </div>

    {{-- ── Document ── --}}
    <div style="padding: 10px 14px;">

        {{-- Reference --}}
        <div class="ref">KSG/12/WS/01</div>

        {{-- Header --}}
        <div class="ksg-header">
            <div class="logos">
                <div style="display:flex;align-items:center;gap:0">
                    <img src="{{ asset('assets/logos/1.png') }}" alt="">
                    <img src="{{ asset('assets/logos/2.png') }}" alt="">
                </div>
                <div>
                    <h1>KENYA SCHOOL OF GOVERNMENT</h1>
                    <h2>CAMPUS: {{ strtoupper($week->campus ?? 'NAIROBI') }}</h2>
                    <h3>Schedule of Events</h3>
                </div>
                <img src="{{ asset('assets/logos/3.png') }}" alt="" style="height:56px">
            </div>
            <div class="week-label">WEEK: {{ $week->label }}</div>
        </div>

        {{-- ── Main schedule table ── --}}
        @php
            $counter = 0;
            $sectionHeaders = [
                'new_programme' => 'NEW PROGRAMMES',
                'ongoing'       => 'ONGOING PROGRAMS / EVENTS',
                'hosted'        => 'HOSTED EVENTS / CONFERENCES',
                'ksg_activity'  => 'KSG ACTIVITIES',
                'consultancy'   => 'CONSULTANCIES & RESEARCH',
            ];

            // Hosted & Consultancy use different column labels
            $standardCols    = ['Course Coordinator', 'Programme Assistant', 'Venue', 'Remarks'];
            $hostedCols      = ['Program Administrator', 'Facility & Logistics Coordinator', 'Venue', 'Remarks'];
            $consultancyCols = ['Project Coordinator', 'Research Assistant', 'Venue', 'Remarks'];

            function eventCols(string $cat): array {
                return match($cat) {
                    'hosted'      => ['Program Administrator', 'Facility & Logistics Coordinator', 'Venue', 'Remarks'],
                    'consultancy' => ['Project Coordinator', 'Research Assistant', 'Venue', 'Remarks'],
                    default       => ['Course Coordinator', 'Programme Assistant', 'Venue', 'Remarks'],
                };
            }
        @endphp

        <table class="schedule-table">
            <thead>
                <tr>
                    <th class="row-num">#</th>
                    <th class="col-prog">Programme</th>
                    <th class="col-client">Client</th>
                    <th class="col-date">Date</th>
                    <th class="col-res">Res</th>
                    <th class="col-nonres">Non-Res</th>
                    <th class="col-coord">Course Coordinator</th>
                    <th class="col-asst">Programme Assistant</th>
                    <th class="col-venue">Venue</th>
                    <th class="col-rem">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($categoryOrder as $cat)
                    @php $catEvents = $grouped[$cat->value]; @endphp

                    {{-- Section header --}}
                    <tr>
                        <td colspan="10" class="section-header">{{ $sectionHeaders[$cat->value] }}</td>
                    </tr>

                    {{-- Column sub-header for hosted/consultancy --}}
                    @if (in_array($cat->value, ['hosted', 'consultancy']) && $catEvents->isNotEmpty())
                        @php $cols = eventCols($cat->value); @endphp
                        <tr class="sub-header">
                            <th class="row-num">#</th>
                            <th class="col-prog">Programme</th>
                            <th class="col-client">Client</th>
                            <th class="col-date">Date</th>
                            <th class="col-res">Res</th>
                            <th class="col-nonres">Non-Res</th>
                            <th class="col-coord">{{ $cols[0] }}</th>
                            <th class="col-asst">{{ $cols[1] }}</th>
                            <th class="col-venue">Venue</th>
                            <th class="col-rem">Remarks</th>
                        </tr>
                    @endif

                    @if ($catEvents->isEmpty())
                        <tr>
                            <td colspan="10" style="text-align:center;color:#999;font-style:italic;padding:6px">
                                No events in this category
                            </td>
                        </tr>
                    @else
                        @foreach ($catEvents as $event)
                            @php $counter++; @endphp
                            <tr>
                                <td class="row-num">{{ $counter }}.</td>
                                <td class="col-prog">
                                    <strong>{{ $event->name }}</strong>
                                    @if ($event->schedule_reference)
                                        <br><span style="color:#555">{{ $event->schedule_reference }}</span>
                                    @endif
                                </td>
                                <td class="col-client">{{ $event->client ?? '—' }}</td>
                                <td class="col-date">
                                    {{ $event->event_date->format('d M Y') }}
                                    @if ($event->event_date_end && ! $event->event_date->isSameDay($event->event_date_end))
                                        – {{ $event->event_date_end->format('d M Y') }}
                                    @endif
                                </td>
                                <td class="col-res">
                                    @if ($event->participants_residential)
                                        {{ $event->participants_residential }}
                                    @else
                                        <span class="dash">-</span>
                                    @endif
                                </td>
                                <td class="col-nonres">
                                    @if ($event->participants_non_residential)
                                        {{ $event->participants_non_residential }}
                                    @else
                                        <span class="dash">-</span>
                                    @endif
                                </td>
                                <td class="col-coord">{{ $event->coordinator ?? '—' }}</td>
                                <td class="col-asst">{{ $event->programme_assistant ?? '—' }}</td>
                                <td class="col-venue">{{ $event->venue ?? '—' }}</td>
                                <td class="col-rem">{{ $event->package_remarks ?? '' }}</td>
                            </tr>
                        @endforeach
                    @endif
                @endforeach
            </tbody>
        </table>

        {{-- ── Analysis section ── --}}
        @php
            $onlineCount    = $grouped['ongoing']->filter(fn($e) => str_contains(strtolower($e->venue ?? ''), 'lms') || str_contains(strtolower($e->venue ?? ''), 'online'))->sum(fn($e) => ($e->participants_non_residential ?? 0));
            $offsiteCount   = $grouped['consultancy']->filter(fn($e) => str_contains(strtolower($e->package_remarks ?? ''), 'offsite'))->sum(fn($e) => ($e->participants_non_residential ?? 0));
            $ksgActTotal    = $grouped['ksg_activity']->sum(fn($e) => ($e->participants_residential ?? 0) + ($e->participants_non_residential ?? 0));
            $consultTotal   = $grouped['consultancy']->sum(fn($e) => ($e->participants_residential ?? 0) + ($e->participants_non_residential ?? 0));
        @endphp

        <div class="analysis-wrap">
            {{-- Participant analysis --}}
            <div class="analysis-box">
                <h4>Analysis</h4>
                <table class="analysis-table">
                    <tbody>
                        @foreach ([
                            ['Online',                   $onlineCount],
                            ['Non-residents',             $totalNonResidents],
                            ['Residents',                 $totalResidents],
                            ['Offsite',                   $offsiteCount],
                            ['KSG Activities',            $ksgActTotal],
                            ['Consultancies and Research',$consultTotal],
                        ] as [$label, $val])
                            <tr>
                                <td>{{ $label }}</td>
                                <td style="text-align:right;font-weight:600">{{ $val }}</td>
                            </tr>
                        @endforeach
                        <tr class="highlight-row">
                            <td><strong>Total participants this week</strong></td>
                            <td style="text-align:right"><strong>{{ $totalParticipants }}</strong></td>
                        </tr>
                        <tr class="highlight-row">
                            <td><strong>Total participants in Campus</strong></td>
                            <td style="text-align:right"><strong>{{ $totalResidents }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Accommodation analysis placeholder --}}
            <div class="analysis-box">
                <h4>Accommodation Analysis</h4>
                <table class="analysis-table">
                    <thead>
                        <tr>
                            <th>Hostel Name</th>
                            <th>Residency</th>
                            <th>Capacity</th>
                            <th>Occupancy</th>
                            <th>Out of Order</th>
                            <th>Available</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="6" style="text-align:center;color:#999;font-style:italic">
                                Fill in accommodation data manually
                            </td>
                        </tr>
                        <tr class="total-row">
                            <td colspan="2"><strong>TOTAL</strong></td>
                            <td></td><td></td><td></td><td></td>
                        </tr>
                    </tbody>
                </table>
                <p style="font-size:10px;margin:4px 0 0">➤ Outsourced Accommodation = ___</p>
            </div>
        </div>

        <p style="font-size:9px;color:#666;margin-top:12px;text-align:right">
            Revised on {{ now()->format('n/j/Y g:i A') }}
        </p>

    </div>

</body>
</html>
