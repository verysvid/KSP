<?php

namespace App\Services;

use App\Models\LoanInstallment;

class LoanOverdueService
{
    public function __construct(
        protected LoanPenaltyService $loanPenaltyService,
        protected BranchContext $branchContext
    ) {}

    public function refreshOpenInstallments(): int
    {
        $count = 0;

        $query = LoanInstallment::query()
            ->with('loan.loanType')
            ->where('status', '!=', 'PAID');

        $branchId = $this->branchContext->getCurrentBranchId();

        if ($branchId !== null) {
            $query->whereHas('loan', function ($loanQuery) use ($branchId) {
                $loanQuery->where('branch_id', $branchId);
            });
        }

        $query
            ->orderBy('id')
            ->chunkById(200, function ($items) use (&$count) {
                foreach ($items as $item) {
                    $this->loanPenaltyService->refreshInstallment($item);
                    $count++;
                }
            });

        return $count;
    }
}