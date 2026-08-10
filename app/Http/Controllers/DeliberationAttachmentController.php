<?php

namespace App\Http\Controllers;

use App\Models\Deliberation;
use App\Models\DeliberationAttachment;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DeliberationAttachmentController extends Controller
{
    /**
     * Stream or download an attachment.
     */
    public function download(Event $event, Deliberation $deliberation, DeliberationAttachment $attachment): StreamedResponse
    {
        $this->authorizeAccess($event, $deliberation, $attachment);

        abort_unless(Storage::disk('local')->exists($attachment->path), 404);

        return Storage::disk('local')->download($attachment->path, $attachment->original_name);
    }

    /**
     * Preview an attachment inline (images and PDFs).
     */
    public function preview(Event $event, Deliberation $deliberation, DeliberationAttachment $attachment): StreamedResponse
    {
        $this->authorizeAccess($event, $deliberation, $attachment);

        abort_unless($attachment->canBePreviewed(), 404, 'This file type cannot be previewed.');
        abort_unless(Storage::disk('local')->exists($attachment->path), 404);

        $disk = Storage::disk('local');
        $stream = $disk->readStream($attachment->path);

        return response()->stream(function () use ($stream): void {
            fpassthru($stream);
        }, 200, [
            'Content-Type' => $attachment->mime_type ?? 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="'.$attachment->original_name.'"',
        ]);
    }

    /**
     * Store additional attachments on an existing deliberation.
     */
    public function store(Request $request, Event $event, Deliberation $deliberation): RedirectResponse
    {
        abort_unless($deliberation->event_id === $event->id, 404);
        abort_unless($deliberation->status->isOpen(), 403, 'Attachments cannot be added to closed deliberations.');

        $user = auth()->user();
        $isCreator = $deliberation->approved_by === $user->id || $deliberation->participants->contains($user->id);
        abort_unless($user->isAdmin() || $isCreator, 403);

        $validated = $request->validate([
            'attachments' => ['required', 'array'],
            'attachments.*' => ['file', 'max:10240'],
        ]);

        foreach ($request->file('attachments', []) as $file) {
            $path = $file->store('deliberations/'.$deliberation->id, 'local');

            DeliberationAttachment::create([
                'deliberation_id' => $deliberation->id,
                'user_id' => $user->id,
                'filename' => $file->hashName(),
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        return back()->with('success', count($validated['attachments']).' attachment(s) uploaded.');
    }

    /**
     * Remove an attachment.
     */
    public function destroy(Event $event, Deliberation $deliberation, DeliberationAttachment $attachment): RedirectResponse
    {
        $this->authorizeAccess($event, $deliberation, $attachment);

        $user = auth()->user();
        abort_unless($user->isAdmin() || $attachment->user_id === $user->id, 403);

        $attachment->delete();

        return back()->with('success', 'Attachment deleted.');
    }

    private function authorizeAccess(Event $event, Deliberation $deliberation, DeliberationAttachment $attachment): void
    {
        abort_unless($deliberation->event_id === $event->id, 404);
        abort_unless($attachment->deliberation_id === $deliberation->id, 404);
    }
}
