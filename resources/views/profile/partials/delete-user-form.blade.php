<section x-data="{ confirmDelete: @js($errors->userDeletion->isNotEmpty()) }">
    <p class="text-sm leading-relaxed text-slate-500">
        Setelah akun dihapus, seluruh data akses akun ini akan hilang secara permanen. Pastikan Anda sudah mengunduh data yang diperlukan.
    </p>

    <button type="button" @click="confirmDelete = true" class="btn-danger mt-4">
        <x-icon name="trash" class="h-4 w-4" /> Hapus Akun Saya
    </button>

    <div x-show="confirmDelete" x-cloak class="fixed inset-0 z-50 overflow-y-auto px-4 py-6">
        <div x-show="confirmDelete" x-transition.opacity @click="confirmDelete = false" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm"></div>

        <div x-show="confirmDelete" x-transition class="relative mx-auto w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-rose-50 text-rose-600">
                <x-icon name="alert" class="h-5 w-5" />
            </div>

            <h3 class="mt-4 text-lg font-bold text-slate-900">Yakin ingin menghapus akun?</h3>
            <p class="mt-1 text-sm leading-relaxed text-slate-500">
                Tindakan ini permanen. Masukkan password Anda untuk mengonfirmasi penghapusan akun.
            </p>

            <form method="post" action="{{ route('profile.destroy') }}" class="mt-5 space-y-4">
                @csrf
                @method('delete')

                <div>
                    <label for="delete_password" class="label">Password</label>
                    <input id="delete_password" name="password" type="password" class="input" placeholder="Masukkan password Anda">
                    <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-1.5" />
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" @click="confirmDelete = false" class="btn-secondary">Batal</button>
                    <button type="submit" class="btn-danger">Hapus Akun</button>
                </div>
            </form>
        </div>
    </div>
</section>
