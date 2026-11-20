<x-app-layout>
    <x-page-header title="Profil Saya" subtitle="Kelola informasi akun dan keamanan akses Anda.">
        <x-slot name="actions">
            <a href="{{ route('appearance.edit') }}" class="btn-secondary">
                <x-icon name="palette" class="h-4 w-4" /> Tampilan & Latar
            </a>
        </x-slot>
    </x-page-header>

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
                <x-user-avatar :user="$user" size="h-24 w-24" text="text-2xl" rounded="rounded-3xl"
                               class="mx-auto shadow-lg shadow-indigo-500/25" />
                <p class="mt-4 text-lg font-bold text-slate-900">{{ $user->name }}</p>
                <p class="text-sm text-slate-500">{{ $user->email }}</p>
                <div class="mt-3 flex justify-center">
                    <x-badge :tone="$user->isOwner() ? 'success' : ($user->hasRole('kasir') ? 'info' : 'warning')">{{ $user->role_label }}</x-badge>
                </div>

                <div class="mt-5 border-t border-slate-100 pt-4 text-left">
                    <form method="POST" action="{{ route('profile.avatar.update') }}" enctype="multipart/form-data"
                          x-data="{ name: '' }" class="space-y-2">
                        @csrf
                        <label for="avatar" class="label">Foto profil</label>
                        <label for="avatar" class="btn-secondary w-full cursor-pointer justify-center">
                            <x-icon name="image" class="h-4 w-4" /> Pilih Foto
                        </label>
                        <input id="avatar" name="avatar" type="file" accept="image/png,image/jpeg,image/webp"
                               class="sr-only" required @change="name = $event.target.files[0]?.name || ''">
                        <p class="truncate text-[11px] text-slate-400" x-text="name || 'PNG, JPG, atau WEBP · maks. 2 MB'"></p>
                        <x-input-error :messages="$errors->get('avatar')" />
                        <button type="submit" class="btn-primary w-full justify-center">
                            <x-icon name="check" class="h-4 w-4" /> Simpan Foto
                        </button>
                    </form>

                    @if ($user->avatar)
                        <form method="POST" action="{{ route('profile.avatar.destroy') }}" class="mt-2">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-ghost w-full justify-center text-rose-600 hover:bg-rose-50/80 hover:text-rose-700">
                                <x-icon name="trash" class="h-4 w-4" /> Hapus Foto
                            </button>
                        </form>
                    @endif
                </div>

                <div class="mt-5 space-y-3 border-t border-slate-100 pt-5 text-left text-xs">
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
