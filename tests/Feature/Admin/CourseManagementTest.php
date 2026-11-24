<?php

namespace Tests\Feature\Admin;

use App\Models\{Course, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

class CourseManagementTest extends TestCase
{
    use RefreshDatabase;

    #[TestDox('Cenário: Quem não é admin não acessa o catálogo')]
    public function test_non_admins_cannot_open_the_courses_pages()
    {
        $this->get(route('admin.courses.index'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->get('/admin/courses')
            ->assertForbidden();

        $this->actingAs(User::factory()->professor()->create())
            ->get(route('admin.courses.index'))
            ->assertForbidden();
    }

    public function test_admin_sees_courses_ordered_by_code()
    {
        $admin = User::factory()->admin()->create();
        Course::factory()->create(['code' => 'IMD0030', 'name' => 'Linguagem de Programação I']);
        Course::factory()->archived()->create(['code' => 'IMD0012', 'name' => 'Introdução às Técnicas de Programação']);

        $this->actingAs($admin)
            ->get(route('admin.courses.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/courses/index')
                ->has('courses.data', 2)
                ->has('courses.data.0', fn (Assert $course) => $course
                    ->has('id')
                    ->where('code', 'IMD0012')
                    ->where('name', 'Introdução às Técnicas de Programação')
                    ->where('archived_at', fn ($value) => $value !== null))
                ->where('courses.data.1.code', 'IMD0030')
                ->where('courses.data.1.archived_at', null)
                ->where('filters.search', null));
    }

    public function test_the_courses_list_is_paginated()
    {
        $admin = User::factory()->admin()->create();
        Course::factory()->count(16)->create();

        $this->actingAs($admin)
            ->get(route('admin.courses.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('courses.data', 15)
                ->where('courses.current_page', 1)
                ->where('courses.last_page', 2)
                ->where('courses.total', 16));
    }

    #[TestDox('Cenário: Buscar por código ou nome')]
    public function test_admin_searches_courses_by_code_or_name()
    {
        $admin = User::factory()->admin()->create();
        Course::factory()->create(['code' => 'IMD0030', 'name' => 'Linguagem de Programacao I']);
        Course::factory()->create(['code' => 'IMD0012', 'name' => 'Introducao as Tecnicas']);

        $this->actingAs($admin)
            ->get(route('admin.courses.index', ['search' => '0030']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('courses.data', 1)
                ->where('courses.data.0.code', 'IMD0030')
                ->where('filters.search', '0030'));

        $this->actingAs($admin)
            ->get(route('admin.courses.index', ['search' => 'TECNICAS']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('courses.data', 1)
                ->where('courses.data.0.code', 'IMD0012'));
    }

    #[TestDox('Cenário: Estados vazios')]
    public function test_empty_states_come_from_total_and_search()
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.courses.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('courses.total', 0)
                ->where('filters.search', null));

        Course::factory()->create(['code' => 'IMD0030']);

        $this->actingAs($admin)
            ->get(route('admin.courses.index', ['search' => 'IMD9999']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('courses.total', 0)
                ->where('filters.search', 'IMD9999'));
    }
}