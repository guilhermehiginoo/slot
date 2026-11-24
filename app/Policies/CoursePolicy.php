<?php

namespace App\Policies;

use App\Models\{Course, User};

class CoursePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Course $course): bool
    {
        return $user->isAdmin();
    }

    public function archive(User $user, Course $course): bool
    {
        return $user->isAdmin();
    }
}
