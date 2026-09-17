<x-card title="Parameter Tahun Buku" description="Persentase dapat disesuaikan mengikuti keputusan RAT. Basis Jasa Modal dapat memilih satu atau beberapa jenis simpanan.">
    <form method="POST" action="{{ $action }}" id="shu-form">
        @csrf
        @if($method !== 'POST') @method($method) @endif

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-4">
            @if($isSuperAdmin)
                <div><label class="form-label">Cabang <span class="text-red-500">*</span></label><select name="branch_id" class="form-select" required><option value="">Pilih Cabang</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected((string)old('branch_id',$period->branch_id)===(string)$branch->id)>{{ $branch->code }} - {{ $branch->name }}</option>@endforeach</select>@error('branch_id')<p class="form-error">{{ $message }}</p>@enderror</div>
            @endif
            <div><label class="form-label">Tahun Buku <span class="text-red-500">*</span></label><input type="number" name="year" class="form-control" min="2000" max="2100" value="{{ old('year',$period->year) }}" required>@error('year')<p class="form-error">{{ $message }}</p>@enderror</div>
            <div><label class="form-label">Tanggal Mulai <span class="text-red-500">*</span></label><input type="date" name="start_date" class="form-control" value="{{ old('start_date', optional($period->start_date)->format('Y-m-d')) }}" required></div>
            <div><label class="form-label">Tanggal Akhir <span class="text-red-500">*</span></label><input type="date" name="end_date" class="form-control" value="{{ old('end_date', optional($period->end_date)->format('Y-m-d')) }}" required>@error('end_date')<p class="form-error">{{ $message }}</p>@enderror</div>
        </div>

        <div class="mt-6 border-t border-slate-200 pt-5 dark:border-slate-800">
            <div class="mb-3"><div class="font-bold text-slate-900 dark:text-white">Basis Jasa Modal</div><p class="text-sm text-slate-500 dark:text-slate-400">Default Pokok + Wajib. Centang Manasuka/Sukarela bila keputusan RAT memasukkannya sebagai modal partisipasi.</p></div>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($savingTypes as $type)
                    <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-4 dark:border-slate-700"><input type="checkbox" name="saving_type_ids[]" value="{{ $type->id }}" class="mt-1 h-4 w-4" @checked(in_array($type->id, old('saving_type_ids',$selectedSavingTypeIds)))><span><strong>{{ $type->code }} - {{ $type->name }}</strong><span class="mt-1 block text-xs text-slate-500">{{ $type->is_mandatory ? 'Wajib' : 'Tidak wajib' }} · {{ $type->is_withdrawable ? 'Dapat ditarik' : 'Tidak dapat ditarik' }}</span></span></label>
                @endforeach
            </div>
            @error('saving_type_ids')<p class="form-error mt-2">{{ $message }}</p>@enderror
        </div>

        <div class="mt-6 grid grid-cols-1 gap-5 md:grid-cols-2">
            <div><label class="form-label">Jasa Modal (%)</label><input type="number" step="0.0001" min="0" max="100" name="capital_share_percentage" class="form-control" value="{{ old('capital_share_percentage',$period->capital_share_percentage) }}" required></div>
            <div><label class="form-label">Jasa Usaha (%)</label><input type="number" step="0.0001" min="0" max="100" name="business_share_percentage" class="form-control" value="{{ old('business_share_percentage',$period->business_share_percentage) }}" required></div>
        </div>
        @error('member_share')<p class="form-error mt-2">{{ $message }}</p>@enderror

        <div class="mt-6 border-t border-slate-200 pt-5 dark:border-slate-800">
            <div class="mb-3 flex items-center justify-between gap-3"><div><div class="font-bold">Alokasi SHU</div><p class="text-sm text-slate-500">Total harus 100%. Tandai tepat satu baris sebagai Bagian Anggota.</p></div><button type="button" id="add-allocation" class="btn btn-secondary">Tambah Alokasi</button></div>
            <div id="allocation-rows" class="space-y-3">
                @foreach(old('allocations',$allocations->toArray()) as $i => $allocation)
                    <div class="allocation-row grid grid-cols-1 gap-3 rounded-xl border border-slate-200 p-4 md:grid-cols-[1fr_150px_1fr_auto] dark:border-slate-700">
                        <div><label class="form-label">Nama Alokasi</label><input type="text" name="allocations[{{ $i }}][name]" class="form-control allocation-name" value="{{ $allocation['name'] ?? '' }}" required></div>
                        <div><label class="form-label">Persentase</label><input type="number" step="0.0001" min="0" max="100" name="allocations[{{ $i }}][percentage]" class="form-control allocation-percent" value="{{ $allocation['percentage'] ?? 0 }}" required></div>
                        <div><label class="form-label">Akun Tujuan Jurnal</label><select name="allocations[{{ $i }}][account_id]" class="form-select allocation-account"><option value="">-- Belum dipetakan --</option>@foreach($postableAccounts as $account)<option value="{{ $account->id }}" @selected((string)($allocation['account_id'] ?? '')===(string)$account->id)>{{ $account->code }} - {{ $account->name }}</option>@endforeach</select></div>
                        <div class="flex items-end gap-2"><label class="mb-2 flex items-center gap-2 whitespace-nowrap text-sm"><input type="radio" name="member_allocation_index" class="member-radio" value="{{ $i }}" @checked((string)old('member_allocation_index', collect($allocations)->search(fn($a)=>$a['is_member_pool'] ?? false)) === (string)$i)> Anggota</label><button type="button" class="btn btn-secondary remove-allocation">×</button></div>
                    </div>
                @endforeach
            </div>
            @error('allocations')<p class="form-error mt-2">{{ $message }}</p>@enderror
            @error('member_allocation_index')<p class="form-error mt-2">{{ $message }}</p>@enderror
        </div>

        <div class="mt-6 rounded-xl border border-slate-200 p-5 dark:border-slate-700">
            <label class="flex items-start gap-3"><input type="checkbox" name="post_journal" id="post_journal" value="1" class="mt-1 h-4 w-4" @checked(old('post_journal',$period->post_journal))><span><strong>Posting jurnal otomatis saat Finalisasi</strong><span class="mt-1 block text-sm text-slate-500">Opsional. Jika aktif, akun sumber dan akun tujuan seluruh alokasi wajib dipetakan.</span></span></label>
            <div class="mt-4"><label class="form-label">Akun Sumber SHU (Ekuitas)</label><select name="source_equity_account_id" class="form-select"><option value="">-- Pilih akun ekuitas --</option>@foreach($equityAccounts as $account)<option value="{{ $account->id }}" @selected((string)old('source_equity_account_id',$period->source_equity_account_id)===(string)$account->id)>{{ $account->code }} - {{ $account->name }}</option>@endforeach</select>@error('source_equity_account_id')<p class="form-error">{{ $message }}</p>@enderror</div>
        </div>

        <div class="mt-6 flex justify-end gap-2 border-t border-slate-200 pt-5 dark:border-slate-800"><a href="{{ $cancelUrl }}" class="btn btn-secondary">Batal</a><button class="btn btn-primary">Simpan Parameter</button></div>
    </form>
</x-card>

@push('scripts')
<script>
(() => {
    const container = document.getElementById('allocation-rows');
    const addButton = document.getElementById('add-allocation');
    if (!container || !addButton) return;
    const accountOptions = @json($postableAccounts->map(fn($a) => ['id'=>$a->id,'label'=>$a->code.' - '.$a->name])->values());

    function reindex() {
        [...container.querySelectorAll('.allocation-row')].forEach((row, i) => {
            row.querySelector('.allocation-name').name = `allocations[${i}][name]`;
            row.querySelector('.allocation-percent').name = `allocations[${i}][percentage]`;
            row.querySelector('.allocation-account').name = `allocations[${i}][account_id]`;
            row.querySelector('.member-radio').value = i;
        });
    }
    addButton.addEventListener('click', () => {
        const i = container.querySelectorAll('.allocation-row').length;
        const options = accountOptions.map(a => `<option value="${a.id}">${a.label}</option>`).join('');
        const row = document.createElement('div');
        row.className = 'allocation-row grid grid-cols-1 gap-3 rounded-xl border border-slate-200 p-4 md:grid-cols-[1fr_150px_1fr_auto] dark:border-slate-700';
        row.innerHTML = `<div><label class="form-label">Nama Alokasi</label><input type="text" name="allocations[${i}][name]" class="form-control allocation-name" required></div><div><label class="form-label">Persentase</label><input type="number" step="0.0001" min="0" max="100" name="allocations[${i}][percentage]" class="form-control allocation-percent" value="0" required></div><div><label class="form-label">Akun Tujuan Jurnal</label><select name="allocations[${i}][account_id]" class="form-select allocation-account"><option value="">-- Belum dipetakan --</option>${options}</select></div><div class="flex items-end gap-2"><label class="mb-2 flex items-center gap-2 whitespace-nowrap text-sm"><input type="radio" name="member_allocation_index" class="member-radio" value="${i}"> Anggota</label><button type="button" class="btn btn-secondary remove-allocation">×</button></div>`;
        container.appendChild(row);
    });
    container.addEventListener('click', e => {
        if (!e.target.classList.contains('remove-allocation')) return;
        if (container.querySelectorAll('.allocation-row').length <= 1) return;
        e.target.closest('.allocation-row').remove();
        reindex();
    });
})();
</script>
@endpush
