<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\{ClassGroup, User};

class ClassGroupPolicy
{
    public function viewTaught(User $user): bool
    {
        return $user->role === Role::Professor;
    }

    public function view(User $user, ClassGroup $classGroup): bool
    {
        return $user->role === Role::Professor
            && $classGroup->professors()->whereKey($user->id)->exists();
    }

    public function manageMonitors(User $user, ClassGroup $classGroup): bool
    {
        return $this->view($user, $classGroup);
    }

    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, ClassGroup $classGroup): bool
    {
        return $user->isAdmin();
    }
}
