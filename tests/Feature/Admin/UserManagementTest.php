<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $this->get(route('admin.users.index'))->assertRedirect(route('login'));
    }

    #[TestDox('Cenário: Quem não é admin não acessa as páginas de admin')]
    public function test_non_admins_cannot_open_the_admin_users_page()
    {
        $student = User::factory()->create();

        $this->actingAs($student)
            ->get('/admin/users')
            ->assertForbidden();
    }

    public function test_professors_cannot_open_the_admin_users_page()
    {
        $professor = User::factory()->professor()->create();

        $this->actingAs($professor)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_admin_sees_the_users_with_their_roles()
    {
        $admin = User::factory()->admin()->create(['name' => 'Ana']);
        User::factory()->professor()->create(['name' => 'Bruno']);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/users/index')
                ->has('users.data', 2)
                ->has('users.data.0', fn (Assert $user) => $user
                    ->where('name', 'Ana')
                    ->where('role', 'admin')
                    ->hasAll(['id', 'email'])
                    ->missing('password'))
                ->where('users.data.1.role', 'professor')
                ->where('filters.search', null));
    }

    #[TestDox('Cenário: Admin busca um usuário')]
    public function test_admin_searches_users_by_name_or_email()
    {
        $admin = User::factory()->admin()->create(['name' => 'Ana', 'email' => 'ana@example.com']);
        User::factory()->create(['name' => 'Maria Souza', 'email' => 'maria@example.com']);
        User::factory()->create(['name' => 'João', 'email' => 'joao@example.com']);

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['search' => 'souza']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('users.data', 1)
                ->where('users.data.0.email', 'maria@example.com')
                ->where('filters.search', 'souza'));

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['search' => 'JOAO@']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('users.data', 1)
                ->where('users.data.0.name', 'João'));
    }

    public function test_the_users_list_is_paginated()
    {
        $admin = User::factory()->admin()->create();
        User::factory()->count(15)->create();

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('users.data', 15)
                ->where('users.current_page', 1)
                ->where('users.last_page', 2));
    }

    #[TestDox('Cenário: Admin muda um papel')]
    public function test_admin_changes_a_role()
    {
        $admin = User::factory()->admin()->create();
        $maria = User::factory()->create(['name' => 'Maria']);

        $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->patch(route('admin.users.update', $maria), ['role' => 'professor'])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.users.index'));

        $this->assertSame(Role::Professor, $maria->refresh()->role);

        $this->actingAs($maria)
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page->where('auth.user.role', 'professor'));
    }

    public function test_admin_can_promote_another_user_to_admin()
    {
        $admin = User::factory()->admin()->create();
        $maria = User::factory()->professor()->create();

        $this->actingAs($admin)
            ->patch(route('admin.users.update', $maria), ['role' => 'admin'])
            ->assertSessionHasNoErrors();

        $this->assertSame(Role::Admin, $maria->refresh()->role);
        $this->assertNotNull($maria->admin_since);

        $this->actingAs($maria)
            ->get(route('admin.users.index'))
            ->assertOk();
    }

    #[TestDox('Cenário: Admin mais antigo rebaixa um admin mais novo')]
    public function test_older_admin_demotes_a_newer_admin()
    {
        $admin = User::factory()->admin()->create(['admin_since' => now()->subYear()]);
        $maria = User::factory()->admin()->create(['admin_since' => now()->subDay()]);

        $this->actingAs($admin)
            ->patch(route('admin.users.update', $maria), ['role' => 'student'])
            ->assertSessionHasNoErrors();

        $maria->refresh();
        $this->assertSame(Role::Student, $maria->role);
        $this->assertNull($maria->admin_since);

        $this->actingAs($maria)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    #[TestDox('Cenário: Admin novo não rebaixa um admin mais antigo')]
    public function test_newer_admin_cannot_demote_an_older_admin()
    {
        $maria = User::factory()->admin()->create(['admin_since' => now()->subYear()]);
        $admin = User::factory()->admin()->create(['admin_since' => now()->subDay()]);

        $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->patch(route('admin.users.update', $maria), ['role' => 'student'])
            ->assertSessionHasErrors(['role' => 'Você não pode rebaixar um admin mais antigo que você.'])
            ->assertRedirect(route('admin.users.index'));

        $this->assertSame(Role::Admin, $maria->refresh()->role);
    }

    #[TestDox('Cenário: Admin não tira o próprio papel de admin')]
    public function test_admin_cannot_remove_their_own_admin_role()
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->patch(route('admin.users.update', $admin), ['role' => 'student'])
            ->assertSessionHasErrors(['role' => 'Você não pode tirar o seu próprio papel de admin.'])
            ->assertRedirect(route('admin.users.index'));

        $this->assertSame(Role::Admin, $admin->refresh()->role);
    }

    public function test_the_role_must_be_a_valid_one()
    {
        $admin = User::factory()->admin()->create();
        $maria = User::factory()->create();

        $this->actingAs($admin)
            ->patch(route('admin.users.update', $maria), ['role' => 'monitor'])
            ->assertSessionHasErrors('role');

        $this->assertSame(Role::Student, $maria->refresh()->role);
    }

    public function test_non_admins_cannot_change_a_role()
    {
        $professor = User::factory()->professor()->create();
        $maria     = User::factory()->create();

        $this->actingAs($professor)
            ->patch(route('admin.users.update', $maria), ['role' => 'admin'])
            ->assertForbidden();

        $this->assertSame(Role::Student, $maria->refresh()->role);
    }

    public function test_the_role_cannot_be_changed_through_the_profile_settings()
    {
        $student = User::factory()->create();

        $this->actingAs($student)->patch(route('profile.update'), [
            'name'  => $student->name,
            'email' => $student->email,
            'role'  => 'admin',
        ]);

        $this->assertSame(Role::Student, $student->refresh()->role);
    }
}
