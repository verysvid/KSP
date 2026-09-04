<x-app-layout>
    <x-slot name="title">Detail Transaksi Bulk</x-slot>
    <x-page-header title="Detail Transaksi Bulk" description="{{ $bulkTransaction->batch_no }}">
        <x-slot name="actions"><a href="{{ route('bulk-transactions.index') }}" class="btn btn-secondary">Kembali</a></x-slot>
    </x-page-header>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <x-card><div class="text-sm text-slate-500">Cabang</div><div class="mt-1 font-bold">{{ $bulkTransaction->branch?->code }} - {{ $bulkTransaction->branch?->name }}</div></x-card>
        <x-card><div class="text-sm text-slate-500">Periode / Tanggal</div><div class="mt-1 font-bold">{{ $bulkTransaction->period }} · {{ $bulkTransaction->transaction_date?->format('d/m/Y') }}</div></x-card>
        <x-card><div class="text-sm text-slate-500">Jumlah Anggota</div><div class="mt-1 font-bold">{{ $bulkTransaction->member_count }}</div></x-card>
        <x-card><div class="text-sm text-slate-500">Total Transaksi</div><div class="mt-1 font-bold text-indigo-600">Rp {{ number_format((float)$bulkTransaction->grand_total,0,',','.') }}</div></x-card>
    </div>

    <div class="mt-6"><x-card title="Ringkasan" description="Rincian total transaksi dan penjurnalan batch.">
        <div class="grid grid-cols-2 gap-4 md:grid-cols-5">
            @foreach([
                'Simpanan'=>$bulkTransaction->saving_total,
                'Pokok Pinjaman'=>$bulkTransaction->loan_principal_total,
                'Bunga'=>$bulkTransaction->loan_interest_total,
                'Denda'=>$bulkTransaction->penalty_total,
                'Total'=>$bulkTransaction->grand_total,
            ] as $label=>$amount)
                <div><div class="text-sm text-slate-500">{{ $label }}</div><div class="mt-1 font-bold">Rp {{ number_format((float)$amount,0,',','.') }}</div></div>
            @endforeach
        </div>
        <div class="mt-4 text-sm text-slate-500">Diproses oleh {{ $bulkTransaction->processor?->name ?? '-' }} pada {{ $bulkTransaction->created_at?->format('d/m/Y H:i') }}</div>
    </x-card></div>

    <div class="mt-6"><x-card title="Detail Anggota" description="Transaksi simpanan dan pembayaran yang terbentuk.">
        <div class="space-y-4">
            @foreach($bulkTransaction->members as $bulkMember)
                <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                    <div class="flex flex-col justify-between gap-2 sm:flex-row">
                        <div><div class="font-bold">{{ $bulkMember->member?->name }}</div><div class="text-sm text-slate-500">{{ $bulkMember->member?->member_number }}</div></div>
                        <div class="font-bold">Rp {{ number_format((float)$bulkMember->grand_total,0,',','.') }}</div>
                    </div>
                    <div class="table-wrapper mt-3"><table class="data-table"><thead><tr><th>Jenis</th><th>Kategori</th><th>No. Transaksi</th><th class="text-right">Pokok/Nominal</th><th class="text-right">Bunga</th><th class="text-right">Denda</th><th class="text-right">Total</th></tr></thead><tbody>
                    @foreach($bulkMember->items as $item)
                        <tr><td>{{ $item->item_type === 'SAVING' ? 'Simpanan' : 'Pembayaran Pinjaman' }}</td><td>{{ $item->category }}</td>
                            <td>
                                @if($item->savingTransaction)<a class="font-semibold text-indigo-600" href="{{ route('saving-transactions.show',$item->savingTransaction) }}">{{ $item->savingTransaction->trx_no }}</a>
                                @elseif($item->loanPayment){{ $item->loanPayment->payment_no }}@else - @endif
                            </td>
                            <td class="text-right">{{ number_format((float)$item->principal_amount,0,',','.') }}</td><td class="text-right">{{ number_format((float)$item->interest_amount,0,',','.') }}</td><td class="text-right">{{ number_format((float)$item->penalty_amount,0,',','.') }}</td><td class="text-right font-semibold">{{ number_format((float)$item->total_amount,0,',','.') }}</td></tr>
                    @endforeach
                    </tbody></table></div>
                </div>
            @endforeach
        </div>
    </x-card></div>
</x-app-layout>
