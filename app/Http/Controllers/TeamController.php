<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class TeamController extends Controller
{
    public function index(): View
    {
        $users = User::with('roles')
            ->where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name')
            ->get();

        $roles = Role::whereIn('name', ['owner', 'kasir', 'gudang'])->orderBy('name')->get();

        return view('team.index', compact('users', 'roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')],
            'role' => ['required', 'in:owner,kasir,gudang'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $user = User::create([
                'tenant_id' => $request->user()->tenant_id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'is_active' => true,
            ]);

            $user->assignRole($validated['role']);
        });

        return redirect()->route('team.index')->with('success', 'Anggota tim berhasil ditambahkan.');
    }

    public function update(Request $request, User $member): RedirectResponse
    {
        abort_if($member->tenant_id !== $request->user()->tenant_id, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($member->id)],
            'role' => ['required', 'in:owner,kasir,gudang'],
            'is_active' => ['boolean'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        if ($member->id === $request->user()->id && $validated['role'] !== 'owner') {
            return back()->with('error', 'Anda tidak dapat mengubah role akun Anda sendiri.');
        }

        if ($member->id === $request->user()->id && ! $request->boolean('is_active')) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        if ($member->hasRole('owner') && $validated['role'] !== 'owner' && $this->ownerCount() <= 1) {
            return back()->with('error', 'Bisnis harus memiliki minimal satu owner.');
        }

        DB::transaction(function () use ($member, $validated, $request) {
            $data = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'is_active' => $request->boolean('is_active'),
            ];

            if (! empty($validated['password'])) {
                $data['password'] = $validated['password'];
            }

            $member->update($data);
            $member->syncRoles([$validated['role']]);
        });

        return redirect()->route('team.index')->with('success', 'Data anggota tim berhasil diperbarui.');
    }

    public function destroy(Request $request, User $member): RedirectResponse
    {
        abort_if($member->tenant_id !== $request->user()->tenant_id, 404);

        if ($member->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($member->hasRole('owner') && $this->ownerCount() <= 1) {
            return back()->with('error', 'Bisnis harus memiliki minimal satu owner.');
        }

        $member->delete();

        return redirect()->route('team.index')->with('success', 'Anggota tim berhasil dihapus.');
    }

    private function ownerCount(): int
    {
        return User::where('tenant_id', auth()->user()->tenant_id)
            ->whereHas('roles', fn ($query) => $query->where('name', 'owner'))
            ->count();
    }
}
