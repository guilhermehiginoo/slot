<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\{CreateCourse, UpdateCourse};
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveCourseRequest;
use App\Models\Course;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\Gate;
use Inertia\{Inertia, Response};

class CourseController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Course::class);

        $search = $request->string('search')->trim()->value() ?: null;

        $courses = Course::query()
            ->select(['id', 'code', 'name', 'archived_at'])
            ->when($search, fn (Builder $query, string $search) => $query->where(
                fn (Builder $query) => $query
                    ->whereLike('code', "%{$search}%")
                    ->orWhereLike('name', "%{$search}%"),
            ))
            ->orderBy('code')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/courses/index', [
            'courses' => $courses,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Course::class);

        return Inertia::render('admin/courses/create');
    }

    public function store(SaveCourseRequest $request, CreateCourse $createCourse): RedirectResponse
    {
        $createCourse->handle(
            $request->string('code')->value(),
            $request->string('name')->value(),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Disciplina salva com sucesso!']);

        return to_route('admin.courses.index');
    }

    public function edit(Course $course): Response
    {
        Gate::authorize('update', $course);

        return Inertia::render('admin/courses/edit', [
            'course' => $course->only(['id', 'code', 'name']),
        ]);
    }

    public function update(SaveCourseRequest $request, Course $course, UpdateCourse $updateCourse): RedirectResponse
    {
        $updateCourse->handle(
            $course,
            $request->string('code')->value(),
            $request->string('name')->value(),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Disciplina salva com sucesso!']);

        return to_route('admin.courses.index');
    }
}
