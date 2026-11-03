<section>
    <form method="post" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="label">Password saat ini <span class="text-rose-500">*</span></label>
            <input id="update_password_current_password" name="current_password" type="password" class="input" autocomplete="current-password">
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1.5" />
        </div>

        <div>
            <label for="update_password_password" class="label">Password baru <span class="text-rose-500">*</span></label>
            <input id="update_password_password" name="password" type="password" class="input" autocomplete="new-password">
            <p class="mt-1 text-[11px] text-slate-400">Minimal 8 karakter.</p>
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1.5" />
        </div>

        <div>
            <label for="update_password_password_confirmation" class="label">Konfirmasi password baru <span class="text-rose-500">*</span></label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="input" autocomplete="new-password">
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1.5" />
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary"><x-icon name="shield" class="h-4 w-4" /> Perbarui Password</button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2500)"
                   class="text-sm font-medium text-emerald-600">Password diperbarui.</p>
            @endif
        </div>
    </form>
</section>
