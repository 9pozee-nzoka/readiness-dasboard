<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Event;
use App\Models\OutsourcedService;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OutsourcedServiceController extends Controller
{
    public function __construct()
    {
        abort_unless(auth()->user()?->isDirectorOrAdmin(), 403, 'Access restricted to Directors and Administrators');
    }

    /**
     * Director/Senior Management view of all hirings and outsourced services
     */
    public function index(Request $request): View
    {
        $query = OutsourcedService::with(['event', 'department', 'responsibleUser']);

        // Filters
        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $services = $query->latest()->paginate(20);

        // Summary statistics
        $stats = [
            'total' => OutsourcedService::count(),
            'pending_approval' => OutsourcedService::where('approval_status', OutsourcedService::APPROVAL_PENDING)->count(),
            'active' => OutsourcedService::active()->count(),
            'total_cost' => OutsourcedService::where('approval_status', OutsourcedService::APPROVAL_APPROVED)->sum('cost'),
            'by_department' => OutsourcedService::with('department')
                ->selectRaw('department_id, count(*) as count, sum(cost) as total_cost')
                ->groupBy('department_id')
                ->get(),
        ];

        $events = Event::orderBy('event_date', 'desc')->limit(50)->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('admin.outsourced-services.index', compact('services', 'stats', 'events', 'departments'));
    }

    /**
     * Show single service
     */
    public function show(OutsourcedService $outsourcedService): View
    {
        $outsourcedService->load(['event', 'department', 'responsibleUser']);

        return view('admin.outsourced-services.show', compact('outsourcedService'));
    }

    /**
     * Show create form
     */
    public function create(Request $request): View
    {
        $events = Event::orderBy('event_date', 'desc')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $users = User::whereNotNull('department_id')
            ->with('department')
            ->orderBy('name')
            ->get();
        $selectedEventId = $request->integer('event_id');

        return view('admin.outsourced-services.create', compact('events', 'departments', 'users', 'selectedEventId'));
    }

    /**
     * Store new service
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'event_id' => ['required', 'exists:events,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'provider' => ['required', 'string', 'max:255'],
            'service_type' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:outsourced_service,hiring'],
            'reason' => ['required', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'responsible_user_id' => ['required', 'exists:users,id'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['status'] = OutsourcedService::STATUS_REQUESTED;
        $validated['approval_status'] = OutsourcedService::APPROVAL_PENDING;

        $outsourcedService = OutsourcedService::create($validated);

        return redirect()
            ->route('admin.outsourced-services.show', $outsourcedService)
            ->with('success', 'Outsourced service recorded successfully.');
    }

    /**
     * Show edit form
     */
    public function edit(OutsourcedService $outsourcedService): View
    {
        $events = Event::orderBy('event_date', 'desc')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $users = User::whereNotNull('department_id')
            ->with('department')
            ->orderBy('name')
            ->get();

        return view('admin.outsourced-services.edit', compact('outsourcedService', 'events', 'departments', 'users'));
    }

    /**
     * Update service
     */
    public function update(Request $request, OutsourcedService $outsourcedService): RedirectResponse
    {
        $validated = $request->validate([
            'event_id' => ['required', 'exists:events,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'provider' => ['required', 'string', 'max:255'],
            'service_type' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:outsourced_service,hiring'],
            'reason' => ['required', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'responsible_user_id' => ['required', 'exists:users,id'],
            'status' => ['required', 'in:requested,approved,confirmed,completed,cancelled'],
            'approval_status' => ['required', 'in:pending,approved,rejected'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $outsourcedService->update($validated);

        return redirect()
            ->route('admin.outsourced-services.show', $outsourcedService)
            ->with('success', 'Service updated successfully.');
    }

    /**
     * Approve service
     */
    public function approve(OutsourcedService $outsourcedService): RedirectResponse
    {
        $outsourcedService->approve();

        return back()->with('success', 'Service approved successfully.');
    }

    /**
     * Reject service
     */
    public function reject(OutsourcedService $outsourcedService): RedirectResponse
    {
        $outsourcedService->reject();

        return back()->with('success', 'Service rejected.');
    }

    /**
     * Delete service
     */
    public function destroy(OutsourcedService $outsourcedService): RedirectResponse
    {
        $outsourcedService->delete();

        return redirect()
            ->route('admin.outsourced-services.index')
            ->with('success', 'Service deleted successfully.');
    }
}
