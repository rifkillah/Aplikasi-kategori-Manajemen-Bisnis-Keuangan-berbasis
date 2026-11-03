@php
    $isEdit = isset($customer);
    $value = fn (string $field) => old($field, $isEdit ? $customer->{$field} : null);
@endphp

<div class="grid gap-6 xl:grid-cols-3">
    <div class="space-y-6 xl:col-span-2">
        <div class="card">
            <div class="border-b border-slate-100 p-5 sm:p-6">
                <h2 class="section-title">Informasi pelanggan</h2>
                <p class="muted mt-0.5">Data ini akan muncul pada invoice dan riwayat transaksi.</p>
            </div>

            <div class="grid gap-5 p-5 sm:grid-cols-2 sm:p-6">
                <div class="sm:col-span-2">
                    <label for="name" class="label">Nama pelanggan <span class="text-rose-500">*</span></label>
                    <input id="name" name="name" type="text" required value="{{ $value('name') }}"
                           placeholder="Nama lengkap atau nama perusahaan" class="input">
                    <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
                </div>
                <div>
                    <label for="phone" class="label">Nomor telepon</label>
                    <input id="phone" name="phone" type="text" value="{{ $value('phone') }}" placeholder="0812-xxxx-xxxx" class="input">
                    <x-input-error :messages="$errors->get('phone')" class="mt-1.5" />
                </div>
                <div>
                    <label for="email" class="label">Email</label>
                    <input id="email" name="email" type="email" value="{{ $value('email') }}" placeholder="pelanggan@email.com" class="input">
                    <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
                </div>
                <div class="sm:col-span-2">
                    <label for="address" class="label">Alamat</label>
                    <textarea id="address" name="address" rows="3" placeholder="Alamat lengkap" class="input">{{ $value('address') }}</textarea>
                    <x-input-error :messages="$errors->get('address')" class="mt-1.5" />
                </div>
                <div class="sm:col-span-2">
                    <label for="notes" class="label">Catatan internal</label>
                    <textarea id="notes" name="notes" rows="3" placeholder="Preferensi pelanggan, catatan khusus, dll." class="input">{{ $value('notes') }}</textarea>
                    <x-input-error :messages="$errors->get('notes')" class="mt-1.5" />
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row">
            <button type="submit" class="btn-primary">
                <x-icon name="check" class="h-4 w-4" /> {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Pelanggan' }}
            </button>
            <a href="{{ $isEdit ? route('customers.show', $customer) : route('customers.index') }}" class="btn-secondary">Batal</a>
        </div>
    </div>

    <div class="space-y-6">
        <div class="card p-5 sm:p-6">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                <x-icon name="users" class="h-5 w-5" />
            </div>
            <h3 class="mt-4 text-sm font-bold text-slate-900">Kenapa data pelanggan penting?</h3>
            <ul class="mt-3 space-y-2.5 text-xs leading-relaxed text-slate-500">
                <li class="flex gap-2"><x-icon name="check-simple" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-emerald-500" /> Invoice tercetak dengan identitas pelanggan.</li>
                <li class="flex gap-2"><x-icon name="check-simple" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-emerald-500" /> Melacak total belanja dan transaksi terakhir.</li>
                <li class="flex gap-2"><x-icon name="check-simple" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-emerald-500" /> Memudahkan program loyalitas di masa depan.</li>
            </ul>
        </div>

        @if ($isEdit)
            <div class="rounded-2xl border border-rose-200 bg-rose-50/50 p-5">
                <h3 class="text-sm font-bold text-rose-900">Hapus pelanggan</h3>
                <p class="mt-1 text-xs text-rose-700">Transaksi lama tetap tersimpan tanpa nama pelanggan.</p>
                <form method="POST" action="{{ route('customers.destroy', $customer) }}" class="mt-3"
                      onsubmit="return confirm('Hapus pelanggan ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger btn-sm w-full"><x-icon name="trash" class="h-3.5 w-3.5" /> Hapus Pelanggan</button>
                </form>
            </div>
        @endif
    </div>
</div>
