<x-app-layout>
    <x-page-header title="Profil Saya" subtitle="Kelola informasi akun dan keamanan akses Anda." />

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <div data-reveal class="card">
                <div class="border-b border-slate-100 p-5 sm:p-6">
                    <h2 class="section-title">Informasi profil</h2>
                    <p class="muted mt-0.5">Perbarui nama dan alamat email akun Anda.</p>
                </div>
                <div class="p-5 sm:p-6">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div data-reveal class="card">
                <div class="border-b border-slate-100 p-5 sm:p-6">
                    <h2 class="section-title">Ganti password</h2>
                    <p class="muted mt-0.5">Gunakan password yang panjang dan unik agar akun tetap aman.</p>
                </div>
                <div class="p-5 sm:p-6">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div data-reveal class="card border-rose-100">
                <div class="border-b border-rose-100 bg-rose-50/50 p-5 sm:p-6">
                    <h2 class="text-base font-bold text-rose-700">Hapus akun</h2>
                    <p class="mt-0.5 text-sm text-rose-600/80">Tindakan ini permanen dan tidak dapat dibatalkan.</p>
                </div>
                <div class="p-5 sm:p-6">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div data-reveal class="card p-5 text-center sm:p-6">
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 text-2xl font-extrabold text-white shadow-lg shadow-indigo-500/20">
                    {{ $user->initials }}
                </div>
                <p class="mt-4 text-lg font-bold text-slate-900">{{ $user->name }}</p>
                <p class="text-sm text-slate-500">{{ $user->email }}</p>
                <div class="mt-3 flex justify-center">
                    <x-badge :tone="$user->isOwner() ? 'success' : ($user->hasRole('kasir') ? 'info' : 'warning')">{{ $user->role_label }}</x-badge>
                </div>

                <div class="mt-6 space-y-3 border-t border-slate-100 pt-5 text-left text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Bisnis</span>
                        <span class="font-semibold text-slate-700">{{ $user->tenant?->business_name ?? '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Status akun</span>
                        <span class="font-semibold {{ $user->is_active ? 'text-emerald-600' : 'text-rose-600' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Bergabung</span>
                        <span class="font-semibold text-slate-700">{{ id_date($user->created_at) }}</span>
                    </div>
                </div>
            </div>

            <div data-reveal class="card p-5 sm:p-6">
                <h3 class="text-sm font-bold text-slate-900">Hak akses Anda</h3>
                <ul class="mt-3 space-y-2.5 text-xs text-slate-500">
                    <li class="flex items-center gap-2">
                        <x-icon name="cart" class="h-4 w-4 shrink-0 text-slate-400" />
                        <span>{{ $user->canOperateCashier() ? 'Dapat menggunakan kasir & transaksi' : 'Tidak memiliki akses kasir' }}</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <x-icon name="cube" class="h-4 w-4 shrink-0 text-slate-400" />
                        <span>{{ $user->canManageInventory() ? 'Dapat mengelola produk & stok' : 'Tidak memiliki akses inventaris' }}</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <x-icon name="chart" class="h-4 w-4 shrink-0 text-slate-400" />
                        <span>{{ $user->isOwner() ? 'Dapat melihat laporan & keuangan' : 'Laporan hanya untuk owner' }}</span>
                    </li>
                </ul>
                @if ($user->isOwner())
                    <a href="{{ route('team.index') }}" class="btn-secondary mt-4 w-full justify-center">
                        <x-icon name="users" class="h-4 w-4" /> Kelola Tim
                    </a>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
