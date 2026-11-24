<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Vite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Attach hardening headers to every response and set the CSP nonce that
     *
     * @vite and the inline bootstrap scripts use.
     *
     * Alpine.js evaluates expressions with the Function constructor, so the
     * policy still needs 'unsafe-eval'. It does not need 'unsafe-inline' for
     * scripts anymore: every inline <script> carries the per-request nonce.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $vite = app(Vite::class);
        $nonce = $vite->useCspNonce();
        View::share('cspNonce', $nonce);

        $response = $next($request);

        $scriptSources = ["'self'", "'nonce-{$nonce}'", "'unsafe-eval'"];
        $styleSources = ["'self'", "'unsafe-inline'"];
        $connectSources = ["'self'"];
        $fontSources = ["'self'", 'data:'];

        // Only while the Vite dev server is running (local development).
        if ($vite->isRunningHot()) {
            $hotUrl = trim((string) @file_get_contents($vite->hotFile()));
            $parts = parse_url($hotUrl);
            $origin = is_array($parts) && isset($parts['host'])
                ? ($parts['scheme'] ?? 'http').'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '')
                : '';

            if ($origin !== '') {
                $wsOrigin = preg_replace('#^http#', 'ws', $origin);

                $scriptSources[] = $origin;
                $styleSources[] = $origin;
                $connectSources[] = $origin;
                $connectSources[] = $wsOrigin;
                $fontSources[] = $origin;
            }
        }

        $policy = [
            "default-src 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "frame-ancestors 'none'",
            "form-action 'self'",
            'img-src '."'self' data: blob:",
            'font-src '.implode(' ', array_unique($fontSources)),
            'style-src '.implode(' ', array_unique($styleSources)),
            'script-src '.implode(' ', array_unique($scriptSources)),
            'connect-src '.implode(' ', array_unique($connectSources)),
            "manifest-src 'self'",
            "media-src 'self'",
            "worker-src 'self'",
        ];

        if ($request->secure()) {
            $policy[] = 'upgrade-insecure-requests';
        }

        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=(), usb=(), browsing-topics=()',
            'Cross-Origin-Opener-Policy' => 'same-origin',
            'Cross-Origin-Resource-Policy' => 'same-origin',
            'X-Permitted-Cross-Domain-Policies' => 'none',
            'Content-Security-Policy' => implode('; ', $policy),
        ];

        if ($request->secure()) {
            // No includeSubDomains: a custom domain may host unrelated
            // subdomains that are not ready for HTTPS-only yet.
            $headers['Strict-Transport-Security'] = 'max-age=31536000';
        }

        if ($request->user()) {
            // Authenticated pages, JSON endpoints, PDFs and CSV exports must
            // never be stored by a shared browser cache or a proxy.
            $headers['Cache-Control'] = 'no-store, private';
            $headers['Pragma'] = 'no-cache';
            $headers['X-Robots-Tag'] = 'noindex, nofollow, noarchive';
        }

        foreach ($headers as $name => $value) {
            $response->headers->set($name, $value);
        }

        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        // PHP's SAPI adds X-Powered-By outside the Symfony header bag, so the
        // framework-level removal above is not enough on its own.
        if (! headers_sent()) {
            header_remove('X-Powered-By');
            header_remove('Server');
        }

        return $response;
    }
}
