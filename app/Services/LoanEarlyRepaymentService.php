<?php

namespace App\Services;

use App\Models\Account;
use App\Models\ApplicationSetting;
use App\Models\Loan;
use App\Models\LoanEarlyRepayment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class LoanEarlyRepaymentService
{
    public function __construct(
        protected LoanEarlyRepaymentGuardService $guardService,
        protected JournalService $journalService,
        protected AuditLogService $auditLogService,
    ) {
    }

    public function buildSnapshot(Loan $loan): array
    {
        $loan->loadMissing(['installments', 'payments']);

        $firstInstallment = $loan->installments
            ->sortBy(fn ($item) => $item->due_date?->format('Y-m-d'))
            ->first();

        $lastInstallment = $loan->installments
            ->sortByDesc(fn ($item) => $item->due_date?->format('Y-m-d'))
            ->first();

        $originalPrincipal = (float) ($loan->total_principal ?: $loan->principal_amount);
        $outstandingPrincipal = round((float) $loan->outstanding_principal, 2);
        $outstandingInterest = round((float) $loan->outstanding_interest, 2);
        $principalPaid = max(0, round($originalPrincipal - $outstandingPrincipal, 2));
        $interestPaid = max(0, round((float) $loan->total_interest - $outstandingInterest, 2));

        return [
            'loan_no' => $loan->loan_no,
            'principal_amount' => round((float) $loan->principal_amount, 2),
            'tenor_months' => (int) $loan->tenor_months,
            'installment_start_date' => $firstInstallment?->due_date,
            'installment_end_date' => $lastInstallment?->due_date,
            'principal_paid_to_date' => $principalPaid,
            'interest_paid_to_date' => $interestPaid,
            'outstanding_principal' => $outstandingPrincipal,
            'outstanding_interest' => $outstandingInterest,
            'total_repayment' => $outstandingPrincipal,
        ];
    }

    public function buildNotes(Loan $loan): string
    {
        return sprintf(
            'Pelunasan Dipercepat dengan No Pinjaman %s dan Sisa Pokok sebesar Rp %s. Bukti pelunasan terlampir',
            $loan->loan_no,
            number_format((float) $loan->outstanding_principal, 0, ',', '.')
        );
    }

    public function settingsForRepayment(): array
    {
        $setting = ApplicationSetting::current();

        if (
            blank($setting->bank_name)
            || blank($setting->account_no)
            || !$setting->bank_account_id
        ) {
            throw ValidationException::withMessages([
                'bank_setting' => 'Rekening Pelunasan Dini belum lengkap. Isi Nama Bank, Nomor Rekening, dan Akun Akuntansi pada Pengaturan Aplikasi.',
            ]);
        }

        $bankAccount = Account::query()
            ->whereKey($setting->bank_account_id)
            ->where('type', Account::TYPE_ASSET)
            ->where('is_cash_bank', true)
            ->where('is_postable', true)
            ->where('is_active', true)
            ->first();

        if (!$bankAccount) {
            throw ValidationException::withMessages([
                'bank_setting' => 'Akun Akuntansi untuk rekening Pelunasan Dini tidak valid, tidak aktif, atau bukan akun Kas/Bank postable.',
            ]);
        }

        return [
            'setting' => $setting,
            'bank_account' => $bankAccount,
        ];
    }

    public function submit(Loan $loan, UploadedFile $paymentProof, int $userId): LoanEarlyRepayment
    {
        $proofPath = null;

        try {
            return DB::transaction(function () use ($loan, $paymentProof, $userId, &$proofPath) {
                $lockedLoan = Loan::query()
                    ->lockForUpdate()
                    ->findOrFail($loan->id);

                $this->guardService->ensureEligible($lockedLoan);
                $lockedLoan->loadMissing(['installments', 'payments']);

                ['setting' => $setting, 'bank_account' => $bankAccount] = $this->settingsForRepayment();
                $snapshot = $this->buildSnapshot($lockedLoan);

                if ($snapshot['total_repayment'] <= 0) {
                    throw ValidationException::withMessages([
                        'early_repayment' => 'Sisa pokok pinjaman sudah nol.',
                    ]);
                }

                $proofPath = $paymentProof->store(
                    'loan-early-repayments/' . $lockedLoan->branch_id . '/' . now()->format('Ym'),
                    'local'
                );

                $repayment = LoanEarlyRepayment::create([
                    'loan_id' => $lockedLoan->id,
                    'branch_id' => $lockedLoan->branch_id,
                    'repayment_no' => $this->generateRepaymentNo((int) $lockedLoan->branch_id),
                    'request_date' => now()->toDateString(),
                    'outstanding_principal' => $snapshot['outstanding_principal'],
                    'outstanding_interest' => $snapshot['outstanding_interest'],
                    'principal_paid_to_date' => $snapshot['principal_paid_to_date'],
                    'interest_paid_to_date' => $snapshot['interest_paid_to_date'],
                    'total_repayment' => $snapshot['total_repayment'],
                    'installment_start_date' => $snapshot['installment_start_date']?->toDateString(),
                    'installment_end_date' => $snapshot['installment_end_date']?->toDateString(),
                    'bank_name' => $setting->bank_name,
                    'account_no' => $setting->account_no,
                    'bank_account_id' => $bankAccount->id,
                    'payment_proof_path' => $proofPath,
                    'notes' => $this->buildNotes($lockedLoan),
                    'status' => LoanEarlyRepayment::STATUS_SUBMITTED,
                    'submitted_by' => $userId,
                    'submitted_at' => now(),
                ]);

                $oldLoan = $lockedLoan->toArray();

                $lockedLoan->update([
                    'is_early_repayment' => true,
                    'updated_by' => $userId,
                ]);

                $this->auditLogService->log(
                    'CREATE',
                    $repayment,
                    "Mengajukan Pelunasan Dini {$repayment->repayment_no} untuk pinjaman {$lockedLoan->loan_no}",
                    [],
                    $repayment->fresh()->toArray()
                );

                $this->auditLogService->log(
                    'UPDATE',
                    $lockedLoan,
                    "Mengunci pembayaran pinjaman {$lockedLoan->loan_no} selama verifikasi Pelunasan Dini",
                    $oldLoan,
                    $lockedLoan->fresh()->toArray()
                );

                return $repayment->fresh([
                    'loan',
                    'bankAccount',
                    'submitter',
                ]);
            }, 3);
        } catch (Throwable $e) {
            if ($proofPath) {
                Storage::disk('local')->delete($proofPath);
            }

            throw $e;
        }
    }

    public function approve(LoanEarlyRepayment $earlyRepayment, int $userId): LoanEarlyRepayment
    {
        return DB::transaction(function () use ($earlyRepayment, $userId) {
            $repayment = LoanEarlyRepayment::withoutGlobalScopes()
                ->lockForUpdate()
                ->findOrFail($earlyRepayment->id);

            if ($repayment->status !== LoanEarlyRepayment::STATUS_SUBMITTED) {
                throw ValidationException::withMessages([
                    'early_repayment' => 'Hanya Pengajuan Pelunasan Dini berstatus SUBMITTED yang dapat di-approve.',
                ]);
            }

            $loan = Loan::query()
                ->lockForUpdate()
                ->findOrFail($repayment->loan_id);

            if (
                $loan->status !== Loan::STATUS_ACTIVE
                || !(bool) $loan->is_early_repayment
            ) {
                throw ValidationException::withMessages([
                    'loan' => 'Pinjaman sudah tidak dalam kondisi ACTIVE dengan proses Pelunasan Dini.',
                ]);
            }

            $currentOutstanding = round((float) $loan->outstanding_principal, 2);
            $submittedOutstanding = round((float) $repayment->outstanding_principal, 2);

            if (abs($currentOutstanding - $submittedOutstanding) > 0.01) {
                throw ValidationException::withMessages([
                    'loan' => 'Sisa pokok pinjaman berubah sejak Pelunasan Dini diajukan. Reject pengajuan ini dan ajukan ulang agar nominal sesuai saldo terbaru.',
                ]);
            }

            if ($currentOutstanding <= 0) {
                throw ValidationException::withMessages([
                    'loan' => 'Sisa pokok pinjaman sudah nol.',
                ]);
            }

            $loan->loadMissing('loanType');

            if (!$loan->loanType?->receivable_account_id) {
                throw ValidationException::withMessages([
                    'loan' => 'Akun piutang belum dipetakan pada Jenis Pinjaman.',
                ]);
            }

            $bankAccount = Account::query()
                ->whereKey($repayment->bank_account_id)
                ->where('type', Account::TYPE_ASSET)
                ->where('is_cash_bank', true)
                ->where('is_postable', true)
                ->where('is_active', true)
                ->first();

            if (!$bankAccount) {
                throw ValidationException::withMessages([
                    'bank_account' => 'Akun bank tujuan Pelunasan Dini tidak valid atau tidak aktif.',
                ]);
            }

            $oldRepayment = $repayment->toArray();
            $oldLoan = $loan->toArray();
            $approvalTime = now();

            $journal = $this->journalService->create(
                branchId: (int) $loan->branch_id,
                journalDate: $repayment->request_date->toDateString(),
                description: "Pelunasan Dini {$repayment->repayment_no} - {$loan->loan_no}",
                referenceType: LoanEarlyRepayment::class,
                referenceId: $repayment->id,
                lines: [
                    [
                        'account_id' => $bankAccount->id,
                        'debit' => $currentOutstanding,
                        'credit' => 0,
                        'description' => "Penerimaan Pelunasan Dini {$loan->loan_no}",
                    ],
                    [
                        'account_id' => $loan->loanType->receivable_account_id,
                        'debit' => 0,
                        'credit' => $currentOutstanding,
                        'description' => "Pelunasan sisa pokok {$loan->loan_no}",
                    ],
                ],
                createdBy: $userId
            );

            $loan->installments()
                ->where('status', '!=', 'PAID')
                ->update([
                    'early_repayment_id' => $repayment->id,
                    'principal_paid' => DB::raw('principal_amount'),
                    'interest_paid' => 0,
                    'penalty_amount' => 0,
                    'penalty_paid' => 0,
                    'status' => 'PAID',
                    'is_overdue' => false,
                    'days_overdue' => 0,
                    'overdue_calculated_at' => $approvalTime,
                    'paid_at' => $approvalTime,
                    'updated_at' => $approvalTime,
                ]);

            $repayment->update([
                'status' => LoanEarlyRepayment::STATUS_APPROVED,
                'approved_by' => $userId,
                'approved_at' => $approvalTime,
                'rejected_by' => null,
                'rejected_at' => null,
                'rejection_reason' => null,
                'journal_entry_id' => $journal->id,
            ]);

            $loan->update([
                'status' => Loan::STATUS_PAID_OFF,
                'outstanding_principal' => 0,
                'outstanding_interest' => 0,
                'is_early_repayment' => false,
                'updated_by' => $userId,
            ]);

            $this->auditLogService->log(
                'APPROVE',
                $repayment,
                "Approve Pelunasan Dini {$repayment->repayment_no} untuk pinjaman {$loan->loan_no}",
                $oldRepayment,
                $repayment->fresh()->toArray()
            );

            $this->auditLogService->log(
                'UPDATE',
                $loan,
                "Melunasi pinjaman {$loan->loan_no} melalui Pelunasan Dini {$repayment->repayment_no}",
                $oldLoan,
                $loan->fresh()->toArray()
            );

            return $repayment->fresh([
                'loan',
                'bankAccount',
                'submitter',
                'approver',
                'journalEntry',
            ]);
        }, 3);
    }

    public function reject(LoanEarlyRepayment $earlyRepayment, string $reason, int $userId): LoanEarlyRepayment
    {
        return DB::transaction(function () use ($earlyRepayment, $reason, $userId) {
            $repayment = LoanEarlyRepayment::withoutGlobalScopes()
                ->lockForUpdate()
                ->findOrFail($earlyRepayment->id);

            if ($repayment->status !== LoanEarlyRepayment::STATUS_SUBMITTED) {
                throw ValidationException::withMessages([
                    'early_repayment' => 'Hanya Pengajuan Pelunasan Dini berstatus SUBMITTED yang dapat ditolak.',
                ]);
            }

            $loan = Loan::query()
                ->lockForUpdate()
                ->findOrFail($repayment->loan_id);

            $oldRepayment = $repayment->toArray();
            $oldLoan = $loan->toArray();

            $repayment->update([
                'status' => LoanEarlyRepayment::STATUS_REJECTED,
                'rejected_by' => $userId,
                'rejected_at' => now(),
                'rejection_reason' => $reason,
                'approved_by' => null,
                'approved_at' => null,
            ]);

            if ($loan->status === Loan::STATUS_ACTIVE) {
                $loan->update([
                    'is_early_repayment' => false,
                    'updated_by' => $userId,
                ]);
            }

            $this->auditLogService->log(
                'REJECT',
                $repayment,
                "Menolak Pelunasan Dini {$repayment->repayment_no} untuk pinjaman {$loan->loan_no}",
                $oldRepayment,
                $repayment->fresh()->toArray()
            );

            $this->auditLogService->log(
                'UPDATE',
                $loan,
                "Membuka kembali transaksi pinjaman {$loan->loan_no} setelah Pelunasan Dini ditolak",
                $oldLoan,
                $loan->fresh()->toArray()
            );

            return $repayment->fresh([
                'loan',
                'submitter',
                'rejecter',
            ]);
        }, 3);
    }

    protected function generateRepaymentNo(int $branchId): string
    {
        $prefix = 'ER-' . now()->format('Ym') . '-' . sprintf('%02d', $branchId);

        $last = LoanEarlyRepayment::withoutGlobalScopes()
            ->where('branch_id', $branchId)
            ->where('repayment_no', 'like', $prefix . '-%')
            ->lockForUpdate()
            ->latest('id')
            ->first();

        $sequence = 0;

        if ($last && preg_match('/-(\d{6})$/', $last->repayment_no, $matches)) {
            $sequence = (int) $matches[1];
        }

        return sprintf('%s-%06d', $prefix, $sequence + 1);
    }
}
