<?php

namespace Tests\Feature\Database;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_one_user_of_each_role()
    {
        $this->seed();

        $this->assertSame(
            [
                'admin@slot.test'     => Role::Admin,
                'professor@slot.test' => Role::Professor,
                'student@slot.test'   => Role::Student,
            ],
            User::orderBy('email')->pluck('role', 'email')->all(),
        );
    }
}
