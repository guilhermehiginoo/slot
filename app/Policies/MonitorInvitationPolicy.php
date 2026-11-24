<?php

namespace App\Policies;

use App\Models\{MonitorInvitation, User};

class MonitorInvitationPolicy
{
    public function revoke(User $user, MonitorInvitation $invitation): bool
    {
        return $user->can('manageMonitors', $invitation->classGroup);
    }

    public function respond(User $user, MonitorInvitation $invitation): bool
    {
        return $invitation->user_id === $user->id;
    }
}
