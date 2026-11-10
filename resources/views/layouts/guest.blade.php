@php
    $features = [
        ['icon' => 'receipt', 'title' => 'Invoice profesional', 'text' => 'Buat invoice PDF otomatis untuk setiap transaksi.'],
        ['icon' => 'archive', 'title' => 'Stok terkontrol', 'text' => 'Pantau barang masuk, keluar, dan stok menipis.'],
        ['icon' => 'chart', 'title' => 'Laporan real-time', 'text' => 'Laba rugi dan produk terlaris dalam satu layar.'],
    ];
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#eef0fb">
    <title>{{ config('app.name', 'Kasirin') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div class="relative min-h-screen overflow-hidden">
        {{-- Ambient liquid background --}}
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 z-0 overflow-hidden">
            <div class="absolute -left-44 -top-56 h-[38rem] w-[38rem] animate-blob rounded-full bg-indigo-400/45 blur-[120px]"></div>
            <div class="absolute -right-40 top-0 h-[30rem] w-[30rem] animate-blob rounded-full bg-fuchsia-400/25 blur-[110px] [animation-delay:2s]"></div>
            <div class="absolute -bottom-56 left-1/4 h-[36rem] w-[36rem] animate-blob rounded-full bg-sky-400/30 blur-[130px] [animation-delay:4s]"></div>
            <div class="absolute bottom-0 right-1/3 h-80 w-80 animate-blob rounded-full bg-violet-400/30 blur-[110px] [animation-delay:6s]"></div>
        </div>

        <div class="relative z-10 grid min-h-screen grid-cols-1 lg:grid-cols-[1.05fr_1fr]">
            {{-- Brand side --}}
            <div class="hidden p-4 lg:block">
                <div class="relative flex h-full flex-col justify-between overflow-hidden rounded-[36px] border border-white/10 bg-slate-950/85 p-12 shadow-[0_40px_100px_-40px_rgba(2,6,23,0.9)] backdrop-blur-2xl">
                    <div class="pointer-events-none absolute inset-0">
                        <div class="absolute -left-24 -top-24 h-80 w-80 rounded-full bg-indigo-600/40 blur-3xl"></div>
                        <div class="absolute -bottom-24 right-0 h-80 w-80 rounded-full bg-violet-600/30 blur-3xl"></div>
                        <div class="absolute inset-0 opacity-[0.06]" style="background-image: linear-gradient(#fff 1px, transparent 1px), linear-gradient(90deg, #fff 1px, transparent 1px); background-size: 48px 48px;"></div>
                    </div>

                    <a href="{{ route('home') }}" class="relative flex items-center gap-3">
                        <x-application-logo class="h-11 w-11" />
                        <div>
                            <p class="text-xl font-extrabold tracking-tight text-white">Kasirin<span class="text-indigo-400">.</span></p>
                            <p class="-mt-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400">Finance & Inventory</p>
                        </div>
                    </a>

                    <div class="relative max-w-lg">
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/[0.07] px-3 py-1.5 text-xs font-semibold text-indigo-200 backdrop-blur">
                            <x-icon name="sparkles" class="h-3.5 w-3.5" /> Platform kasir & gudang untuk UMKM
                        </span>
                        <h1 class="mt-6 text-4xl font-extrabold leading-tight tracking-tight text-white text-balance">
                            Satu tempat untuk <span class="bg-gradient-to-r from-indigo-300 to-violet-300 bg-clip-text text-transparent">kas, stok,</span> dan pertumbuhan bisnis.
                        </h1>
                        <p class="mt-4 text-sm leading-relaxed text-slate-400">
                            Catat penjualan, kelola inventaris, kirim invoice, dan baca laporan keuangan tanpa ribet.
                        </p>

                        <div class="mt-8 space-y-3">
                            @foreach ($features as $feature)
                                <div class="hover-lift flex items-start gap-3 rounded-2xl border border-white/10 bg-white/[0.06] p-3.5 shadow-[inset_0_1px_0_rgba(255,255,255,0.08)] backdrop-blur-xl" data-reveal>
                                    <div class="icon-pop flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-500/20 text-indigo-300 ring-1 ring-inset ring-white/10">
                                        <x-icon :name="$feature['icon']" class="h-4 w-4" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-white">{{ $feature['title'] }}</p>
                                        <p class="text-xs text-slate-400">{{ $feature['text'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <p class="relative text-xs text-slate-500">© {{ date('Y') }} Kasirin. Dibangun dengan Laravel.</p>
                </div>
            </div>

            {{-- Form side --}}
            <div class="flex min-h-screen min-w-0 flex-col">
                <div class="flex items-center justify-between px-4 py-4 lg:hidden">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5 rounded-full border border-white/70 bg-white/60 py-1.5 pl-1.5 pr-4 shadow-sm backdrop-blur-xl">
                        <x-application-logo class="h-8 w-8" />
                        <span class="text-base font-extrabold tracking-tight text-slate-900">Kasirin<span class="text-indigo-500">.</span></span>
                    </a>
                    <a href="{{ route('home') }}" class="rounded-full border border-white/70 bg-white/60 px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm backdrop-blur-xl">Kembali</a>
                </div>

                <div class="flex flex-1 items-center justify-center px-4 py-6 sm:px-8">
                    <div class="animate-pop w-full max-w-md rounded-[32px] border border-white/70 bg-white/60 p-6 shadow-[0_30px_80px_-32px_rgba(15,23,42,0.45),inset_0_1px_0_rgba(255,255,255,0.95)] backdrop-blur-2xl sm:p-8">
                        {{ $slot }}
                    </div>
                </div>

                <div class="px-6 pb-6 pt-2 text-center text-[11px] text-slate-500">
                    Data bisnis Anda aman dan terisolasi dengan enkripsi.
                </div>
            </div>
        </div>
    </div>
</body>
</html>
