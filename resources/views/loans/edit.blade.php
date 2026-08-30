<x-app-layout>
    <x-slot name="title">Edit Pengajuan Pinjaman</x-slot>

    <x-page-header
        title="Edit Pengajuan Pinjaman"
        description="{{ $loan->loan_no }} - {{ $loan->member->name ?? '-' }}" />

    <div class="mx-auto max-w-4xl">
        <x-card
            title="Informasi Pengajuan Pinjaman"
            description="Perbarui pengajuan pinjaman selama masih berstatus Draft.">

            <form
                method="POST"
                action="{{ route('loans.update', $loan) }}">

                @method('PUT')

                @include('loans._form', [
                    'submitLabel' => 'Simpan Perubahan',
                    'loan' => $loan
                ])
            </form>
        </x-card>
    </div>
</x-app-layout>
