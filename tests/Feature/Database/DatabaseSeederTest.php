<?php

namespace Tests\Feature\Database;

use App\Enums\Role;
use App\Models\{ClassGroup, Course, MonitorApplication, MonitorInvitation, User};
use Database\Seeders\{ClassGroupSeeder, CourseSeeder, MonitorSeeder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_the_users_of_each_role()
    {
        $this->seed();

        $this->assertSame(
            [
                'admin@slot.test'         => Role::Admin,
                'ana.costa@slot.test'     => Role::Professor,
                'beatriz.lima@slot.test'  => Role::Student,
                'carlos.alves@slot.test'  => Role::Professor,
                'joao.silva@slot.test'    => Role::Student,
                'lucas.almeida@slot.test' => Role::Student,
                'marina.santos@slot.test' => Role::Student,
                'professor@slot.test'     => Role::Professor,
                'student@slot.test'       => Role::Student,
            ],
            User::orderBy('email')->pluck('role', 'email')->all(),
        );
    }

    public function test_seeder_imports_the_imd_courses_from_sigaa()
    {
        $this->seed();

        $data = json_decode((string) file_get_contents(database_path('seeders/data/courses.json')), true);

        $this->assertGreaterThan(500, count($data));
        $this->assertSame(count($data), Course::count());
        $this->assertSame(
            [
                'IMD0012' => 'Introdução às Técnicas de Programação',
                'IMD0029' => 'Estrutura de Dados Básicas I',
                'IMD0030' => 'Linguagem de Programação I',
                'IMD0040' => 'Linguagem de Programação II',
            ],
            Course::whereIn('code', ['IMD0012', 'IMD0029', 'IMD0030', 'IMD0040'])->orderBy('code')->pluck('name', 'code')->all(),
        );

        $course = Course::firstWhere('code', 'IMD0030');
        $this->assertSame(60, $course->workload_hours);
        $this->assertNull($course->description);
        $this->assertSame('linguagem de programacao i', $course->search_name);
    }

    public function test_running_the_seeder_twice_does_not_duplicate_courses()
    {
        $this->seed(CourseSeeder::class);
        $count = Course::count();

        $this->seed(CourseSeeder::class);

        $this->assertSame($count, Course::count());
    }

    public function test_seeder_creates_the_class_groups_of_the_current_semester()
    {
        $this->seed();

        $this->assertSame(
            [
                'IMD0012 T01' => ['Ana Costa'],
                'IMD0029 T01' => ['Carlos Alves'],
                'IMD0030 T01' => ['Ana Costa', 'Carlos Alves', 'Professor'],
                'IMD0030 T02' => ['Carlos Alves'],
            ],
            $this->seededClassGroups(),
        );
        $this->assertSame(4, ClassGroup::where('semester', '2026.2')->count());
    }

    public function test_running_the_class_group_seeder_twice_does_not_duplicate_anything()
    {
        $this->seed(CourseSeeder::class);
        $this->seed(ClassGroupSeeder::class);
        $this->seed(ClassGroupSeeder::class);

        $this->assertSame(3, User::count());
        $this->assertSame(4, ClassGroup::count());
        $this->assertSame(6, DB::table('class_group_user')->count());
    }

    public function test_seeder_creates_monitors_an_invitation_and_an_application_in_imd0030_t01()
    {
        $this->seed();

        $t01 = $this->imd0030T01();

        $this->assertSame(
            ['João Silva', 'Marina Santos'],
            $t01->monitors()->orderBy('name')->pluck('name')->all(),
        );
        $this->assertSame(
            ['Lucas Almeida'],
            User::whereIn('id', $t01->monitorInvitations()->pending()->select('user_id'))->pluck('name')->all(),
        );
        $this->assertSame(
            ['Beatriz Lima'],
            User::whereIn('id', $t01->monitorApplications()->pending()->select('user_id'))->pluck('name')->all(),
        );
        $this->assertSame(1, MonitorInvitation::count());
        $this->assertSame(1, MonitorApplication::count());
    }

    public function test_running_the_monitor_seeder_twice_does_not_duplicate_anything()
    {
        $this->seed(CourseSeeder::class);
        $this->seed(ClassGroupSeeder::class);
        $this->seed(MonitorSeeder::class);
        $this->seed(MonitorSeeder::class);

        $this->assertSame(7, User::count());
        $this->assertSame(2, DB::table('class_monitor')->count());
        $this->assertSame(1, MonitorInvitation::count());
        $this->assertSame(1, MonitorApplication::count());
    }

    private function imd0030T01(): ClassGroup
    {
        return ClassGroup::query()
            ->whereRelation('course', 'code', 'IMD0030')
            ->where('semester', '2026.2')
            ->where('number', 'T01')
            ->firstOrFail();
    }

    /**
     * @return array<string, list<string>>
     */
    private function seededClassGroups(): array
    {
        return ClassGroup::query()
            ->with(['course:id,code', 'professors:id,name'])
            ->get()
            ->mapWithKeys(fn (ClassGroup $classGroup) => [
                "{$classGroup->course->code} {$classGroup->number}" => $classGroup->professors->pluck('name')->sort()->values()->all(),
            ])
            ->sortKeys()
            ->all();
    }
}
