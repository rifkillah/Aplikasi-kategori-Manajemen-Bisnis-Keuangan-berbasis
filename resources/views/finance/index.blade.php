<x-app-layout>
    <x-page-header title="Kas & Keuangan" subtitle="Catat pemasukan dan pengeluaran operasional di luar penjualan kasir.">
        <x-slot name="actions">
            <a href="{{ route('finance.create', ['type' => 'income']) }}" class="btn-secondary">
                <x-icon name="trending-up" class="h-4 w-4 text-emerald-600" /> Catat Pemasukan
            </a>
            <a href="{{ route('finance.create', ['type' => 'expense']) }}" class="btn-primary">
                <x-icon name="plus" class="h-4 w-4" /> Catat Pengeluaran
            </a>
        </x-slot>
    </x-page-header>

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <x-stat-card label="Total pemasukan" :value="rupiah($summary['income'])" icon="trending-up" tone="emerald" hint="Sesuai filter aktif" />
        <x-stat-card label="Total pengeluaran" :value="rupiah($summary['expense'])" icon="trending-down" tone="rose" hint="Sesuai filter aktif" />
        <x-stat-card label="Selisih bersih" :value="rupiah($summary['difference'])" icon="wallet"
                     :tone="$summary['difference'] < 0 ? 'rose' : 'indigo'"
                     :hint="$summary['difference'] < 0 ? 'Pengeluaran lebih besar' : 'Pemasukan lebih besar'" />
    </div>

    <div class="card overflow-hidden">
        <div class="border-b border-slate-100 p-4 sm:p-5">
            <form method="GET" action="{{ route('finance.index') }}" class="flex flex-col gap-3">
                <div class="flex flex-col gap-3 lg:flex-row">
                    <div class="relative flex-1">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <x-icon name="search" class="h-4 w-4" />
                        </span>
                        <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari keterangan atau nomor catatan..." class="input py-2.5 pl-10">
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <select name="type" class="input py-2.5 sm:w-40">
                            <option value="">Semua tipe</option>
                            <option value="income" @selected(request('type') === 'income')>Pemasukan</option>
                            <option value="expense" @selected(request('type') === 'expense')>Pengeluaran</option>
                        </select>
                        <input type="date" name="from" value="{{ request('from') }}" class="input py-2.5 sm:w-40" title="Dari tanggal">
                        <input type="date" name="to" value="{{ request('to') }}" class="input py-2.5 sm:w-40" title="Sampai tanggal">
                        <button type="submit" class="btn-secondary"><x-icon name="filter" class="h-4 w-4" /> Filter</button>
                        @if (request()->hasAny(['q', 'type', 'from', 'to']))
                            <a href="{{ route('finance.index') }}" class="btn-ghost">Reset</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        @if ($transactions->isEmpty())
            <x-empty-state title="Belum ada catatan keuangan" description="Catat pemasukan lain atau pengeluaran operasional seperti sewa, listrik, dan gaji." icon="wallet">
                <x-slot name="action">
                    <a href="{{ route('finance.create', ['type' => 'expense']) }}" class="btn-primary">
                        <x-icon name="plus" class="h-4 w-4" /> Catat Pengeluaran
                    </a>
                </x-slot>
            </x-empty-state>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th>Tipe</th>
                            <th>Metode</th>
                            <th class="text-right">Nominal</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 bg-white">
                        @foreach ($transactions as $transaction)
                            <tr>
                                <td>
                                    <p class="text-xs font-medium text-slate-700">{{ id_date($transaction->transaction_date) }}</p>
                                    <p class="text-[11px] text-slate-400">{{ $transaction->user?->name ?? 'Sistem' }}</p>
                                </td>
                                <td>
                                    <p class="max-w-[260px] truncate text-sm font-semibold text-slate-800">{{ $transaction->description }}</p>
                                    <p class="text-[11px] text-slate-400">{{ $transaction->invoice_number }}@if ($transaction->notes) · {{ Str::limit($transaction->notes, 60) }}@endif</p>
                                </td>
                                <td>
                                    <x-badge :tone="$transaction->type === 'income' ? 'success' : 'danger'">{{ $transaction->type_label }}</x-badge>
                                </td>
                                <td class="text-xs font-medium text-slate-600">{{ $transaction->payment_method_label }}</td>
                                <td class="text-right">
                                    <span class="text-sm font-bold {{ $transaction->type === 'income' ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $transaction->type === 'income' ? '+' : '−' }}{{ rupiah($transaction->total_amount) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="flex justify-end">
                                        <form method="POST" action="{{ route('finance.destroy', $transaction) }}"
                                              onsubmit="return confirm('Hapus catatan {{ $transaction->type_label }} ini? Tindakan ini tidak dapat dibatalkan.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus catatan"
                                                    class="rounded-lg p-2 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600">
                                                <x-icon name="trash" class="h-4 w-4" />
                                            </button>
                                        </form>
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
