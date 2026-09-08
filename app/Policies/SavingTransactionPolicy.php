<?php

namespace App\Policies;

use App\Models\SavingTransaction;
use App\Models\User;

class SavingTransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('saving-transaction.view');
    }

    public function view(
        User $user,
        SavingTransaction $transaction
    ): bool {
        if (! $user->can('saving-transaction.view')) {
            return false;
        }

        if ($user->hasRole('SuperAdmin')) {
            return true;
        }

        if ($user->hasRole('Anggota')) {
            $memberId = $user->member?->id;

            return $memberId !== null
                && (int) $transaction->member_id === (int) $memberId;
        }

        return (int) $user->branch_id === (int) $transaction->branch_id;
    }

    public function create(User $user): bool
    {
        if (! $user->can('saving-transaction.create')) {
            return false;
        }

        if ($user->hasRole('Anggota')) {
            return $user->member()
                ->where('member_status', 'ACTIVE')
                ->exists();
        }

        return true;
    }

    public function approve(
        User $user,
        SavingTransaction $transaction
    ): bool {
        if ($user->hasRole('Anggota')) {
            return false;
        }

        if (
            $transaction->status !== 'PENDING'
            || ! $user->can('saving-transaction.approve')
        ) {
            return false;
        }

        if ($user->hasRole('SuperAdmin')) {
            return true;
        }

        return (int) $user->branch_id === (int) $transaction->branch_id;
    }

    public function reject(
        User $user,
        SavingTransaction $transaction
    ): bool {
        if ($user->hasRole('Anggota')) {
            return false;
        }

        if (
            $transaction->status !== 'PENDING'
            || ! $user->can('saving-transaction.reject')
        ) {
            return false;
        }

        if ($user->hasRole('SuperAdmin')) {
            return true;
        }

        return (int) $user->branch_id === (int) $transaction->branch_id;
    }
}
