<x-app-layout>
    <x-page-header title="Catat Keuangan" subtitle="Tambahkan pemasukan lain atau pengeluaran operasional bisnis Anda.">
        <x-slot name="actions">
            <a href="{{ route('finance.index') }}" class="btn-secondary"><x-icon name="arrow-left" class="h-4 w-4" /> Kembali</a>
        </x-slot>
    </x-page-header>

    <form method="POST" action="{{ route('finance.store') }}" x-data="financeForm()" class="grid gap-6 xl:grid-cols-3">
        @csrf

        <div class="space-y-6 xl:col-span-2">
            <div class="card">
                <div class="border-b border-slate-100 p-5 sm:p-6">
                    <h2 class="section-title">Detail catatan</h2>
                    <p class="muted mt-0.5">Pilih jenis catatan lalu lengkapi informasinya.</p>
                </div>

                <div class="space-y-5 p-5 sm:p-6">
                    <div>
                        <span class="label">Jenis catatan <span class="text-rose-500">*</span></span>
                        <input type="hidden" name="type" :value="type">
                        <div class="grid gap-2 sm:grid-cols-2">
                            <button type="button" @click="type = 'income'"
                                    class="flex items-center gap-3 rounded-xl border p-3.5 text-left transition"
                                    :class="type === 'income' ? 'border-emerald-500 bg-emerald-50 ring-1 ring-emerald-500' : 'border-slate-200 hover:bg-slate-50'">
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl"
                                      :class="type === 'income' ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-500'">
                                    <x-icon name="trending-up" class="h-5 w-5" />
                                </span>
                                <span>
                                    <span class="block text-sm font-bold" :class="type === 'income' ? 'text-emerald-700' : 'text-slate-700'">Pemasukan</span>
                                    <span class="block text-[11px] text-slate-500">Pendapatan di luar penjualan kasir</span>
                                </span>
                            </button>
                            <button type="button" @click="type = 'expense'"
                                    class="flex items-center gap-3 rounded-xl border p-3.5 text-left transition"
                                    :class="type === 'expense' ? 'border-rose-500 bg-rose-50 ring-1 ring-rose-500' : 'border-slate-200 hover:bg-slate-50'">
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl"
                                      :class="type === 'expense' ? 'bg-rose-100 text-rose-600' : 'bg-slate-100 text-slate-500'">
                                    <x-icon name="trending-down" class="h-5 w-5" />
                                </span>
                                <span>
                                    <span class="block text-sm font-bold" :class="type === 'expense' ? 'text-rose-700' : 'text-slate-700'">Pengeluaran</span>
                                    <span class="block text-[11px] text-slate-500">Biaya operasional, sewa, gaji, listrik</span>
                                </span>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('type')" class="mt-1.5" />
                    </div>

                    <div>
                        <label for="description" class="label">Keterangan <span class="text-rose-500">*</span></label>
                        <input id="description" name="description" type="text" value="{{ old('description') }}" required
                               class="input" placeholder="Contoh: Bayar sewa toko bulan ini" maxlength="255">
                        <x-input-error :messages="$errors->get('description')" class="mt-1.5" />
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="amount" class="label">Nominal <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-sm font-semibold text-slate-400">Rp</span>
                                <input id="amount" name="amount" type="number" min="1" step="1" value="{{ old('amount') }}"
                                       required x-model.number="amount" class="input pl-10" placeholder="0">
                            </div>
                            <p class="mt-1 text-[11px] font-medium text-slate-500" x-show="amount > 0" x-text="formattedAmount"></p>
                            <x-input-error :messages="$errors->get('amount')" class="mt-1.5" />
                        </div>

                        <div>
                            <label for="transaction_date" class="label">Tanggal <span class="text-rose-500">*</span></label>
                            <input id="transaction_date" name="transaction_date" type="date"
                                   value="{{ old('transaction_date', now()->toDateString()) }}" required class="input">
                            <x-input-error :messages="$errors->get('transaction_date')" class="mt-1.5" />
                        </div>
                    </div>

                    <div>
                        <label for="payment_method" class="label">Metode pembayaran <span class="text-rose-500">*</span></label>
                        <select id="payment_method" name="payment_method" required class="input">
                            @foreach (['cash' => 'Tunai', 'transfer' => 'Transfer Bank', 'qris' => 'QRIS', 'other' => 'Lainnya'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('payment_method', 'cash') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('payment_method')" class="mt-1.5" />
                    </div>

                    <div>
                        <label for="notes" class="label">Catatan tambahan</label>
                        <textarea id="notes" name="notes" rows="3" class="input"
                                  placeholder="Opsional — nomor referensi, nama vendor, atau detail lainnya...">{{ old('notes') }}</textarea>
                        <x-input-error :messages="$errors->get('notes')" class="mt-1.5" />
                    </div>
                </div>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="btn-primary"><x-icon name="check" class="h-4 w-4" /> Simpan Catatan</button>
                <a href="{{ route('finance.index') }}" class="btn-secondary">Batal</a>
            </div>
        </div>

        <div class="space-y-6">
            <div class="card p-5 sm:p-6">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Ringkasan</p>
                <p class="mt-2 text-sm font-semibold text-slate-900" x-text="type === 'income' ? 'Pemasukan baru' : 'Pengeluaran baru'"></p>
                <p class="mt-1 text-3xl font-extrabold tracking-tight"
                   :class="type === 'income' ? 'text-emerald-600' : 'text-rose-600'"
                   x-text="amount > 0 ? (type === 'income' ? '+' : '−') + formattedAmount : 'Rp 0'"></p>
                <p class="mt-3 text-[11px] leading-relaxed text-slate-400">
                    Catatan ini akan muncul di laporan laba rugi dan tidak memengaruhi stok produk.
                </p>
            </div>

            <div class="card p-5 sm:p-6">
                <h3 class="text-sm font-bold text-slate-900">Tips pencatatan</h3>
                <ul class="mt-3 space-y-3 text-xs leading-relaxed text-slate-500">
                    <li><span class="font-semibold text-slate-700">Pengeluaran operasional</span> seperti sewa, listrik, air, internet, dan gaji karyawan.</li>
                    <li><span class="font-semibold text-slate-700">Pemasukan lain</span> seperti pendapatan bunga, penjualan aset, atau modal tambahan.</li>
                    <li>Gunakan keterangan yang jelas agar laporan keuangan mudah diaudit.</li>
                </ul>
            </div>

            <div class="rounded-2xl border border-indigo-100 bg-indigo-50/70 p-4">
                <div class="flex gap-3">
                    <x-icon name="wallet" class="mt-0.5 h-4 w-4 shrink-0 text-indigo-600" />
                    <p class="text-[11px] leading-relaxed text-indigo-900">Penjualan dari kasir otomatis tercatat sebagai pemasukan, jadi tidak perlu dicatat ulang di sini.</p>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
        <script>
            function financeForm() {
                return {
                    type: @json(old('type', $type)),
                    amount: {{ (float) old('amount', 0) }},
                    get formattedAmount() {
                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(Number(this.amount) || 0);
                    }
                };
            }
        </script>
    @endpush
</x-app-layout>
