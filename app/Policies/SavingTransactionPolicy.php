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

    public function view(User $user, SavingTransaction $transaction): bool
    {
        if ($user->hasRole('SuperAdmin')) {
            return $user->can('saving-transaction.view');
        }

        return (int) $user->branch_id === (int) $transaction->branch_id
            && $user->can('saving-transaction.view');
    }

    public function create(User $user): bool
    {
        return $user->can('saving-transaction.create');
    }

    public function approve(User $user, SavingTransaction $transaction): bool
    {
        if ($transaction->status !== 'PENDING') {
            return false;
        }

        if ($user->hasRole('SuperAdmin')) {
            return $user->can('saving-transaction.approve');
        }

        return (int) $user->branch_id === (int) $transaction->branch_id
            && $user->can('saving-transaction.approve');
    }

    public function reject(User $user, SavingTransaction $transaction): bool
    {
        if ($transaction->status !== 'PENDING') {
            return false;
        }

        if ($user->hasRole('SuperAdmin')) {
            return $user->can('saving-transaction.reject');
        }

        return (int) $user->branch_id === (int) $transaction->branch_id
            && $user->can('saving-transaction.reject');
    }
}
