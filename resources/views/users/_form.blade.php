@csrf

<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div>
        <label for="name" class="form-label">
            Nama <span class="text-red-500">*</span>
        </label>
        <input id="name"
               name="name"
               type="text"
               required
               value="{{ old('name', $user->name ?? '') }}"
               class="form-control">
        @error('name') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="email" class="form-label">
            Email <span class="text-red-500">*</span>
        </label>
        <input id="email"
               name="email"
               type="email"
               required
               value="{{ old('email', $user->email ?? '') }}"
               class="form-control">
        @error('email') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="branch_id" class="form-label">
            Cabang <span class="text-red-500">*</span>
        </label>
        <select id="branch_id"
                name="branch_id"
                required
                class="form-select">
            <option value="">Pilih Cabang</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}"
                        @selected(old('branch_id', $user->branch_id ?? '') == $branch->id)>
                    {{ $branch->code }} - {{ $branch->name }}
                </option>
            @endforeach
        </select>
        @error('branch_id') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="role" class="form-label">
            Role <span class="text-red-500">*</span>
        </label>
        <select id="role"
                name="role"
                required
                class="form-select">
            <option value="">Pilih Role</option>
            @foreach($roles as $role)
                <option value="{{ $role->name }}"
                        @selected(old('role', isset($user) ? $user->getRoleNames()->first() : '') === $role->name)>
                    {{ $role->name }}
                </option>
            @endforeach
        </select>
        @error('role') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="password" class="form-label">
            Password @if(!isset($user)) <span class="text-red-500">*</span> @endif
        </label>
        <input id="password"
               name="password"
               type="password"
               @if(!isset($user)) required @endif
               class="form-control"
               autocomplete="new-password">
        @if(isset($user))
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                Kosongkan jika password tidak diubah.
            </p>
        @endif
        @error('password') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="password_confirmation" class="form-label">
            Konfirmasi Password
        </label>
        <input id="password_confirmation"
               name="password_confirmation"
               type="password"
               @if(!isset($user)) required @endif
               class="form-control"
               autocomplete="new-password">
    </div>

    <div class="md:col-span-2">
        <label class="form-label">Status</label>
        <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-800/60">
            <input type="checkbox"
                   name="is_active"
                   value="1"
                   @checked(old('is_active', $user->is_active ?? true))
                   class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
            <span class="text-sm font-medium text-slate-700 dark:text-slate-200">
                User aktif
            </span>
        </label>
        @error('is_active') <p class="form-error">{{ $message }}</p> @enderror
    </div>
</div>

<div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end dark:border-slate-800">
    <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
</div>
