<?php

namespace App\Http\Controllers;

use App\Models\{ClassGroup, Course, User};
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\{Inertia, Response};

class CourseController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->value() ?: null;

        $courses = Course::query()
            ->active()
            ->search($search)
            ->select(['code', 'name'])
            ->orderBy('code')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('courses/index', [
            'courses' => $courses,
            'filters' => ['search' => $search],
        ]);
    }

    public function show(Course $course): Response
    {
        Gate::authorize('view', $course);

        $people = fn (BelongsToMany $query) => $query->select(['users.id', 'users.name'])->orderBy('users.name');

        $classGroups = $course->classGroups()
            ->currentSemester()
            ->select(['id', 'course_id', 'number', 'schedule'])
            ->with(['professors' => $people, 'monitors' => $people])
            ->orderBy('number')
            ->get()
            ->map(fn (ClassGroup $classGroup) => [
                'id'         => $classGroup->id,
                'number'     => $classGroup->number,
                'schedule'   => $classGroup->schedule,
                'professors' => $classGroup->professors->map(fn (User $user) => $user->name)->all(),
                'monitors'   => $classGroup->monitors->map(fn (User $user) => $user->name)->all(),
            ]);

        return Inertia::render('courses/show', [
            'course'      => $course->only(['code', 'name']),
            'classGroups' => $classGroups,
        ]);
    }
}
