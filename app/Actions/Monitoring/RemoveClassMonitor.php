<?php

namespace App\Actions\Monitoring;

use App\Models\{ClassGroup, User};

class RemoveClassMonitor
{
    public function handle(ClassGroup $classGroup, User $monitor): void
    {
        $classGroup->monitors()->detach($monitor->id);
    }
}
