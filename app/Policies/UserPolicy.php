<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('user.view');
    }

    public function view(User $user, User $target): bool
    {
        if ($user->hasRole('SuperAdmin')) {
            return $user->can('user.view');
        }

        return (int) $user->branch_id === (int) $target->branch_id
            && $user->can('user.view');
    }

    public function create(User $user): bool
    {
        return $user->can('user.create');
    }

    public function update(User $user, User $target): bool
    {
        if ($user->hasRole('SuperAdmin')) {
            return $user->can('user.edit');
        }

        return (int) $user->branch_id === (int) $target->branch_id
            && $user->can('user.edit');
    }

    public function delete(User $user, User $target): bool
    {
        if ($user->id === $target->id) {
            return false;
        }

        if ($user->hasRole('SuperAdmin')) {
            return $user->can('user.delete');
        }

        return (int) $user->branch_id === (int) $target->branch_id
            && $user->can('user.delete');
    }

    public function restore(User $user, User $target): bool
    {
        if ($user->hasRole('SuperAdmin')) {
            return $user->can('user.restore');
        }

        return (int) $user->branch_id === (int) $target->branch_id
            && $user->can('user.restore');
    }
}
