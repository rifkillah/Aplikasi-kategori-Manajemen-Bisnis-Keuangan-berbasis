<x-guest-layout>
    <div class="mb-8">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-600">Selamat datang kembali</p>
        <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900">Masuk ke workspace</h1>
        <p class="mt-2 text-sm text-slate-500">Kelola penjualan, stok, dan keuangan bisnis Anda dari satu dashboard.</p>
    </div>

    <x-auth-session-status class="mb-5" :status="session('status')" />

    @if (session('success'))
        <div class="mb-5 rounded-2xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm font-medium text-emerald-800 backdrop-blur-xl">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-6 rounded-[24px] border border-white/70 bg-white/45 p-4 shadow-[inset_0_1px_0_rgba(255,255,255,0.8)] backdrop-blur-xl">
        <p class="text-xs font-bold uppercase tracking-wider text-indigo-700">Akun demo siap pakai</p>
        <div class="mt-3 space-y-1.5 text-[11px]">
            <div class="flex items-center justify-between gap-3 rounded-xl border border-white/70 bg-white/60 px-3 py-2 backdrop-blur">
                <p class="font-semibold text-slate-800">Owner</p>
                <p class="truncate text-slate-500">owner@demo.com</p>
            </div>
            <div class="flex items-center justify-between gap-3 rounded-xl border border-white/70 bg-white/60 px-3 py-2 backdrop-blur">
                <p class="font-semibold text-slate-800">Kasir</p>
                <p class="truncate text-slate-500">kasir@demo.com</p>
            </div>
            <div class="flex items-center justify-between gap-3 rounded-xl border border-white/70 bg-white/60 px-3 py-2 backdrop-blur">
                <p class="font-semibold text-slate-800">Gudang</p>
                <p class="truncate text-slate-500">gudang@demo.com</p>
            </div>
        </div>
        <p class="mt-2 text-[11px] text-indigo-600">Password semua akun: <span class="font-semibold">password</span></p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="label">Alamat email</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                    <x-icon name="mail" class="h-4.5 w-4.5" />
                </span>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                       autocomplete="username" placeholder="nama@bisnis.com" class="input py-3 pl-10">
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <div>
            <div class="flex items-center justify-between">
                <label for="password" class="label">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="mb-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-700">Lupa password?</a>
                @endif
            </div>
            <div class="relative" x-data="{ show: false }">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                    <x-icon name="shield" class="h-4.5 w-4.5" />
                </span>
                <input id="password" :type="show ? 'text' : 'password'" name="password" required
                       autocomplete="current-password" placeholder="••••••••" class="input py-3 pl-10 pr-11">
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600">
                    <x-icon name="eye" class="h-4.5 w-4.5" />
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <label for="remember_me" class="flex items-center gap-2.5 text-sm text-slate-600">
            <input id="remember_me" type="checkbox" name="remember" class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
            Ingat saya di perangkat ini
        </label>

        <button type="submit" class="btn-primary w-full py-3">
            Masuk ke Dashboard
            <x-icon name="arrow-right" class="h-4 w-4" />
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        Belum punya akun?
        <a href="{{ route('register') }}" class="font-semibold text-indigo-600 hover:text-indigo-700">Daftarkan bisnis Anda</a>
    </p>
</x-guest-layout>
