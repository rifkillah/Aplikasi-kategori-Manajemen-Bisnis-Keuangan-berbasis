<x-app-layout>
    <x-page-header :title="'Edit '.$product->name" subtitle="Perbarui informasi produk, harga, dan batas stok.">
        <x-slot name="actions">
            <a href="{{ route('products.show', $product) }}" class="btn-secondary">
                <x-icon name="arrow-left" class="h-4 w-4" /> Kembali
            </a>
        </x-slot>
    </x-page-header>

    <form method="POST" action="{{ route('products.update', $product) }}">
        @csrf
        @method('PUT')
        @include('products._form')
    </form>
</x-app-layout>
