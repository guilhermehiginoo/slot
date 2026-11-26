<?php

namespace App\Policies;

use App\Models\{Course, User};
use Illuminate\Auth\Access\Response;

class CoursePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Course $course): Response
    {
        return $course->isArchived() ? Response::denyAsNotFound() : Response::allow();
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
