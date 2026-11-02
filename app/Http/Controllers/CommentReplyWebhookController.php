<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CommentReplyWebhookController extends Controller
{
    /**
     * Handle incoming email replies via webhook.
     * Expects email provider to POST with: from, subject, body, in_reply_to
     */
    public function handleEmailReply(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'from' => ['required', 'email'],
                'subject' => ['nullable', 'string'],
                'body' => ['required', 'string'],
                'in_reply_to' => ['nullable', 'string'], // Original email message ID
                'references' => ['nullable', 'string'], // Thread references
            ]);

            // Find user by email
            $user = User::where('email', $validated['from'])->first();

            if (! $user) {
                Log::warning('Email reply from unknown user', ['email' => $validated['from']]);

                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }

            // Extract parent comment reference from email headers
            $parentComment = $this->findParentCommentFromEmail(
                $validated['in_reply_to'] ?? null,
                $validated['references'] ?? null,
                $validated['subject'] ?? null
            );

            if (! $parentComment) {
                Log::warning('Could not identify parent comment', [
                    'email' => $validated['from'],
                    'in_reply_to' => $validated['in_reply_to'] ?? null,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Could not identify parent comment',
                ], 400);
            }

            // Clean email body (remove quoted text, signatures)
            $cleanBody = $this->cleanEmailBody($validated['body']);

            // Create reply comment
            $reply = Comment::create([
                'user_id' => $user->id,
                'parent_comment_id' => $parentComment->id,
                'commentable_type' => $parentComment->commentable_type,
                'commentable_id' => $parentComment->commentable_id,
                'body' => $cleanBody,
                'channel' => 'email',
                'external_id' => $validated['in_reply_to'] ?? Str::uuid(),
            ]);

            Log::info('Email reply processed', [
                'user_id' => $user->id,
                'comment_id' => $reply->id,
                'parent_comment_id' => $parentComment->id,
            ]);

            return response()->json([
                'success' => true,
                'comment_id' => $reply->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Email webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Handle incoming WhatsApp replies via webhook.
     * Expects WhatsApp Business API format with: from, body, context
     */
    public function handleWhatsAppReply(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'from' => ['required', 'string'], // WhatsApp phone number
                'body' => ['required', 'string'],
                'context.message_id' => ['nullable', 'string'], // Parent message ID being replied to
            ]);

            // Find user by WhatsApp phone number
            $user = User::where('whatsapp_number', $validated['from'])->first();

            if (! $user) {
                Log::warning('WhatsApp reply from unknown user', ['phone' => $validated['from']]);

                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }

            // Find parent comment by WhatsApp message ID
            $parentComment = null;
            if (isset($validated['context']['message_id'])) {
                $parentComment = Comment::where('external_id', $validated['context']['message_id'])
                    ->where('channel', 'whatsapp')
                    ->first();
            }

            if (! $parentComment) {
                Log::warning('Could not identify parent comment', [
                    'phone' => $validated['from'],
                    'context_message_id' => $validated['context']['message_id'] ?? null,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Could not identify parent comment',
                ], 400);
            }

            // Create reply comment
            $reply = Comment::create([
                'user_id' => $user->id,
                'parent_comment_id' => $parentComment->id,
                'commentable_type' => $parentComment->commentable_type,
                'commentable_id' => $parentComment->commentable_id,
                'body' => $validated['body'],
                'channel' => 'whatsapp',
                'external_id' => Str::uuid(),
            ]);

            Log::info('WhatsApp reply processed', [
                'user_id' => $user->id,
                'comment_id' => $reply->id,
                'parent_comment_id' => $parentComment->id,
            ]);

            return response()->json([
                'success' => true,
                'comment_id' => $reply->id,
            ]);
        } catch (\Exception $e) {
            Log::error('WhatsApp webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Find parent comment from email headers.
     */
    private function findParentCommentFromEmail(?string $inReplyTo, ?string $references, ?string $subject): ?Comment
    {
        // Try to find by external_id (message ID)
        if ($inReplyTo) {
            $comment = Comment::where('external_id', $inReplyTo)
                ->where('channel', 'email')
                ->first();

            if ($comment) {
                return $comment;
            }
        }

        // Try to parse references header
        if ($references) {
            $messageIds = array_filter(array_map('trim', explode(' ', $references)));
            foreach ($messageIds as $messageId) {
                $comment = Comment::where('external_id', $messageId)
                    ->where('channel', 'email')
                    ->first();

                if ($comment) {
                    return $comment;
                }
            }
        }

        // Try to extract comment ID from subject line (e.g., "Re: Comment #123")
        if ($subject && preg_match('/#(\d+)/', $subject, $matches)) {
            return Comment::find($matches[1]);
        }

        return null;
    }

    /**
     * Clean email body by removing quoted text and signatures.
     */
    private function cleanEmailBody(string $body): string
    {
        // Remove everything after common reply separators
        $patterns = [
            '/^On .+ wrote:$/m', // "On [date] [name] wrote:"
            '/^From: .+$/m', // Outlook-style quotes
            '/^_{5,}$/m', // Horizontal lines
            '/^-{5,}$/m',
            '/^>.*$/m', // Quoted lines starting with >
        ];

        foreach ($patterns as $pattern) {
            $parts = preg_split($pattern, $body, 2);
            if (count($parts) > 1) {
                $body = trim($parts[0]);
            }
        }

        // Remove common signatures
        $signaturePatterns = [
            '/^--\s*$/m',
            '/^Sent from my .+$/mi',
            '/^Best regards,?$/mi',
            '/^Thanks,?$/mi',
        ];

        foreach ($signaturePatterns as $pattern) {
            $parts = preg_split($pattern, $body, 2);
            if (count($parts) > 1) {
                $body = trim($parts[0]);
            }
        }

        return trim($body);
    }
}
