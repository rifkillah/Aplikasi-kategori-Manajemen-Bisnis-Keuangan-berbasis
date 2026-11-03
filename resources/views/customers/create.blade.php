<x-app-layout>
    <x-page-header title="Tambah Pelanggan" subtitle="Simpan data pelanggan untuk invoice yang lebih profesional.">
        <x-slot name="actions">
            <a href="{{ route('customers.index') }}" class="btn-secondary"><x-icon name="arrow-left" class="h-4 w-4" /> Kembali</a>
        </x-slot>
    </x-page-header>

    <form method="POST" action="{{ route('customers.store') }}">
        @csrf
        @include('customers._form')
    </form>
</x-app-layout>
