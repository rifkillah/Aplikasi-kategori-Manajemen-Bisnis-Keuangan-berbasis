<x-app-layout>
    <x-page-header title="Pergerakan Stok" subtitle="Jejak audit barang masuk, keluar, penjualan, dan penyesuaian.">
        <x-slot name="actions">
            <a href="{{ route('stock.create') }}" class="btn-primary">
                <x-icon name="plus" class="h-4 w-4" /> Catat Pergerakan
            </a>
            <a href="{{ route('products.index') }}" class="btn-secondary">
                <x-icon name="cube" class="h-4 w-4" /> Produk
            </a>
        </x-slot>
    </x-page-header>

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <x-stat-card label="Barang masuk hari ini" :value="number_format($summary['in'], 0, ',', '.').' unit'" icon="trending-up" tone="emerald" hint="Dari pembelian & restock" />
        <x-stat-card label="Barang keluar hari ini" :value="number_format($summary['out'], 0, ',', '.').' unit'" icon="trending-down" tone="rose" hint="Penjualan & pengurangan" />
        <x-stat-card label="Total catatan" :value="$logs->total().' entri'" icon="archive" tone="indigo" hint="Sesuai filter aktif" />
    </div>

    <div data-reveal class="card overflow-hidden">
        <div class="border-b border-slate-100 p-4 sm:p-5">
            <form method="GET" action="{{ route('stock.index') }}" class="flex flex-col gap-3">
                <div class="flex flex-col gap-3 lg:flex-row">
                    <div class="relative flex-1">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <x-icon name="search" class="h-4 w-4" />
                        </span>
                        <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari produk atau SKU..." class="input py-2.5 pl-10">
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <select name="product" class="input py-2.5 sm:w-48">
                            <option value="">Semua produk</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" @selected(request('product') == $product->id)>{{ $product->name }}</option>
                            @endforeach
                        </select>
                        <select name="type" class="input py-2.5 sm:w-36">
                            <option value="">Semua tipe</option>
                            @foreach (['in' => 'Masuk', 'out' => 'Keluar', 'adjustment' => 'Penyesuaian', 'sale' => 'Penjualan', 'void' => 'Pembatalan'] as $value => $label)
                                <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <input type="date" name="from" value="{{ request('from') }}" class="input py-2.5 sm:w-40" title="Dari tanggal">
                        <input type="date" name="to" value="{{ request('to') }}" class="input py-2.5 sm:w-40" title="Sampai tanggal">
                        <button type="submit" class="btn-secondary"><x-icon name="filter" class="h-4 w-4" /> Filter</button>
                        @if (request()->hasAny(['q', 'product', 'type', 'from', 'to']))
                            <a href="{{ route('stock.index') }}" class="btn-ghost">Reset</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        @if ($logs->isEmpty())
            <x-empty-state title="Belum ada pergerakan stok" description="Catat barang masuk atau sesuaikan stok produk untuk memulai." icon="archive">
                <x-slot name="action">
                    <a href="{{ route('stock.create') }}" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> Catat Pergerakan</a>
                </x-slot>
            </x-empty-state>
        @else
            {{-- Mobile cards --}}
            <div class="space-y-3 p-4 md:hidden">
                @foreach ($logs as $log)
                    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-slate-800">{{ $log->product?->name ?? 'Produk dihapus' }}</p>
                                <p class="mt-0.5 text-[11px] text-slate-400">
                                    {{ id_date($log->created_at) }} · {{ $log->created_at->format('H:i') }} · {{ $log->user?->name ?? 'Sistem' }}
                                </p>
                            </div>
                            <x-badge :tone="$log->type_badge">{{ $log->type_label }}</x-badge>
                        </div>

                        <div class="mt-3 grid grid-cols-3 gap-2">
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
                            <div class="rounded-lg bg-slate-50 p-2.5">
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">SKU</p>
                                <p class="mt-0.5 truncate text-xs font-medium text-slate-700">{{ $log->product?->sku ?? '—' }}</p>
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
                            <th>Produk</th>
                            <th>Tipe</th>
                            <th>Perubahan</th>
                            <th>Stok</th>
                            <th>Petugas</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-900/[0.06]">
                        @foreach ($logs as $log)
                            <tr>
                                <td>
                                    <p class="text-xs font-medium text-slate-700">{{ id_date($log->created_at) }}</p>
                                    <p class="text-[11px] text-slate-400">{{ $log->created_at->format('H:i') }}</p>
                                </td>
                                <td>
                                    <p class="max-w-[200px] truncate text-sm font-semibold text-slate-800">{{ $log->product?->name ?? 'Produk dihapus' }}</p>
                                    <p class="text-[11px] text-slate-400">{{ $log->product?->sku }}</p>
                                </td>
                                <td><x-badge :tone="$log->type_badge">{{ $log->type_label }}</x-badge></td>
                                <td>
                                    <span class="text-sm font-bold {{ $log->qty >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $log->qty >= 0 ? '+' : '' }}{{ $log->qty }}
                                    </span>
                                </td>
                                <td class="text-xs text-slate-500">{{ $log->stock_before }} → <span class="font-bold text-slate-700">{{ $log->stock_after }}</span></td>
                                <td class="text-xs text-slate-600">{{ $log->user?->name ?? 'Sistem' }}</td>
                                <td class="max-w-[200px] truncate text-xs text-slate-500" title="{{ $log->notes }}">{{ $log->notes ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-5 py-4">{{ $logs->links() }}</div>
        @endif
    </div>
</x-app-layout>
