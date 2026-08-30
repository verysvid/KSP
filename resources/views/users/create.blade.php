<x-app-layout>
    <x-slot name="title">Tambah User</x-slot>

    <x-page-header
        title="Tambah User"
        description="Buat akun login baru dan tentukan cabang serta role." />

    <div class="mx-auto max-w-4xl">
        <x-card title="Informasi User"
                description="Lengkapi informasi akun user.">
            <form method="POST" action="{{ route('users.store') }}">
                @include('users._form', ['submitLabel' => 'Simpan User'])
            </form>
        </x-card>
    </div>
</x-app-layout>
