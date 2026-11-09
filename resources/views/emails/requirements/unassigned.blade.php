<x-mail::message>
<div style="text-align: center; margin-bottom: 30px;">
<div style="display: inline-block; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); padding: 15px 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);">
<h1 style="color: white; margin: 0; font-size: 24px; font-weight: 600;">
🔄 Requirement Unassigned
</h1>
</div>
</div>

<div style="background: linear-gradient(135deg, #f59e0b15 0%, #d9770615 100%); padding: 20px; border-radius: 12px; border-left: 4px solid #f59e0b; margin-bottom: 25px;">
<p style="margin: 0 0 10px 0; font-size: 15px; color: #374151;">
Hello <strong style="color: #d97706;">{{ $previousAssignee->name }}</strong>,
</p>
<p style="margin: 0; font-size: 15px; color: #4b5563;">
<strong style="color: #1f2937;">{{ $reassignedBy->name }}</strong> has unassigned you from the following requirement. It has been reassigned to another staff member.
</p>
</div>

<div style="background: white; border: 2px solid #e5e7eb; border-radius: 12px; padding: 20px; margin-bottom: 25px;">
<h2 style="margin: 0 0 15px 0; color: #1f2937; font-size: 18px; border-bottom: 2px solid #f59e0b; padding-bottom: 10px;">
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
{{ $requirement->priority->value === 'critical' ? 'background: #fef2f2; color: #991b1b;' : ($requirement->priority->value === 'high' ? 'background: #fee2e2; color: #991b1b;' : ($requirement->priority->value === 'medium' ? 'background: #fef3c7; color: #92400e;' : 'background: #dbeafe; color: #1e40af;')) }}">
{{ $requirement->priority->label() }}
</span>
</td>
</tr>
@if($requirement->deadline)
<tr>
<td style="padding: 8px 0; color: #6b7280; font-weight: 600;">Deadline</td>
<td style="padding: 8px 0; color: #1f2937;">{{ \Carbon\Carbon::parse($requirement->deadline)->format('D, d M Y') }}</td>
</tr>
@endif
</table>
</div>

<div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 15px; margin-bottom: 25px;">
<p style="margin: 0; font-size: 14px; color: #78350f; line-height: 1.6;">
ℹ️ <strong>Note:</strong> You are no longer responsible for this requirement. If you have any questions, please contact {{ $reassignedBy->name }}.
</p>
</div>

<x-mail::button :url="$actionUrl" color="warning">
View Event Details
</x-mail::button>

<p style="margin-top: 25px; color: #6b7280; font-size: 14px; line-height: 1.6;">
If you have any concerns about this reassignment, please reach out to <strong>{{ $reassignedBy->name }}</strong> or your department head.
</p>

<hr style="border: none; border-top: 1px solid #e5e7eb; margin: 25px 0;">

<p style="color: #9ca3af; font-size: 13px; line-height: 1.5; margin: 0;">
This is an automated notification from the <strong>KSG Readiness Dashboard</strong>.<br>
You received this email because you were previously assigned to this requirement.
</p>

</x-mail::message>
