<?php

namespace App\Http\Controllers;

use App\Models\MonitorInvitation;
use Illuminate\Http\Request;
use Inertia\{Inertia, Response};

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $monitorInvitations = $request->user()->monitorInvitations()
            ->pending()
            ->select(['id', 'class_group_id', 'invited_by', 'created_at'])
            ->with(['classGroup:id,course_id,number', 'classGroup.course:id,code,name', 'invitedBy:id,name'])
            ->latest()
            ->latest('id')
            ->get()
            ->map(fn (MonitorInvitation $invitation) => [
                'id'         => $invitation->id,
                'classGroup' => [
                    'number' => $invitation->classGroup->number,
                    'course' => $invitation->classGroup->course->only(['code', 'name']),
                ],
                'invitedBy'  => $invitation->invitedBy->name,
                'created_at' => $invitation->created_at?->toIso8601String(),
            ]);

        return Inertia::render('dashboard', [
            'monitorInvitations' => $monitorInvitations,
        ]);
    }
}
