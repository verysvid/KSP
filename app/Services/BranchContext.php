<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\User;

class BranchContext
{
    public function getCurrentUser(): ?User
    {
        return auth()->user();
    }

    public function isSuperAdmin(): bool
    {
        $user = $this->getCurrentUser();

        return $user !== null
            && $user->hasRole('SuperAdmin');
    }

    public function getCurrentBranchId(): ?int
    {
        $user = $this->getCurrentUser();

        if (!$user) {
            return null;
        }

        if ($this->isSuperAdmin()) {
            return null;
        }

        return $user->branch_id;
    }

    public function getCurrentBranch(): ?Branch
    {
        $branchId = $this->getCurrentBranchId();

        if (!$branchId) {
            return null;
        }

        return Branch::find($branchId);
    }

    public function hasBranch(): bool
    {
        return $this->getCurrentBranchId() !== null;
    }
}