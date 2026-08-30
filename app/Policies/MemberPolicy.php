<?php

namespace App\Policies;

use App\Models\Member;
use App\Models\User;

class MemberPolicy
{
    /**
     * Determine whether the user can view any members.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('member.view');
    }

    /**
     * Determine whether the user can view the member.
     */
    public function view(User $user, Member $member): bool
    {
        if ($user->hasRole('SuperAdmin')) {
            return true;
        }

        return $user->branch_id === $member->branch_id
            && $user->can('member.view');
    }

    /**
     * Determine whether the user can create members.
     */
    public function create(User $user): bool
    {
        return $user->can('member.create');
    }

    /**
     * Determine whether the user can update the member.
     */
    public function update(User $user, Member $member): bool
    {
        if ($user->hasRole('SuperAdmin')) {
            return true;
        }

        return $user->branch_id === $member->branch_id
            && $user->can('member.edit');
    }

    /**
     * Determine whether the user can delete the member.
     */
    public function delete(User $user, Member $member): bool
    {
        if ($user->hasRole('SuperAdmin')) {
            return true;
        }

        return $user->branch_id === $member->branch_id
            && $user->can('member.delete');
    }
}