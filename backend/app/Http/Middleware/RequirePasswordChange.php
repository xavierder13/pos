<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * "Require password change at next sign-in": until the user sets a new
 * password (PUT /auth/profile), every other API call answers 403 with
 * `code: password_change_required` — the SPA shows only its Change Password
 * screen. Applied to every route outside /auth.
 */
class RequirePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->must_change_password) {
            return response()->json([
                'message' => 'You must change your password before continuing.',
                'code' => 'password_change_required',
            ], 403);
        }

        return $next($request);
    }
}
