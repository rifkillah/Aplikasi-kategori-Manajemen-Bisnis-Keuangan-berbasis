<?php

use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\Middleware\AuthenticateSession;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Proxy trust lives in config/trustedproxy.php so it is read after the
        // environment is loaded. Set TRUSTED_PROXIES only when a real proxy
        // terminates TLS in front of the app.

        // Harden every response, including error pages and file downloads.
        $middleware->append(SecurityHeaders::class);

        // One group for every authenticated area. AuthenticateSession invalidates
        // other devices as soon as the account password changes, and the active
        // check cuts off users who were deactivated while already signed in.
        $middleware->group('authenticated', [
            Authenticate::class,
            AuthenticateSession::class,
            EnsureUserIsActive::class,
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
