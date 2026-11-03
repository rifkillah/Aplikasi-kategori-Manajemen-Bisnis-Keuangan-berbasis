<x-app-layout>
    <x-page-header :title="'Invoice '.$transaction->invoice_number" :subtitle="$transaction->type_label.' · '.id_date($transaction->transaction_date, true)">
        <x-slot name="actions">
            <a href="{{ route('transactions.index') }}" class="btn-secondary">
                <x-icon name="arrow-left" class="h-4 w-4" /> Transaksi
            </a>
            <a href="{{ route('transactions.pdf', $transaction) }}" class="btn-primary">
                <x-icon name="download" class="h-4 w-4" /> Unduh PDF
            </a>
        </x-slot>
    </x-page-header>

    @if ($transaction->is_void)
        <div class="mb-5 flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3">
            <x-icon name="alert" class="mt-0.5 h-5 w-5 shrink-0 text-rose-600" />
            <div>
                <p class="text-sm font-semibold text-rose-900">Transaksi telah dibatalkan</p>
                <p class="mt-0.5 text-xs text-rose-700">Dibatalkan pada {{ id_date($transaction->voided_at, true) }}. Stok produk sudah dikembalikan.</p>
            </div>
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <div class="card overflow-hidden">
                <div class="flex flex-col gap-4 border-b border-slate-100 bg-slate-50/50 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                    <div class="flex items-center gap-3">
                        <x-application-logo class="h-11 w-11" />
                        <div>
                            <p class="text-sm font-extrabold text-slate-900">{{ $transaction->tenant->business_name }}</p>
                            <p class="text-[11px] text-slate-500">{{ $transaction->tenant->address ?: 'Alamat bisnis belum diisi' }}</p>
                        </div>
                    </div>
                    <div class="text-left sm:text-right">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Invoice</p>
                        <p class="text-sm font-bold text-slate-900">{{ $transaction->invoice_number }}</p>
                        <div class="mt-1 flex items-center gap-2 sm:justify-end">
                            <x-badge :tone="$transaction->payment_status_badge">{{ $transaction->payment_status_label }}</x-badge>
                            @if ($transaction->is_void)
                                <x-badge tone="danger">Dibatalkan</x-badge>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 p-5 sm:grid-cols-2 sm:p-6">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Ditagihkan kepada</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900">{{ $transaction->customer?->name ?? 'Pelanggan umum' }}</p>
                        <p class="mt-1 text-xs leading-relaxed text-slate-500">
                            {{ $transaction->customer?->phone ?? 'Tanpa nomor telepon' }}<br>
                            {{ $transaction->customer?->address ?? 'Tanpa alamat' }}
                        </p>
                    </div>
                    <div class="sm:text-right">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Detail transaksi</p>
                        <p class="mt-2 text-xs text-slate-600">Tanggal: <span class="font-medium text-slate-800">{{ id_date($transaction->transaction_date, true) }}</span></p>
                        <p class="mt-1 text-xs text-slate-600">Kasir: <span class="font-medium text-slate-800">{{ $transaction->user?->name ?? 'Sistem' }}</span></p>
                        <p class="mt-1 text-xs text-slate-600">Metode: <span class="font-medium uppercase text-slate-800">{{ $transaction->payment_method ?? '—' }}</span></p>
                    </div>
                </div>

                <div class="border-t border-slate-100">
                    @if ($transaction->details->isNotEmpty())
                        <div class="table-wrap">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th>Harga</th>
                                        <th>Qty</th>
                                        <th class="text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50 bg-white">
                                    @foreach ($transaction->details as $detail)
                                        <tr>
                                            <td>
                                                <p class="text-sm font-semibold text-slate-800">{{ $detail->product_name }}</p>
                                                <p class="text-[11px] text-slate-400">{{ $detail->sku }}</p>
                                            </td>
                                            <td class="text-sm">{{ rupiah($detail->price) }}</td>
                                            <td class="text-sm">{{ $detail->quantity }}</td>
                                            <td class="text-right text-sm font-semibold text-slate-800">{{ rupiah($detail->subtotal) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-5 sm:p-6">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Deskripsi</p>
                            <p class="mt-1 text-sm font-medium text-slate-800">{{ $transaction->description ?? '—' }}</p>
                        </div>
                    @endif
                </div>

                <div class="border-t border-slate-100 p-5 sm:p-6">
                    <div class="ml-auto max-w-xs space-y-2 text-sm">
                        <div class="flex justify-between text-slate-500">
                            <span>Subtotal</span>
                            <span class="font-medium text-slate-700">{{ rupiah($transaction->subtotal) }}</span>
                        </div>
                        @if ((float) $transaction->discount > 0)
                            <div class="flex justify-between text-rose-500">
                                <span>Diskon</span>
                                <span class="font-medium">- {{ rupiah($transaction->discount) }}</span>
                            </div>
                        @endif
                        @if ((float) $transaction->tax > 0)
                            <div class="flex justify-between text-slate-500">
                                <span>Pajak</span>
                                <span class="font-medium text-slate-700">{{ rupiah($transaction->tax) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between border-t border-slate-200 pt-2 text-base">
                            <span class="font-semibold text-slate-800">Total</span>
                            <span class="font-extrabold text-slate-900">{{ rupiah($transaction->total_amount) }}</span>
                        </div>
                        <div class="flex justify-between text-xs text-slate-500">
                            <span>Dibayar</span>
                            <span class="font-medium text-slate-700">{{ rupiah($transaction->paid_amount) }}</span>
                        </div>
                        @if ((float) $transaction->paid_amount > (float) $transaction->total_amount)
                            <div class="flex justify-between text-xs text-emerald-600">
                                <span>Kembalian</span>
                                <span class="font-medium">{{ rupiah($transaction->paid_amount - $transaction->total_amount) }}</span>
                            </div>
                        @endif
                        @if ((float) $transaction->total_amount > (float) $transaction->paid_amount)
                            <div class="flex justify-between text-xs text-rose-600">
                                <span>Sisa tagihan</span>
                                <span class="font-medium">{{ rupiah($transaction->total_amount - $transaction->paid_amount) }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                @if ($transaction->notes)
                    <div class="border-t border-slate-100 bg-amber-50/40 p-5 text-xs text-amber-800 sm:p-6">
                        <span class="font-semibold">Catatan:</span> {{ $transaction->notes }}
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div class="card p-5 sm:p-6">
                <h2 class="section-title">Aksi</h2>
                <div class="mt-4 space-y-2">
                    <a href="{{ route('transactions.pdf', $transaction) }}" class="btn-primary w-full">
                        <x-icon name="printer" class="h-4 w-4" /> Cetak Invoice
                    </a>
                    @if ($transaction->customer)
                        <a href="{{ route('customers.show', $transaction->customer) }}" class="btn-secondary w-full">
                            <x-icon name="user" class="h-4 w-4" /> Lihat Pelanggan
                        </a>
                    @endif
                </div>

                @if ($transaction->type === 'sale' && ! $transaction->is_void && auth()->user()->isOwner())
                    <div class="mt-5 border-t border-slate-100 pt-4">
                        <p class="text-xs font-semibold text-slate-700">Batalkan transaksi</p>
                        <p class="mt-1 text-[11px] leading-relaxed text-slate-500">Stok akan dikembalikan otomatis dan invoice ditandai batal. Tindakan ini tidak dapat dibatalkan.</p>
                        <form method="POST" action="{{ route('transactions.void', $transaction) }}" class="mt-3"
                              onsubmit="return confirm('Batalkan transaksi {{ $transaction->invoice_number }}? Stok akan dikembalikan.')">
                            @csrf
                            <button type="submit" class="btn-danger btn-sm w-full">
                                <x-icon name="refresh" class="h-3.5 w-3.5" /> Batalkan Transaksi
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            <div class="card p-5 sm:p-6">
                <h2 class="section-title">Ringkasan pembayaran</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">Status</dt>
                        <dd><x-badge :tone="$transaction->payment_status_badge">{{ $transaction->payment_status_label }}</x-badge></dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">Metode</dt>
                        <dd class="font-medium uppercase text-slate-800">{{ $transaction->payment_method ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">Total</dt>
                        <dd class="font-semibold text-slate-900">{{ rupiah($transaction->total_amount) }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">Dibayar</dt>
                        <dd class="font-medium text-slate-800">{{ rupiah($transaction->paid_amount) }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <x-icon name="shield" class="h-4 w-4" />
                    </div>
                    <p class="text-[11px] leading-relaxed text-slate-500">
                        Invoice ini dibuat otomatis dan dilindungi oleh kebijakan akses bisnis. Hanya pengguna dari {{ $transaction->tenant->business_name }} yang dapat melihatnya.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
