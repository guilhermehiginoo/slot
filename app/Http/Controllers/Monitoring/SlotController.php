<?php

namespace App\Http\Controllers\Monitoring;

use App\Actions\Monitoring\{CancelSlot, SaveSlot};
use App\Http\Controllers\Controller;
use App\Http\Requests\Monitoring\SaveSlotRequest;
use App\Models\{ClassGroup, Slot};
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\Gate;
use Inertia\{Inertia, Response};

class SlotController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Slot::class);

        $monitor = $request->user();
        $groups  = $monitor->monitoredClassGroups()->with('course:id,code,name')
            ->orderBy('semester', 'desc')->orderBy('class_groups.id')->get();
        $slots = Slot::query()->where('monitor_id', $monitor->id)
            ->with('classGroup.course:id,code,name')->orderByDesc('starts_at')->paginate(20);

        return Inertia::render('monitoring/slots/index', [
            'groups' => $groups->map(fn (ClassGroup $group) => [
                'id'    => $group->id,
                'label' => "{$group->course->code} {$group->number} · {$group->semester}",
            ]),
            'slots' => $slots->through(fn (Slot $slot) => [
                'id'        => $slot->id,
                'group'     => "{$slot->classGroup->course->code} {$slot->classGroup->number}",
                'group_id'  => $slot->class_group_id,
                'starts_at' => $slot->starts_at->setTimezone(Slot::TIMEZONE)->format('Y-m-d\TH:i'),
                'ends_at'   => $slot->ends_at->setTimezone(Slot::TIMEZONE)->format('Y-m-d\TH:i'),
                'location'  => $slot->location,
                'capacity'  => $slot->capacity,
                'cancelled' => $slot->cancelled_at !== null,
                'editable'  => $slot->cancelled_at === null && $groups->contains('id', $slot->class_group_id),
            ]),
        ]);
    }

    public function store(SaveSlotRequest $request, ClassGroup $classGroup, SaveSlot $saveSlot): RedirectResponse
    {
        $count = $saveSlot->handle($request->user(), $classGroup, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => $count === 1 ? 'Horário publicado.' : "{$count} horários publicados."]);

        return to_route('monitor.slots.index');
    }

    public function update(SaveSlotRequest $request, Slot $slot, SaveSlot $saveSlot): RedirectResponse
    {
        $saveSlot->handle($request->user(), $slot->classGroup, $request->validated(), $slot);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Horário atualizado.']);

        return to_route('monitor.slots.index');
    }

    public function destroy(Slot $slot, CancelSlot $cancelSlot): RedirectResponse
    {
        Gate::authorize('delete', $slot);

        $cancelSlot->handle($slot);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Horário cancelado.']);

        return to_route('monitor.slots.index');
    }
}
