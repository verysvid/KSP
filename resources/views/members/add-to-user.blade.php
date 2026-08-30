<x-app-layout>
    <x-slot name="title">Add to User</x-slot>

    <x-page-header
        title="Add to User"
        description="Buat akun login untuk anggota {{ $member->member_number }}." />

    <div class="mx-auto max-w-4xl">
        <x-card
            title="Akun Login Anggota"
            description="Cabang user otomatis mengikuti cabang anggota.">

            <div class="mb-6 grid grid-cols-1 gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4 sm:grid-cols-2 dark:border-slate-700 dark:bg-slate-800/60">
                <div>
                    <div class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Anggota
                    </div>
                    <div class="mt-1 font-semibold text-slate-900 dark:text-white">
                        {{ $member->member_number }} - {{ $member->name }}
                    </div>
                </div>
                <div>
                    <div class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Cabang
                    </div>
                    <div class="mt-1 font-semibold text-slate-900 dark:text-white">
                        {{ $member->branch?->name ?? '-' }}
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('members.user.store', $member) }}">
                @csrf

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label for="name" class="form-label">Nama User <span class="text-red-500">*</span></label>
                        <input id="name" name="name" type="text" required
                               value="{{ old('name', $member->name) }}"
                               class="form-control">
                        @error('name') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="email" class="form-label">Email Login <span class="text-red-500">*</span></label>
                        <input id="email" name="email" type="email" required
                               value="{{ old('email', $member->email) }}"
                               class="form-control">
                        @error('email') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="role" class="form-label">Role <span class="text-red-500">*</span></label>
                        <select id="role" name="role" required class="form-select">
                            <option value="">Pilih Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}"
                                        @selected(old('role', 'Anggota') === $role->name)>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('role') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div></div>

                    <div>
                        <label for="password" class="form-label">Password <span class="text-red-500">*</span></label>
                        <input id="password" name="password" type="password" required
                               class="form-control" autocomplete="new-password">
                        @error('password') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="form-label">
                            Konfirmasi Password <span class="text-red-500">*</span>
                        </label>
                        <input id="password_confirmation" name="password_confirmation"
                               type="password" required class="form-control"
                               autocomplete="new-password">
                    </div>
                </div>

                <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end dark:border-slate-800">
                    <a href="{{ route('members.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Buat User Login</button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
