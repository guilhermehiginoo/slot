<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
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
}
