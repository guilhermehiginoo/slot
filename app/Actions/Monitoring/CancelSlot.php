<?php

namespace App\Actions\Monitoring;

use App\Models\{Slot, User};
use Illuminate\Support\Facades\DB;

class CancelSlot
{
    public function handle(Slot $slot): void
    {
        DB::transaction(function () use ($slot): void {
            User::query()->lockForUpdate()->findOrFail($slot->monitor_id);
            Slot::query()->active()->whereKey($slot->id)->update(['cancelled_at' => now()]);
        }, attempts: 3);
    }
}
