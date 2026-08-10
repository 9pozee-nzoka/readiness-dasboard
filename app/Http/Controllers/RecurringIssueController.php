<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Event;
use App\Models\RecurringIssue;
use App\Models\RecurringIssueOccurrence;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RecurringIssueController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();
        $category = $request->string('category')->value();
        $severity = $request->string('severity')->value();
        $status = $request->string('status', 'open')->value();

        $issues = RecurringIssue::with(['department', 'reportedBy'])
            ->withCount('occurrences')
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('root_cause', 'like', "%{$search}%");
            }))
            ->when($category, fn ($q) => $q->where('category', $category))
            ->when($severity, fn ($q) => $q->where('severity', $severity))
            ->when($status === 'resolved', fn ($q) => $q->where('is_resolved', true))
            ->when($status === 'open', fn ($q) => $q->where('is_resolved', false))
            ->orderByDesc('occurrences_count')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        // Stats
        $totalIssues = RecurringIssue::count();
        $openIssues = RecurringIssue::where('is_resolved', false)->count();
        $criticalIssues = RecurringIssue::where('severity', 'critical')->where('is_resolved', false)->count();
        $trendingCount = RecurringIssue::where('is_resolved', false)->get()->filter(fn ($i) => $i->isTrending())->count();

        // Top recurring by frequency
        $topIssues = RecurringIssue::withCount('occurrences')
            ->where('is_resolved', false)
            ->orderByDesc('occurrences_count')
            ->limit(5)
            ->get();

        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $categories = RecurringIssue::categories();

        return view('knowledge-base.recurring-issues.index', compact(
            'issues', 'search', 'category', 'severity', 'status',
            'totalIssues', 'openIssues', 'criticalIssues', 'trendingCount',
            'topIssues', 'departments', 'categories'
        ));
    }

    public function create(): View
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $categories = RecurringIssue::categories();
        $events = Event::orderByDesc('event_date')->limit(30)->get();

        return view('knowledge-base.recurring-issues.create', compact('departments', 'categories', 'events'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['required', 'in:'.implode(',', array_keys(RecurringIssue::categories()))],
            'severity' => ['required', 'in:low,medium,high,critical'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'root_cause' => ['nullable', 'string'],
            'recommended_fix' => ['nullable', 'string'],
            // Optional first occurrence
            'event_id' => ['nullable', 'exists:events,id'],
            'occurrence_notes' => ['nullable', 'string'],
        ]);

        $issue = RecurringIssue::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'],
            'severity' => $validated['severity'],
            'department_id' => $validated['department_id'] ?? null,
            'root_cause' => $validated['root_cause'] ?? null,
            'recommended_fix' => $validated['recommended_fix'] ?? null,
            'reported_by' => Auth::id(),
        ]);

        // Log the first occurrence if an event was provided
        if (! empty($validated['event_id'])) {
            RecurringIssueOccurrence::create([
                'recurring_issue_id' => $issue->id,
                'event_id' => $validated['event_id'],
                'notes' => $validated['occurrence_notes'] ?? null,
                'source' => 'manual',
                'occurred_at' => now(),
            ]);
        }

        return redirect()->route('recurring-issues.show', $issue)
            ->with('success', 'Recurring issue recorded successfully.');
    }

    public function show(RecurringIssue $recurringIssue): View
    {
        $recurringIssue->load(['department', 'reportedBy']);

        $occurrences = $recurringIssue->occurrences()
            ->with(['event', 'postEventReview'])
            ->get();

        $events = Event::orderByDesc('event_date')->limit(30)->get();

        return view('knowledge-base.recurring-issues.show', compact('recurringIssue', 'occurrences', 'events'));
    }

    public function edit(RecurringIssue $recurringIssue): View
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $categories = RecurringIssue::categories();

        return view('knowledge-base.recurring-issues.edit', compact('recurringIssue', 'departments', 'categories'));
    }

    public function update(Request $request, RecurringIssue $recurringIssue): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['required', 'in:'.implode(',', array_keys(RecurringIssue::categories()))],
            'severity' => ['required', 'in:low,medium,high,critical'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'root_cause' => ['nullable', 'string'],
            'recommended_fix' => ['nullable', 'string'],
        ]);

        $recurringIssue->update($validated);

        return redirect()->route('recurring-issues.show', $recurringIssue)
            ->with('success', 'Issue updated.');
    }

    public function destroy(RecurringIssue $recurringIssue): RedirectResponse
    {
        abort_unless(Auth::user()->isAdmin(), 403);
        $recurringIssue->delete();

        return redirect()->route('recurring-issues.index')
            ->with('success', 'Issue deleted.');
    }

    /** Mark issue as resolved. */
    public function resolve(RecurringIssue $recurringIssue): RedirectResponse
    {
        abort_unless(Auth::user()->isAdmin() || Auth::user()->isHod(), 403);

        $recurringIssue->update([
            'is_resolved' => true,
            'resolved_at' => now(),
        ]);

        return back()->with('success', 'Issue marked as resolved.');
    }

    /** Log a new occurrence against an existing issue. */
    public function logOccurrence(Request $request, RecurringIssue $recurringIssue): RedirectResponse
    {
        $validated = $request->validate([
            'event_id' => ['nullable', 'exists:events,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'occurred_at' => ['nullable', 'date'],
        ]);

        RecurringIssueOccurrence::create([
            'recurring_issue_id' => $recurringIssue->id,
            'event_id' => $validated['event_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'source' => 'manual',
            'occurred_at' => $validated['occurred_at'] ?? now(),
        ]);

        // Auto-reopen if resolved
        if ($recurringIssue->is_resolved) {
            $recurringIssue->update(['is_resolved' => false, 'resolved_at' => null]);
        }

        return back()->with('success', 'Occurrence logged.');
    }
}
