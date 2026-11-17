<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Booking Approved</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="background-color: #10B981; color: white; padding: 20px; text-align: center;">
            <h1 style="margin: 0;">🎉 Booking Approved!</h1>
        </div>
        
        <div style="background-color: #f9fafb; padding: 20px; margin-top: 20px;">
            <p>Dear {{ $booking->contact_person_name }},</p>
            
            <p>Great news! Your booking request has been approved.</p>
            
            <div style="background-color: white; padding: 15px; border-left: 4px solid #10B981; margin: 20px 0;">
                <p style="margin-top: 0;"><strong>Booking Reference:</strong></p>
                <p style="font-size: 20px; font-weight: bold; color: #10B981; margin: 0;">{{ $booking->booking_reference }}</p>
            </div>
            
            <h3>Event Details</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;"><strong>Event:</strong></td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">{{ $booking->event_title }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;"><strong>Dates:</strong></td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">
                        {{ $booking->preferred_start_date->format('M d, Y') }} - {{ $booking->preferred_end_date->format('M d, Y') }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>Venue:</strong></td>
                    <td style="padding: 8px 0;">{{ $booking->allocated_venue ?? $booking->preferred_venue }}</td>
                </tr>
            </table>
            
            @if($booking->review_notes)
                <div style="background-color: #EFF6FF; border: 1px solid #DBEAFE; padding: 15px; border-radius: 4px; margin: 20px 0;">
                    <p style="margin: 0;"><strong>Notes from our team:</strong></p>
                    <p style="margin: 10px 0 0 0;">{{ $booking->review_notes }}</p>
                </div>
            @endif
            
            <h3>Next Steps</h3>
            <ol style="padding-left: 20px;">
                <li>Review the quotation we've sent separately</li>
                <li>Make the required deposit payment</li>
                <li>We'll send final confirmation once payment is received</li>
            </ol>
            
            <p>Our team will be in touch shortly to finalize the arrangements.</p>
            
            <p>Contact us if you have any questions:<br>
            <strong>Email:</strong> events@ksg.ac.ke<br>
            <strong>Phone:</strong> +254 20 2717260</p>
            
            <p>Best regards,<br>
            <strong>Events Team</strong><br>
            Kenya School of Government</p>
        </div>
        
        <div style="text-align: center; padding: 20px; color: #666; font-size: 12px;">
            <p>&copy; {{ date('Y') }} Kenya School of Government. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
