<?php

namespace Database\Seeders;

use App\Models\{ClassGroup, Slot, User};
use Carbon\CarbonImmutable;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SlotSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $slots = [
            ['monitor@slot.test', 'IMD0030', 'T02', CarbonImmutable::MONDAY, 14, 'Sala A204', 4, 3],
            ['monitor@slot.test', 'IMD0041', 'T01', CarbonImmutable::WEDNESDAY, 10, 'Laboratório 3', 3, 3],
            ['joao.silva@slot.test', 'IMD0030', 'T01', CarbonImmutable::TUESDAY, 16, 'Sala A101', 5, 2],
            ['marina.santos@slot.test', 'IMD0030', 'T01', CarbonImmutable::THURSDAY, 8, 'Google Meet', 8, 2],
            ['camila.rodrigues@slot.test', 'IMD0029', 'T02', CarbonImmutable::FRIDAY, 14, 'Sala B105', 4, 2],
        ];

        foreach ($slots as [$email, $code, $number, $weekday, $hour, $location, $capacity, $weeks]) {
            $monitor    = User::query()->where('email', $email)->firstOrFail();
            $classGroup = ClassGroup::query()
                ->whereRelation('course', 'code', $code)
                ->currentSemester()
                ->where('number', $number)
                ->firstOrFail();

            $hasUpcomingSlots = Slot::query()
                ->where('monitor_id', $monitor->id)
                ->where('class_group_id', $classGroup->id)
                ->where('starts_at', '>', now())
                ->exists();

            if ($hasUpcomingSlots) {
                continue;
            }

            $firstStart = CarbonImmutable::now(Slot::TIMEZONE)->next($weekday)->setTime($hour, 0);

            for ($week = 0; $week < $weeks; $week++) {
                $startsAt = $firstStart->addWeeks($week);

                Slot::query()->create([
                    'class_group_id' => $classGroup->id,
                    'monitor_id'     => $monitor->id,
                    'starts_at'      => $startsAt->utc(),
                    'ends_at'        => $startsAt->addHours(2)->utc(),
                    'location'       => $location,
                    'capacity'       => $capacity,
                    'cancelled_at'   => $week === $weeks - 1 && $code === 'IMD0041' ? now() : null,
                ]);
            }
        }
    }
}
