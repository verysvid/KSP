<x-app-layout>
    <x-slot name="title">Tambah Anggota</x-slot>
	{{--
    <x-page-header
        title="Tambah Anggota"
        description="Tambahkan data anggota koperasi baru." />
	--}}
    <div class="mx-auto max-w-4xl">
        <x-card
            title="Informasi Anggota"
            description="Lengkapi informasi anggota koperasi.">

            <form
                method="POST"
                action="{{ route('members.store') }}"
                class="member-create-activation-form">

                @include('members._form', [
                    'submitLabel' => 'Aktivasi'
                ])
            </form>
        </x-card>
    </div>
    @push('scripts')
    <script>
        document.querySelectorAll('.member-create-activation-form').forEach((form) => {
            form.addEventListener('submit', function (event) {
                if (!window.swalConfirm) return;
                event.preventDefault();
                window.swalConfirm({
                    icon: 'question',
                    title: 'Aktivasi Anggota?',
                    text: 'Data anggota akan disimpan, diaktifkan, dan akun login role Anggota akan dibuat dengan password awal password123.',
                    confirmButtonText: 'Ya, Aktivasi',
                }).then((result) => { if (result.isConfirmed) form.submit(); });
            });
        });
    </script>
    @endpush
</x-app-layout>
