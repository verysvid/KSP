<x-app-layout>
    <x-slot name="title">Buat Pengajuan Pinjaman Saya</x-slot>

    <x-page-header
        title="Buat Pengajuan Pinjaman Saya"
        description="Ajukan pinjaman baru melalui aplikasi koperasi." />

    <div class="mx-auto max-w-4xl">
        <x-card
            title="Informasi Pengajuan Pinjaman"
            description="Lengkapi informasi pengajuan. Data awal akan disimpan sebagai Draft.">

            <form
                method="POST"
                action="{{ route('member-loan-applications.store') }}">

                @include('member-loan-applications._form', [
                    'submitLabel' => 'Simpan Draft Pengajuan'
                ])
            </form>
        </x-card>
    </div>
</x-app-layout>
