<?php

namespace Tests\Feature\Professor;

use App\Models\{ClassGroup, Course, MonitorApplication, MonitorInvitation, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

class ProfessorClassGroupsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['slot.current_semester' => '2026.2']);
    }

    #[TestDox('Cenário: O professor vê só as próprias turmas')]
    public function test_professors_only_see_their_own_class_groups()
    {
        $ana    = User::factory()->professor()->create();
        $carlos = User::factory()->professor()->create();
        $lp1    = Course::factory()->create(['code' => 'IMD0030', 'name' => 'Linguagem de Programação I']);
        $t01    = ClassGroup::factory()->for($lp1)->create(['number' => 'T01', 'schedule' => '35M12']);
        $t02    = ClassGroup::factory()->for($lp1)->create(['number' => 'T02']);
        $t01->professors()->attach([$ana->id, $carlos->id]);
        $t02->professors()->attach($carlos);

        $this->actingAs($ana)
            ->get(route('professor.class-groups.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('professor/class-groups/index')
                ->where('currentSemester', '2026.2')
                ->has('classGroups.data', 1)
                ->where('classGroups.data.0', [
                    'id'             => $t01->id,
                    'number'         => 'T01',
                    'schedule'       => '35M12',
                    'course'         => ['code' => 'IMD0030', 'name' => 'Linguagem de Programação I'],
                    'monitors_count' => 0,
                ]));
    }

    public function test_only_class_groups_of_the_current_semester_are_listed()
    {
        $ana = User::factory()->professor()->create();
        ClassGroup::factory()->create(['semester' => '2026.1'])->professors()->attach($ana);
        $current = ClassGroup::factory()->create(['semester' => '2026.2']);
        $current->professors()->attach($ana);

        $this->actingAs($ana)
            ->get('/professor/turmas')
            ->assertInertia(fn (Assert $page) => $page
                ->has('classGroups.data', 1)
                ->where('classGroups.data.0.id', $current->id));
    }

    public function test_each_class_group_shows_how_many_monitors_it_has()
    {
        $ana = User::factory()->professor()->create();
        $lp1 = Course::factory()->create(['code' => 'IMD0030']);
        $t01 = ClassGroup::factory()->for($lp1)->create(['number' => 'T01']);
        $t02 = ClassGroup::factory()->for($lp1)->create(['number' => 'T02']);
        $t01->professors()->attach($ana);
        $t02->professors()->attach($ana);
        $t01->monitors()->attach(User::factory()->count(2)->create(), ['appointed_by' => $ana->id, 'appointed_at' => now()]);
        $t02->monitors()->attach(User::factory()->create(), ['appointed_by' => $ana->id, 'appointed_at' => now()]);

        $this->actingAs($ana)
            ->get(route('professor.class-groups.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('classGroups.data.0.number', 'T01')
                ->where('classGroups.data.0.monitors_count', 2)
                ->where('classGroups.data.1.number', 'T02')
                ->where('classGroups.data.1.monitors_count', 1));
    }

    public function test_class_groups_are_ordered_by_course_code_and_number_with_15_per_page()
    {
        $ana         = User::factory()->professor()->create();
        $lp1         = Course::factory()->create(['code' => 'IMD0030']);
        $itp         = Course::factory()->create(['code' => 'IMD0012']);
        $classGroups = collect([
            ClassGroup::factory()->for($lp1)->create(['number' => 'T02']),
            ClassGroup::factory()->for($lp1)->create(['number' => 'T01']),
            ClassGroup::factory()->for($itp)->create(['number' => 'T03']),
        ]);

        foreach (range(10, 22) as $number) {
            $classGroups->push(ClassGroup::factory()->for(Course::factory()->create(['code' => "IMD99{$number}"]))->create());
        }

        $classGroups->each(fn (ClassGroup $classGroup) => $classGroup->professors()->attach($ana));

        $this->actingAs($ana)
            ->get(route('professor.class-groups.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('classGroups.data', 15)
                ->where('classGroups.total', 16)
                ->where('classGroups.data.0.course.code', 'IMD0012')
                ->where('classGroups.data.1.course.code', 'IMD0030')
                ->where('classGroups.data.1.number', 'T01')
                ->where('classGroups.data.2.number', 'T02'));
    }

    #[TestDox('Cenário: Professor abre a turma de outro professor')]
    public function test_professors_cannot_open_class_groups_they_do_not_teach()
    {
        $ana    = User::factory()->professor()->create();
        $carlos = User::factory()->professor()->create();
        $lp1    = Course::factory()->create(['code' => 'IMD0030']);
        $t02    = ClassGroup::factory()->for($lp1)->create(['number' => 'T02']);
        $t02->professors()->attach($carlos);

        $this->actingAs($ana)
            ->get("/professor/turmas/{$t02->id}")
            ->assertForbidden();

        $this->actingAs($carlos)
            ->get(route('professor.class-groups.show', $t02))
            ->assertOk();
    }

    public function test_the_class_group_page_lists_monitors_pending_invitations_and_pending_applications()
    {
        $ana = User::factory()->professor()->create(['name' => 'Ana Costa']);
        $lp1 = Course::factory()->create(['code' => 'IMD0030', 'name' => 'Linguagem de Programação I']);
        $t01 = ClassGroup::factory()->for($lp1)->create(['number' => 'T01', 'schedule' => '35M12']);
        $t01->professors()->attach($ana);
        $other = ClassGroup::factory()->for($lp1)->create(['number' => 'T02']);

        $marina = User::factory()->create(['name' => 'Marina Santos']);
        $joao   = User::factory()->create(['name' => 'João Silva']);
        $t01->monitors()->attach($marina, ['appointed_by' => $ana->id, 'appointed_at' => '2026-11-20 10:00:00']);
        $t01->monitors()->attach($joao, ['appointed_by' => $ana->id, 'appointed_at' => '2026-11-18 09:00:00']);
        $other->monitors()->attach(User::factory()->create(), ['appointed_by' => $ana->id, 'appointed_at' => now()]);

        $lucas = User::factory()->create(['name' => 'Lucas Almeida']);
        $pedro = User::factory()->create(['name' => 'Pedro Rocha']);
        $older = MonitorInvitation::factory()->for($t01)->for($pedro, 'student')->create(['created_at' => '2026-11-19 08:00:00']);
        $newer = MonitorInvitation::factory()->for($t01)->for($lucas, 'student')->create(['created_at' => '2026-11-21 08:00:00']);
        MonitorInvitation::factory()->for($t01)->declined()->create();
        MonitorInvitation::factory()->for($t01)->accepted()->create();
        MonitorInvitation::factory()->for($t01)->revoked()->create();
        MonitorInvitation::factory()->for($other)->create();

        $beatriz = User::factory()->create(['name' => 'Beatriz Lima']);
        $rafa    = User::factory()->create(['name' => 'Rafaela Dias']);
        $first   = MonitorApplication::factory()->for($t01)->for($beatriz, 'student')
            ->create(['message' => 'Tirei 10 na disciplina.', 'created_at' => '2026-11-17 08:00:00']);
        $second = MonitorApplication::factory()->for($t01)->for($rafa, 'student')
            ->create(['message' => null, 'created_at' => '2026-11-22 08:00:00']);
        MonitorApplication::factory()->for($t01)->approved()->create();
        MonitorApplication::factory()->for($t01)->rejected()->create();
        MonitorApplication::factory()->for($other)->create();

        $this->actingAs($ana)
            ->get(route('professor.class-groups.show', $t01))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('professor/class-groups/show')
                ->where('classGroup', [
                    'id'         => $t01->id,
                    'semester'   => '2026.2',
                    'number'     => 'T01',
                    'schedule'   => '35M12',
                    'course'     => ['code' => 'IMD0030', 'name' => 'Linguagem de Programação I'],
                    'professors' => [['id' => $ana->id, 'name' => 'Ana Costa']],
                ])
                ->where('monitors', [
                    ['id' => $joao->id, 'name' => 'João Silva', 'appointed_at' => '2026-11-18T09:00:00+00:00'],
                    ['id' => $marina->id, 'name' => 'Marina Santos', 'appointed_at' => '2026-11-20T10:00:00+00:00'],
                ])
                ->where('invitations', [
                    ['id' => $newer->id, 'student' => ['id' => $lucas->id, 'name' => 'Lucas Almeida'], 'created_at' => '2026-11-21T08:00:00+00:00'],
                    ['id' => $older->id, 'student' => ['id' => $pedro->id, 'name' => 'Pedro Rocha'], 'created_at' => '2026-11-19T08:00:00+00:00'],
                ])
                ->where('applications', [
                    ['id' => $first->id, 'student' => ['id' => $beatriz->id, 'name' => 'Beatriz Lima'], 'message' => 'Tirei 10 na disciplina.', 'created_at' => '2026-11-17T08:00:00+00:00'],
                    ['id' => $second->id, 'student' => ['id' => $rafa->id, 'name' => 'Rafaela Dias'], 'message' => null, 'created_at' => '2026-11-22T08:00:00+00:00'],
                ]));
    }

    public function test_the_class_group_page_lists_professors_by_name()
    {
        $carlos = User::factory()->professor()->create(['name' => 'Carlos Alves']);
        $ana    = User::factory()->professor()->create(['name' => 'Ana Costa']);
        $t01    = ClassGroup::factory()->create();
        $t01->professors()->attach([$carlos->id, $ana->id]);

        $this->actingAs($carlos)
            ->get(route('professor.class-groups.show', $t01))
            ->assertInertia(fn (Assert $page) => $page
                ->where('classGroup.professors', [
                    ['id' => $ana->id, 'name' => 'Ana Costa'],
                    ['id' => $carlos->id, 'name' => 'Carlos Alves'],
                ])
                ->where('monitors', [])
                ->where('invitations', [])
                ->where('applications', []));
    }

    public function test_professors_open_their_class_groups_of_other_semesters()
    {
        $ana  = User::factory()->professor()->create();
        $past = ClassGroup::factory()->create(['semester' => '2026.1']);
        $past->professors()->attach($ana);

        $this->actingAs($ana)
            ->get(route('professor.class-groups.show', $past))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('classGroup.semester', '2026.1'));
    }

    public function test_only_professors_who_teach_the_class_group_open_its_page()
    {
        $classGroup      = ClassGroup::factory()->create();
        $formerProfessor = User::factory()->create();
        $classGroup->professors()->attach($formerProfessor);

        $this->get(route('professor.class-groups.show', $classGroup))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('professor.class-groups.show', $classGroup))
            ->assertForbidden();

        $this->actingAs($formerProfessor)
            ->get(route('professor.class-groups.show', $classGroup))
            ->assertForbidden();
    }

    public function test_only_professors_open_my_class_groups()
    {
        $this->get(route('professor.class-groups.index'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('professor.class-groups.index'))
            ->assertForbidden();

        $this->actingAs(User::factory()->create())
            ->get(route('professor.class-groups.index'))
            ->assertForbidden();
    }
}
