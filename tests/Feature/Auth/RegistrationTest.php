<?php

namespace Tests\Feature\Auth;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessFortifyHas(Features::registration());
    }

    public function test_registration_screen_can_be_rendered()
    {
        $response = $this->get(route('register'));

        $response->assertOk();
    }

    public function test_new_users_can_register()
    {
        $response = $this->post(route('register.store'), [
            'name'                  => 'Test User',
            'email'                 => 'test@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    #[TestDox('Cenário: Novo cadastro entra como aluno')]
    public function test_new_users_register_as_students()
    {
        $this->post(route('register.store'), [
            'name'                  => 'Maria',
            'email'                 => 'maria@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertSame(Role::Student, User::where('email', 'maria@example.com')->sole()->role);
    }

    public function test_the_role_sent_in_the_registration_form_is_ignored()
    {
        $this->post(route('register.store'), [
            'name'                  => 'Maria',
            'email'                 => 'maria@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            'role'                  => 'admin',
        ]);

        $this->assertSame(Role::Student, User::where('email', 'maria@example.com')->sole()->role);
    }
}
