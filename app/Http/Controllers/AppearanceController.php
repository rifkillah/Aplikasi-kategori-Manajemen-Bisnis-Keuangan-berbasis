<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AppearanceController extends Controller
{
    public function edit(Request $request): View
    {
        return view('appearance.edit', [
            'user' => $request->user(),
            'presets' => config('appearance.presets'),
            'modes' => config('appearance.modes'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'theme_mode' => ['required', Rule::in(array_keys(config('appearance.modes')))],
            'background_preset' => ['required', Rule::in([...array_keys(config('appearance.presets')), 'custom'])],
            'wallpaper' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            'remove_wallpaper' => ['nullable', 'boolean'],
        ], [
            'wallpaper.max' => 'Ukuran wallpaper maksimal 4 MB.',
            'wallpaper.image' => 'Wallpaper harus berupa gambar.',
        ]);

        $wallpaperPath = $user->background_image;
        $preset = $validated['background_preset'];

        if ($request->boolean('remove_wallpaper') && $wallpaperPath) {
            Storage::disk('public')->delete($wallpaperPath);
            $wallpaperPath = null;
            $preset = $preset === 'custom' ? 'aurora' : $preset;
        }

        if ($request->hasFile('wallpaper')) {
            if ($wallpaperPath) {
                Storage::disk('public')->delete($wallpaperPath);
            }

            $wallpaperPath = $request->file('wallpaper')->store('wallpapers', 'public');
            $preset = 'custom';
        }

        // "custom" only makes sense while a wallpaper exists.
        if ($preset === 'custom' && ! $wallpaperPath) {
            $preset = 'aurora';
        }

        $user->update([
            'theme_mode' => $validated['theme_mode'],
            'background_preset' => $preset,
            'background_image' => $wallpaperPath,
        ]);

        return redirect()
            ->route('appearance.edit')
            ->with('success', 'Tampilan berhasil diperbarui.');
    }

    /**
     * Quick switch from the header: system → light → dark → system.
     */
    public function theme(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'theme_mode' => ['required', Rule::in(array_keys(config('appearance.modes')))],
        ]);

        $request->user()->update(['theme_mode' => $validated['theme_mode']]);

        return response()->json(['theme_mode' => $validated['theme_mode']]);
    }
}
