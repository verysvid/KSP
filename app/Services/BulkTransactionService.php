<?php

namespace App\Services;

use App\Models\Account;
use App\Models\BulkTransaction;
use App\Models\BulkTransactionItem;
use App\Models\BulkTransactionMember;
use App\Models\Loan;
use App\Models\Member;
use App\Models\SavingType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BulkTransactionService
{
    public function __construct(
        protected BulkTransactionPreviewService $previewService,
        protected SavingService $savingService,
        protected SavingJournalService $savingJournalService,
        protected LoanPaymentService $loanPaymentService,
        protected AuditLogService $auditLogService,
    ) {
    }

    public function process(
        int $branchId,
        int $month,
        int $year,
        string $transactionDate,
        array $memberIds,
        int $userId
    ): BulkTransaction {
        $date = Carbon::parse($transactionDate)->startOfDay();
        $period = sprintf('%04d-%02d', $year, $month);

        if ($date->format('Y-m') !== $period) {
            throw ValidationException::withMessages([
                'transaction_date' => 'Tanggal transaksi wajib berada dalam periode yang dipilih.',
            ]);
        }

        $memberIds = collect($memberIds)->map(fn ($id) => (int) $id)->unique()->values();

        return DB::transaction(function () use ($branchId, $month, $year, $date, $period, $memberIds, $userId) {
            $bankAccount = Account::query()
                ->where('code', '1102')
                ->where('type', Account::TYPE_ASSET)
                ->where('is_cash_bank', true)
                ->where('is_postable', true)
                ->where('is_active', true)
                ->first();

            if (!$bankAccount) {
                throw ValidationException::withMessages([
                    'account' => 'Akun 1102 Bank tidak ditemukan atau belum valid sebagai akun Bank aktif dan postable.',
                ]);
            }

            $members = Member::withoutGlobalScopes()
                ->where('branch_id', $branchId)
                ->where('member_status', 'ACTIVE')
                ->whereIn('id', $memberIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($members->count() !== $memberIds->count()) {
                throw ValidationException::withMessages([
                    'member_ids' => 'Terdapat anggota yang tidak aktif atau bukan anggota cabang terpilih.',
                ]);
            }

            $preview = $this->previewService->generate($branchId, $month, $year);
            $rows = $preview['groups']->flatMap(fn (array $group) => $group['rows'])->keyBy('member_id');

            foreach ($memberIds as $memberId) {
                $row = $rows->get($memberId);
                if (!$row || !$row['selectable']) {
                    throw ValidationException::withMessages([
                        'member_ids' => "Anggota {$members[$memberId]->name} sudah diproses atau tidak dapat diproses.",
                    ]);
                }
            }

            $batch = BulkTransaction::create([
                'branch_id' => $branchId,
                'batch_no' => $this->generateBatchNo($branchId, $period),
                'period' => $period,
                'transaction_date' => $date->toDateString(),
                'status' => BulkTransaction::STATUS_PROCESSING,
                'processed_by' => $userId,
            ]);

            $totals = [
                'saving' => 0.0, 'principal' => 0.0,
                'interest' => 0.0, 'penalty' => 0.0, 'grand' => 0.0,
            ];

            $savingTypes = SavingType::query()
                ->whereIn('code', ['POKOK', 'WAJIB', 'MANASUKA', 'SUKARELA'])
                ->get()
                ->keyBy(fn (SavingType $type) => strtoupper($type->code));

            foreach ($memberIds as $memberId) {
                $member = $members[$memberId];
                $row = $rows[$memberId];
                $bulkMember = BulkTransactionMember::create([
                    'bulk_transaction_id' => $batch->id,
                    'branch_id' => $branchId,
                    'member_id' => $memberId,
                    'period' => $period,
                    'saving_principal' => $row['saving_principal'],
                    'saving_mandatory' => $row['saving_mandatory'],
                    'saving_voluntary' => $row['saving_voluntary'],
                    'money_principal' => $row['money_principal'],
                    'money_interest' => $row['money_interest'],
                    'goods_principal' => $row['goods_principal'],
                    'goods_interest' => $row['goods_interest'],
                    'penalty_total' => 0,
                    'grand_total' => $row['all_total'],
                ]);

                $savingMap = [
                    'POKOK' => ['amount' => (float) $row['saving_principal'], 'type' => $savingTypes->get('POKOK')],
                    'WAJIB' => ['amount' => (float) $row['saving_mandatory'], 'type' => $savingTypes->get('WAJIB')],
                    'MANASUKA' => [
                        'amount' => (float) $row['saving_voluntary'],
                        'type' => $savingTypes->get('MANASUKA') ?? $savingTypes->get('SUKARELA'),
                    ],
                ];

                foreach ($savingMap as $category => $savingData) {
                    if ($savingData['amount'] <= 0) continue;
                    if (!$savingData['type']) {
                        throw ValidationException::withMessages([
                            'saving_type' => "Jenis Simpanan {$category} tidak ditemukan.",
                        ]);
                    }

                    $saving = $this->savingService->createTransaction([
                        'member_id' => $memberId,
                        'saving_type_id' => $savingData['type']->id,
                        'transaction_date' => $date->toDateString(),
                        'transaction_type' => 'SETORAN',
                        'amount' => $savingData['amount'],
                        'remarks' => "Transaksi Bulk {$batch->batch_no}",
                    ]);
                    $this->savingService->approve($saving);
                    $this->savingJournalService->post($saving, $bankAccount->id, $userId);

                    BulkTransactionItem::create([
                        'bulk_transaction_id' => $batch->id,
                        'bulk_transaction_member_id' => $bulkMember->id,
                        'member_id' => $memberId,
                        'item_type' => BulkTransactionItem::TYPE_SAVING,
                        'category' => $category,
                        'saving_transaction_id' => $saving->id,
                        'principal_amount' => $savingData['amount'],
                        'total_amount' => $savingData['amount'],
                    ]);
                    $totals['saving'] += $savingData['amount'];
                    $totals['grand'] += $savingData['amount'];
                }

                $memberPenalty = 0.0;
                $loans = Loan::withoutGlobalScopes()
                    ->with('loanType')
                    ->where('branch_id', $branchId)
                    ->where('member_id', $memberId)
                    ->where('status', Loan::STATUS_ACTIVE)
                    ->whereHas('loanType', fn ($query) => $query->whereIn('code', ['PU', 'PB']))
                    ->lockForUpdate()
                    ->get();

                foreach ($loans as $loan) {
                    $installment = $loan->installments()
                        ->where('status', '!=', 'PAID')
                        ->whereDate('due_date', '<=', $date->copy()->endOfMonth()->toDateString())
                        ->orderBy('due_date')
                        ->orderBy('installment_no')
                        ->lockForUpdate()
                        ->first();

                    if (!$installment) continue;

                    $payment = $this->loanPaymentService->payInstallment(
                        $loan,
                        $installment,
                        [
                            'payment_date' => $date->toDateString(),
                            'cash_account_id' => $bankAccount->id,
                            'reference_no' => $batch->batch_no,
                            'notes' => "Pembayaran melalui Transaksi Bulk {$batch->batch_no}",
                        ],
                        $userId
                    );

                    $category = strtoupper((string) $loan->loanType?->code) === 'PB' ? 'PB' : 'PU';
                    BulkTransactionItem::create([
                        'bulk_transaction_id' => $batch->id,
                        'bulk_transaction_member_id' => $bulkMember->id,
                        'member_id' => $memberId,
                        'item_type' => BulkTransactionItem::TYPE_LOAN_PAYMENT,
                        'category' => $category,
                        'loan_payment_id' => $payment->id,
                        'principal_amount' => $payment->principal_amount,
                        'interest_amount' => $payment->interest_amount,
                        'penalty_amount' => $payment->penalty_amount,
                        'total_amount' => $payment->total_amount,
                    ]);

                    $totals['principal'] += (float) $payment->principal_amount;
                    $totals['interest'] += (float) $payment->interest_amount;
                    $totals['penalty'] += (float) $payment->penalty_amount;
                    $totals['grand'] += (float) $payment->total_amount;
                    $memberPenalty += (float) $payment->penalty_amount;
                }

                $bulkMember->update([
                    'penalty_total' => $memberPenalty,
                    'grand_total' => (float) $row['all_total'] + $memberPenalty,
                ]);
            }

            $batch->update([
                'status' => BulkTransaction::STATUS_COMPLETED,
                'member_count' => $memberIds->count(),
                'saving_total' => $totals['saving'],
                'loan_principal_total' => $totals['principal'],
                'loan_interest_total' => $totals['interest'],
                'penalty_total' => $totals['penalty'],
                'grand_total' => $totals['grand'],
            ]);

            $this->auditLogService->log(
                'CREATE', $batch, "Memproses Transaksi Bulk {$batch->batch_no}", [], $batch->fresh()->toArray()
            );

            return $batch->fresh();
        }, 3);
    }

    private function generateBatchNo(int $branchId, string $period): string
    {
        $prefix = 'BULK-' . str_replace('-', '', $period) . '-' . sprintf('%02d', $branchId);
        $last = BulkTransaction::withoutGlobalScopes()
            ->where('batch_no', 'like', $prefix . '-%')
            ->lockForUpdate()
            ->latest('id')
            ->first();
        $sequence = 1;
        if ($last && preg_match('/-(\d{6})$/', $last->batch_no, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }
        return sprintf('%s-%06d', $prefix, $sequence);
    }
}
