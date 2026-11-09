<x-mail::message>
<div style="text-align: center; margin-bottom: 30px;">
<div style="display: inline-block; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); padding: 15px 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);">
<h1 style="color: white; margin: 0; font-size: 24px; font-weight: 600;">
📅 New Event Created
</h1>
</div>
</div>

<div style="background: linear-gradient(135deg, #6366f115 0%, #4f46e515 100%); padding: 20px; border-radius: 12px; border-left: 4px solid #6366f1; margin-bottom: 25px;">
<p style="margin: 0 0 10px 0; font-size: 15px; color: #374151;">
Hello,
</p>
<p style="margin: 0; font-size: 15px; color: #4b5563;">
<strong style="color: #1f2937;">{{ $creator->name }}</strong> has created a new event that requires your department's attention.
</p>
</div>

<div style="background: white; border: 2px solid #e5e7eb; border-radius: 12px; padding: 20px; margin-bottom: 25px;">
<h2 style="margin: 0 0 15px 0; color: #1f2937; font-size: 18px; border-bottom: 2px solid #6366f1; padding-bottom: 10px;">
📋 Event Details
</h2>
<table style="width: 100%; border-collapse: collapse;">
<tr>
<td style="padding: 8px 0; color: #6b7280; font-weight: 600; width: 30%;">Event Name</td>
<td style="padding: 8px 0; color: #1f2937; font-weight: 600; font-size: 16px;">{{ $event->name }}</td>
</tr>
<tr>
<td style="padding: 8px 0; color: #6b7280; font-weight: 600;">Type</td>
<td style="padding: 8px 0;">
<span style="display: inline-block; padding: 4px 12px; border-radius: 6px; font-size: 13px; font-weight: 600; background: #e0e7ff; color: #3730a3;">
{{ $event->type }}
</span>
</td>
</tr>
<tr>
<td style="padding: 8px 0; color: #6b7280; font-weight: 600;">Date</td>
<td style="padding: 8px 0; color: #1f2937;">{{ $event->event_date->format('l, F j, Y') }}</td>
</tr>
@if($event->event_time)
<tr>
<td style="padding: 8px 0; color: #6b7280; font-weight: 600;">Time</td>
<td style="padding: 8px 0; color: #1f2937;">{{ \Carbon\Carbon::parse($event->event_time)->format('h:i A') }}</td>
</tr>
@endif
@if($event->venue)
<tr>
<td style="padding: 8px 0; color: #6b7280; font-weight: 600;">Venue</td>
<td style="padding: 8px 0; color: #1f2937;">{{ $event->venue }}</td>
</tr>
@endif
@if($event->description)
<tr>
<td style="padding: 8px 0; color: #6b7280; font-weight: 600; vertical-align: top;">Description</td>
<td style="padding: 8px 0; color: #4b5563; line-height: 1.5;">{{ $event->description }}</td>
</tr>
@endif
<tr>
<td style="padding: 8px 0; color: #6b7280; font-weight: 600;">Planning Week</td>
<td style="padding: 8px 0; color: #1f2937;">{{ $event->planningWeek->label }}</td>
</tr>
</table>
</div>

<div style="text-align: center; margin: 35px 0;">
<x-mail::button :url="$eventUrl" color="primary">
View Event & Requirements
</x-mail::button>
</div>

<div style="background: #fef3c7; border: 2px solid #fbbf24; border-radius: 12px; padding: 20px; margin: 25px 0;">
<h3 style="margin: 0 0 10px 0; color: #92400e; font-size: 16px;">
⚡ Action Required
</h3>
<p style="margin: 0 0 10px 0; font-size: 14px; color: #78350f; line-height: 1.6;">
As an HOD, you can now:
</p>
<ul style="margin: 10px 0; padding-left: 20px; color: #78350f; font-size: 14px; line-height: 1.8;">
<li><strong>View event requirements</strong> assigned to your department</li>
<li><strong>Add additional requirements</strong> specific to your department's needs</li>
<li><strong>Track progress</strong> of all requirements in real-time</li>
<li><strong>Assign tasks</strong> to your team members</li>
</ul>
</div>

<div style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); border-left: 4px solid #3b82f6; border-radius: 12px; padding: 18px; margin-top: 30px;">
<p style="margin: 0; font-size: 14px; color: #1e40af; line-height: 1.6;">
<strong style="font-size: 16px;">💡 Quick Tip:</strong><br>
Click "View Event & Requirements" above to see all requirements and add any department-specific items needed for this event.
</p>
</div>

<div style="margin-top: 35px; padding-top: 25px; border-top: 2px solid #e5e7eb; text-align: center;">
<p style="margin: 0; color: #6b7280; font-size: 14px;">Best regards,</p>
<p style="margin: 5px 0 0 0; color: #1f2937; font-weight: 600; font-size: 15px;">{{ config('app.name') }} Team</p>
</div>

</x-mail::message>
