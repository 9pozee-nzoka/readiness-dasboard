<x-mail::message>
<div style="text-align: center; margin-bottom: 30px;">
<div style="display: inline-block; background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 15px 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);">
<h1 style="color: white; margin: 0; font-size: 24px; font-weight: 600;">
📋 New Requirement Assigned
</h1>
</div>
</div>

<div style="background: linear-gradient(135deg, #10b98115 0%, #05966915 100%); padding: 20px; border-radius: 12px; border-left: 4px solid #10b981; margin-bottom: 25px;">
<p style="margin: 0 0 10px 0; font-size: 15px; color: #374151;">
Hello <strong style="color: #059669;">{{ $assignee->name }}</strong>,
</p>
<p style="margin: 0; font-size: 15px; color: #4b5563;">
<strong style="color: #1f2937;">{{ $assignedBy->name }}</strong> has assigned a new requirement to you.
</p>
</div>

<div style="background: white; border: 2px solid #e5e7eb; border-radius: 12px; padding: 20px; margin-bottom: 25px;">
<h2 style="margin: 0 0 15px 0; color: #1f2937; font-size: 18px; border-bottom: 2px solid #10b981; padding-bottom: 10px;">
📋 Requirement Details
</h2>
<table style="width: 100%; border-collapse: collapse;">
<tr>
<td style="padding: 8px 0; color: #6b7280; font-weight: 600; width: 30%;">Requirement</td>
<td style="padding: 8px 0; color: #1f2937;">{{ $requirement->description }}</td>
</tr>
<tr>
<td style="padding: 8px 0; color: #6b7280; font-weight: 600;">Event</td>
<td style="padding: 8px 0; color: #1f2937;">{{ $event->name }}</td>
</tr>
<tr>
<td style="padding: 8px 0; color: #6b7280; font-weight: 600;">Department</td>
<td style="padding: 8px 0; color: #1f2937;">{{ $requirement->department->name }}</td>
</tr>
<tr>
<td style="padding: 8px 0; color: #6b7280; font-weight: 600;">Priority</td>
<td style="padding: 8px 0;">
<span style="display: inline-block; padding: 4px 12px; border-radius: 6px; font-size: 13px; font-weight: 600; 
{{ $requirement->priority->value === 'high' ? 'background: #fee2e2; color: #991b1b;' : ($requirement->priority->value === 'medium' ? 'background: #fef3c7; color: #92400e;' : 'background: #dbeafe; color: #1e40af;') }}">
{{ ucfirst($requirement->priority->value) }}
</span>
</td>
</tr>
<tr>
<td style="padding: 8px 0; color: #6b7280; font-weight: 600;">Status</td>
<td style="padding: 8px 0;">
<span style="display: inline-block; padding: 4px 12px; border-radius: 6px; font-size: 13px; font-weight: 600; background: #e0e7ff; color: #3730a3;">
{{ $requirement->status->label() }}
</span>
</td>
</tr>
<tr>
<td style="padding: 8px 0; color: #6b7280; font-weight: 600;">Deadline</td>
<td style="padding: 8px 0; color: #1f2937; {{ $requirement->deadline && $requirement->deadline->isPast() ? 'font-weight: 700; color: #dc2626;' : '' }}">
{{ $requirement->deadline ? \Carbon\Carbon::parse($requirement->deadline)->format('F j, Y') : 'Not set' }}
</td>
</tr>
</table>
</div>

<div style="text-align: center; margin: 35px 0;">
<x-mail::button :url="$actionUrl" color="success">
View Requirement Details
</x-mail::button>
</div>

<div style="background: #f9fafb; border: 2px solid #e5e7eb; border-radius: 12px; padding: 20px; margin: 25px 0;">
<h3 style="margin: 0 0 15px 0; color: #1f2937; font-size: 16px; text-align: center;">
⚡ Quick Actions
</h3>
<p style="margin: 0 0 15px 0; text-align: center; font-size: 13px; color: #6b7280;">
Take action on this requirement directly from your email
</p>

<table style="width: 100%; border-collapse: collapse;">
<tr>
<td style="padding: 5px; width: 33.33%; text-align: center;">
<a href="{{ $acknowledgeUrl }}" style="display: inline-block; width: 100%; padding: 10px 12px; background: #10b981; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 13px;">
✓ Acknowledge
</a>
</td>
<td style="padding: 5px; width: 33.33%; text-align: center;">
<a href="{{ $completeUrl }}" style="display: inline-block; width: 100%; padding: 10px 12px; background: #3b82f6; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 13px;">
✓ Mark Complete
</a>
</td>
<td style="padding: 5px; width: 33.33%; text-align: center;">
<a href="{{ $escalateUrl }}" style="display: inline-block; width: 100%; padding: 10px 12px; background: #f59e0b; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 13px;">
⚠ Need Help
</a>
</td>
</tr>
</table>

<p style="margin: 15px 0 0 0; text-align: center; font-size: 11px; color: #9ca3af;">
These links are secure and unique to you. They expire after use.
</p>
</div>

<div style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); border-left: 4px solid #3b82f6; border-radius: 12px; padding: 18px; margin-top: 30px;">
<p style="margin: 0; font-size: 14px; color: #1e40af; line-height: 1.6;">
<strong style="font-size: 16px;">💡 Quick Tip:</strong><br>
You can reply directly to this email to respond to <strong>{{ $assignedBy->name }}</strong> ({{ $assignedBy->email }}).
</p>
</div>

<div style="margin-top: 35px; padding-top: 25px; border-top: 2px solid #e5e7eb; text-align: center;">
<p style="margin: 0; color: #6b7280; font-size: 14px;">Best regards,</p>
<p style="margin: 5px 0 0 0; color: #1f2937; font-weight: 600; font-size: 15px;">{{ config('app.name') }} Team</p>
</div>

</x-mail::message>
