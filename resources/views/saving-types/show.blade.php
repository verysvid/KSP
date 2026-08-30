<x-app-layout>
    <x-slot name="title">Detail Jenis Simpanan</x-slot>

    <x-page-header
        title="Detail Jenis Simpanan"
        description="{{ $savingType->code }} - {{ $savingType->name }}">

        <x-slot name="actions">
            <a href="{{ route('saving-types.index') }}"
               class="btn btn-secondary">
                Kembali
            </a>

            @can('update', $savingType)
                <a href="{{ route('saving-types.edit', $savingType) }}"
                   class="btn btn-primary">
                    Edit Jenis Simpanan
                </a>
            @endcan
        </x-slot>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <x-card>
            <div class="text-center">
                <div class="mx-auto flex h-20 w-20 items-center justify-center
                            rounded-2xl bg-indigo-100 text-2xl font-extrabold
                            text-indigo-700
                            dark:bg-indigo-500/15 dark:text-indigo-300">
                    {{ strtoupper(substr($savingType->code, 0, 2)) }}
                </div>

                <h2 class="mt-4 text-lg font-bold text-slate-900 dark:text-white">
                    {{ $savingType->name }}
                </h2>

                <div class="mt-1 text-sm font-medium text-indigo-600 dark:text-indigo-400">
                    {{ $savingType->code }}
                </div>

                <div class="mt-3">
                    <x-status-badge
                        :status="$savingType->is_active ? 'ACTIVE' : 'INACTIVE'" />
                </div>
            </div>
        </x-card>

        <x-card
            class="lg:col-span-2"
            title="Informasi Jenis Simpanan"
            description="Detail konfigurasi jenis simpanan.">

            <div class="info-list">
                <div class="info-row">
                    <span>Kode</span>
                    <strong>{{ $savingType->code }}</strong>
                </div>

                <div class="info-row">
                    <span>Nama</span>
                    <strong>{{ $savingType->name }}</strong>
                </div>

                <div class="info-row">
                    <span>Nominal Default</span>
                    <strong>
                        @if($savingType->amount !== null)
                            Rp {{ number_format($savingType->amount, 0, ',', '.') }}
                        @else
                            Fleksibel
                        @endif
                    </strong>
                </div>

                <div class="info-row">
                    <span>Wajib</span>
                    <strong>
                        {{ $savingType->is_mandatory ? 'Ya' : 'Tidak' }}
                    </strong>
                </div>

                <div class="info-row">
                    <span>Dapat Ditarik</span>
                    <strong>
                        {{ $savingType->is_withdrawable ? 'Ya' : 'Tidak' }}
                    </strong>
                </div>

                <div class="info-row">
                    <span>Deskripsi</span>
                    <strong class="max-w-md">
                        {{ $savingType->description ?: '-' }}
                    </strong>
                </div>
            </div>
        </x-card>

    </div>
</x-app-layout>
