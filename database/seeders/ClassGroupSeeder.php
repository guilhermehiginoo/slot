<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\{ClassGroup, Course, User};
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClassGroupSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $ana    = $this->professor('Ana Costa', 'ana.costa@slot.test');
        $carlos = $this->professor('Carlos Alves', 'carlos.alves@slot.test');

        $classGroups = [
            ['IMD0030', 'T01', '35M12', [$ana, $carlos]],
            ['IMD0030', 'T02', '24T34', [$carlos]],
            ['IMD0012', 'T01', '246M34', [$ana]],
            ['IMD0029', 'T01', '35T12', [$carlos]],
        ];

        foreach ($classGroups as [$code, $number, $schedule, $professors]) {
            ClassGroup::query()
                ->updateOrCreate(
                    [
                        'course_id' => Course::query()->where('code', $code)->value('id'),
                        'semester'  => '2026.2',
                        'number'    => $number,
                    ],
                    ['schedule' => $schedule],
                )
                ->professors()
                ->sync(array_map(fn (User $professor) => $professor->id, $professors));
        }
    }

    private function professor(string $name, string $email): User
    {
        return User::query()->updateOrCreate(['email' => $email], [
            'name'              => $name,
            'password'          => 'password',
            'role'              => Role::Professor,
            'email_verified_at' => now(),
        ]);
    }
}
