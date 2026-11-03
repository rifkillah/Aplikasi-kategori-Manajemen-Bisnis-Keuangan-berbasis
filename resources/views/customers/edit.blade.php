<x-app-layout>
    <x-page-header :title="'Edit '.$customer->name" subtitle="Perbarui informasi kontak dan catatan pelanggan.">
        <x-slot name="actions">
            <a href="{{ route('customers.show', $customer) }}" class="btn-secondary"><x-icon name="arrow-left" class="h-4 w-4" /> Kembali</a>
        </x-slot>
    </x-page-header>

    <form method="POST" action="{{ route('customers.update', $customer) }}">
        @csrf
        @method('PUT')
        @include('customers._form')
    </form>
</x-app-layout>
