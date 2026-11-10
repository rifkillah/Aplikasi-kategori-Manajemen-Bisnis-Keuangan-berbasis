<x-app-layout>
    @php
        $hour = (int) now()->format('H');
        $greeting = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 19 ? 'Selamat sore' : 'Selamat malam'));
        $user = auth()->user();
    @endphp

    <x-page-header
        :title="$greeting.', '.explode(' ', $user->name)[0].' 👋'"
        :subtitle="'Ringkasan operasional '.(auth()->user()->tenant?->business_name ?? 'bisnis Anda').' per '.now()->translatedFormat('d F Y').'.'">
        <x-slot name="actions">
            @if ($user->canOperateCashier())
                <a href="{{ route('pos.index') }}" class="btn-primary">
                    <x-icon name="cart" class="h-4 w-4" /> Buka Kasir
                </a>
            @endif
            @if ($user->isOwner())
                <a href="{{ route('reports.index') }}" class="btn-secondary">
                    <x-icon name="chart" class="h-4 w-4" /> Lihat Laporan
                </a>
            @endif
        </x-slot>
    </x-page-header>

    @if ($user->canOperateCashier())
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card label="Penjualan hari ini" :value="rupiah($salesToday)" icon="banknotes" tone="indigo" :hint="$transactionsToday.' transaksi selesai'" />
            <x-stat-card label="Omzet bulan ini" :value="rupiah($monthRevenue)" icon="trending-up" tone="sky" hint="Penjualan berstatus selesai" />
            <x-stat-card label="Laba bersih bulan ini" :value="rupiah($monthProfit)" icon="wallet" :tone="$monthProfit >= 0 ? 'emerald' : 'rose'" hint="Setelah HPP, beban & pemasukan lain" />
            <x-stat-card label="Stok menipis" :value="$lowStockCount.' produk'" icon="alert" :tone="$lowStockCount > 0 ? 'amber' : 'slate'" :hint="$totalProducts.' produk aktif'" />
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card label="Total produk aktif" :value="$totalProducts.' produk'" icon="cube" tone="indigo" hint="Tersedia di inventaris" />
            <x-stat-card label="Nilai stok" :value="rupiah($stockValue)" icon="banknotes" tone="emerald" hint="Berdasarkan harga beli" />
            <x-stat-card label="Stok menipis" :value="$lowStockCount.' produk'" icon="alert" :tone="$lowStockCount > 0 ? 'amber' : 'slate'" hint="Perlu segera restock" />
            <x-stat-card label="Transaksi hari ini" :value="$transactionsToday.' transaksi'" icon="receipt" tone="sky" hint="Seluruh transaksi kasir" />
        </div>
    @endif

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        {{-- Chart --}}
        <div class="card card-hover xl:col-span-2" data-reveal>
            <div class="flex flex-col gap-4 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                <div>
                    <h2 class="section-title">Arus kas 6 bulan terakhir</h2>
                    <p class="muted mt-0.5">Perbandingan pemasukan dan pengeluaran</p>
                </div>
                <div class="flex items-center gap-4 text-xs font-medium text-slate-500">
                    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-indigo-500"></span> Pemasukan</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-rose-400"></span> Pengeluaran</span>
                </div>
            </div>
            <div class="p-5 sm:p-6">
                <div class="h-[290px]">
                    <canvas id="cashflowChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Top products --}}
        <div class="card card-hover" data-reveal>
            <div class="flex items-center justify-between border-b border-slate-100 p-5 sm:p-6">
                <div>
                    <h2 class="section-title">Produk terlaris</h2>
                    <p class="muted mt-0.5">Berdasarkan omzet bulan ini</p>
                </div>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 ring-1 ring-inset ring-amber-500/20">
                    <x-icon name="sparkles" class="h-4.5 w-4.5" />
                </span>
            </div>
            <div class="space-y-5 p-5 sm:p-6">
                @forelse ($topProducts as $index => $product)
                    @php $maxRevenue = (float) $topProducts->max('total_revenue') ?: 1; @endphp
                    <div>
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex min-w-0 items-start gap-3">
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-white/60 text-[11px] font-bold text-slate-500 ring-1 ring-inset ring-slate-900/5">{{ $index + 1 }}</span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-800">{{ $product->product_name }}</p>
                                    <p class="text-[11px] text-slate-400">{{ number_format($product->total_qty, 0, ',', '.') }} terjual</p>
                                </div>
                            </div>
                            <p class="shrink-0 text-xs font-semibold text-slate-700">{{ rupiah($product->total_revenue) }}</p>
                        </div>
                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-900/[0.06]">
                            <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-violet-500" style="width: {{ max(6, ((float) $product->total_revenue / $maxRevenue) * 100) }}%"></div>
                        </div>
                    </div>
                @empty
                    <x-empty-state title="Belum ada penjualan" description="Produk terlaris muncul setelah ada transaksi bulan ini." icon="cube" />
                @endforelse
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 {{ $user->canOperateCashier() ? 'xl:grid-cols-5' : 'xl:grid-cols-3' }}">
        {{-- Recent transactions --}}
        @if ($user->canOperateCashier())
            <div class="card card-hover overflow-hidden xl:col-span-3" data-reveal>
                <div class="flex items-center justify-between border-b border-slate-100 p-5 sm:p-6">
                    <div>
                        <h2 class="section-title">Transaksi terbaru</h2>
                        <p class="muted mt-0.5">Aktivitas kasir terakhir</p>
                    </div>
                    <a href="{{ route('transactions.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-700">
                        Lihat semua <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                    </a>
                </div>

                @if ($recentTransactions->isEmpty())
                    <x-empty-state title="Belum ada transaksi" description="Mulai transaksi pertama Anda melalui menu Kasir." icon="receipt" />
                @else
                    {{-- Mobile list --}}
                    <div class="divide-y divide-slate-100 md:hidden">
                        @foreach ($recentTransactions as $transaction)
                            <a href="{{ route('transactions.show', $transaction) }}" class="flex items-center gap-3 px-4 py-3.5 transition active:bg-slate-50">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <p class="truncate text-sm font-semibold text-slate-800">{{ $transaction->invoice_number }}</p>
                                        @if ($transaction->is_void)
                                            <x-badge tone="danger">Void</x-badge>
                                        @endif
                                    </div>
                                    <p class="mt-0.5 truncate text-[11px] text-slate-400">
                                        {{ $transaction->customer?->name ?? 'Pelanggan umum' }} · {{ id_date($transaction->transaction_date, true) }}
                                    </p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <p class="text-sm font-bold text-slate-800">{{ rupiah($transaction->total_amount) }}</p>
                                    <x-badge :tone="$transaction->payment_status_badge">{{ $transaction->payment_status_label }}</x-badge>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <div class="hidden table-wrap md:block">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Invoice</th>
                                    <th>Pelanggan</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Waktu</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-900/[0.06]">
                                @foreach ($recentTransactions as $transaction)
                                    <tr>
                                        <td>
                                            <a href="{{ route('transactions.show', $transaction) }}" class="font-semibold text-indigo-600 hover:text-indigo-700">
                                                {{ $transaction->invoice_number }}
                                            </a>
                                            <p class="text-[11px] text-slate-400">{{ $transaction->type_label }}</p>
                                        </td>
                                        <td>
                                            <p class="text-sm text-slate-700">{{ $transaction->customer?->name ?? 'Pelanggan umum' }}</p>
                                            <p class="text-[11px] text-slate-400">{{ $transaction->user?->name }}</p>
                                        </td>
                                        <td class="font-semibold text-slate-800">{{ rupiah($transaction->total_amount) }}</td>
                                        <td>
                                            <x-badge :tone="$transaction->payment_status_badge">{{ $transaction->payment_status_label }}</x-badge>
                                            @if ($transaction->is_void)
                                                <x-badge tone="danger">Dibatalkan</x-badge>
                                            @endif
                                        </td>
                                        <td class="text-xs text-slate-500">{{ id_date($transaction->transaction_date, true) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif

        {{-- Low stock --}}
        <div class="card card-hover xl:col-span-2" data-reveal>
            <div class="flex items-center justify-between border-b border-slate-100 p-5 sm:p-6">
                <div>
                    <h2 class="section-title">Perlu restock</h2>
                    <p class="muted mt-0.5">Stok di bawah batas minimum</p>
                </div>
                <a href="{{ route('products.index', ['status' => 'low']) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-700">
                    Kelola <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                </a>
            </div>

            <div class="p-5 sm:p-6">
                @forelse ($lowStockProducts as $product)
                    <div class="flex items-center gap-3 border-b border-slate-100 py-3 first:pt-0 last:border-0 last:pb-0">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl ring-1 ring-inset {{ $product->stock <= 0 ? 'bg-rose-500/10 text-rose-600 ring-rose-500/20' : 'bg-amber-500/10 text-amber-600 ring-amber-500/20' }}">
                            <x-icon name="cube" class="h-4 w-4" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-slate-800">{{ $product->name }}</p>
                            <p class="text-[11px] text-slate-400">{{ $product->sku }} · min. {{ $product->min_stock_alert }} {{ $product->unit }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold {{ $product->stock <= 0 ? 'text-rose-600' : 'text-amber-600' }}">{{ $product->stock }}</p>
                            <p class="text-[10px] text-slate-400">tersisa</p>
                        </div>
                        <a href="{{ route('stock.create', ['product' => $product->id, 'type' => 'in']) }}"
                           class="rounded-full border border-white/70 bg-white/50 p-2 text-slate-500 backdrop-blur transition hover:border-indigo-200 hover:bg-white/80 hover:text-indigo-600" title="Restock">
                            <x-icon name="plus" class="h-4 w-4" />
                        </a>
                    </div>
                @empty
                    <div class="flex flex-col items-center py-8 text-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-600 ring-1 ring-inset ring-emerald-500/20">
                            <x-icon name="check" class="h-6 w-6" />
                        </div>
                        <p class="mt-3 text-sm font-semibold text-slate-800">Semua stok aman</p>
                        <p class="mt-1 text-xs text-slate-500">Tidak ada produk di bawah batas minimum.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const canvas = document.getElementById('cashflowChart');
                if (!canvas || typeof Chart === 'undefined') return;

                const isDark = () => document.documentElement.dataset.theme === 'dark';

                fetch('{{ route('dashboard.chart') }}', { headers: { 'Accept': 'application/json' } })
                    .then(response => response.json())
                    .then(data => {
                        const chart = new Chart(canvas, {
                            type: 'bar',
                            data: {
                                labels: data.labels,
                                datasets: [
                                    {
                                        label: 'Pemasukan',
                                        data: data.income,
                                        backgroundColor: 'rgba(99, 102, 241, 0.85)',
                                        hoverBackgroundColor: 'rgba(79, 70, 229, 1)',
                                        borderRadius: 8,
                                        maxBarThickness: 26,
                                    },
                                    {
                                        label: 'Pengeluaran',
                                        data: data.expense,
                                        backgroundColor: 'rgba(251, 113, 133, 0.55)',
                                        hoverBackgroundColor: 'rgba(244, 63, 94, 0.8)',
                                        borderRadius: 8,
                                        maxBarThickness: 26,
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                interaction: { mode: 'index', intersect: false },
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        backgroundColor: '#0f172a',
                                        padding: 12,
                                        cornerRadius: 16,
                                        callbacks: {
                                            label: function (context) {
                                                return context.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    x: {
                                        grid: { display: false },
                                        border: { display: false },
                                        ticks: { color: '#94a3b8', font: { size: 11, family: 'Plus Jakarta Sans' } }
                                    },
                                    y: {
                                        beginAtZero: true,
                                        grid: { color: isDark() ? 'rgba(148, 163, 184, 0.18)' : 'rgba(226, 232, 240, 0.6)' },
                                        border: { display: false },
                                        ticks: {
                                            color: '#94a3b8',
                                            font: { size: 11, family: 'Plus Jakarta Sans' },
                                            callback: function (value) {
                                                if (value >= 1000000) return 'Rp ' + (value / 1000000) + ' jt';
                                                if (value >= 1000) return 'Rp ' + (value / 1000) + ' rb';
                                                return 'Rp ' + value;
                                            }
                                        }
                                    }
                                }
                            }
                        });

                        window.addEventListener('app-theme-changed', () => {
                            chart.options.scales.y.grid.color = isDark()
                                ? 'rgba(148, 163, 184, 0.18)'
                                : 'rgba(226, 232, 240, 0.6)';
                            chart.update('none');
                        });
                    })
                    .catch(() => {
                        canvas.parentElement.innerHTML = '<div class="flex h-full items-center justify-center text-sm text-slate-400">Gagal memuat grafik.</div>';
                    });
            });
        </script>
    @endpush
</x-app-layout>
