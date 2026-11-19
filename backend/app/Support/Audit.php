<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

/**
 * Audit trail entries the AuditsActivity trait can't see: pivot syncs such as
 * a user's roles or a role's permissions (spatie writes them without model
 * events). Same shape as the trait's entries (`attributes` new, `old`), so the
 * Audit Trail page shows them alike.
 */
final class Audit
{
    /**
     * @param  array<string, mixed>  $new
     * @param  array<string, mixed>  $old
     */
    public static function record(Model $subject, string $event, array $new, array $old = [], string $logName = 'Administration'): void
    {
        if ($event === 'updated' && $new == $old) {
            return;
        }

        activity($logName)
            ->performedOn($subject)
            ->event($event)
            ->withChanges(array_filter(['attributes' => $new, 'old' => $old]))
            ->log($event);
    }

    /**
     * A list on the record changed (role names, permission names …); no change, no entry.
     *
     * @param  array<int, string>  $old
     * @param  array<int, string>  $new
     */
    public static function listChanged(Model $subject, string $field, array $old, array $new, string $logName = 'Administration'): void
    {
        sort($old);
        sort($new);
        if ($old === $new) {
            return;
        }
        self::record($subject, 'updated', [$field => array_values($new)], [$field => array_values($old)], $logName);
    }
}
