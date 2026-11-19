<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Middleware\CheckSessionActivity;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;

/**
 * Forgot / reset password for the SPA. The email links to the SPA's
 * /reset-password page (config app.frontend_url — AppServiceProvider).
 */
class PasswordResetController extends Controller
{
    // POST /auth/forgot-password { email } → always the same answer (doesn't reveal which emails exist)
    public function forgot(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $user = User::where('email', $request->input('email'))->where('active', true)->first();
        if ($user) {
            Password::sendResetLink(['email' => $user->email]);
        }

        return response()->json(['message' => 'If an account exists for that email, a reset link is on its way.']);
    }

    // POST /auth/reset-password { token, email, password, password_confirmation } → { message }; 422 for a bad / expired link
    public function reset(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => $password, 'must_change_password' => false])->save();
                // signed out everywhere
                $user->tokens()->each(function ($token) {
                    CheckSessionActivity::forget($token->id);
                    $token->delete();
                });
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json(['message' => 'This reset link is invalid or has expired. Ask for a new one.'], 422);
        }

        return response()->json(['message' => 'Your password has been reset. Sign in with your new password.']);
    }
}
