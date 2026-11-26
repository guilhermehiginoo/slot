<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CourseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        /** @var list<array{code: string, name: string, workload_hours: int|null}> $courses */
        $courses = File::json(database_path('seeders/data/courses.json'));

        foreach ($courses as $course) {
            Course::query()->updateOrCreate(
                ['code' => $course['code']],
                ['name' => $course['name'], 'workload_hours' => $course['workload_hours']],
            );
        }
    }
}
