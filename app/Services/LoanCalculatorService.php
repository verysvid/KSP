<?php

namespace App\Services;

use App\Models\Loan;
use Carbon\Carbon;

class LoanCalculatorService
{
    public function buildSchedule(
        Loan $loan,
        string $disbursementDate
    ): array {
        $principal = round((float) $loan->principal_amount, 2);
        $rate = (float) $loan->interest_rate / 100;
        $tenor = (int) $loan->tenor_months;

        $start = Carbon::parse($disbursementDate)
            ->addMonthNoOverflow()
            ->day((int) $loan->due_day);

        $schedule = [];
        $opening = $principal;

        $basePrincipal = round($principal / $tenor, 2);

        for ($i = 1; $i <= $tenor; $i++) {
            $principalPart = $i === $tenor
                ? round($opening, 2)
                : $basePrincipal;

            if ($loan->interest_type === Loan::INTEREST_FLAT) {
                $interestPart = round($principal * $rate, 2);
            } else {
                $interestPart = round($opening * $rate, 2);
            }

            $ending = round(max(0, $opening - $principalPart), 2);

            $schedule[] = [
                'installment_no' => $i,
                'due_date' => $start->copy()->addMonthsNoOverflow($i - 1)->toDateString(),
                'opening_principal' => $opening,
                'principal_amount' => $principalPart,
                'interest_amount' => $interestPart,
                'penalty_amount' => 0,
                'installment_amount' => round($principalPart + $interestPart, 2),
                'ending_principal' => $ending,
                'principal_paid' => 0,
                'interest_paid' => 0,
                'penalty_paid' => 0,
                'status' => 'UNPAID',
            ];

            $opening = $ending;
        }

        return $schedule;
    }
}
