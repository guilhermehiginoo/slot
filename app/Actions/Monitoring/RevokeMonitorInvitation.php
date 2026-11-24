<?php

namespace App\Actions\Monitoring;

use App\Enums\MonitorInvitationStatus;
use App\Models\MonitorInvitation;
use Illuminate\Validation\ValidationException;

class RevokeMonitorInvitation
{
    /**
     * @throws ValidationException
     */
    public function handle(MonitorInvitation $invitation): void
    {
        $revoked = MonitorInvitation::whereKey($invitation->id)
            ->pending()
            ->update(['status' => MonitorInvitationStatus::Revoked]);

        if ($revoked === 0) {
            throw ValidationException::withMessages(['invitation' => 'Este convite não está mais pendente.']);
        }
    }
}
