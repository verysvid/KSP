@csrf

<div class="grid grid-cols-1 gap-5 md:grid-cols-2">

    <div>
        <label for="code" class="form-label">
            Kode Cabang <span class="text-red-500">*</span>
        </label>
        <input
            id="code"
            name="code"
            type="text"
            required
            maxlength="30"
            value="{{ old('code', $branch->code ?? '') }}"
            class="form-control"
            placeholder="BDG">
        @error('code')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="name" class="form-label">
            Nama Cabang <span class="text-red-500">*</span>
        </label>
        <input
            id="name"
            name="name"
            type="text"
            required
            maxlength="150"
            value="{{ old('name', $branch->name ?? '') }}"
            class="form-control"
            placeholder="Cabang Bandung">
        @error('name')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label for="address" class="form-label">Alamat</label>
        <textarea
            id="address"
            name="address"
            rows="3"
            class="form-textarea"
            placeholder="Alamat lengkap cabang">{{ old('address', $branch->address ?? '') }}</textarea>
        @error('address')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="phone" class="form-label">Telepon</label>
        <input
            id="phone"
            name="phone"
            type="text"
            maxlength="30"
            value="{{ old('phone', $branch->phone ?? '') }}"
            class="form-control"
            placeholder="022-1234567">
        @error('phone')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="email" class="form-label">Email</label>
        <input
            id="email"
            name="email"
            type="email"
            maxlength="150"
            value="{{ old('email', $branch->email ?? '') }}"
            class="form-control"
            placeholder="bandung@koperasi.test">
        @error('email')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="manager_name" class="form-label">Kepala Cabang</label>
        <input
            id="manager_name"
            name="manager_name"
            type="text"
            maxlength="150"
            value="{{ old('manager_name', $branch->manager_name ?? '') }}"
            class="form-control"
            placeholder="Nama Kepala Cabang">
        @error('manager_name')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="form-label">Status</label>

        <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-800/60">
            <input
                type="checkbox"
                name="is_active"
                value="1"
                @checked(old('is_active', $branch->is_active ?? true))
                class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">

            <span class="text-sm font-medium text-slate-700 dark:text-slate-200">
                Cabang aktif
            </span>
        </label>
    </div>

</div>

<div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end dark:border-slate-800">
    <a href="{{ route('branches.index') }}" class="btn btn-secondary">
        Batal
    </a>

    <button type="submit" class="btn btn-primary">
        {{ $submitLabel }}
    </button>
</div>
