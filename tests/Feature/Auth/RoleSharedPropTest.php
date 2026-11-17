<?php

namespace Tests\Feature\Auth;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RoleSharedPropTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{Role}>
     */
    public static function roles(): array
    {
        return [
            'admin'     => [Role::Admin],
            'professor' => [Role::Professor],
            'aluno'     => [Role::Student],
        ];
    }

    #[DataProvider('roles')]
    public function test_the_logged_user_role_is_shared_with_every_page(Role $role)
    {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page->where('auth.user.role', $role->value));
    }
}
