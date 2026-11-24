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
            ->get('/admin/disciplinas')
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

    #[TestDox('Cenário: Admin cria uma disciplina')]
    public function test_admin_creates_a_course()
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.courses.store'), [
                'code' => 'IMD0030',
                'name' => 'Linguagem de Programação I',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.courses.index'));

        $this->assertDatabaseHas('courses', [
            'code'        => 'IMD0030',
            'name'        => 'Linguagem de Programação I',
            'archived_at' => null,
        ]);
    }

    #[TestDox('Cenário: O código é normalizado')]
    public function test_the_code_is_normalized()
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.courses.store'), [
                'code' => 'imd0030 ',
                'name' => 'Linguagem de Programação I',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('courses', ['code' => 'IMD0030']);
        $this->assertDatabaseCount('courses', 1);
    }

    #[TestDox('Cenário: Código inválido')]
    public function test_the_code_must_have_three_letters_and_four_digits()
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.courses.store'), [
                'code' => 'IMD30',
                'name' => 'Linguagem de Programação I',
            ])
            ->assertSessionHasErrors(['code' => 'O código deve ter 3 letras seguidas de 4 dígitos']);

        $this->assertDatabaseCount('courses', 0);
    }

    #[TestDox('Cenário: Código repetido')]
    public function test_the_code_must_be_unique()
    {
        $admin = User::factory()->admin()->create();
        Course::factory()->create(['code' => 'IMD0030']);

        $this->actingAs($admin)
            ->post(route('admin.courses.store'), [
                'code' => 'IMD0030',
                'name' => 'Outra disciplina',
            ])
            ->assertSessionHasErrors(['code' => 'Este código já está em uso']);

        $this->assertDatabaseCount('courses', 1);
    }

    public function test_an_archived_course_keeps_its_code_reserved()
    {
        $admin = User::factory()->admin()->create();
        Course::factory()->archived()->create(['code' => 'IMD0030']);

        $this->actingAs($admin)
            ->post(route('admin.courses.store'), [
                'code' => 'IMD0030',
                'name' => 'Outra disciplina',
            ])
            ->assertSessionHasErrors(['code' => 'Este código já está em uso']);

        $this->assertDatabaseCount('courses', 1);
    }

    public function test_the_name_is_required_and_limited_to_255_characters()
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.courses.store'), ['code' => 'IMD0030', 'name' => ''])
            ->assertSessionHasErrors(['name' => 'Informe o nome da disciplina.']);

        $this->actingAs($admin)
            ->post(route('admin.courses.store'), ['code' => 'IMD0030', 'name' => str_repeat('a', 256)])
            ->assertSessionHasErrors(['name' => 'O nome deve ter no máximo 255 caracteres.']);

        $this->assertDatabaseCount('courses', 0);
    }

    public function test_non_admins_cannot_create_courses()
    {
        $data = ['code' => 'IMD0030', 'name' => 'Linguagem de Programação I'];

        $this->actingAs(User::factory()->create())
            ->post(route('admin.courses.store'), $data)
            ->assertForbidden();

        $this->actingAs(User::factory()->professor()->create())
            ->post(route('admin.courses.store'), $data)
            ->assertForbidden();

        $this->assertDatabaseCount('courses', 0);
    }

    #[TestDox('Cenário: Admin edita uma disciplina')]
    public function test_admin_edits_a_course()
    {
        $admin  = User::factory()->admin()->create();
        $course = Course::factory()->create(['code' => 'IMD0030', 'name' => 'Linguagem de Programação I']);

        $this->actingAs($admin)
            ->patch(route('admin.courses.update', $course), [
                'code' => 'IMD0031',
                'name' => 'Linguagem de Programação 1',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.courses.index'));

        $this->assertDatabaseHas('courses', [
            'id'   => $course->id,
            'code' => 'IMD0031',
            'name' => 'Linguagem de Programação 1',
        ]);
    }

    #[TestDox('Cenário: Salvar sem mudar o código')]
    public function test_saving_without_changing_the_code_is_allowed()
    {
        $admin  = User::factory()->admin()->create();
        $course = Course::factory()->create(['code' => 'IMD0030']);

        $this->actingAs($admin)
            ->patch(route('admin.courses.update', $course), [
                'code' => 'IMD0030',
                'name' => 'Linguagem de Programação 1',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('Linguagem de Programação 1', $course->fresh()->name);
    }

    public function test_editing_to_a_code_in_use_is_rejected()
    {
        $admin = User::factory()->admin()->create();
        Course::factory()->create(['code' => 'IMD0012']);
        $course = Course::factory()->create(['code' => 'IMD0030']);

        $this->actingAs($admin)
            ->patch(route('admin.courses.update', $course), ['code' => 'imd0012', 'name' => 'Outra'])
            ->assertSessionHasErrors(['code' => 'Este código já está em uso']);

        $this->assertSame('IMD0030', $course->fresh()->code);
    }

    public function test_admin_edits_an_archived_course()
    {
        $admin  = User::factory()->admin()->create();
        $course = Course::factory()->archived()->create(['code' => 'IMD0030']);

        $this->actingAs($admin)
            ->patch(route('admin.courses.update', $course), ['code' => 'IMD0030', 'name' => 'Novo nome'])
            ->assertSessionHasNoErrors();

        $course->refresh();
        $this->assertSame('Novo nome', $course->name);
        $this->assertTrue($course->isArchived());
    }

    public function test_non_admins_cannot_edit_courses()
    {
        $course = Course::factory()->create(['code' => 'IMD0030', 'name' => 'Original']);

        foreach ([User::factory()->create(), User::factory()->professor()->create()] as $user) {
            $this->actingAs($user)
                ->patch(route('admin.courses.update', $course), ['code' => 'IMD0030', 'name' => 'Mudou'])
                ->assertForbidden();
        }

        $this->assertSame('Original', $course->fresh()->name);
    }

    #[TestDox('Cenário: Admin arquiva uma disciplina')]
    public function test_admin_archives_a_course()
    {
        $admin  = User::factory()->admin()->create();
        $course = Course::factory()->create(['code' => 'IMD0030']);

        $this->actingAs($admin)
            ->from(route('admin.courses.index', ['search' => 'IMD', 'page' => 1]))
            ->post(route('admin.courses.archive.store', $course))
            ->assertRedirect(route('admin.courses.index', ['search' => 'IMD', 'page' => 1]))
            ->assertInertiaFlash('toast.message', 'Disciplina arquivada com sucesso!');

        $this->assertTrue($course->fresh()->isArchived());
    }

    public function test_archiving_twice_keeps_the_original_date()
    {
        $admin      = User::factory()->admin()->create();
        $course     = Course::factory()->create(['archived_at' => now()->subDay()]);
        $archivedAt = $course->fresh()->archived_at;

        $this->actingAs($admin)->post(route('admin.courses.archive.store', $course));

        $this->assertEquals($archivedAt, $course->fresh()->archived_at);
    }

    public function test_non_admins_cannot_archive_courses()
    {
        $course = Course::factory()->create();

        foreach ([User::factory()->create(), User::factory()->professor()->create()] as $user) {
            $this->actingAs($user)
                ->post(route('admin.courses.archive.store', $course))
                ->assertForbidden();
        }

        $this->assertFalse($course->fresh()->isArchived());
    }
}
