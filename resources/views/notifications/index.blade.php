<x-app-layout>
    @php
        $unreadOnPage = $notifications->getCollection()->whereNull('read_at')->count();
        $unreadTotal = auth()->user()->unreadNotifications()->count();
    @endphp

    <x-page-header title="Notifikasi" subtitle="Pemberitahuan stok menipis dan aktivitas penting lainnya.">
        <x-slot name="actions">
            @if ($unreadTotal > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="btn-secondary"><x-icon name="check-simple" class="h-4 w-4" /> Tandai Semua Dibaca</button>
                </form>
            @endif
        </x-slot>
    </x-page-header>

    <div class="grid gap-6 xl:grid-cols-3">
        <div data-reveal class="card overflow-hidden xl:col-span-2">
            <div class="flex items-center justify-between border-b border-slate-100 p-5 sm:p-6">
                <div>
                    <h2 class="section-title">Kotak masuk</h2>
                    <p class="muted mt-0.5">{{ $unreadTotal }} belum dibaca · {{ $notifications->total() }} total notifikasi</p>
                </div>
                @if ($unreadOnPage > 0)
                    <span class="badge badge-info">{{ $unreadOnPage }} baru di halaman ini</span>
                @endif
            </div>

            @if ($notifications->isEmpty())
                <x-empty-state title="Belum ada notifikasi" description="Notifikasi stok menipis akan muncul di sini secara otomatis." icon="bell" />
            @else
                <div class="divide-y divide-slate-100">
                    @foreach ($notifications as $notification)
                        @php
                            $data = $notification->data;
                            $isUnread = is_null($notification->read_at);
                        @endphp
                        <a href="{{ route('notifications.read', $notification->id) }}"
                           class="flex gap-4 p-5 transition hover:bg-slate-50 sm:px-6 {{ $isUnread ? 'bg-indigo-50/40' : '' }}">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $isUnread ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-400' }}">
                                <x-icon name="alert" class="h-5 w-5" />
                            </span>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <p class="text-sm font-semibold {{ $isUnread ? 'text-slate-900' : 'text-slate-700' }}">
                                        Stok menipis: {{ $data['product_name'] ?? 'Produk' }}
                                    </p>
                                    <span class="shrink-0 text-[11px] text-slate-400">{{ $notification->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="mt-1 text-xs leading-relaxed text-slate-500">
                                    {{ $data['message'] ?? 'Stok produk Anda berada di bawah batas minimum.' }}
                                </p>
                                @if (isset($data['sku'], $data['stock'], $data['min_stock_alert']))
                                    <div class="mt-2 flex flex-wrap items-center gap-2 text-[11px]">
                                        <span class="rounded-md bg-slate-100 px-1.5 py-0.5 font-medium text-slate-500">{{ $data['sku'] }}</span>
                                        <span class="font-semibold text-amber-600">Sisa {{ $data['stock'] }}</span>
                                        <span class="text-slate-400">· minimum {{ $data['min_stock_alert'] }}</span>
                                    </div>
                                @endif
                            </div>

                            @if ($isUnread)
                                <span class="mt-2 h-2 w-2 shrink-0 rounded-full bg-indigo-500"></span>
                            @endif
                        </a>
                    @endforeach
                </div>
                <div class="border-t border-slate-100 px-5 py-4">{{ $notifications->links() }}</div>
            @endif
        </div>

        <div class="space-y-6">
            <div data-reveal class="card p-5 sm:p-6">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                        <x-icon name="bell" class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Status kotak masuk</p>
                        <p class="text-sm font-bold text-slate-900">{{ $unreadTotal }} belum dibaca</p>
                    </div>
                </div>

                <div class="mt-5 space-y-3 text-xs text-slate-500">
                    <div class="flex items-center justify-between">
                        <span>Total notifikasi</span>
                        <span class="font-semibold text-slate-700">{{ $notifications->total() }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Belum dibaca</span>
                        <span class="font-semibold {{ $unreadTotal > 0 ? 'text-indigo-600' : 'text-emerald-600' }}">{{ $unreadTotal }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Sudah dibaca</span>
                        <span class="font-semibold text-slate-700">{{ max(0, $notifications->total() - $unreadTotal) }}</span>
                    </div>
                </div>
            </div>

            <div data-reveal class="card p-5 sm:p-6">
                <h3 class="text-sm font-bold text-slate-900">Cara kerja notifikasi</h3>
                <ul class="mt-3 space-y-3 text-xs leading-relaxed text-slate-500">
                    <li><span class="font-semibold text-slate-700">Stok menipis</span> dikirim ke owner saat stok produk menyentuh atau melewati batas minimum.</li>
                    <li>Notifikasi yang sama tidak dikirim ulang dalam 6 jam untuk menghindari spam.</li>
                    <li>Klik notifikasi untuk membuka detail produk dan langsung melakukan restock.</li>
                </ul>
            </div>

            <div class="rounded-2xl border border-indigo-100 bg-indigo-50/70 p-4">
                <div class="flex gap-3">
                    <x-icon name="cube" class="mt-0.5 h-4 w-4 shrink-0 text-indigo-600" />
                    <p class="text-[11px] leading-relaxed text-indigo-900">Atur batas minimum stok tiap produk di halaman edit produk agar peringatan lebih akurat.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
