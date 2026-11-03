<x-guest-layout>
    <div class="mb-7">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-600">Mulai gratis</p>
        <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900">Buat workspace bisnis</h1>
        <p class="mt-2 text-sm text-slate-500">Daftarkan bisnis Anda dan mulai kelola kas serta stok dalam hitungan menit.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label for="business_name" class="label">Nama bisnis</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                    <x-icon name="building" class="h-4.5 w-4.5" />
                </span>
                <input id="business_name" type="text" name="business_name" value="{{ old('business_name') }}" required autofocus
                       placeholder="Contoh: Kopi Senja Nusantara" class="input py-3 pl-10">
            </div>
            <x-input-error :messages="$errors->get('business_name')" class="mt-1.5" />
        </div>

        <div>
            <label for="name" class="label">Nama Anda</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                    <x-icon name="user" class="h-4.5 w-4.5" />
                </span>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name"
                       placeholder="Nama lengkap" class="input py-3 pl-10">
            </div>
            <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
        </div>

        <div>
            <label for="email" class="label">Email</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                    <x-icon name="mail" class="h-4.5 w-4.5" />
                </span>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                       placeholder="nama@bisnis.com" class="input py-3 pl-10">
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="password" class="label">Password</label>
                <input id="password" type="password" name="password" required autocomplete="new-password"
                       placeholder="Min. 8 karakter" class="input py-3">
                <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
            </div>
            <div>
                <label for="password_confirmation" class="label">Ulangi password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required
                       autocomplete="new-password" placeholder="Ulangi password" class="input py-3">
            </div>
        </div>

        <p class="text-[11px] leading-relaxed text-slate-500">
            Dengan mendaftar, Anda menyetujui ketentuan layanan dan kebijakan privasi Kasirin. Data setiap bisnis dipisahkan secara aman.
        </p>

        <button type="submit" class="btn-primary w-full py-3">
            Buat Akun & Workspace
            <x-icon name="arrow-right" class="h-4 w-4" />
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:text-indigo-700">Masuk di sini</a>
    </p>
</x-guest-layout>
