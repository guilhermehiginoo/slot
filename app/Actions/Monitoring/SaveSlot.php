<?php

namespace App\Actions\Monitoring;

use App\Models\{ClassGroup, Slot, User};
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\{DB, Gate};
use Illuminate\Validation\ValidationException;

class SaveSlot
{
    /** @param array<string, mixed> $data */
    public function handle(User $monitor, ClassGroup $classGroup, array $data, ?Slot $slot = null): int
    {
        $startsAt = CarbonImmutable::parse((string) $data['starts_at'], Slot::TIMEZONE);
        $endsAt   = CarbonImmutable::parse((string) $data['ends_at'], Slot::TIMEZONE);
        $weeks    = (int) ($data['weeks'] ?? 1);

        return DB::transaction(function () use ($monitor, $classGroup, $data, $slot, $startsAt, $endsAt, $weeks): int {
            User::query()->lockForUpdate()->findOrFail($monitor->id);
            Gate::forUser($monitor)->authorize('create', [Slot::class, $classGroup]);

            if ($slot !== null && $slot->refresh()->cancelled_at !== null) {
                throw ValidationException::withMessages(['starts_at' => 'Este horário já foi cancelado']);
            }

            if ($startsAt->lessThanOrEqualTo(now())) {
                throw ValidationException::withMessages(['starts_at' => 'O horário precisa começar no futuro']);
            }

            if ($weeks > 1 && $endsAt->greaterThan($startsAt->addWeek())) {
                throw ValidationException::withMessages(['starts_at' => 'Este horário se sobrepõe a outro horário seu']);
            }

            $occurrences = [];

            for ($week = 0; $week < $weeks; $week++) {
                $occurrences[] = [
                    'class_group_id' => $classGroup->id,
                    'monitor_id'     => $monitor->id,
                    'starts_at'      => $startsAt->addWeeks($week)->utc(),
                    'ends_at'        => $endsAt->addWeeks($week)->utc(),
                    'location'       => $data['location'],
                    'capacity'       => $data['capacity'],
                ];
            }

            $overlaps = Slot::query()->active()
                ->where('monitor_id', $monitor->id)
                ->when($slot !== null, fn ($query) => $query->whereKeyNot($slot?->id))
                ->where(function ($query) use ($occurrences): void {
                    foreach ($occurrences as $occurrence) {
                        $query->orWhere(fn ($interval) => $interval
                            ->where('starts_at', '<', $occurrence['ends_at'])
                            ->where('ends_at', '>', $occurrence['starts_at']));
                    }
                })
                ->exists();

            if ($overlaps) {
                throw ValidationException::withMessages(['starts_at' => 'Este horário se sobrepõe a outro horário seu']);
            }

            if ($slot === null) {
                $timestamp = now();
                $rows      = array_map(
                    fn (array $occurrence) => $occurrence + ['created_at' => $timestamp, 'updated_at' => $timestamp],
                    $occurrences,
                );
                Slot::query()->insert($rows);
            } else {
                $slot->update($occurrences[0]);
            }

            return $weeks;
        }, attempts: 3);
    }
}
