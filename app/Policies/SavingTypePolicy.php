<?php

namespace App\Policies;

use App\Models\SavingType;
use App\Models\User;

class SavingTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('saving-type.view');
    }

    public function view(User $user, SavingType $savingType): bool
    {
        return $user->can('saving-type.view');
    }

    public function create(User $user): bool
    {
        return $user->can('saving-type.create');
    }

    public function update(User $user, SavingType $savingType): bool
    {
        return $user->can('saving-type.edit');
    }

    public function delete(User $user, SavingType $savingType): bool
    {
        return $user->can('saving-type.delete');
    }
}
