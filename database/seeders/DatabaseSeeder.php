<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->admin()->create([
            'name'  => 'Admin',
            'email' => 'admin@slot.test',
        ]);

        User::factory()->professor()->create([
            'name'  => 'Professor',
            'email' => 'professor@slot.test',
        ]);

        User::factory()->create([
            'name'  => 'Aluno',
            'email' => 'student@slot.test',
        ]);

        $this->call([CourseSeeder::class, ClassGroupSeeder::class]);
    }
}
