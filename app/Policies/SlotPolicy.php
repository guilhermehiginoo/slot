<?php

namespace App\Policies;

use App\Models\{ClassGroup, Slot, User};

class SlotPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->monitoredClassGroups()->exists()
            || Slot::query()->where('monitor_id', $user->id)->exists();
    }

    public function create(User $user, ClassGroup $classGroup): bool
    {
        return $user->monitoredClassGroups()->whereKey($classGroup->id)->exists();
    }

    public function update(User $user, Slot $slot): bool
    {
        return $slot->monitor_id === $user->id
            && $this->create($user, $slot->classGroup);
    }

    public function delete(User $user, Slot $slot): bool
    {
        return $slot->monitor_id === $user->id;
    }
}
