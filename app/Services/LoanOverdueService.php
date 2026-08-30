<?php
namespace App\Services;
use App\Models\LoanInstallment;
class LoanOverdueService {
    public function __construct(protected LoanPenaltyService $loanPenaltyService) {}
    public function refreshOpenInstallments(): int {
        $count=0;
        LoanInstallment::query()->with('loan.loanType')->where('status','!=','PAID')->orderBy('id')->chunkById(200,function($items) use (&$count){
            foreach($items as $item){$this->loanPenaltyService->refreshInstallment($item);$count++;}
        });
        return $count;
    }
}
