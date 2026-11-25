<?php

namespace Tests\Feature\Monitoring;

use App\Actions\Monitoring\RemoveClassMonitor;
use App\Models\{ClassGroup, Slot, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

class ManageSlotsTest extends TestCase
{
    use RefreshDatabase;

    private User $monitor;

    private ClassGroup $group;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(now()->setDate(2026, 11, 24)->setTime(12, 0));
        $this->monitor = User::factory()->create();
        $this->group   = ClassGroup::factory()->create();
        $this->group->monitors()->attach($this->monitor->id, [
            'appointed_by' => $this->monitor->id,
            'appointed_at' => now(),
        ]);
    }

    #[TestDox('Cenário: Publicar um horário')]
    public function test_monitor_publishes_a_slot(): void
    {
        $this->actingAs($this->monitor)->post(route('monitor.slots.store', $this->group), $this->data())->assertRedirect();
        $this->assertDatabaseHas('slots', ['class_group_id' => $this->group->id, 'monitor_id' => $this->monitor->id, 'capacity' => 3]);
    }

    #[TestDox('Cenário: Horários sobrepostos')]
    public function test_overlap_rejects_the_entire_series(): void
    {
        $url = route('monitor.slots.store', $this->group);
        $this->actingAs($this->monitor)->post($url, $this->data(['starts_at' => '2026-12-08T14:00', 'ends_at' => '2026-12-08T15:00']))->assertRedirect();
        $this->post($url, $this->data(['weeks' => 2]))
            ->assertSessionHasErrors(['starts_at' => 'Este horário se sobrepõe a outro horário seu']);
        $this->assertDatabaseCount('slots', 1);
    }

    #[TestDox('Cenário: Horário no passado')]
    public function test_past_slots_are_rejected(): void
    {
        $this->actingAs($this->monitor)->post(route('monitor.slots.store', $this->group), $this->data([
            'starts_at' => '2026-11-23T14:00',
            'ends_at'   => '2026-11-23T15:00',
        ]))->assertSessionHasErrors(['starts_at' => 'O horário precisa começar no futuro']);
    }

    #[TestDox('Cenário: Não é monitor')]
    public function test_other_students_cannot_publish(): void
    {
        $student = User::factory()->create();
        $this->actingAs($student)->post(route('monitor.slots.store', $this->group), $this->data())->assertForbidden();
    }

    public function test_monitor_can_list_and_publish_a_weekly_series(): void
    {
        $this->actingAs($this->monitor)
            ->post(route('monitor.slots.store', $this->group), $this->data(['weeks' => 3]))
            ->assertRedirect();

        $this->assertDatabaseCount('slots', 3);
        $this->assertDatabaseHas('slots', ['starts_at' => '2026-12-15 17:00:00']);

        $this->get(route('monitor.slots.index'))->assertInertia(fn (Assert $page) => $page
            ->component('monitoring/slots/index')
            ->where('canManageSlots', true)
            ->has('groups', 1)
            ->has('slots.data', 3)
            ->where('slots.data.0.starts_at', '2026-12-15T14:00'));
    }

    public function test_overlap_is_checked_across_classes_and_adjacent_slots_are_allowed(): void
    {
        $otherGroup = ClassGroup::factory()->create();
        $otherGroup->monitors()->attach($this->monitor->id, [
            'appointed_by' => $this->monitor->id,
            'appointed_at' => now(),
        ]);

        $this->actingAs($this->monitor)
            ->post(route('monitor.slots.store', $this->group), $this->data())
            ->assertRedirect();

        $url = route('monitor.slots.store', $otherGroup);
        $this->post($url, $this->data(['starts_at' => '2026-12-01T14:30', 'ends_at' => '2026-12-01T15:30']))
            ->assertSessionHasErrors('starts_at');
        $this->post($url, $this->data(['starts_at' => '2026-12-01T15:00', 'ends_at' => '2026-12-01T16:00']))
            ->assertSessionDoesntHaveErrors();

        $this->assertDatabaseCount('slots', 2);
    }

    public function test_only_the_owner_can_edit_and_cancel(): void
    {
        $this->actingAs($this->monitor)
            ->post(route('monitor.slots.store', $this->group), $this->data())
            ->assertRedirect();

        $slot    = Slot::query()->sole();
        $update  = route('monitor.slots.update', $slot);
        $destroy = route('monitor.slots.destroy', $slot);

        $this->actingAs(User::factory()->create())->patch($update, $this->data())->assertForbidden();
        $this->delete($destroy)->assertForbidden();

        $this->actingAs($this->monitor)->patch($update, $this->data(['capacity' => 5]))
            ->assertSessionDoesntHaveErrors();
        $this->assertDatabaseHas('slots', ['id' => $slot->id, 'capacity' => 5]);

        $this->delete($destroy)->assertRedirect();
        $this->assertNotNull($slot->refresh()->cancelled_at);
        $this->patch($update, $this->data())->assertSessionHasErrors('starts_at');

        $this->post(route('monitor.slots.store', $this->group), $this->data())
            ->assertSessionDoesntHaveErrors();
        $this->assertDatabaseCount('slots', 2);
    }

    public function test_removing_a_monitor_cancels_only_future_slots_of_that_class(): void
    {
        $this->actingAs($this->monitor)
            ->post(route('monitor.slots.store', $this->group), $this->data(['weeks' => 2]))
            ->assertRedirect();

        $this->travelTo(now()->setDate(2026, 12, 2));
        app(RemoveClassMonitor::class)->handle($this->group, $this->monitor);

        $this->assertDatabaseHas('slots', ['starts_at' => '2026-12-01 17:00:00', 'cancelled_at' => null]);
        $this->assertDatabaseMissing('slots', ['starts_at' => '2026-12-08 17:00:00', 'cancelled_at' => null]);
        $this->post(route('monitor.slots.store', $this->group), $this->data(['weeks' => 2]))->assertForbidden();
    }

    /** @param array<string, int|string> $overrides */
    private function data(array $overrides = []): array
    {
        return array_replace([
            'starts_at' => '2026-12-01T14:00',
            'ends_at'   => '2026-12-01T15:00',
            'location'  => 'CIVT A204',
            'capacity'  => 3,
        ], $overrides);
    }
}
