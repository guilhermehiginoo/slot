<?php

namespace Database\Seeders;

use App\Enums\{MonitorApplicationStatus, MonitorInvitationStatus, Role};
use App\Models\{ClassGroup, MonitorApplication, MonitorInvitation, User};
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MonitorSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $ana = User::query()->where('email', 'ana.costa@slot.test')->firstOrFail();
        $t01 = ClassGroup::query()
            ->whereRelation('course', 'code', 'IMD0030')
            ->where('semester', '2026.2')
            ->where('number', 'T01')
            ->firstOrFail();

        $joao    = $this->student('João Silva', 'joao.silva@slot.test');
        $marina  = $this->student('Marina Santos', 'marina.santos@slot.test');
        $lucas   = $this->student('Lucas Almeida', 'lucas.almeida@slot.test');
        $beatriz = $this->student('Beatriz Lima', 'beatriz.lima@slot.test');

        $t01->monitors()->syncWithoutDetaching([
            $joao->id   => ['appointed_by' => $ana->id, 'appointed_at' => now()],
            $marina->id => ['appointed_by' => $ana->id, 'appointed_at' => now()],
        ]);

        MonitorInvitation::query()->firstOrCreate(
            ['class_group_id' => $t01->id, 'user_id' => $lucas->id],
            ['invited_by' => $ana->id, 'status' => MonitorInvitationStatus::Pending],
        );

        MonitorApplication::query()->firstOrCreate(
            ['class_group_id' => $t01->id, 'user_id' => $beatriz->id],
            ['message' => 'Fui aprovada em LP1 com 9,5 e gostaria de ajudar a turma.', 'status' => MonitorApplicationStatus::Pending],
        );
    }

    private function student(string $name, string $email): User
    {
        return User::query()->updateOrCreate(['email' => $email], [
            'name'              => $name,
            'password'          => 'password',
            'role'              => Role::Student,
            'email_verified_at' => now(),
        ]);
    }
}
