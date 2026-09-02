<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    private const MANAGEABLE_ROLES = [
        'Manager',
        'Pengurus',
        'Accounting',
        'Anggota',
    ];

    public function viewAny(User $user): bool
    {
        return $user->can('user.view');
    }

    public function view(
        User $user,
        User $target
    ): bool {
        if (! $user->can('user.view')) {
            return false;
        }

        if ($user->hasRole('SuperAdmin')) {
            return true;
        }

        return $this->canManageTarget(
            $user,
            $target
        );
    }

    public function create(User $user): bool
    {
        return $user->can('user.create');
    }

    public function update(
        User $user,
        User $target
    ): bool {
        if (! $user->can('user.edit')) {
            return false;
        }

        if ($user->hasRole('SuperAdmin')) {
            return true;
        }

        return $this->canManageTarget(
            $user,
            $target
        );
    }

    public function delete(
        User $user,
        User $target
    ): bool {
        if (! $user->can('user.delete')) {
            return false;
        }

        if ($user->id === $target->id) {
            return false;
        }

        if ($user->hasRole('SuperAdmin')) {
            return true;
        }

        return $this->canManageTarget(
            $user,
            $target
        );
    }

    public function restore(
        User $user,
        User $target
    ): bool {
        if (! $user->can('user.restore')) {
            return false;
        }

        if ($user->hasRole('SuperAdmin')) {
            return true;
        }

        return $this->canManageTarget(
            $user,
            $target
        );
    }

    private function canManageTarget(
        User $user,
        User $target
    ): bool {
        if (
            $user->branch_id === null
            || $target->branch_id === null
        ) {
            return false;
        }

        if (
            (int) $user->branch_id
            !== (int) $target->branch_id
        ) {
            return false;
        }

        $targetRoles = $target
            ->getRoleNames()
            ->values()
            ->all();

        if (empty($targetRoles)) {
            return false;
        }

        foreach ($targetRoles as $role) {
            if (
                ! in_array(
                    $role,
                    self::MANAGEABLE_ROLES,
                    true
                )
            ) {
                return false;
            }
        }

        return true;
    }
}