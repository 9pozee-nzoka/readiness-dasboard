<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\BookingApproved;
use App\Mail\BookingRejected;
use App\Mail\QuotationSent;
use App\Models\Department;
use App\Models\Event;
use App\Models\EventBooking;
use App\Models\PlanningWeek;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class BookingManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = EventBooking::with(['clientUser', 'reviewedBy', 'event'])
            ->orderByDesc('created_at');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->paginate(20);

        $statusCounts = [
            'all' => EventBooking::count(),
            'pending' => EventBooking::where('status', 'pending')->count(),
            'under_review' => EventBooking::where('status', 'under_review')->count(),
            'approved' => EventBooking::where('status', 'approved')->count(),
            'rejected' => EventBooking::where('status', 'rejected')->count(),
        ];

        return view('admin.bookings.index', compact('bookings', 'statusCounts'));
    }

    public function show(EventBooking $booking)
    {
        $booking->load(['clientUser', 'reviewedBy', 'event']);

        return view('admin.bookings.show', compact('booking'));
    }

    public function review(EventBooking $booking)
    {
        $booking->update(['status' => 'under_review']);

        return back()->with('success', 'Booking marked as under review.');
    }

    public function approve(Request $request, EventBooking $booking)
    {
        $request->validate([
            'allocated_venue' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $booking->approve(auth()->user(), $request->notes);

            // Update allocated venue
            $booking->update(['allocated_venue' => $request->allocated_venue]);

            // Send approval email
            if ($booking->contact_email) {
                Mail::to($booking->contact_email)->send(new BookingApproved($booking));
            }

            DB::commit();

            return redirect()->route('admin.bookings.show', $booking)
                ->with('success', 'Booking approved successfully and venue allocated. You can now convert it to an internal event.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Failed to approve booking: '.$e->getMessage());
        }
    }

    public function reject(Request $request, EventBooking $booking)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        try {
            $booking->reject(auth()->user(), $request->rejection_reason);

            // Send rejection email
            if ($booking->contact_email) {
                Mail::to($booking->contact_email)->send(new BookingRejected($booking));
            }

            return redirect()->route('admin.bookings.show', $booking)
                ->with('success', 'Booking rejected.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to reject booking: '.$e->getMessage());
        }
    }

    public function generateQuotation(Request $request, EventBooking $booking)
    {
        $request->validate([
            'estimated_cost' => 'required|numeric|min:0',
            'quotation_breakdown' => 'required|string',
            'deposit_amount' => 'nullable|numeric|min:0',
        ]);

        try {
            $booking->update([
                'estimated_cost' => $request->estimated_cost,
                'quotation_breakdown' => $request->quotation_breakdown,
                'deposit_amount' => $request->deposit_amount,
                'quotation_sent_at' => now(),
            ]);

            // Send quotation email
            if ($booking->contact_email) {
                Mail::to($booking->contact_email)->send(new QuotationSent($booking));
            }

            return back()->with('success', 'Quotation generated and sent successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate quotation: '.$e->getMessage());
        }
    }

    public function convertToEvent(EventBooking $booking)
    {
        if ($booking->status !== 'approved') {
            return back()->with('error', 'Only approved bookings can be converted to events.');
        }

        if ($booking->event_id) {
            return redirect()->route('events.show', $booking->event_id)
                ->with('info', 'This booking has already been converted to an event.');
        }

        // Redirect to requirements review page
        return redirect()->route('admin.bookings.review-requirements', $booking);
    }

    public function reviewRequirements(EventBooking $booking)
    {
        if ($booking->status !== 'approved') {
            return back()->with('error', 'Only approved bookings can be converted to events.');
        }

        if ($booking->event_id) {
            return redirect()->route('events.show', $booking->event_id)
                ->with('info', 'This booking has already been converted to an event.');
        }

        // Generate requirements list
        $requirements = $this->generateRequirements($booking);
        $departments = Department::orderBy('name')->get();

        return view('admin.bookings.convert', compact('booking', 'requirements', 'departments'));
    }

    public function processConversion(Request $request, EventBooking $booking)
    {
        $request->validate([
            'requirements' => 'required|array|min:1',
            'requirements.*.description' => 'required|string',
            'requirements.*.department_id' => 'required|exists:departments,id',
            'requirements.*.priority' => 'nullable|in:low,medium,high',
        ]);

        try {
            $event = $this->createInternalEventWithRequirements($booking, $request->requirements);

            return redirect()->route('events.show', $event)
                ->with('success', 'Booking converted to internal event successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to convert booking: '.$e->getMessage());
        }
    }

    protected function generateRequirements(EventBooking $booking): array
    {
        $requirements = [];

        if ($booking->accommodation_required) {
            $requirements[] = [
                'description' => "Accommodation: {$booking->accommodation_rooms} rooms for {$booking->accommodation_nights} nights",
                'suggested_department_id' => 7, // Facilities
            ];
        }

        if ($booking->catering_required) {
            $cateringDetails = $booking->catering_details ?? [];
            $package = ucfirst(str_replace('_', ' ', $booking->catering_package ?? 'standard'));

            // Main catering requirement
            $requirements[] = [
                'description' => "Catering setup: {$package} package for {$booking->expected_participants} participants",
                'suggested_department_id' => 10, // Catering
            ];

            // Individual meal requirements
            if (! empty($cateringDetails['breakfast'])) {
                $requirements[] = [
                    'description' => "Breakfast for {$booking->expected_participants} participants",
                    'suggested_department_id' => 10, // Catering
                ];
            }
            if (! empty($cateringDetails['morning_tea'])) {
                $requirements[] = [
                    'description' => "Morning tea for {$booking->expected_participants} participants",
                    'suggested_department_id' => 10, // Catering
                ];
            }
            if (! empty($cateringDetails['lunch'])) {
                $requirements[] = [
                    'description' => "Lunch for {$booking->expected_participants} participants",
                    'suggested_department_id' => 10, // Catering
                ];
            }
            if (! empty($cateringDetails['afternoon_tea'])) {
                $requirements[] = [
                    'description' => "Afternoon tea for {$booking->expected_participants} participants",
                    'suggested_department_id' => 10, // Catering
                ];
            }
            if (! empty($cateringDetails['dinner'])) {
                $requirements[] = [
                    'description' => "Dinner for {$booking->expected_participants} participants",
                    'suggested_department_id' => 10, // Catering
                ];
            }
        }

        if ($booking->equipment_requirements) {
            foreach ($booking->equipment_requirements as $key => $value) {
                if ($value) {
                    $label = ucfirst(str_replace('_', ' ', $key));
                    if (is_numeric($value) && $value > 1) {
                        $description = "{$label}: {$value} units";
                    } else {
                        $description = $label;
                    }

                    $requirements[] = [
                        'description' => $description,
                        'suggested_department_id' => 1, // ICT
                    ];
                }
            }
        }

        if ($booking->preferred_venue) {
            $requirements[] = [
                'description' => "Venue Setup: {$booking->preferred_venue} for {$booking->expected_participants} participants",
                'suggested_department_id' => 7, // Facilities
            ];
        }

        if ($booking->transport_required) {
            $requirements[] = [
                'description' => "Transport: {$booking->transport_details}",
                'suggested_department_id' => 5, // Logistics
            ];
        }

        // Additional requirements from special requests
        if ($booking->special_requests) {
            $requirements[] = [
                'description' => "Special Requests: {$booking->special_requests}",
                'suggested_department_id' => 3, // Administration
            ];
        }

        // Default requirement if nothing specified
        if (empty($requirements)) {
            $requirements[] = [
                'description' => "General event coordination for: {$booking->event_title}",
                'suggested_department_id' => 3, // Administration
            ];
        }

        return $requirements;
    }

    protected function createInternalEventWithRequirements(EventBooking $booking, array $requirements): Event
    {
        DB::beginTransaction();

        try {
            // Find a planning week that covers the event dates
            $planningWeek = PlanningWeek::where('week_start', '<=', $booking->preferred_start_date)
                ->where('week_end', '>=', $booking->preferred_start_date)
                ->first();

            // If no matching planning week, get the current or next one
            if (! $planningWeek) {
                $planningWeek = PlanningWeek::where('week_start', '>=', now())
                    ->orderBy('week_start')
                    ->first();
            }

            // If still no planning week, use the most recent one
            if (! $planningWeek) {
                $planningWeek = PlanningWeek::orderByDesc('week_start')->first();
            }

            // Create event
            $event = Event::create([
                'name' => $booking->event_title,
                'type' => 'External Booking',
                'event_date' => $booking->preferred_start_date,
                'event_time' => $booking->daily_start_time,
                'venue' => $booking->preferred_venue ?? 'TBD',
                'description' => $booking->event_description,
                'planning_week_id' => $planningWeek?->id,
            ]);

            // Link booking to event
            $booking->update(['event_id' => $event->id]);

            // Create requirements with assigned departments
            foreach ($requirements as $req) {
                $event->requirements()->create([
                    'description' => $req['description'],
                    'department_id' => $req['department_id'],
                    'priority' => $req['priority'] ?? 'medium',
                ]);
            }

            DB::commit();

            return $event;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
