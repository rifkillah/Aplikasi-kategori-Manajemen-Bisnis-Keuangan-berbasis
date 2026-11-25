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
        'appearance.edit' => 'Tampilan',
    ];
    $pageTitle = $pageTitles[$routeName] ?? 'Kasirin';
    $user = auth()->user();

    $themeMode = in_array($user->theme_mode, ['system', 'light', 'dark'], true) ? $user->theme_mode : 'system';
    $bgPreset = $user->background_preset === 'custom' && $user->background_image
        ? 'custom'
        : (array_key_exists($user->background_preset ?? '', config('appearance.presets')) ? $user->background_preset : 'aurora');
    $wallpaperUrl = $user->wallpaper_url;
    $glassBlur = is_numeric($user->glass_blur) ? max(0, min(40, (int) $user->glass_blur)) : 24;
    $htmlStyle = '--glass-blur: '.$glassBlur.'px;';
    if ($wallpaperUrl) {
        $htmlStyle .= " --wallpaper-url: url('{$wallpaperUrl}');";
    }

    $tabBase = 'flex min-h-[52px] flex-1 touch-manipulation select-none flex-col items-center justify-center gap-1 rounded-[20px] px-2 py-2 text-[10px] font-semibold transition-all duration-200 active:scale-95';
    $tabOn = 'animate-pop bg-indigo-600 text-white shadow-[0_10px_22px_-10px_rgba(79,70,229,0.95)]';
    $tabOff = 'text-slate-600 hover:bg-white/60 hover:text-slate-800';
@endphp

<!DOCTYPE html>
<html lang="id" data-theme-mode="{{ $themeMode }}" data-bg="{{ $bgPreset }}"
      @if ($wallpaperUrl) data-wallpaper="true" @endif
      style="{{ $htmlStyle }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#eef0fb">
    <title>{{ $pageTitle }} · Kasirin</title>
    @include('partials.theme-boot', ['mode' => $themeMode])
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body x-data="{ sidebarOpen: false, profileOpen: false, sheetStartY: 0 }"
      x-effect="document.body.classList.toggle('overflow-hidden', sidebarOpen)"
      @keydown.escape.window="sidebarOpen = false; profileOpen = false"
      class="font-sans antialiased">
    {{-- Ambient liquid background --}}
    <div aria-hidden="true" class="pointer-events-none fixed inset-0 z-0 overflow-hidden">
        <div class="absolute -left-44 -top-56 h-[38rem] w-[38rem] animate-blob rounded-full bg-indigo-400/40 blur-[120px]"></div>
        <div class="absolute -right-40 top-6 h-[30rem] w-[30rem] animate-blob rounded-full bg-fuchsia-400/25 blur-[110px] [animation-delay:2s]"></div>
        <div class="absolute -bottom-56 left-1/3 h-[36rem] w-[36rem] animate-blob rounded-full bg-sky-400/25 blur-[130px] [animation-delay:4s]"></div>
        <div class="absolute bottom-0 right-1/4 h-80 w-80 animate-blob rounded-full bg-violet-400/30 blur-[110px] [animation-delay:6s]"></div>
    </div>

    <div class="relative z-10 min-h-screen">
        {{-- Desktop sidebar --}}
        <aside class="app-glass fixed inset-y-3 left-3 z-50 hidden w-64 flex-col overflow-hidden rounded-[30px] border border-white/10 bg-slate-950/90 shadow-[0_30px_80px_-30px_rgba(2,6,23,0.85)] lg:flex">
            <div class="pointer-events-none absolute -top-24 left-1/2 h-56 w-56 -translate-x-1/2 rounded-full bg-indigo-500/35 blur-3xl"></div>

            <div class="relative flex h-16 shrink-0 items-center px-5">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                    <x-application-logo class="h-9 w-9" />
                    <div>
                        <p class="text-base font-extrabold tracking-tight text-white">Kasirin<span class="text-indigo-400">.</span></p>
                        <p class="-mt-0.5 text-[10px] font-medium uppercase tracking-[0.18em] text-slate-500">Finance & Inventory</p>
                    </div>
                </a>
            </div>

            @include('layouts.partials.sidebar-nav')
        </aside>

        {{-- Mobile backdrop --}}
        <div x-show="sidebarOpen" x-cloak x-transition.opacity @click="sidebarOpen = false"
             class="fixed inset-0 z-40 bg-slate-950/55 backdrop-blur-xs lg:hidden"></div>

        {{-- Mobile bottom-sheet menu --}}
        <div x-show="sidebarOpen" x-cloak
             x-transition:enter="transition ease-[cubic-bezier(0.22,1,0.36,1)] duration-300"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-y-0"
             x-transition:leave-end="translate-y-full"
             role="dialog" aria-modal="true" aria-label="Menu navigasi"
             class="app-glass fixed inset-x-0 bottom-0 z-50 flex max-h-[88vh] flex-col overflow-hidden overscroll-contain rounded-t-[32px] border-t border-white/15 bg-slate-950/95 shadow-[0_-30px_80px_-30px_rgba(2,6,23,0.9)] lg:hidden">
            <div class="mx-auto mt-2.5 flex h-6 w-16 shrink-0 items-center justify-center touch-none"
                 @touchstart.passive="sheetStartY = $event.touches[0].clientY"
                 @touchend="if ($event.changedTouches[0].clientY - sheetStartY > 70) sidebarOpen = false">
                <span class="h-1.5 w-12 rounded-full bg-white/25"></span>
            </div>

            <div class="flex shrink-0 items-center justify-between px-5 pb-2 pt-3">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">Navigasi</p>
                    <p class="text-base font-extrabold text-white">Menu Kasirin<span class="text-indigo-400">.</span></p>
                </div>
                <button type="button" @click="sidebarOpen = false" aria-label="Tutup menu"
                        class="flex h-11 w-11 touch-manipulation items-center justify-center rounded-full bg-white/[0.06] text-slate-300 ring-1 ring-inset ring-white/10 transition active:scale-90">
                    <x-icon name="x" class="h-5 w-5" />
                </button>
            </div>

            {{-- Quick theme switch — touch friendly --}}
            <div x-data="themeToggle(@js($themeMode))" data-theme-url="{{ route('appearance.theme') }}"
                 class="mx-4 mb-1 flex shrink-0 gap-1 rounded-2xl border border-white/10 bg-white/[0.05] p-1">
                <button type="button" @click="set('system')"
                        :class="mode === 'system' ? 'bg-white/15 text-white shadow-xs' : 'text-slate-400'"
                        class="flex min-h-[42px] flex-1 items-center justify-center gap-1.5 rounded-xl px-2 text-[11px] font-semibold transition active:scale-95">
                    <x-icon name="monitor" class="h-4 w-4" /> Sistem
                </button>
                <button type="button" @click="set('light')"
                        :class="mode === 'light' ? 'bg-white/15 text-white shadow-xs' : 'text-slate-400'"
                        class="flex min-h-[42px] flex-1 items-center justify-center gap-1.5 rounded-xl px-2 text-[11px] font-semibold transition active:scale-95">
                    <x-icon name="sun" class="h-4 w-4" /> Terang
                </button>
                <button type="button" @click="set('dark')"
                        :class="mode === 'dark' ? 'bg-white/15 text-white shadow-xs' : 'text-slate-400'"
                        class="flex min-h-[42px] flex-1 items-center justify-center gap-1.5 rounded-xl px-2 text-[11px] font-semibold transition active:scale-95">
                    <x-icon name="moon" class="h-4 w-4" /> Gelap
                </button>
            </div>

            <div class="flex min-h-0 flex-1 flex-col" @click="if ($event.target.closest('a')) sidebarOpen = false">
                @include('layouts.partials.sidebar-nav')
            </div>
        </div>

        {{-- Main --}}
        <div class="lg:pl-[17.5rem]">
            <header class="sticky top-0 z-30 px-3 pt-3 sm:px-4 lg:px-6 lg:pt-4">
                <div class="liquid-glass liquid-glass-bar page-enter flex h-16 items-center justify-between gap-3 rounded-[22px] px-3 sm:px-4">
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5 text-[11px] font-medium text-slate-500">
                                <span>Kasirin</span>
                                <x-icon name="chevron-right" class="h-3 w-3" />
                                <span class="truncate text-slate-600">{{ $pageTitle }}</span>
                            </div>
                            <p class="truncate text-sm font-semibold text-slate-900">{{ $currentTenant->business_name ?? 'Bisnis Anda' }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-1 sm:gap-2">
                        <a href="{{ route('pos.index') }}" class="btn-primary btn-sm hidden sm:inline-flex">
                            <x-icon name="plus" class="h-4 w-4" />
                            Transaksi Baru
                        </a>

                        <button type="button"
                                x-data="themeToggle(@js($themeMode))"
                                @click="cycle()"
                                :title="'Mode tampilan: ' + label"
                                :aria-label="'Mode tampilan: ' + label"
                                data-theme-url="{{ route('appearance.theme') }}"
                                class="relative hidden h-11 w-11 touch-manipulation items-center justify-center rounded-full text-slate-600 transition hover:bg-white/70 hover:text-slate-800 active:scale-90 sm:inline-flex">
                            <x-icon name="monitor" class="h-5 w-5" x-show="mode === 'system'" x-cloak />
                            <x-icon name="sun" class="h-5 w-5" x-show="mode === 'light'" x-cloak />
                            <x-icon name="moon" class="h-5 w-5" x-show="mode === 'dark'" x-cloak />
                        </button>

                        <a href="{{ route('notifications.index') }}"
                           class="relative flex h-11 w-11 touch-manipulation items-center justify-center rounded-full text-slate-600 transition hover:bg-white/70 hover:text-slate-800 active:scale-90">
                            <x-icon name="bell" class="hover-wiggle h-5 w-5" />
                            @if ($unreadNotificationCount > 0)
                                <span class="animate-pulse-ring absolute right-1 top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 text-[9px] font-bold text-white ring-2 ring-white/80">
                                    {{ $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount }}
                                </span>
                            @endif
                        </a>

                        <div class="relative" @click.outside="profileOpen = false">
                            <button @click="profileOpen = !profileOpen"
                                    class="flex min-h-11 touch-manipulation items-center gap-2 rounded-full border border-white/80 bg-white/65 p-1 pr-2.5 shadow-xs transition hover:bg-white/85 active:scale-[0.97]">
                                <x-user-avatar :user="$user" size="h-8 w-8" text="text-[11px]"
                                               class="shadow-md shadow-indigo-600/30" />
                                <span class="hidden text-left sm:block">
                                    <span class="block max-w-[130px] truncate text-xs font-semibold text-slate-800">{{ $user->name }}</span>
                                    <span class="block text-[10px] text-slate-500">{{ $user->role_label }}</span>
                                </span>
                                <x-icon name="chevron-down" class="hidden h-3.5 w-3.5 text-slate-500 sm:block" />
                            </button>

                            <div x-show="profileOpen" x-cloak x-transition.origin.top.right
                                 class="liquid-glass absolute right-0 mt-2 w-60 overflow-hidden rounded-[24px] py-1.5">
                                <div class="border-b border-white/60 px-4 py-2.5">
                                    <p class="truncate text-sm font-semibold text-slate-800">{{ $user->name }}</p>
                                    <p class="truncate text-xs text-slate-500">{{ $user->email }}</p>
                                </div>
                                <a href="{{ route('profile.edit') }}" class="flex min-h-[44px] touch-manipulation items-center gap-2.5 px-4 py-2.5 text-sm text-slate-600 transition hover:bg-white/70 active:bg-white/80">
                                    <x-icon name="user" class="h-4 w-4 text-slate-400" /> Profil Saya
                                </a>
                                <a href="{{ route('appearance.edit') }}" class="flex min-h-[44px] touch-manipulation items-center gap-2.5 px-4 py-2.5 text-sm text-slate-600 transition hover:bg-white/70 active:bg-white/80">
                                    <x-icon name="palette" class="h-4 w-4 text-slate-400" /> Tampilan & Latar
                                </a>
                                @if ($user->isOwner())
                                    <a href="{{ route('settings.edit') }}" class="flex min-h-[44px] touch-manipulation items-center gap-2.5 px-4 py-2.5 text-sm text-slate-600 transition hover:bg-white/70 active:bg-white/80">
                                        <x-icon name="settings" class="h-4 w-4 text-slate-400" /> Pengaturan Bisnis
                                    </a>
                                @endif
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex min-h-[44px] w-full touch-manipulation items-center gap-2.5 px-4 py-2.5 text-sm text-rose-600 transition hover:bg-rose-50/80 active:bg-rose-50">
                                        <x-icon name="logout" class="h-4 w-4" /> Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="page-enter mx-auto max-w-[1600px] px-4 pb-32 pt-5 sm:px-6 lg:px-8 lg:pb-10 lg:pt-6">
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

        {{-- Mobile bottom navigation — floating liquid glass tab bar --}}
        <nav x-show="!sidebarOpen" x-cloak
             x-transition:enter="transition duration-200" x-transition:enter-start="translate-y-6 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
             class="app-tabbar fixed inset-x-0 bottom-0 z-40 px-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))] pt-2 lg:hidden">
            <div class="liquid-glass liquid-glass-bar mx-auto flex max-w-md items-stretch justify-around gap-1 rounded-[26px] p-2">
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
    </div>

    @stack('scripts')
</body>
</html>
