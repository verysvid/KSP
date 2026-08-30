<x-app-layout>
    <x-page-header
        title="Edit Jenis Pinjaman"
        description="Perbarui informasi jenis pinjaman."
    />

    <x-card>
        <form
            method="POST"
            action="{{ route('loan-types.update', $loanType) }}"
            class="space-y-6"
        >
            @method('PUT')

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
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </x-card>
</x-app-layout>
