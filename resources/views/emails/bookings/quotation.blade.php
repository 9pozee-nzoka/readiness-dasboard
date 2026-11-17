<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Event Quotation</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="background-color: #7C3AED; color: white; padding: 20px; text-align: center;">
            <h1 style="margin: 0;">Event Quotation</h1>
        </div>
        
        <div style="background-color: #f9fafb; padding: 20px; margin-top: 20px;">
            <p>Dear {{ $booking->contact_person_name }},</p>
            
            <p>Thank you for your booking request. Please find below the quotation for your event.</p>
            
            <div style="background-color: white; padding: 15px; border-left: 4px solid #7C3AED; margin: 20px 0;">
                <p style="margin-top: 0;"><strong>Booking Reference:</strong></p>
                <p style="font-size: 20px; font-weight: bold; color: #7C3AED; margin: 0;">{{ $booking->booking_reference }}</p>
            </div>
            
            <h3>Event Details</h3>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
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
                    <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;"><strong>Participants:</strong></td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">{{ $booking->expected_participants }} people</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>Venue:</strong></td>
                    <td style="padding: 8px 0;">{{ $booking->preferred_venue }}</td>
                </tr>
            </table>
            
            <h3>Cost Breakdown</h3>
            <div style="background-color: white; padding: 15px; border: 1px solid #e5e7eb; border-radius: 4px; margin: 20px 0;">
                @if($booking->quotation_breakdown)
                    <div style="white-space: pre-line;">{{ $booking->quotation_breakdown }}</div>
                @endif
                
                <div style="border-top: 2px solid #7C3AED; margin-top: 20px; padding-top: 15px;">
                    <table style="width: 100%;">
                        @if($booking->deposit_amount)
                            <tr>
                                <td style="padding: 5px 0;"><strong>Deposit Required:</strong></td>
                                <td style="text-align: right; padding: 5px 0;"><strong style="color: #7C3AED;">KES {{ number_format($booking->deposit_amount, 2) }}</strong></td>
                            </tr>
                        @endif
                        <tr style="font-size: 18px;">
                            <td style="padding: 10px 0;"><strong>Total Estimated Cost:</strong></td>
                            <td style="text-align: right; padding: 10px 0;"><strong style="color: #7C3AED;">KES {{ number_format($booking->estimated_cost, 2) }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div style="background-color: #FFFBEB; border: 1px solid #FDE68A; padding: 15px; border-radius: 4px; margin: 20px 0;">
                <p style="margin: 0;"><strong>Note:</strong> This quotation is valid for 14 days. Prices are subject to change based on final requirements and availability.</p>
            </div>
            
            <h3>Next Steps</h3>
            <ol style="padding-left: 20px;">
                <li>Review the quotation and let us know if you have any questions</li>
                <li>To proceed, please confirm your booking and make the deposit payment</li>
                <li>We'll send payment instructions separately</li>
                <li>Once payment is received, we'll issue your booking confirmation</li>
            </ol>
            
            <p>We look forward to hosting your event at Kenya School of Government!</p>
            
            <p>For any questions or clarifications:<br>
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
