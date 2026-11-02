<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Event;
use App\Models\Requirement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CommentController extends Controller
{
    /**
     * Display all comments accessible to the current user
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Build base query with relationships
        $query = Comment::with([
            'user',
            'requirement.event',
            'requirement.department',
            'event',
            'parentComment.user',
            'replies.user',
        ])
            ->whereNull('parent_comment_id') // Only top-level comments
            ->orderBy('created_at', 'desc');

        // Filter based on user role
        if ($user->isEmployee()) {
            // Employees see comments on their own requirements
            $query->whereHas('requirement', function ($q) use ($user) {
                $q->where('assigned_to', $user->id);
            });
        } elseif ($user->isHod()) {
            // HODs see comments on their department's requirements
            $query->whereHas('requirement', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        }
        // Directors and admins see all comments

        // Filter by event if specified
        if ($request->filled('event_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('event_id', $request->event_id)
                    ->orWhereHas('requirement', function ($q2) use ($request) {
                        $q2->where('event_id', $request->event_id);
                    });
            });
        }

        // Filter by requirement if specified
        if ($request->filled('requirement_id')) {
            $query->where('requirement_id', $request->requirement_id);
        }

        $comments = $query->paginate(20);

        // Get events for filter dropdown
        $events = Event::orderBy('event_date', 'desc')->limit(50)->get();

        return view('comments.index', compact('comments', 'events'));
    }

    /**
     * Store a new comment (on event or requirement).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'commentable_type' => ['required', Rule::in(['App\Models\Event', 'App\Models\Requirement'])],
            'commentable_id' => ['required', 'integer'],
            'body' => ['required', 'string', 'max:5000'],
            'parent_comment_id' => ['nullable', 'exists:comments,id'],
            'directed_to' => ['nullable', Rule::in(['auto', 'director', 'staff', 'specific_user'])],
            'directed_to_user_id' => ['nullable', 'exists:users,id'],
        ]);

        // Verify the commentable exists
        $commentable = match ($validated['commentable_type']) {
            'App\Models\Event' => Event::findOrFail($validated['commentable_id']),
            'App\Models\Requirement' => Requirement::findOrFail($validated['commentable_id']),
        };

        // Create comment with automatic notification
        $commentData = [
            'user_id' => Auth::id(),
            'commentable_type' => $validated['commentable_type'],
            'commentable_id' => $validated['commentable_id'],
            'message' => $validated['body'],
            'channel' => 'system',
            'parent_comment_id' => $validated['parent_comment_id'] ?? null,
            'directed_to' => $validated['directed_to'] ?? 'auto',
            'directed_to_user_id' => $validated['directed_to_user_id'] ?? null,
        ];

        // Map commentable_type to the correct field
        if ($validated['commentable_type'] === 'App\Models\Event') {
            $commentData['event_id'] = $validated['commentable_id'];
            unset($commentData['commentable_type'], $commentData['commentable_id']);
        } elseif ($validated['commentable_type'] === 'App\Models\Requirement') {
            $commentData['requirement_id'] = $validated['commentable_id'];
            unset($commentData['commentable_type'], $commentData['commentable_id']);
        }

        $comment = Comment::createWithNotification($commentData);

        return back()->with('success', 'Comment posted successfully.');
    }

    /**
     * Delete a comment.
     */
    public function destroy(Comment $comment): RedirectResponse
    {
        $user = Auth::user();

        // Only comment author or admin can delete
        abort_unless(
            $user->id === $comment->user_id || $user->isAdmin(),
            403,
            'You can only delete your own comments.'
        );

        $comment->delete();

        return back()->with('success', 'Comment deleted.');
    }
}
