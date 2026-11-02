<x-mail::message>
<div style="text-align: center; margin-bottom: 30px;">
<div style="display: inline-block; background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%); padding: 15px 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(124, 58, 237, 0.3);">
<h1 style="color: white; margin: 0; font-size: 24px; font-weight: 600;">
📅 Weekly Schedule Shared
</h1>
</div>
</div>

<div style="background: linear-gradient(135deg, #7c3aed15 0%, #5b21b615 100%); padding: 20px; border-radius: 12px; border-left: 4px solid #7c3aed; margin-bottom: 25px;">
<p style="margin: 0 0 10px 0; font-size: 15px; color: #374151;">
Hello <strong style="color: #5b21b6;">{{ $recipient->name }}</strong>,
</p>
<p style="margin: 0 0 10px 0; font-size: 15px; color: #4b5563;">
<strong style="color: #1f2937;">{{ $sharedBy->name }}</strong> has shared the weekly schedule for <strong style="color: #5b21b6;">{{ $planningWeek->label }}</strong> with <strong>{{ $departmentName }}</strong>.
</p>
<p style="margin: 0; font-size: 14px; color: #6b7280;">
📎 <strong>A detailed PDF schedule is attached to this email.</strong>
</p>
</div>

<div style="background: white; border: 2px solid #e5e7eb; border-radius: 12px; padding: 20px; margin-bottom: 25px;">
<h2 style="margin: 0 0 15px 0; color: #1f2937; font-size: 18px; border-bottom: 2px solid #7c3aed; padding-bottom: 10px;">
📋 Events This Week
</h2>

@if($events->count() > 0)
<table style="width: 100%; border-collapse: collapse;">
<thead>
<tr style="background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%);">
<th style="padding: 10px; text-align: left; color: #5b21b6; font-weight: 600; border-bottom: 2px solid #7c3aed;">Event</th>
<th style="padding: 10px; text-align: left; color: #5b21b6; font-weight: 600; border-bottom: 2px solid #7c3aed;">Date</th>
<th style="padding: 10px; text-align: left; color: #5b21b6; font-weight: 600; border-bottom: 2px solid #7c3aed;">Type</th>
</tr>
</thead>
<tbody>
@foreach($events as $event)
<tr style="{{ $loop->even ? 'background-color: #f9fafb;' : 'background-color: white;' }}">
<td style="padding: 10px; color: #1f2937; border-bottom: 1px solid #e5e7eb;">{{ $event->name }}</td>
<td style="padding: 10px; color: #4b5563; border-bottom: 1px solid #e5e7eb;">{{ $event->event_date->format('D, M j') }}{{ $event->event_time ? ' at '.\Carbon\Carbon::parse($event->event_time)->format('H:i') : '' }}</td>
<td style="padding: 10px; border-bottom: 1px solid #e5e7eb;">
<span style="display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; background: #dbeafe; color: #1e40af;">
{{ $event->type }}
</span>
</td>
</tr>
@endforeach
</tbody>
</table>

<div style="margin-top: 15px; padding: 12px; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-radius: 8px; border-left: 4px solid #22c55e;">
<p style="margin: 0; color: #166534; font-weight: 600; font-size: 14px;">
📊 Total Events: <span style="font-size: 18px; color: #15803d;">{{ $events->count() }}</span>
</p>
</div>
@else
<p style="margin: 0; padding: 20px; text-align: center; color: #6b7280; font-style: italic; background: #f9fafb; border-radius: 8px;">
No events scheduled for this week yet.
</p>
@endif
</div>

<div style="text-align: center; margin: 35px 0;">
<x-mail::button :url="$actionUrl" color="primary">
View Full Schedule Online
</x-mail::button>
</div>

<div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-left: 4px solid #f59e0b; border-radius: 12px; padding: 18px; margin-top: 30px;">
<p style="margin: 0; font-size: 14px; color: #92400e; line-height: 1.6;">
<strong style="font-size: 16px;">💡 Important:</strong><br>
Please review the attached PDF schedule and ensure your department is prepared for all upcoming events. Check requirements and deadlines on the dashboard.
</p>
</div>

<div style="margin-top: 35px; padding-top: 25px; border-top: 2px solid #e5e7eb; text-align: center;">
<p style="margin: 0; color: #6b7280; font-size: 14px;">Best regards,</p>
<p style="margin: 5px 0 0 0; color: #1f2937; font-weight: 600; font-size: 15px;">{{ config('app.name') }} Team</p>
</div>

</x-mail::message>
