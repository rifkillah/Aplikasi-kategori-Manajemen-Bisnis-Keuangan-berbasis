<x-app-layout>
    <x-page-header title="Pelanggan" subtitle="Database pelanggan untuk invoice dan riwayat pembelian.">
        <x-slot name="actions">
            <a href="{{ route('customers.create') }}" class="btn-primary">
                <x-icon name="plus" class="h-4 w-4" /> Tambah Pelanggan
            </a>
        </x-slot>
    </x-page-header>

    <div class="card overflow-hidden">
        <div class="border-b border-slate-100 p-4 sm:p-5">
            <form method="GET" action="{{ route('customers.index') }}" class="flex gap-2">
                <div class="relative flex-1">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <x-icon name="search" class="h-4 w-4" />
                    </span>
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama, telepon, atau email..."
                           class="input py-2.5 pl-10">
                </div>
                <button type="submit" class="btn-secondary"><x-icon name="search" class="h-4 w-4" /> Cari</button>
                @if (request('q'))
                    <a href="{{ route('customers.index') }}" class="btn-ghost">Reset</a>
                @endif
            </form>
        </div>

        @if ($customers->isEmpty())
            <x-empty-state title="Belum ada pelanggan" description="Tambahkan pelanggan agar invoice dan riwayat pembelian lebih rapi." icon="users">
                <x-slot name="action">
                    <a href="{{ route('customers.create') }}" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> Tambah Pelanggan</a>
                </x-slot>
            </x-empty-state>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Pelanggan</th>
                            <th>Kontak</th>
                            <th>Transaksi</th>
                            <th>Total belanja</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 bg-white">
                        @foreach ($customers as $customer)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-xs font-bold text-indigo-600">
                                            {{ collect(explode(' ', $customer->name))->take(2)->map(fn($p) => mb_substr($p, 0, 1))->implode('') }}
                                        </div>
                                        <div class="min-w-0">
                                            <a href="{{ route('customers.show', $customer) }}" class="block max-w-[220px] truncate text-sm font-semibold text-slate-800 hover:text-indigo-600">
                                                {{ $customer->name }}
                                            </a>
                                            <p class="max-w-[220px] truncate text-[11px] text-slate-400">{{ $customer->address ?: 'Alamat belum diisi' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <p class="text-xs text-slate-600">{{ $customer->phone ?? '—' }}</p>
                                    <p class="text-[11px] text-slate-400">{{ $customer->email ?? '—' }}</p>
                                </td>
                                <td>
                                    <span class="text-sm font-semibold text-slate-700">{{ $customer->transactions_count }}</span>
                                    <span class="text-[11px] text-slate-400">transaksi</span>
                                </td>
                                <td class="text-sm font-semibold text-slate-800">{{ rupiah($customer->total_spent ?? 0) }}</td>
                                <td>
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('customers.show', $customer) }}" title="Detail"
                                           class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                                            <x-icon name="eye" class="h-4 w-4" />
                                        </a>
                                        <a href="{{ route('customers.edit', $customer) }}" title="Edit"
                                           class="rounded-lg p-2 text-slate-400 transition hover:bg-indigo-50 hover:text-indigo-600">
                                            <x-icon name="edit" class="h-4 w-4" />
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-5 py-4">{{ $customers->links() }}</div>
        @endif
    </div>
</x-app-layout>
