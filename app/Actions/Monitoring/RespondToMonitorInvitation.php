<?php

namespace App\Actions\Monitoring;

use App\Enums\MonitorInvitationStatus;
use App\Models\MonitorInvitation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RespondToMonitorInvitation
{
    /**
     * @throws ValidationException
     */
    public function handle(MonitorInvitation $invitation, MonitorInvitationStatus $status): void
    {
        DB::transaction(function () use ($invitation, $status) {
            $pending = MonitorInvitation::whereKey($invitation->id)->pending()->lockForUpdate()->first();

            if ($pending === null) {
                throw ValidationException::withMessages(['invitation' => 'Este convite não está mais pendente.']);
            }

            $pending->update(['status' => $status]);

            if ($status === MonitorInvitationStatus::Accepted) {
                $pending->classGroup()->firstOrFail()->monitors()->syncWithoutDetaching([
                    $pending->user_id => ['appointed_by' => $pending->invited_by, 'appointed_at' => now()],
                ]);
            }
        });
    }
}
