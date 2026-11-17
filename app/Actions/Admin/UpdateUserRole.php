<?php

namespace App\Actions\Admin;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class UpdateUserRole
{
    /**
     * Change a user's role. An admin never removes their own admin role,
     * so the app always keeps at least one admin.
     *
     * @throws ValidationException
     */
    public function handle(User $actor, User $user, Role $role): User
    {
        if ($actor->is($user) && $user->isAdmin() && $role !== Role::Admin) {
            throw ValidationException::withMessages([
                'role' => 'Você não pode tirar o seu próprio papel de admin.',
            ]);
        }

        $user->role = $role;
        $user->save();

        return $user;
    }
}
