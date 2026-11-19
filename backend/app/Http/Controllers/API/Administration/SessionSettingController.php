<?php

namespace App\Http\Controllers\API\Administration;

use App\Http\Controllers\Controller;
use App\Models\SessionSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// Administration → Session Settings (session-setting-edit)
class SessionSettingController extends Controller
{
    // GET /session-settings → { data: { idle_timeout_minutes, session_lifetime_minutes }, limits: {...} }
    public function show(): JsonResponse
    {
        return response()->json(['data' => SessionSetting::values(), 'limits' => self::limits()]);
    }

    // PUT /session-settings { idle_timeout_minutes, session_lifetime_minutes } → same; applies to every session at once
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'idle_timeout_minutes' => ['required', 'integer', 'min:'.SessionSetting::IDLE_MIN, 'max:'.SessionSetting::IDLE_MAX],
            'session_lifetime_minutes' => ['required', 'integer', 'min:'.SessionSetting::LIFETIME_MIN, 'max:'.SessionSetting::LIFETIME_MAX, 'gte:idle_timeout_minutes'],
        ], ['session_lifetime_minutes.gte' => 'The session duration must be at least the idle time.']);

        SessionSetting::current()->update($data);

        return response()->json(['message' => 'Session settings saved.', 'data' => SessionSetting::values(), 'limits' => self::limits()]);
    }

    /**
     * @return array<string, int>
     */
    private static function limits(): array
    {
        return [
            'idle_min' => SessionSetting::IDLE_MIN, 'idle_max' => SessionSetting::IDLE_MAX,
            'lifetime_min' => SessionSetting::LIFETIME_MIN, 'lifetime_max' => SessionSetting::LIFETIME_MAX,
        ];
    }
}
