<x-app-layout>
    <x-page-header title="Tambah Produk" subtitle="Masukkan detail produk baru ke katalog inventaris Anda.">
        <x-slot name="actions">
            <a href="{{ route('products.index') }}" class="btn-secondary">
                <x-icon name="arrow-left" class="h-4 w-4" /> Kembali
            </a>
        </x-slot>
    </x-page-header>

    <form method="POST" action="{{ route('products.store') }}">
        @csrf
        @include('products._form')
    </form>
</x-app-layout>
