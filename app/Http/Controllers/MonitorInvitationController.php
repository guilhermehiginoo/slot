<?php

namespace App\Http\Controllers;

use App\Actions\Monitoring\RespondToMonitorInvitation;
use App\Enums\MonitorInvitationStatus;
use App\Http\Requests\Monitoring\RespondMonitorInvitationRequest;
use App\Models\MonitorInvitation;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class MonitorInvitationController extends Controller
{
    public function update(RespondMonitorInvitationRequest $request, MonitorInvitation $monitorInvitation, RespondToMonitorInvitation $respond): RedirectResponse
    {
        $status = $request->enum('status', MonitorInvitationStatus::class) ?? MonitorInvitationStatus::Declined;

        $respond->handle($monitorInvitation, $status);

        $message = $status === MonitorInvitationStatus::Accepted ? 'Convite aceito. Agora você é monitor desta turma.' : 'Convite recusado.';
        Inertia::flash('toast', ['type' => 'success', 'message' => $message]);

        return back();
    }
}
