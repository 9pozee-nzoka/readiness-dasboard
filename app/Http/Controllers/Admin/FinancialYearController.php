<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BudgetCarryForward;
use App\Models\FinancialYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FinancialYearController extends Controller
{
    public function __construct()
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }

    public function index(): View
    {
        $financialYears = FinancialYear::query()
            ->withCount('planningWeeks')
            ->orderByDesc('start_date')
            ->get();

        return view('admin.financial-years.index', compact('financialYears'));
    }

    public function create(): View
    {
        return view('admin.financial-years.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:financial_years,name',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_current' => 'boolean',
        ]);

        $fy = DB::transaction(function () use ($validated) {
            if ($validated['is_current'] ?? false) {
                FinancialYear::query()->update(['is_current' => false]);
            }

            return FinancialYear::create($validated);
        });

        return redirect()
            ->route('admin.financial-years.show', $fy)
            ->with('success', 'Financial year created.');
    }

    public function show(FinancialYear $financialYear): View
    {
        $financialYear->load([
            'planningWeeks' => fn ($query) => $query
                ->withCount('events')
                ->orderBy('week_start'),
            'closedBy',
        ]);

        $transitionWeeks = $financialYear->planningWeeks()
            ->where('is_transition_week', true)
            ->get();

        $carryForwards = BudgetCarryForward::query()
            ->where('from_financial_year_id', $financialYear->id)
            ->orWhere('to_financial_year_id', $financialYear->id)
            ->with(['fromFinancialYear', 'toFinancialYear', 'requestedBy', 'approvedBy'])
            ->orderByDesc('requested_at')
            ->get();

        // Get week counts per campus
        $weeksByCampus = $financialYear->planningWeeks()
            ->selectRaw('campus, COUNT(*) as count')
            ->groupBy('campus')
            ->pluck('count', 'campus');

        return view('admin.financial-years.show', [
            'financialYear' => $financialYear,
            'transitionWeeks' => $transitionWeeks,
            'carryForwards' => $carryForwards,
            'weeksByCampus' => $weeksByCampus,
        ]);
    }

    public function edit(FinancialYear $financialYear): View
    {
        abort_if($financialYear->is_locked, 403, 'Cannot edit locked financial year.');

        return view('admin.financial-years.edit', [
            'financialYear' => $financialYear,
        ]);
    }

    public function update(Request $request, FinancialYear $financialYear): RedirectResponse
    {
        abort_if($financialYear->is_locked, 403, 'Cannot update locked financial year.');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:financial_years,name,'.$financialYear->id,
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_current' => 'boolean',
        ]);

        DB::transaction(function () use ($validated, $financialYear) {
            if ($validated['is_current'] ?? false) {
                FinancialYear::query()
                    ->whereKeyNot($financialYear->id)
                    ->update(['is_current' => false]);
            }

            $financialYear->update($validated);
        });

        return redirect()
            ->route('admin.financial-years.show', $financialYear)
            ->with('success', 'Financial year updated.');
    }

    public function destroy(FinancialYear $financialYear): RedirectResponse
    {
        abort_if($financialYear->is_locked, 403, 'Cannot delete locked financial year.');
        abort_if($financialYear->is_current, 403, 'Cannot delete current financial year.');

        $financialYear->delete();

        return redirect()
            ->route('admin.financial-years.index')
            ->with('success', 'Financial year removed.');
    }

    public function setCurrent(FinancialYear $financialYear): RedirectResponse
    {
        $financialYear->markAsCurrent();

        return back()->with('success', "{$financialYear->name} is now the current financial year.");
    }

    public function close(Request $request, FinancialYear $financialYear): RedirectResponse
    {
        $validated = $request->validate([
            'closure_notes' => 'nullable|string|max:1000',
        ]);

        $financialYear->close(auth()->user(), $validated['closure_notes'] ?? null);

        return back()->with('success', "{$financialYear->name} has been closed and locked.");
    }

    public function reopen(FinancialYear $financialYear): RedirectResponse
    {
        $financialYear->reopen();

        return back()->with('success', "{$financialYear->name} has been reopened.");
    }

    public function carryForwards(): View
    {
        $carryForwards = BudgetCarryForward::query()
            ->with(['fromFinancialYear', 'toFinancialYear', 'requestedBy', 'approvedBy', 'department', 'event'])
            ->orderByDesc('requested_at')
            ->paginate(20);

        return view('admin.financial-years.carry-forwards', compact('carryForwards'));
    }

    public function approveCarryForward(Request $request, BudgetCarryForward $carryForward): RedirectResponse
    {
        $validated = $request->validate([
            'approval_notes' => 'nullable|string|max:1000',
        ]);

        $carryForward->approve(auth()->user(), $validated['approval_notes'] ?? null);

        return back()->with('success', 'Budget carry-forward approved.');
    }

    public function rejectCarryForward(Request $request, BudgetCarryForward $carryForward): RedirectResponse
    {
        $validated = $request->validate([
            'approval_notes' => 'required|string|max:1000',
        ]);

        $carryForward->reject(auth()->user(), $validated['approval_notes']);

        return back()->with('success', 'Budget carry-forward rejected.');
    }

    public function generateWeeks(Request $request, FinancialYear $financialYear): RedirectResponse
    {
        $validated = $request->validate([
            'campus' => 'required|string|in:NRB,MTB,BRC,LWR',
            'force' => 'boolean',
        ]);

        $created = $financialYear->generateWeeks(
            $validated['campus'],
            $validated['force'] ?? false
        );

        if ($created === 0) {
            return back()->with('info', "Planning weeks already exist for {$validated['campus']} campus.");
        }

        return back()->with('success', "Generated {$created} planning weeks for {$validated['campus']} campus.");
    }
}
