<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Immediately terminate sessions belonging to deactivated accounts.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Only an explicit false means disabled. Rows created before the column
        // existed (null) must not lock their owners out of the application.
        if ($user && $user->is_active === false) {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Akun Anda dinonaktifkan. Hubungi owner bisnis Anda.']);
        }

        return $next($request);
    }
}
