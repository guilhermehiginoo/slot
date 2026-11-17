<?php

namespace App\Actions\Admin;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class UpdateUserRole
{
    /**
     * @throws ValidationException
     */
    public function handle(User $actor, User $user, Role $role): User
    {
        if ($user->isAdmin() && $role !== Role::Admin) {
            if ($actor->is($user)) {
                throw ValidationException::withMessages([
                    'role' => 'Você não pode tirar o seu próprio papel de admin.',
                ]);
            }

            if (!$actor->admin_since?->lt($user->admin_since)) {
                throw ValidationException::withMessages([
                    'role' => 'Você não pode rebaixar um admin mais antigo que você.',
                ]);
            }
        }

        if ($role === Role::Admin && !$user->isAdmin()) {
            $user->admin_since = now();
        } elseif ($role !== Role::Admin) {
            $user->admin_since = null;
        }

        $user->role = $role;
        $user->save();

        return $user;
    }
}
