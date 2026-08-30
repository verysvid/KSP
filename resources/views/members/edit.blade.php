<x-app-layout>
    <x-slot name="title">Edit Anggota</x-slot>
	{{--
    <x-page-header
        title="Edit Anggota"
        description="{{ $member->member_number }} - {{ $member->name }}" />
	--}}
    <div class="mx-auto max-w-4xl">
        <x-card
            title="Informasi Anggota"
            description="Perbarui informasi anggota koperasi.">

            <form
                method="POST"
                action="{{ route('members.update', $member) }}">

                @method('PUT')

                @include('members._form', [
                    'submitLabel' => 'Simpan Perubahan',
                    'member' => $member
                ])
            </form>
        </x-card>
    </div>
</x-app-layout>
