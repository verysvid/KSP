<?php
namespace App\Services;
use App\Models\LoanInstallment;
use Carbon\Carbon;
class LoanPenaltyService {
    public function calculateForInstallment(LoanInstallment $installment, string|\DateTimeInterface|null $asOfDate=null): array {
        $installment->loadMissing('loan.loanType');
        $asOf=$asOfDate?Carbon::parse($asOfDate)->startOfDay():now()->startOfDay();
        $dueDate=Carbon::parse($installment->due_date)->startOfDay();
        $isPaid=$installment->status==='PAID';
        $isOverdue=!$isPaid && $asOf->gt($dueDate);
        $daysOverdue=$isOverdue?$dueDate->diffInDays($asOf):0;
        $loanType=$installment->loan?->loanType;
        $penalty=0.0;
        if($isOverdue && $loanType){
            if($loanType->penalty_type==='FIXED') $penalty=round((float)($loanType->penalty_amount??0),2);
            elseif($loanType->penalty_type==='PERCENTAGE'){
                $base=round((float)$installment->principal_amount+(float)$installment->interest_amount,2);
                $penalty=round($base*((float)($loanType->penalty_rate??0)/100),2);
            } else $penalty=0;
        }
        return ['is_overdue'=>$isOverdue,'days_overdue'=>$daysOverdue,'penalty_amount'=>$penalty];
    }
    public function refreshInstallment(LoanInstallment $installment, string|\DateTimeInterface|null $asOfDate=null): LoanInstallment {
        $r=$this->calculateForInstallment($installment,$asOfDate);
        $installment->update(['is_overdue'=>$r['is_overdue'],'days_overdue'=>$r['days_overdue'],'penalty_amount'=>$r['penalty_amount'],'overdue_calculated_at'=>now()]);
        return $installment->fresh();
    }
}
