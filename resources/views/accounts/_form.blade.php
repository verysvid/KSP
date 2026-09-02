@csrf

<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div>
        <label for="code" class="form-label">Kode Akun <span class="text-red-500">*</span></label>
        <input id="code" name="code" type="text" maxlength="30" required value="{{ old('code', $account->code ?? '') }}" class="form-control" placeholder="1101">
        @error('code') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="name" class="form-label">Nama Akun <span class="text-red-500">*</span></label>
        <input id="name" name="name" type="text" maxlength="150" required value="{{ old('name', $account->name ?? '') }}" class="form-control" placeholder="Kas">
        @error('name') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="type" class="form-label">Tipe Akun <span class="text-red-500">*</span></label>
        <select id="type" name="type" class="form-select" required>
            <option value="">Pilih Tipe</option>
            <option value="ASSET" @selected(old('type', $account->type ?? '') === 'ASSET')>Aset</option>
            <option value="LIABILITY" @selected(old('type', $account->type ?? '') === 'LIABILITY')>Liabilitas</option>
            <option value="EQUITY" @selected(old('type', $account->type ?? '') === 'EQUITY')>Ekuitas</option>
            <option value="REVENUE" @selected(old('type', $account->type ?? '') === 'REVENUE')>Pendapatan</option>
            <option value="EXPENSE" @selected(old('type', $account->type ?? '') === 'EXPENSE')>Beban</option>
        </select>
        @error('type') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="parent_id" class="form-label">Parent Akun</label>
        <select id="parent_id" name="parent_id" class="form-select">
            <option value="">Tanpa Parent</option>
            @foreach($parents as $parent)
                <option value="{{ $parent->id }}" @selected((string) old('parent_id', $account->parent_id ?? '') === (string) $parent->id)>
                    {{ $parent->code }} - {{ $parent->name }}
                </option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Parent harus header/non-postable dan bertipe sama.</p>
        @error('parent_id') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="normal_balance" class="form-label">Normal Balance <span class="text-red-500">*</span></label>
        <select id="normal_balance" name="normal_balance" class="form-select" required>
            <option value="DEBIT" @selected(old('normal_balance', $account->normal_balance ?? 'DEBIT') === 'DEBIT')>Debit</option>
            <option value="CREDIT" @selected(old('normal_balance', $account->normal_balance ?? 'DEBIT') === 'CREDIT')>Kredit</option>
        </select>
        @error('normal_balance') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="sort_order" class="form-label">Urutan</label>
        <input id="sort_order" name="sort_order" type="number" min="0" max="999999" value="{{ old('sort_order', $account->sort_order ?? 0) }}" class="form-control">
        @error('sort_order') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div class="md:col-span-2">
        <label for="description" class="form-label">Deskripsi</label>
        <textarea id="description" name="description" rows="3" class="form-textarea" placeholder="Keterangan akun">{{ old('description', $account->description ?? '') }}</textarea>
        @error('description') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div class="md:col-span-2 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-4 dark:border-slate-700">
            <input type="checkbox" name="is_postable" value="1" class="mt-1" @checked(old('is_postable', $account->is_postable ?? true))>
            <span><strong class="block text-slate-900 dark:text-white">Posting Account</strong><span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">Dapat dipakai pada jurnal/transaksi.</span></span>
        </label>
        <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-4 dark:border-slate-700">
            <input type="checkbox" name="is_cash_bank" value="1" class="mt-1" @checked(old('is_cash_bank', $account->is_cash_bank ?? false))>
            <span><strong class="block text-slate-900 dark:text-white">Kas / Bank</strong><span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">Muncul di pilihan Kas/Bank.</span></span>
        </label>
        <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-4 dark:border-slate-700">
            <input type="checkbox" name="is_active" value="1" class="mt-1" @checked(old('is_active', $account->is_active ?? true))>
            <span><strong class="block text-slate-900 dark:text-white">Aktif</strong><span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">Akun tersedia digunakan.</span></span>
        </label>
    </div>
</div>
