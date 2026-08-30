<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\User;

class BranchService
{
    public function getCurrentBranch(User $user): ?Branch
    {
        return $user->branch;
    }

    public function isSuperAdmin(User $user): bool
    {
        return $user->hasRole('SuperAdmin');
    }

    public function canAccessBranch(User $user, int $branchId): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return (int) $user->branch_id === $branchId;
    }
}