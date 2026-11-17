<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Only admins list the users in the admin area.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only admins change someone else's role.
     */
    public function update(User $user, User $model): bool
    {
        return $user->isAdmin();
    }
}
