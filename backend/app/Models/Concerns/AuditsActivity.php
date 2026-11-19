<?php

namespace App\Models\Concerns;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Audit trail (spatie/laravel-activitylog → activity_log): every create /
 * update / delete of the model is logged with the user who did it (causer)
 * and the old / new values of the fields that changed.
 *
 * A model can override auditLogName() (the Audit Trail's module), add
 * non-fillable fields with `protected array $auditAlso` and leave fields out
 * with `protected array $auditIgnore`. Secrets are never logged.
 * Bulk query updates / deletes skip model events.
 */
trait AuditsActivity
{
    use LogsActivity;

    private const AUDIT_NEVER = ['password', 'remember_token', 'created_at', 'updated_at'];

    public function getActivitylogOptions(): LogOptions
    {
        $fields = array_values(array_diff(
            [...$this->getFillable(), ...($this->auditAlso ?? [])],
            [...self::AUDIT_NEVER, ...($this->auditIgnore ?? [])],
        ));

        return LogOptions::defaults()
            ->logOnly($fields)
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName($this->auditLogName());
    }

    protected function auditLogName(): string
    {
        return 'Administration';
    }
}
