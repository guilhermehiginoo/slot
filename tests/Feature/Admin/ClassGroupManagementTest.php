<?php

namespace Tests\Feature\Admin;

use App\Models\{ClassGroup, Course, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

class ClassGroupManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['slot.current_semester' => '2026.2']);
    }

    #[TestDox('Cenário: Quem não é admin não acessa as turmas')]
    public function test_non_admins_cannot_open_the_class_groups_pages()
    {
        $this->get(route('admin.class-groups.index'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->professor()->create())
            ->get('/admin/turmas')
            ->assertForbidden();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.class-groups.index'))
            ->assertForbidden();
    }

    #[TestDox('Cenário: Filtrar por disciplina e semestre')]
    public function test_admin_filters_class_groups_by_course_and_semester()
    {
        $admin = User::factory()->admin()->create();
        $lp1   = Course::factory()->create(['code' => 'IMD0030', 'name' => 'Linguagem de Programação I']);
        $itp   = Course::factory()->create(['code' => 'IMD0012']);
        $ana   = User::factory()->professor()->create(['name' => 'Ana Costa']);
        ClassGroup::factory()->for($lp1)->create(['semester' => '2026.2', 'number' => 'T01', 'schedule' => '35M12'])
            ->professors()->attach($ana);
        ClassGroup::factory()->for($itp)->create(['semester' => '2026.1', 'number' => 'T01']);

        $this->actingAs($admin)
            ->get(route('admin.class-groups.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/class-groups/index')
                ->has('classGroups.data', 1)
                ->has('classGroups.data.0', fn (Assert $classGroup) => $classGroup
                    ->has('id')
                    ->where('semester', '2026.2')
                    ->where('number', 'T01')
                    ->where('schedule', '35M12')
                    ->where('course', ['id' => $lp1->id, 'code' => 'IMD0030', 'name' => 'Linguagem de Programação I'])
                    ->where('professors', [['id' => $ana->id, 'name' => 'Ana Costa']]))
                ->where('filters', ['course' => null, 'semester' => '2026.2']));

        $this->actingAs($admin)
            ->get(route('admin.class-groups.index', ['semester' => '2026.1']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('classGroups.data', 1)
                ->where('classGroups.data.0.course.code', 'IMD0012')
                ->where('filters.semester', '2026.1'));

        $this->actingAs($admin)
            ->get(route('admin.class-groups.index', ['semester' => '2026.1', 'course' => $lp1->id]))
            ->assertInertia(fn (Assert $page) => $page
                ->has('classGroups.data', 0)
                ->where('filters', ['course' => $lp1->id, 'semester' => '2026.1']));
    }

    public function test_class_groups_are_ordered_by_course_code_and_number_with_15_per_page()
    {
        $admin = User::factory()->admin()->create();
        $lp1   = Course::factory()->create(['code' => 'IMD0030']);
        $itp   = Course::factory()->create(['code' => 'IMD0012']);
        ClassGroup::factory()->for($lp1)->create(['number' => 'T02']);
        ClassGroup::factory()->for($lp1)->create(['number' => 'T01']);
        ClassGroup::factory()->for($itp)->create(['number' => 'T03']);

        foreach (range(10, 22) as $number) {
            ClassGroup::factory()->for(Course::factory()->create(['code' => "IMD99{$number}"]))->create();
        }

        $this->actingAs($admin)
            ->get(route('admin.class-groups.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('classGroups.data', 15)
                ->where('classGroups.total', 16)
                ->where('classGroups.last_page', 2)
                ->where('classGroups.data.0.course.code', 'IMD0012')
                ->where('classGroups.data.1.course.code', 'IMD0030')
                ->where('classGroups.data.1.number', 'T01')
                ->where('classGroups.data.2.number', 'T02'));
    }

    public function test_the_page_gets_course_professor_and_semester_options()
    {
        $admin = User::factory()->admin()->create();
        $lp1   = Course::factory()->create(['code' => 'IMD0030', 'name' => 'Linguagem de Programação I']);
        Course::factory()->archived()->create(['code' => 'IMD0012']);
        $carlos = User::factory()->professor()->create(['name' => 'Carlos Alves']);
        $ana    = User::factory()->professor()->create(['name' => 'Ana Costa']);
        User::factory()->create(['name' => 'Aluno']);
        ClassGroup::factory()->for($lp1)->create(['semester' => '2025.1']);
        ClassGroup::factory()->for($lp1)->create(['semester' => '2027.1']);
        ClassGroup::factory()->for($lp1)->create(['semester' => '2025.1', 'number' => 'T02']);

        $this->actingAs($admin)
            ->get(route('admin.class-groups.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('courses', [['id' => $lp1->id, 'code' => 'IMD0030', 'name' => 'Linguagem de Programação I']])
                ->where('professors', [
                    ['id' => $ana->id, 'name' => 'Ana Costa'],
                    ['id' => $carlos->id, 'name' => 'Carlos Alves'],
                ])
                ->where('semesters', ['2027.1', '2026.2', '2025.1']));
    }

    public function test_has_class_groups_separates_the_empty_states()
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.class-groups.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('classGroups.total', 0)
                ->where('hasClassGroups', false));

        ClassGroup::factory()->create(['semester' => '2026.1']);

        $this->actingAs($admin)
            ->get(route('admin.class-groups.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('classGroups.total', 0)
                ->where('hasClassGroups', true));
    }

    public function test_the_current_semester_scope_follows_the_config()
    {
        $current  = ClassGroup::factory()->create(['semester' => '2026.2']);
        $previous = ClassGroup::factory()->create(['semester' => '2026.1']);

        $this->assertEquals([$current->id], ClassGroup::currentSemester()->pluck('id')->all());

        config(['slot.current_semester' => '2026.1']);

        $this->assertEquals([$previous->id], ClassGroup::currentSemester()->pluck('id')->all());
    }

    /**
     * @return array<string, mixed>
     */
    private function classGroupData(Course $course, User ...$professors): array
    {
        return [
            'course_id'  => $course->id,
            'semester'   => '2026.2',
            'number'     => 'T01',
            'professors' => array_map(fn (User $professor) => $professor->id, $professors),
            'schedule'   => '35M12',
        ];
    }

    #[TestDox('Cenário: Admin cria uma turma')]
    public function test_admin_creates_a_class_group()
    {
        $admin = User::factory()->admin()->create();
        $lp1   = Course::factory()->create(['code' => 'IMD0030']);
        $ana   = User::factory()->professor()->create(['name' => 'Ana Costa']);

        $this->actingAs($admin)
            ->post(route('admin.class-groups.store'), $this->classGroupData($lp1, $ana))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.class-groups.index'))
            ->assertInertiaFlash('toast.message', 'Turma salva com sucesso!');

        $this->actingAs($admin)
            ->get(route('admin.class-groups.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('classGroups.data', 1)
                ->where('classGroups.data.0.course.code', 'IMD0030')
                ->where('classGroups.data.0.number', 'T01')
                ->where('classGroups.data.0.schedule', '35M12')
                ->where('classGroups.data.0.professors', [['id' => $ana->id, 'name' => 'Ana Costa']]));
    }

    #[TestDox('Cenário: Turma repetida')]
    public function test_course_semester_and_number_must_be_unique()
    {
        $admin = User::factory()->admin()->create();
        $lp1   = Course::factory()->create();
        $ana   = User::factory()->professor()->create();
        ClassGroup::factory()->for($lp1)->create(['semester' => '2026.2', 'number' => 'T01']);

        $this->actingAs($admin)
            ->post(route('admin.class-groups.store'), $this->classGroupData($lp1, $ana))
            ->assertSessionHasErrors(['number' => 'Esta turma já existe neste semestre']);

        $this->assertDatabaseCount('class_groups', 1);
    }

    public function test_the_same_class_group_can_exist_in_another_semester()
    {
        $admin = User::factory()->admin()->create();
        $lp1   = Course::factory()->create();
        $ana   = User::factory()->professor()->create();
        ClassGroup::factory()->for($lp1)->create(['semester' => '2026.1', 'number' => 'T01']);

        $this->actingAs($admin)
            ->post(route('admin.class-groups.store'), $this->classGroupData($lp1, $ana))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('class_groups', 2);
    }

    #[TestDox('Cenário: Só professores dão aula')]
    public function test_only_professors_can_teach_a_class_group()
    {
        $admin   = User::factory()->admin()->create();
        $lp1     = Course::factory()->create();
        $ana     = User::factory()->professor()->create(['name' => 'Ana Costa']);
        $student = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.class-groups.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('professors', [['id' => $ana->id, 'name' => 'Ana Costa']]));

        $this->actingAs($admin)
            ->post(route('admin.class-groups.store'), $this->classGroupData($lp1, $student))
            ->assertSessionHasErrors('professors.0');

        $this->assertDatabaseCount('class_groups', 0);
    }

    #[TestDox('Cenário: O número é normalizado')]
    public function test_the_number_is_normalized()
    {
        $admin = User::factory()->admin()->create();
        $lp1   = Course::factory()->create();
        $ana   = User::factory()->professor()->create();

        $this->actingAs($admin)
            ->post(route('admin.class-groups.store'), ['number' => 't01 '] + $this->classGroupData($lp1, $ana))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('class_groups', ['number' => 'T01']);
        $this->assertDatabaseCount('class_groups', 1);
    }

    public function test_the_number_must_be_t_followed_by_two_digits()
    {
        $admin = User::factory()->admin()->create();
        $lp1   = Course::factory()->create();
        $ana   = User::factory()->professor()->create();

        foreach (['T1', 'A01'] as $number) {
            $this->actingAs($admin)
                ->post(route('admin.class-groups.store'), ['number' => $number] + $this->classGroupData($lp1, $ana))
                ->assertSessionHasErrors(['number' => 'O número da turma deve ser T seguido de 2 dígitos']);
        }

        $this->assertDatabaseCount('class_groups', 0);
    }

    #[TestDox('Cenário: Semestre inválido')]
    public function test_the_semester_must_end_in_1_or_2()
    {
        $admin = User::factory()->admin()->create();
        $lp1   = Course::factory()->create();
        $ana   = User::factory()->professor()->create();

        $this->actingAs($admin)
            ->post(route('admin.class-groups.store'), ['semester' => '2026.3'] + $this->classGroupData($lp1, $ana))
            ->assertSessionHasErrors(['semester' => 'O semestre deve estar no formato ano.1 ou ano.2']);

        $this->assertDatabaseCount('class_groups', 0);
    }

    #[TestDox('Cenário: Disciplina arquivada não recebe turma nova')]
    public function test_archived_courses_do_not_get_new_class_groups()
    {
        $admin    = User::factory()->admin()->create();
        $archived = Course::factory()->archived()->create(['code' => 'IMD0030']);
        $ana      = User::factory()->professor()->create();

        $this->actingAs($admin)
            ->get(route('admin.class-groups.index'))
            ->assertInertia(fn (Assert $page) => $page->has('courses', 0));

        $this->actingAs($admin)
            ->post(route('admin.class-groups.store'), $this->classGroupData($archived, $ana))
            ->assertSessionHasErrors('course_id');

        $this->assertDatabaseCount('class_groups', 0);
    }

    public function test_all_fields_are_required()
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.class-groups.store'), [])
            ->assertSessionHasErrors([
                'course_id'  => 'Selecione a disciplina.',
                'semester'   => 'Informe o semestre.',
                'number'     => 'Informe o número da turma.',
                'professors' => 'Selecione ao menos um professor',
                'schedule'   => 'Informe o código de horário.',
            ]);

        $this->assertDatabaseCount('class_groups', 0);
    }

    public function test_the_schedule_is_limited_to_50_characters()
    {
        $admin = User::factory()->admin()->create();
        $lp1   = Course::factory()->create();
        $ana   = User::factory()->professor()->create();

        $this->actingAs($admin)
            ->post(route('admin.class-groups.store'), ['schedule' => str_repeat('a', 51)] + $this->classGroupData($lp1, $ana))
            ->assertSessionHasErrors(['schedule' => 'O código de horário deve ter no máximo 50 caracteres.']);

        $this->assertDatabaseCount('class_groups', 0);
    }

    public function test_non_admins_cannot_create_class_groups()
    {
        $lp1  = Course::factory()->create();
        $ana  = User::factory()->professor()->create();
        $data = $this->classGroupData($lp1, $ana);

        $this->actingAs(User::factory()->create())
            ->post(route('admin.class-groups.store'), $data)
            ->assertForbidden();

        $this->actingAs($ana)
            ->post(route('admin.class-groups.store'), $data)
            ->assertForbidden();

        $this->assertDatabaseCount('class_groups', 0);
    }

    #[TestDox('Cenário: Admin edita uma turma')]
    public function test_admin_edits_a_class_group()
    {
        $admin      = User::factory()->admin()->create();
        $lp1        = Course::factory()->create(['code' => 'IMD0030']);
        $ana        = User::factory()->professor()->create(['name' => 'Ana Costa']);
        $carlos     = User::factory()->professor()->create(['name' => 'Carlos Alves']);
        $classGroup = ClassGroup::factory()->for($lp1)->hasAttached($ana, [], 'professors')->create();

        $this->actingAs($admin)
            ->patch(route('admin.class-groups.update', $classGroup), $this->classGroupData($lp1, $ana, $carlos))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.class-groups.index'))
            ->assertInertiaFlash('toast.message', 'Turma salva com sucesso!');

        $this->actingAs($admin)
            ->get(route('admin.class-groups.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('classGroups.data', 1)
                ->where('classGroups.data.0.professors', [
                    ['id' => $ana->id, 'name' => 'Ana Costa'],
                    ['id' => $carlos->id, 'name' => 'Carlos Alves'],
                ]));
    }

    public function test_editing_without_changing_the_number_is_allowed()
    {
        $admin      = User::factory()->admin()->create();
        $lp1        = Course::factory()->create();
        $ana        = User::factory()->professor()->create();
        $classGroup = ClassGroup::factory()->for($lp1)->hasAttached($ana, [], 'professors')->create();

        $this->actingAs($admin)
            ->patch(route('admin.class-groups.update', $classGroup), ['schedule' => '24T34'] + $this->classGroupData($lp1, $ana))
            ->assertSessionHasNoErrors();

        $this->assertSame('24T34', $classGroup->fresh()->schedule);
    }

    public function test_editing_cannot_take_the_number_of_another_class_group()
    {
        $admin = User::factory()->admin()->create();
        $lp1   = Course::factory()->create();
        $ana   = User::factory()->professor()->create();
        ClassGroup::factory()->for($lp1)->create(['number' => 'T01']);
        $classGroup = ClassGroup::factory()->for($lp1)->create(['number' => 'T02']);

        $this->actingAs($admin)
            ->patch(route('admin.class-groups.update', $classGroup), $this->classGroupData($lp1, $ana))
            ->assertSessionHasErrors(['number' => 'Esta turma já existe neste semestre']);

        $this->assertSame('T02', $classGroup->fresh()->number);
    }

    public function test_a_class_group_of_an_archived_course_keeps_its_course_when_edited()
    {
        $admin      = User::factory()->admin()->create();
        $archived   = Course::factory()->archived()->create();
        $other      = Course::factory()->archived()->create();
        $ana        = User::factory()->professor()->create();
        $classGroup = ClassGroup::factory()->for($archived)->create();

        $this->actingAs($admin)
            ->patch(route('admin.class-groups.update', $classGroup), ['schedule' => '24T34'] + $this->classGroupData($archived, $ana))
            ->assertSessionHasNoErrors();

        $this->assertSame('24T34', $classGroup->fresh()->schedule);

        $this->actingAs($admin)
            ->patch(route('admin.class-groups.update', $classGroup), $this->classGroupData($other, $ana))
            ->assertSessionHasErrors('course_id');

        $this->assertSame($archived->id, $classGroup->fresh()->course_id);
    }

    public function test_editing_removes_a_professor()
    {
        $admin      = User::factory()->admin()->create();
        $lp1        = Course::factory()->create();
        $ana        = User::factory()->professor()->create();
        $carlos     = User::factory()->professor()->create();
        $classGroup = ClassGroup::factory()->for($lp1)->hasAttached([$ana, $carlos], [], 'professors')->create();

        $this->actingAs($admin)
            ->patch(route('admin.class-groups.update', $classGroup), $this->classGroupData($lp1, $carlos))
            ->assertSessionHasNoErrors();

        $this->assertSame([$carlos->id], $classGroup->professors()->pluck('users.id')->all());
    }

    public function test_non_admins_cannot_edit_class_groups()
    {
        $lp1        = Course::factory()->create();
        $ana        = User::factory()->professor()->create();
        $classGroup = ClassGroup::factory()->for($lp1)->create(['schedule' => '35M12']);

        foreach ([User::factory()->create(), $ana] as $user) {
            $this->actingAs($user)
                ->patch(route('admin.class-groups.update', $classGroup), ['schedule' => '24T34'] + $this->classGroupData($lp1, $ana))
                ->assertForbidden();
        }

        $this->assertSame('35M12', $classGroup->fresh()->schedule);
    }
}
