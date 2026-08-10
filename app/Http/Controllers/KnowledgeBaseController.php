<?php

namespace App\Http\Controllers;

use App\Models\PostEventReview;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KnowledgeBaseController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();
        $category = $request->string('category')->value();

        $reviews = PostEventReview::with(['event', 'completedBy'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('what_went_well', 'like', "%{$search}%")
                        ->orWhere('challenges', 'like', "%{$search}%")
                        ->orWhere('lessons_learned', 'like', "%{$search}%")
                        ->orWhere('recommendations', 'like', "%{$search}%");
                });
            })
            ->when($category === 'challenges', fn ($q) => $q->whereNotNull('challenges'))
            ->when($category === 'lessons', fn ($q) => $q->whereNotNull('lessons_learned'))
            ->when($category === 'recommendations', fn ($q) => $q->whereNotNull('recommendations'))
            ->latest('completed_at')
            ->paginate(12)
            ->withQueryString();

        // Aggregate insights
        $totalReviews = PostEventReview::count();
        $recentReviews = PostEventReview::where('completed_at', '>=', now()->subMonths(3))->count();

        return view('knowledge-base.index', compact(
            'reviews', 'search', 'category', 'totalReviews', 'recentReviews'
        ));
    }

    public function show(PostEventReview $postEventReview): View
    {
        $postEventReview->load(['event', 'completedBy']);

        return view('knowledge-base.show', ['review' => $postEventReview]);
    }
}
