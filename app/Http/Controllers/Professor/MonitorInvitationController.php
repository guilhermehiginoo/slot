<?php

namespace App\Http\Controllers\Professor;

use App\Actions\Monitoring\{InviteMonitor, RevokeMonitorInvitation};
use App\Http\Controllers\Controller;
use App\Http\Requests\Professor\InviteMonitorRequest;
use App\Models\{ClassGroup, MonitorInvitation, User};
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class MonitorInvitationController extends Controller
{
    public function store(InviteMonitorRequest $request, ClassGroup $classGroup, InviteMonitor $inviteMonitor): RedirectResponse
    {
        $inviteMonitor->handle($request->user(), $classGroup, User::findOrFail($request->integer('user_id')));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Convite enviado.']);

        return back();
    }

    public function destroy(MonitorInvitation $monitorInvitation, RevokeMonitorInvitation $revokeMonitorInvitation): RedirectResponse
    {
        Gate::authorize('revoke', $monitorInvitation);

        $revokeMonitorInvitation->handle($monitorInvitation);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Convite revogado.']);

        return back();
    }
}
