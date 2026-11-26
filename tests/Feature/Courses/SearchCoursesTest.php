<?php

namespace Tests\Feature\Courses;

use App\Models\{Course, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

class SearchCoursesTest extends TestCase
{
    use RefreshDatabase;

    #[TestDox('Cenário: Busca pelo código')]
    public function test_finds_by_code_prefix(): void
    {
        Course::factory()->create(['code' => 'IMD0030', 'name' => 'Linguagem de Programação I']);
        Course::factory()->create(['code' => 'DIM0030', 'name' => 'Cálculo']);

        $this->actingAs(User::factory()->create())
            ->get(route('courses.index', ['search' => 'imd0030']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('courses/index')
                ->has('courses.data', 1)
                ->where('courses.data.0', ['code' => 'IMD0030', 'name' => 'Linguagem de Programação I'])
                ->where('filters.search', 'imd0030'));

        $this->get(route('courses.index', ['search' => '0030']))
            ->assertInertia(fn (Assert $page) => $page->where('courses.total', 0));
    }

    #[TestDox('Cenário: Parte do nome sem acento')]
    public function test_finds_by_name_without_accents(): void
    {
        Course::factory()->create(['code' => 'IMD0030', 'name' => 'Linguagem de Programação I']);
        Course::factory()->create(['code' => 'IMD0040', 'name' => 'Linguagem de PROGRAMAÇÃO II']);
        Course::factory()->create(['code' => 'IMD0029', 'name' => 'Estrutura de Dados Básicas I']);

        $this->actingAs(User::factory()->create())
            ->get(route('courses.index', ['search' => 'programacao']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('courses.data', 2)
                ->where('courses.data.0.code', 'IMD0030')
                ->where('courses.data.1.code', 'IMD0040'));
    }

    #[TestDox('Cenário: Disciplina arquivada não aparece')]
    public function test_hides_archived(): void
    {
        Course::factory()->archived()->create(['code' => 'IMD0030']);

        $this->actingAs(User::factory()->create())
            ->get(route('courses.index', ['search' => 'imd0030']))
            ->assertInertia(fn (Assert $page) => $page->where('courses.total', 0));

        $this->get(route('courses.index'))
            ->assertInertia(fn (Assert $page) => $page->where('courses.total', 0));
    }

    #[TestDox('Cenário: Nenhum resultado')]
    public function test_no_results(): void
    {
        Course::factory()->create(['code' => 'IMD0030']);

        $this->actingAs(User::factory()->create())
            ->get(route('courses.index', ['search' => 'xyz']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('courses.total', 0)
                ->where('filters.search', 'xyz'));
    }

    public function test_empty_search_lists_active_courses_by_code_paginated(): void
    {
        Course::factory()->create(['code' => 'IMD0030']);
        Course::factory()->create(['code' => 'DIM0115']);
        Course::factory()->count(14)->create();

        $this->actingAs(User::factory()->create())
            ->get(route('courses.index', ['search' => '   ']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('courses.data', 15)
                ->where('courses.total', 16)
                ->where('courses.data.0.code', Course::query()->orderBy('code')->value('code'))
                ->where('filters.search', null));
    }

    public function test_every_logged_in_role_can_search_and_guests_cannot(): void
    {
        $this->get(route('courses.index'))->assertRedirect(route('login'));

        foreach ([User::factory()->create(), User::factory()->professor()->create(), User::factory()->admin()->create()] as $user) {
            $this->actingAs($user)->get('/disciplinas')->assertOk();
        }
    }

    public function test_search_name_follows_the_course_name(): void
    {
        $course = Course::factory()->create(['name' => 'Introdução à Computação']);
        $this->assertSame('introducao a computacao', $course->search_name);

        $course->update(['name' => 'Cálculo II']);
        $this->assertSame('calculo ii', $course->fresh()->search_name);
    }
}
