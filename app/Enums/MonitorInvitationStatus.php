<?php

namespace App\Enums;

enum MonitorInvitationStatus: string
{
    case Pending  = 'pending';
    case Accepted = 'accepted';
    case Declined = 'declined';
    case Revoked  = 'revoked';
}
