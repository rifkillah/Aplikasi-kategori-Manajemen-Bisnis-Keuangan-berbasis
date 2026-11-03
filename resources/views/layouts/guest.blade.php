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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Kasirin') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-slate-950 lg:grid lg:grid-cols-[1.05fr_1fr]">
        {{-- Brand side --}}
        <div class="relative hidden overflow-hidden lg:flex lg:flex-col lg:justify-between lg:p-12">
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,_rgba(99,102,241,0.35),_transparent_55%),radial-gradient(ellipse_at_bottom_right,_rgba(139,92,246,0.25),_transparent_50%)]"></div>
            <div class="absolute inset-0 opacity-[0.07]" style="background-image: linear-gradient(#fff 1px, transparent 1px), linear-gradient(90deg, #fff 1px, transparent 1px); background-size: 48px 48px;"></div>

            <a href="{{ route('home') }}" class="relative flex items-center gap-3">
                <x-application-logo class="h-11 w-11" />
                <div>
                    <p class="text-xl font-extrabold tracking-tight text-white">Kasirin<span class="text-indigo-400">.</span></p>
                    <p class="-mt-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400">Finance & Inventory</p>
                </div>
            </a>

            <div class="relative max-w-lg">
                <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-xs font-semibold text-indigo-200">
                    <x-icon name="sparkles" class="h-3.5 w-3.5" /> Platform kasir & gudang untuk UMKM
                </span>
                <h1 class="mt-6 text-4xl font-extrabold leading-tight tracking-tight text-white">
                    Satu tempat untuk <span class="bg-gradient-to-r from-indigo-300 to-violet-300 bg-clip-text text-transparent">kas, stok,</span> dan pertumbuhan bisnis.
                </h1>
                <p class="mt-4 text-sm leading-relaxed text-slate-400">
                    Catat penjualan, kelola inventaris, kirim invoice, dan baca laporan keuangan tanpa ribet.
                </p>

                <div class="mt-8 space-y-3">
                    @foreach ($features as $feature)
                        <div class="flex items-start gap-3 rounded-2xl border border-white/10 bg-white/[0.04] p-3.5 backdrop-blur">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-500/15 text-indigo-300">
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

        {{-- Form side --}}
        <div class="flex min-h-screen flex-col bg-slate-50">
            <div class="flex items-center justify-between px-6 py-5 lg:hidden">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                    <x-application-logo class="h-9 w-9" />
                    <span class="text-lg font-extrabold tracking-tight text-slate-900">Kasirin<span class="text-indigo-500">.</span></span>
                </a>
                <a href="{{ route('home') }}" class="text-sm font-semibold text-slate-500">Kembali</a>
            </div>

            <div class="flex flex-1 items-center justify-center px-5 py-8 sm:px-8">
                <div class="w-full max-w-md">
                    {{ $slot }}
                </div>
            </div>

            <div class="px-6 py-5 text-center text-[11px] text-slate-400">
                Data bisnis Anda aman dan terisolasi dengan enkripsi.
            </div>
        </div>
    </div>
</body>
</html>
