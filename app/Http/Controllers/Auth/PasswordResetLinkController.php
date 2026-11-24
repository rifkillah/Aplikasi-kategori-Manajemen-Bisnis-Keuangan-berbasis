<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // The response is intentionally identical whether or not the address
        // exists, so this endpoint cannot be used to enumerate accounts. The
        // per-route throttle limits mailbox flooding.
        Password::sendResetLink($request->only('email'));

        return back()->with(
            'status',
            'Jika email tersebut terdaftar, tautan reset kata sandi telah dikirim.'
        );
    }
}
