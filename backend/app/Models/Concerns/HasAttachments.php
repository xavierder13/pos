<?php

namespace App\Models\Concerns;

use App\Models\Attachment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Files attached to the model (the SPA's <Attachments> on a view page).
 * Register the model in App\Support\RecordTypes too. Deleting the model
 * deletes its files.
 */
trait HasAttachments
{
    public static function bootHasAttachments(): void
    {
        static::deleting(function ($model) {
            $model->attachments()->get()->each->delete();
        });
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable')->latest();
    }
}
