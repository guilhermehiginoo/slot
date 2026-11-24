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
                ->component('admin/class-groups/index', false)
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
}
