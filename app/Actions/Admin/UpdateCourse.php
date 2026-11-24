<?php

namespace App\Actions\Admin;

use App\Models\Course;

class UpdateCourse
{
    public function handle(Course $course, string $code, string $name): Course
    {
        $course->update(['code' => $code, 'name' => $name]);

        return $course;
    }
}
