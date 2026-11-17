<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Booking Update</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="background-color: #6B7280; color: white; padding: 20px; text-align: center;">
            <h1 style="margin: 0;">Booking Update</h1>
        </div>
        
        <div style="background-color: #f9fafb; padding: 20px; margin-top: 20px;">
            <p>Dear {{ $booking->contact_person_name }},</p>
            
            <p>Thank you for your interest in hosting your event at Kenya School of Government.</p>
            
            <p>Unfortunately, we are unable to accommodate your booking request at this time.</p>
            
            <div style="background-color: white; padding: 15px; border-left: 4px solid #6B7280; margin: 20px 0;">
                <p style="margin-top: 0;"><strong>Booking Reference:</strong></p>
                <p style="font-size: 20px; font-weight: bold; color: #6B7280; margin: 0;">{{ $booking->booking_reference }}</p>
            </div>
            
            @if($booking->rejection_reason)
                <div style="background-color: #FEF2F2; border: 1px solid #FEE2E2; padding: 15px; border-radius: 4px; margin: 20px 0;">
                    <p style="margin: 0;"><strong>Reason:</strong></p>
                    <p style="margin: 10px 0 0 0;">{{ $booking->rejection_reason }}</p>
                </div>
            @endif
            
            <p>We understand this may be disappointing. Here are some options:</p>
            <ul style="padding-left: 20px;">
                <li>Consider alternative dates that may be available</li>
                <li>Contact us to discuss other venue options</li>
                <li>Submit a new booking request with different specifications</li>
            </ul>
            
            <p>Our team is happy to work with you to find a solution. Please don't hesitate to reach out:</p>
            <p style="margin: 5px 0;">
                <strong>Email:</strong> events@ksg.ac.ke<br>
                <strong>Phone:</strong> +254 20 2717260
            </p>
            
            <p>Thank you for considering Kenya School of Government.</p>
            
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
