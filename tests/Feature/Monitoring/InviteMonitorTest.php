<?php

namespace Tests\Feature\Monitoring;

use App\Enums\MonitorInvitationStatus;
use App\Models\{ClassGroup, MonitorInvitation, User};
use App\Notifications\MonitorInvitationReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

class InviteMonitorTest extends TestCase
{
    use RefreshDatabase;

    private User $ana;

    private ClassGroup $classGroup;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        $this->ana        = User::factory()->professor()->create();
        $this->classGroup = ClassGroup::factory()->create();
        $this->classGroup->professors()->attach($this->ana);
    }

    #[TestDox('Cenário: Convidar um monitor')]
    public function test_professor_invites_a_student()
    {
        $joao = User::factory()->create(['name' => 'João Silva']);

        $this->actingAs($this->ana)
            ->post(route('professor.class-groups.invitations.store', $this->classGroup), ['user_id' => $joao->id])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('monitor_invitations', [
            'class_group_id' => $this->classGroup->id,
            'user_id'        => $joao->id,
            'invited_by'     => $this->ana->id,
            'status'         => 'pending',
        ]);
        $this->assertFalse($this->classGroup->monitors()->whereKey($joao->id)->exists());
        Notification::assertSentTo($joao, MonitorInvitationReceived::class);

        $this->get(route('professor.class-groups.show', $this->classGroup))
            ->assertInertia(fn (Assert $page) => $page
                ->where('invitations.0.student.name', 'João Silva')
                ->where('invitations.0.status', 'pending')
                ->has('monitors', 0));
    }

    #[TestDox('Cenário: Convite repetido')]
    public function test_a_student_with_a_pending_invitation_cannot_be_invited_again()
    {
        $joao = User::factory()->create(['name' => 'João']);
        MonitorInvitation::factory()->for($this->classGroup)->create(['user_id' => $joao->id]);

        $this->actingAs($this->ana)
            ->post(route('professor.class-groups.invitations.store', $this->classGroup), ['user_id' => $joao->id])
            ->assertSessionHasErrors(['user_id' => 'O João já tem um convite pendente para esta turma.']);

        $this->assertSame(1, MonitorInvitation::count());
    }

    public function test_a_declined_invitation_does_not_block_a_new_one()
    {
        $joao = User::factory()->create();
        MonitorInvitation::factory()->for($this->classGroup)->declined()->create(['user_id' => $joao->id]);

        $this->actingAs($this->ana)
            ->post(route('professor.class-groups.invitations.store', $this->classGroup), ['user_id' => $joao->id])
            ->assertSessionHasNoErrors();

        $this->assertSame(2, MonitorInvitation::count());
    }

    public function test_a_current_monitor_cannot_be_invited()
    {
        $joao = User::factory()->create(['name' => 'João']);
        $this->classGroup->monitors()->attach($joao, ['appointed_by' => $this->ana->id, 'appointed_at' => now()]);

        $this->actingAs($this->ana)
            ->post(route('professor.class-groups.invitations.store', $this->classGroup), ['user_id' => $joao->id])
            ->assertSessionHasErrors(['user_id' => 'O João já é monitor desta turma.']);
    }

    #[TestDox('Cenário: Só alunos podem ser monitores')]
    public function test_only_students_appear_as_candidates_and_can_be_invited()
    {
        $student   = User::factory()->create(['name' => 'Joana Aluna']);
        $professor = User::factory()->professor()->create(['name' => 'Joana Professora']);
        User::factory()->admin()->create(['name' => 'Joana Admin']);

        $this->actingAs($this->ana)
            ->get(route('professor.class-groups.show', ['classGroup' => $this->classGroup, 'busca' => 'Joana']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('candidates', 1)
                ->where('candidates.0', ['id' => $student->id, 'name' => 'Joana Aluna', 'email' => $student->email]));

        $this->post(route('professor.class-groups.invitations.store', $this->classGroup), ['user_id' => $professor->id])
            ->assertSessionHasErrors('user_id');
    }

    public function test_candidates_exclude_monitors_and_pending_invitations()
    {
        $monitor = User::factory()->create(['name' => 'Joana Monitora']);
        $invited = User::factory()->create(['name' => 'Joana Convidada']);
        $this->classGroup->monitors()->attach($monitor, ['appointed_by' => $this->ana->id, 'appointed_at' => now()]);
        MonitorInvitation::factory()->for($this->classGroup)->create(['user_id' => $invited->id]);

        $this->actingAs($this->ana)
            ->get(route('professor.class-groups.show', ['classGroup' => $this->classGroup, 'busca' => 'Joana']))
            ->assertInertia(fn (Assert $page) => $page->has('candidates', 0));
    }

    public function test_candidates_are_empty_without_a_search()
    {
        User::factory()->create();

        $this->actingAs($this->ana)
            ->get(route('professor.class-groups.show', $this->classGroup))
            ->assertInertia(fn (Assert $page) => $page->has('candidates', 0));
    }

    public function test_only_professors_of_the_class_group_can_invite()
    {
        $other = User::factory()->professor()->create();
        $joao  = User::factory()->create();

        $this->actingAs($other)
            ->post(route('professor.class-groups.invitations.store', $this->classGroup), ['user_id' => $joao->id])
            ->assertForbidden();

        $this->assertSame(0, MonitorInvitation::count());
    }

    public function test_the_page_lists_declined_and_revoked_invitations_but_not_accepted_ones()
    {
        MonitorInvitation::factory()->for($this->classGroup)->declined()->create();
        MonitorInvitation::factory()->for($this->classGroup)->revoked()->create();
        MonitorInvitation::factory()->for($this->classGroup)->accepted()->create();

        $this->actingAs($this->ana)
            ->get(route('professor.class-groups.show', $this->classGroup))
            ->assertInertia(fn (Assert $page) => $page->has('invitations', 2));

        $this->assertSame(MonitorInvitationStatus::Accepted, MonitorInvitation::latest('id')->first()?->status);
    }
}
