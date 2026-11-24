<?php

namespace App\Http\Controllers\Professor;

use App\Enums\{MonitorInvitationStatus, Role};
use App\Http\Controllers\Controller;
use App\Models\{ClassGroup, ClassMonitor, Course, MonitorApplication, MonitorInvitation, User};
use Illuminate\Database\Eloquent\Builder;
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

    public function show(Request $request, ClassGroup $classGroup): Response
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
            ->whereNot('status', MonitorInvitationStatus::Accepted)
            ->select(['id', 'user_id', 'status', 'created_at'])
            ->with('student:id,name')
            ->latest()
            ->latest('id')
            ->get()
            ->map(fn (MonitorInvitation $invitation) => [
                'id'         => $invitation->id,
                'student'    => $invitation->student->only(['id', 'name']),
                'status'     => $invitation->status,
                'created_at' => $invitation->created_at?->toIso8601String(),
            ]);

        $search = $request->string('busca')->trim()->value();

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
            'search'       => $search,
            'candidates'   => mb_strlen($search) < 2 ? [] : $this->candidates($classGroup, $search),
        ]);
    }

    /**
     * @return array<int, array{id: int, name: string, email: string}>
     */
    private function candidates(ClassGroup $classGroup, string $search): array
    {
        return User::query()
            ->select(['id', 'name', 'email'])
            ->where('role', Role::Student)
            ->where(fn (Builder $query) => $query
                ->whereLike('name', "%{$search}%")
                ->orWhereLike('email', "%{$search}%"))
            ->whereDoesntHave('monitoredClassGroups', fn (Builder $query) => $query->whereKey($classGroup->id))
            ->whereDoesntHave('monitorInvitations', fn (Builder $query) => $query
                ->where('class_group_id', $classGroup->id)
                ->where('status', MonitorInvitationStatus::Pending))
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(fn (User $user) => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email])
            ->all();
    }
}
