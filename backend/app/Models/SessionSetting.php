<?php

namespace App\Models;

use App\Models\Concerns\AuditsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Session Settings (one row): how long a sign-in may sit idle and how long it
 * may last at most. Enforced by CheckSessionActivity; sent to the SPA on
 * login / init as `session` for its idle timer.
 */
#[Fillable(['idle_timeout_minutes', 'session_lifetime_minutes'])]
class SessionSetting extends Model
{
    use AuditsActivity;

    public const IDLE_MIN = 5;

    public const IDLE_MAX = 480;

    public const LIFETIME_MIN = 30;

    public const LIFETIME_MAX = 10080;   // 7 days

    private const CACHE_KEY = 'session_settings';

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
    }

    public static function current(): self
    {
        return self::query()->first() ?? self::create(['idle_timeout_minutes' => 30, 'session_lifetime_minutes' => 720]);
    }

    /**
     * @return array{idle_timeout_minutes: int, session_lifetime_minutes: int}
     */
    public static function values(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            $s = self::current();

            return [
                'idle_timeout_minutes' => (int) $s->idle_timeout_minutes,
                'session_lifetime_minutes' => (int) $s->session_lifetime_minutes,
            ];
        });
    }

    protected function auditLogName(): string
    {
        return 'Administration';
    }
}
