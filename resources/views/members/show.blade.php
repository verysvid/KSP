<x-app-layout>
    <x-slot name="title">Detail Anggota</x-slot>

    <x-page-header title="Detail Anggota" description="{{ $member->member_number }}">
        <x-slot name="actions">
            <a href="{{ route('members.index') }}" class="btn btn-secondary">Kembali</a>

            @if(!$member->user_id && $member->member_status === 'NEW' && auth()->user()->can('member.edit'))
                <form method="POST" action="{{ route('members.activate', $member) }}" class="member-activation-form">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-primary">Aktivasi</button>
                </form>
            @endif

			@can('update', $member)
				@if($member->member_status !== 'NEW')
					<a href="{{ route('members.edit', $member) }}" class="btn btn-primary">Edit Anggota</a>
				@endif
			@endcan
        </x-slot>
    </x-page-header>

    <div
        x-data="{
            ktpModalOpen: false,
            openKtp() {
                this.ktpModalOpen = true;
            },
            closeKtp() {
                this.ktpModalOpen = false;
            }
        }"
    >
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <x-card>
                <div class="text-center">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-indigo-100 text-2xl font-extrabold text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300">
                        {{ strtoupper(substr($member->name, 0, 1)) }}
                    </div>

                    <h2 class="mt-4 text-lg font-bold text-slate-900 dark:text-white">
                        {{ $member->name }}
                    </h2>

                    <div class="mt-1 text-sm font-medium text-indigo-600 dark:text-indigo-400">
                        {{ $member->member_number }}
                    </div>

                    <div class="mt-3">
                        <x-status-badge :status="$member->member_status" />
                    </div>

                    <div class="mt-4">
                        @if($member->user_id && $member->user)
                            <a href="{{ route('users.show', $member->user) }}" class="badge badge-success">
                                User Login: {{ $member->user->email }}
                            </a>
                        @else
                            <span class="badge badge-neutral">Belum memiliki user login</span>
                        @endif
                    </div>
                </div>
            </x-card>

            <x-card
                class="lg:col-span-2"
                title="Informasi Anggota"
                description="Detail data anggota koperasi."
            >
                <div class="info-list">
                    <div class="info-row">
                        <span>Cabang</span>
                        <strong>{{ $member->branch?->name ?? '-' }}</strong>
                    </div>

                    <div class="info-row">
                        <span>Jenis Anggota</span>
                        <strong>{{ $member->memberType?->name ?? '-' }}</strong>
                    </div>

                    <div class="info-row">
                        <span>NIK</span>
                        <strong>{{ $member->nik ?: '-' }}</strong>
                    </div>

                    <div class="info-row">
                        <span>Jenis Kelamin</span>
                        <strong>
                            {{ $member->gender === 'L'
                                ? 'Laki-laki'
                                : ($member->gender === 'P' ? 'Perempuan' : '-') }}
                        </strong>
                    </div>

                    <div class="info-row">
                        <span>Tempat / Tanggal Lahir</span>
                        <strong>
                            {{ $member->birth_place ?: '-' }}
                            @if($member->birth_date)
                                / {{ $member->birth_date->format('d/m/Y') }}
                            @endif
                        </strong>
                    </div>

                    <div class="info-row">
                        <span>Telepon</span>
                        <strong>{{ $member->phone ?: '-' }}</strong>
                    </div>

                    <div class="info-row">
                        <span>Email</span>
                        <strong>{{ $member->email ?: '-' }}</strong>
                    </div>

                    <div class="info-row">
                        <span>Pekerjaan / Jabatan</span>
                        <strong>{{ $member->occupation ?: '-' }}</strong>
                    </div>

                    <div class="info-row">
                        <span>Unit Kerja</span>
                        <strong>{{ $member->work_unit ?: '-' }}</strong>
                    </div>

                    <div class="info-row">
                        <span>Nominal Simpanan Manasuka</span>
                        <strong>
                            {{ $member->amount_saving !== null
                                ? 'Rp ' . number_format((float) $member->amount_saving, 0, ',', '.')
                                : '-' }}
                        </strong>
                    </div>

                    <div class="info-row">
                        <span>Tanggal Bergabung</span>
                        <strong>{{ $member->join_date?->format('d/m/Y') ?? '-' }}</strong>
                    </div>

                    <div class="info-row">
                        <span>Alamat</span>
                        <strong class="max-w-md">{{ $member->address ?: '-' }}</strong>
                    </div>

                    <div class="info-row">
                        <span>Catatan</span>
                        <strong class="max-w-md">{{ $member->notes ?: '-' }}</strong>
                    </div>
                </div>
            </x-card>
        </div>

        <div class="mt-6">
            <x-card
                title="KTP / Kartu Identitas"
                description="Lampiran identitas anggota."
            >
                @if($member->id_card_image)
                    <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                        <button
                            type="button"
                            class="block w-full"
                            @click="openKtp()"
                        >
                            <img
                                src="{{ asset('storage/' . $member->id_card_image) }}"
                                alt="KTP {{ $member->name }}"
                                class="mx-auto max-h-80 rounded-xl object-contain shadow-sm"
                            >
                        </button>

                        <div class="mt-4 flex flex-wrap items-center justify-center gap-3">
                            <button
                                type="button"
                                class="btn btn-primary"
                                @click="openKtp()"
                            >
                                Lihat KTP
                            </button>

                            <a
                                href="{{ asset('storage/' . $member->id_card_image) }}"
                                target="_blank"
                                rel="noopener"
                                class="btn btn-secondary"
                            >
                                Buka Tab Baru
                            </a>
                        </div>

                        <p class="mt-3 text-center text-xs text-slate-500 dark:text-slate-400">
                            Klik gambar atau tombol “Lihat KTP” untuk memperbesar.
                        </p>
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                        Belum ada file KTP yang tersimpan untuk anggota ini.
                    </div>
                @endif
            </x-card>
        </div>

        @if($member->id_card_image)
            <div
                x-show="ktpModalOpen"
                x-cloak
                x-transition.opacity
                @keydown.escape.window="closeKtp()"
                @click.self="closeKtp()"
                class="fixed inset-0 z-[100] flex items-center justify-center bg-black/75 p-4"
                style="display: none;"
            >
                <div class="relative max-h-[92vh] w-full max-w-5xl overflow-auto rounded-2xl bg-white p-3 shadow-2xl dark:bg-slate-900">
                    <button
                        type="button"
                        class="absolute right-4 top-4 z-10 flex h-9 w-9 items-center justify-center rounded-full bg-black/70 text-xl font-bold text-white hover:bg-black"
                        @click="closeKtp()"
                        aria-label="Tutup KTP"
                    >
                        &times;
                    </button>

                    <img
                        src="{{ asset('storage/' . $member->id_card_image) }}"
                        alt="KTP {{ $member->name }} ukuran besar"
                        class="mx-auto max-h-[86vh] w-auto rounded-xl object-contain"
                    >
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        document.querySelectorAll('.member-activation-form').forEach((form) => {
            form.addEventListener('submit', function (event) {
                if (!window.swalConfirm) return;

                event.preventDefault();

                window.swalConfirm({
                    icon: 'question',
                    title: 'Aktivasi Anggota?',
                    text: 'Anggota akan diaktifkan dan dibuatkan akun login role Anggota dengan password awal password123.',
                    confirmButtonText: 'Ya, Aktivasi',
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });
        });
    </script>
    @endpush
</x-app-layout>
