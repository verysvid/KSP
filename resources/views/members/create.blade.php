<x-app-layout>
    <x-slot name="title">Tambah Anggota</x-slot>
	{{--
    <x-page-header
        title="Tambah Anggota"
        description="Tambahkan data anggota koperasi baru." />
	--}}
    <div class="mx-auto max-w-4xl">
        <x-card
            title="Informasi Anggota"
            description="Lengkapi informasi anggota koperasi.">

            <form
                method="POST"
                action="{{ route('members.store') }}">

                @include('members._form', [
                    'submitLabel' => 'Simpan Anggota'
                ])
            </form>
        </x-card>
    </div>
</x-app-layout>
