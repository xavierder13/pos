<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

// a file on a record (HasAttachments); the file itself is on `disk` at `path`
#[Fillable(['original_name', 'disk', 'path', 'mime_type', 'size', 'uploaded_by'])]
class Attachment extends Model
{
    protected static function booted(): void
    {
        // the file goes with its row
        static::deleted(fn (Attachment $a) => Storage::disk($a->disk)->delete($a->path));
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
