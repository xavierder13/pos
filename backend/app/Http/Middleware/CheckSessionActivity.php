<?php

namespace App\Http\Middleware;

use App\Models\SessionSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

/**
 * Session Settings, enforced per sign-in token:
 * - idle: no API call for idle_timeout_minutes → 401 "Your session has expired due to inactivity."
 * - lifetime: session_lifetime_minutes after sign-in → 401 "Your session has expired."
 * Either way the token is deleted. A call with `X-Session-Check: passive`
 * (the SPA's background checks: unread count, idle confirmation) is checked
 * but doesn't count as activity. The SPA signs out on these two messages.
 */
class CheckSessionActivity
{
    public const IDLE_MESSAGE = 'Your session has expired due to inactivity.';

    public const LIFETIME_MESSAGE = 'Your session has expired.';

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->user()?->currentAccessToken();

        // Sanctum::actingAs in tests, or a transient token: nothing to track
        if (! $token instanceof PersonalAccessToken || ! $token->exists) {
            return $next($request);
        }

        $settings = SessionSetting::values();
        $now = now()->getTimestamp();

        if ($token->created_at && $token->created_at->getTimestamp() + $settings['session_lifetime_minutes'] * 60 <= $now) {
            return $this->end($token, self::LIFETIME_MESSAGE);
        }

        $last = Cache::get(self::key($token->id));
        if ($last && $now - (int) $last >= $settings['idle_timeout_minutes'] * 60) {
            return $this->end($token, self::IDLE_MESSAGE);
        }

        if (! $last || $request->header('X-Session-Check') !== 'passive') {
            self::touch($token->id);
        }

        return $next($request);
    }

    // a new sign-in starts active now
    public static function touch(int $tokenId): void
    {
        Cache::put(self::key($tokenId), now()->getTimestamp(), now()->addMinutes(SessionSetting::LIFETIME_MAX));
    }

    public static function forget(int $tokenId): void
    {
        Cache::forget(self::key($tokenId));
    }

    private static function key(int $tokenId): string
    {
        return "session_activity:{$tokenId}";
    }

    private function end(PersonalAccessToken $token, string $message): Response
    {
        self::forget($token->id);
        $token->delete();

        return response()->json(['message' => $message], 401);
    }
}
