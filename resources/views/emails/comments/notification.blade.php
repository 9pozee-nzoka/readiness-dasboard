<x-mail::message>
<div style="text-align: center; margin-bottom: 30px;">
<div style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 15px 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);">
<h1 style="color: white; margin: 0; font-size: 24px; font-weight: 600;">
{{ match($notificationType) {
    'new_comment' => '💬 New Comment',
    'comment_reply' => '↩️ Reply to Your Comment',
    'hod_notification' => '📢 Department Comment',
    default => '💬 New Comment'
} }}
</h1>
</div>
</div>

<div style="background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%); padding: 20px; border-radius: 12px; border-left: 4px solid #667eea; margin-bottom: 25px;">
<p style="margin: 0 0 10px 0; font-size: 15px; color: #374151;">
Hello <strong style="color: #667eea;">{{ $recipient->name }}</strong>,
</p>
<p style="margin: 0; font-size: 15px; color: #4b5563;">
<strong style="color: #1f2937;">{{ $comment->user->name }}</strong> 
{{ match($notificationType) {
    'new_comment' => 'commented on a requirement assigned to you',
    'comment_reply' => 'replied to your comment',
    'hod_notification' => 'posted a comment in your department',
    default => 'posted a comment'
} }}
</p>
</div>

@if($requirement)
<div style="background: white; border: 2px solid #e5e7eb; border-radius: 12px; padding: 20px; margin-bottom: 25px;">
<h2 style="margin: 0 0 15px 0; color: #1f2937; font-size: 18px; border-bottom: 2px solid #667eea; padding-bottom: 10px;">
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
<td style="padding: 8px 0; color: #1f2937;">{{ $requirement->deadline ? \Carbon\Carbon::parse($requirement->deadline)->format('F j, Y') : 'Not set' }}</td>
</tr>
</table>
</div>
@else
<div style="background: white; border: 2px solid #e5e7eb; border-radius: 12px; padding: 20px; margin-bottom: 25px;">
<h2 style="margin: 0 0 15px 0; color: #1f2937; font-size: 18px;">📅 Event Details</h2>
<p style="margin: 5px 0; color: #4b5563;"><strong>Event:</strong> {{ $event->name }}</p>
<p style="margin: 5px 0; color: #4b5563;"><strong>Date:</strong> {{ $event->event_date->format('F j, Y') }}</p>
</div>
@endif

<div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-left: 4px solid #f59e0b; border-radius: 12px; padding: 20px; margin-bottom: 25px;">
<h3 style="margin: 0 0 12px 0; color: #92400e; font-size: 16px; display: flex; align-items: center;">
💬 Comment
</h3>
<div style="background: white; padding: 15px; border-radius: 8px; color: #1f2937; line-height: 1.6;">
{{ $comment->message }}
</div>
<div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #fbbf2480; font-size: 13px; color: #92400e;">
<strong>{{ $comment->user->name }}</strong> • {{ $comment->created_at->diffForHumans() }}
</div>
</div>

<div style="text-align: center; margin: 35px 0;">
<x-mail::button :url="$actionUrl" color="primary">
View Full Discussion & Reply
</x-mail::button>
</div>

<div style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); border-left: 4px solid #3b82f6; border-radius: 12px; padding: 18px; margin-top: 30px;">
<p style="margin: 0; font-size: 14px; color: #1e40af; line-height: 1.6;">
<strong style="font-size: 16px;">💡 Quick Tip:</strong><br>
You can reply directly to this email to respond to <strong>{{ $comment->user->name }}</strong> ({{ $comment->user->email }}).
</p>
</div>

<div style="margin-top: 35px; padding-top: 25px; border-top: 2px solid #e5e7eb; text-align: center;">
<p style="margin: 0; color: #6b7280; font-size: 14px;">Best regards,</p>
<p style="margin: 5px 0 0 0; color: #1f2937; font-weight: 600; font-size: 15px;">{{ config('app.name') }} Team</p>
</div>

</x-mail::message>
