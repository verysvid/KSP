<x-app-layout>
    <x-slot name="title">Edit Jenis Simpanan</x-slot>

    <x-page-header
        title="Edit Jenis Simpanan"
        description="{{ $savingType->code }} - {{ $savingType->name }}" />

    <div class="mx-auto max-w-4xl">
        <x-card
            title="Informasi Jenis Simpanan"
            description="Perbarui informasi jenis simpanan.">

            <form
                method="POST"
                action="{{ route('saving-types.update', $savingType) }}">

                @method('PUT')

                @include('saving-types._form', [
                    'submitLabel' => 'Simpan Perubahan',
                    'savingType' => $savingType
                ])
            </form>
        </x-card>
    </div>
</x-app-layout>
