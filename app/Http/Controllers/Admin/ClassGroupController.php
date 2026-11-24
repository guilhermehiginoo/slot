<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\{CreateClassGroup, UpdateClassGroup};
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveClassGroupRequest;
use App\Models\{ClassGroup, Course, User};
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\Gate;
use Inertia\{Inertia, Response};

class ClassGroupController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', ClassGroup::class);

        $currentSemester = config('slot.current_semester');
        $course          = $request->integer('course') ?: null;
        $semester        = $request->string('semester')->trim()->value() ?: $currentSemester;

        $classGroups = ClassGroup::query()
            ->select(['id', 'course_id', 'semester', 'number', 'schedule'])
            ->with([
                'course:id,code,name',
                'professors' => fn (BelongsToMany $query) => $query->select(['users.id', 'users.name'])->orderBy('users.name'),
            ])
            ->where('semester', $semester)
            ->when($course, fn ($query, int $course) => $query->where('course_id', $course))
            ->orderBy(Course::select('code')->whereColumn('courses.id', 'class_groups.course_id'))
            ->orderBy('number')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (ClassGroup $classGroup) => [
                'id'         => $classGroup->id,
                'semester'   => $classGroup->semester,
                'number'     => $classGroup->number,
                'schedule'   => $classGroup->schedule,
                'course'     => $classGroup->course->only(['id', 'code', 'name']),
                'professors' => $classGroup->professors->map->only(['id', 'name'])->all(),
            ]);

        return Inertia::render('admin/class-groups/index', [
            'classGroups'    => $classGroups,
            'filters'        => ['course' => $course, 'semester' => $semester],
            'hasClassGroups' => ClassGroup::query()->exists(),
            'courses'        => Course::query()->select(['id', 'code', 'name'])->whereNull('archived_at')->orderBy('code')->get(),
            'professors'     => User::query()->select(['id', 'name'])->where('role', Role::Professor)->orderBy('name')->get(),
            'semesters'      => ClassGroup::query()->distinct()->pluck('semester')
                ->push($currentSemester)->unique()->sortDesc()->values(),
        ]);
    }

    public function store(SaveClassGroupRequest $request, CreateClassGroup $createClassGroup): RedirectResponse
    {
        $createClassGroup->handle($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Turma salva com sucesso!']);

        return to_route('admin.class-groups.index');
    }

    public function update(SaveClassGroupRequest $request, ClassGroup $classGroup, UpdateClassGroup $updateClassGroup): RedirectResponse
    {
        $updateClassGroup->handle($classGroup, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Turma salva com sucesso!']);

        return to_route('admin.class-groups.index');
    }
}
