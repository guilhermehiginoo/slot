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
        $ana       = $this->professor('Ana Costa', 'ana.costa@slot.test');
        $carlos    = $this->professor('Carlos Alves', 'carlos.alves@slot.test');
        $professor = $this->professor('Professor', 'professor@slot.test');
        $fernanda  = $this->professor('Fernanda Rocha', 'fernanda.rocha@slot.test');
        $ricardo   = $this->professor('Ricardo Menezes', 'ricardo.menezes@slot.test');
        $juliana   = $this->professor('Juliana Freitas', 'juliana.freitas@slot.test');
        $paulo     = $this->professor('Paulo Teixeira', 'paulo.teixeira@slot.test');

        $classGroups = [
            ['IMD0030', '2026.2', 'T01', '35M12', [$ana, $carlos, $professor]],
            ['IMD0030', '2026.2', 'T02', '24T34', [$carlos]],
            ['IMD0012', '2026.2', 'T01', '246M34', [$ana]],
            ['IMD0029', '2026.2', 'T01', '35T12', [$carlos]],
            ['IMD0029', '2026.2', 'T02', '24M34', [$fernanda]],
            ['IMD0028', '2026.2', 'T01', '35M34', [$fernanda]],
            ['IMD0041', '2026.2', 'T01', '24T12', [$ricardo, $professor]],
            ['IMD0024', '2026.2', 'T01', '246T12', [$juliana]],
            ['IMD0033', '2026.2', 'T01', '35T34', [$paulo]],
            ['IMD0030', '2026.1', 'T01', '35M12', [$ricardo]],
            ['IMD0012', '2026.1', 'T01', '246M34', [$professor, $ana]],
            ['IMD0029', '2026.1', 'T01', '35T12', [$juliana]],
            ['IMD0030', '2025.2', 'T01', '24M12', [$paulo]],
            ['IMD0028', '2025.2', 'T01', '35M34', [$carlos]],
            ['IMD0030', '2025.1', 'T01', '35M12', [$ana]],
            ['IMD0041', '2025.1', 'T01', '24T12', [$ricardo]],
        ];

        foreach ($classGroups as [$code, $semester, $number, $schedule, $professors]) {
            ClassGroup::query()
                ->updateOrCreate(
                    [
                        'course_id' => Course::query()->where('code', $code)->value('id'),
                        'semester'  => $semester,
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
