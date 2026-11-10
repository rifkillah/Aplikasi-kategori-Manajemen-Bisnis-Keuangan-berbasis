<x-app-layout>
    @php
        $logoUrl = $tenant->logo ? \Illuminate\Support\Facades\Storage::disk('public')->url($tenant->logo) : null;
    @endphp

    <x-page-header title="Pengaturan Bisnis" subtitle="Informasi ini tampil pada invoice, laporan, dan identitas workspace.">
        <x-slot name="actions">
            <a href="{{ route('team.index') }}" class="btn-secondary"><x-icon name="users" class="h-4 w-4" /> Tim & Akses</a>
        </x-slot>
    </x-page-header>

    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data"
          x-data="settingsForm(@js($logoUrl))" class="grid gap-6 xl:grid-cols-3">
        @csrf
        @method('PATCH')

        <div class="space-y-6 xl:col-span-2">
            <div data-reveal class="card">
                <div class="border-b border-slate-100 p-5 sm:p-6">
                    <h2 class="section-title">Identitas bisnis</h2>
                    <p class="muted mt-0.5">Data ini digunakan pada header invoice PDF.</p>
                </div>

                <div class="space-y-5 p-5 sm:p-6">
                    <div>
                        <label for="business_name" class="label">Nama bisnis <span class="text-rose-500">*</span></label>
                        <input id="business_name" name="business_name" type="text" value="{{ old('business_name', $tenant->business_name) }}"
                               required class="input" maxlength="255">
                        <x-input-error :messages="$errors->get('business_name')" class="mt-1.5" />
                    </div>

                    <div>
                        <label for="address" class="label">Alamat</label>
                        <textarea id="address" name="address" rows="3" class="input"
                                  placeholder="Alamat lengkap toko atau kantor...">{{ old('address', $tenant->address) }}</textarea>
                        <x-input-error :messages="$errors->get('address')" class="mt-1.5" />
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="phone" class="label">Telepon / WhatsApp</label>
                            <input id="phone" name="phone" type="text" value="{{ old('phone', $tenant->phone) }}" class="input" placeholder="08xxxxxxxxxx">
                            <x-input-error :messages="$errors->get('phone')" class="mt-1.5" />
                        </div>
                        <div>
                            <label for="email" class="label">Email bisnis</label>
                            <input id="email" name="email" type="email" value="{{ old('email', $tenant->email) }}" class="input" placeholder="halo@bisnis.com">
                            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
                        </div>
                    </div>
                </div>
            </div>

            <div data-reveal class="card">
                <div class="border-b border-slate-100 p-5 sm:p-6">
                    <h2 class="section-title">Invoice & pajak</h2>
                    <p class="muted mt-0.5">Atur format nomor invoice dan tarif pajak default.</p>
                </div>

                <div class="space-y-5 p-5 sm:p-6">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="invoice_prefix" class="label">Prefix invoice <span class="text-rose-500">*</span></label>
                            <input id="invoice_prefix" name="invoice_prefix" type="text"
                                   value="{{ old('invoice_prefix', $tenant->invoice_prefix) }}" required
                                   class="input uppercase" maxlength="12" placeholder="INV">
                            <p class="mt-1 text-[11px] text-slate-400">Contoh hasil: <span class="font-semibold text-slate-600">{{ strtoupper(old('invoice_prefix', $tenant->invoice_prefix)) }}-202611-0001</span></p>
                            <x-input-error :messages="$errors->get('invoice_prefix')" class="mt-1.5" />
                        </div>
                        <div>
                            <label for="tax_percent" class="label">Pajak default (%) <span class="text-rose-500">*</span></label>
                            <input id="tax_percent" name="tax_percent" type="number" min="0" max="100" step="0.01"
                                   value="{{ old('tax_percent', $tenant->tax_percent) }}" required class="input">
                            <p class="mt-1 text-[11px] text-slate-400">Diterapkan otomatis pada transaksi kasir. Isi 0 jika tanpa pajak.</p>
                            <x-input-error :messages="$errors->get('tax_percent')" class="mt-1.5" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="btn-primary"><x-icon name="check" class="h-4 w-4" /> Simpan Pengaturan</button>
                <a href="{{ route('dashboard') }}" class="btn-secondary">Batal</a>
            </div>
        </div>

        <div class="space-y-6">
            <div data-reveal class="card p-5 sm:p-6">
                <h3 class="text-sm font-bold text-slate-900">Logo bisnis</h3>
                <p class="mt-0.5 text-xs text-slate-500">Format PNG, JPG, atau WEBP. Maksimal 2 MB.</p>

                <div class="mt-4 flex flex-col items-center rounded-2xl border-2 border-dashed border-slate-200 p-5">
                    <template x-if="preview">
                        <img :src="preview" alt="Preview logo" class="h-24 w-24 rounded-2xl object-cover ring-1 ring-slate-200">
                    </template>
                    <template x-if="! preview">
                        <div class="flex h-24 w-24 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 text-3xl font-extrabold text-white">
                            {{ mb_strtoupper(mb_substr($tenant->business_name ?? 'K', 0, 1)) }}
                        </div>
                    </template>

                    <label for="logo" class="btn-secondary mt-4 cursor-pointer">
                        <x-icon name="download" class="h-4 w-4 rotate-180" /> Pilih Logo
                        <input id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/webp"
                               class="sr-only" @change="pickLogo($event)">
                    </label>
                    <p class="mt-2 text-[11px] text-slate-400" x-text="fileName || 'Belum ada file dipilih'"></p>
                </div>
                <x-input-error :messages="$errors->get('logo')" class="mt-2" />
            </div>

            <div data-reveal class="card p-5 sm:p-6">
                <h3 class="text-sm font-bold text-slate-900">Pratinjau invoice</h3>
                <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Nomor invoice berikutnya</p>
                    <p class="mt-1 text-sm font-bold text-slate-800">{{ strtoupper(old('invoice_prefix', $tenant->invoice_prefix)) }}-{{ now()->format('Ym') }}-0001</p>
                    <div class="mt-3 space-y-1.5 border-t border-slate-200 pt-3 text-[11px] text-slate-500">
                        <p class="flex justify-between"><span>Subtotal</span><span>Rp 100.000</span></p>
                        <p class="flex justify-between"><span>Pajak {{ (float) old('tax_percent', $tenant->tax_percent) }}%</span><span>Rp {{ number_format(100000 * (float) old('tax_percent', $tenant->tax_percent) / 100, 0, ',', '.') }}</span></p>
                        <p class="flex justify-between font-semibold text-slate-700"><span>Total</span><span>Rp {{ number_format(100000 * (1 + (float) old('tax_percent', $tenant->tax_percent) / 100), 0, ',', '.') }}</span></p>
                    </div>
                </div>
                <p class="mt-3 text-[11px] leading-relaxed text-slate-400">Nomor invoice berurutan otomatis per bulan. Prefix dapat diubah kapan saja tanpa memengaruhi invoice lama.</p>
            </div>

            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4">
                <div class="flex gap-3">
                    <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0 text-amber-600" />
                    <p class="text-[11px] leading-relaxed text-amber-800">Mengubah tarif pajak tidak mengubah transaksi yang sudah tersimpan. Tarif baru berlaku untuk transaksi berikutnya.</p>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
        <script>
            function settingsForm(initialLogo) {
                return {
                    preview: initialLogo,
                    fileName: '',
                    pickLogo(event) {
                        const file = event.target.files[0];
                        if (!file) {
                            this.preview = initialLogo;
                            this.fileName = '';
                            return;
                        }
                        this.fileName = file.name;
                        this.preview = URL.createObjectURL(file);
                    }
                };
            }
        </script>
    @endpush
</x-app-layout>
