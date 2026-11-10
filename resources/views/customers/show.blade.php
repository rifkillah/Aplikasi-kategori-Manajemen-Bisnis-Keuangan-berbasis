<x-app-layout>
    <x-page-header :title="$customer->name" :subtitle="$customer->phone ?: 'Pelanggan tanpa nomor telepon'">
        <x-slot name="actions">
            <a href="{{ route('customers.index') }}" class="btn-secondary"><x-icon name="arrow-left" class="h-4 w-4" /> Pelanggan</a>
            <a href="{{ route('customers.edit', $customer) }}" class="btn-primary"><x-icon name="edit" class="h-4 w-4" /> Edit</a>
        </x-slot>
    </x-page-header>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <div class="grid gap-4 sm:grid-cols-3">
                <x-stat-card label="Total transaksi" :value="$summary['transactions'].' transaksi'" icon="receipt" tone="indigo" />
                <x-stat-card label="Total belanja" :value="rupiah($summary['total_spent'])" icon="banknotes" tone="emerald" />
                <x-stat-card label="Terakhir order" :value="$summary['last_order'] ? \Illuminate\Support\Carbon::parse($summary['last_order'])->format('d M Y') : '—'" icon="clock" tone="sky" />
            </div>

            <div data-reveal class="card overflow-hidden">
                <div class="border-b border-slate-100 p-5 sm:p-6">
                    <h2 class="section-title">Riwayat transaksi</h2>
                    <p class="muted mt-0.5">Semua transaksi yang tercatat untuk pelanggan ini.</p>
                </div>

                @if ($transactions->isEmpty())
                    <x-empty-state title="Belum ada transaksi" description="Pelanggan ini belum memiliki riwayat pembelian." icon="receipt" />
                @else
                    {{-- Mobile cards --}}
                    <div class="space-y-3 p-4 md:hidden">
                        @foreach ($transactions as $transaction)
                            <a href="{{ route('transactions.show', $transaction) }}" class="block rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-bold text-indigo-600">{{ $transaction->invoice_number }}</p>
                                        <p class="mt-0.5 text-[11px] text-slate-400">{{ id_date($transaction->transaction_date, true) }} · {{ $transaction->type_label }}</p>
                                    </div>
                                    <x-badge :tone="$transaction->payment_status_badge">{{ $transaction->payment_status_label }}</x-badge>
                                </div>
                                <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-2.5">
                                    <span class="text-[11px] text-slate-400">Total belanja</span>
                                    <span class="text-sm font-extrabold text-slate-900">{{ rupiah($transaction->total_amount) }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <div class="hidden table-wrap md:block">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Invoice</th>
                                    <th>Tanggal</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-900/[0.06]">
                                @foreach ($transactions as $transaction)
                                    <tr>
                                        <td>
                                            <a href="{{ route('transactions.show', $transaction) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">
                                                {{ $transaction->invoice_number }}
                                            </a>
                                            <p class="text-[11px] text-slate-400">{{ $transaction->type_label }}</p>
                                        </td>
                                        <td class="text-xs text-slate-600">{{ id_date($transaction->transaction_date, true) }}</td>
                                        <td class="text-sm font-semibold text-slate-800">{{ rupiah($transaction->total_amount) }}</td>
                                        <td><x-badge :tone="$transaction->payment_status_badge">{{ $transaction->payment_status_label }}</x-badge></td>
                                        <td>
                                            <a href="{{ route('transactions.show', $transaction) }}" class="flex justify-end p-2 text-slate-400 hover:text-indigo-600">
                                                <x-icon name="chevron-right" class="h-4 w-4" />
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-slate-100 px-5 py-4">{{ $transactions->links() }}</div>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div data-reveal class="card p-5 sm:p-6">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-lg font-extrabold text-indigo-600">
                    {{ collect(explode(' ', $customer->name))->take(2)->map(fn($p) => mb_substr($p, 0, 1))->implode('') }}
                </div>
                <h2 class="mt-4 text-base font-bold text-slate-900">{{ $customer->name }}</h2>
                <p class="mt-1 text-xs text-slate-500">Pelanggan sejak {{ id_date($customer->created_at) }}</p>

                <dl class="mt-5 space-y-3 text-sm">
                    <div class="flex items-start gap-3">
                        <x-icon name="phone" class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" />
                        <div><dt class="text-[10px] uppercase tracking-wider text-slate-400">Telepon</dt><dd class="text-slate-700">{{ $customer->phone ?? '—' }}</dd></div>
                    </div>
                    <div class="flex items-start gap-3">
                        <x-icon name="mail" class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" />
                        <div><dt class="text-[10px] uppercase tracking-wider text-slate-400">Email</dt><dd class="break-all text-slate-700">{{ $customer->email ?? '—' }}</dd></div>
                    </div>
                    <div class="flex items-start gap-3">
                        <x-icon name="map-pin" class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" />
                        <div><dt class="text-[10px] uppercase tracking-wider text-slate-400">Alamat</dt><dd class="text-slate-700">{{ $customer->address ?? '—' }}</dd></div>
                    </div>
                </dl>

                @if ($customer->notes)
                    <div class="mt-5 rounded-xl bg-amber-50 p-3 text-xs text-amber-800">
                        <span class="font-semibold">Catatan:</span> {{ $customer->notes }}
                    </div>
                @endif
            </div>

            <div data-reveal class="card p-5 sm:p-6">
                <h2 class="section-title">Aksi cepat</h2>
                <div class="mt-4 space-y-2">
                    <a href="{{ route('pos.index') }}" class="btn-primary w-full"><x-icon name="cart" class="h-4 w-4" /> Transaksi Baru</a>
                    <a href="{{ route('customers.edit', $customer) }}" class="btn-secondary w-full"><x-icon name="edit" class="h-4 w-4" /> Edit Pelanggan</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
