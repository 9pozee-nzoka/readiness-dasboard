<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageVisit;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminVisitorController extends Controller
{
    public function __construct()
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }

    public function index(Request $request): View
    {
        $range = $request->string('range', '7')->value();
        $days  = in_array($range, ['1', '7', '30']) ? (int) $range : 7;

        $since = now()->subDays($days - 1)->startOfDay();

        // Summary stats
        $totalVisits   = PageVisit::where('visited_at', '>=', $since)->count();
        $uniqueIps     = PageVisit::where('visited_at', '>=', $since)->distinct('ip_address')->count('ip_address');
        $loggedIn      = PageVisit::where('visited_at', '>=', $since)->whereNotNull('user_id')->count();
        $anonymous     = $totalVisits - $loggedIn;

        // Visits per day
        $visitsByDay = collect(range($days - 1, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo)->toDateString();

            return [
                'date'  => now()->subDays($daysAgo)->format('d M'),
                'count' => PageVisit::whereDate('visited_at', $date)->count(),
            ];
        });

        // Top pages
        $topPages = PageVisit::where('visited_at', '>=', $since)
            ->selectRaw('path, COUNT(*) as visits')
            ->groupBy('path')
            ->orderByDesc('visits')
            ->limit(10)
            ->get();

        // Recent visits
        $recentVisits = PageVisit::with('user')
            ->where('visited_at', '>=', $since)
            ->orderByDesc('visited_at')
            ->limit(50)
            ->get();

        return view('admin.visitors.index', compact(
            'totalVisits', 'uniqueIps', 'loggedIn', 'anonymous',
            'visitsByDay', 'topPages', 'recentVisits', 'range', 'days',
        ));
    }
}
