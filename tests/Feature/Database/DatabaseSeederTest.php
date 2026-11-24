<?php

namespace Tests\Feature\Database;

use App\Enums\Role;
use App\Models\{Course, User};
use Database\Seeders\CourseSeeder;
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

    public function test_seeder_creates_the_bti_courses()
    {
        $this->seed();

        $this->assertSame(
            [
                'IMD0012' => 'Introdução às Técnicas de Programação',
                'IMD0017' => 'Práticas de Leitura e Escrita em Português I',
                'IMD0018' => 'Práticas de Leitura em Inglês',
                'IMD0019' => 'Resolução de Problemas Matemáticos para TI',
                'IMD0020' => 'Tecnologia da Informação e Sociedade',
                'IMD0024' => 'Cálculo Diferencial e Integral I',
                'IMD0027' => 'Práticas de Leitura e Escrita em Português II',
                'IMD0028' => 'Fundamentos Matemáticos da Computação I',
                'IMD0029' => 'Estrutura de Dados Básicas I',
                'IMD0030' => 'Linguagem de Programação I',
                'IMD0033' => 'Probabilidade',
                'IMD0034' => 'Vetores e Geometria Analítica',
                'IMD0038' => 'Fundamentos Matemáticos da Computação II',
                'IMD0041' => 'Introdução à Organização e Arquitetura de Computadores',
            ],
            Course::orderBy('code')->pluck('name', 'code')->all(),
        );
    }

    public function test_running_the_seeder_twice_does_not_duplicate_courses()
    {
        $this->seed(CourseSeeder::class);
        $this->seed(CourseSeeder::class);

        $this->assertSame(14, Course::count());
    }
}
