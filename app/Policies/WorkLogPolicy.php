<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\WorkLog;

class WorkLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, WorkLog $workLog): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $visibleIds = $user->visibleUserIds() ?? [];

        return in_array($workLog->user_id, $visibleIds, true);
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, WorkLog $workLog): bool
    {
        return $user->isAdmin() || $workLog->user_id === $user->id;
    }

    public function delete(User $user, WorkLog $workLog): bool
    {
        return $user->isAdmin() || $workLog->user_id === $user->id;
    }
}
