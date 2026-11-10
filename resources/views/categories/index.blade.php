<x-app-layout>
    <x-page-header title="Kategori" subtitle="Kelompokkan produk agar pencarian dan laporan lebih rapi.">
        <x-slot name="actions">
            <a href="{{ route('products.index') }}" class="btn-secondary">
                <x-icon name="cube" class="h-4 w-4" /> Produk
            </a>
        </x-slot>
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-1">
            <div data-reveal class="card p-5 sm:p-6 lg:sticky lg:top-24">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                    <x-icon name="tag" class="h-5 w-5" />
                </div>
                <h2 class="mt-4 text-base font-bold text-slate-900">Tambah kategori</h2>
                <p class="mt-1 text-xs text-slate-500">Buat kelompok baru untuk produk Anda.</p>

                <form method="POST" action="{{ route('categories.store') }}" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <label for="name" class="label">Nama kategori <span class="text-rose-500">*</span></label>
                        <input id="name" name="name" type="text" required value="{{ old('name') }}" placeholder="Contoh: Minuman" class="input">
                        <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
                    </div>
                    <div>
                        <label for="description" class="label">Deskripsi</label>
                        <textarea id="description" name="description" rows="3" placeholder="Keterangan singkat (opsional)" class="input">{{ old('description') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-1.5" />
                    </div>
                    <button type="submit" class="btn-primary w-full">
                        <x-icon name="plus" class="h-4 w-4" /> Simpan Kategori
                    </button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="space-y-3">
                @forelse ($categories as $category)
                    <div data-reveal class="card p-5" x-data="{ editing: false }">
                        <div x-show="!editing" class="flex items-start gap-4">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500">
                                <x-icon name="tag" class="h-5 w-5" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-sm font-bold text-slate-900">{{ $category->name }}</h3>
                                    <x-badge tone="info">{{ $category->products_count }} produk</x-badge>
                                </div>
                                <p class="mt-1 text-xs text-slate-500">{{ $category->description ?: 'Tidak ada deskripsi.' }}</p>
                            </div>
                            <div class="flex shrink-0 items-center gap-1">
                                <button type="button" @click="editing = true" title="Edit"
                                        class="rounded-lg p-2 text-slate-400 transition hover:bg-indigo-50 hover:text-indigo-600">
                                    <x-icon name="edit" class="h-4 w-4" />
                                </button>
                                <form method="POST" action="{{ route('categories.destroy', $category) }}"
                                      onsubmit="return confirm('Hapus kategori {{ addslashes($category->name) }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Hapus"
                                            class="rounded-lg p-2 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600">
                                        <x-icon name="trash" class="h-4 w-4" />
                                    </button>
                                </form>
                            </div>
                        </div>

                        <form x-show="editing" x-cloak method="POST" action="{{ route('categories.update', $category) }}" class="space-y-3">
                            @csrf
                            @method('PUT')
                            <div>
                                <label class="label">Nama kategori</label>
                                <input name="name" type="text" required value="{{ $category->name }}" class="input">
                            </div>
                            <div>
                                <label class="label">Deskripsi</label>
                                <textarea name="description" rows="2" class="input">{{ $category->description }}</textarea>
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="btn-primary btn-sm">Simpan</button>
                                <button type="button" @click="editing = false" class="btn-secondary btn-sm">Batal</button>
                            </div>
                        </form>
                    </div>
                @empty
                    <div data-reveal class="card">
                        <x-empty-state title="Belum ada kategori" description="Tambahkan kategori pertama melalui formulir di samping." icon="tag" />
                    </div>
                @endforelse
            </div>

            <div class="mt-5 rounded-2xl border border-sky-100 bg-sky-50 p-4">
                <div class="flex gap-3">
                    <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0 text-sky-600" />
                    <p class="text-xs leading-relaxed text-sky-800">Kategori yang masih memiliki produk tidak dapat dihapus. Pindahkan produk ke kategori lain terlebih dahulu.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
