<?php

namespace Tests\Feature\Courses;

use App\Models\{ClassGroup, Course, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

class ShowCourseTest extends TestCase
{
    use RefreshDatabase;

    private Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        config(['slot.current_semester' => '2026.2']);

        $this->course = Course::factory()->create(['code' => 'IMD0030', 'name' => 'Linguagem de Programação I']);
    }

    #[TestDox('Cenário: Abrir uma disciplina')]
    public function test_shows_current_class_groups(): void
    {
        $ana   = User::factory()->professor()->create(['name' => 'Ana Costa']);
        $bruno = User::factory()->professor()->create(['name' => 'Bruno Lima']);
        $joao  = User::factory()->create(['name' => 'João']);
        $carla = User::factory()->create(['name' => 'Carla']);

        $t02 = ClassGroup::factory()->for($this->course)->create(['number' => 'T02', 'schedule' => '24T12']);
        $t01 = ClassGroup::factory()->for($this->course)->create(['number' => 'T01', 'schedule' => '35M12']);
        $t01->professors()->attach([$bruno->id, $ana->id]);
        $t02->professors()->attach($ana);
        $t01->monitors()->attach($joao, ['appointed_by' => $ana->id, 'appointed_at' => now()]);
        $t01->monitors()->attach($carla, ['appointed_by' => $ana->id, 'appointed_at' => now()]);
        ClassGroup::factory()->for($this->course)->create(['semester' => '2026.1', 'number' => 'T03']);

        $this->actingAs(User::factory()->create())
            ->get(route('courses.show', 'IMD0030'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('courses/show')
                ->where('course.code', 'IMD0030')
                ->where('course.name', 'Linguagem de Programação I')
                ->where('currentSemester', '2026.2')
                ->has('classGroups', 2)
                ->where('classGroups.0', [
                    'id'         => $t01->id,
                    'number'     => 'T01',
                    'schedule'   => '35M12',
                    'professors' => ['Ana Costa', 'Bruno Lima'],
                    'monitors'   => ['Carla', 'João'],
                ])
                ->where('classGroups.1.number', 'T02')
                ->where('classGroups.1.monitors', []));
    }

    #[TestDox('Cenário: Página de disciplina arquivada')]
    public function test_archived_is_not_found(): void
    {
        Course::factory()->archived()->create(['code' => 'IMD0040']);

        $this->actingAs(User::factory()->admin()->create())
            ->get('/disciplinas/IMD0040')
            ->assertNotFound();

        $this->get('/disciplinas/XXX9999')->assertNotFound();
    }

    public function test_props_never_carry_emails(): void
    {
        $classGroup = ClassGroup::factory()->for($this->course)->create();
        $classGroup->professors()->attach(User::factory()->professor()->create());

        $response = $this->actingAs(User::factory()->create())->get(route('courses.show', 'IMD0030'));

        $this->assertStringNotContainsString('@', json_encode($response->viewData('page')['props']['classGroups']));
    }

    #[TestDox('Cenário: Descrição da disciplina')]
    public function test_shows_description(): void
    {
        $this->course->update(['description' => "Introdução à programação.\nCom C++."]);

        $this->actingAs(User::factory()->create())
            ->get(route('courses.show', 'IMD0030'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('course.description', "Introdução à programação.\nCom C++."));
    }

    #[TestDox('Cenário: Professores que já deram a disciplina')]
    public function test_lists_past_professors(): void
    {
        $ana    = User::factory()->professor()->create(['name' => 'Ana Costa']);
        $bruno  = User::factory()->professor()->create(['name' => 'Bruno Lima']);
        $carlos = User::factory()->professor()->create(['name' => 'Carlos Alves']);
        $diana  = User::factory()->professor()->create(['name' => 'Diana Reis']);

        ClassGroup::factory()->for($this->course)->create(['semester' => '2025.2'])->professors()->attach([$bruno->id, $ana->id]);
        ClassGroup::factory()->for($this->course)->create(['semester' => '2026.1'])->professors()->attach($bruno);
        ClassGroup::factory()->for($this->course)->create(['semester' => '2026.2'])->professors()->attach([$carlos->id, $ana->id]);
        ClassGroup::factory()->for($this->course)->create(['semester' => '2027.1'])->professors()->attach($diana);
        ClassGroup::factory()->create(['semester' => '2025.1'])->professors()->attach($diana);

        $this->actingAs(User::factory()->create())
            ->get(route('courses.show', 'IMD0030'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('pastProfessors', ['Ana Costa', 'Bruno Lima']));
    }
}
