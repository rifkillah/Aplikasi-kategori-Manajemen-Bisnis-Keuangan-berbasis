@php
    $routeName = request()->route()?->getName() ?? 'dashboard';
    $pageTitles = [
        'dashboard' => 'Dashboard',
        'pos.index' => 'Kasir',
        'transactions.index' => 'Transaksi',
        'transactions.show' => 'Detail Transaksi',
        'customers.index' => 'Pelanggan',
        'customers.create' => 'Tambah Pelanggan',
        'customers.show' => 'Detail Pelanggan',
        'customers.edit' => 'Edit Pelanggan',
        'products.index' => 'Produk',
        'products.create' => 'Tambah Produk',
        'products.show' => 'Detail Produk',
        'products.edit' => 'Edit Produk',
        'categories.index' => 'Kategori',
        'stock.index' => 'Riwayat Stok',
        'stock.create' => 'Catat Pergerakan Stok',
        'finance.index' => 'Keuangan',
        'finance.create' => 'Catat Keuangan',
        'reports.index' => 'Laporan',
        'team.index' => 'Tim & Akses',
        'settings.edit' => 'Pengaturan Bisnis',
        'notifications.index' => 'Notifikasi',
        'profile.edit' => 'Profil Saya',
    ];
    $pageTitle = $pageTitles[$routeName] ?? 'Kasirin';
    $user = auth()->user();

    $tabBase = 'flex flex-1 flex-col items-center justify-center gap-1 rounded-[20px] px-2 py-2 text-[10px] font-semibold transition-all duration-200';
    $tabOn = 'bg-indigo-600 text-white shadow-[0_10px_22px_-10px_rgba(79,70,229,0.95)]';
    $tabOff = 'text-slate-500 hover:bg-white/60 hover:text-slate-700';
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#eef0fb">
    <title>{{ $pageTitle }} · Kasirin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body x-data="{ sidebarOpen: false, profileOpen: false }" class="font-sans antialiased">
    {{-- Ambient liquid background --}}
    <div aria-hidden="true" class="pointer-events-none fixed inset-0 z-0 overflow-hidden">
        <div class="absolute -left-44 -top-56 h-[38rem] w-[38rem] animate-blob rounded-full bg-indigo-400/40 blur-[120px]"></div>
        <div class="absolute -right-40 top-6 h-[30rem] w-[30rem] animate-blob rounded-full bg-fuchsia-400/25 blur-[110px] [animation-delay:2s]"></div>
        <div class="absolute -bottom-56 left-1/3 h-[36rem] w-[36rem] animate-blob rounded-full bg-sky-400/25 blur-[130px] [animation-delay:4s]"></div>
        <div class="absolute bottom-0 right-1/4 h-80 w-80 animate-blob rounded-full bg-violet-400/30 blur-[110px] [animation-delay:6s]"></div>
    </div>

    <div class="relative z-10 min-h-screen">
        {{-- Mobile overlay --}}
        <div x-show="sidebarOpen" x-cloak x-transition.opacity @click="sidebarOpen = false"
             class="fixed inset-0 z-40 bg-slate-950/40 backdrop-blur-md lg:hidden"></div>

        {{-- Sidebar --}}
        <aside class="fixed inset-y-0 left-0 z-50 flex w-72 max-w-[86vw] -translate-x-full flex-col overflow-hidden border-r border-white/10 bg-slate-950/85 shadow-[0_30px_80px_-30px_rgba(2,6,23,0.85)] backdrop-blur-2xl transition-transform duration-300 ease-out lg:inset-y-3 lg:left-3 lg:w-64 lg:translate-x-0 lg:rounded-[30px] lg:border lg:border-white/10"
               :style="sidebarOpen ? 'transform: translateX(0)' : ''">
            <div class="pointer-events-none absolute -top-24 left-1/2 h-56 w-56 -translate-x-1/2 rounded-full bg-indigo-500/35 blur-3xl"></div>

            <div class="relative flex h-16 shrink-0 items-center justify-between px-5">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                    <x-application-logo class="h-9 w-9" />
                    <div>
                        <p class="text-base font-extrabold tracking-tight text-white">Kasirin<span class="text-indigo-400">.</span></p>
                        <p class="-mt-0.5 text-[10px] font-medium uppercase tracking-[0.18em] text-slate-500">Finance & Inventory</p>
                    </div>
                </a>
                <button @click="sidebarOpen = false" class="rounded-full p-1.5 text-slate-400 transition hover:bg-white/10 hover:text-white lg:hidden">
                    <x-icon name="x" class="h-5 w-5" />
                </button>
            </div>

            {{-- Workspace card --}}
            <div class="relative mx-4 mt-2 rounded-2xl border border-white/10 bg-white/[0.06] p-3 shadow-[inset_0_1px_0_rgba(255,255,255,0.08)] backdrop-blur">
                <div class="flex items-center gap-3">
                    @if ($currentTenant?->logo)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($currentTenant->logo) }}"
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

            <nav class="relative mt-6 flex-1 space-y-6 overflow-y-auto px-3 pb-6">
                <div>
                    <p class="px-3 pb-2 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">Ringkasan</p>
                    <div class="space-y-1">
                        <x-sidebar-link :href="route('dashboard')" icon="home" :active="request()->routeIs('dashboard')">Dashboard</x-sidebar-link>
                    </div>
                </div>

                @if ($user->canOperateCashier())
                    <div>
                        <p class="px-3 pb-2 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">Penjualan</p>
                        <div class="space-y-1">
                            <x-sidebar-link :href="route('pos.index')" icon="cart" :active="request()->routeIs('pos.*')">Kasir / POS</x-sidebar-link>
                            <x-sidebar-link :href="route('transactions.index')" icon="receipt" :active="request()->routeIs('transactions.*')">Transaksi</x-sidebar-link>
                            <x-sidebar-link :href="route('customers.index')" icon="users" :active="request()->routeIs('customers.*')">Pelanggan</x-sidebar-link>
                        </div>
                    </div>
                @endif

                @if ($user->canManageInventory())
                    <div>
                        <p class="px-3 pb-2 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">Inventaris</p>
                        <div class="space-y-1">
                            <x-sidebar-link :href="route('products.index')" icon="cube" :active="request()->routeIs('products.*')">Produk</x-sidebar-link>
                            <x-sidebar-link :href="route('categories.index')" icon="tag" :active="request()->routeIs('categories.*')">Kategori</x-sidebar-link>
                            <x-sidebar-link :href="route('stock.index')" icon="archive" :active="request()->routeIs('stock.*')">Pergerakan Stok</x-sidebar-link>
                        </div>
                    </div>
                @endif

                @if ($user->isOwner())
                    <div>
                        <p class="px-3 pb-2 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">Bisnis</p>
                        <div class="space-y-1">
                            <x-sidebar-link :href="route('finance.index')" icon="wallet" :active="request()->routeIs('finance.*')">Kas & Keuangan</x-sidebar-link>
                            <x-sidebar-link :href="route('reports.index')" icon="chart" :active="request()->routeIs('reports.*')">Laporan</x-sidebar-link>
                            <x-sidebar-link :href="route('team.index')" icon="shield" :active="request()->routeIs('team.*')">Tim & Akses</x-sidebar-link>
                            <x-sidebar-link :href="route('settings.edit')" icon="settings" :active="request()->routeIs('settings.*')">Pengaturan</x-sidebar-link>
                        </div>
                    </div>
                @endif
            </nav>

            <div class="relative border-t border-white/10 p-3">
                <div class="flex items-center gap-3 rounded-2xl px-2 py-2">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-800 text-xs font-bold text-slate-200 ring-1 ring-white/15">
                        {{ $user->initials }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-white">{{ $user->name }}</p>
                        <p class="truncate text-[11px] text-slate-500">{{ $user->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Keluar" class="rounded-full p-2 text-slate-500 transition hover:bg-white/10 hover:text-rose-300">
                            <x-icon name="logout" class="h-4 w-4" />
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Main --}}
        <div class="lg:pl-[17.5rem]">
            <header class="sticky top-0 z-30 px-3 pt-3 sm:px-4 lg:px-6 lg:pt-4">
                <div class="flex h-16 items-center justify-between gap-3 rounded-[22px] border border-white/70 bg-white/60 px-3 shadow-[0_16px_40px_-24px_rgba(15,23,42,0.45),inset_0_1px_0_rgba(255,255,255,0.9)] backdrop-blur-2xl sm:px-4">
                    <div class="flex min-w-0 items-center gap-3">
                        <button @click="sidebarOpen = true" class="rounded-full p-2.5 text-slate-600 transition hover:bg-white/70 lg:hidden">
                            <x-icon name="menu" class="h-5 w-5" />
                        </button>
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5 text-[11px] font-medium text-slate-400">
                                <span>Kasirin</span>
                                <x-icon name="chevron-right" class="h-3 w-3" />
                                <span class="truncate text-slate-500">{{ $pageTitle }}</span>
                            </div>
                            <p class="truncate text-sm font-semibold text-slate-900">{{ $currentTenant->business_name ?? 'Bisnis Anda' }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5 sm:gap-2">
                        <a href="{{ route('pos.index') }}" class="btn-primary btn-sm hidden sm:inline-flex">
                            <x-icon name="plus" class="h-4 w-4" />
                            Transaksi Baru
                        </a>

                        <a href="{{ route('notifications.index') }}"
                           class="relative rounded-full p-2.5 text-slate-600 transition hover:bg-white/70 hover:text-slate-800">
                            <x-icon name="bell" class="h-5 w-5" />
                            @if ($unreadNotificationCount > 0)
                                <span class="absolute right-1 top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 text-[9px] font-bold text-white ring-2 ring-white/80">
                                    {{ $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount }}
                                </span>
                            @endif
                        </a>

                        <div class="relative" @click.outside="profileOpen = false">
                            <button @click="profileOpen = !profileOpen"
                                    class="flex items-center gap-2 rounded-full border border-white/70 bg-white/50 p-1 pr-2.5 shadow-sm backdrop-blur transition hover:bg-white/80">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-[11px] font-bold text-white shadow-md shadow-indigo-600/30">
                                    {{ $user->initials }}
                                </span>
                                <span class="hidden text-left sm:block">
                                    <span class="block max-w-[130px] truncate text-xs font-semibold text-slate-800">{{ $user->name }}</span>
                                    <span class="block text-[10px] text-slate-400">{{ $user->role_label }}</span>
                                </span>
                                <x-icon name="chevron-down" class="hidden h-3.5 w-3.5 text-slate-400 sm:block" />
                            </button>

                            <div x-show="profileOpen" x-cloak x-transition.origin.top.right
                                 class="absolute right-0 mt-2 w-60 overflow-hidden rounded-[24px] border border-white/70 bg-white/85 py-1.5 shadow-[0_24px_60px_-20px_rgba(15,23,42,0.4)] backdrop-blur-2xl">
                                <div class="border-b border-white/60 px-4 py-2.5">
                                    <p class="truncate text-sm font-semibold text-slate-800">{{ $user->name }}</p>
                                    <p class="truncate text-xs text-slate-500">{{ $user->email }}</p>
                                </div>
                                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-600 transition hover:bg-white/70">
                                    <x-icon name="user" class="h-4 w-4 text-slate-400" /> Profil Saya
                                </a>
                                @if ($user->isOwner())
                                    <a href="{{ route('settings.edit') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-600 transition hover:bg-white/70">
                                        <x-icon name="settings" class="h-4 w-4 text-slate-400" /> Pengaturan Bisnis
                                    </a>
                                @endif
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex w-full items-center gap-2.5 px-4 py-2.5 text-sm text-rose-600 transition hover:bg-rose-50/80">
                                        <x-icon name="logout" class="h-4 w-4" /> Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="mx-auto max-w-[1600px] px-4 pb-32 pt-5 sm:px-6 lg:px-8 lg:pb-10 lg:pt-6">
                @if (session('success'))
                    <x-alert type="success">{{ session('success') }}</x-alert>
                @endif
                @if (session('error'))
                    <x-alert type="error">{{ session('error') }}</x-alert>
                @endif
                @if ($errors->any())
                    <x-alert type="error">
                        <span class="font-semibold">Periksa kembali data Anda.</span>
                        <ul class="mt-1 list-inside list-disc text-xs font-normal">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </x-alert>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

    {{-- Mobile bottom navigation — floating liquid glass tab bar --}}
    @unless (request()->routeIs('pos.*'))
        <nav class="fixed inset-x-0 bottom-0 z-40 px-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))] pt-2 lg:hidden">
            <div class="mx-auto flex max-w-md items-stretch justify-around gap-1 rounded-[26px] border border-white/70 bg-white/65 p-1.5 shadow-[0_20px_50px_-16px_rgba(15,23,42,0.45),inset_0_1px_0_rgba(255,255,255,0.95)] backdrop-blur-2xl">
                <a href="{{ route('dashboard') }}" class="{{ $tabBase }} {{ request()->routeIs('dashboard') ? $tabOn : $tabOff }}">
                    <x-icon name="home" class="h-5 w-5" />
                    <span>Dashboard</span>
                </a>

                @if ($user->canOperateCashier())
                    <a href="{{ route('pos.index') }}" class="{{ $tabBase }} {{ request()->routeIs('pos.*') ? $tabOn : $tabOff }}">
                        <x-icon name="cart" class="h-5 w-5" />
                        <span>Kasir</span>
                    </a>
                    <a href="{{ route('transactions.index') }}" class="{{ $tabBase }} {{ request()->routeIs('transactions.*') ? $tabOn : $tabOff }}">
                        <x-icon name="receipt" class="h-5 w-5" />
                        <span>Transaksi</span>
                    </a>
                @endif

                @if ($user->canManageInventory())
                    <a href="{{ route('products.index') }}" class="{{ $tabBase }} {{ request()->routeIs('products.*', 'categories.*', 'stock.*') ? $tabOn : $tabOff }}">
                        <x-icon name="cube" class="h-5 w-5" />
                        <span>Produk</span>
                    </a>
                @endif

                <button type="button" @click="sidebarOpen = true" class="{{ $tabBase }} {{ $tabOff }}">
                    <x-icon name="menu" class="h-5 w-5" />
                    <span>Menu</span>
                </button>
            </div>
        </nav>
    @endunless

    @stack('scripts')
</body>
</html>
