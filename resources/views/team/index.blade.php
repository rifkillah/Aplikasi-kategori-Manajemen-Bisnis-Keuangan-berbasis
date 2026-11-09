<x-app-layout>
    @php
        $ownerCount = $users->filter(fn ($member) => $member->hasRole('owner'))->count();
        $cashierCount = $users->filter(fn ($member) => $member->hasRole('kasir'))->count();
        $warehouseCount = $users->filter(fn ($member) => $member->hasRole('gudang'))->count();
    @endphp

    <x-page-header title="Tim & Akses" subtitle="Kelola anggota tim beserta hak akses mereka di bisnis Anda.">
        <x-slot name="actions">
            <a href="{{ route('settings.edit') }}" class="btn-secondary"><x-icon name="settings" class="h-4 w-4" /> Pengaturan Bisnis</a>
        </x-slot>
    </x-page-header>

    <div class="mb-6 grid gap-4 sm:grid-cols-4">
        <x-stat-card label="Total anggota" :value="$users->count().' orang'" icon="users" tone="indigo" hint="Termasuk akun Anda" />
        <x-stat-card label="Owner" :value="$ownerCount.' orang'" icon="shield" tone="emerald" hint="Akses penuh" />
        <x-stat-card label="Kasir" :value="$cashierCount.' orang'" icon="cart" tone="sky" hint="POS & transaksi" />
        <x-stat-card label="Staff gudang" :value="$warehouseCount.' orang'" icon="cube" tone="amber" hint="Produk & stok" />
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        {{-- Members --}}
        <div class="card overflow-hidden xl:col-span-2">
            <div class="border-b border-slate-100 p-5 sm:p-6">
                <h2 class="section-title">Anggota tim</h2>
                <p class="muted mt-0.5">Semua akun yang dapat mengakses workspace ini.</p>
            </div>

            {{-- Mobile cards --}}
            <div class="space-y-3 p-4 md:hidden">
                @foreach ($users as $member)
                    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="flex items-start gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-[11px] font-bold text-white">
                                {{ $member->initials }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-bold text-slate-800">
                                    {{ $member->name }}
                                    @if ($member->id === auth()->id())
                                        <span class="ml-1 rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold text-slate-500">Anda</span>
                                    @endif
                                </p>
                                <p class="truncate text-[11px] text-slate-400">{{ $member->email }}</p>
                            </div>
                            <x-badge :tone="$member->hasRole('owner') ? 'success' : ($member->hasRole('kasir') ? 'info' : 'warning')">
                                {{ $member->role_label }}
                            </x-badge>
                        </div>

                        <div class="mt-3 flex items-center justify-between gap-2 border-t border-slate-100 pt-3">
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium {{ $member->is_active ? 'text-emerald-600' : 'text-slate-400' }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $member->is_active ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                                {{ $member->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                            <div class="flex items-center gap-1">
                                <button type="button" @click="$dispatch('open-edit-member', {{ $member->id }})"
                                        class="btn-secondary btn-sm">
                                    <x-icon name="edit" class="h-3.5 w-3.5" /> Edit
                                </button>
                                @if ($member->id !== auth()->id())
                                    <form method="POST" action="{{ route('team.destroy', $member) }}"
                                          onsubmit="return confirm('Hapus {{ $member->name }} dari tim? Akun akan kehilangan akses.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg p-2 text-slate-400 hover:bg-rose-50 hover:text-rose-600">
                                            <x-icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="hidden table-wrap md:block">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Anggota</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-900/[0.06]">
                        @foreach ($users as $member)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-[11px] font-bold text-white">
                                            {{ $member->initials }}
                                        </span>
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold text-slate-800">
                                                {{ $member->name }}
                                                @if ($member->id === auth()->id())
                                                    <span class="ml-1 rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold text-slate-500">Anda</span>
                                                @endif
                                            </p>
                                            <p class="truncate text-[11px] text-slate-400">{{ $member->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <x-badge :tone="$member->hasRole('owner') ? 'success' : ($member->hasRole('kasir') ? 'info' : 'warning')">
                                        {{ $member->role_label }}
                                    </x-badge>
                                </td>
                                <td>
                                    @if ($member->is_active)
                                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-600">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-400">
                                            <span class="h-1.5 w-1.5 rounded-full bg-slate-300"></span> Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button"
                                                @click="$dispatch('open-edit-member', {{ $member->id }})"
                                                title="Edit anggota"
                                                class="rounded-lg p-2 text-slate-400 transition hover:bg-indigo-50 hover:text-indigo-600">
                                            <x-icon name="edit" class="h-4 w-4" />
                                        </button>
                                        @if ($member->id !== auth()->id())
                                            <form method="POST" action="{{ route('team.destroy', $member) }}"
                                                  onsubmit="return confirm('Hapus {{ $member->name }} dari tim? Akun akan kehilangan akses.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Hapus anggota"
                                                        class="rounded-lg p-2 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600">
                                                    <x-icon name="trash" class="h-4 w-4" />
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Add member --}}
        <div class="card h-fit">
            <div class="border-b border-slate-100 p-5 sm:p-6">
                <h2 class="section-title">Tambah anggota</h2>
                <p class="muted mt-0.5">Buat akun baru untuk anggota tim.</p>
            </div>

            <form method="POST" action="{{ route('team.store') }}" class="space-y-4 p-5 sm:p-6">
                @csrf

                <div>
                    <label for="name" class="label">Nama lengkap <span class="text-rose-500">*</span></label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required class="input" placeholder="Nama anggota">
                    <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
                </div>

                <div>
                    <label for="email" class="label">Email <span class="text-rose-500">*</span></label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required class="input" placeholder="nama@bisnis.com">
                    <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
                </div>

                <div>
                    <label for="role" class="label">Role <span class="text-rose-500">*</span></label>
                    <select id="role" name="role" required class="input">
                        <option value="" disabled @selected(! old('role'))>Pilih role...</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}" @selected(old('role') === $role->name)>{{ role_label($role->name) }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-[11px] text-slate-400">Owner: akses penuh · Kasir: POS & transaksi · Gudang: produk & stok.</p>
                    <x-input-error :messages="$errors->get('role')" class="mt-1.5" />
                </div>

                <div>
                    <label for="password" class="label">Password <span class="text-rose-500">*</span></label>
                    <input id="password" name="password" type="password" required class="input" placeholder="Minimal 8 karakter">
                    <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
                </div>

                <div>
                    <label for="password_confirmation" class="label">Konfirmasi password <span class="text-rose-500">*</span></label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required class="input" placeholder="Ulangi password">
                </div>

                <button type="submit" class="btn-primary w-full justify-center">
                    <x-icon name="plus" class="h-4 w-4" /> Tambah Anggota
                </button>
            </form>
        </div>
    </div>

    {{-- Edit modal --}}
    <div x-data="teamEditor(@js($users->map(fn ($member) => [
            'id' => $member->id,
            'name' => $member->name,
            'email' => $member->email,
            'role' => $member->getRoleNames()->first(),
            'is_active' => $member->is_active,
            'update_url' => route('team.update', $member),
        ])))"
         @open-edit-member.window="open($event.detail)"
         x-show="openModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto px-4 py-6">
        <div x-show="openModal" x-transition.opacity @click="openModal = false" class="fixed inset-0 bg-slate-950/40 backdrop-blur-md"></div>

        <div x-show="openModal" x-transition
             class="relative mx-auto w-full max-w-lg overflow-hidden rounded-[28px] border border-white/70 bg-white/85 shadow-[0_40px_100px_-30px_rgba(15,23,42,0.6)] backdrop-blur-2xl">
            <div class="flex items-center justify-between border-b border-white/60 px-4 py-4 sm:px-6">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Edit anggota</h3>
                    <p class="text-xs text-slate-500" x-text="form.email"></p>
                </div>
                <button type="button" @click="openModal = false" class="rounded-full p-1.5 text-slate-400 transition hover:bg-white/70 hover:text-slate-600">
                    <x-icon name="x" class="h-5 w-5" />
                </button>
            </div>

            <form method="POST" :action="form.update_url" class="max-h-[75vh] space-y-4 overflow-y-auto p-4 sm:p-6">
                @csrf
                @method('PATCH')

                <div>
                    <label class="label">Nama lengkap <span class="text-rose-500">*</span></label>
                    <input name="name" type="text" x-model="form.name" required class="input">
                </div>

                <div>
                    <label class="label">Email <span class="text-rose-500">*</span></label>
                    <input name="email" type="email" x-model="form.email" required class="input">
                </div>

                <div>
                    <label class="label">Role <span class="text-rose-500">*</span></label>
                    <select name="role" x-model="form.role" required class="input">
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}">{{ role_label($role->name) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="label">Password baru</label>
                    <input name="password" type="password" class="input" placeholder="Kosongkan jika tidak diubah">
                    <p class="mt-1 text-[11px] text-slate-400">Minimal 8 karakter. Hanya diisi jika ingin mengganti password.</p>
                </div>

                <div>
                    <label class="label">Konfirmasi password baru</label>
                    <input name="password_confirmation" type="password" class="input" placeholder="Ulangi password baru">
                </div>

                <label class="flex items-center gap-2.5 rounded-2xl border border-white/70 bg-white/50 p-3 backdrop-blur-xl">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" x-model="form.is_active"
                           class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm font-medium text-slate-700">Akun aktif (dapat masuk)</span>
                </label>

                <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">
                    <button type="button" @click="openModal = false" class="btn-secondary">Batal</button>
                    <button type="submit" class="btn-primary"><x-icon name="check" class="h-4 w-4" /> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function teamEditor(members) {
                return {
                    members: members,
                    openModal: false,
                    form: { name: '', email: '', role: 'kasir', is_active: true, update_url: '' },
                    open(id) {
                        const member = this.members.find(item => item.id === id);
                        if (!member) return;
                        this.form = { ...member, is_active: Boolean(member.is_active) };
                        this.openModal = true;
                    }
                };
            }
        </script>
    @endpush
</x-app-layout>
