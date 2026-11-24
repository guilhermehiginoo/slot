<?php

namespace App\Actions\Admin;

use App\Models\Course;

class ArchiveCourse
{
    public function handle(Course $course): void
    {
        if ($course->isArchived()) {
            return;
        }

        $course->forceFill(['archived_at' => now()])->save();
    }
}
