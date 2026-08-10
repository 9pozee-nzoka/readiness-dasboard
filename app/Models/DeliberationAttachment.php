<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DeliberationAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'deliberation_id',
        'user_id',
        'filename',
        'original_name',
        'path',
        'mime_type',
        'size',
    ];

    public function deliberation(): BelongsTo
    {
        return $this->belongsTo(Deliberation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    public function canBePreviewed(): bool
    {
        return $this->isImage() || $this->isPdf();
    }

    public function downloadUrl(): string
    {
        return route('deliberations.attachments.download', [$this->deliberation->event, $this->deliberation, $this]);
    }

    public function previewUrl(): string
    {
        return route('deliberations.attachments.preview', [$this->deliberation->event, $this->deliberation, $this]);
    }

    public function formattedSize(): string
    {
        $size = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2).' '.$units[$unitIndex];
    }

    protected static function booted(): void
    {
        static::deleting(function (DeliberationAttachment $attachment): void {
            if ($attachment->path) {
                Storage::disk('local')->delete($attachment->path);
            }
        });
    }
}
