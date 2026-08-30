<x-app-layout>
    <x-slot name="title">Edit User</x-slot>

    <x-page-header
        title="Edit User"
        description="{{ $user->name }} - {{ $user->email }}" />

    <div class="mx-auto max-w-4xl">
        <x-card title="Informasi User"
                description="Perbarui akun, cabang, role, atau status user.">
            <form method="POST" action="{{ route('users.update', $user) }}">
                @method('PUT')
                @include('users._form', [
                    'submitLabel' => 'Simpan Perubahan',
                    'user' => $user
                ])
            </form>
        </x-card>
    </div>
</x-app-layout>
