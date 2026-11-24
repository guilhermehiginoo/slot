<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
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
}