<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\UpdateUserRole;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRoleRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\{RedirectResponse, Request};
use Inertia\{Inertia, Response};

class UserController extends Controller
{
    /**
     * List the users, searching by name or email. Access is checked by the admin route group.
     */
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->value() ?: null;

        $users = User::query()
            ->select(['id', 'name', 'email', 'role'])
            ->when($search, fn (Builder $query, string $search) => $query->where(
                fn (Builder $query) => $query
                    ->whereLike('name', "%{$search}%")
                    ->orWhereLike('email', "%{$search}%"),
            ))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/users/index', [
            'users'   => $users,
            'filters' => ['search' => $search],
        ]);
    }

    /**
     * Change the user's role.
     */
    public function update(UpdateUserRoleRequest $request, User $user, UpdateUserRole $updateUserRole): RedirectResponse
    {
        $updateUserRole->handle($request->user(), $user, $request->enum('role', Role::class));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Papel atualizado.']);

        return back();
    }
}
