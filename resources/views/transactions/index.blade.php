<x-app-layout>
    <x-page-header title="Transaksi" subtitle="Riwayat penjualan, invoice, dan status pembayaran.">
        <x-slot name="actions">
            @if (auth()->user()->canOperateCashier())
                <a href="{{ route('pos.index') }}" class="btn-primary">
                    <x-icon name="plus" class="h-4 w-4" /> Transaksi Baru
                </a>
            @endif
        </x-slot>
    </x-page-header>

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <x-stat-card label="Omzet (filter aktif)" :value="rupiah($summary['revenue'])" icon="banknotes" tone="indigo" hint="Penjualan selesai" />
        <x-stat-card label="Jumlah transaksi" :value="$summary['count'].' transaksi'" icon="receipt" tone="sky" hint="Sesuai rentang tanggal" />
        <x-stat-card label="Rata-rata transaksi" :value="rupiah($summary['count'] > 0 ? $summary['revenue'] / $summary['count'] : 0)" icon="chart" tone="emerald" hint="Nilai belanja rata-rata" />
    </div>

    <div class="card overflow-hidden">
        <div class="border-b border-slate-100 p-4 sm:p-5">
            <form method="GET" action="{{ route('transactions.index') }}" class="flex flex-col gap-3">
                <div class="flex flex-col gap-3 lg:flex-row">
                    <div class="relative flex-1">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <x-icon name="search" class="h-4 w-4" />
                        </span>
                        <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari invoice, pelanggan, atau deskripsi..."
                               class="input py-2.5 pl-10">
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <input type="date" name="from" value="{{ request('from') }}" class="input py-2.5 sm:w-40" title="Dari tanggal">
                        <input type="date" name="to" value="{{ request('to') }}" class="input py-2.5 sm:w-40" title="Sampai tanggal">
                        <select name="type" class="input py-2.5 sm:w-36">
                            <option value="">Semua tipe</option>
                            <option value="sale" @selected(request('type') === 'sale')>Penjualan</option>
                            <option value="income" @selected(request('type') === 'income')>Pemasukan</option>
                            <option value="expense" @selected(request('type') === 'expense')>Pengeluaran</option>
                        </select>
                        <select name="status" class="input py-2.5 sm:w-36">
                            <option value="">Semua status</option>
                            <option value="paid" @selected(request('status') === 'paid')>Lunas</option>
                            <option value="partial" @selected(request('status') === 'partial')>Sebagian</option>
                            <option value="unpaid" @selected(request('status') === 'unpaid')>Belum bayar</option>
                        </select>
                        <button type="submit" class="btn-secondary"><x-icon name="filter" class="h-4 w-4" /> Filter</button>
                        @if (request()->hasAny(['q', 'from', 'to', 'type', 'status']))
                            <a href="{{ route('transactions.index') }}" class="btn-ghost">Reset</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        @if ($transactions->isEmpty())
            <x-empty-state title="Belum ada transaksi" description="Transaksi yang Anda buat akan muncul di sini." icon="receipt">
                <x-slot name="action">
                    <a href="{{ route('pos.index') }}" class="btn-primary"><x-icon name="cart" class="h-4 w-4" /> Buka Kasir</a>
                </x-slot>
            </x-empty-state>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Pelanggan</th>
                            <th>Tipe</th>
                            <th>Total</th>
                            <th>Pembayaran</th>
                            <th>Tanggal</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 bg-white">
                        @foreach ($transactions as $transaction)
                            <tr>
                                <td>
                                    <a href="{{ route('transactions.show', $transaction) }}" class="font-semibold text-indigo-600 hover:text-indigo-700">
                                        {{ $transaction->invoice_number }}
                                    </a>
                                    <p class="text-[11px] text-slate-400">{{ $transaction->user?->name ?? 'Sistem' }}</p>
                                </td>
                                <td>
                                    <p class="text-sm text-slate-700">{{ $transaction->customer?->name ?? 'Pelanggan umum' }}</p>
                                    @if ($transaction->description)
                                        <p class="max-w-[180px] truncate text-[11px] text-slate-400">{{ $transaction->description }}</p>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $transaction->type === 'sale' ? 'badge-info' : ($transaction->type === 'income' ? 'badge-success' : 'badge-warning') }}">
                                        {{ $transaction->type_label }}
                                    </span>
                                </td>
                                <td class="font-semibold text-slate-800">{{ rupiah($transaction->total_amount) }}</td>
                                <td>
                                    <x-badge :tone="$transaction->payment_status_badge">{{ $transaction->payment_status_label }}</x-badge>
                                    @if ($transaction->is_void)
                                        <x-badge tone="danger">Void</x-badge>
                                    @endif
                                </td>
                                <td>
                                    <p class="text-xs text-slate-600">{{ $transaction->transaction_date->format('d M Y') }}</p>
                                    <p class="text-[11px] text-slate-400">{{ $transaction->transaction_date->format('H:i') }}</p>
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('transactions.pdf', $transaction) }}" title="Unduh invoice"
                                           class="rounded-lg p-2 text-slate-400 transition hover:bg-indigo-50 hover:text-indigo-600">
                                            <x-icon name="download" class="h-4 w-4" />
                                        </a>
                                        <a href="{{ route('transactions.show', $transaction) }}" title="Detail"
                                           class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                                            <x-icon name="eye" class="h-4 w-4" />
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-5 py-4">{{ $transactions->links() }}</div>
        @endif
    </div>
</x-app-layout>
