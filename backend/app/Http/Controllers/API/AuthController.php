<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Middleware\CheckSessionActivity;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Models\SessionSetting;
use App\Models\User;
use App\Services\Administration\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Bearer-token auth for the React SPA (Sanctum personal access tokens).
 *
 * login / init answer the payload the SPA's authStore expects:
 *   { user, user_roles, user_permissions, session }  (+ access_token on login)
 * `session` = Session Settings (idle time, session duration) for the SPA's
 * idle timer; CheckSessionActivity enforces them.
 */
class AuthController extends Controller
{
    public function __construct(private readonly UserService $users) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->input('email'))->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            return response()->json(['message' => 'Invalid email or password.'], 401);
        }

        if (! $user->active) {
            return response()->json(['message' => 'Your account is inactive. Please contact the administrator.'], 403);
        }

        $user->forceFill(['last_login_at' => now()])->saveQuietly();

        // the token's expiry is only a ceiling: the session duration in force
        // (Session Settings) is checked from its creation by CheckSessionActivity
        $token = $user->createToken('spa', ['*'], now()->addMinutes(SessionSetting::LIFETIME_MAX));
        CheckSessionActivity::touch($token->accessToken->id);

        return response()->json(['access_token' => $token->plainTextToken] + $this->payload($user));
    }

    public function init(Request $request): JsonResponse
    {
        return response()->json($this->payload($request->user()));
    }

    // GET /auth/ping → { ok } (session.activity does the work; X-Session-Check: passive = check only)
    public function ping(): JsonResponse
    {
        return response()->json(['ok' => true]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();
        if ($token && method_exists($token, 'delete')) {
            if ($token->getKey()) {
                CheckSessionActivity::forget($token->getKey());
            }
            $token->delete();
        }

        return response()->json(['message' => 'Logged out.']);
    }

    // PUT /auth/profile — own name / password (a new password signs out the other sessions)
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->users->updateProfile($request->user(), $request->validated());

        return response()->json(['message' => 'Profile updated.'] + $this->payload($user));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(User $user): array
    {
        return [
            'user' => $user->only(['id', 'name', 'email']) + ['must_change_password' => (bool) $user->must_change_password],
            'user_roles' => $user->getRoleNames()->values(),
            'user_permissions' => $user->getAllPermissions()->pluck('name')->sort()->values(),
            'session' => SessionSetting::values(),
        ];
    }
}
