<x-app-layout>
    <x-slot name="title">Edit Cabang</x-slot>

    <x-page-header
        title="Edit Cabang"
        description="{{ $branch->code }} - {{ $branch->name }}" />

    <div class="mx-auto max-w-4xl">
        <x-card
            title="Informasi Cabang"
            description="Perbarui informasi cabang.">

            <form method="POST" action="{{ route('branches.update', $branch) }}">
                @method('PUT')

                @include('branches._form', [
                    'submitLabel' => 'Simpan Perubahan',
                    'branch' => $branch
                ])
            </form>
        </x-card>
    </div>
</x-app-layout>
