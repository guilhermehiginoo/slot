<?php

namespace Tests\Feature\Monitoring;

use App\Enums\MonitorInvitationStatus;
use App\Models\{ClassGroup, Course, MonitorInvitation, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

class RespondMonitorInvitationTest extends TestCase
{
    use RefreshDatabase;

    private User $ana;

    private User $joao;

    private ClassGroup $classGroup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ana        = User::factory()->professor()->create(['name' => 'Ana Costa']);
        $this->joao       = User::factory()->create();
        $course           = Course::factory()->create(['code' => 'IMD0030', 'name' => 'Linguagem de Programação I']);
        $this->classGroup = ClassGroup::factory()->for($course)->create(['number' => 'T01']);
    }

    private function invite(?ClassGroup $classGroup = null): MonitorInvitation
    {
        return MonitorInvitation::factory()
            ->for($classGroup ?? $this->classGroup)
            ->create(['user_id' => $this->joao->id, 'invited_by' => $this->ana->id]);
    }

    public function test_the_student_sees_pending_invitations_on_the_dashboard()
    {
        $invitation = $this->invite();
        MonitorInvitation::factory()->declined()->create(['user_id' => $this->joao->id]);
        MonitorInvitation::factory()->create();

        $this->actingAs($this->joao)
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('dashboard')
                ->where('monitorInvitations', [[
                    'id'         => $invitation->id,
                    'classGroup' => ['number' => 'T01', 'course' => ['code' => 'IMD0030', 'name' => 'Linguagem de Programação I']],
                    'invitedBy'  => 'Ana Costa',
                    'created_at' => $invitation->created_at?->toIso8601String(),
                ]]));
    }

    #[TestDox('Cenário: O aluno aceita')]
    public function test_the_student_accepts_and_becomes_a_monitor()
    {
        $invitation = $this->invite();

        $this->actingAs($this->joao)
            ->patch(route('invitations.update', $invitation), ['status' => 'accepted'])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame(MonitorInvitationStatus::Accepted, $invitation->fresh()?->status);
        $this->assertDatabaseHas('class_monitor', [
            'class_group_id' => $this->classGroup->id,
            'user_id'        => $this->joao->id,
            'appointed_by'   => $this->ana->id,
        ]);
    }

    #[TestDox('Cenário: O aluno recusa')]
    public function test_the_student_declines()
    {
        $invitation = $this->invite();
        $this->classGroup->professors()->attach($this->ana);

        $this->actingAs($this->joao)
            ->patch(route('invitations.update', $invitation), ['status' => 'declined'])
            ->assertSessionHasNoErrors();

        $this->assertSame(0, $this->classGroup->monitors()->count());
        $this->actingAs($this->ana)
            ->get(route('professor.class-groups.show', $this->classGroup))
            ->assertInertia(fn (Assert $page) => $page
                ->where('invitations.0.status', 'declined')
                ->has('monitors', 0));
    }

    public function test_a_revoked_invitation_cannot_be_accepted()
    {
        $invitation = $this->invite();
        $invitation->update(['status' => MonitorInvitationStatus::Revoked]);

        $this->actingAs($this->joao)
            ->patch(route('invitations.update', $invitation), ['status' => 'accepted'])
            ->assertSessionHasErrors(['invitation' => 'Este convite não está mais pendente.']);

        $this->assertSame(0, $this->classGroup->monitors()->count());
    }

    public function test_only_the_invited_student_can_respond()
    {
        $invitation = $this->invite();

        $this->actingAs(User::factory()->create())
            ->patch(route('invitations.update', $invitation), ['status' => 'accepted'])
            ->assertForbidden();

        $this->assertSame(MonitorInvitationStatus::Pending, $invitation->fresh()?->status);
    }

    public function test_the_response_must_be_accept_or_decline()
    {
        $invitation = $this->invite();

        $this->actingAs($this->joao)
            ->patch(route('invitations.update', $invitation), ['status' => 'revoked'])
            ->assertSessionHasErrors('status');
    }

    public function test_a_student_can_monitor_more_than_one_class_group()
    {
        $other = ClassGroup::factory()->create();

        $this->actingAs($this->joao);
        $this->patch(route('invitations.update', $this->invite()), ['status' => 'accepted']);
        $this->patch(route('invitations.update', $this->invite($other)), ['status' => 'accepted']);

        $this->assertSame(2, $this->joao->monitoredClassGroups()->count());
    }
}
