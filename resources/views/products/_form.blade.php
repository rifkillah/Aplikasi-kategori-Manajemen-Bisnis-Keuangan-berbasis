@php
    $isEdit = isset($product);
    $value = fn (string $field, $default = null) => old($field, $isEdit ? $product->{$field} : $default);
@endphp

<div class="grid gap-6 xl:grid-cols-3">
    <div class="space-y-6 xl:col-span-2">
        <div class="card">
            <div class="border-b border-slate-100 p-5 sm:p-6">
                <h2 class="section-title">Informasi produk</h2>
                <p class="muted mt-0.5">Detail dasar yang muncul di kasir dan invoice.</p>
            </div>

            <div class="grid gap-5 p-5 sm:grid-cols-2 sm:p-6">
                <div class="sm:col-span-2">
                    <label for="name" class="label">Nama produk <span class="text-rose-500">*</span></label>
                    <input id="name" name="name" type="text" required value="{{ $value('name') }}"
                           placeholder="Contoh: Kopi Susu Senja" class="input">
                    <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
                </div>

                <div>
                    <label for="category_id" class="label">Kategori</label>
                    <select id="category_id" name="category_id" class="input">
                        <option value="">Tanpa kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) $value('category_id') === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('category_id')" class="mt-1.5" />
                </div>

                <div>
                    <label for="unit" class="label">Satuan <span class="text-rose-500">*</span></label>
                    <input id="unit" name="unit" type="text" required value="{{ $value('unit', 'pcs') }}"
                           placeholder="pcs / botol / pack" class="input">
                    <x-input-error :messages="$errors->get('unit')" class="mt-1.5" />
                </div>

                <div x-data="{ sku: '{{ $value('sku', $suggestedSku ?? '') }}' }">
                    <label for="sku" class="label">SKU <span class="text-rose-500">*</span></label>
                    <div class="flex gap-2">
                        <input id="sku" name="sku" type="text" required x-model="sku" placeholder="PRD-001" class="input">
                        <button type="button" @click="sku = 'PRD-' + Math.random().toString(36).slice(2, 8).toUpperCase()"
                                class="btn-secondary shrink-0 px-3" title="Generate SKU">
                            <x-icon name="refresh" class="h-4 w-4" />
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('sku')" class="mt-1.5" />
                </div>

                <div x-data="{ barcode: '{{ $value('barcode', '') }}' }">
                    <label for="barcode" class="label">Barcode</label>
                    <div class="flex gap-2">
                        <input id="barcode" name="barcode" type="text" x-model="barcode" placeholder="Scan atau ketik barcode" class="input">
                        <button type="button" @click="barcode = Array.from({length: 13}, () => Math.floor(Math.random() * 10)).join('')"
                                class="btn-secondary shrink-0 px-3" title="Generate barcode">
                            <x-icon name="barcode" class="h-4 w-4" />
                        </button>
                    </div>
                    <p class="mt-1 text-[11px] text-slate-400">Opsional. Bisa diisi dengan hasil scan barcode.</p>
                    <x-input-error :messages="$errors->get('barcode')" class="mt-1.5" />
                </div>
            </div>
        </div>

        <div class="card">
            <div class="border-b border-slate-100 p-5 sm:p-6">
                <h2 class="section-title">Harga & persediaan</h2>
                <p class="muted mt-0.5">Atur harga jual serta batas peringatan stok.</p>
            </div>

            <div class="grid gap-5 p-5 sm:grid-cols-2 sm:p-6">
                <div>
                    <label for="purchase_price" class="label">Harga beli <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-xs font-semibold text-slate-400">Rp</span>
                        <input id="purchase_price" name="purchase_price" type="number" min="0" step="100" required
                               value="{{ $value('purchase_price') }}" class="input pl-10" placeholder="0">
                    </div>
                    <x-input-error :messages="$errors->get('purchase_price')" class="mt-1.5" />
                </div>

                <div>
                    <label for="selling_price" class="label">Harga jual <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-xs font-semibold text-slate-400">Rp</span>
                        <input id="selling_price" name="selling_price" type="number" min="0" step="100" required
                               value="{{ $value('selling_price') }}" class="input pl-10" placeholder="0">
                    </div>
                    <x-input-error :messages="$errors->get('selling_price')" class="mt-1.5" />
                </div>

                @if (! $isEdit)
                    <div>
                        <label for="stock" class="label">Stok awal <span class="text-rose-500">*</span></label>
                        <input id="stock" name="stock" type="number" min="0" required value="{{ $value('stock', 0) }}" class="input">
                        <p class="mt-1 text-[11px] text-slate-400">Dicatat sebagai pergerakan stok masuk.</p>
                        <x-input-error :messages="$errors->get('stock')" class="mt-1.5" />
                    </div>
                @else
                    <div>
                        <span class="label">Stok saat ini</span>
                        <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5">
                            <span class="text-sm font-semibold text-slate-800">{{ $product->stock }} {{ $product->unit }}</span>
                            <a href="{{ route('stock.create', ['product' => $product->id, 'type' => 'adjustment']) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">Sesuaikan</a>
                        </div>
                        <p class="mt-1 text-[11px] text-slate-400">Gunakan menu pergerakan stok untuk mengubah stok.</p>
                    </div>
                @endif

                <div>
                    <label for="min_stock_alert" class="label">Batas minimum stok <span class="text-rose-500">*</span></label>
                    <input id="min_stock_alert" name="min_stock_alert" type="number" min="0" required
                           value="{{ $value('min_stock_alert', 5) }}" class="input">
                    <p class="mt-1 text-[11px] text-slate-400">Notifikasi dikirim saat stok mencapai angka ini.</p>
                    <x-input-error :messages="$errors->get('min_stock_alert')" class="mt-1.5" />
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="card p-5 sm:p-6">
            <h2 class="section-title">Visibilitas</h2>
            <p class="muted mt-1">Produk nonaktif tidak tampil di kasir.</p>

            <label class="mt-5 flex cursor-pointer items-center justify-between gap-4 rounded-xl border border-slate-200 p-4">
                <div>
                    <p class="text-sm font-semibold text-slate-800">Produk aktif</p>
                    <p class="text-[11px] text-slate-400">Dapat dijual melalui kasir</p>
                </div>
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked((bool) $value('is_active', true))
                       class="h-5 w-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
            </label>
        </div>

        <div class="card p-5 sm:p-6">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                <x-icon name="barcode" class="h-5 w-5" />
            </div>
            <h3 class="mt-4 text-sm font-bold text-slate-900">Tips barcode</h3>
            <p class="mt-1.5 text-xs leading-relaxed text-slate-500">
                Gunakan barcode dari kemasan produk. Di halaman kasir, cukup scan pada kolom pencarian untuk menambahkan produk ke keranjang secara otomatis.
            </p>
        </div>

        <div class="flex flex-col gap-2">
            <button type="submit" class="btn-primary w-full">
                <x-icon name="check" class="h-4 w-4" /> {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Produk' }}
            </button>
            <a href="{{ $isEdit ? route('products.show', $product) : route('products.index') }}" class="btn-secondary w-full">Batal</a>
        </div>
    </div>
</div>
