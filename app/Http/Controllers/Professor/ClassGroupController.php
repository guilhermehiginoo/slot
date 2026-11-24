<?php

namespace App\Http\Controllers\Professor;

use App\Http\Controllers\Controller;
use App\Models\{ClassGroup, ClassMonitor, Course, MonitorApplication, MonitorInvitation, User};
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\{Inertia, Response};

class ClassGroupController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewTaught', ClassGroup::class);

        $classGroups = ClassGroup::query()
            ->select(['id', 'course_id', 'number', 'schedule'])
            ->currentSemester()
            ->taughtBy($request->user())
            ->with('course:id,code,name')
            ->withCount('monitors')
            ->orderBy(Course::select('code')->whereColumn('courses.id', 'class_groups.course_id'))
            ->orderBy('number')
            ->paginate(15)
            ->through(fn (ClassGroup $classGroup) => [
                'id'             => $classGroup->id,
                'number'         => $classGroup->number,
                'schedule'       => $classGroup->schedule,
                'course'         => $classGroup->course->only(['code', 'name']),
                'monitors_count' => $classGroup->monitors_count,
            ]);

        return Inertia::render('professor/class-groups/index', [
            'classGroups'     => $classGroups,
            'currentSemester' => config('slot.current_semester'),
        ]);
    }

    public function show(ClassGroup $classGroup): Response
    {
        Gate::authorize('view', $classGroup);

        $classGroup->load([
            'course:id,code,name',
            'professors' => fn (BelongsToMany $query) => $query->select(['users.id', 'users.name'])->orderBy('users.name'),
        ]);

        $monitors = ClassMonitor::query()
            ->select(['user_id', 'appointed_at'])
            ->where('class_group_id', $classGroup->id)
            ->with('monitor:id,name')
            ->orderBy(User::select('name')->whereColumn('users.id', 'class_monitor.user_id'))
            ->get()
            ->map(fn (ClassMonitor $classMonitor) => [
                'id'           => $classMonitor->user_id,
                'name'         => $classMonitor->monitor->name,
                'appointed_at' => $classMonitor->appointed_at->toIso8601String(),
            ]);

        $invitations = $classGroup->monitorInvitations()
            ->pending()
            ->select(['id', 'user_id', 'created_at'])
            ->with('student:id,name')
            ->latest()
            ->latest('id')
            ->get()
            ->map(fn (MonitorInvitation $invitation) => [
                'id'         => $invitation->id,
                'student'    => $invitation->student->only(['id', 'name']),
                'created_at' => $invitation->created_at?->toIso8601String(),
            ]);

        $applications = $classGroup->monitorApplications()
            ->pending()
            ->select(['id', 'user_id', 'message', 'created_at'])
            ->with('student:id,name')
            ->oldest()
            ->oldest('id')
            ->get()
            ->map(fn (MonitorApplication $application) => [
                'id'         => $application->id,
                'student'    => $application->student->only(['id', 'name']),
                'message'    => $application->message,
                'created_at' => $application->created_at?->toIso8601String(),
            ]);

        return Inertia::render('professor/class-groups/show', [
            'classGroup' => [
                'id'         => $classGroup->id,
                'semester'   => $classGroup->semester,
                'number'     => $classGroup->number,
                'schedule'   => $classGroup->schedule,
                'course'     => $classGroup->course->only(['code', 'name']),
                'professors' => $classGroup->professors->map->only(['id', 'name'])->all(),
            ],
            'monitors'     => $monitors,
            'invitations'  => $invitations,
            'applications' => $applications,
        ]);
    }
}
