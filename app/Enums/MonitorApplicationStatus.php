<?php

namespace App\Enums;

enum MonitorApplicationStatus: string
{
    case Pending  = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
