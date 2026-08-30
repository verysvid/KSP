<x-app-layout>
    <x-slot name="title">Tambah Pengajuan Pinjaman</x-slot>

    <x-page-header
        title="Tambah Pengajuan Pinjaman"
        description="Buat pengajuan pinjaman anggota baru." />

    <div class="mx-auto max-w-4xl">
        <x-card
            title="Informasi Pengajuan Pinjaman"
            description="Lengkapi informasi pengajuan. Data awal akan disimpan sebagai Draft.">

            <form
                method="POST"
                action="{{ route('loans.store') }}">

                @include('loans._form', [
                    'submitLabel' => 'Simpan Draft Pengajuan'
                ])
            </form>
        </x-card>
    </div>
</x-app-layout>
