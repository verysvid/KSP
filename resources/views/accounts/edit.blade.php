<x-app-layout>
    <x-slot name="title">Edit Akun</x-slot>
    <x-page-header title="Edit Akun" description="{{ $account->code }} - {{ $account->name }}">
        <x-slot name="actions"><a href="{{ route('accounts.show', $account) }}" class="btn btn-secondary">Kembali</a></x-slot>
    </x-page-header>
    <x-card>
        <form method="POST" action="{{ route('accounts.update', $account) }}">
            @method('PUT')
            @include('accounts._form')
            <div class="mt-6 flex justify-end gap-2">
                <a href="{{ route('accounts.show', $account) }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </x-card>
</x-app-layout>
