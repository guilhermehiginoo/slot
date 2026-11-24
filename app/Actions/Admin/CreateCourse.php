<?php

namespace App\Actions\Admin;

use App\Models\Course;

class CreateCourse
{
    public function handle(string $code, string $name): Course
    {
        return Course::create(['code' => $code, 'name' => $name]);
    }
}