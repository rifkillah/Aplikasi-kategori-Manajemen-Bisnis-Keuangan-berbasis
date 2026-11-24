{{-- Workspace card --}}
<div class="relative mx-4 mt-2 rounded-2xl border border-white/10 bg-white/[0.08] p-3 shadow-[inset_0_1px_0_rgba(255,255,255,0.08)] backdrop-blur">
    <div class="flex items-center gap-3">
        @if ($currentTenant?->logo)
            <img src="{{ $currentTenant->logo_url }}"
                 alt="Logo {{ $currentTenant->business_name }}"
                 class="h-10 w-10 rounded-xl object-cover ring-1 ring-white/15">
        @else
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-sm font-bold text-white shadow-lg shadow-indigo-950/40 ring-1 ring-white/20">
                {{ mb_substr($currentTenant->business_name ?? 'K', 0, 1) }}
            </div>
        @endif
        <div class="min-w-0">
            <p class="truncate text-sm font-semibold text-white">{{ $currentTenant->business_name ?? 'Bisnis Anda' }}</p>
            <p class="text-[11px] font-medium text-indigo-300">{{ $user->role_label ?? 'Anggota' }}</p>
        </div>
    </div>
</div>

<nav class="relative mt-5 flex-1 space-y-6 overflow-y-auto overscroll-contain px-3 pb-6">
    <div>
        <p class="px-3 pb-2 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">Ringkasan</p>
        <div class="space-y-1">
            <x-sidebar-link :href="route('dashboard')" icon="home" :active="request()->routeIs('dashboard')">Dashboard</x-sidebar-link>
        </div>
    </div>

    @if ($user->canOperateCashier())
        <div>
            <p class="px-3 pb-2 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">Penjualan</p>
            <div class="space-y-1">
                <x-sidebar-link :href="route('pos.index')" icon="cart" :active="request()->routeIs('pos.*')">Kasir / POS</x-sidebar-link>
                <x-sidebar-link :href="route('transactions.index')" icon="receipt" :active="request()->routeIs('transactions.*')">Transaksi</x-sidebar-link>
                <x-sidebar-link :href="route('customers.index')" icon="users" :active="request()->routeIs('customers.*')">Pelanggan</x-sidebar-link>
            </div>
        </div>
    @endif

    @if ($user->canManageInventory())
        <div>
            <p class="px-3 pb-2 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">Inventaris</p>
            <div class="space-y-1">
                <x-sidebar-link :href="route('products.index')" icon="cube" :active="request()->routeIs('products.*')">Produk</x-sidebar-link>
                <x-sidebar-link :href="route('categories.index')" icon="tag" :active="request()->routeIs('categories.*')">Kategori</x-sidebar-link>
                <x-sidebar-link :href="route('stock.index')" icon="archive" :active="request()->routeIs('stock.*')">Pergerakan Stok</x-sidebar-link>
            </div>
        </div>
    @endif

    @if ($user->isOwner())
        <div>
            <p class="px-3 pb-2 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">Bisnis</p>
            <div class="space-y-1">
                <x-sidebar-link :href="route('finance.index')" icon="wallet" :active="request()->routeIs('finance.*')">Kas & Keuangan</x-sidebar-link>
                <x-sidebar-link :href="route('reports.index')" icon="chart" :active="request()->routeIs('reports.*')">Laporan</x-sidebar-link>
                <x-sidebar-link :href="route('team.index')" icon="shield" :active="request()->routeIs('team.*')">Tim & Akses</x-sidebar-link>
                <x-sidebar-link :href="route('settings.edit')" icon="settings" :active="request()->routeIs('settings.*')">Pengaturan</x-sidebar-link>
            </div>
        </div>
    @endif
</nav>

<div class="relative border-t border-white/10 p-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))]">
    <a href="{{ route('appearance.edit') }}"
       class="flex items-center gap-3 rounded-2xl px-2 py-2 text-sm font-medium text-slate-300 transition hover:bg-white/[0.07] hover:text-white active:scale-[0.98]">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white/[0.06] text-indigo-300 ring-1 ring-inset ring-white/10">
            <x-icon name="palette" class="h-4 w-4" />
        </span>
        <span class="min-w-0 flex-1">
            <span class="block truncate">Tampilan & Latar</span>
            <span class="block truncate text-[10px] font-normal text-slate-400">
                {{ ['system' => 'Ikuti sistem', 'light' => 'Terang', 'dark' => 'Gelap'][$themeMode] ?? 'Ikuti sistem' }}
            </span>
        </span>
        <x-icon name="chevron-right" class="h-4 w-4 text-slate-400" />
    </a>

    <div class="mt-1 flex items-center gap-3 rounded-2xl px-2 py-2">
        <x-user-avatar :user="$user" size="h-9 w-9" text="text-[11px]" />
        <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-semibold text-white">{{ $user->name }}</p>
            <p class="truncate text-[11px] text-slate-400">{{ $user->email }}</p>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" title="Keluar" aria-label="Keluar"
                    class="flex h-10 w-10 touch-manipulation items-center justify-center rounded-full text-slate-400 transition hover:bg-white/10 hover:text-rose-300 active:scale-90">
                <x-icon name="logout" class="h-4 w-4" />
            </button>
        </form>
    </div>
</div>
