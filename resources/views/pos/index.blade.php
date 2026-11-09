<x-app-layout>
    <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Kasir</h1>
            <p class="mt-1 text-sm text-slate-500">Scan barcode atau pilih produk untuk memulai transaksi.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-3 py-1.5 font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-500/20 backdrop-blur">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Sesi kasir aktif
            </span>
            <span class="rounded-full border border-white/70 bg-white/60 px-3 py-1.5 font-medium text-slate-600 shadow-sm backdrop-blur">{{ now()->translatedFormat('d M Y') }}</span>
        </div>
    </div>

    <div x-data="posApp()" x-effect="document.body.classList.toggle('overflow-hidden', cartOpen)"
         @keydown.escape.window="cartOpen = false"
         class="grid grid-cols-1 items-start gap-6 pb-24 lg:grid-cols-[1.55fr_1fr] lg:pb-0">
        {{-- Products panel --}}
        <div class="min-w-0 space-y-4">
            <div class="card p-4 sm:p-5">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-[1fr_auto]">
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <x-icon name="search" class="h-4 w-4" />
                        </span>
                        <input type="search" x-model="search" placeholder="Cari nama produk atau SKU..."
                               class="input py-2.5 pl-10" autocomplete="off">
                    </div>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-indigo-500">
                            <x-icon name="barcode" class="h-4 w-4" />
                        </span>
                        <input x-ref="scanInput" type="text" x-model="barcode" @keydown.enter.prevent="addByBarcode()"
                               x-init="if (window.innerWidth >= 1024) $el.focus()"
                               placeholder="Scan barcode lalu Enter" class="input border-indigo-200 bg-indigo-50/40 py-2.5 pl-10 pr-14 focus:border-indigo-400"
                               autocomplete="off">
                        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                            <kbd class="rounded border border-slate-200 bg-white px-1.5 py-0.5 text-[9px] font-semibold text-slate-400">ENTER</kbd>
                        </span>
                    </div>
                </div>

                <div x-show="scanMessage" x-cloak x-transition
                     class="mt-3 flex items-center gap-2 rounded-xl px-3 py-2 text-xs font-medium"
                     :class="scanError ? 'bg-rose-50 text-rose-700' : 'bg-emerald-50 text-emerald-700'">
                    <x-icon name="alert" class="h-4 w-4 shrink-0" />
                    <span x-text="scanMessage"></span>
                </div>

                <div class="mt-3 flex gap-2 overflow-x-auto pb-1">
                    <button type="button" @click="activeCategory = 'Semua'"
                            class="shrink-0 rounded-full border px-3.5 py-1.5 text-xs font-semibold transition"
                            :class="activeCategory === 'Semua' ? 'border-transparent bg-slate-900 text-white shadow-md shadow-slate-900/20' : 'border-white/70 bg-white/50 text-slate-600 backdrop-blur hover:bg-white/80'">
                        Semua
                    </button>
                    <template x-for="category in categories" :key="category">
                        <button type="button" @click="activeCategory = category"
                                class="shrink-0 rounded-full border px-3.5 py-1.5 text-xs font-semibold transition"
                                :class="activeCategory === category ? 'border-transparent bg-slate-900 text-white shadow-md shadow-slate-900/20' : 'border-white/70 bg-white/50 text-slate-600 backdrop-blur hover:bg-white/80'"
                                x-text="category"></button>
                    </template>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2.5 sm:gap-3 xl:grid-cols-3">
                <template x-for="product in filteredProducts" :key="product.id">
                    <button type="button" @click="addProduct(product)"
                            :disabled="product.stock <= 0 || quantityInCart(product.id) >= product.stock"
                            class="group flex h-full flex-col rounded-[22px] border border-white/70 bg-white/55 p-3 text-left shadow-[0_14px_36px_-24px_rgba(15,23,42,0.45),inset_0_1px_0_rgba(255,255,255,0.9)] backdrop-blur-xl transition duration-200 hover:-translate-y-0.5 hover:border-indigo-300 hover:bg-white/75 hover:shadow-[0_20px_44px_-22px_rgba(79,70,229,0.45)] disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:translate-y-0 sm:p-4">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-600 ring-1 ring-inset ring-indigo-500/15 sm:h-10 sm:w-10">
                                <x-icon name="cube" class="h-4 w-4 sm:h-5 sm:w-5" />
                            </div>
                            <span class="badge text-[10px]"
                                  :class="product.stock <= 0 ? 'badge-danger' : (product.stock <= 5 ? 'badge-warning' : 'badge-success')"
                                  x-text="product.stock <= 0 ? 'Habis' : product.stock + ' ' + product.unit"></span>
                        </div>
                        <p class="mt-2.5 line-clamp-2 text-xs font-semibold leading-snug text-slate-800 sm:mt-3 sm:text-sm" x-text="product.name"></p>
                        <p class="mt-0.5 line-clamp-1 text-[10px] text-slate-400" x-text="product.category + ' · ' + product.sku"></p>
                        <div class="mt-auto flex items-end justify-between gap-1 pt-2.5 sm:pt-3">
                            <p class="text-xs font-bold text-indigo-600 sm:text-sm" x-text="'Rp ' + format(product.price)"></p>
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-white/70 text-slate-500 ring-1 ring-inset ring-slate-900/5 transition group-hover:bg-indigo-600 group-hover:text-white">
                                <x-icon name="plus" class="h-3.5 w-3.5" />
                            </span>
                        </div>
                    </button>
                </template>
            </div>

            <div x-show="filteredProducts.length === 0" x-cloak class="card">
                <x-empty-state title="Produk tidak ditemukan" description="Coba kata kunci lain atau scan barcode produk." icon="search" />
            </div>
        </div>

        {{-- Mobile cart backdrop --}}
        <div x-show="cartOpen" x-cloak @click="cartOpen = false"
             class="fixed inset-0 z-40 bg-slate-950/50 backdrop-blur-sm lg:hidden"></div>

        {{-- Cart --}}
        <div class="card hidden min-w-0 lg:sticky lg:top-24 lg:flex lg:max-h-[calc(100dvh-12rem)] lg:flex-col lg:overflow-hidden"
             :class="cartOpen ? 'fixed inset-x-0 bottom-0 z-50 !block max-h-[88vh] overflow-y-auto rounded-b-none shadow-2xl lg:sticky lg:rounded-[28px] lg:shadow-sm' : ''">
            <div class="flex shrink-0 items-center justify-between border-b border-slate-900/[0.06] p-4 sm:p-5">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-600 ring-1 ring-inset ring-indigo-500/15">
                        <x-icon name="cart" class="h-4.5 w-4.5" />
                    </span>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Keranjang</h2>
                        <p class="text-[11px] text-slate-400" x-text="cartCount + ' item · ' + cart.length + ' produk'"></p>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <button type="button" @click="clearCart()" x-show="cart.length" x-cloak
                            class="text-[11px] font-semibold text-rose-500 hover:text-rose-600">Kosongkan</button>
                    <button type="button" @click="cartOpen = false"
                            class="rounded-full p-1.5 text-slate-400 transition hover:bg-white/70 hover:text-slate-600 lg:hidden">
                        <x-icon name="x" class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <form method="POST" action="{{ route('pos.store') }}" @submit="prepareSubmit()" class="flex flex-col lg:min-h-0 lg:flex-1">
                @csrf

                <div class="max-h-[38vh] overflow-y-auto p-4 sm:p-5 lg:max-h-none lg:min-h-0 lg:flex-1">
                    <div x-show="cart.length === 0" class="flex flex-col items-center py-8 text-center">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl border border-white/70 bg-white/60 text-slate-400 shadow-[inset_0_1px_0_rgba(255,255,255,0.9)] backdrop-blur-xl">
                            <x-icon name="cart" class="h-7 w-7" />
                        </div>
                        <p class="mt-3 text-sm font-semibold text-slate-700">Keranjang masih kosong</p>
                        <p class="mt-1 max-w-[220px] text-xs text-slate-400">Pilih produk dari daftar atau scan barcode untuk menambahkan.</p>
                    </div>

                    <div class="space-y-3">
                        <template x-for="(item, index) in cart" :key="item.id">
                            <div class="rounded-2xl border border-white/70 bg-white/55 p-3 shadow-[inset_0_1px_0_rgba(255,255,255,0.8)] backdrop-blur-xl">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="truncate text-xs font-semibold text-slate-800" x-text="item.name"></p>
                                        <p class="mt-0.5 text-[10px] text-slate-400" x-text="'Rp ' + format(item.price) + ' / ' + item.unit"></p>
                                    </div>
                                    <button type="button" @click="removeItem(index)" class="rounded-full p-1 text-slate-400 transition hover:bg-rose-500/10 hover:text-rose-500">
                                        <x-icon name="x" class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                                <div class="mt-2.5 flex items-center justify-between">
                                    <div class="flex items-center rounded-full border border-white/80 bg-white/70 shadow-sm">
                                        <button type="button" @click="decrease(index)" class="rounded-full px-2.5 py-1.5 text-slate-500 transition hover:text-indigo-600">
                                            <x-icon name="minus" class="h-3 w-3" />
                                        </button>
                                        <span class="min-w-[28px] text-center text-xs font-bold text-slate-800" x-text="item.qty"></span>
                                        <button type="button" @click="increase(index)" :disabled="item.qty >= item.stock"
                                                class="rounded-full px-2.5 py-1.5 text-slate-500 transition hover:text-indigo-600 disabled:opacity-30">
                                            <x-icon name="plus" class="h-3 w-3" />
                                        </button>
                                    </div>
                                    <p class="text-xs font-bold text-slate-800" x-text="'Rp ' + format(item.price * item.qty)"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="shrink-0 border-t border-slate-900/[0.06] p-4 sm:p-5">
                    <div class="space-y-3">
                        <div>
                            <label class="label text-xs">Pelanggan</label>
                            <select name="customer_id" x-model="customerId" class="input py-2 text-xs">
                                <option value="">Pelanggan umum</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="label text-xs">Metode pembayaran</label>
                            <div class="grid grid-cols-4 gap-1.5">
                                <input type="hidden" name="payment_method" :value="paymentMethod">
                                @foreach ([['cash', 'Tunai'], ['qris', 'QRIS'], ['transfer', 'Transfer'], ['other', 'Lain']] as [$value, $label])
                                    <button type="button" @click="paymentMethod = '{{ $value }}'"
                                            class="rounded-full border px-1 py-1.5 text-[10px] font-semibold transition"
                                            :class="paymentMethod === '{{ $value }}' ? 'border-transparent bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'border-white/70 bg-white/50 text-slate-500 backdrop-blur hover:bg-white/80'">
                                        {{ $label }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="label text-xs">Diskon (Rp)</label>
                                <input type="number" name="discount" x-model.number="discount" min="0" step="500" class="input py-2 text-xs" placeholder="0">
                            </div>
                            <div>
                                <label class="label text-xs">Uang diterima</label>
                                <input type="number" name="paid_amount" x-model.number="paid" min="0" step="1000" class="input py-2 text-xs" placeholder="0">
                            </div>
                        </div>

                        <div class="space-y-1.5 rounded-2xl border border-white/70 bg-white/50 p-3 text-xs backdrop-blur-xl">
                            <div class="flex justify-between text-slate-500">
                                <span>Subtotal</span>
                                <span class="font-medium text-slate-700" x-text="'Rp ' + format(subtotal)"></span>
                            </div>
                            <div x-show="taxPercent > 0" class="flex justify-between text-slate-500">
                                <span>Pajak (<span x-text="taxPercent"></span>%)</span>
                                <span class="font-medium text-slate-700" x-text="'Rp ' + format(tax)"></span>
                            </div>
                            <div x-show="discount > 0" class="flex justify-between text-rose-500">
                                <span>Diskon</span>
                                <span class="font-medium" x-text="'- Rp ' + format(discount)"></span>
                            </div>
                            <div class="flex justify-between border-t border-slate-900/10 pt-1.5 text-sm">
                                <span class="font-semibold text-slate-700">Total</span>
                                <span class="font-bold text-slate-900" x-text="'Rp ' + format(total)"></span>
                            </div>
                            <div x-show="paid !== '' && paid !== null" class="flex justify-between text-emerald-600">
                                <span>Kembalian</span>
                                <span class="font-semibold" x-text="'Rp ' + format(change)"></span>
                            </div>
                        </div>

                        <input type="hidden" name="notes" :value="notes">

                        <button type="submit" :disabled="cart.length === 0"
                                class="btn-primary w-full py-3 text-sm disabled:opacity-50">
                            <x-icon name="check" class="h-4 w-4" />
                            Simpan & Cetak Invoice
                        </button>
                        <p class="text-center text-[10px] text-slate-400">Transaksi akan mengurangi stok secara otomatis.</p>
                    </div>
                </div>

                {{-- Dynamic item inputs --}}
                <template x-for="(item, index) in cart" :key="'input-' + item.id">
                    <div>
                        <input type="hidden" :name="'items[' + index + '][product_id]'" :value="item.id">
                        <input type="hidden" :name="'items[' + index + '][quantity]'" :value="item.qty">
                    </div>
                </template>
            </form>
        </div>

        {{-- Mobile cart summary bar --}}
        <div x-show="cart.length > 0" x-cloak
             class="fixed inset-x-0 bottom-0 z-30 px-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))] lg:hidden">
            <div class="mx-auto flex max-w-lg items-center gap-3 rounded-[26px] border border-white/70 bg-white/70 p-3 shadow-[0_20px_50px_-16px_rgba(15,23,42,0.5),inset_0_1px_0_rgba(255,255,255,0.95)] backdrop-blur-2xl">
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] text-slate-500">
                        <span x-text="cartCount"></span> item · <span x-text="cart.length"></span> produk
                    </p>
                    <p class="truncate text-base font-extrabold text-slate-900" x-text="'Rp ' + format(total)"></p>
                </div>
                <button type="button" @click="cartOpen = true" class="btn-primary shrink-0 px-5 py-3">
                    <x-icon name="cart" class="h-4 w-4" /> Keranjang
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            window.POS_PRODUCTS = @json($products);
            window.POS_TAX_PERCENT = {{ (float) $tenant->tax_percent }};
            window.POS_LOOKUP_URL = @json(route('pos.lookup'));

            function posApp() {
                return {
                    products: window.POS_PRODUCTS,
                    search: '',
                    barcode: '',
                    activeCategory: 'Semua',
                    cart: [],
                    cartOpen: false,
                    customerId: '',
                    discount: 0,
                    paid: '',
                    paymentMethod: 'cash',
                    notes: '',
                    taxPercent: window.POS_TAX_PERCENT,
                    scanMessage: '',
                    scanError: false,

                    get categories() {
                        return [...new Set(this.products.map(product => product.category))].sort();
                    },
                    get filteredProducts() {
                        const term = this.search.toLowerCase().trim();
                        return this.products.filter(product => {
                            const matchesCategory = this.activeCategory === 'Semua' || product.category === this.activeCategory;
                            const matchesTerm = !term
                                || product.name.toLowerCase().includes(term)
                                || product.sku.toLowerCase().includes(term)
                                || (product.barcode || '').includes(term);
                            return matchesCategory && matchesTerm;
                        });
                    },
                    get cartCount() {
                        return this.cart.reduce((sum, item) => sum + item.qty, 0);
                    },
                    get subtotal() {
                        return this.cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
                    },
                    get discountValue() {
                        return Math.min(Number(this.discount) || 0, this.subtotal);
                    },
                    get tax() {
                        return Math.round((this.subtotal - this.discountValue) * this.taxPercent) / 100;
                    },
                    get total() {
                        return Math.max(0, this.subtotal - this.discountValue + this.tax);
                    },
                    get change() {
                        return Math.max(0, (Number(this.paid) || 0) - this.total);
                    },
                    format(value) {
                        return new Intl.NumberFormat('id-ID').format(Math.round(Number(value) || 0));
                    },
                    quantityInCart(id) {
                        const item = this.cart.find(item => item.id === id);
                        return item ? item.qty : 0;
                    },
                    addProduct(product) {
                        if (product.stock <= 0) return;
                        const existing = this.cart.find(item => item.id === product.id);
                        if (existing) {
                            if (existing.qty < product.stock) {
                                existing.qty++;
                            } else {
                                this.notify('Stok ' + product.name + ' sudah mencapai batas.', true);
                            }
                        } else {
                            this.cart.push({ ...product, qty: 1 });
                        }
                        this.$nextTick(() => this.$refs.scanInput.focus());
                    },
                    increase(index) {
                        const item = this.cart[index];
                        if (item.qty < item.stock) item.qty++;
                    },
                    decrease(index) {
                        if (this.cart[index].qty > 1) {
                            this.cart[index].qty--;
                        } else {
                            this.cart.splice(index, 1);
                        }
                    },
                    removeItem(index) {
                        this.cart.splice(index, 1);
                    },
                    clearCart() {
                        this.cart = [];
                        this.cartOpen = false;
                        this.discount = 0;
                        this.paid = '';
                        this.notes = '';
                    },
                    notify(message, isError = false) {
                        this.scanMessage = message;
                        this.scanError = isError;
                        setTimeout(() => { this.scanMessage = ''; }, 4000);
                    },
                    async addByBarcode() {
                        const code = this.barcode.trim();
                        if (!code) return;

                        try {
                            const response = await fetch(window.POS_LOOKUP_URL + '?code=' + encodeURIComponent(code), {
                                headers: { 'Accept': 'application/json' }
                            });
                            const data = await response.json();

                            if (!response.ok) {
                                this.notify(data.message || 'Produk tidak ditemukan.', true);
                            } else {
                                this.addProduct(data);
                                this.notify(data.name + ' ditambahkan ke keranjang.', false);
                            }
                        } catch (error) {
                            this.notify('Gagal memindai barcode. Coba lagi.', true);
                        }

                        this.barcode = '';
                        this.$nextTick(() => this.$refs.scanInput.focus());
                    },
                    prepareSubmit() {
                        if (this.cart.length === 0) return;
                        if (this.paid === '' || this.paid === null) {
                            this.paid = this.total;
                        }
                        this.discount = this.discountValue;
                    }
                };
            }
        </script>
    @endpush
</x-app-layout>
