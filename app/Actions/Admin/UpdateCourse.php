<?php

namespace App\Actions\Admin;

use App\Models\Course;

class UpdateCourse
{
    public function handle(Course $course, string $code, string $name, ?string $description): Course
    {
        $course->update(['code' => $code, 'name' => $name, 'description' => $description]);

        return $course;
    }
}
