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
        $ana       = $this->professor('ana.costa@slot.test');
        $carlos    = $this->professor('carlos.alves@slot.test');
        $professor = $this->professor('professor@slot.test');
        $ricardo   = $this->professor('ricardo.menezes@slot.test');
        $fernanda  = $this->professor('fernanda.rocha@slot.test');

        $joao    = $this->student('João Silva', 'joao.silva@slot.test');
        $marina  = $this->student('Marina Santos', 'marina.santos@slot.test');
        $lucas   = $this->student('Lucas Almeida', 'lucas.almeida@slot.test');
        $beatriz = $this->student('Beatriz Lima', 'beatriz.lima@slot.test');
        $monitor = $this->student('Monitor', 'monitor@slot.test');
        $aluno   = $this->student('Aluno', 'student@slot.test');
        $pedro   = $this->student('Pedro Henrique', 'pedro.henrique@slot.test');
        $camila  = $this->student('Camila Rodrigues', 'camila.rodrigues@slot.test');
        $rafael  = $this->student('Rafael Souza', 'rafael.souza@slot.test');
        $larissa = $this->student('Larissa Melo', 'larissa.melo@slot.test');
        $gabriel = $this->student('Gabriel Nunes', 'gabriel.nunes@slot.test');

        $lp1T01      = $this->classGroup('IMD0030', '2026.2', 'T01');
        $lp1T02      = $this->classGroup('IMD0030', '2026.2', 'T02');
        $edbT01      = $this->classGroup('IMD0029', '2026.2', 'T01');
        $edbT02      = $this->classGroup('IMD0029', '2026.2', 'T02');
        $oacT01      = $this->classGroup('IMD0041', '2026.2', 'T01');
        $lp1Previous = $this->classGroup('IMD0030', '2026.1', 'T01');
        $itpPrevious = $this->classGroup('IMD0012', '2026.1', 'T01');

        $this->appoint($lp1T01, $ana, [$joao, $marina]);
        $this->appoint($lp1T02, $carlos, [$monitor]);
        $this->appoint($oacT01, $professor, [$monitor]);
        $this->appoint($edbT02, $fernanda, [$camila]);
        $this->appoint($lp1Previous, $ricardo, [$joao]);
        $this->appoint($itpPrevious, $ana, [$marina]);

        $this->invite($lp1T01, $ana, $lucas, MonitorInvitationStatus::Pending);
        $this->invite($lp1T01, $ana, $larissa, MonitorInvitationStatus::Declined);
        $this->invite($lp1T01, $carlos, $pedro, MonitorInvitationStatus::Revoked);
        $this->invite($lp1T02, $carlos, $monitor, MonitorInvitationStatus::Accepted);
        $this->invite($edbT01, $carlos, $aluno, MonitorInvitationStatus::Pending);
        $this->invite($oacT01, $professor, $rafael, MonitorInvitationStatus::Pending);

        $this->apply($lp1T01, $beatriz, 'Fui aprovada em LP1 com 9,5 e gostaria de ajudar a turma.');
        $this->apply($lp1T01, $gabriel, 'Já ajudo colegas com C++ no grupo de estudos e queria fazer isso como monitor.');
        $this->apply($oacT01, $pedro, null);
        $this->apply($edbT01, $larissa, 'Tenho disponibilidade nas tardes de terça e quinta.');
    }

    /** @param list<User> $students */
    private function appoint(ClassGroup $classGroup, User $professor, array $students): void
    {
        $classGroup->monitors()->syncWithoutDetaching(
            collect($students)->mapWithKeys(fn (User $student) => [
                $student->id => ['appointed_by' => $professor->id, 'appointed_at' => now()],
            ])->all(),
        );
    }

    private function invite(ClassGroup $classGroup, User $professor, User $student, MonitorInvitationStatus $status): void
    {
        MonitorInvitation::query()->firstOrCreate(
            ['class_group_id' => $classGroup->id, 'user_id' => $student->id],
            ['invited_by' => $professor->id, 'status' => $status],
        );
    }

    private function apply(ClassGroup $classGroup, User $student, ?string $message): void
    {
        MonitorApplication::query()->firstOrCreate(
            ['class_group_id' => $classGroup->id, 'user_id' => $student->id],
            ['message' => $message, 'status' => MonitorApplicationStatus::Pending],
        );
    }

    private function classGroup(string $code, string $semester, string $number): ClassGroup
    {
        return ClassGroup::query()
            ->whereRelation('course', 'code', $code)
            ->where('semester', $semester)
            ->where('number', $number)
            ->firstOrFail();
    }

    private function professor(string $email): User
    {
        return User::query()->where('email', $email)->firstOrFail();
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
