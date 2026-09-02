<x-app-layout>
    <x-slot name="title">Tambah Akun</x-slot>
    <x-page-header title="Tambah Akun" description="Tambahkan akun baru ke Chart of Accounts.">
        <x-slot name="actions"><a href="{{ route('accounts.index') }}" class="btn btn-secondary">Kembali</a></x-slot>
    </x-page-header>
    <x-card>
        <form method="POST" action="{{ route('accounts.store') }}">
            @include('accounts._form')
            <div class="mt-6 flex justify-end gap-2">
                <a href="{{ route('accounts.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </x-card>
</x-app-layout>
