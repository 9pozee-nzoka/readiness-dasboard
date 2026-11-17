<?php

namespace App\Http\Controllers;

use App\Mail\BookingConfirmation;
use App\Models\EventBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class BookingController extends Controller
{
    public function landing()
    {
        return view('bookings.landing');
    }

    public function create()
    {
        // Multi-step form - Step 1: Client info
        return view('bookings.create', [
            'step' => 1,
            'organizationCategories' => $this->getOrganizationCategories(),
            'countries' => $this->getCountries(),
        ]);
    }

    public function storeStep(Request $request)
    {
        $step = $request->input('step', 1);

        // Store step data in session
        $sessionKey = "booking_step_{$step}";
        session([$sessionKey => $request->except(['_token', 'step'])]);

        $nextStep = $step + 1;

        // If final step, create booking
        if ($nextStep > 6) {
            return $this->finalizeBooking($request);
        }

        return redirect()->route('bookings.step', ['step' => $nextStep]);
    }

    public function showStep(Request $request, int $step)
    {
        if ($step < 1 || $step > 6) {
            return redirect()->route('bookings.create');
        }

        $data = [
            'step' => $step,
            'organizationCategories' => $this->getOrganizationCategories(),
            'countries' => $this->getCountries(),
            'eventCategories' => $this->getEventCategories(),
            'eventTypes' => $this->getEventTypes(),
            'seatingArrangements' => $this->getSeatingArrangements(),
            'cateringPackages' => $this->getCateringPackages(),
        ];

        return view('bookings.create', $data);
    }

    protected function finalizeBooking(Request $request)
    {
        try {
            DB::beginTransaction();

            // Gather all session data
            $bookingData = [];
            for ($i = 1; $i <= 6; $i++) {
                $stepData = session("booking_step_{$i}", []);
                $bookingData = array_merge($bookingData, $stepData);
            }

            // Handle equipment requirements (convert to JSON)
            if (isset($bookingData['equipment'])) {
                $bookingData['equipment_requirements'] = $bookingData['equipment'];
                unset($bookingData['equipment']);
            }

            // Handle catering details (convert to JSON)
            if (isset($bookingData['catering'])) {
                $bookingData['catering_details'] = $bookingData['catering'];
                unset($bookingData['catering']);
            }

            // Link to authenticated user if logged in
            if (auth()->check()) {
                $bookingData['client_user_id'] = auth()->id();
            }

            // Set defaults for removed optional fields
            $bookingData['event_description'] = $bookingData['event_description'] ?? 'To be discussed';
            $bookingData['event_purpose'] = $bookingData['event_purpose'] ?? 'To be discussed';
            $bookingData['event_type'] = $bookingData['event_type'] ?? 'general';
            $bookingData['event_visibility'] = $bookingData['event_visibility'] ?? 'invitation_only';
            $bookingData['is_residential'] = $bookingData['is_residential'] ?? false;
            $bookingData['vip_attendance'] = $bookingData['vip_attendance'] ?? false;
            $bookingData['is_recurring'] = $bookingData['is_recurring'] ?? false;

            // Generate booking reference
            $bookingData['booking_reference'] = EventBooking::generateBookingReference();
            $bookingData['status'] = 'pending';

            // Create booking
            $booking = EventBooking::create($bookingData);

            // Clear session data
            for ($i = 1; $i <= 6; $i++) {
                session()->forget("booking_step_{$i}");
            }

            // Send confirmation email
            if ($booking->contact_email) {
                Mail::to($booking->contact_email)->send(new BookingConfirmation($booking));
            }

            DB::commit();

            return redirect()->route('bookings.confirmation', $booking)
                ->with('success', 'Your booking request has been submitted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Booking submission failed: '.$e->getMessage(), [
                'exception' => $e,
                'booking_data' => $bookingData ?? [],
            ]);

            return back()->with('error', 'Failed to submit booking: '.$e->getMessage());
        }
    }

    public function confirmation(EventBooking $booking)
    {
        return view('bookings.confirmation', compact('booking'));
    }

    public function track(Request $request)
    {
        $request->validate([
            'booking_reference' => 'required|string',
            'email' => 'required|email',
        ]);

        $booking = EventBooking::where('booking_reference', $request->booking_reference)
            ->where('contact_email', $request->email)
            ->first();

        if (! $booking) {
            return back()->with('error', 'Booking not found. Please check your details.');
        }

        return view('bookings.status', compact('booking'));
    }

    public function myBookings()
    {
        if (! auth()->check() || ! auth()->user()->isClient()) {
            return redirect()->route('bookings.landing');
        }

        $bookings = EventBooking::where('client_user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('bookings.my-bookings', compact('bookings'));
    }

    // Helper methods for dropdown options
    protected function getOrganizationCategories(): array
    {
        return [
            'government' => 'Government Institution',
            'county' => 'County Government',
            'state_corporation' => 'State Corporation',
            'private' => 'Private Company',
            'ngo' => 'NGO / Non-profit',
            'educational' => 'Educational Institution',
            'international' => 'International Organization',
            'individual' => 'Individual',
            'other' => 'Other',
        ];
    }

    protected function getCountries(): array
    {
        return [
            'Kenya' => 'Kenya',
            'Uganda' => 'Uganda',
            'Tanzania' => 'Tanzania',
            'Rwanda' => 'Rwanda',
            'Burundi' => 'Burundi',
            'South Sudan' => 'South Sudan',
            'Ethiopia' => 'Ethiopia',
            'Somalia' => 'Somalia',
            'Other' => 'Other',
        ];
    }

    protected function getEventCategories(): array
    {
        return [
            'conference' => 'Conference',
            'workshop' => 'Workshop',
            'seminar' => 'Seminar',
            'training' => 'Training',
            'retreat' => 'Retreat',
            'meeting' => 'Meeting',
            'symposium' => 'Symposium',
            'exhibition' => 'Exhibition',
            'other' => 'Other',
        ];
    }

    protected function getEventTypes(): array
    {
        return [
            'academic' => 'Academic',
            'corporate' => 'Corporate',
            'government' => 'Government',
            'social' => 'Social',
            'cultural' => 'Cultural',
            'sports' => 'Sports',
            'other' => 'Other',
        ];
    }

    protected function getSeatingArrangements(): array
    {
        return [
            'theatre' => 'Theatre',
            'classroom' => 'Classroom',
            'u_shape' => 'U-Shape',
            'boardroom' => 'Boardroom',
            'banquet' => 'Banquet',
            'cabaret' => 'Cabaret',
            'custom' => 'Custom Arrangement',
        ];
    }

    protected function getCateringPackages(): array
    {
        return [
            'basic' => 'Basic Package',
            'standard' => 'Standard Package',
            'premium' => 'Premium Package',
            'vip' => 'VIP Package',
            'custom' => 'Custom Package',
        ];
    }
}
