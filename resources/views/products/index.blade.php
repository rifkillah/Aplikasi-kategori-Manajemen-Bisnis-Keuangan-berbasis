<x-app-layout>
    <x-page-header title="Produk" subtitle="Kelola katalog, harga, dan ketersediaan stok barang Anda.">
        <x-slot name="actions">
            <a href="{{ route('categories.index') }}" class="btn-secondary">
                <x-icon name="tag" class="h-4 w-4" /> Kategori
            </a>
            <a href="{{ route('products.create') }}" class="btn-primary">
                <x-icon name="plus" class="h-4 w-4" /> Tambah Produk
            </a>
        </x-slot>
    </x-page-header>

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <x-stat-card label="Total produk" :value="$summary['total'].' produk'" icon="cube" tone="indigo" hint="Termasuk produk nonaktif" />
        <x-stat-card label="Stok menipis" :value="$summary['low'].' produk'" icon="alert" :tone="$summary['low'] > 0 ? 'amber' : 'emerald'" hint="Di bawah batas minimum" />
        <x-stat-card label="Nilai persediaan" :value="rupiah($summary['stock_value'])" icon="banknotes" tone="emerald" hint="Harga beli × stok saat ini" />
    </div>

    <div class="card overflow-hidden">
        <div class="border-b border-slate-100 p-4 sm:p-5">
            <form method="GET" action="{{ route('products.index') }}" class="flex flex-col gap-3 lg:flex-row lg:items-center">
                <div class="relative flex-1">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <x-icon name="search" class="h-4 w-4" />
                    </span>
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama, SKU, atau barcode..."
                           class="input py-2.5 pl-10">
                </div>
                <div class="flex flex-wrap gap-2">
                    <select name="category" class="input py-2.5 sm:w-44">
                        <option value="">Semua kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <select name="status" class="input py-2.5 sm:w-40">
                        <option value="">Semua status</option>
                        <option value="low" @selected(request('status') === 'low')>Stok menipis</option>
                        <option value="out" @selected(request('status') === 'out')>Stok habis</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option>
                    </select>
                    <button type="submit" class="btn-secondary">
                        <x-icon name="filter" class="h-4 w-4" /> Filter
                    </button>
                    @if (request()->hasAny(['q', 'category', 'status']))
                        <a href="{{ route('products.index') }}" class="btn-ghost">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        @if ($products->isEmpty())
            <x-empty-state title="Produk tidak ditemukan" description="Tambahkan produk pertama atau ubah filter pencarian Anda." icon="cube">
                <x-slot name="action">
                    <a href="{{ route('products.create') }}" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> Tambah Produk</a>
                </x-slot>
            </x-empty-state>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Kategori</th>
                            <th>Harga jual</th>
                            <th>Stok</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 bg-white">
                        @foreach ($products as $product)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500">
                                            <x-icon name="cube" class="h-5 w-5" />
                                        </div>
                                        <div class="min-w-0">
                                            <a href="{{ route('products.show', $product) }}" class="block max-w-[240px] truncate text-sm font-semibold text-slate-800 hover:text-indigo-600">
                                                {{ $product->name }}
                                            </a>
                                            <p class="text-[11px] text-slate-400">{{ $product->sku }}@if($product->barcode) · {{ $product->barcode }}@endif</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-sm">{{ $product->category?->name ?? '—' }}</span>
                                </td>
                                <td>
                                    <p class="text-sm font-semibold text-slate-800">{{ rupiah($product->selling_price) }}</p>
                                    <p class="text-[11px] text-slate-400">Beli {{ rupiah($product->purchase_price) }}</p>
                                </td>
                                <td>
                                    <p class="text-sm font-bold {{ $product->stock <= 0 ? 'text-rose-600' : ($product->is_low_stock ? 'text-amber-600' : 'text-slate-800') }}">
                                        {{ $product->stock }} <span class="text-[11px] font-normal text-slate-400">{{ $product->unit }}</span>
                                    </p>
                                    <p class="text-[11px] text-slate-400">min. {{ $product->min_stock_alert }}</p>
                                </td>
                                <td>
                                    @if (! $product->is_active)
                                        <x-badge tone="gray">Nonaktif</x-badge>
                                    @elseif ($product->stock <= 0)
                                        <x-badge tone="danger">Habis</x-badge>
                                    @elseif ($product->is_low_stock)
                                        <x-badge tone="warning">Menipis</x-badge>
                                    @else
                                        <x-badge tone="success">Tersedia</x-badge>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('stock.create', ['product' => $product->id, 'type' => 'in']) }}" title="Tambah stok"
                                           class="rounded-lg p-2 text-slate-400 transition hover:bg-emerald-50 hover:text-emerald-600">
                                            <x-icon name="plus" class="h-4 w-4" />
                                        </a>
                                        <a href="{{ route('products.show', $product) }}" title="Detail"
                                           class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                                            <x-icon name="eye" class="h-4 w-4" />
                                        </a>
                                        <a href="{{ route('products.edit', $product) }}" title="Edit"
                                           class="rounded-lg p-2 text-slate-400 transition hover:bg-indigo-50 hover:text-indigo-600">
                                            <x-icon name="edit" class="h-4 w-4" />
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 px-5 py-4">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
