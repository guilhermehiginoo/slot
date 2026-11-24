<?php

namespace App\Policies;

use App\Models\{ClassGroup, User};

class ClassGroupPolicy
{
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
