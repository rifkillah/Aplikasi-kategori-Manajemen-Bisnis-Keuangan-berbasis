<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Stronger passwords for real deployments; local/tests stay ergonomic.
        Password::defaults(fn () => $this->app->environment('production')
            ? Password::min(12)->mixedCase()->numbers()
            : Password::min(8));

        // Pin absolute URLs (password reset emails, signed links, PDF links) to
        // APP_URL so a spoofed Host header cannot redirect them to another site.
        if ($this->app->environment('production') && ($appUrl = config('app.url'))) {
            URL::forceRootUrl($appUrl);

            if (str_starts_with($appUrl, 'https://')) {
                URL::forceScheme('https');
            }
        }

        View::composer('layouts.app', function ($view) {
            $user = auth()->user();

            $view->with('currentTenant', $user?->tenant);
            $view->with('unreadNotificationCount', $user ? $user->unreadNotifications()->count() : 0);
        });
    }
}
