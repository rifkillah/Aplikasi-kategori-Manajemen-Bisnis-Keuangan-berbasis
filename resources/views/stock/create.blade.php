<x-app-layout>
    <x-page-header title="Catat Pergerakan Stok" subtitle="Catat barang masuk, keluar, atau sesuaikan stok fisik.">
        <x-slot name="actions">
            <a href="{{ route('stock.index') }}" class="btn-secondary"><x-icon name="arrow-left" class="h-4 w-4" /> Riwayat Stok</a>
        </x-slot>
    </x-page-header>

    <form method="POST" action="{{ route('stock.store') }}"
          x-data="stockForm()" class="grid gap-6 xl:grid-cols-3">
        @csrf

        <div class="space-y-6 xl:col-span-2">
            <div class="card">
                <div class="border-b border-slate-100 p-5 sm:p-6">
                    <h2 class="section-title">Detail pergerakan</h2>
                    <p class="muted mt-0.5">Pilih produk dan jenis pergerakan stok.</p>
                </div>

                <div class="space-y-5 p-5 sm:p-6">
                    <div>
                        <label for="product_id" class="label">Produk <span class="text-rose-500">*</span></label>
                        <select id="product_id" name="product_id" x-model.number="productId" required class="input">
                            <option value="">Pilih produk...</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" @selected((string) old('product_id', $selectedProduct?->id) === (string) $product->id)>
                                    {{ $product->name }} — stok {{ $product->stock }} {{ $product->unit }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('product_id')" class="mt-1.5" />
                    </div>

                    <div>
                        <span class="label">Jenis pergerakan <span class="text-rose-500">*</span></span>
                        <input type="hidden" name="type" :value="type">
                        <div class="grid gap-2 sm:grid-cols-3">
                            <template x-for="option in typeOptions" :key="option.value">
                                <button type="button" @click="type = option.value"
                                        class="rounded-xl border p-3 text-left transition"
                                        :class="type === option.value ? 'border-indigo-500 bg-indigo-50 ring-1 ring-indigo-500' : 'border-slate-200 hover:bg-slate-50'">
                                    <span class="flex items-center gap-2 text-xs font-bold"
                                          :class="type === option.value ? 'text-indigo-700' : 'text-slate-700'">
                                        <span class="flex h-6 w-6 items-center justify-center rounded-lg"
                                              :class="option.color">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" :d="option.icon"/></svg>
                                        </span>
                                        <span x-text="option.label"></span>
                                    </span>
                                    <span class="mt-1.5 block text-[10px] leading-relaxed text-slate-500" x-text="option.description"></span>
                                </button>
                            </template>
                        </div>
                        <x-input-error :messages="$errors->get('type')" class="mt-1.5" />
                    </div>

                    <div>
                        <label for="quantity" class="label" x-text="quantityLabel"></label>
                        <input id="quantity" name="quantity" type="number" :min="type === 'adjustment' ? 0 : 1" required
                               value="{{ old('quantity') }}" class="input" placeholder="0">
                        <p class="mt-1 text-[11px] text-slate-400" x-text="quantityHint"></p>
                        <x-input-error :messages="$errors->get('quantity')" class="mt-1.5" />
                    </div>

                    <div>
                        <label for="notes" class="label">Catatan</label>
                        <textarea id="notes" name="notes" rows="3" placeholder="Contoh: Pembelian dari supplier, barang rusak, opname bulanan..." class="input">{{ old('notes') }}</textarea>
                        <x-input-error :messages="$errors->get('notes')" class="mt-1.5" />
                    </div>
                </div>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="btn-primary"><x-icon name="check" class="h-4 w-4" /> Simpan Pergerakan</button>
                <a href="{{ route('stock.index') }}" class="btn-secondary">Batal</a>
            </div>
        </div>

        <div class="space-y-6">
            <div class="card p-5 sm:p-6">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                        <x-icon name="cube" class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Produk terpilih</p>
                        <p class="text-sm font-bold text-slate-900" x-text="selected ? selected.name : 'Belum dipilih'"></p>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-3">
                    <div class="rounded-xl bg-slate-50 p-3">
                        <p class="text-[10px] uppercase tracking-wider text-slate-400">Stok saat ini</p>
                        <p class="mt-1 text-xl font-bold text-slate-900" x-text="selected ? selected.stock : '—'"></p>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3">
                        <p class="text-[10px] uppercase tracking-wider text-slate-400">Batas minimum</p>
                        <p class="mt-1 text-xl font-bold text-slate-900" x-text="selected ? selected.min_stock_alert : '—'"></p>
                    </div>
                </div>

                <div class="mt-3 rounded-xl border border-indigo-100 bg-indigo-50/60 p-3">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-indigo-600">Stok setelah disimpan</p>
                    <p class="mt-1 text-2xl font-extrabold" :class="afterStock < 0 ? 'text-rose-600' : 'text-indigo-700'" x-text="afterStock"></p>
                    <p class="text-[11px] text-indigo-600" x-text="selected ? selected.unit : 'unit'"></p>
                </div>
            </div>

            <div class="card p-5 sm:p-6">
                <h3 class="text-sm font-bold text-slate-900">Panduan singkat</h3>
                <ul class="mt-3 space-y-3 text-xs leading-relaxed text-slate-500">
                    <li><span class="font-semibold text-slate-700">Barang masuk</span> menambah stok, misalnya pembelian dari supplier.</li>
                    <li><span class="font-semibold text-slate-700">Barang keluar</span> mengurangi stok untuk barang rusak, hilang, atau pemakaian internal.</li>
                    <li><span class="font-semibold text-slate-700">Penyesuaian</span> mengubah stok menjadi angka fisik hasil opname.</li>
                </ul>
            </div>

            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4">
                <div class="flex gap-3">
                    <x-icon name="bell" class="mt-0.5 h-4 w-4 shrink-0 text-amber-600" />
                    <p class="text-[11px] leading-relaxed text-amber-800">Jika stok berada di bawah batas minimum, notifikasi akan dikirim ke owner melalui email dan pusat notifikasi.</p>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
        @php
            $stockProducts = $products->map(fn ($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'stock' => $product->stock,
                'unit' => $product->unit,
                'min_stock_alert' => $product->min_stock_alert,
            ]);
        @endphp
        <script>
            window.STOCK_PRODUCTS = @json($stockProducts);

            function stockForm() {
                return {
                    productId: @json((int) old('product_id', $selectedProduct?->id)),
                    quantity: {{ (int) old('quantity', 0) }},
                    type: @json(old('type', $type)),
                    products: window.STOCK_PRODUCTS,
                    typeOptions: [
                        { value: 'in', label: 'Barang Masuk', description: 'Menambah jumlah stok produk.', color: 'bg-emerald-100 text-emerald-600', icon: 'M12 4.5v15m7.5-7.5h-15' },
                        { value: 'out', label: 'Barang Keluar', description: 'Mengurangi stok untuk pemakaian internal.', color: 'bg-rose-100 text-rose-600', icon: 'M5 12h14' },
                        { value: 'adjustment', label: 'Penyesuaian', description: 'Set stok sesuai hasil opname fisik.', color: 'bg-amber-100 text-amber-600', icon: 'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182' }
                    ],

                    get selected() {
                        return this.products.find(product => product.id === Number(this.productId)) || null;
                    },
                    get quantityValue() {
                        return Number(this.quantity) || 0;
                    },
                    get afterStock() {
                        if (!this.selected) return 0;
                        if (this.type === 'in') return this.selected.stock + this.quantityValue;
                        if (this.type === 'out') return this.selected.stock - this.quantityValue;
                        return this.quantityValue;
                    },
                    get quantityLabel() {
                        return this.type === 'adjustment' ? 'Stok fisik hasil opname' : 'Jumlah';
                    },
                    get quantityHint() {
                        if (this.type === 'adjustment') return 'Masukkan jumlah stok fisik yang sebenarnya. Sistem menghitung selisihnya otomatis.';
                        if (this.type === 'out') return this.selected ? 'Stok tersedia: ' + this.selected.stock + ' ' + this.selected.unit : 'Jumlah yang akan dikurangi dari stok.';
                        return 'Jumlah barang yang akan ditambahkan ke stok.';
                    }
                };
            }
        </script>
    @endpush
</x-app-layout>
