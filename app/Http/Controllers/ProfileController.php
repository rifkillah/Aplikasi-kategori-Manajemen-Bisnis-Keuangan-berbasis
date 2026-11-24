<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // Only the two editable columns; current_password is validation-only.
        $request->user()->fill($request->safe()->only(['name', 'email']));

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the user's profile photo.
     */
    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ], [
            'avatar.required' => 'Pilih foto profil terlebih dahulu.',
            'avatar.image' => 'Foto profil harus berupa gambar.',
            'avatar.mimes' => 'Format foto harus PNG, JPG, atau WEBP.',
            'avatar.max' => 'Ukuran foto maksimal 2 MB.',
        ]);

        $user = $request->user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->update([
            'avatar' => $request->file('avatar')->store('avatars', 'public'),
        ]);

        return back()->with('success', 'Foto profil berhasil diperbarui.');
    }

    /**
     * Remove the user's profile photo.
     */
    public function destroyAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->update(['avatar' => null]);
        }

        return back()->with('success', 'Foto profil berhasil dihapus.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Never let the last owner delete themselves and orphan the tenant.
        if ($user->hasRole('owner') && $user->tenant_id) {
            $otherOwners = User::query()
                ->where('tenant_id', $user->tenant_id)
                ->whereKeyNot($user->id)
                ->role('owner')
                ->count();

            if ($otherOwners === 0) {
                return Redirect::route('profile.edit')->withErrors(
                    ['password' => 'Anda adalah satu-satunya owner. Tambahkan owner lain sebelum menghapus akun.'],
                    'userDeletion'
                );
            }
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
