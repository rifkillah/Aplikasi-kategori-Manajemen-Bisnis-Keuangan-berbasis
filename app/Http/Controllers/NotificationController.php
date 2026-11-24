<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()->notifications()->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    public function read(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return redirect()->to($this->safeRedirectUrl($request, $notification->data['url'] ?? null));
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    /**
     * Only allow destinations on this application. Notification payloads are
     * stored in the database, so treating their URL as trusted would turn a
     * future data-writing bug into an open redirect.
     */
    private function safeRedirectUrl(Request $request, mixed $url): string
    {
        $fallback = route('notifications.index');

        if (! is_string($url) || $url === '') {
            return $fallback;
        }

        // Protocol-relative URLs ("//evil.test") are not local paths.
        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return $url;
        }

        $parts = parse_url($url);

        if ($parts === false || ! isset($parts['host'])) {
            return $fallback;
        }

        $scheme = strtolower($parts['scheme'] ?? 'https');

        if (! in_array($scheme, ['http', 'https'], true) || strcasecmp($parts['host'], $request->getHost()) !== 0) {
            return $fallback;
        }

        $path = $parts['path'] ?? '/';

        return $path.(isset($parts['query']) ? '?'.$parts['query'] : '');
    }
}
