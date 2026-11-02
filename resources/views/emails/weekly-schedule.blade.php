<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Event Schedule</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: Arial, sans-serif;">
    <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f3f4f6; padding: 20px 0;">
        <tr>
            <td align="center">
                <!-- Main Container -->
                <table role="presentation" style="width: 600px; max-width: 100%; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); overflow: hidden;">
                    
                    <!-- Header with Kenya Coat of Arms & KSG Logo -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); padding: 30px 20px; text-align: center;">
                            <table role="presentation" style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td style="width: 20%; text-align: left; vertical-align: middle;">
                                        <!-- Kenya Coat of Arms placeholder -->
                                        <div style="width: 60px; height: 60px; background-color: rgba(255,255,255,0.2); border-radius: 50%; display: inline-block; border: 2px solid #ffffff;"></div>
                                    </td>
                                    <td style="width: 60%; text-align: center; vertical-align: middle;">
                                        <h1 style="margin: 0; color: #ffffff; font-size: 22px; font-weight: bold; letter-spacing: 0.5px;">KENYA SCHOOL OF GOVERNMENT</h1>
                                        <p style="margin: 5px 0 0 0; color: #e0e7ff; font-size: 13px; font-weight: 500;">Campus: {{ strtoupper($planningWeek->campus ?? 'Nairobi') }}</p>
                                    </td>
                                    <td style="width: 20%; text-align: right; vertical-align: middle;">
                                        <!-- KSG Logo placeholder -->
                                        <div style="width: 60px; height: 60px; background-color: rgba(255,255,255,0.2); border-radius: 8px; display: inline-block; border: 2px solid #ffffff;"></div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Red Banner -->
                    <tr>
                        <td style="background-color: #dc2626; padding: 15px 20px; text-align: center;">
                            <h2 style="margin: 0; color: #ffffff; font-size: 18px; font-weight: bold; letter-spacing: 1px;">📅 WEEKLY EVENT SCHEDULE</h2>
                        </td>
                    </tr>

                    <!-- Greeting Section -->
                    <tr>
                        <td style="padding: 30px 30px 20px 30px;">
                            <p style="margin: 0 0 15px 0; font-size: 15px; color: #1f2937; line-height: 1.6;">
                                Dear <strong>{{ $recipient->name }}</strong>,
                            </p>
                            <p style="margin: 0; font-size: 14px; color: #4b5563; line-height: 1.6;">
                                You are receiving the <strong>weekly event schedule</strong> {{ $departmentPart }}. Please review the attached document for complete details.
                            </p>
                        </td>
                    </tr>

                    <!-- Week Info Card -->
                    <tr>
                        <td style="padding: 0 30px 20px 30px;">
                            <table role="presentation" style="width: 100%; background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border-radius: 8px; border: 2px solid #3b82f6; padding: 20px;">
                                <tr>
                                    <td>
                                        <h3 style="margin: 0 0 10px 0; color: #1e40af; font-size: 16px; font-weight: bold;">{{ $planningWeek->label }}</h3>
                                        <p style="margin: 0 0 5px 0; color: #1e40af; font-size: 14px;">
                                            <strong>📆 Period:</strong> {{ $planningWeek->week_start->format('l, d F Y') }} – {{ $planningWeek->week_end->format('l, d F Y') }}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Statistics Section -->
                    <tr>
                        <td style="padding: 0 30px 25px 30px;">
                            <table role="presentation" style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td style="width: 48%; padding: 15px; background-color: #f0fdf4; border-radius: 8px; border-left: 4px solid #22c55e; vertical-align: top;">
                                        <p style="margin: 0 0 5px 0; color: #166534; font-size: 12px; font-weight: 600; text-transform: uppercase;">Scheduled Events</p>
                                        <p style="margin: 0; color: #15803d; font-size: 28px; font-weight: bold;">{{ $eventCount }}</p>
                                    </td>
                                    <td style="width: 4%;"></td>
                                    <td style="width: 48%; padding: 15px; background-color: #fef3c7; border-radius: 8px; border-left: 4px solid #f59e0b; vertical-align: top;">
                                        <p style="margin: 0 0 5px 0; color: #92400e; font-size: 12px; font-weight: 600; text-transform: uppercase;">Total Participants</p>
                                        <p style="margin: 0; color: #b45309; font-size: 28px; font-weight: bold;">{{ $totalParticipants }}</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Events by Category -->
                    @foreach($grouped as $category => $events)
                    <tr>
                        <td style="padding: 0 30px 20px 30px;">
                            <!-- Category Header -->
                            <div style="background-color: #90EE90; padding: 10px 15px; border-radius: 6px 6px 0 0; border-bottom: 3px solid #22c55e;">
                                <h4 style="margin: 0; color: #166534; font-size: 14px; font-weight: bold; text-transform: uppercase;">{{ $category }} ({{ $events->count() }})</h4>
                            </div>
                            
                            <!-- Events List -->
                            <div style="background-color: #f9fafb; padding: 15px; border-radius: 0 0 6px 6px; border: 1px solid #e5e7eb; border-top: none;">
                                @foreach($events as $event)
                                <div style="padding: 12px; background-color: #ffffff; border-radius: 6px; margin-bottom: {{ $loop->last ? '0' : '10px' }}; border-left: 3px solid #3b82f6; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                                    <p style="margin: 0 0 8px 0; color: #1f2937; font-size: 14px; font-weight: bold;">
                                        {{ $event->name }}
                                    </p>
                                    <table role="presentation" style="width: 100%; font-size: 12px; color: #6b7280;">
                                        <tr>
                                            <td style="padding: 2px 0; width: 50%;">
                                                <span style="color: #9ca3af;">📅</span> {{ $event->dateRangeLabel() }}
                                            </td>
                                            @if($event->totalParticipants())
                                            <td style="padding: 2px 0; width: 50%;">
                                                <span style="color: #9ca3af;">👥</span> {{ $event->totalParticipants() }} participants
                                            </td>
                                            @endif
                                        </tr>
                                        @if($event->venue)
                                        <tr>
                                            <td colspan="2" style="padding: 2px 0;">
                                                <span style="color: #9ca3af;">📍</span> {{ $event->venue }}
                                            </td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                    @endforeach

                    <!-- PDF Attachment Notice -->
                    <tr>
                        <td style="padding: 0 30px 25px 30px;">
                            <table role="presentation" style="width: 100%; background-color: #fef3c7; border: 2px dashed #f59e0b; border-radius: 8px; padding: 15px;">
                                <tr>
                                    <td style="text-align: center;">
                                        <p style="margin: 0; color: #92400e; font-size: 14px;">
                                            <strong>📎 Complete Schedule Attached</strong>
                                        </p>
                                        <p style="margin: 5px 0 0 0; color: #b45309; font-size: 12px;">
                                            Please find the full schedule document attached to this email
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- CTA Button -->
                    <tr>
                        <td style="padding: 0 30px 30px 30px; text-align: center;">
                            <a href="{{ $actionUrl }}" style="display: inline-block; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 6px; font-weight: bold; font-size: 14px; box-shadow: 0 4px 6px rgba(59, 130, 246, 0.3);">
                                🔗 View Online Dashboard
                            </a>
                            <p style="margin: 15px 0 0 0; color: #6b7280; font-size: 12px;">
                                For questions, contact your department head
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9fafb; padding: 25px 30px; border-top: 1px solid #e5e7eb;">
                            <table role="presentation" style="width: 100%;">
                                <tr>
                                    <td style="text-align: center;">
                                        <p style="margin: 0 0 5px 0; color: #4b5563; font-size: 13px; font-weight: 600;">
                                            Best regards,
                                        </p>
                                        <p style="margin: 0 0 15px 0; color: #1f2937; font-size: 14px; font-weight: bold;">
                                            {{ $sentBy->name }}
                                        </p>
                                        <div style="height: 1px; background-color: #e5e7eb; margin: 15px 0;"></div>
                                        <p style="margin: 0; color: #6b7280; font-size: 11px;">
                                            Kenya School of Government<br>
                                            Event Readiness Dashboard<br>
                                            <a href="{{ config('app.url') }}" style="color: #3b82f6; text-decoration: none;">{{ config('app.url') }}</a>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>
                
                <!-- Email Footer -->
                <table role="presentation" style="width: 600px; max-width: 100%; margin-top: 20px;">
                    <tr>
                        <td style="text-align: center; padding: 15px; color: #9ca3af; font-size: 11px;">
                            <p style="margin: 0;">
                                This is an automated message from the KSG Event Readiness Dashboard.<br>
                                © {{ date('Y') }} Kenya School of Government. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>
</body>
</html>
