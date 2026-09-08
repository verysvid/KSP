<x-app-layout>
    <x-slot name="title">Edit Anggota</x-slot>

    <div class="mx-auto max-w-4xl">
        <x-card
            title="Informasi Anggota"
            description="Perbarui informasi anggota koperasi.">

            <form
                method="POST"
                action="{{ route('members.update', $member) }}"
                enctype="multipart/form-data">

                @method('PUT')

                @include('members._form', [
                    'submitLabel' => 'Simpan Perubahan',
                    'member' => $member
                ])
            </form>
        </x-card>
    </div>
</x-app-layout>
