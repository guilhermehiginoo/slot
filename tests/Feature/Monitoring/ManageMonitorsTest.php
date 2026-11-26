<?php

namespace Tests\Feature\Monitoring;

use App\Enums\MonitorInvitationStatus;
use App\Models\{ClassGroup, MonitorInvitation, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

class ManageMonitorsTest extends TestCase
{
    use RefreshDatabase;

    private User $ana;

    private ClassGroup $classGroup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ana        = User::factory()->professor()->create();
        $this->classGroup = ClassGroup::factory()->create();
        $this->classGroup->professors()->attach($this->ana);
    }

    #[TestDox('Cenário: Revogar um convite')]
    public function test_professor_revokes_a_pending_invitation()
    {
        $invitation = MonitorInvitation::factory()->for($this->classGroup)->create();

        $this->actingAs($this->ana)
            ->delete(route('professor.invitations.destroy', $invitation))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame(MonitorInvitationStatus::Revoked, $invitation->fresh()?->status);
    }

    public function test_only_pending_invitations_can_be_revoked()
    {
        $invitation = MonitorInvitation::factory()->for($this->classGroup)->declined()->create();

        $this->actingAs($this->ana)
            ->delete(route('professor.invitations.destroy', $invitation))
            ->assertSessionHasErrors(['invitation' => 'Este convite não está mais pendente.']);

        $this->assertSame(MonitorInvitationStatus::Declined, $invitation->fresh()?->status);
    }

    public function test_other_professors_cannot_revoke_the_invitation()
    {
        $invitation = MonitorInvitation::factory()->for($this->classGroup)->create();

        $this->actingAs(User::factory()->professor()->create())
            ->delete(route('professor.invitations.destroy', $invitation))
            ->assertForbidden();

        $this->assertSame(MonitorInvitationStatus::Pending, $invitation->fresh()?->status);
    }

    #[TestDox('Cenário: Remover um monitor')]
    public function test_professor_removes_a_monitor()
    {
        $joao  = User::factory()->create();
        $other = ClassGroup::factory()->create();
        $this->classGroup->monitors()->attach($joao, ['appointed_by' => $this->ana->id, 'appointed_at' => now()]);
        $other->monitors()->attach($joao, ['appointed_by' => $this->ana->id, 'appointed_at' => now()]);

        $this->actingAs($this->ana)
            ->delete(route('professor.class-groups.monitors.destroy', [$this->classGroup, $joao]))
            ->assertRedirect();

        $this->assertFalse($this->classGroup->monitors()->whereKey($joao->id)->exists());
        $this->assertTrue($other->monitors()->whereKey($joao->id)->exists());
    }

    public function test_other_professors_cannot_remove_a_monitor()
    {
        $joao = User::factory()->create();
        $this->classGroup->monitors()->attach($joao, ['appointed_by' => $this->ana->id, 'appointed_at' => now()]);

        $this->actingAs(User::factory()->professor()->create())
            ->delete(route('professor.class-groups.monitors.destroy', [$this->classGroup, $joao]))
            ->assertForbidden();

        $this->assertTrue($this->classGroup->monitors()->whereKey($joao->id)->exists());
    }

    public function test_removing_someone_who_is_not_a_monitor_returns_not_found()
    {
        $this->actingAs($this->ana)
            ->delete(route('professor.class-groups.monitors.destroy', [$this->classGroup, User::factory()->create()]))
            ->assertNotFound();
    }
}
