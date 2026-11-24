<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\ArchiveCourse;
use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class CourseArchiveController extends Controller
{
    public function store(Course $course, ArchiveCourse $archiveCourse): RedirectResponse
    {
        Gate::authorize('archive', $course);

        $archiveCourse->handle($course);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Disciplina arquivada com sucesso!']);

        return back();
    }
}
