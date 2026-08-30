<x-app-layout>
    <x-page-header
        title="Tambah Jenis Pinjaman"
        description="Tambahkan master jenis pinjaman baru."
    />

    <x-card>
        <form
            method="POST"
            action="{{ route('loan-types.store') }}"
            class="space-y-6"
        >
            @include('loan-types._form')

            <div class="flex flex-col-reverse gap-3 border-t border-gray-200 pt-5 sm:flex-row sm:justify-end dark:border-gray-700">
                <a
                    href="{{ route('loan-types.index') }}"
                    class="btn btn-secondary"
                >
                    Batal
                </a>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Simpan
                </button>
            </div>
        </form>
    </x-card>
</x-app-layout>
