<section>
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-5">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="label">Nama lengkap <span class="text-rose-500">*</span></label>
            <input id="name" name="name" type="text" class="input" value="{{ old('name', $user->name) }}"
                   required autofocus autocomplete="name">
            <x-input-error class="mt-1.5" :messages="$errors->get('name')" />
        </div>

        <div>
            <label for="email" class="label">Email <span class="text-rose-500">*</span></label>
            <input id="email" name="email" type="email" class="input" value="{{ old('email', $user->email) }}"
                   required autocomplete="username">
            <x-input-error class="mt-1.5" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2 rounded-xl border border-amber-200 bg-amber-50 p-3">
                    <p class="text-xs text-amber-800">
                        Email Anda belum diverifikasi.
                        <button form="send-verification" class="font-semibold underline hover:text-amber-900">
                            Kirim ulang email verifikasi.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-1 text-xs font-semibold text-emerald-600">
                            Tautan verifikasi baru telah dikirim ke email Anda.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary"><x-icon name="check" class="h-4 w-4" /> Simpan Profil</button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2500)"
                   class="text-sm font-medium text-emerald-600">Tersimpan.</p>
            @endif
        </div>
    </form>
</section>
