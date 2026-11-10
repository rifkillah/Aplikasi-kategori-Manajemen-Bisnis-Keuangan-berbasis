<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kasirin — Kas, Invoice & Inventaris untuk UMKM</title>
    <meta name="description" content="Platform kasir, invoicing, dan manajemen gudang untuk UMKM. Kelola penjualan, stok, dan laporan keuangan dalam satu dashboard.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div class="relative overflow-hidden bg-slate-950">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -left-40 -top-40 h-[520px] w-[520px] rounded-full bg-indigo-600/25 blur-[120px]"></div>
            <div class="absolute -right-32 top-24 h-[420px] w-[420px] rounded-full bg-violet-600/20 blur-[110px]"></div>
            <div class="absolute inset-0 opacity-[0.05]" style="background-image: linear-gradient(#fff 1px, transparent 1px), linear-gradient(90deg, #fff 1px, transparent 1px); background-size: 56px 56px;"></div>
        </div>

        {{-- Nav --}}
        <header class="relative z-20">
            <nav class="mx-auto flex h-20 max-w-7xl items-center justify-between px-5 sm:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                    <x-application-logo class="h-10 w-10" />
                    <div>
                        <p class="text-lg font-extrabold tracking-tight text-white">Kasirin<span class="text-indigo-400">.</span></p>
                        <p class="-mt-1 text-[9px] font-semibold uppercase tracking-[0.2em] text-slate-500">Finance & Inventory</p>
                    </div>
                </a>

                <div class="hidden items-center gap-8 text-sm font-medium text-slate-400 md:flex">
                    <a href="#fitur" class="transition hover:text-white">Fitur</a>
                    <a href="#cara-kerja" class="transition hover:text-white">Cara Kerja</a>
                    <a href="#harga" class="transition hover:text-white">Harga</a>
                </div>

                <div class="flex items-center gap-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn-primary">Buka Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="hidden rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white sm:inline-flex">Masuk</a>
                        <a href="{{ route('register') }}" class="btn-primary">Coba Gratis <x-icon name="arrow-right" class="h-4 w-4" /></a>
                    @endauth
                </div>
            </nav>
        </header>

        {{-- Hero --}}
        <section class="relative z-10 mx-auto grid max-w-7xl gap-14 px-5 pb-24 pt-14 sm:px-8 lg:grid-cols-[1.05fr_1fr] lg:items-center lg:pb-32 lg:pt-20">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3.5 py-1.5 text-xs font-semibold text-indigo-200">
                    <span class="flex h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                    Dipercaya UMKM untuk operasional harian
                </div>

                <h1 class="mt-6 text-4xl font-extrabold leading-[1.1] tracking-tight text-white sm:text-5xl lg:text-[3.4rem]">
                    Kelola <span class="animate-gradient bg-gradient-to-r from-indigo-300 via-violet-300 to-fuchsia-300 bg-clip-text text-transparent">kas, stok, dan invoice</span> dalam satu tempat.
                </h1>

                <p class="mt-5 max-w-xl text-base leading-relaxed text-slate-400">
                    Kasirin membantu pemilik bisnis mencatat penjualan, mengontrol gudang, menerbitkan invoice PDF, dan membaca laba rugi — tanpa spreadsheet yang berantakan.
                </p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('register') }}" class="btn-primary px-6 py-3.5 text-base">
                        Mulai Sekarang — Gratis
                        <x-icon name="arrow-right" class="h-4 w-4" />
                    </a>
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 rounded-full border border-white/15 bg-white/5 px-6 py-3.5 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/10">
                        <x-icon name="eye" class="h-4 w-4" /> Lihat Akun Demo
                    </a>
                </div>

                <div class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-3 text-xs font-medium text-slate-400">
                    <span class="inline-flex items-center gap-1.5"><x-icon name="check" class="h-4 w-4 text-emerald-400" /> Tanpa kartu kredit</span>
                    <span class="inline-flex items-center gap-1.5"><x-icon name="check" class="h-4 w-4 text-emerald-400" /> Siap pakai 5 menit</span>
                    <span class="inline-flex items-center gap-1.5"><x-icon name="check" class="h-4 w-4 text-emerald-400" /> Data multi-tenant aman</span>
                </div>
            </div>

            {{-- Product mockup --}}
            <div class="animate-float-slow relative lg:pl-6">
                <div class="absolute -inset-4 rounded-[2rem] bg-gradient-to-br from-indigo-500/25 to-violet-500/10 blur-2xl"></div>
                <div class="relative overflow-hidden rounded-[1.5rem] border border-white/10 bg-slate-900 shadow-2xl shadow-indigo-950/50">
                    <div class="flex items-center gap-1.5 border-b border-white/5 bg-slate-950/60 px-4 py-3">
                        <span class="h-2.5 w-2.5 rounded-full bg-rose-400/70"></span>
                        <span class="h-2.5 w-2.5 rounded-full bg-amber-400/70"></span>
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-400/70"></span>
                        <span class="ml-3 rounded-md bg-white/5 px-2 py-1 text-[9px] text-slate-500">app.kasirin.id/dashboard</span>
                    </div>

                    <div class="flex">
                        <div class="hidden w-36 shrink-0 border-r border-white/5 bg-slate-950/70 p-3 sm:block">
                            <div class="mb-4 flex items-center gap-2">
                                <div class="h-6 w-6 rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600"></div>
                                <span class="text-xs font-bold text-white">Kasirin.</span>
                            </div>
                            @foreach ([['Dashboard', true], ['Kasir', false], ['Produk', false], ['Laporan', false], ['Pengaturan', false]] as [$label, $active])
                                <div class="mb-0.5 flex items-center gap-2 rounded-lg px-2 py-1.5 text-[10px] {{ $active ? 'bg-white/10 font-semibold text-white' : 'text-slate-500' }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $active ? 'bg-indigo-400' : 'bg-slate-700' }}"></span>{{ $label }}
                                </div>
                            @endforeach
                        </div>

                        <div class="flex-1 p-4 sm:p-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-[10px] text-slate-500">Selamat pagi, Aditya 👋</p>
                                    <p class="text-sm font-bold text-white">Ringkasan Bisnis</p>
                                </div>
                                <span class="rounded-lg bg-indigo-500/15 px-2 py-1 text-[9px] font-semibold text-indigo-300">November</span>
                            </div>

                            <div class="mt-4 grid grid-cols-2 gap-2.5">
                                <div class="rounded-xl border border-white/5 bg-white/[0.03] p-3">
                                    <p class="text-[9px] text-slate-500">Penjualan bulan ini</p>
                                    <p class="mt-1 text-sm font-bold text-white">Rp 48,2 jt</p>
                                    <p class="mt-1 text-[8px] font-medium text-emerald-400">↑ 12,4% vs bulan lalu</p>
                                </div>
                                <div class="rounded-xl border border-white/5 bg-white/[0.03] p-3">
                                    <p class="text-[9px] text-slate-500">Laba bersih</p>
                                    <p class="mt-1 text-sm font-bold text-white">Rp 16,7 jt</p>
                                    <p class="mt-1 text-[8px] font-medium text-emerald-400">Margin 34,6%</p>
                                </div>
                            </div>

                            <div class="mt-3 rounded-xl border border-white/5 bg-white/[0.03] p-3">
                                <div class="flex items-center justify-between">
                                    <p class="text-[9px] text-slate-400">Pemasukan vs pengeluaran</p>
                                    <p class="text-[8px] text-slate-600">6 bulan</p>
                                </div>
                                <div class="mt-3 flex h-20 items-end gap-2">
                                    @foreach ([42, 55, 48, 70, 64, 88] as $index => $height)
                                        <div class="flex h-full flex-1 items-end gap-0.5">
                                            <div class="flex-1 rounded-t bg-indigo-500/80" style="height: {{ $height }}%"></div>
                                            <div class="flex-1 rounded-t bg-violet-500/30" style="height: {{ $height * 0.55 }}%"></div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-3 rounded-xl border border-white/5 bg-white/[0.03] p-3">
                                <div class="flex items-center justify-between text-[9px] text-slate-400">
                                    <span class="font-semibold text-white">Stok perlu perhatian</span>
                                    <span class="text-amber-400">3 produk</span>
                                </div>
                                <div class="mt-2 space-y-1.5">
                                    <div class="flex items-center justify-between text-[9px]">
                                        <span class="text-slate-400">Biji Arabika Gayo 250gr</span>
                                        <span class="rounded bg-rose-500/15 px-1.5 py-0.5 font-semibold text-rose-300">3 tersisa</span>
                                    </div>
                                    <div class="flex items-center justify-between text-[9px]">
                                        <span class="text-slate-400">Cinnamon Roll</span>
                                        <span class="rounded bg-amber-500/15 px-1.5 py-0.5 font-semibold text-amber-300">4 tersisa</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Stats --}}
        <section class="relative z-10 border-t border-white/5 bg-slate-950/50">
            <div class="mx-auto grid max-w-7xl grid-cols-2 gap-6 px-5 py-8 sm:px-8 md:grid-cols-4">
                @foreach ([['1.200+', 'UMKM aktif'], ['48 rb+', 'Transaksi tercatat'], ['99,9%', 'Uptime sistem'], ['4,9/5', 'Rating pengguna']] as [$value, $label])
                    <div class="text-center" data-reveal>
                        <p class="text-2xl font-extrabold text-white">{{ $value }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $label }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    {{-- Features --}}
    <section id="fitur" class="py-24">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-600">Fitur utama</p>
                <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">Semua yang bisnis Anda butuhkan, tanpa alat terpisah.</h2>
                <p class="mt-4 text-sm leading-relaxed text-slate-500">Dari transaksi di meja kasir sampai laporan laba rugi di akhir bulan — semuanya saling terhubung.</p>
            </div>

            <div class="mt-14 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @php
                    $features = [
                        ['cart', 'Kasir cepat & barcode', 'Cari produk lewat scan barcode atau SKU, susun keranjang, dan selesaikan transaksi dalam hitungan detik.', 'bg-indigo-50 text-indigo-600'],
                        ['receipt', 'Invoice PDF otomatis', 'Setiap penjualan menghasilkan invoice profesional yang siap diunduh dan dikirim ke pelanggan.', 'bg-violet-50 text-violet-600'],
                        ['archive', 'Inventaris real-time', 'Stok berkurang otomatis setelah penjualan. Catat barang masuk, keluar, dan opname dengan riwayat lengkap.', 'bg-amber-50 text-amber-600'],
                        ['bell', 'Notifikasi stok menipis', 'Sistem memantau batas minimum tiap produk dan mengirim notifikasi sebelum stok benar-benar habis.', 'bg-rose-50 text-rose-600'],
                        ['chart', 'Laporan laba rugi', 'Lihat omzet, HPP, beban, laba kotor, dan produk terlaris dalam grafik yang mudah dipahami.', 'bg-emerald-50 text-emerald-600'],
                        ['shield', 'Akses berbasis peran', 'Owner, kasir, dan staf gudang punya hak akses berbeda. Data antar bisnis terisolasi dengan global scope.', 'bg-sky-50 text-sky-600'],
                    ];
                @endphp

                @foreach ($features as [$icon, $title, $description, $tone])
                    <div class="group hover-lift rounded-[26px] border border-white/70 bg-white/55 p-6 shadow-[0_18px_50px_-28px_rgba(15,23,42,0.4),inset_0_1px_0_rgba(255,255,255,0.9)] backdrop-blur-2xl transition duration-300 hover:-translate-y-1 hover:border-indigo-200 hover:bg-white/75 hover:shadow-[0_26px_60px_-26px_rgba(79,70,229,0.35)]" data-reveal>
                        <div class="icon-pop flex h-11 w-11 items-center justify-center rounded-2xl ring-1 ring-inset ring-white/50 {{ $tone }}">
                            <x-icon :name="$icon" class="h-5 w-5" />
                        </div>
                        <h3 class="mt-5 text-base font-bold text-slate-900">{{ $title }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-500">{{ $description }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- How it works --}}
    <section id="cara-kerja" class="py-24">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div class="grid gap-12 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-600">Cara kerja</p>
                    <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900">Dari daftar sampai transaksi pertama, kurang dari 5 menit.</h2>
                    <p class="mt-4 text-sm leading-relaxed text-slate-500">Tidak perlu instalasi atau pelatihan panjang. Buat workspace, tambahkan produk, dan mulai jualan.</p>

                    <div class="mt-8 space-y-5">
                        @foreach ([
                            ['01', 'Buat workspace bisnis', 'Daftar dengan nama bisnis Anda. Sistem otomatis menyiapkan tenant, kategori dasar, dan akun owner.'],
                            ['02', 'Tambahkan produk & stok', 'Input produk dengan SKU, barcode, harga beli, harga jual, dan batas stok minimum.'],
                            ['03', 'Jualan & pantau laporan', 'Gunakan kasir untuk transaksi harian. Pantau stok dan laba rugi secara real-time.'],
                        ] as [$number, $title, $text])
                            <div class="flex gap-4" data-reveal>
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-xs font-bold text-white shadow-lg shadow-indigo-600/20">{{ $number }}</div>
                                <div>
                                    <h3 class="text-sm font-bold text-slate-900">{{ $title }}</h3>
                                    <p class="mt-1 text-sm leading-relaxed text-slate-500">{{ $text }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="relative">
                    <div class="absolute -inset-5 rounded-[2rem] bg-gradient-to-br from-indigo-100 to-violet-100 blur-xl"></div>
                    <div class="relative rounded-[28px] border border-white/70 bg-white/55 p-5 shadow-[0_24px_60px_-30px_rgba(15,23,42,0.45),inset_0_1px_0_rgba(255,255,255,0.9)] backdrop-blur-2xl">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                            <div class="flex items-center gap-2">
                                <x-application-logo class="h-8 w-8" />
                                <div>
                                    <p class="text-xs font-bold text-slate-900">Kasir / POS</p>
                                    <p class="text-[10px] text-slate-400">Kopi Senja Nusantara</p>
                                </div>
                            </div>
                            <span class="badge-info"><x-icon name="barcode" class="h-3 w-3" /> Scan aktif</span>
                        </div>

                        <div class="mt-4 flex gap-2">
                            <div class="flex flex-1 items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-[11px] text-slate-400">
                                <x-icon name="search" class="h-3.5 w-3.5" /> Cari produk atau scan barcode...
                            </div>
                            <div class="rounded-xl bg-indigo-600 px-3 py-2 text-[11px] font-semibold text-white">Scan</div>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-2">
                            @foreach ([['Kopi Susu Senja', 'Rp 24.000', '86 stok'], ['Croissant Butter', 'Rp 20.000', '18 stok'], ['Matcha Latte', 'Rp 30.000', '27 stok'], ['Teh Lemon Madu', 'Rp 18.000', '6 stok']] as [$name, $price, $stock])
                                <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-3">
                                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                                        <x-icon name="cube" class="h-3.5 w-3.5" />
                                    </div>
                                    <p class="mt-2 truncate text-[11px] font-semibold text-slate-800">{{ $name }}</p>
                                    <div class="mt-1 flex items-center justify-between">
                                        <span class="text-[10px] font-bold text-indigo-600">{{ $price }}</span>
                                        <span class="text-[9px] text-slate-400">{{ $stock }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4 rounded-xl bg-slate-900 p-4 text-white">
                            <p class="text-[10px] font-medium uppercase tracking-wider text-slate-400">Total keranjang</p>
                            <div class="mt-1 flex items-end justify-between">
                                <p class="text-xl font-extrabold">Rp 72.000</p>
                                <span class="rounded-lg bg-emerald-500/15 px-2 py-1 text-[9px] font-semibold text-emerald-300">2 item</span>
                            </div>
                            <div class="mt-3 rounded-lg bg-emerald-500 py-2 text-center text-[11px] font-bold">Bayar & Cetak Invoice</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Pricing --}}
    <section id="harga" class="py-24">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-600">Harga sederhana</p>
                <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900">Mulai gratis, upgrade saat bisnis tumbuh.</h2>
                <p class="mt-4 text-sm text-slate-500">Semua paket sudah termasuk multi-user, laporan, dan invoice PDF.</p>
            </div>

            <div class="mx-auto mt-12 grid max-w-5xl gap-5 md:grid-cols-3">
                @php
                    $plans = [
                        ['Starter', 'Gratis', 'Untuk bisnis yang baru mulai', ['1 outlet', '3 pengguna', '100 transaksi/bulan', 'Inventaris dasar', 'Invoice PDF'], false],
                        ['Growth', 'Rp 149rb', 'Untuk UMKM yang berkembang', ['3 outlet', '10 pengguna', 'Transaksi tanpa batas', 'Notifikasi stok WA & email', 'Laporan laba rugi lengkap', 'Ekspor Excel/CSV'], true],
                        ['Business', 'Rp 399rb', 'Untuk jaringan multi-cabang', ['Outlet tanpa batas', 'Pengguna tanpa batas', 'Prioritas support', 'API & integrasi', 'Audit log lanjutan'], false],
                    ];
                @endphp

                @foreach ($plans as [$name, $price, $description, $features, $popular])
                    <div class="relative rounded-[26px] border p-6 backdrop-blur-2xl {{ $popular ? 'border-white/15 bg-slate-950/90 text-white shadow-[0_30px_70px_-30px_rgba(79,70,229,0.7)]' : 'border-white/70 bg-white/55 shadow-[0_18px_50px_-28px_rgba(15,23,42,0.4),inset_0_1px_0_rgba(255,255,255,0.9)]' }}" data-reveal>
                        @if ($popular)
                            <span class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-indigo-500 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-white">Paling populer</span>
                        @endif
                        <p class="text-sm font-bold {{ $popular ? 'text-indigo-300' : 'text-indigo-600' }}">{{ $name }}</p>
                        <div class="mt-2 flex items-end gap-1">
                            <span class="text-3xl font-extrabold {{ $popular ? 'text-white' : 'text-slate-900' }}">{{ $price }}</span>
                            @if ($price !== 'Gratis')<span class="pb-1 text-xs {{ $popular ? 'text-slate-400' : 'text-slate-500' }}">/bulan</span>@endif
                        </div>
                        <p class="mt-2 text-xs {{ $popular ? 'text-slate-400' : 'text-slate-500' }}">{{ $description }}</p>

                        <ul class="mt-6 space-y-2.5">
                            @foreach ($features as $feature)
                                <li class="flex items-start gap-2 text-xs {{ $popular ? 'text-slate-300' : 'text-slate-600' }}">
                                    <x-icon name="check-simple" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-emerald-500" />
                                    {{ $feature }}
                                </li>
                            @endforeach
                        </ul>

                        <a href="{{ route('register') }}" class="mt-6 flex w-full items-center justify-center gap-2 rounded-full py-2.5 text-xs font-bold transition {{ $popular ? 'bg-white text-slate-900 hover:bg-slate-100' : 'border border-white/70 bg-white/60 text-slate-700 hover:bg-white/85' }}">
                            Mulai sekarang <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="py-20">
        <div class="mx-auto max-w-5xl px-5 sm:px-8">
            <div class="relative overflow-hidden rounded-[36px] border border-white/10 bg-slate-950/90 px-6 py-14 text-center shadow-[0_40px_100px_-40px_rgba(2,6,23,0.9)] backdrop-blur-2xl sm:px-12" data-reveal>
                <div class="pointer-events-none absolute inset-0">
                    <div class="absolute -left-20 -top-20 h-72 w-72 rounded-full bg-indigo-600/30 blur-3xl"></div>
                    <div class="absolute -bottom-20 -right-20 h-72 w-72 rounded-full bg-violet-600/25 blur-3xl"></div>
                </div>
                <div class="relative">
                    <h2 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">Siap merapikan keuangan bisnis Anda?</h2>
                    <p class="mx-auto mt-3 max-w-xl text-sm text-slate-400">Bergabung dengan UMKM yang sudah meninggalkan pencatatan manual. Gratis untuk memulai.</p>
                    <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                        <a href="{{ route('register') }}" class="btn-primary px-6 py-3">Buat Workspace Gratis <x-icon name="arrow-right" class="h-4 w-4" /></a>
                        <a href="mailto:halo@kasirin.id" class="inline-flex items-center justify-center gap-2 rounded-full border border-white/15 bg-white/5 px-6 py-3 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/10">
                            <x-icon name="mail" class="h-4 w-4" /> Hubungi Sales
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="border-t border-white/50 bg-white/40 backdrop-blur-xl">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-5 py-8 sm:flex-row sm:px-8">
            <div class="flex items-center gap-2.5">
                <x-application-logo class="h-8 w-8" />
                <span class="text-sm font-extrabold tracking-tight text-slate-900">Kasirin<span class="text-indigo-500">.</span></span>
            </div>
            <p class="text-xs text-slate-400">© {{ date('Y') }} Kasirin. Dibangun dengan Laravel, Tailwind CSS, dan Chart.js.</p>
            <div class="flex gap-5 text-xs font-medium text-slate-500">
                <a href="{{ route('login') }}" class="hover:text-slate-900">Masuk</a>
                <a href="{{ route('register') }}" class="hover:text-slate-900">Daftar</a>
            </div>
        </div>
    </footer>
</body>
</html>
