<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Booking Confirmation</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="background-color: #4F46E5; color: white; padding: 20px; text-align: center;">
            <h1 style="margin: 0;">Booking Confirmation</h1>
        </div>
        
        <div style="background-color: #f9fafb; padding: 20px; margin-top: 20px;">
            <p>Dear {{ $booking->contact_person_name }},</p>
            
            <p>Thank you for choosing Kenya School of Government for your event. We have received your booking request.</p>
            
            <div style="background-color: white; padding: 15px; border-left: 4px solid #4F46E5; margin: 20px 0;">
                <h2 style="margin-top: 0;">Booking Reference</h2>
                <p style="font-size: 24px; font-weight: bold; color: #4F46E5; margin: 0;">{{ $booking->booking_reference }}</p>
                <p style="margin-bottom: 0; font-size: 14px; color: #666;">Please save this reference number for tracking your booking.</p>
            </div>
            
            <h3>Event Details</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;"><strong>Event Title:</strong></td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">{{ $booking->event_title }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;"><strong>Organization:</strong></td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">{{ $booking->organization_name }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;"><strong>Event Dates:</strong></td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">
                        {{ $booking->preferred_start_date->format('M d, Y') }} - {{ $booking->preferred_end_date->format('M d, Y') }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;"><strong>Participants:</strong></td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">{{ $booking->expected_participants }} people</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>Status:</strong></td>
                    <td style="padding: 8px 0;"><span style="background-color: #FEF3C7; color: #92400E; padding: 4px 12px; border-radius: 12px; font-size: 14px;">Pending Review</span></td>
                </tr>
            </table>
            
            <h3 style="margin-top: 30px;">What Happens Next?</h3>
            <ol style="padding-left: 20px;">
                <li>Our events team will review your booking request</li>
                <li>We'll check venue and resource availability for your selected dates</li>
                <li>You'll receive a detailed quotation within 2-3 business days</li>
                <li>Once you approve the quotation, we'll confirm your booking</li>
            </ol>
            
            <div style="background-color: #EFF6FF; border: 1px solid #DBEAFE; padding: 15px; border-radius: 4px; margin: 20px 0;">
                <p style="margin: 0;"><strong>Track Your Booking:</strong></p>
                <p style="margin: 10px 0 0 0;">Visit our website and enter your booking reference and email to check the status of your request.</p>
            </div>
            
            <p>If you have any questions, please don't hesitate to contact us:</p>
            <p style="margin: 5px 0;">
                <strong>Email:</strong> events@ksg.ac.ke<br>
                <strong>Phone:</strong> +254 20 2717260
            </p>
            
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
