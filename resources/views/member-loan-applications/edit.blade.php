<x-app-layout>
    <x-slot name="title">Edit Pengajuan Pinjaman Saya</x-slot>

    <x-page-header
        title="Edit Pengajuan Pinjaman Saya"
        description="{{ $loan->loan_no }} - {{ $member->name }}" />

    <div class="mx-auto max-w-4xl">
        <x-card
            title="Informasi Pengajuan Pinjaman"
            description="Perbarui pengajuan pinjaman selama masih berstatus Draft.">

            <form
                method="POST"
                action="{{ route('member-loan-applications.update', $loan) }}">

                @method('PUT')

                @include('member-loan-applications._form', [
                    'submitLabel' => 'Simpan Perubahan',
                    'loan' => $loan,
                    'member' => $member
                ])
            </form>
        </x-card>
    </div>
</x-app-layout>
