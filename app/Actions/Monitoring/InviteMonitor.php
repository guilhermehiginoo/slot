<?php

namespace App\Actions\Monitoring;

use App\Models\{ClassGroup, MonitorInvitation, User};
use App\Notifications\MonitorInvitationReceived;

class InviteMonitor
{
    public function handle(User $professor, ClassGroup $classGroup, User $student): MonitorInvitation
    {
        $invitation = $classGroup->monitorInvitations()->create([
            'user_id'    => $student->id,
            'invited_by' => $professor->id,
        ]);

        $student->notify(new MonitorInvitationReceived($invitation));

        return $invitation;
    }
}
