<?php

namespace App\Providers;

use App\Support\Rbac;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Product rule: the Administrator can do every action on every
        // feature. Covers spatie's `permission:` middleware (it checks via
        // the gate), can() and policies. Services with ownership / status
        // rules must let the Administrator through too.
        Gate::before(fn ($user) => $user->hasRole(Rbac::ADMINISTRATOR) ? true : null);

        // sign-in attempts per email + IP
        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(10)
            ->by(strtolower((string) $request->input('email')).'|'.$request->ip()));

        // reset-password emails link to the SPA's page, not a Laravel route
        ResetPassword::createUrlUsing(fn ($user, string $token) => rtrim(config('app.frontend_url'), '/')
            .'/reset-password?token='.$token.'&email='.urlencode($user->getEmailForPasswordReset()));

        // forgot-password requests per email + IP
        RateLimiter::for('password-reset', fn (Request $request) => Limit::perMinute(5)
            ->by(strtolower((string) $request->input('email')).'|'.$request->ip()));

        // outside production an N+1 (lazy loading in a loop) throws — eager load with ->with()
        Model::preventLazyLoading(! $this->app->isProduction());
    }
}
