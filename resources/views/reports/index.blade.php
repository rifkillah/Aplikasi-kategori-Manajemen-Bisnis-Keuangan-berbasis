<x-app-layout>
    <x-page-header title="Laporan Bisnis" subtitle="Analisis penjualan, laba rugi, dan performa produk pada rentang tanggal tertentu.">
        <x-slot name="actions">
            <a href="{{ route('reports.export', ['from' => $from, 'to' => $to]) }}" class="btn-primary">
                <x-icon name="download" class="h-4 w-4" /> Ekspor CSV
            </a>
        </x-slot>
    </x-page-header>

    <div class="card mb-6 p-4 sm:p-5">
        <form method="GET" action="{{ route('reports.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div>
                    <label for="from" class="label">Dari tanggal</label>
                    <input id="from" type="date" name="from" value="{{ $from }}" class="input py-2.5 sm:w-44">
                </div>
                <div>
                    <label for="to" class="label">Sampai tanggal</label>
                    <input id="to" type="date" name="to" value="{{ $to }}" class="input py-2.5 sm:w-44">
                </div>
                <button type="submit" class="btn-secondary sm:mb-0"><x-icon name="filter" class="h-4 w-4" /> Terapkan</button>
            </div>
            <div class="flex flex-wrap gap-2 text-xs">
                <a href="{{ route('reports.index', ['from' => now()->startOfMonth()->toDateString(), 'to' => now()->toDateString()]) }}" class="btn-ghost">Bulan ini</a>
                <a href="{{ route('reports.index', ['from' => now()->subDays(29)->toDateString(), 'to' => now()->toDateString()]) }}" class="btn-ghost">30 hari</a>
                <a href="{{ route('reports.index', ['from' => now()->startOfYear()->toDateString(), 'to' => now()->toDateString()]) }}" class="btn-ghost">Tahun ini</a>
            </div>
        </form>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card label="Omzet penjualan" :value="rupiah($revenue)" icon="banknotes" tone="indigo" :hint="$transactionCount.' transaksi selesai'" />
        <x-stat-card label="Item terjual" :value="number_format($itemsSold, 0, ',', '.').' item'" icon="cube" tone="sky" hint="Total kuantitas produk" />
        <x-stat-card label="Laba kotor" :value="rupiah($grossProfit)" icon="trending-up" tone="emerald" hint="Omzet dikurangi HPP" />
        <x-stat-card label="Laba bersih" :value="rupiah($netProfit)" icon="wallet" :tone="$netProfit >= 0 ? 'amber' : 'rose'" hint="Setelah beban & pemasukan lain" />
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <div class="card xl:col-span-2">
            <div class="flex flex-col gap-3 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                <div>
                    <h2 class="section-title">Arus kas harian</h2>
                    <p class="muted mt-0.5">{{ \Illuminate\Support\Carbon::parse($from)->translatedFormat('d M Y') }} – {{ \Illuminate\Support\Carbon::parse($to)->translatedFormat('d M Y') }}</p>
                </div>
                <div class="flex items-center gap-4 text-xs font-medium text-slate-500">
                    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-indigo-500"></span> Pemasukan</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-rose-400"></span> Pengeluaran</span>
                </div>
            </div>
            <div class="p-5 sm:p-6">
                @if ($daily->isEmpty())
                    <x-empty-state title="Belum ada data pada rentang ini" description="Coba pilih rentang tanggal lain atau catat transaksi terlebih dahulu." icon="chart" />
                @else
                    <div class="h-[300px]"><canvas id="reportChart"></canvas></div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="border-b border-slate-100 p-5 sm:p-6">
                <h2 class="section-title">Ringkasan laba rugi</h2>
                <p class="muted mt-0.5">Periode {{ \Illuminate\Support\Carbon::parse($from)->translatedFormat('d M') }} – {{ \Illuminate\Support\Carbon::parse($to)->translatedFormat('d M Y') }}</p>
            </div>
            <div class="space-y-3 p-5 sm:p-6">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-500">Omzet penjualan</span>
                    <span class="font-semibold text-slate-800">{{ rupiah($revenue) }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-500">Harga pokok penjualan</span>
                    <span class="font-semibold text-rose-600">− {{ rupiah($cogs) }}</span>
                </div>
                <div class="flex items-center justify-between border-t border-slate-100 pt-3 text-sm">
                    <span class="font-semibold text-slate-700">Laba kotor</span>
                    <span class="font-bold text-slate-900">{{ rupiah($grossProfit) }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-500">Pemasukan lain</span>
                    <span class="font-semibold text-emerald-600">+ {{ rupiah($otherIncome) }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-500">Beban operasional</span>
                    <span class="font-semibold text-rose-600">− {{ rupiah($expenses) }}</span>
                </div>
                <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3">
                    <span class="text-sm font-bold text-slate-700">Laba bersih</span>
                    <span class="text-base font-extrabold {{ $netProfit >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ rupiah($netProfit) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-6 overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 p-5 sm:p-6">
            <div>
                <h2 class="section-title">Produk terlaris</h2>
                <p class="muted mt-0.5">10 produk dengan omzet tertinggi pada periode ini</p>
            </div>
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                <x-icon name="sparkles" class="h-4.5 w-4.5" />
            </span>
        </div>

        @if ($topProducts->isEmpty())
            <x-empty-state title="Belum ada penjualan" description="Produk terlaris akan muncul setelah ada transaksi pada periode ini." icon="cube" />
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Produk</th>
                            <th class="text-right">Terjual</th>
                            <th class="text-right">Omzet</th>
                            <th class="text-right">Kontribusi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 bg-white">
                        @foreach ($topProducts as $index => $product)
                            <tr>
                                <td class="text-xs font-bold text-slate-400">{{ $index + 1 }}</td>
                                <td>
                                    <p class="text-sm font-semibold text-slate-800">{{ $product->product_name }}</p>
                                    <p class="text-[11px] text-slate-400">{{ $product->sku ?? '—' }}</p>
                                </td>
                                <td class="text-right text-sm font-semibold text-slate-700">{{ number_format($product->total_qty, 0, ',', '.') }}</td>
                                <td class="text-right text-sm font-semibold text-slate-800">{{ rupiah($product->total_revenue) }}</td>
                                <td class="text-right">
                                    <div class="ml-auto flex w-28 items-center gap-2">
                                        <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100">
                                            <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-violet-500"
                                                 style="width: {{ $revenue > 0 ? min(100, ((float) $product->total_revenue / (float) $revenue) * 100) : 0 }}%"></div>
                                        </div>
                                        <span class="w-10 shrink-0 text-[11px] font-medium text-slate-500">
                                            {{ $revenue > 0 ? round(((float) $product->total_revenue / (float) $revenue) * 100, 1) : 0 }}%
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @push('scripts')
        @if ($daily->isNotEmpty())
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const canvas = document.getElementById('reportChart');
                    if (!canvas || typeof Chart === 'undefined') return;

                    const labels = @json($daily->map(fn ($row) => \Illuminate\Support\Carbon::parse($row['day'])->translatedFormat('d M')));
                    const income = @json($daily->pluck('income'));
                    const expense = @json($daily->pluck('expense'));

                    new Chart(canvas, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'Pemasukan',
                                    data: income,
                                    borderColor: 'rgba(99, 102, 241, 1)',
                                    backgroundColor: 'rgba(99, 102, 241, 0.10)',
                                    fill: true,
                                    tension: 0.35,
                                    borderWidth: 2.5,
                                    pointRadius: 2.5,
                                    pointHoverRadius: 5
                                },
                                {
                                    label: 'Pengeluaran',
                                    data: expense,
                                    borderColor: 'rgba(251, 113, 133, 1)',
                                    backgroundColor: 'rgba(251, 113, 133, 0.08)',
                                    fill: true,
                                    tension: 0.35,
                                    borderWidth: 2.5,
                                    pointRadius: 2.5,
                                    pointHoverRadius: 5
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
                                    cornerRadius: 10,
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
                                    grid: { color: 'rgba(226, 232, 240, 0.6)' },
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
                });
            </script>
        @endif
    @endpush
</x-app-layout>
