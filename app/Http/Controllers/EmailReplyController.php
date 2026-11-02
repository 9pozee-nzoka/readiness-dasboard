<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Requirement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailReplyController extends Controller
{
    /**
     * Handle incoming email replies (webhook endpoint)
     *
     * This endpoint receives emails forwarded by email services like SendGrid, Mailgun, etc.
     * The reply-to address format: replies+{type}-{id}-{user_id}@yourdomain.com
     */
    public function handleIncoming(Request $request)
    {
        try {
            // Log the incoming request for debugging
            Log::info('Incoming email reply', ['data' => $request->all()]);

            // Extract email content and metadata
            $to = $request->input('to') ?? $request->input('recipient');
            $from = $request->input('from') ?? $request->input('sender');
            $subject = $request->input('subject');
            $textBody = $request->input('text') ?? $request->input('body-plain');
            $htmlBody = $request->input('html') ?? $request->input('body-html');

            // Use text body, fallback to HTML if text not available
            $body = $textBody ?? strip_tags($htmlBody ?? '');

            if (empty($body)) {
                Log::warning('Email reply received with empty body');

                return response()->json(['status' => 'ignored', 'reason' => 'empty body'], 200);
            }

            // Parse the reply-to address to extract metadata
            // Format: replies+comment-123-45@domain.com
            // Or: replies+requirement-456-78@domain.com
            preg_match('/replies\+([a-z]+)-(\d+)-(\d+)@/', $to, $matches);

            if (count($matches) < 4) {
                Log::warning('Invalid reply-to format', ['to' => $to]);

                return response()->json(['status' => 'ignored', 'reason' => 'invalid format'], 200);
            }

            [$full, $type, $entityId, $userId] = $matches;

            // Clean up the email body (remove quoted replies, signatures)
            $body = $this->cleanEmailBody($body);

            if (empty(trim($body))) {
                Log::warning('Email body empty after cleaning');

                return response()->json(['status' => 'ignored', 'reason' => 'empty after cleaning'], 200);
            }

            // Verify user exists
            $user = User::find($userId);
            if (! $user) {
                Log::warning('User not found for email reply', ['user_id' => $userId]);

                return response()->json(['status' => 'error', 'reason' => 'user not found'], 404);
            }

            // Handle based on type
            switch ($type) {
                case 'comment':
                    $this->handleCommentReply($entityId, $userId, $body);
                    break;

                case 'requirement':
                    $this->handleRequirementReply($entityId, $userId, $body);
                    break;

                default:
                    Log::warning('Unknown reply type', ['type' => $type]);

                    return response()->json(['status' => 'ignored', 'reason' => 'unknown type'], 200);
            }

            return response()->json(['status' => 'success'], 200);

        } catch (\Exception $e) {
            Log::error('Error processing email reply', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle reply to a comment
     */
    private function handleCommentReply(int $commentId, int $userId, string $body): void
    {
        $originalComment = Comment::find($commentId);

        if (! $originalComment) {
            throw new \Exception("Comment not found: {$commentId}");
        }

        // Create a new comment as a reply
        $reply = Comment::create([
            'user_id' => $userId,
            'event_id' => $originalComment->event_id,
            'requirement_id' => $originalComment->requirement_id,
            'parent_id' => $commentId,
            'message' => $body,
            'via_email' => true,
        ]);

        Log::info('Comment reply created via email', [
            'comment_id' => $reply->id,
            'parent_id' => $commentId,
            'user_id' => $userId,
        ]);
    }

    /**
     * Handle reply to a requirement notification
     */
    private function handleRequirementReply(int $requirementId, int $userId, string $body): void
    {
        $requirement = Requirement::find($requirementId);

        if (! $requirement) {
            throw new \Exception("Requirement not found: {$requirementId}");
        }

        // Create a comment on the requirement
        $comment = Comment::create([
            'user_id' => $userId,
            'event_id' => $requirement->event_id,
            'requirement_id' => $requirementId,
            'message' => $body,
            'via_email' => true,
        ]);

        Log::info('Requirement comment created via email', [
            'comment_id' => $comment->id,
            'requirement_id' => $requirementId,
            'user_id' => $userId,
        ]);
    }

    /**
     * Clean email body by removing quoted replies and signatures
     */
    private function cleanEmailBody(string $body): string
    {
        // Remove lines starting with > (quoted text)
        $lines = explode("\n", $body);
        $cleanedLines = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // Stop at common reply markers
            if (
                str_starts_with($trimmed, '>') ||
                str_starts_with($trimmed, 'On ') && str_contains($trimmed, 'wrote:') ||
                str_contains($trimmed, '--- Original Message ---') ||
                str_contains($trimmed, '________________________________') ||
                str_contains($trimmed, 'From:') && str_contains($body, 'Sent:') ||
                $trimmed === '--'
            ) {
                break;
            }

            $cleanedLines[] = $line;
        }

        return trim(implode("\n", $cleanedLines));
    }

    /**
     * Generate a unique reply-to email address
     */
    public static function generateReplyAddress(string $type, int $entityId, int $userId): string
    {
        $domain = config('mail.reply_domain') ?? env('MAIL_REPLY_DOMAIN', parse_url(config('app.url'), PHP_URL_HOST));

        return "replies+{$type}-{$entityId}-{$userId}@{$domain}";
    }
}
