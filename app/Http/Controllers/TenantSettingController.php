<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TenantSettingController extends Controller
{
    public function edit(): View
    {
        $tenant = auth()->user()->tenant;

        return view('settings.edit', compact('tenant'));
    }

    public function update(Request $request): RedirectResponse
    {
        $tenant = $request->user()->tenant;

        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'invoice_prefix' => ['required', 'string', 'max:12', 'regex:/^[A-Za-z0-9-]+$/'],
            'tax_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ], [
            'invoice_prefix.regex' => 'Prefix invoice hanya boleh berisi huruf, angka, dan tanda hubung.',
        ]);

        if ($request->hasFile('logo')) {
            if ($tenant->logo) {
                Storage::disk('public')->delete($tenant->logo);
            }

            $validated['logo'] = $request->file('logo')->store('tenant-logos', 'public');
        }

        $tenant->update($validated);

        return redirect()->route('settings.edit')->with('success', 'Pengaturan bisnis berhasil disimpan.');
    }
}
