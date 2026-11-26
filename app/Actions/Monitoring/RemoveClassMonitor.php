<?php

namespace App\Actions\Monitoring;

use App\Models\{ClassGroup, Slot, User};
use Illuminate\Support\Facades\DB;

class RemoveClassMonitor
{
    public function handle(ClassGroup $classGroup, User $monitor): void
    {
        DB::transaction(function () use ($classGroup, $monitor): void {
            User::query()->lockForUpdate()->findOrFail($monitor->id);
            $classGroup->monitors()->detach($monitor->id);

            $now = now();
            Slot::query()->active()
                ->where('class_group_id', $classGroup->id)
                ->where('monitor_id', $monitor->id)
                ->where('starts_at', '>', $now)
                ->update(['cancelled_at' => $now]);
        }, attempts: 3);
    }
}
