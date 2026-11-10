<x-app-layout>
    <x-page-header :title="$product->name" :subtitle="$product->sku.($product->barcode ? ' · '.$product->barcode : '')">
        <x-slot name="actions">
            <a href="{{ route('products.index') }}" class="btn-secondary">
                <x-icon name="arrow-left" class="h-4 w-4" /> Produk
            </a>
            <a href="{{ route('stock.create', ['product' => $product->id, 'type' => 'in']) }}" class="btn-success">
                <x-icon name="plus" class="h-4 w-4" /> Tambah Stok
            </a>
            <a href="{{ route('products.edit', $product) }}" class="btn-primary">
                <x-icon name="edit" class="h-4 w-4" /> Edit
            </a>
        </x-slot>
    </x-page-header>

    @if ($product->is_low_stock)
        <div class="mb-5 flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3">
            <x-icon name="alert" class="mt-0.5 h-5 w-5 shrink-0 text-amber-600" />
            <div class="flex-1">
                <p class="text-sm font-semibold text-amber-900">Stok produk ini menipis</p>
                <p class="mt-0.5 text-xs text-amber-700">
                    Tersisa {{ $product->stock }} {{ $product->unit }} dari batas minimum {{ $product->min_stock_alert }} {{ $product->unit }}.
                </p>
            </div>
            <a href="{{ route('stock.create', ['product' => $product->id, 'type' => 'in']) }}" class="text-xs font-bold text-amber-800 underline underline-offset-2">Restock</a>
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div data-reveal class="card p-5">
                    <p class="text-xs font-medium text-slate-500">Stok saat ini</p>
                    <p class="mt-1 text-2xl font-bold {{ $product->stock <= 0 ? 'text-rose-600' : ($product->is_low_stock ? 'text-amber-600' : 'text-slate-900') }}">
                        {{ $product->stock }}
                    </p>
                    <p class="mt-1 text-[11px] text-slate-400">{{ $product->unit }} · min. {{ $product->min_stock_alert }}</p>
                </div>
                <div data-reveal class="card p-5">
                    <p class="text-xs font-medium text-slate-500">Harga jual</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">{{ rupiah($product->selling_price) }}</p>
                    <p class="mt-1 text-[11px] text-slate-400">Beli {{ rupiah($product->purchase_price) }}</p>
                </div>
                <div data-reveal class="card p-5">
                    <p class="text-xs font-medium text-slate-500">Margin kotor</p>
                    <p class="mt-1 text-2xl font-bold text-emerald-600">
                        {{ $product->selling_price > 0 ? number_format((($product->selling_price - $product->purchase_price) / $product->selling_price) * 100, 0) : 0 }}%
                    </p>
                    <p class="mt-1 text-[11px] text-slate-400">{{ rupiah($product->selling_price - $product->purchase_price) }} / unit</p>
                </div>
                <div data-reveal class="card p-5">
                    <p class="text-xs font-medium text-slate-500">Total terjual</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($sold->qty ?? 0, 0, ',', '.') }}</p>
                    <p class="mt-1 text-[11px] text-slate-400">{{ rupiah($sold->revenue ?? 0) }} omzet</p>
                </div>
            </div>

            <div data-reveal class="card overflow-hidden">
                <div class="flex items-center justify-between border-b border-slate-100 p-5 sm:p-6">
                    <div>
                        <h2 class="section-title">Riwayat pergerakan stok</h2>
                        <p class="muted mt-0.5">Semua perubahan stok tercatat otomatis.</p>
                    </div>
                    <a href="{{ route('stock.index', ['product' => $product->id]) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">Lihat semua</a>
                </div>

                @if ($stockLogs->isEmpty())
                    <x-empty-state title="Belum ada pergerakan stok" description="Stok awal dan penyesuaian akan tampil di sini." icon="archive" />
                @else
                    {{-- Mobile cards --}}
                    <div class="space-y-3 p-4 md:hidden">
                        @foreach ($stockLogs as $log)
                            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold text-slate-700">{{ id_date($log->created_at) }} · {{ $log->created_at->format('H:i') }}</p>
                                        <p class="mt-0.5 truncate text-[11px] text-slate-400">{{ $log->user?->name ?? 'Sistem' }}</p>
                                    </div>
                                    <x-badge :tone="$log->type_badge">{{ $log->type_label }}</x-badge>
                                </div>
                                <div class="mt-3 grid grid-cols-2 gap-2">
                                    <div class="rounded-lg bg-slate-50 p-2.5">
                                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Perubahan</p>
                                        <p class="mt-0.5 text-sm font-bold {{ $log->qty >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                            {{ $log->qty >= 0 ? '+' : '' }}{{ $log->qty }}
                                        </p>
                                    </div>
                                    <div class="rounded-lg bg-slate-50 p-2.5">
                                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Stok</p>
                                        <p class="mt-0.5 text-xs font-semibold text-slate-700">{{ $log->stock_before }} → {{ $log->stock_after }}</p>
                                    </div>
                                </div>
                                @if ($log->notes)
                                    <p class="mt-3 rounded-lg bg-amber-50 px-3 py-2 text-[11px] leading-relaxed text-amber-800">{{ $log->notes }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="hidden table-wrap md:block">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Tipe</th>
                                    <th>Perubahan</th>
                                    <th>Stok</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-900/[0.06]">
                                @foreach ($stockLogs as $log)
                                    <tr>
                                        <td>
                                            <p class="text-xs font-medium text-slate-700">{{ id_date($log->created_at) }}</p>
                                            <p class="text-[11px] text-slate-400">{{ $log->created_at->format('H:i') }} · {{ $log->user?->name ?? 'Sistem' }}</p>
                                        </td>
                                        <td><x-badge :tone="$log->type_badge">{{ $log->type_label }}</x-badge></td>
                                        <td>
                                            <span class="font-semibold {{ $log->qty >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                                {{ $log->qty >= 0 ? '+' : '' }}{{ $log->qty }}
                                            </span>
                                        </td>
                                        <td class="text-xs text-slate-500">{{ $log->stock_before }} → <span class="font-semibold text-slate-700">{{ $log->stock_after }}</span></td>
                                        <td class="max-w-[220px] truncate text-xs text-slate-500" title="{{ $log->notes }}">{{ $log->notes ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-slate-100 px-5 py-4">{{ $stockLogs->links() }}</div>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div data-reveal class="card p-5 sm:p-6">
                <h2 class="section-title">Detail produk</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-slate-500">Kategori</dt>
                        <dd class="font-medium text-slate-800">{{ $product->category?->name ?? 'Tanpa kategori' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-slate-500">Satuan</dt>
                        <dd class="font-medium text-slate-800">{{ $product->unit }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-slate-500">Status</dt>
                        <dd>
                            @if ($product->is_active)
                                <x-badge tone="success">Aktif</x-badge>
                            @else
                                <x-badge tone="gray">Nonaktif</x-badge>
                            @endif
                        </dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-slate-500">Nilai stok</dt>
                        <dd class="font-semibold text-slate-800">{{ rupiah($product->stock_value) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-slate-500">Dibuat</dt>
                        <dd class="text-slate-700">{{ id_date($product->created_at) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-slate-500">Terakhir diubah</dt>
                        <dd class="text-slate-700">{{ id_date($product->updated_at) }}</dd>
                    </div>
                </dl>
            </div>

            <div data-reveal class="card p-5 sm:p-6">
                <h2 class="section-title">Aksi cepat</h2>
                <div class="mt-4 space-y-2">
                    <a href="{{ route('stock.create', ['product' => $product->id, 'type' => 'in']) }}" class="btn-secondary w-full justify-start">
                        <x-icon name="plus" class="h-4 w-4 text-emerald-600" /> Barang masuk
                    </a>
                    <a href="{{ route('stock.create', ['product' => $product->id, 'type' => 'out']) }}" class="btn-secondary w-full justify-start">
                        <x-icon name="minus" class="h-4 w-4 text-rose-600" /> Barang keluar
                    </a>
                    <a href="{{ route('stock.create', ['product' => $product->id, 'type' => 'adjustment']) }}" class="btn-secondary w-full justify-start">
                        <x-icon name="refresh" class="h-4 w-4 text-amber-600" /> Stock opname
                    </a>
                </div>
            </div>

            <div class="rounded-2xl border border-rose-200 bg-rose-50/50 p-5">
                <h3 class="text-sm font-bold text-rose-900">Hapus produk</h3>
                <p class="mt-1 text-xs text-rose-700">Produk akan dihapus dari katalog. Riwayat transaksi tetap tersimpan.</p>
                <form method="POST" action="{{ route('products.destroy', $product) }}" class="mt-3"
                      onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger btn-sm w-full">
                        <x-icon name="trash" class="h-3.5 w-3.5" /> Hapus Produk
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
