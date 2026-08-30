<x-app-layout>
    <x-slot name="title">Tambah Cabang</x-slot>

    <x-page-header
        title="Tambah Cabang"
        description="Tambahkan cabang koperasi baru." />

    <div class="mx-auto max-w-4xl">
        <x-card
            title="Informasi Cabang"
            description="Lengkapi informasi cabang di bawah ini.">

            <form method="POST" action="{{ route('branches.store') }}">
                @include('branches._form', [
                    'submitLabel' => 'Simpan Cabang'
                ])
            </form>
        </x-card>
    </div>
</x-app-layout>
