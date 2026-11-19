<?php

use App\Exceptions\BusinessRuleException;
use App\Http\Middleware\CheckSessionActivity;
use App\Http\Middleware\RequirePasswordChange;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // API only: there is no `login` page to redirect guests to. Without this,
        // a request that doesn't send Accept: application/json (e.g. a browser
        // tab) crashes with "Route [login] not defined" instead of a 401.
        $middleware->redirectGuestsTo(fn () => null);

        $middleware->alias([
            'permission' => PermissionMiddleware::class,
            'role' => RoleMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'session.activity' => CheckSessionActivity::class,
            'password.change' => RequirePasswordChange::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // the API always answers JSON (the SPA sends Accept: application/json)
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // a business rule refused the action: { message } with its status (422)
        $exceptions->render(
            fn (BusinessRuleException $e) => response()->json(['message' => $e->getMessage()], $e->status()),
        );

        // a missing permission / role: 403 — the SPA leaves it to the page and
        // only signs out on 401 (no or expired token)
        $exceptions->render(
            fn (UnauthorizedException $e) => response()->json(['message' => 'You do not have permission to do that.'], 403),
        );

        // a record that doesn't exist (route model binding): 404 { message }
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*') && $e->getPrevious() instanceof ModelNotFoundException) {
                return response()->json(['message' => 'Record not found.'], 404);
            }
        });
    })->create();
