@php
    $wallpaperUrl = $user->background_image
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($user->background_image)
        : null;
@endphp

<x-app-layout>
    <x-page-header title="Tampilan" subtitle="Pilih mode warna dan latar belakang yang paling nyaman untuk Anda.">
        <x-slot name="actions">
            <a href="{{ route('profile.edit') }}" class="btn-secondary">
                <x-icon name="user" class="h-4 w-4" /> Profil Saya
            </a>
        </x-slot>
    </x-page-header>

    <form method="POST" action="{{ route('appearance.update') }}" enctype="multipart/form-data"
          x-data="appearanceForm(@js([
              'mode' => old('theme_mode', $user->theme_mode ?: 'system'),
              'preset' => old('background_preset', $user->background_preset ?: 'aurora'),
              'blur' => (int) old('glass_blur', $user->glass_blur ?? 24),
              'wallpaper' => $wallpaperUrl,
              'presets' => $presets,
          ]))"
          class="grid gap-6 xl:grid-cols-3">
        @csrf
        @method('PATCH')

        <div class="space-y-6 xl:col-span-2">
            {{-- Mode --}}
            <div data-reveal class="card">
                <div class="border-b border-slate-100 p-5 sm:p-6">
                    <h2 class="section-title">Mode warna</h2>
                    <p class="muted mt-0.5">Pilih otomatis mengikuti pengaturan perangkat, atau kunci terang/gelap.</p>
                </div>

                <div class="grid gap-3 p-5 sm:grid-cols-3 sm:p-6">
                    @foreach ($modes as $value => $label)
                        <label class="relative cursor-pointer">
                            <input type="radio" name="theme_mode" value="{{ $value }}" class="peer sr-only"
                                   x-model="mode" @change="setMode('{{ $value }}')">
                            <div class="flex h-full flex-col gap-3 rounded-2xl border border-white/70 bg-white/50 p-4 shadow-xs backdrop-blur-xl transition duration-200 hover:-translate-y-0.5 hover:bg-white/80 peer-checked:border-indigo-400 peer-checked:bg-indigo-50/70 peer-checked:shadow-[0_16px_40px_-24px_rgba(79,70,229,0.6)]">
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-600 ring-1 ring-inset ring-indigo-500/20">
                                    <x-icon :name="$value === 'system' ? 'monitor' : ($value === 'light' ? 'sun' : 'moon')" class="h-5 w-5" />
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ $label }}</p>
                                    <p class="mt-0.5 text-[11px] leading-relaxed text-slate-500">
                                        @if ($value === 'system')
                                            Mengikuti tema perangkat secara otomatis.
                                        @elseif ($value === 'light')
                                            Selalu tampil terang sepanjang hari.
                                        @else
                                            Nyaman untuk malam hari dan ruangan gelap.
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Presets --}}
            <div data-reveal class="card">
                <div class="border-b border-slate-100 p-5 sm:p-6">
                    <h2 class="section-title">Latar belakang</h2>
                    <p class="muted mt-0.5">Gradasi warna lembut yang otomatis menyesuaikan mode terang/gelap.</p>
                </div>

                <div class="grid grid-cols-2 gap-3 p-5 sm:grid-cols-3 sm:p-6">
                    @foreach ($presets as $key => $preset)
                        <label class="relative cursor-pointer">
                            <input type="radio" name="background_preset" value="{{ $key }}" class="peer sr-only"
                                   x-model="preset" @change="setPreset('{{ $key }}')">
                            <div class="h-full rounded-2xl border border-white/70 bg-white/50 p-2.5 shadow-xs backdrop-blur-xl transition duration-200 hover:-translate-y-0.5 hover:bg-white/80 peer-checked:border-indigo-400 peer-checked:ring-2 peer-checked:ring-indigo-400/40">
                                <div class="h-16 w-full rounded-xl ring-1 ring-inset ring-white/40" style="background-image: {{ $preset['swatch'] }}"></div>
                                <p class="mt-2 text-xs font-semibold text-slate-900">{{ $preset['label'] }}</p>
                                <p class="mt-0.5 text-[10px] leading-snug text-slate-500">{{ $preset['description'] }}</p>
                            </div>
                        </label>
                    @endforeach

                    {{-- Custom wallpaper tile, only when a wallpaper exists. --}}
                    <label class="relative cursor-pointer" x-show="preview" x-cloak>
                        <input type="radio" name="background_preset" value="custom" class="peer sr-only"
                               x-model="preset" @change="setPreset('custom')">
                        <div class="h-full rounded-2xl border border-white/70 bg-white/50 p-2.5 shadow-xs backdrop-blur-xl transition duration-200 hover:-translate-y-0.5 hover:bg-white/80 peer-checked:border-indigo-400 peer-checked:ring-2 peer-checked:ring-indigo-400/40">
                            <div class="h-16 w-full overflow-hidden rounded-xl ring-1 ring-inset ring-white/40">
                                <img :src="preview" alt="Wallpaper kustom" class="h-full w-full object-cover">
                            </div>
                            <p class="mt-2 text-xs font-semibold text-slate-900">Wallpaper saya</p>
                            <p class="mt-0.5 text-[10px] leading-snug text-slate-500">Gambar yang Anda unggah</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Glass blur --}}
            <div data-reveal class="card">
                <div class="border-b border-slate-100 p-5 sm:p-6">
                    <h2 class="section-title">Ketebalan kaca</h2>
                    <p class="muted mt-0.5">Atur seberapa buram kotak informasi. Naikkan bila teks terasa menyatu dengan latar.</p>
                </div>

                <div class="p-5 sm:p-6">
                    <div class="flex items-center justify-between gap-3">
                        <label for="glass_blur" class="label mb-0">Tingkat keburaman</label>
                        <span class="rounded-full bg-indigo-50/80 px-3 py-1 text-xs font-bold text-indigo-700 ring-1 ring-inset ring-indigo-500/20"
                              x-text="blur + ' px'"></span>
                    </div>

                    <input id="glass_blur" name="glass_blur" type="range" min="0" max="40" step="4"
                           x-model.number="blur" @input="setBlur(blur)"
                           class="mt-4 h-2 w-full cursor-pointer appearance-none rounded-full bg-slate-200 accent-indigo-600">

                    <div class="mt-1.5 flex justify-between text-[10px] font-medium text-slate-500">
                        <span>Jernih</span>
                        <span>Buram</span>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ([0, 8, 16, 24, 32, 40] as $value)
                            <button type="button" @click="setBlur({{ $value }})"
                                    :class="blur === {{ $value }} ? 'border-indigo-400 bg-indigo-50/80 text-indigo-700' : 'border-white/80 bg-white/60 text-slate-600 hover:bg-white/85'"
                                    class="touch-manipulation rounded-full border px-3.5 py-2 text-xs font-semibold transition active:scale-95">
                                {{ $value }} px
                            </button>
                        @endforeach
                    </div>

                    <p class="mt-3 text-[11px] leading-relaxed text-slate-500">
                        0 px membuat kaca sepenuhnya jernih. 24 px adalah bawaan yang seimbang untuk sebagian besar layar.
                    </p>
                </div>
            </div>

            {{-- Wallpaper upload --}}
            <div data-reveal class="card">
                <div class="border-b border-slate-100 p-5 sm:p-6">
                    <h2 class="section-title">Wallpaper kustom</h2>
                    <p class="muted mt-0.5">Unggah foto atau gambar favorit Anda. Format PNG, JPG, atau WEBP, maksimal 4 MB.</p>
                </div>

                <div class="p-5 sm:p-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                        <div class="relative h-28 w-full shrink-0 overflow-hidden rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/60 sm:w-44">
                            <template x-if="preview">
                                <img :src="preview" alt="Pratinjau wallpaper" class="h-full w-full object-cover">
                            </template>
                            <template x-if="! preview">
                                <div class="flex h-full w-full flex-col items-center justify-center gap-1 text-slate-400">
                                    <x-icon name="image" class="h-6 w-6" />
                                    <span class="text-[10px] font-medium">Belum ada wallpaper</span>
                                </div>
                            </template>
                        </div>

                        <div class="flex-1">
                            <div class="flex flex-wrap gap-2">
                                <label for="wallpaper" class="btn-secondary cursor-pointer">
                                    <x-icon name="download" class="h-4 w-4 rotate-180" /> Pilih Gambar
                                    <input id="wallpaper" name="wallpaper" type="file"
                                           accept="image/png,image/jpeg,image/webp"
                                           class="sr-only" @change="pickWallpaper($event)">
                                </label>

                                <button type="button" class="btn-ghost text-rose-600 hover:bg-rose-50/80 hover:text-rose-700"
                                        x-show="preview" x-cloak @click="removeWallpaper()">
                                    <x-icon name="trash" class="h-4 w-4" /> Hapus
                                </button>
                            </div>

                            <p class="mt-2 text-[11px] text-slate-400" x-text="fileName || 'Belum ada file dipilih'"></p>
                            <p class="mt-1 text-[11px] leading-relaxed text-slate-400">
                                Wallpaper akan otomatis diberi lapisan transparan agar teks tetap mudah dibaca.
                            </p>
                        </div>
                    </div>

                    <input type="hidden" name="remove_wallpaper" :value="removed ? 1 : 0">
                    <x-input-error :messages="$errors->get('wallpaper')" class="mt-2" />
                </div>
            </div>
        </div>

        <div class="space-y-6">
            {{-- Live preview --}}
            <div data-reveal class="card p-5 sm:p-6">
                <h3 class="text-sm font-bold text-slate-900">Pratinjau</h3>
                <p class="mt-0.5 text-xs text-slate-500">Perubahan langsung terlihat di seluruh halaman.</p>

                <div class="mt-4 h-44 overflow-hidden rounded-2xl border border-white/70 shadow-inner" :style="previewStyle">
                    <div class="flex h-full flex-col justify-between p-4">
                        <div class="flex items-center justify-between">
                            <span class="rounded-full bg-white/70 px-2.5 py-1 text-[10px] font-semibold text-slate-700 backdrop-blur">Kasirin</span>
                            <span class="rounded-full bg-white/70 px-2.5 py-1 text-[10px] font-semibold text-slate-700 backdrop-blur" x-text="modeLabel"></span>
                        </div>
                        <div class="app-glass rounded-xl border border-white/80 bg-white/75 p-3 shadow-xs">
                            <p class="text-[10px] font-medium text-slate-500">Penjualan hari ini</p>
                            <p class="mt-0.5 text-lg font-extrabold text-slate-900">Rp 2.470.860</p>
                        </div>
                    </div>
                </div>

                <div class="mt-4 space-y-2 text-[11px] text-slate-500">
                    <p class="flex items-center gap-2">
                        <x-icon name="check-simple" class="h-3.5 w-3.5 text-emerald-500" />
                        Preferensi tersimpan per akun.
                    </p>
                    <p class="flex items-center gap-2">
                        <x-icon name="check-simple" class="h-3.5 w-3.5 text-emerald-500" />
                        Otomatis mengikuti sistem saat mode "Ikuti sistem".
                    </p>
                </div>
            </div>

            <div data-reveal class="card p-5 sm:p-6">
                <div class="flex flex-col gap-2">
                    <button type="submit" class="btn-primary w-full justify-center">
                        <x-icon name="check" class="h-4 w-4" /> Simpan Tampilan
                    </button>
                    <button type="button" class="btn-secondary w-full justify-center" @click="resetAll()">
                        <x-icon name="refresh" class="h-4 w-4" /> Kembalikan Default
                    </button>
                    <a href="{{ route('dashboard') }}" class="btn-ghost w-full justify-center">Batal</a>
                </div>
            </div>

            <div class="rounded-2xl border border-indigo-100 bg-indigo-50/70 p-4">
                <div class="flex gap-3">
                    <x-icon name="palette" class="mt-0.5 h-4 w-4 shrink-0 text-indigo-600" />
                    <p class="text-[11px] leading-relaxed text-indigo-900">
                        Tombol cepat di header bisa dipakai untuk berganti mode tanpa membuka halaman ini.
                    </p>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
        <script>
            function appearanceForm(config) {
                return {
                    mode: config.mode,
                    preset: config.preset,
                    blur: Number(config.blur ?? 24),
                    preview: config.wallpaper,
                    presets: config.presets,
                    removed: false,
                    fileName: '',

                    init() {
                        this.setBlur(this.blur);
                    },

                    get modeLabel() {
                        return { system: 'Ikuti sistem', light: 'Terang', dark: 'Gelap' }[this.mode] || 'Ikuti sistem';
                    },

                    get previewStyle() {
                        if (this.preset === 'custom' && this.preview) {
                            const scrim = this.mode === 'dark' ? 'rgba(7,11,26,0.86)' : 'rgba(238,240,251,0.82)';

                            return {
                                backgroundImage: `linear-gradient(${scrim}, ${scrim}), url('${this.preview}')`,
                                backgroundSize: 'cover',
                                backgroundPosition: 'center',
                            };
                        }

                        return {
                            backgroundImage: this.presets[this.preset]?.swatch || this.presets.aurora.swatch,
                        };
                    },

                    setMode(mode) {
                        this.mode = mode;
                        window.applyAppTheme(mode);
                    },

                    setBlur(value) {
                        this.blur = Math.max(0, Math.min(40, Number(value) || 0));
                        document.documentElement.style.setProperty('--glass-blur', `${this.blur}px`);
                    },

                    setPreset(preset) {
                        this.preset = preset;
                        document.documentElement.dataset.bg = preset;

                        if (preset === 'custom' && this.preview) {
                            document.documentElement.dataset.wallpaper = 'true';
                            document.documentElement.style.setProperty('--wallpaper-url', `url('${this.preview}')`);
                        } else {
                            document.documentElement.dataset.wallpaper = 'false';
                            document.documentElement.style.removeProperty('--wallpaper-url');
                        }
                    },

                    pickWallpaper(event) {
                        const file = event.target.files[0];
                        if (!file) {
                            return;
                        }

                        if (this.preview && this.preview.startsWith('blob:')) {
                            URL.revokeObjectURL(this.preview);
                        }

                        this.preview = URL.createObjectURL(file);
                        this.fileName = file.name;
                        this.removed = false;
                        this.setPreset('custom');
                    },

                    removeWallpaper() {
                        this.removed = true;
                        this.fileName = '';
                        this.preview = null;

                        const input = document.getElementById('wallpaper');
                        if (input) {
                            input.value = '';
                        }

                        this.setPreset('aurora');
                    },

                    resetAll() {
                        this.setMode('system');
                        this.removed = true;
                        this.fileName = '';
                        this.preview = null;
                        this.setPreset('aurora');
                        this.setBlur(24);
                    },
                };
            }
        </script>
    @endpush
</x-app-layout>
