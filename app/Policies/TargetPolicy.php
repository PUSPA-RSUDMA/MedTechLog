<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Target;
use App\Models\User;

class TargetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Target $target): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $visibleIds = $user->visibleUserIds() ?? [];

        if (in_array($target->owner_id, $visibleIds, true)) {
            return true;
        }

        return $user->isSupervisor() && $user->unit_id !== null && $target->unit_id === $user->unit_id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isSupervisor();
    }

    public function update(User $user, Target $target): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isSupervisor()) {
            $visibleIds = $user->visibleUserIds() ?? [];

            return in_array($target->owner_id, $visibleIds, true)
                || ($user->unit_id !== null && $target->unit_id === $user->unit_id);
        }

        return false;
    }

    public function delete(User $user, Target $target): bool
    {
        return $this->update($user, $target);
    }
}
