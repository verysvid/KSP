<x-app-layout>
    <x-slot name="title">Tambah Jenis Simpanan</x-slot>

    <x-page-header
        title="Tambah Jenis Simpanan"
        description="Tambahkan jenis simpanan koperasi baru." />

    <div class="mx-auto max-w-4xl">
        <x-card
            title="Informasi Jenis Simpanan"
            description="Lengkapi informasi jenis simpanan.">

            <form
                method="POST"
                action="{{ route('saving-types.store') }}">

                @include('saving-types._form', [
                    'submitLabel' => 'Simpan Jenis Simpanan'
                ])
            </form>
        </x-card>
    </div>
</x-app-layout>
